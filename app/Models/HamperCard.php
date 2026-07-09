<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HamperCard extends Model
{
    protected $fillable = ['name', 'image', 'extra_price', 'is_active'];
}
