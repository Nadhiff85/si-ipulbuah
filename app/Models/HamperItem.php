<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HamperItem extends Model
{
    protected $fillable = ['hamper_id', 'product_id', 'qty'];

    public function hamper() { return $this->belongsTo(Hamper::class); }
    public function product() { return $this->belongsTo(Product::class); }
}
