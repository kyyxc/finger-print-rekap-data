<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Grade extends Model
{
    protected $fillable = ['name', 'phone_no'];

    /**
     * Get the users (students) that belong to this grade/class.
     */
    public function users()
    {
        return $this->hasMany(User::class, 'grade_id', 'id');
    }

    /**
     * Get the count of users in this grade.
     */
    public function getUsersCountAttribute()
    {
        return $this->users()->count();
    }
}
