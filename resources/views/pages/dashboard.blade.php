@extends('layouts.main')

@section('title', 'Portal Presensi Siswa')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
    <style>
        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            background-color: #fff;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.5em 1em;
        }

        .swiper-container img {
            object-fit: cover;
            width: 100%;
            height: 100%;
            border-radius: 1rem;
        }
    </style>
@endpush

@section('content')
    <div class="bg-gray-50 text-gray-800">
        {{-- HEADER --}}
        <header class="bg-white shadow-md">
            <div class="container mx-auto px-6 py-4">
                <h1 class="text-3xl font-bold text-blue-600">Portal Presensi Siswa RPL</h1>
                <p class="text-gray-500">Pantau kehadiran secara transparan dan real-time.</p>
            </div>
        </header>

        {{-- CAROUSEL --}}
        <div class="container mx-auto my-6 px-6">
            <div class="swiper-container h-64 md:h-80 rounded-2xl shadow-lg overflow-hidden">
                <div class="swiper-wrapper">
                    <div class="swiper-slide"><img src="{{ asset(path: 'static/img/1.jpg') }}" alt="Slide 1"></div>
                    <div class="swiper-slide"><img src="{{ asset('static/img/2.jpg') }}" alt="Slide 2"></div>
                    <div class="swiper-slide"><img src="{{ asset('static/img/3.jpg') }}" alt="Slide 3"></div>
                </div>
                <div class="swiper-pagination"></div>
                <div class="swiper-button-next text-white"></div>
                <div class="swiper-button-prev text-white"></div>
            </div>
        </div>

        {{-- TABEL UTAMA --}}
        <main class="container mx-auto px-6 pb-12">
            <div class="bg-white shadow-xl rounded-2xl p-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 gap-4">
                    {{-- ... Judul dan tombol admin tetap sama ... --}}
                </div>

                {{-- HAPUS BAGIAN TAB FILTER DI SINI --}}

                {{-- FORM FILTER YANG DIPERBARUI --}}
                <form method="GET" action="{{ route('dashboard') }}"
                    class="mb-6 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 items-end">

                    {{-- Filter Tanggal --}}
                    <div>
                        <label for="tanggal" class="block text-sm font-medium text-gray-700 mb-1">Tanggal</label>
                        <input type="date" name="tanggal" id="tanggal" value="{{ $tanggal }}"
                            class="w-full px-4 py-2 rounded-lg border shadow-sm">
                    </div>

                    {{-- Filter Status Absen --}}
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" id="status" class="w-full px-4 py-2 rounded-lg border shadow-sm">
                            <option value="">Semua</option>
                            <option value="masuk" @selected(request('status') === 'masuk')>Masuk</option>
                            <option value="telat" @selected(request('status') === 'telat')>Telat</option>
                            <option value="pulang" @selected(request('status') === 'pulang')>Pulang</option>
                        </select>
                    </div>

                    {{-- Filter Kelengkapan Data (BARU) --}}
                    <div>
                        <label for="completeness" class="block text-sm font-medium text-gray-700 mb-1">Kelengkapan
                            Data</label>
                        <select name="completeness" id="completeness" class="w-full px-4 py-2 rounded-lg border shadow-sm">
                            <option value="complete" @selected($completeness === 'complete')>Hanya Data Lengkap</option>
                            <option value="all" @selected($completeness === 'all')>Tampilkan Semua</option>
                            <option value="incomplete" @selected($completeness === 'incomplete')>Hanya Tidak Lengkap</option>
                        </select>
                    </div>

                    {{-- Tombol Filter --}}
                    <div>
                        <button type="submit"
                            class="w-full bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold hover:bg-blue-700 transition">
                            Terapkan Filter
                        </button>
                    </div>
                </form>

                {{-- TABEL YANG JAUH LEBIH BERSIH --}}
                <div class="overflow-x-auto">
                    <table id="attendanceTable" class="display w-full">
                        <thead>
                            <tr>
                                <th>Foto</th>
                                <th>NIS</th>
                                <th>Nama</th>
                                <th>Kelas</th>
                                <th>Waktu</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- TIDAK PERLU LAGI BLOK @php DAN @if DI SINI --}}
                            @foreach ($query as $attendance)
                                <tr>
                                    <td>
                                        <a href="{{ $attendance->user->photo_path ?? asset('default/default.jpg') }}"
                                            target="_blank">
                                            <img src="{{ $attendance->user->photo_path ?? asset('default/default.jpg') }}"
                                                class="h-12 w-12 rounded-full object-cover mx-auto" alt="Foto">
                                        </a>
                                    </td>
                                    <td>{{ $attendance->user->nis ?? '-' }}</td>
                                    <td>{{ $attendance->user->nama ?? '-' }}</td>
                                    <td>{{ $attendance->user->kelas ?? '-' }}</td>
                                    <td>{{ $attendance->record_time->format('H:i, d-m-Y') }}</td>
                                    <td>
                                        <span
                                            class="px-3 py-1 rounded-full text-xs font-semibold {{ $attendance->badge_color }}">
                                            {{ ucfirst($attendance->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <footer class="bg-white mt-12 py-8 border-t">
            <div class="container mx-auto text-center text-gray-500">
                <p>&copy; {{ date('Y') }} Portal Presensi Siswa. All rights reserved.</p>
                <p class="text-sm">Dibuat dengan ❤️ dan semangat untuk pendidikan.</p>
            </div>
        </footer>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#attendanceTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
                },
                "pageLength": 10
            });

            new Swiper('.swiper-container', {
                loop: true,
                autoplay: { delay: 4000, disableOnInteraction: false },
                pagination: { el: '.swiper-pagination', clickable: true },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                }
            });
        });
    </script>
@endpush
