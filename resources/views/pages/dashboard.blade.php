@extends('layouts.main')

@section('title', 'Portal Presensi Siswa')

@push('styles')
    {{-- Aset CSS untuk DataTables dan Swiper (Carousel) --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://unpkg.com/swiper/swiper-bundle.min.css" />
    <style>
        /* Kustomisasi kecil untuk DataTables agar cocok dengan Tailwind */
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
    </style>
@endpush

@section('content')
    <div class="bg-gray-50 text-gray-800">

        {{-- Bagian Header & Carousel --}}
        <header class="relative bg-white shadow-md">
            <div class="container mx-auto px-6 py-4">
                <h1 class="text-3xl font-bold text-blue-600">Portal Presensi Siswa</h1>
                <p class="text-gray-500">Memantau kehadiran secara transparan dan real-time.</p>
            </div>
        </header>

        {{-- Carousel Section --}}
        <div class="container mx-auto my-8 px-6">
            <div class="swiper-container h-64 md:h-80 rounded-2xl shadow-lg">
                <div class="swiper-wrapper">
                    <div class="swiper-slide bg-cover bg-center" style="background-image: url('https://placehold.co/1200x400/3498db/ffffff?text=Selamat+Datang');"></div>
                    <div class="swiper-slide bg-cover bg-center" style="background-image: url('https://placehold.co/1200x400/2ecc71/ffffff?text=Disiplin+Adalah+Kunci');"></div>
                    <div class="swiper-slide bg-cover bg-center" style="background-image: url('https://placehold.co/1200x400/e74c3c/ffffff?text=Cek+Kehadiranmu+Di+Sini');"></div>
                </div>
                <div class="swiper-pagination"></div>
                <div class="swiper-button-next text-white"></div>
                <div class="swiper-button-prev text-white"></div>
            </div>
        </div>

        {{-- Bagian Utama (Tabel Data) --}}
        <main class="container mx-auto px-6 pb-12">
            <div class="bg-white shadow-xl rounded-2xl p-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                    <h2 class="text-2xl font-bold text-gray-800">Data Kehadiran Hari Ini</h2>
                    @auth('admin')
                        <div class="flex gap-4 flex-wrap">
                            <a href="{{ route('admins.dashboard') }}"
                                class="flex-shrink-0 bg-gray-700 text-white px-5 py-2 rounded-lg font-semibold hover:bg-gray-800 transition-colors">
                                Dashboard Admin
                            </a>
                        </div>
                    @endauth
                </div>
                
                {{-- Form Filter tidak lagi diperlukan karena DataTables punya fitur search sendiri --}}
                {{-- Anda bisa menyimpannya jika ingin filter tanggal atau status yang lebih spesifik --}}

                <div class="overflow-x-auto">
                    {{-- Tambahkan ID ke tabel untuk target JavaScript --}}
                    <table id="attendanceTable" class="display" style="width:100%">
                        <thead>
                            <tr>
                                <th>Photo</th>
                                <th>NIS</th>
                                <th>Nama</th>
                                <th>Kelas</th>
                                <th>Waktu</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($query as $attendance)
                                @php
                                    $user = $attendance->user;
                                    $showIncomplete = request('show_incomplete');
                                    $isComplete = $user && $user->nama && $user->kelas;
                                @endphp

                                @if ($showIncomplete || $isComplete)
                                    <tr>
                                        <td>
                                            <a href="{{ $user->photo_path ?? asset('default/default.jpg') }}" target="_blank">
                                                <img src="{{ $user->photo_path ?? asset('default/default.jpg') }}" alt="Foto"
                                                    class="h-12 w-12 rounded-full object-cover mx-auto">
                                            </a>
                                        </td>
                                        <td>{{ $user->nis ?? '-' }}</td>
                                        <td>{{ $user->nama ?? '-' }}</td>
                                        <td>{{ $user->kelas ?? '-' }}</td>
                                        <td>{{ $attendance->record_time->format('H:i, d-m-Y') }}</td>
                                        <td>
                                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $attendance->badge_color }}">
                                                {{ ucfirst($attendance->status) }}
                                            </span>
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        {{-- Footer --}}
        <footer class="bg-white mt-12 py-8 border-t">
            <div class="container mx-auto text-center text-gray-500">
                <p>&copy; {{ date('Y') }} Portal Presensi Siswa. All rights reserved.</p>
                <p class="text-sm">Dibuat dengan ❤️ dan semangat untuk pendidikan.</p>
            </div>
        </footer>
    </div>
@endsection

@push('scripts')
    {{-- Aset JavaScript untuk jQuery, DataTables, dan Swiper --}}
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inisialisasi DataTables
            $('#attendanceTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json" // Bahasa Indonesia
                },
                "pageLength": 10,
                "responsive": true
            });

            // Inisialisasi Swiper Carousel
            var swiper = new Swiper('.swiper-container', {
                loop: true,
                autoplay: {
                    delay: 4000,
                    disableOnInteraction: false,
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
            });
        });
    </script>
@endpush