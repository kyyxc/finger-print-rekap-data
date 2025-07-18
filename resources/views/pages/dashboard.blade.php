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

        /* PERUBAHAN: Wrapper untuk Carousel agar tombol bisa di posisi absolut */
        .swiper-container-wrapper {
            position: relative;
        }

        /* PERUBAHAN: Posisi tombol navigasi carousel di kanan-kiri gambar */
        .swiper-container-wrapper .swiper-button-next,
        .swiper-container-wrapper .swiper-button-prev {
            color: white;
            top: 50%;
            transform: translateY(-50%);
            width: 44px;
            height: 44px;
            background-color: rgba(0, 0, 0, 0.3); /* Latar belakang agar terlihat jelas */
            border-radius: 50%;
        }
        .swiper-container-wrapper .swiper-button-next:after,
        .swiper-container-wrapper .swiper-button-prev:after {
            font-size: 1.25rem; /* Ukuran ikon panah */
        }
        .swiper-container-wrapper .swiper-button-next {
            right: 16px;
        }
        .swiper-container-wrapper .swiper-button-prev {
            left: 16px;
        }
        .swiper-container img {
            object-fit: cover;
            width: 100%;
            height: 100%;
            border-radius: 1rem;
        }

        .modal-overlay {
            transition: opacity 0.3s ease-in-out;
        }
        .modal-content {
            transition: transform 0.3s ease-in-out;
        }
    </style>
@endpush

@section('content')
    <div class="bg-gray-50 text-gray-800">
        <header class="bg-red-500 shadow-md flex px-56 py-4 items-center gap-4">
            <img src="{{ asset('static/img/smkn2.png') }}" alt="Logo SMKN2" class="h-24">
            <div class="container">
                <h1 class="text-3xl font-bold text-slate-100">Portal Presensi Siswa RPL</h1>
                <p class="text-slate-100">Pantau kehadiran secara transparan dan real-time.</p>
            </div>
        </header>

        <div class="container mx-auto my-8 px-6">
            <div class="swiper-container-wrapper">
                <div class="swiper-container h-64 md:h-80 rounded-2xl shadow-lg overflow-hidden">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide"><img src="{{ asset('static/img/1.jpg') }}" alt="Slide 1"></div>
                        <div class="swiper-slide"><img src="{{ asset('static/img/2.jpg') }}" alt="Slide 2"></div>
                        <div class="swiper-slide"><img src="{{ asset('static/img/3.jpg') }}" alt="Slide 3"></div>
                        <div class="swiper-slide"><img src="{{ asset('static/img/4.jpeg') }}" alt="Slide 4"></div>
                        <div class="swiper-slide"><img src="{{ asset('static/img/5.jpeg') }}" alt="Slide 5"></div>
                    </div>
                    <div class="swiper-pagination"></div>
                </div>
                <div class="swiper-button-next"></div>
                <div class="swiper-button-prev"></div>
            </div>
        </div>


        <main class="container mx-auto px-6 pb-12">
            <div class="bg-white shadow-xl rounded-2xl p-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-gray-800">Data Kehadiran Siswa</h2>
                        <p class="text-gray-500 mt-1">Data terbaru dari sistem presensi.</p>
                    </div>
                    <div class="flex gap-4">
                        <button id="openFilterModal"
                            class="bg-red-600 text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-red-700 transition flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z" clip-rule="evenodd" />
                            </svg>
                            Filter Data
                        </button>
                        @auth('admin')
                            <a href="{{ route('admins.dashboard') }}" class="bg-blue-600 text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-blue-700 transition flex items-center gap-2"">Back to Dashboard</a>
                            <a href="{{ route('admins.attendances.export') }}?{{ request()->getQueryString() }}"
                                class="bg-green-600 text-white px-5 py-2.5 rounded-lg font-semibold hover:bg-green-700 transition flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Ekspor Data
                            </a>
                        @endauth
                    </div>
                </div>

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
                            @foreach ($query as $attendance)
                                <tr>
                                    <td>
                                        <a href="{{ $attendance->user->photo_path ?? asset('default/default.jpg') }}" target="_blank">
                                            <img src="{{ $attendance->user->photo_path ?? asset('default/default.jpg') }}" class="h-12 w-12 rounded-full object-cover mx-auto" alt="Foto">
                                        </a>
                                    </td>
                                    <td>{{ $attendance->user->nis ?? '-' }}</td>
                                    <td>{{ $attendance->user->nama ?? '-' }}</td>
                                    <td>{{ $attendance->user->kelas ?? '-' }}</td>
                                    <td>{{ $attendance->record_time->format('H:i, d-m-Y') }}</td>
                                    <td>
                                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $attendance->badge_color }}">
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

        <div id="filterModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 modal-overlay opacity-0 pointer-events-none">
            <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl p-8 m-4 transform scale-95 modal-content">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-bold text-gray-800">Filter Opsi Lanjutan</h3>
                    <button id="closeFilterModal" class="text-gray-500 hover:text-gray-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="GET" action="{{ route('dashboard') }}">
                    <fieldset class="mb-6">
                        <legend class="text-lg font-semibold text-gray-700 mb-3">Filter Berdasarkan Tanggal</legend>
                        <div class="flex flex-col sm:flex-row gap-4 sm:gap-8">
                            <div class="flex items-center">
                                <input type="radio" id="date_type_single" name="date_type" value="single" class="h-4 w-4 text-red-600 border-gray-300 focus:ring-red-500" checked>
                                <label for="date_type_single" class="ml-3 block text-sm font-medium text-gray-700">Tanggal Tertentu</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="date_type_range" name="date_type" value="range" class="h-4 w-4 text-red-600 border-gray-300 focus:ring-red-500">
                                <label for="date_type_range" class="ml-3 block text-sm font-medium text-gray-700">Rentang Tanggal</label>
                            </div>
                        </div>
                    </fieldset>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div id="single_date_filter">
                            <label for="tanggal_tunggal" class="block text-sm font-medium text-gray-700 mb-1">Pilih Tanggal</label>
                            <input type="date" name="tanggal_tunggal" id="tanggal_tunggal" value="{{ request('tanggal_tunggal', now()->toDateString()) }}" class="w-full px-4 py-2 rounded-lg border shadow-sm">
                        </div>

                        <div id="range_date_filter" class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4 hidden">
                            <div>
                                <label for="tanggal_mulai" class="block text-sm font-medium text-gray-700 mb-1">Dari Tanggal</label>
                                <input type="date" name="tanggal_mulai" id="tanggal_mulai" value="{{ request('tanggal_mulai') }}" class="w-full px-4 py-2 rounded-lg border shadow-sm" disabled>
                            </div>
                            <div>
                                <label for="tanggal_akhir" class="block text-sm font-medium text-gray-700 mb-1">Sampai Tanggal</label>
                                <input type="date" name="tanggal_akhir" id="tanggal_akhir" value="{{ request('tanggal_akhir') }}" class="w-full px-4 py-2 rounded-lg border shadow-sm" disabled>
                            </div>
                        </div>
                    </div>

                    <fieldset class="mb-8">
                        <legend class="text-lg font-semibold text-gray-700 mb-3">Filter Berdasarkan Status</legend>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                            <div class="flex items-center">
                                <input id="status_masuk" name="status[]" type="checkbox" value="masuk" @if(in_array('masuk', request('status', []))) checked @endif class="h-4 w-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                                <label for="status_masuk" class="ml-3 text-sm text-gray-600">Masuk</label>
                            </div>
                            <div class="flex items-center">
                                <input id="status_telat" name="status[]" type="checkbox" value="telat" @if(in_array('telat', request('status', []))) checked @endif class="h-4 w-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                                <label for="status_telat" class="ml-3 text-sm text-gray-600">Telat</label>
                            </div>
                            <div class="flex items-center">
                                <input id="status_pulang" name="status[]" type="checkbox" value="pulang" @if(in_array('pulang', request('status', []))) checked @endif class="h-4 w-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                                <label for="status_pulang" class="ml-3 text-sm text-gray-600">Pulang</tabel>
                            </div>
                        </div>
                    </fieldset>

                    <div class="flex justify-end gap-4">
                        <a href="{{ route('dashboard') }}" class="px-6 py-2.5 rounded-lg border text-gray-600 hover:bg-gray-100 transition">Reset Filter</a>
                        <button type="submit" class="bg-red-600 text-white px-6 py-2.5 rounded-lg font-semibold hover:bg-red-700 transition">
                            Terapkan Filter
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <footer class="bg-red-500 text-slate-100 mt-12 py-12 border-t">
            <div class="container mx-auto text-center flex flex-col items-center gap-4">
                <div class="flex justify-center items-center gap-4">
                    <img src="{{ asset('static/img/smkn2.png') }}" alt="Logo Instansi" class="h-12">
                    <p class="text-xl font-bold ">SMKN 2 Sukabumi</p>
                </div>
                <p class="mt-4">&copy; {{ date('Y') }} Portal Presensi Siswa. All rights reserved.</p>
            </div>
        </footer>
    </div>
