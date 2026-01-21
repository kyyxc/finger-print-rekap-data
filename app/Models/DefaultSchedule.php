<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DefaultSchedule extends Model
{
    protected $fillable = [
        'day_of_week',
        'day_name',
        'jam_datang',
        'jam_pulang',
        'is_holiday',
    ];

    protected $casts = [
        'is_holiday' => 'boolean',
    ];

    /**
     * Get default schedule for a specific day of week
     * @param int $dayOfWeek 0=Minggu, 1=Senin, ..., 6=Sabtu
     */
    public static function getByDayOfWeek($dayOfWeek)
    {
        return self::where('day_of_week', $dayOfWeek)->first();
    }

    /**
     * Get all default schedules
     */
    public static function getAllDefaults()
    {
        return self::orderBy('day_of_week')->get()->keyBy('day_of_week');
    }

    /**
     * Check if a day is default holiday
     */
    public static function isDefaultHoliday($dayOfWeek): bool
    {
        $default = self::where('day_of_week', $dayOfWeek)->first();
        return $default ? $default->is_holiday : false;
    }

    /**
     * Get default working hours for a specific day
     */
    public static function getDefaultHours($dayOfWeek)
    {
        $default = self::where('day_of_week', $dayOfWeek)->first();

        if ($default && !$default->is_holiday) {
            return [
                'jam_datang' => $default->jam_datang,
                'jam_pulang' => $default->jam_pulang,
            ];
        }

        return null;
    }
}
