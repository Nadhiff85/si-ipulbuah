<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliverySlotBooking extends Model
{
    protected $fillable = ['order_id', 'delivery_slot_id', 'date'];
    protected $casts = ['date' => 'date'];

    public function order() { return $this->belongsTo(Order::class); }
    public function slot() { return $this->belongsTo(DeliverySlot::class, 'delivery_slot_id'); }
}
