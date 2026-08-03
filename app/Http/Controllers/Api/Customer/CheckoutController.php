<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\DeliverySlotBooking;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\StockLog;
use App\Models\StoreSetting;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function __construct(private NotificationService $notifications) {}

    /**
     * Proses Checkout (fitur A.5): pilih delivery/pickup, tanggal & slot waktu,
     * catatan, hitung subtotal+ongkir+diskon, lalu buat Order + Payment.
     * Pesanan SELALU dibuat online - fulfillment_type hanya menentukan cara terima barang.
     */
    public function store(Request $request)
    {
        $request->validate([
            'fulfillment_type' => 'required|in:delivery,pickup',
            'address_id' => 'required_if:fulfillment_type,delivery|exists:addresses,id',
            'delivery_slot_id' => 'required|exists:delivery_slots,id',
            'scheduled_date' => 'required|date|after_or_equal:today',
            'note' => 'nullable|string|max:500',
            'payment_method' => 'required|in:qris,transfer_bank,bayar_di_tempat',
        ]);

        $user = $request->user();
        $cart = $user->cart()->with(['items.product', 'items.hamper', 'items.variant'])->firstOrFail();

        abort_if($cart->items->isEmpty(), 422, 'Keranjang Anda masih kosong.');

        // "Bayar di Tempat" hanya berlaku untuk metode pickup (fitur A.8)
        if ($request->payment_method === 'bayar_di_tempat' && $request->fulfillment_type !== 'pickup') {
            return response()->json(['message' => 'Bayar di Tempat hanya tersedia untuk metode Pickup.'], 422);
        }

        // Cek kuota slot pengiriman pada tanggal tsb (fitur B.18 - Manajemen Jadwal Pengiriman)
        $slot = \App\Models\DeliverySlot::findOrFail($request->delivery_slot_id);
        $bookedCount = DeliverySlotBooking::where('delivery_slot_id', $slot->id)
            ->where('date', $request->scheduled_date)->count();

        abort_if($bookedCount >= $slot->quota_per_day, 422, 'Kuota slot waktu tersebut sudah penuh, silakan pilih slot lain.');

        $deliveryRegionId = null;
        $shippingCost = 0;

        if ($request->fulfillment_type === 'delivery') {
            $address = $user->addresses()->with('deliveryRegion')->findOrFail($request->address_id);
            $deliveryRegionId = $address->delivery_region_id;
            $shippingCost = $address->deliveryRegion->shipping_cost;

            $settings = StoreSetting::first();
            $subtotalCheck = $cart->items->sum(fn ($i) => $this->unitPrice($i) * $i->qty);
            if ($settings && $subtotalCheck < $settings->min_order_delivery) {
                return response()->json([
                    'message' => 'Minimum pembelian untuk Delivery adalah Rp ' . number_format($settings->min_order_delivery, 0, ',', '.'),
                ], 422);
            }
        }

        $order = DB::transaction(function () use ($request, $user, $cart, $deliveryRegionId, $shippingCost) {
            $subtotal = $cart->items->sum(fn ($i) => $this->unitPrice($i) * $i->qty);
            $discount = 0; // TODO: hitung dari tabel promotions aktif
            $total = $subtotal - $discount + $shippingCost;

            $order = Order::create([
                'order_number' => 'INV/' . now()->format('Ymd') . '/' . str_pad(Order::whereDate('created_at', now())->count() + 1, 4, '0', STR_PAD_LEFT),
                'user_id' => $user->id,
                'fulfillment_type' => $request->fulfillment_type,
                'address_id' => $request->fulfillment_type === 'delivery' ? $request->address_id : null,
                'delivery_region_id' => $deliveryRegionId,
                'delivery_slot_id' => $request->delivery_slot_id,
                'scheduled_date' => $request->scheduled_date,
                'note' => $request->note,
                'subtotal' => $subtotal,
                'discount' => $discount,
                'shipping_cost' => $shippingCost,
                'total' => $total,
                'status' => 'menunggu_bayar',
            ]);

            foreach ($cart->items as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item->product_id,
                    'hamper_id' => $item->hamper_id,
                    'product_variant_id' => $item->product_variant_id,
                    'item_name' => $item->product->name ?? $item->hamper->name ?? 'Parsel Kustom',
                    'qty' => $item->qty,
                    'price' => $this->unitPrice($item),
                    'cost_price' => $item->product->cost_price ?? null,
                    'note' => $item->note,
                    'custom_hamper_config' => $item->custom_hamper_config,
                ]);

                // Kurangi stok produk & catat riwayatnya (fitur B.14) - tanpa ini,
                // laporan Riwayat Perubahan Stok cuma menampilkan edit manual admin,
                // tidak pernah menampilkan pengurangan dari penjualan asli.
                if ($item->product) {
                    $stockBefore = $item->product->stock;
                    $item->product->decrement('stock', $item->qty);
                    $item->product->increment('sold_count', $item->qty);

                    StockLog::create([
                        'product_id' => $item->product_id,
                        'user_id' => null, // otomatis oleh sistem, bukan admin
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockBefore - $item->qty,
                        'change' => -$item->qty,
                        'reason' => 'Penjualan - ' . $order->order_number,
                    ]);
                }
            }

            Payment::create([
                'order_id' => $order->id,
                'method' => $request->payment_method,
                'amount' => $order->total,
                'status' => $request->payment_method === 'bayar_di_tempat' ? 'pending' : 'pending',
            ]);

            DeliverySlotBooking::create([
                'order_id' => $order->id,
                'delivery_slot_id' => $request->delivery_slot_id,
                'date' => $request->scheduled_date,
            ]);

            // Kosongkan keranjang setelah checkout berhasil
            $cart->items()->delete();

            return $order;
        });

        // Kirim notifikasi "Pesanan Dibuat" via WhatsApp & Email (fitur A.16 - template 1)
        $this->notifications->sendOrderNotification($order, 'order_created');

        return response()->json(['order' => $order->load('items', 'payment')], 201);
    }

    private function unitPrice($cartItem): float
    {
        if ($cartItem->custom_hamper_config && isset($cartItem->custom_hamper_config['estimated_price'])) {
            return (float) $cartItem->custom_hamper_config['estimated_price'];
        }

        $price = $cartItem->product->price_unit ?? $cartItem->hamper->base_price ?? 0;
        if ($cartItem->variant) {
            $price += $cartItem->variant->price_adjustment;
        }
        return $price;
    }
}
