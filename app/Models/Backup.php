<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Backup extends Model
{
    protected $fillable = ['file_path', 'type', 'created_by', 'file_size'];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
