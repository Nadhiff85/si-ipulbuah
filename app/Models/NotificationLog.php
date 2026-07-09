<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    protected $fillable = ['user_id', 'channel', 'template_type', 'message', 'status', 'is_broadcast', 'sent_at'];

    public function user() { return $this->belongsTo(User::class); }
}
