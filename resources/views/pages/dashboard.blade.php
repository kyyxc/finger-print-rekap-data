@extends('layouts.main')

@section('title', 'Dashboard Presensi')

@section('content')
    <div class="min-h-screen bg-gray-100 p-6">
        <div class="max-w-6xl mx-auto bg-white shadow-xl rounded-2xl p-6">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                <h1 class="text-2xl font-bold text-gray-800">Dashboard Presensi Siswa</h1>
                @auth('admin')
                <div class="flex gap-4">
                    <a href="{{ route('admins.dashboard') }}"
                        class="flex-shrink-0 bg-red-500 text-white px-5 py-2 rounded-lg font-semibold hover:bg-red-600 transition-colors">
                        Kembali ke Dashboard Admin
                    </a>
                    <a href="{{ route('admins.attendances.export', request()->query()) }}"
                        class="flex-shrink-0 bg-green-600 text-white px-5 py-2 rounded-lg font-semibold hover:bg-green-700 transition-colors">
                        Export Absensi
                    </a>
                </div>
                @endauth
            </div>

            <form method="GET" action="{{ route('dashboard') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                <input type="text" name="search" placeholder="Cari nama atau NIS" value="{{ request('search') }}"
                    class="p-2 border border-gray-300 rounded-xl">

                <input type="date" name="tanggal" value="{{ request('tanggal', now()->toDateString()) }}"
                    class="p-2 border border-gray-300 rounded-xl">

                <select name="status" class="p-2 border border-gray-300 rounded-xl">
                    <option value="">Semua Status</option>
                    <option value="masuk" {{ request('status') === 'masuk' ? 'selected' : '' }}>Masuk</option>
                    <option value="telat" {{ request('status') === 'telat' ? 'selected' : '' }}>Telat</option>
                    <option value="pulang" {{ request('status') === 'pulang' ? 'selected' : '' }}>Pulang</option>
                </select>

                <label class="inline-flex items-center space-x-2">
                    <input type="checkbox" name="show_incomplete" value="1" {{ request('show_incomplete') ? 'checked' : '' }}>
                    <span class="text-sm">Tampilkan Data Belum Lengkap</span>
                </label>

                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-xl hover:bg-blue-600">
                    Filter
                </button>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-300 rounded-xl">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="px-4 py-2 text-left">Photo</th>
                            <th class="px-4 py-2 text-left">NIS</th>
                            <th class="px-4 py-2 text-left">Nama</th>
                            <th class="px-4 py-2 text-left">Kelas</th>
                            <th class="px-4 py-2 text-left">Waktu</th>
                            <th class="px-4 py-2 text-left">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($query as $attendance)
                            @php
                                $user = $attendance->user;
                                $showIncomplete = request('show_incomplete');
                                $isComplete = $user && $user->nama && $user->kelas;
                            @endphp

                            @if ($showIncomplete || $isComplete)
                                <tr class="border-t">
                                    <td class="px-4 py-2">
                                        @if ($user?->photo)
                                            <a href="{{ asset('storage/' . $user->photo) }}" target="_blank">
                                                <img src="{{ asset('storage/' . $user->photo) }}" alt="Foto"
                                                    class="h-12 w-12 rounded-full object-cover">
                                            </a>
                                        @else
                                            <a href="{{ asset('default/default.jpg') }}" target="_blank">
                                                <img src="{{ asset('default/default.jpg') }}" alt="Foto"
                                                    class="h-12 w-12 rounded-full object-cover">
                                            </a>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2">{{ $user->nis ?? '-' }}</td>
                                    <td class="px-4 py-2">{{ $user->nama ?? '-' }}</td>
                                    <td class="px-4 py-2">{{ $user->kelas ?? '-' }}</td>
                                    <td class="px-4 py-2">{{ $attendance->record_time->format('H:i d-m-Y') }}</td>
                                    <td class="px-4 py-2">
                                        @php
                                            $badgeColor = match ($attendance->status) {
                                                'masuk' => 'bg-green-100 text-green-700',
                                                'telat' => 'bg-yellow-100 text-yellow-700',
                                                'pulang' => 'bg-blue-100 text-blue-700',
                                                default => 'bg-gray-100 text-gray-700',
                                            };
                                        @endphp
                                        <span class="px-3 py-1 rounded-full text-sm font-semibold {{ $badgeColor }}">
                                            {{ ucfirst($attendance->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endif
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-gray-500">Tidak ada data presensi</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-6">
                {{ $query->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
@endsection
