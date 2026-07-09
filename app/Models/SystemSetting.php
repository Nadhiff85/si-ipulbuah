<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SystemSetting extends Model
{
    protected $fillable = [
        'session_timeout_minutes', 'max_login_attempts', 'rate_limit_per_minute',
        'maintenance_mode', 'max_freshness_days_default', 'delivery_globally_enabled',
    ];
}
