@extends('layouts.main')

@section('title', 'Portal Presensi Siswa')

@push('styles')
    <style>
        /* Modern table animations */
        .table-row-hover {
            transition: all 0.2s ease;
        }

        .table-row-hover:hover {
            transform: translateX(4px);
            box-shadow: -4px 0 0 0 #dc2626;
        }

        .search-input:focus {
            box-shadow: 0 0 0 3px rgba(220, 38, 38, 0.15);
        }

        .glass-effect {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
    </style>
@endpush

@section('content')
    <div class="bg-gray-50 text-gray-800 flex flex-col min-h-screen">
        <header class="bg-gradient-to-r from-red-600 to-red-500 shadow-xl py-3">
            <div class="container mx-auto flex items-center gap-4 px-4 md:px-6">
                <img src="{{ asset('static/img/smkn2.png') }}" alt="Logo SMKN2"
                    class="h-10 md:h-16 drop-shadow-lg select-none">

                <div class="leading-tight">
                    <h1 class="text-lg md:text-2xl font-bold text-white drop-shadow-sm tracking-wide">
                        Portal Presensi Siswa RPL
                    </h1>
                    <p class="text-red-100 text-xs md:text-sm mt-0.5">
                        Pantau kehadiran secara transparan dan real-time.
                    </p>
                </div>
            </div>
        </header>

        <main class="container mx-auto px-4 md:px-6 py-6 flex-1">
            <div class="bg-white shadow-lg rounded-xl p-6 border border-gray-100">


                <!-- Header Section -->
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
                    <div>
                        <h2 class="text-xl md:text-2xl font-semibold text-gray-900 tracking-tight">
                            Data Kehadiran Siswa
                        </h2>
                        <p class="text-gray-500 text-sm mt-1">Data terbaru dari sistem presensi.</p>
                    </div>

                    <!-- Buttons -->
                    <div class="flex flex-wrap gap-2 sm:gap-3">

                        <!-- Filter -->
                        <button id="openFilterModal"
                            class="bg-red-600 text-white px-3 py-2 sm:px-4 sm:py-2.5 rounded-lg text-xs sm:text-sm font-medium hover:bg-red-700 active:scale-[.98] transition flex items-center gap-1.5 sm:gap-2 shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M3 3a1 1 0 011-1h12a1 1 0 011 1v3a1 1 0 01-.293.707L12 11.414V15a1 1 0 01-.293.707l-2 2A1 1 0 018 17v-5.586L3.293 6.707A1 1 0 013 6V3z"
                                    clip-rule="evenodd" />
                            </svg>
                            <span class="hidden sm:inline">Filter Data</span>
                            <span class="sm:hidden">Filter</span>
                        </button>

                        @auth('role')
                            <!-- Back -->
                            <a href="{{ route('admin.dashboard') }}"
                                class="bg-blue-600 text-white px-3 py-2 sm:px-4 sm:py-2.5 rounded-lg text-xs sm:text-sm font-medium hover:bg-blue-700 active:scale-[.98] transition flex items-center gap-1.5 sm:gap-2 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                                </svg>
                                <span class="hidden sm:inline">Dashboard</span>
                            </a>

                            <!-- Export -->
                            <a href="{{ route('admin.attendances.export') }}?{{ request()->getQueryString() }}"
                                class="bg-green-600 text-white px-3 py-2 sm:px-4 sm:py-2.5 rounded-lg text-xs sm:text-sm font-medium hover:bg-green-700 active:scale-[.98] transition flex items-center gap-1.5 sm:gap-2 shadow-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 sm:h-5 sm:w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                <span class="hidden sm:inline">Ekspor</span>
                            </a>
                        @endauth

                    </div>
                </div>

                <!-- Search Bar & Limit Selector -->
                <div class="mb-6 flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
                    <div class="relative w-full sm:max-w-md">
                        <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                fill="currentColor">
                                <path fill-rule="evenodd"
                                    d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                    clip-rule="evenodd" />
                            </svg>
                        </div>
                        <input type="text" id="searchInput" placeholder="Cari berdasarkan NIS, nama, atau kelas..."
                            class="search-input w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:border-red-500 focus:bg-white transition-all duration-200">
                    </div>

                    <!-- Limit Selector -->
                    <div class="flex items-center gap-3">
                        <label for="perPageSelect"
                            class="text-sm text-gray-600 font-medium whitespace-nowrap">Tampilkan:</label>
                        <select id="perPageSelect" onchange="changePerPage(this.value)"
                            class="px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-700 focus:outline-none focus:border-red-500 focus:bg-white transition-all duration-200 cursor-pointer">
                            <option value="10" {{ ($perPage ?? 10) == 10 ? 'selected' : '' }}>10</option>
                            <option value="25" {{ ($perPage ?? 10) == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ ($perPage ?? 10) == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ ($perPage ?? 10) == 100 ? 'selected' : '' }}>100</option>
                        </select>
                        <span class="text-sm text-gray-500">data</span>
                    </div>
                </div>

                <!-- Table -->
                <div class="overflow-hidden rounded-xl sm:rounded-2xl border border-gray-200 shadow-sm">
                    <div class="overflow-x-auto">
                        <table id="attendanceTable" class="w-full">
                            <thead>
                                <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
                                    <th
                                        class="py-3 px-2 sm:py-4 sm:px-6 text-left text-[10px] sm:text-xs font-bold text-gray-600 uppercase tracking-wider">
                                        Foto</th>
                                    <th
                                        class="py-3 px-2 sm:py-4 sm:px-6 text-left text-[10px] sm:text-xs font-bold text-gray-600 uppercase tracking-wider hidden sm:table-cell">
                                        NIS</th>
                                    <th
                                        class="py-3 px-2 sm:py-4 sm:px-6 text-left text-[10px] sm:text-xs font-bold text-gray-600 uppercase tracking-wider">
                                        Nama</th>
                                    <th
                                        class="py-3 px-2 sm:py-4 sm:px-6 text-left text-[10px] sm:text-xs font-bold text-gray-600 uppercase tracking-wider hidden sm:table-cell">
                                        Kelas</th>
                                    <th
                                        class="py-3 px-2 sm:py-4 sm:px-6 text-left text-[10px] sm:text-xs font-bold text-gray-600 uppercase tracking-wider hidden md:table-cell">
                                        Waktu</th>
                                    <th
                                        class="py-3 px-2 sm:py-4 sm:px-6 text-left text-[10px] sm:text-xs font-bold text-gray-600 uppercase tracking-wider">
                                        Status</th>
                                </tr>
                            </thead>

                            <tbody class="divide-y divide-gray-100">
                                @forelse ($query as $attendance)
                                    <tr class="table-row-hover bg-white hover:bg-red-50/50 transition-all duration-200"
                                        data-search="{{ strtolower(($attendance->user->nis ?? '') . ' ' . ($attendance->user->nama ?? '') . ' ' . ($attendance->user->kelas ?? '')) }}">
                                        <td class="py-2 px-2 sm:py-4 sm:px-6">
                                            <a href="{{ $attendance->user->photo_path ?? asset('default/default.jpg') }}"
                                                target="_blank" class="block">
                                                <img src="{{ $attendance->user->photo_path ?? asset('default/default.jpg') }}"
                                                    class="h-8 w-8 sm:h-11 sm:w-11 rounded-lg sm:rounded-xl object-cover ring-2 ring-gray-100 hover:ring-red-400 transition-all duration-200 shadow-sm"
                                                    alt="Foto">
                                            </a>
                                        </td>
                                        <td class="py-2 px-2 sm:py-4 sm:px-6 hidden sm:table-cell">
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 sm:px-3 sm:py-1 rounded-md sm:rounded-lg bg-gray-100 text-gray-700 text-[10px] sm:text-sm font-mono font-medium">
                                                {{ $attendance->user->nis ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="py-2 px-2 sm:py-4 sm:px-6">
                                            <div class="flex flex-col gap-0.5">
                                                <span
                                                    class="text-gray-800 font-medium text-[11px] sm:text-sm line-clamp-1">{{ $attendance->user->nama ?? '-' }}</span>
                                                {{-- Show NIS & kelas on mobile --}}
                                                <span class="text-[9px] text-gray-500 sm:hidden">{{ $attendance->user->nis ?? '-' }} • {{ $attendance->user->kelas ?? '-' }}</span>
                                            </div>
                                        </td>
                                        <td class="py-2 px-2 sm:py-4 sm:px-6 hidden sm:table-cell">
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 sm:px-2.5 sm:py-1 rounded-md sm:rounded-lg bg-blue-50 text-blue-700 text-[10px] sm:text-xs font-semibold">
                                                {{ $attendance->user->kelas ?? '-' }}
                                            </span>
                                        </td>
                                        <td class="py-2 px-2 sm:py-4 sm:px-6 hidden md:table-cell">
                                            <div
                                                class="flex items-center gap-1.5 sm:gap-2 text-gray-600 text-xs sm:text-sm">
                                                <svg class="h-3.5 w-3.5 sm:h-4 sm:w-4 text-gray-400"
                                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                    fill="currentColor">
                                                    <path fill-rule="evenodd"
                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                {{ $attendance->record_time?->format('H:i, d M Y') ?? '-' }}
                                            </div>
                                        </td>
                                        <td class="py-2 px-2 sm:py-4 sm:px-6">
                                            @if (is_null($attendance->status))
                                                <span
                                                    class="inline-flex items-center gap-1 sm:gap-1.5 px-1.5 py-0.5 sm:px-3 sm:py-1.5 rounded-full text-[9px] sm:text-xs font-semibold bg-gray-200 text-gray-700 shadow-sm">
                                                    <span class="w-1 h-1 sm:w-1.5 sm:h-1.5 rounded-full bg-gray-500"></span>
                                                    <span class="hidden sm:inline">Tidak Hadir</span>
                                                    <span class="sm:hidden">Absen</span>
                                                </span>
                                            @else
                                                <span
                                                    class="inline-flex items-center gap-1 sm:gap-1.5 px-1.5 py-0.5 sm:px-3 sm:py-1.5 rounded-full text-[9px] sm:text-xs font-semibold {{ $attendance->badge_color }} shadow-sm">
                                                    <span
                                                        class="w-1 h-1 sm:w-1.5 sm:h-1.5 rounded-full bg-current opacity-70"></span>
                                                    {{ ucfirst($attendance->status) }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="py-12 sm:py-16 px-4 sm:px-6 text-center">
                                            <div class="flex flex-col items-center gap-2 sm:gap-3">
                                                <div
                                                    class="w-12 h-12 sm:w-16 sm:h-16 rounded-full bg-gray-100 flex items-center justify-center">
                                                    <svg class="h-6 w-6 sm:h-8 sm:w-8 text-gray-400"
                                                        xmlns="http://www.w3.org/2000/svg" fill="none"
                                                        viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="1.5"
                                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                                    </svg>
                                                </div>
                                                <p class="text-gray-500 text-xs sm:text-sm font-medium">Tidak ada data
                                                    kehadiran</p>
                                                <p class="text-gray-400 text-[10px] sm:text-xs">Coba ubah filter untuk
                                                    melihat data lain</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Data Count -->
                @if ($query->total() > 0)
                    <div class="mt-3 sm:mt-6 flex flex-col sm:flex-row items-center justify-between gap-2 sm:gap-4">
                        <p class="text-[10px] sm:text-sm text-gray-500 order-2 sm:order-1">
                            <span class="font-semibold text-gray-700">{{ $query->firstItem() }}-{{ $query->lastItem() }}</span> dari
                            <span class="font-semibold text-gray-700">{{ $query->total() }}</span> data
                        </p>

                        <!-- Pagination Links -->
                        @if ($query->hasPages())
                            <nav class="flex items-center gap-0.5 order-1 sm:order-2">
                                {{-- Previous --}}
                                @if ($query->onFirstPage())
                                    <span
                                        class="p-1.5 sm:px-3 sm:py-2 rounded-md sm:rounded-lg text-gray-400 bg-gray-100 cursor-not-allowed">
                                        <svg class="w-3.5 h-3.5 sm:w-5 sm:h-5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 19l-7-7 7-7"></path>
                                        </svg>
                                    </span>
                                @else
                                    <a href="{{ $query->previousPageUrl() }}"
                                        class="p-1.5 sm:px-3 sm:py-2 rounded-md sm:rounded-lg text-gray-600 bg-white border border-gray-200 hover:bg-red-50 hover:border-red-300 hover:text-red-600 transition-all duration-200">
                                        <svg class="w-3.5 h-3.5 sm:w-5 sm:h-5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M15 19l-7-7 7-7"></path>
                                        </svg>
                                    </a>
                                @endif

                                {{-- Page Numbers --}}
                                @php
                                    $start = max($query->currentPage() - 1, 1);
                                    $end = min($query->currentPage() + 1, $query->lastPage());
                                @endphp

                                @if ($start > 1)
                                    <a href="{{ $query->url(1) }}"
                                        class="px-2 py-1 sm:px-3.5 sm:py-2 rounded-md sm:rounded-lg text-[10px] sm:text-sm text-gray-600 bg-white border border-gray-200 hover:bg-red-50 hover:border-red-300 hover:text-red-600 transition-all duration-200">1</a>
                                    @if ($start > 2)
                                        <span
                                            class="px-0.5 sm:px-2 text-gray-400 text-[10px] sm:text-sm">...</span>
                                    @endif
                                @endif

                                @for ($i = $start; $i <= $end; $i++)
                                    @if ($i == $query->currentPage())
                                        <span
                                            class="px-2 py-1 sm:px-3.5 sm:py-2 rounded-md sm:rounded-lg text-[10px] sm:text-sm font-semibold text-white bg-red-600 shadow-sm">{{ $i }}</span>
                                    @else
                                        <a href="{{ $query->url($i) }}"
                                            class="px-2 py-1 sm:px-3.5 sm:py-2 rounded-md sm:rounded-lg text-[10px] sm:text-sm text-gray-600 bg-white border border-gray-200 hover:bg-red-50 hover:border-red-300 hover:text-red-600 transition-all duration-200">{{ $i }}</a>
                                    @endif
                                @endfor

                                @if ($end < $query->lastPage())
                                    @if ($end < $query->lastPage() - 1)
                                        <span
                                            class="px-0.5 sm:px-2 text-gray-400 text-[10px] sm:text-sm">...</span>
                                    @endif
                                    <a href="{{ $query->url($query->lastPage()) }}"
                                        class="px-2 py-1 sm:px-3.5 sm:py-2 rounded-md sm:rounded-lg text-[10px] sm:text-sm text-gray-600 bg-white border border-gray-200 hover:bg-red-50 hover:border-red-300 hover:text-red-600 transition-all duration-200">{{ $query->lastPage() }}</a>
                                @endif

                                {{-- Next --}}
                                @if ($query->hasMorePages())
                                    <a href="{{ $query->nextPageUrl() }}"
                                        class="p-1.5 sm:px-3 sm:py-2 rounded-md sm:rounded-lg text-gray-600 bg-white border border-gray-200 hover:bg-red-50 hover:border-red-300 hover:text-red-600 transition-all duration-200">
                                        <svg class="w-3.5 h-3.5 sm:w-5 sm:h-5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </a>
                                @else
                                    <span
                                        class="p-1.5 sm:px-3 sm:py-2 rounded-md sm:rounded-lg text-gray-400 bg-gray-100 cursor-not-allowed">
                                        <svg class="w-3.5 h-3.5 sm:w-5 sm:h-5" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 5l7 7-7 7"></path>
                                        </svg>
                                    </span>
                                @endif
                            </nav>
                        @endif
                    </div>
                @endif

            </div>
        </main>

        <div id="filterModal"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50 modal-overlay opacity-0 pointer-events-none transition-opacity duration-200">
            <div class="bg-white rounded-xl sm:rounded-2xl shadow-xl w-full max-w-2xl p-4 sm:p-8 m-3 sm:m-4 transform scale-95 modal-content transition-transform duration-200 max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4 sm:mb-6">
                    <h3 class="text-lg sm:text-2xl font-bold text-gray-800">Filter Data</h3>
                    <button id="closeFilterModal" class="text-gray-500 hover:text-gray-800 p-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 sm:h-6 sm:w-6" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form method="GET" action="{{ route('dashboard') }}">
                    <fieldset class="mb-4 sm:mb-6">
                        <legend class="text-sm sm:text-lg font-semibold text-gray-700 mb-2 sm:mb-3">Filter Berdasarkan Tanggal</legend>
                        <div class="flex flex-col sm:flex-row gap-3 sm:gap-8">
                            <div class="flex items-center">
                                <input type="radio" id="date_type_single" name="date_type" value="single"
                                    class="h-4 w-4 text-red-600 border-gray-300 focus:ring-red-500" checked>
                                <label for="date_type_single" class="ml-2 sm:ml-3 block text-xs sm:text-sm font-medium text-gray-700">Tanggal
                                    Tertentu</label>
                            </div>
                            <div class="flex items-center">
                                <input type="radio" id="date_type_range" name="date_type" value="range"
                                    class="h-4 w-4 text-red-600 border-gray-300 focus:ring-red-500">
                                <label for="date_type_range" class="ml-2 sm:ml-3 block text-xs sm:text-sm font-medium text-gray-700">Rentang
                                    Tanggal</label>
                            </div>
                        </div>
                    </fieldset>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 mb-4 sm:mb-6">
                        <div id="single_date_filter">
                            <label for="tanggal_tunggal" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Pilih
                                Tanggal</label>
                            <input type="date" name="tanggal_tunggal" id="tanggal_tunggal"
                                value="{{ request('tanggal_tunggal', now()->toDateString()) }}"
                                class="w-full px-3 sm:px-4 py-2 text-sm rounded-lg border shadow-sm">
                        </div>

                        <div id="range_date_filter" class="md:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-3 sm:gap-4 hidden">
                            <div>
                                <label for="tanggal_mulai" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Dari
                                    Tanggal</label>
                                <input type="date" name="tanggal_mulai" id="tanggal_mulai"
                                    value="{{ request('tanggal_mulai') }}"
                                    class="w-full px-3 sm:px-4 py-2 text-sm rounded-lg border shadow-sm" disabled>
                            </div>
                            <div>
                                <label for="tanggal_akhir" class="block text-xs sm:text-sm font-medium text-gray-700 mb-1">Sampai
                                    Tanggal</label>
                                <input type="date" name="tanggal_akhir" id="tanggal_akhir"
                                    value="{{ request('tanggal_akhir') }}"
                                    class="w-full px-3 sm:px-4 py-2 text-sm rounded-lg border shadow-sm" disabled>
                            </div>
                        </div>
                    </div>

                    <fieldset class="mb-5 sm:mb-8">
                        <legend class="text-sm sm:text-lg font-semibold text-gray-700 mb-2 sm:mb-3">Filter Berdasarkan Status</legend>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 sm:gap-4">
                            <div class="flex items-center">
                                <input id="status_masuk" name="status[]" type="checkbox" value="masuk"
                                    @if (in_array('masuk', request('status', []))) checked @endif
                                    class="h-4 w-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                                <label for="status_masuk" class="ml-2 sm:ml-3 text-xs sm:text-sm text-gray-600">Masuk</label>
                            </div>

                            <div class="flex items-center">
                                <input id="status_telat" name="status[]" type="checkbox" value="telat"
                                    @if (in_array('telat', request('status', []))) checked @endif
                                    class="h-4 w-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                                <label for="status_telat" class="ml-2 sm:ml-3 text-xs sm:text-sm text-gray-600">Telat</label>
                            </div>

                            <div class="flex items-center">
                                <input id="status_pulang" name="status[]" type="checkbox" value="pulang"
                                    @if (in_array('pulang', request('status', []))) checked @endif
                                    class="h-4 w-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                                <label for="status_pulang" class="ml-2 sm:ml-3 text-xs sm:text-sm text-gray-600">Pulang</label>
                            </div>

                            <div class="flex items-center">
                                <input id="status_izin" name="status[]" type="checkbox" value="izin"
                                    @if (in_array('izin', request('status', []))) checked @endif
                                    class="h-4 w-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                                <label for="status_izin" class="ml-2 sm:ml-3 text-xs sm:text-sm text-gray-600">Izin</label>
                            </div>

                            <div class="flex items-center">
                                <input id="status_sakit" name="status[]" type="checkbox" value="sakit"
                                    @if (in_array('sakit', request('status', []))) checked @endif
                                    class="h-4 w-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                                <label for="status_sakit" class="ml-2 sm:ml-3 text-xs sm:text-sm text-gray-600">Sakit</label>
                            </div>

                            <div class="flex items-center">
                                <input id="status_tidak_hadir" name="status[]" type="checkbox" value="tidak_hadir"
                                    @if (in_array('tidak_hadir', request('status', []))) checked @endif
                                    class="h-4 w-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                                <label for="status_tidak_hadir" class="ml-2 sm:ml-3 text-xs sm:text-sm text-gray-600">Tidak Hadir</label>
                            </div>
                        </div>
                    </fieldset>


                    <div class="flex justify-end gap-2 sm:gap-4">
                        <a href="{{ route('dashboard') }}"
                            class="px-4 sm:px-6 py-2 sm:py-2.5 rounded-lg border text-xs sm:text-sm text-gray-600 hover:bg-gray-100 transition">Reset</a>
                        <button type="submit"
                            class="bg-red-600 text-white px-4 sm:px-6 py-2 sm:py-2.5 rounded-lg text-xs sm:text-sm font-semibold hover:bg-red-700 transition">
                            Terapkan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <footer class="bg-gradient-to-r from-red-600 to-red-500 text-white py-4 md:py-6 border-t">
            <div class="container mx-auto text-center flex flex-col items-center gap-2">
                <div class="flex justify-center items-center gap-3">
                    <img src="{{ asset('static/img/smkn2.png') }}" alt="Logo Instansi" class="h-6 md:h-8">
                    <p class="text-lg font-semibold">SMKN 2 Sukabumi</p>
                </div>

                <p class="text-xs md:text-sm">
                    &copy; {{ date('Y') }} Portal Presensi Siswa. All rights reserved.
                </p>
            </div>
        </footer>

    </div>
@endsection

@push('scripts')
    <script>
        // Change per page
        function changePerPage(value) {
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', value);
            url.searchParams.delete('page'); // Reset to first page
            window.location.href = url.toString();
        }

        // Simple search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#attendanceTable tbody tr[data-search]');
            let visibleCount = 0;

            rows.forEach(row => {
                const searchData = row.getAttribute('data-search');
                if (searchData.includes(searchTerm)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Update visible count
            const countEl = document.getElementById('visibleCount');
            if (countEl) countEl.textContent = visibleCount;
        });

        // Modal functionality
        const openBtn = document.getElementById('openFilterModal');
        const closeBtn = document.getElementById('closeFilterModal');
        const modal = document.getElementById('filterModal');

        if (openBtn && modal) {
            openBtn.addEventListener('click', () => {
                modal.classList.remove('opacity-0', 'pointer-events-none');
                modal.querySelector('.modal-content').classList.remove('scale-95');
            });
        }

        if (closeBtn && modal) {
            closeBtn.addEventListener('click', () => {
                modal.classList.add('opacity-0', 'pointer-events-none');
                modal.querySelector('.modal-content').classList.add('scale-95');
            });

            modal.addEventListener('click', (e) => {
                if (e.target === modal) {
                    modal.classList.add('opacity-0', 'pointer-events-none');
                    modal.querySelector('.modal-content').classList.add('scale-95');
                }
            });
        }

        // Date filter toggle
        const dateTypeSingle = document.getElementById('date_type_single');
        const dateTypeRange = document.getElementById('date_type_range');
        const singleDateFilter = document.getElementById('single_date_filter');
        const rangeDateFilter = document.getElementById('range_date_filter');

        if (dateTypeSingle && dateTypeRange) {
            dateTypeSingle.addEventListener('change', () => {
                singleDateFilter.classList.remove('hidden');
                rangeDateFilter.classList.add('hidden');
                document.getElementById('tanggal_tunggal').disabled = false;
                document.getElementById('tanggal_mulai').disabled = true;
                document.getElementById('tanggal_akhir').disabled = true;
            });

            dateTypeRange.addEventListener('change', () => {
                singleDateFilter.classList.add('hidden');
                rangeDateFilter.classList.remove('hidden');
                document.getElementById('tanggal_tunggal').disabled = true;
                document.getElementById('tanggal_mulai').disabled = false;
                document.getElementById('tanggal_akhir').disabled = false;
            });
        }
    </script>
@endpush
