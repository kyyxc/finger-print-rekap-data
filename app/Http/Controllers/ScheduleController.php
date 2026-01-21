<?php

namespace App\Http\Controllers;

use App\Models\DefaultSchedule;
use App\Models\Schedule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ScheduleController extends Controller
{
    /**
     * Display calendar page with schedules
     */
    public function index(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $schedules = Schedule::getSchedulesForMonth($year, $month);
        $defaultSchedules = DefaultSchedule::getAllDefaults();

        return view('pages.admins.schedules', compact('year', 'month', 'schedules', 'defaultSchedules'));
    }

    /**
     * Get schedules for a specific month (AJAX)
     */
    public function getSchedules(Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $schedules = Schedule::getSchedulesForMonth($year, $month);
        $defaultSchedules = DefaultSchedule::getAllDefaults();

        return response()->json([
            'success' => true,
            'schedules' => $schedules,
            'defaultSchedules' => $defaultSchedules
        ]);
    }

    /**
     * Toggle schedule (set as holiday or remove)
     */
    public function toggle(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'date' => 'required|date',
            'type' => 'required|in:libur,aktif',
            'description' => 'nullable|string|max:255',
            'jam_datang' => 'nullable|date_format:H:i',
            'jam_pulang' => 'nullable|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $date = $request->date;
        $type = $request->type;
        $description = $request->description;
        $jamDatang = $request->jam_datang;
        $jamPulang = $request->jam_pulang;

        // Find or create schedule for this date
        $schedule = Schedule::updateOrCreate(
            ['date' => $date],
            [
                'type' => $type,
                'description' => $description,
                'jam_datang' => $type === 'aktif' ? $jamDatang : null,
                'jam_pulang' => $type === 'aktif' ? $jamPulang : null,
            ]
        );

        // If type is 'aktif' and no description and no custom time, remove the record
        if ($type === 'aktif' && empty($description) && empty($jamDatang) && empty($jamPulang)) {
            $schedule->delete();
            return response()->json([
                'success' => true,
                'message' => 'Jadwal berhasil dihapus',
                'deleted' => true
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $type === 'libur' ? 'Tanggal berhasil ditandai sebagai libur' : 'Jadwal berhasil disimpan',
            'schedule' => $schedule
        ]);
    }

    /**
     * Delete a schedule
     */
    public function destroy($id)
    {
        $schedule = Schedule::find($id);

        if (!$schedule) {
            return response()->json([
                'success' => false,
                'message' => 'Jadwal tidak ditemukan'
            ], 404);
        }

        $schedule->delete();

        return response()->json([
            'success' => true,
            'message' => 'Jadwal berhasil dihapus'
        ]);
    }

    /**
     * Get all holidays
     */
    public function holidays(Request $request)
    {
        $year = $request->get('year', now()->year);

        $holidays = Schedule::whereYear('date', $year)
            ->where('type', 'libur')
            ->orderBy('date')
            ->get();

        return response()->json([
            'success' => true,
            'holidays' => $holidays
        ]);
    }

    /**
     * Get default schedules
     */
    public function getDefaults()
    {
        $defaults = DefaultSchedule::getAllDefaults();

        return response()->json([
            'success' => true,
            'defaults' => $defaults
        ]);
    }

    /**
     * Update default schedule for a day
     */
    public function updateDefault(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'day_of_week' => 'required|integer|min:0|max:6',
            'jam_datang' => 'nullable|date_format:H:i',
            'jam_pulang' => 'nullable|date_format:H:i',
            'is_holiday' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors()
            ], 422);
        }

        $default = DefaultSchedule::where('day_of_week', $request->day_of_week)->first();

        if (!$default) {
            return response()->json([
                'success' => false,
                'message' => 'Data tidak ditemukan'
            ], 404);
        }

        $default->update([
            'jam_datang' => $request->is_holiday ? null : $request->jam_datang,
            'jam_pulang' => $request->is_holiday ? null : $request->jam_pulang,
            'is_holiday' => $request->is_holiday,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Jam default berhasil diperbarui',
            'default' => $default
        ]);
    }
}
