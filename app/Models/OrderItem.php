<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id', 'product_id', 'hamper_id', 'product_variant_id', 'item_name',
        'qty', 'price', 'cost_price', 'note', 'custom_hamper_config', 'is_reviewed',
    ];

    protected $casts = ['custom_hamper_config' => 'array'];

    public function order() { return $this->belongsTo(Order::class); }
    public function product() { return $this->belongsTo(Product::class); }
    public function hamper() { return $this->belongsTo(Hamper::class); }
    public function review() { return $this->hasOne(Review::class); }
}
