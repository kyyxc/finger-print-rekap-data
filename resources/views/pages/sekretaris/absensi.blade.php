@extends('layouts.sidebar-sekretaris')

@section('title', 'Dashboard Absensi')

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
    <div class="max-w-7xl mx-auto h-full flex flex-col">
        {{-- HEADER --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 flex-shrink-0">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Dashboard Absensi</h1>
                <p class="text-gray-500 mt-1">Pantau kehadiran siswa secara real-time</p>
            </div>
        </div>

        {{-- MAIN CONTENT --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 flex-1 min-h-0 flex flex-col overflow-hidden p-6">
            <p class="text-gray-600 text-center py-12">
                Dashboard absensi akan ditampilkan di sini. Ini adalah halaman terpisah untuk sekretaris.
            </p>
        </div>
    </div>
@endsection
