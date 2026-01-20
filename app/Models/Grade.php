<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    protected $fillable = ['name'];

    /**
     * Get the users (students) that belong to this grade/class.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'kelas', 'name');
    }

    /**
     * Get the count of users in this grade.
     */
    public function getUsersCountAttribute()
    {
        return $this->users()->count();
    }
}
