<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = [
        'cart_id', 'product_id', 'hamper_id', 'product_variant_id', 'qty', 'note', 'custom_hamper_config',
    ];

    protected $casts = ['custom_hamper_config' => 'array'];

    public function cart() { return $this->belongsTo(Cart::class); }
    public function product() { return $this->belongsTo(Product::class); }
    public function hamper() { return $this->belongsTo(Hamper::class); }
    public function variant() { return $this->belongsTo(ProductVariant::class, 'product_variant_id'); }
}
