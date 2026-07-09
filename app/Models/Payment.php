<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $fillable = [
        'order_id', 'method', 'amount', 'proof_image', 'qris_reference',
        'status', 'confirmed_by', 'confirmed_at',
    ];

    public function order() { return $this->belongsTo(Order::class); }
    public function confirmer() { return $this->belongsTo(User::class, 'confirmed_by'); }
}
