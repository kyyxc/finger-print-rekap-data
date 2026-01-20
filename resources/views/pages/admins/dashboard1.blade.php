@extends('layouts.sidebar')

@section('title', 'Dashboard Admin')

@section('content')
    <div class="max-w-7xl mx-auto">
        {{-- HEADER --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Dashboard</h1>
            <p class="text-gray-500 mt-1">Selamat datang kembali, {{ auth()->guard('role')->user()->username }}!</p>
        </div>

        {{-- NOTIFIKASI --}}
        @if (session('message'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md mb-6" role="alert">
                <p>{{ session('message') }}</p>
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
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Users</p>
                        <p class="text-2xl font-bold text-gray-800">150</p>
                    </div>
                    <div class="bg-blue-100 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Hadir Hari Ini</p>
                        <p class="text-2xl font-bold text-green-600">120</p>
                    </div>
                    <div class="bg-green-100 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Belum Hadir</p>
                        <p class="text-2xl font-bold text-red-600">30</p>
                    </div>
                    <div class="bg-red-100 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-red-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Kelas</p>
                        <p class="text-2xl font-bold text-purple-600">12</p>
                    </div>
                    <div class="bg-purple-100 p-3 rounded-full">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        {{-- GRAFIK SECTION --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            {{-- Grafik Total Kehadiran --}}
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Total Kehadiran Minggu Ini</h2>
                <div class="h-64">
                    <canvas id="totalAttendanceChart"></canvas>
                </div>
            </div>

            {{-- Grafik Kehadiran Per Kelas --}}
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-800">Kehadiran Per Kelas</h2>
                    <div class="relative">
                        <button id="classDropdownBtn" type="button"
                            class="inline-flex items-center gap-2 px-4 py-2 bg-gray-100 hover:bg-gray-200 rounded-lg text-sm font-medium text-gray-700 transition-colors">
                            <span id="selectedClassName">Pilih Kelas</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                            </svg>
                        </button>
                        {{-- Dropdown Menu --}}
                        <div id="classDropdownMenu"
                            class="hidden absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-10">
                            <div class="max-h-48 overflow-y-auto">
                                <button type="button" data-class="all" data-name="Semua Kelas"
                                    class="class-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 first:rounded-t-lg">
                                    Semua Kelas
                                </button>
                                <button type="button" data-class="X-IPA-1" data-name="X IPA 1"
                                    class="class-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    X IPA 1
                                </button>
                                <button type="button" data-class="X-IPA-2" data-name="X IPA 2"
                                    class="class-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    X IPA 2
                                </button>
                                <button type="button" data-class="X-IPS-1" data-name="X IPS 1"
                                    class="class-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    X IPS 1
                                </button>
                                <button type="button" data-class="X-IPS-2" data-name="X IPS 2"
                                    class="class-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    X IPS 2
                                </button>
                                <button type="button" data-class="XI-IPA-1" data-name="XI IPA 1"
                                    class="class-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    XI IPA 1
                                </button>
                                <button type="button" data-class="XI-IPA-2" data-name="XI IPA 2"
                                    class="class-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    XI IPA 2
                                </button>
                                <button type="button" data-class="XI-IPS-1" data-name="XI IPS 1"
                                    class="class-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    XI IPS 1
                                </button>
                                <button type="button" data-class="XI-IPS-2" data-name="XI IPS 2"
                                    class="class-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    XI IPS 2
                                </button>
                                <button type="button" data-class="XII-IPA-1" data-name="XII IPA 1"
                                    class="class-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    XII IPA 1
                                </button>
                                <button type="button" data-class="XII-IPA-2" data-name="XII IPA 2"
                                    class="class-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    XII IPA 2
                                </button>
                                <button type="button" data-class="XII-IPS-1" data-name="XII IPS 1"
                                    class="class-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    XII IPS 1
                                </button>
                                <button type="button" data-class="XII-IPS-2" data-name="XII IPS 2"
                                    class="class-option w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 last:rounded-b-lg">
                                    XII IPS 2
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="classAttendanceChart"></canvas>
                </div>
            </div>
        </div>

        {{-- QUICK ACTIONS --}}
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 mb-8">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Aksi Cepat</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <button onclick="document.getElementById('modal-import').classList.remove('hidden')"
                    class="flex flex-col items-center justify-center p-4 bg-yellow-50 hover:bg-yellow-100 rounded-xl transition-colors border border-yellow-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-yellow-600 mb-2" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    <span class="text-sm font-medium text-yellow-700">Import Siswa</span>
                </button>

                <button onclick="document.getElementById('modal-import-zip').classList.remove('hidden')"
                    class="flex flex-col items-center justify-center p-4 bg-pink-50 hover:bg-pink-100 rounded-xl transition-colors border border-pink-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-pink-600 mb-2" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="text-sm font-medium text-pink-700">Import Foto</span>
                </button>

                <a href="{{ route('dashboard', ['search' => '', 'tanggal' => now()->toDateString(), 'status' => '', 'show_incomplete' => 1]) }}"
                    class="flex flex-col items-center justify-center p-4 bg-blue-50 hover:bg-blue-100 rounded-xl transition-colors border border-blue-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-blue-600 mb-2" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <span class="text-sm font-medium text-blue-700">Lihat Absensi</span>
                </a>

                <button onclick="document.getElementById('modal-create-admin').classList.remove('hidden')"
                    class="flex flex-col items-center justify-center p-4 bg-green-50 hover:bg-green-100 rounded-xl transition-colors border border-green-200">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600 mb-2" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                    <span class="text-sm font-medium text-green-700">Tambah Admin</span>
                </button>
            </div>
        </div>

        {{-- MANAJEMEN AKUN --}}
        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Manajemen Akun</h2>
            <div class="flex flex-wrap gap-3">
                <button onclick="document.getElementById('modal-change-password').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-gray-700 hover:bg-gray-800 text-white rounded-lg font-medium transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                    </svg>
                    Ganti Password
                </button>
                <button onclick="document.getElementById('modal-delete-account').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Hapus Akun
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL IMPORT SISWA --}}
    <div id="modal-import" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden z-50">
        <div class="bg-white p-6 rounded-xl max-w-md w-full relative shadow-xl mx-4">
            <h3 class="text-xl font-semibold mb-4">Import Siswa dari Excel</h3>

            <div class="mb-4">
                <p class="text-sm text-gray-600 mb-1">Template Format Import Excel</p>
                <a href="{{ asset('default/Template User Import.csv') }}" download
                    class="text-blue-600 underline hover:text-blue-800">
                    Unduh Contoh Format Excel
                </a>
            </div>

            <form action="{{ route('admin.users.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="excel_file" accept=".xlsx,.xls, .csv" required
                        class="w-full border p-2 rounded mb-4">
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="document.getElementById('modal-import').classList.add('hidden')"
                            class="bg-gray-300 px-4 py-2 rounded-xl hover:bg-gray-400">
                            Batal
                        </button>
                        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-xl hover:bg-green-600">
                            Import
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div id="modal-import-zip" class="fixed inset-0 bg-opacity-50 flex items-center justify-center hidden z-50">
            <div class="bg-white p-6 rounded-xl max-w-md w-full relative border">
                <h3 class="text-xl font-semibold mb-4">Import Foto Siswa (ZIP)</h3>
                <p class="text-sm text-gray-600 mb-2">
                    Hanya format <strong>jpg, jpeg, png</strong> dengan ukuran maksimal <strong>5MB</strong> per file.
                    Nama file harus <strong>sesuai NIK</strong> siswa. Contoh: <code>12288901.jpg</code>
                </p>
                <form action="{{ route('admin.users.import.photos') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <input type="file" name="zip_file" accept=".zip" required class="w-full border p-2 rounded mb-4">
                    <div class="flex justify-end gap-2">
                        <button type="button" onclick="document.getElementById('modal-import-zip').classList.add('hidden')"
                            class="bg-gray-300 px-4 py-2 rounded-xl hover:bg-gray-400">
                            Batal
                        </button>
                        <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-xl hover:bg-green-600">
                            Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div id="modal-change-password" class="fixed inset-0 flex items-center justify-center hidden z-50 p-4">
            <div class="bg-white p-6 rounded-xl shadow-lg max-w-md w-full relative border">
                <h3 class="text-xl font-semibold mb-4">Ganti Password Anda</h3>
                <form action="{{ route('admin.change-password') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-700">Password Saat
                            Ini</label>
                        <input type="password" name="current_password" id="current_password" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                        @error('current_password', 'changePassword')<span
                        class="text-red-500 text-xs">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label for="new_password" class="block text-sm font-medium text-gray-700">Password Baru</label>
                        <input type="password" name="new_password" id="new_password" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                        @error('new_password', 'changePassword')<span
                        class="text-red-500 text-xs">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label for="new_password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi
                            Password Baru</label>
                        <input type="password" name="new_password_confirmation" id="new_password_confirmation" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    </div>
                    <div class="flex justify-end gap-2 pt-4">
                        <button type="button"
                            onclick="document.getElementById('modal-change-password').classList.add('hidden')"
                            class="bg-gray-300 px-4 py-2 rounded-lg hover:bg-gray-400">Batal</button>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Simpan
                            Perubahan</button>
                    </div>
                </form>
            </div>
        </div>

    {{-- MODAL BUAT ADMIN --}}
    <div id="modal-create-admin" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden z-50 p-4">
        <div class="bg-white p-6 rounded-xl shadow-xl max-w-md w-full relative">
                <h3 class="text-xl font-semibold mb-4">Buat Admin Baru</h3>
                <form action="{{ route('admin.create') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                        <input type="text" name="username" id="username" value="{{ old('username') }}" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                        @error('username', 'createAdmin')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label for="password_create" class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" name="password" id="password_create" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                        @error('password', 'createAdmin')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label for="password_confirmation_create" class="block text-sm font-medium text-gray-700">Konfirmasi
                            Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation_create" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    </div>
                    <div class="flex justify-end gap-2 pt-4">
                        <button type="button"
                            onclick="document.getElementById('modal-create-admin').classList.add('hidden')"
                            class="bg-gray-300 px-4 py-2 rounded-lg hover:bg-gray-400">Batal</button>
                        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">Buat
                            Admin</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL BUAT SEKRETARIS --}}
        <div id="modal-create-sekretaris" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden z-50 p-4">
            <div class="bg-white p-6 rounded-xl shadow-xl max-w-md w-full relative">
                <h3 class="text-xl font-semibold mb-4">Buat Sekretaris Baru</h3>
                <form action="{{ route('admin.create') }}" method="POST" class="space-y-4">
                    @csrf
                    <input type="hidden" name="role" value="sekretaris">
                    <div>
                        <label for="username_sekretaris" class="block text-sm font-medium text-gray-700">Username</label>
                        <input type="text" name="username" id="username_sekretaris" value="{{ old('username') }}" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                        @error('username', 'createSekretaris')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label for="password_sekretaris" class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" name="password" id="password_sekretaris" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                        @error('password', 'createSekretaris')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                    </div>
                    <div>
                        <label for="password_confirmation_sekretaris" class="block text-sm font-medium text-gray-700">Konfirmasi
                            Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation_sekretaris" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                    </div>
                    <div class="flex justify-end gap-2 pt-4">
                        <button type="button"
                            onclick="document.getElementById('modal-create-sekretaris').classList.add('hidden')"
                            class="bg-gray-300 px-4 py-2 rounded-lg hover:bg-gray-400">Batal</button>
                        <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700">Buat
                            Sekretaris</button>
                    </div>
                </form>
            </div>
        </div>

        <div id="modal-delete-account" class="fixed inset-0 flex items-center justify-center hidden z-50 p-4">
            <div class="bg-white p-6 rounded-xl shadow-lg max-w-md w-full relative border">
                <h3 class="text-xl font-bold text-red-600 mb-2">Hapus Akun Permanen</h3>
                <p class="text-sm text-gray-600 mb-4">Aksi ini tidak dapat dibatalkan. Untuk melanjutkan, masukkan password
                    Anda saat ini.</p>
                <form action="{{ route('admin.delete') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('DELETE')
                    <div>
                        <label for="password_delete" class="block text-sm font-medium text-gray-700">Masukkan Password
                            Anda</label>
                        <input type="password" name="password" id="password_delete" required
                            class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2">
                        @error('password', 'deleteAccount')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                    </div>
                    <div class="flex justify-end gap-2 pt-4">
                        <button type="button"
                            onclick="document.getElementById('modal-delete-account').classList.add('hidden')"
                            class="bg-gray-300 px-4 py-2 rounded-lg hover:bg-gray-400">Batal</button>
                        <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg hover:bg-red-700">Saya
                            Mengerti, Hapus Akun</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Dummy data untuk grafik
        const dummyWeeklyData = {
            labels: ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
            hadir: [142, 138, 145, 140, 135, 120],
            tidakHadir: [8, 12, 5, 10, 15, 30]
        };

        const dummyClassData = {
            'all': { hadir: [142, 138, 145, 140, 135, 120], tidakHadir: [8, 12, 5, 10, 15, 30] },
            'X-IPA-1': { hadir: [28, 27, 30, 29, 28, 25], tidakHadir: [2, 3, 0, 1, 2, 5] },
            'X-IPA-2': { hadir: [26, 28, 27, 26, 25, 22], tidakHadir: [4, 2, 3, 4, 5, 8] },
            'X-IPS-1': { hadir: [24, 25, 26, 24, 23, 20], tidakHadir: [6, 5, 4, 6, 7, 10] },
            'X-IPS-2': { hadir: [25, 24, 25, 24, 23, 21], tidakHadir: [5, 6, 5, 6, 7, 9] },
            'XI-IPA-1': { hadir: [29, 28, 30, 29, 28, 26], tidakHadir: [1, 2, 0, 1, 2, 4] },
            'XI-IPA-2': { hadir: [27, 26, 28, 27, 26, 24], tidakHadir: [3, 4, 2, 3, 4, 6] },
            'XI-IPS-1': { hadir: [23, 24, 25, 23, 22, 19], tidakHadir: [7, 6, 5, 7, 8, 11] },
            'XI-IPS-2': { hadir: [22, 23, 24, 22, 21, 18], tidakHadir: [8, 7, 6, 8, 9, 12] },
            'XII-IPA-1': { hadir: [30, 29, 30, 30, 29, 27], tidakHadir: [0, 1, 0, 0, 1, 3] },
            'XII-IPA-2': { hadir: [28, 27, 29, 28, 27, 25], tidakHadir: [2, 3, 1, 2, 3, 5] },
            'XII-IPS-1': { hadir: [25, 26, 27, 25, 24, 21], tidakHadir: [5, 4, 3, 5, 6, 9] },
            'XII-IPS-2': { hadir: [24, 25, 26, 24, 23, 20], tidakHadir: [6, 5, 4, 6, 7, 10] }
        };

        // Chart Total Kehadiran
        const totalCtx = document.getElementById('totalAttendanceChart').getContext('2d');
        const totalAttendanceChart = new Chart(totalCtx, {
            type: 'bar',
            data: {
                labels: dummyWeeklyData.labels,
                datasets: [
                    {
                        label: 'Hadir',
                        data: dummyWeeklyData.hadir,
                        backgroundColor: 'rgba(34, 197, 94, 0.8)',
                        borderColor: 'rgb(34, 197, 94)',
                        borderWidth: 1,
                        borderRadius: 4
                    },
                    {
                        label: 'Tidak Hadir',
                        data: dummyWeeklyData.tidakHadir,
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
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
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

        // Chart Kehadiran Per Kelas
        const classCtx = document.getElementById('classAttendanceChart').getContext('2d');
        let classAttendanceChart = new Chart(classCtx, {
            type: 'line',
            data: {
                labels: dummyWeeklyData.labels,
                datasets: [
                    {
                        label: 'Hadir',
                        data: dummyClassData['all'].hadir,
                        borderColor: 'rgb(34, 197, 94)',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        fill: true,
                        tension: 0.3
                    },
                    {
                        label: 'Tidak Hadir',
                        data: dummyClassData['all'].tidakHadir,
                        borderColor: 'rgb(239, 68, 68)',
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        fill: true,
                        tension: 0.3
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
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

        // Dropdown functionality
        const dropdownBtn = document.getElementById('classDropdownBtn');
        const dropdownMenu = document.getElementById('classDropdownMenu');
        const selectedClassName = document.getElementById('selectedClassName');
        const classOptions = document.querySelectorAll('.class-option');

        // Toggle dropdown
        dropdownBtn.addEventListener('click', function(e) {
            e.stopPropagation();
            dropdownMenu.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function() {
            dropdownMenu.classList.add('hidden');
        });

        // Handle class selection
        classOptions.forEach(option => {
            option.addEventListener('click', function() {
                const classId = this.dataset.class;
                const className = this.dataset.name;

                selectedClassName.textContent = className;
                dropdownMenu.classList.add('hidden');

                // Update chart data
                const classData = dummyClassData[classId];
                classAttendanceChart.data.datasets[0].data = classData.hadir;
                classAttendanceChart.data.datasets[1].data = classData.tidakHadir;
                classAttendanceChart.update();
            });
        });
    </script>
@endpush
