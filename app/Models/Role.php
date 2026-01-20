<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Role extends Authenticatable
{
    protected $fillable = [
        'username',
        'password',
        'role'
    ];

    protected $hidden = [
        'password',
    ];
}
