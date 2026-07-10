<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'name', 'slug', 'description', 'origin_region', 'origin_type',
        'unit', 'price_unit', 'price_wholesale', 'wholesale_min_qty', 'cost_price',
        'stock', 'min_stock_alert', 'freshness_days', 'stock_in_date', 'storage_tips',
        'labels', 'is_seasonal', 'is_active', 'is_featured', 'rating_avg', 'rating_count', 'sold_count',
    ];

    protected $casts = [
        'labels' => 'array',
        'stock_in_date' => 'date',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    // Estimasi margin: harga jual - harga modal (fitur unggulan dashboard admin)
    public function getEstimatedMarginAttribute()
    {
        if (!$this->cost_price) return null;
        return $this->price_unit - $this->cost_price;
    }

    // Sisa hari kesegaran berdasarkan tanggal masuk stok
    public function getFreshnessRemainingAttribute()
    {
        if (!$this->stock_in_date || !$this->freshness_days) return null;
        $daysPassed = now()->diffInDays($this->stock_in_date);
        return max(0, $this->freshness_days - $daysPassed);
    }
}
