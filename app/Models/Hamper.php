<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hamper extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'image', 'base_price', 'is_custom_allowed', 'is_active'];

    public function items()
    {
        return $this->hasMany(HamperItem::class);
    }
}
