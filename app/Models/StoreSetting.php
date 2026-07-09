<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StoreSetting extends Model
{
    protected $fillable = [
        'store_name', 'tagline', 'about_content', 'logo', 'address', 'latitude', 'longitude',
        'whatsapp_number', 'bank_accounts', 'qris_image', 'operating_hours',
        'min_order_delivery', 'smtp_config', 'whatsapp_api_key',
    ];

    protected $casts = ['bank_accounts' => 'array', 'operating_hours' => 'array'];
}
