<?php

namespace App\Exports;

use App\Models\Attendance;
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
        $search = $this->request->input('search');
        $tanggal = $this->request->input('tanggal', now()->toDateString());
        $status = $this->request->input('status');

        return Attendance::query()
            // ✅ <-- TAMBAHKAN BARIS INI
            ->whereHas('user', fn($q) => $q->whereNull('deleted_at'))
            ->with('user')
            ->whereDate('record_time', $tanggal)
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($search, function ($q) use ($search) {
                $q->whereHas('user', function ($q) use ($search) {
                    $q->where('nama', 'like', "%$search%")
                        ->orWhere('nis', 'like', "%$search%");
                });
            })
            ->orderBy('record_time', 'asc');
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
