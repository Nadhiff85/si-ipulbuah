<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\SystemSetting;
use App\Models\User;
use App\Services\WhatsAppService;
use Illuminate\Console\Command;

class CheckProductFreshness extends Command
{
    protected $signature = 'products:check-freshness';
    protected $description = 'Peringatan dini produk hampir kadaluarsa (fitur B.19) & auto-nonaktifkan produk lewat batas kesegaran (fitur C.9)';

    public function handle(WhatsAppService $whatsapp): int
    {
        $settings = SystemSetting::firstOrCreate([]);
        $maxDays = $settings->max_freshness_days_default ?? 7;

        $products = Product::where('is_active', true)
            ->whereNotNull('stock_in_date')
            ->whereNotNull('freshness_days')
            ->get();

        $admins = User::role(['admin', 'superadmin'])->get();

        foreach ($products as $product) {
            $daysPassed = now()->diffInDays($product->stock_in_date);
            $remaining = $product->freshness_days - $daysPassed;

            // Peringatan Dini Kesegaran (fitur B.19): sisa kesegaran <= 2 hari
            if ($remaining <= 2 && $remaining > 0) {
                foreach ($admins as $admin) {
                    $whatsapp->send(
                        $admin->phone,
                        "⚠️ Peringatan: Produk \"{$product->name}\" tersisa {$remaining} hari kesegaran. Pertimbangkan diskon kilat atau olah menjadi jus/bowl buah.",
                        'freshness_warning',
                        $admin->id
                    );
                }
            }

            // Auto-nonaktifkan produk yang sudah lewat batas masa aktif (fitur C.9)
            if ($daysPassed > $maxDays) {
                $product->update(['is_active' => false]);
                $this->info("Produk '{$product->name}' otomatis dinonaktifkan (lewat {$maxDays} hari).");
            }
        }

        $this->info('Pengecekan kesegaran produk selesai.');
        return self::SUCCESS;
    }
}
