<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'record_time',
        'status',
        'user_id',
        'is_send',
    ];

    protected $casts = [
        'record_time' => 'datetime',
        'is_send' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
