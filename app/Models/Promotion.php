<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $fillable = [
        'name', 'discount_type', 'discount_value', 'scope', 'target_id',
        'min_purchase_qty', 'start_date', 'end_date', 'is_active',
    ];

    protected $casts = ['start_date' => 'date', 'end_date' => 'date'];
}
