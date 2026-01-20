<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Role extends Authenticatable
{
    protected $fillable = [
        'username',
        'password',
        'role',
        'grade_id'
    ];

    protected $hidden = [
        'password',
    ];

    /**
     * Get the grade that the sekretaris belongs to
     */
    public function grade(): BelongsTo
    {
        return $this->belongsTo(Grade::class);
    }
}