@endsection

@push('scripts')
    {{-- Dependensi JS tetap sama --}}
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://unpkg.com/swiper/swiper-bundle.min.js"></script>
    <script>
        $(document).ready(function () {
            // Inisialisasi DataTable
            $('#attendanceTable').DataTable({
                "language": { "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json" },
                "pageLength": 10
            });

            // Inisialisasi Swiper
            new Swiper('.swiper-container', {
                loop: true,
                autoplay: { delay: 4000, disableOnInteraction: false },
                pagination: { el: '.swiper-pagination', clickable: true },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                }
            });

            // BARU: Logika untuk Modal Filter
            const modal = $('#filterModal');
            const openModalBtn = $('#openFilterModal');
            const closeModalBtn = $('#closeFilterModal');

            function showModal() {
                modal.removeClass('opacity-0 pointer-events-none');
                modal.find('.modal-content').removeClass('scale-95');
            }

            function hideModal() {
                modal.addClass('opacity-0 pointer-events-none');
                modal.find('.modal-content').addClass('scale-95');
            }

            openModalBtn.on('click', showModal);
            closeModalBtn.on('click', hideModal);
            // Klik di luar modal untuk menutup
            modal.on('click', function(event) {
                if ($(event.target).is(modal)) {
                    hideModal();
                }
            });

            // BARU: Logika untuk mengganti jenis filter tanggal di dalam modal
            const singleDateFilter = $('#single_date_filter');
            const rangeDateFilter = $('#range_date_filter');
            const singleDateInput = $('#tanggal_tunggal');
            const rangeDateInputs = $('#tanggal_mulai, #tanggal_akhir');

            $('input[name="date_type"]').on('change', function() {
                if (this.value === 'single') {
                    singleDateFilter.removeClass('hidden');
                    rangeDateFilter.addClass('hidden');
                    singleDateInput.prop('disabled', false);
                    rangeDateInputs.prop('disabled', true);
                } else {
                    singleDateFilter.addClass('hidden');
                    rangeDateFilter.removeClass('hidden');
                    singleDateInput.prop('disabled', true);
                    rangeDateInputs.prop('disabled', false);
                }
            }).trigger('change'); // Trigger saat halaman dimuat untuk set state awal
        });
    </script>
@endpush
