<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'order_number', 'user_id', 'outlet_id', 'fulfillment_type', 'address_id',
        'delivery_region_id', 'delivery_slot_id', 'scheduled_date', 'note',
        'subtotal', 'discount', 'shipping_cost', 'total', 'status',
        'confirmed_at', 'processed_at', 'shipped_at', 'completed_at',
    ];

    protected $casts = ['scheduled_date' => 'date'];

    public function user() { return $this->belongsTo(User::class); }
    public function outlet() { return $this->belongsTo(Outlet::class); }
    public function address() { return $this->belongsTo(Address::class); }
    public function deliveryRegion() { return $this->belongsTo(DeliveryRegion::class); }
    public function deliverySlot() { return $this->belongsTo(DeliverySlot::class); }
    public function items() { return $this->hasMany(OrderItem::class); }
    public function payment() { return $this->hasOne(Payment::class); }

    // Timeline status utk tracking pesanan (dipakai di frontend Vue)
    public function getStatusTimelineAttribute()
    {
        return [
            'menunggu_bayar' => 'Menunggu Bayar',
            'dikonfirmasi' => 'Dikonfirmasi',
            'diproses' => 'Diproses',
            'dikirim_siap_ambil' => $this->fulfillment_type === 'delivery' ? 'Dikirim' : 'Siap Ambil',
            'selesai' => 'Selesai',
        ];
    }
}
