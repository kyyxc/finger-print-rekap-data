<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Schedule extends Model
{
    protected $fillable = [
        'date',
        'type',
        'jam_datang',
        'jam_pulang',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Check if a date is a holiday
     */
    public static function isHoliday($date): bool
    {
        $carbonDate = Carbon::parse($date);
        $dayOfWeek = $carbonDate->dayOfWeek;

        // Check specific date schedule first
        $schedule = self::where('date', $date)->first();
        if ($schedule) {
            return $schedule->type === 'libur';
        }

        // Check default schedule
        return DefaultSchedule::isDefaultHoliday($dayOfWeek);
    }

    /**
     * Get all holidays for a specific month and year
     */
    public static function getHolidaysForMonth($year, $month): array
    {
        return self::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->where('type', 'libur')
            ->pluck('date')
            ->map(fn($date) => $date->format('Y-m-d'))
            ->toArray();
    }

    /**
     * Get schedules for a specific month and year
     */
    public static function getSchedulesForMonth($year, $month)
    {
        return self::whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->keyBy(fn($schedule) => $schedule->date->format('Y-m-d'));
    }

    /**
     * Get working hours for a specific date
     */
    public static function getWorkingHours($date)
    {
        $carbonDate = Carbon::parse($date);
        $dayOfWeek = $carbonDate->dayOfWeek;

        // Check specific date schedule first
        $schedule = self::where('date', $date)->first();
        if ($schedule && $schedule->type === 'aktif') {
            if ($schedule->jam_datang || $schedule->jam_pulang) {
                return [
                    'jam_datang' => $schedule->jam_datang,
                    'jam_pulang' => $schedule->jam_pulang,
                ];
            }
        }

        // Return default hours for the day of week
        return DefaultSchedule::getDefaultHours($dayOfWeek);
    }
}
