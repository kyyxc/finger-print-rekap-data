<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    protected $fillable = [
        'record_time',
        'status',
        'user_id',
    ];

    protected $casts = [
        'record_time' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
