<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryRegion extends Model
{
    protected $fillable = ['regency', 'district', 'shipping_cost', 'is_active'];
}
