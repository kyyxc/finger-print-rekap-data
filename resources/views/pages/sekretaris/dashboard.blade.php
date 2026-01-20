@extends('layouts.main')

@section('title', 'Dashboard Sekretaris')

@section('content')
    <div class="min-h-screen bg-gray-100 p-6">
        <div class="max-w-4xl mx-auto bg-white shadow-xl rounded-2xl p-6 space-y-6">

            {{-- HEADER --}}
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Halaman Sekretaris</h2>
                    <p class="text-sm text-gray-500">Selamat datang, {{ auth()->guard('role')->user()->username }}!</p>
                </div>
                <form action="{{ route('logout') }}" method="POST">
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
                    <a href="{{ route('dashboard', ['search' => '', 'tanggal' => now()->toDateString(), 'status' => '', 'show_incomplete' => 1]) }}"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold">
                        Dashboard Absensi
                    </a>
                    <a href="{{ route('admin.users') }}" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg font-semibold">
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
                    <button onclick="document.getElementById('modal-delete-account').classList.remove('hidden')"
                        class="bg-red-700 hover:bg-red-800 text-white px-4 py-2 rounded-lg font-semibold">
                        Hapus Akun Saya
                    </button>
                </div>
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
