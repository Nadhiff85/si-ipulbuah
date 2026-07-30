<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PriceHistory extends Model
{
    protected $fillable = [
        'product_id', 'changed_by',
        'old_price_unit', 'new_price_unit',
        'old_price_wholesale', 'new_price_wholesale',
        'old_cost_price', 'new_cost_price',
        'reason', 'effective_at',
    ];

    protected $casts = [
        'effective_at' => 'datetime',
    ];

    public function product() { return $this->belongsTo(Product::class); }
    public function changedBy() { return $this->belongsTo(User::class, 'changed_by'); }
}
