@extends('layouts.main')

@section('title', 'Dashboard Admin')

@section('content')
    <div class="min-h-screen bg-gray-100 p-6">
        <div class="max-w-4xl mx-auto bg-white shadow-xl rounded-2xl p-6 space-y-6">

            {{-- HEADER --}}
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Halaman Admin</h2>
                    <p class="text-sm text-gray-500">Selamat datang, {{ auth()->guard('admin')->user()->username }}!</p>
                </div>
                <form action="{{ route('admins.logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="w-full sm:w-auto bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg font-semibold transition-colors">
                        Logout
                    </button>
                </form>
            </div>

            {{-- NOTIFIKASI --}}
            @if (session('message'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 rounded-md" role="alert">
                    <p>{{ session('message') }}</p>
                </div>
            @endif

            {{-- MENAMPILKAN SEMUA ERROR DI ATAS (OPSIONAL, JIKA MODAL ERROR TIDAK CUKUP) --}}
            @if ($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 rounded-md" role="alert">
                    <p class="font-bold">Terjadi Kesalahan</p>
                    <ul class="mt-2 list-disc list-inside text-sm">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif


            {{-- AKSI UTAMA --}}
            <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-3 border-b pb-2">Aksi Utama</h3>
                <div class="flex gap-4 flex-wrap">
                    <button onclick="document.getElementById('modal-import').classList.remove('hidden')"
                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg font-semibold">
                        Import Siswa (Excel)
                    </button>
                    <button onclick="document.getElementById('modal-import-zip').classList.remove('hidden')"
                        class="bg-pink-500 hover:bg-pink-600 text-white px-4 py-2 rounded-lg font-semibold">
                        Import Foto (ZIP)
                    </button>
                    <a href="{{ route('dashboard', ['search' => '', 'tanggal' => now()->toDateString(), 'status' => '', 'show_incomplete' => 1]) }}"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold">
                        Dashboard Absensi
                    </a>
                    <a href="{{ route('admins.users') }}" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg font-semibold">
                        Data Users
                    </a>
                </div>
            </div>

            {{-- MANAJEMEN ADMIN --}}
            <div>
                <h3 class="text-lg font-semibold text-gray-700 mb-3 border-b pb-2">Manajemen Akun</h3>
                <div class="flex gap-4 flex-wrap">
                    <button onclick="document.getElementById('modal-change-password').classList.remove('hidden')"
                        class="bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-lg font-semibold">
                        Ganti Password
                    </button>
                    <button onclick="document.getElementById('modal-create-admin').classList.remove('hidden')"
                        class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-semibold">
                        Buat Admin Baru
                    </button>
                    <button onclick="document.getElementById('modal-delete-account').classList.remove('hidden')"
                        class="bg-red-700 hover:bg-red-800 text-white px-4 py-2 rounded-lg font-semibold">
                        Hapus Akun Saya
                    </button>
                </div>
            </div>

        </div>

        <div id="modal-import" class="fixed inset-0 bg-opacity-50 flex items-center justify-center hidden z-50">
            <div class="bg-white p-6 rounded-xl max-w-md w-full relative border">
                <h3 class="text-xl font-semibold mb-4">Import Siswa dari Excel</h3>

                <div class="mb-4">
                    <p class="text-sm text-gray-600 mb-1">Template Format Import Excel</p>
                    <a href="{{ asset('default/Template User Import.csv') }}" download
                        class="text-blue-600 underline hover:text-blue-800">
                        Unduh Contoh Format Excel
                    </a>
                </div>

                <form action="{{ route('admins.users.import') }}" method="POST" enctype="multipart/form-data">
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
                <form action="{{ route('admins.users.import.photos') }}" method="POST" enctype="multipart/form-data">
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
                <form action="{{ route('admins.change-password') }}" method="POST" class="space-y-4">
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

        <div id="modal-create-admin" class="fixed inset-0 flex items-center justify-center hidden z-50 p-4">
            <div class="bg-white p-6 rounded-xl shadow-lg max-w-md w-full relative border">
                <h3 class="text-xl font-semibold mb-4">Buat Admin Baru</h3>
                <form action="{{ route('admins.create') }}" method="POST" class="space-y-4">
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

        <div id="modal-delete-account" class="fixed inset-0 flex items-center justify-center hidden z-50 p-4">
            <div class="bg-white p-6 rounded-xl shadow-lg max-w-md w-full relative border">
                <h3 class="text-xl font-bold text-red-600 mb-2">Hapus Akun Permanen</h3>
                <p class="text-sm text-gray-600 mb-4">Aksi ini tidak dapat dibatalkan. Untuk melanjutkan, masukkan password
                    Anda saat ini.</p>
                <form action="{{ route('admins.delete') }}" method="POST" class="space-y-4">
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
