<?php

namespace App\Exports;

use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AttendancesExport implements FromQuery, WithHeadings, WithMapping
{
    // /**
    // * @return \Illuminate\Support\Collection
    // */
    // public function collection()
    // {
    //     return Attendance::all();
    // }

    protected $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function query()
    {
        $dateType = $this->request->input('date_type', 'single');
        $statuses = $this->request->input('status', []); // Default ke array kosong

        $query = Attendance::query()
            ->with('user')
            ->whereHas('user', fn($q) => $q->whereNull('deleted_at'));

        if ($dateType === 'range') {
            $startDate = $this->request->input('tanggal_mulai');
            $endDate = $this->request->input('tanggal_akhir');

            if ($startDate && $endDate) {
                $realStartDate = min($startDate, $endDate);
                $realEndDate = max($startDate, $endDate);

                $query->whereBetween('record_time', [
                    Carbon::parse($realStartDate)->startOfDay(),
                    Carbon::parse($realEndDate)->endOfDay()
                ]);
            }
        } else {
            $singleDate = $this->request->input('tanggal_tunggal', now()->toDateString());
            $query->whereDate('record_time', $singleDate);
        }

        if (!empty($statuses)) {
            $query->whereIn('status', $statuses);
        }

        return $query->orderBy('record_time', 'desc');
    }

    /**
     * Mendefinisikan baris header untuk file Excel.
     */
    public function headings(): array
    {
        return [
            'NIS',
            'Nama',
            'Kelas',
            'Waktu Absen',
            'Status',
        ];
    }

    /**
     * Memetakan setiap baris data menjadi format array yang diinginkan.
     * @param Attendance $attendance
     */
    public function map($attendance): array
    {
        return [
            $attendance->user->nis ?? '-',
            $attendance->user->nama ?? '-',
            $attendance->user->kelas ?? '-',
            $attendance->record_time->format('d-m-Y H:i:s'),
            ucfirst($attendance->status),
        ];
    }
}
