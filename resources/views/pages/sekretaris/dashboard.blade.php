@extends('layouts.sidebar-sekretaris')

@section('title', 'Dashboard Sekretaris')

@section('content')
    <div class="max-w-7xl mx-auto">
        {{-- HEADER --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Dashboard Sekretaris</h1>
            <p class="text-gray-500 mt-1">Selamat datang kembali, {{ auth()->guard('role')->user()->username }}!</p>
            @if($kelasName)
                <span class="inline-flex items-center px-3 py-1 mt-2 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                    <span class="material-icons text-sm mr-1">school</span>
                    Kelas: {{ $kelasName }}
                </span>
            @else
                <span class="inline-flex items-center px-3 py-1 mt-2 rounded-full text-sm font-medium bg-red-100 text-red-800">
                    <span class="material-icons text-sm mr-1">warning</span>
                    Belum ditugaskan ke kelas manapun
                </span>
            @endif
        </div>

        {{-- NOTIFIKASI --}}
        @if (session('message'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md mb-6" role="alert">
                <p>{{ session('message') }}</p>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md mb-6" role="alert">
                <p>{{ session('error') }}</p>
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md mb-6" role="alert">
                <p class="font-bold">Terjadi Kesalahan</p>
                <ul class="mt-2 list-disc list-inside text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- STATISTIK CARDS --}}
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
            {{-- Total Siswa --}}
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500">Total Siswa</p>
                        <p class="text-2xl font-bold text-gray-800">{{ $totalSiswa }}</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <span class="material-icons text-blue-600">groups</span>
                    </div>
                </div>
            </div>

            {{-- Hadir Hari Ini --}}
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500">Hadir Hari Ini</p>
                        <p class="text-2xl font-bold text-green-600">{{ $hadirHariIni }}</p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <span class="material-icons text-green-600">check_circle</span>
                    </div>
                </div>
            </div>

            {{-- Sakit --}}
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500">Sakit</p>
                        <p class="text-2xl font-bold text-yellow-600">{{ $sakitHariIni }}</p>
                    </div>
                    <div class="bg-yellow-100 p-3 rounded-full">
                        <span class="material-icons text-yellow-600">local_hospital</span>
                    </div>
                </div>
            </div>

            {{-- Izin --}}
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500">Izin</p>
                        <p class="text-2xl font-bold text-blue-600">{{ $izinHariIni }}</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <span class="material-icons text-blue-600">assignment</span>
                    </div>
                </div>
            </div>

            {{-- Alpha --}}
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500">Alpha</p>
                        <p class="text-2xl font-bold text-red-600">{{ $alphaHariIni }}</p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <span class="material-icons text-red-600">cancel</span>
                    </div>
                </div>
            </div>

            {{-- Belum Absen --}}
            <div class="bg-white rounded-xl shadow-sm p-5 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-gray-500">Belum Absen</p>
                        <p class="text-2xl font-bold text-gray-600">{{ $belumAbsen }}</p>
                    </div>
                    <div class="bg-gray-100 p-3 rounded-full">
                        <span class="material-icons text-gray-600">pending</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- GRAFIK KEHADIRAN BULANAN --}}
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 mb-8">
            <h2 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <span class="material-icons text-blue-600">bar_chart</span>
                Grafik Kehadiran Bulan {{ now()->translatedFormat('F Y') }}
            </h2>
            <div class="h-72">
                <canvas id="monthlyAttendanceChart"></canvas>
            </div>
        </div>

        {{-- AKSI UTAMA --}}
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 mb-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <span class="material-icons text-blue-600">touch_app</span>
                Aksi Utama
            </h3>
            <div class="flex gap-4 flex-wrap">
                <a href="{{ route('sekretaris.kelola-absen') }}"
                    class="inline-flex items-center gap-2 bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold transition-colors shadow-sm">
                    <span class="material-icons text-xl">edit_calendar</span>
                    Kelola Absen Kelas
                </a>
                <a href="{{ route('sekretaris.users') }}" 
                    class="inline-flex items-center gap-2 bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg font-semibold transition-colors shadow-sm">
                    <span class="material-icons text-xl">groups</span>
                    Data Siswa
                </a>
            </div>
        </div>

        {{-- MANAJEMEN AKUN --}}
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center gap-2">
                <span class="material-icons text-gray-600">manage_accounts</span>
                Manajemen Akun
            </h3>
            <div class="flex gap-4 flex-wrap">
                <button onclick="document.getElementById('modal-change-password').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-lg font-semibold transition-colors shadow-sm">
                    <span class="material-icons text-xl">lock</span>
                    Ganti Password
                </button>
                <button onclick="document.getElementById('modal-delete-account').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 bg-red-700 hover:bg-red-800 text-white px-4 py-2 rounded-lg font-semibold transition-colors shadow-sm">
                    <span class="material-icons text-xl">delete_forever</span>
                    Hapus Akun Saya
                </button>
            </div>
        </div>

    </div>

    {{-- MODAL CHANGE PASSWORD --}}
    <div id="modal-change-password" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden z-50 p-4">
        <div class="bg-white p-6 rounded-xl shadow-lg max-w-md w-full relative border">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold flex items-center gap-2">
                    <span class="material-icons text-blue-600">lock</span>
                    Ganti Password Anda
                </h3>
                <button type="button" onclick="document.getElementById('modal-change-password').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                    <span class="material-icons">close</span>
                </button>
            </div>
            <form action="{{ route('admin.change-password') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Password Saat Ini</label>
                    <input type="password" name="current_password" id="current_password" required
                        class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('current_password', 'changePassword')<span
                    class="text-red-500 text-xs">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">Password Baru</label>
                    <input type="password" name="new_password" id="new_password" required
                        class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    @error('new_password', 'changePassword')<span
                    class="text-red-500 text-xs">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Konfirmasi Password Baru</label>
                    <input type="password" name="new_password_confirmation" id="new_password_confirmation" required
                        class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm p-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                </div>
                <div class="flex justify-end gap-2 pt-4">
                    <button type="button"
                        onclick="document.getElementById('modal-change-password').classList.add('hidden')"
                        class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium transition-colors">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>


    {{-- MODAL DELETE ACCOUNT --}}
    <div id="modal-delete-account" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden z-50 p-4">
        <div class="bg-white p-6 rounded-xl shadow-lg max-w-md w-full relative border">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-bold text-red-600 flex items-center gap-2">
                    <span class="material-icons">warning</span>
                    Hapus Akun Permanen
                </h3>
                <button type="button" onclick="document.getElementById('modal-delete-account').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                    <span class="material-icons">close</span>
                </button>
            </div>
            <p class="text-sm text-gray-600 mb-4">Aksi ini tidak dapat dibatalkan. Untuk melanjutkan, masukkan password Anda saat ini.</p>
            <form action="{{ route('admin.delete') }}" method="POST" class="space-y-4">
                @csrf
                @method('DELETE')
                <div>
                    <label for="password_delete" class="block text-sm font-medium text-gray-700 mb-1">Masukkan Password Anda</label>
                    <input type="password" name="password" id="password_delete" required
                        class="mt-1 block w-full border border-gray-300 rounded-lg shadow-sm p-2.5 focus:ring-2 focus:ring-red-500 focus:border-red-500">
                    @error('password', 'deleteAccount')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                </div>
                <div class="flex justify-end gap-2 pt-4">
                    <button type="button"
                        onclick="document.getElementById('modal-delete-account').classList.add('hidden')"
                        class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-red-600 hover:bg-red-700 text-white font-medium transition-colors">
                        Saya Mengerti, Hapus Akun
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Data dari controller
        const chartLabels = @json($chartLabels);
        const chartHadir = @json($chartHadir);
        const chartSakit = @json($chartSakit);
        const chartIzin = @json($chartIzin);
        const chartAlpha = @json($chartAlpha);

        // Chart Kehadiran Bulanan
        const ctx = document.getElementById('monthlyAttendanceChart').getContext('2d');
        const monthlyAttendanceChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: chartLabels,
                datasets: [
                    {
                        label: 'Hadir',
                        data: chartHadir,
                        backgroundColor: 'rgba(34, 197, 94, 0.8)',
                        borderColor: 'rgb(34, 197, 94)',
                        borderWidth: 1,
                        borderRadius: 4
                    },
                    {
                        label: 'Sakit',
                        data: chartSakit,
                        backgroundColor: 'rgba(234, 179, 8, 0.8)',
                        borderColor: 'rgb(234, 179, 8)',
                        borderWidth: 1,
                        borderRadius: 4
                    },
                    {
                        label: 'Izin',
                        data: chartIzin,
                        backgroundColor: 'rgba(59, 130, 246, 0.8)',
                        borderColor: 'rgb(59, 130, 246)',
                        borderWidth: 1,
                        borderRadius: 4
                    },
                    {
                        label: 'Alpha',
                        data: chartAlpha,
                        backgroundColor: 'rgba(239, 68, 68, 0.8)',
                        borderColor: 'rgb(239, 68, 68)',
                        borderWidth: 1,
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    title: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    </script>
@endpush

