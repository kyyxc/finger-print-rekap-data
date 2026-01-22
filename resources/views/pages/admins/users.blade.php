@extends('layouts.sidebar')

@section('title', 'Data Siswa')

@push('styles')
    <style>
        .table-row-hover {
            transition: all 0.2s ease;
        }

        .table-row-hover:hover {
            transform: translateX(4px);
            box-shadow: -4px 0 0 0 #10b981;
        }

        .search-input:focus {
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
        }
    </style>
@endpush

@section('content')
    <div class="max-w-7xl mx-auto h-full flex flex-col">
        {{-- HEADER --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8 flex-shrink-0">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Data Siswa</h1>
                <p class="text-gray-500 mt-1">Manajemen data siswa sistem</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <button onclick="document.getElementById('modal-import').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white rounded-lg font-medium transition-colors text-sm">
                    <span class="material-icons text-lg">upload_file</span>
                    Import Siswa
                </button>
                <button onclick="document.getElementById('modal-import-zip').classList.remove('hidden')"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-pink-500 hover:bg-pink-600 text-white rounded-lg font-medium transition-colors text-sm">
                    <span class="material-icons text-lg">photo_library</span>
                    Import Foto
                </button>
            </div>
        </div>

        {{-- NOTIFIKASI --}}
        <div class="flex-shrink-0">
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
        </div>

        {{-- DATA TABLE --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 flex-1 min-h-0 flex flex-col overflow-hidden p-6">

            {{-- Search Bar & Limit Selector --}}
            <div class="mb-6 flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between flex-shrink-0">
                <form method="GET" action="{{ route('admin.users') }}" class="relative w-full sm:max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input type="text" name="search" id="searchInput" placeholder="Cari NIS, nama, atau kelas..."
                        value="{{ $search ?? '' }}"
                        class="search-input w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:border-emerald-500 focus:bg-white transition-all duration-200">
                    <input type="hidden" name="per_page" value="{{ $perPage ?? 10 }}">
                </form>

                {{-- Limit Selector --}}
                <div class="flex items-center gap-3">
                    <label for="perPageSelect" class="text-sm text-gray-600 font-medium whitespace-nowrap">Tampilkan:</label>
                    <select id="perPageSelect" onchange="changePerPage(this.value)"
                        class="px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-700 focus:outline-none focus:border-emerald-500 focus:bg-white transition-all duration-200 cursor-pointer">
                        <option value="10" {{ ($perPage ?? 10) == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ ($perPage ?? 10) == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ ($perPage ?? 10) == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ ($perPage ?? 10) == 100 ? 'selected' : '' }}>100</option>
                    </select>
                    <span class="text-sm text-gray-500">data</span>
                </div>
            </div>

            {{-- Table --}}
            <div class="flex-1 min-h-0 overflow-auto rounded-xl border border-gray-200 shadow-sm">
                <table id="usersTable" class="w-full">
                    <thead class="sticky top-0 z-10 bg-gray-100 shadow-sm">
                        <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
                            <th class="py-4 px-6 text-left text-xs font-bold text-gray-600 uppercase tracking-wider bg-gray-100">No</th>
                            <th class="py-4 px-6 text-left text-xs font-bold text-gray-600 uppercase tracking-wider bg-gray-100">NIS</th>
                            <th class="py-4 px-6 text-left text-xs font-bold text-gray-600 uppercase tracking-wider bg-gray-100">Nama</th>
                            <th class="py-4 px-6 text-left text-xs font-bold text-gray-600 uppercase tracking-wider bg-gray-100">Kelas</th>
                            <th class="py-4 px-6 text-left text-xs font-bold text-gray-600 uppercase tracking-wider bg-gray-100">No. Telepon Ortu</th>
                            <th class="py-4 px-6 text-left text-xs font-bold text-gray-600 uppercase tracking-wider bg-gray-100">Photo</th>
                            <th class="py-4 px-6 text-center text-xs font-bold text-gray-600 uppercase tracking-wider bg-gray-100">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($users as $index => $user)
                            <tr class="table-row-hover bg-white hover:bg-emerald-50/50 transition-all duration-200"
                                data-search="{{ strtolower($user->nis . ' ' . $user->nama . ' ' . ($user->grade->name ?? '') . ' ' . ($user->phone_number ?? '')) }}">
                                <td class="py-4 px-6 text-sm text-gray-700 row-number">{{ $users->firstItem() + $index }}</td>
                                <td class="py-4 px-6">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                        {{ $user->nis }}
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="text-sm font-medium text-gray-800">{{ $user->nama ?? '-' }}</span>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">
                                        {{ $user->grade->name ?? '-' }}
                                    </span>
                                </td>
                                <td class="py-4 px-6">
                                    <span class="text-sm text-gray-700">{{ $user->phone_number ?? '-' }}</span>
                                </td>
                                <td class="py-4 px-6">
                                    @if ($user->photo)
                                        <a href="{{ asset('storage/' . $user->photo) }}" target="_blank">
                                            <img src="{{ asset('storage/' . $user->photo) }}"
                                                class="w-10 h-10 rounded-full object-cover border-2 border-gray-200 hover:border-emerald-400 transition-colors">
                                            </a>
                                        @else
                                            <a href="{{ asset('static/img/default.jpg') }}" target="_blank">
                                                <img src="{{ asset('static/img/default.jpg') }}"
                                                    class="w-10 h-10 rounded-full object-cover border-2 border-gray-200 hover:border-emerald-400 transition-colors">
                                            </a>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <button type="button" onclick="openEditModal({{ $user->id }}, '{{ $user->nis }}', '{{ addslashes($user->nama ?? '') }}', '{{ $user->grade_id ?? '' }}', '{{ $user->photo ? asset('storage/' . $user->photo) : asset('static/img/default.jpg') }}', '{{ addslashes($user->phone_number ?? '') }}')"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-emerald-600 hover:bg-emerald-50 hover:text-emerald-800 transition-colors cursor-pointer" title="Edit">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                <span class="text-xs font-medium">Edit</span>
                                            </button>
                                            <button type="button" onclick="confirmDelete({{ $user->id }}, '{{ addslashes($user->nama ?? $user->nis) }}')"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-red-600 hover:bg-red-50 hover:text-red-800 transition-colors cursor-pointer" title="Hapus">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                <span class="text-xs font-medium">Hapus</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr id="emptyDataRow">
                                    <td colspan="7" class="py-12 sm:py-16 px-4 sm:px-6 text-center">
                                        <div class="flex flex-col items-center gap-2 sm:gap-3">
                                            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-emerald-50 flex items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 sm:h-8 sm:w-8 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                </svg>
                                            </div>
                                            <p class="text-gray-400 text-sm sm:text-base font-medium">Tidak ada data siswa</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                            {{-- Hidden row for no search results --}}
                            <tr id="noSearchResultsRow" class="hidden">
                                <td colspan="7" class="py-12 sm:py-16 px-4 sm:px-6 text-center">
                                    <div class="flex flex-col items-center gap-2 sm:gap-3">
                                        <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-emerald-50 flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 sm:h-8 sm:w-8 text-emerald-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                            </svg>
                                        </div>
                                        <p class="text-gray-400 text-sm sm:text-base font-medium">Tidak ada hasil pencarian</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
            </div>

            {{-- Pagination --}}
            @if ($users->total() > 0)
                <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4 flex-shrink-0">
                    <p class="text-sm text-gray-500">
                        Menampilkan <span class="font-semibold text-gray-700">{{ $users->firstItem() }}-{{ $users->lastItem() }}</span> dari
                        <span class="font-semibold text-gray-700">{{ $users->total() }}</span> data
                    </p>

                    @if ($users->hasPages())
                        <nav class="flex items-center gap-1">
                            {{-- Previous --}}
                            @if ($users->onFirstPage())
                                <span class="px-3 py-2 rounded-lg text-gray-400 bg-gray-100 cursor-not-allowed">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                    </svg>
                                </span>
                            @else
                                <a href="{{ $users->previousPageUrl() }}"
                                    class="px-3 py-2 rounded-lg text-gray-600 bg-white border border-gray-200 hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-600 transition-all duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                    </svg>
                                </a>
                            @endif

                            {{-- Page Numbers --}}
                            @php
                                $start = max($users->currentPage() - 1, 1);
                                $end = min($users->currentPage() + 1, $users->lastPage());
                            @endphp

                            @if ($start > 1)
                                <a href="{{ $users->url(1) }}"
                                    class="px-3.5 py-2 rounded-lg text-sm text-gray-600 bg-white border border-gray-200 hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-600 transition-all duration-200">1</a>
                                @if ($start > 2)
                                    <span class="px-2 text-gray-400 text-sm">...</span>
                                @endif
                            @endif

                            @for ($i = $start; $i <= $end; $i++)
                                @if ($i == $users->currentPage())
                                    <span class="px-3.5 py-2 rounded-lg text-sm font-semibold text-white bg-emerald-600 shadow-sm">{{ $i }}</span>
                                @else
                                    <a href="{{ $users->url($i) }}"
                                        class="px-3.5 py-2 rounded-lg text-sm text-gray-600 bg-white border border-gray-200 hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-600 transition-all duration-200">{{ $i }}</a>
                                @endif
                            @endfor

                            @if ($end < $users->lastPage())
                                @if ($end < $users->lastPage() - 1)
                                    <span class="px-2 text-gray-400 text-sm">...</span>
                                @endif
                                <a href="{{ $users->url($users->lastPage()) }}"
                                    class="px-3.5 py-2 rounded-lg text-sm text-gray-600 bg-white border border-gray-200 hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-600 transition-all duration-200">{{ $users->lastPage() }}</a>
                            @endif

                            {{-- Next --}}
                            @if ($users->hasMorePages())
                                <a href="{{ $users->nextPageUrl() }}"
                                    class="px-3 py-2 rounded-lg text-gray-600 bg-white border border-gray-200 hover:bg-emerald-50 hover:border-emerald-300 hover:text-emerald-600 transition-all duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </a>
                            @else
                                <span class="px-3 py-2 rounded-lg text-gray-400 bg-gray-100 cursor-not-allowed">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                                    </svg>
                                </span>
                            @endif
                        </nav>
                    @endif
                </div>
            @endif
        </div>
    </div>

    {{-- MODAL EDIT USER --}}
    <div id="modal-edit-user" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden z-50 p-4">
        <div class="bg-white p-6 rounded-xl shadow-xl max-w-md w-full relative">
            <h3 class="text-xl font-semibold mb-4">Edit Data Siswa</h3>
            <form id="editUserForm" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                @method('PATCH')

                {{-- Photo Preview & Upload --}}
                <div class="flex flex-col items-center mb-4">
                    <div class="relative">
                        <img id="edit_photo_preview" src="{{ asset('static/img/default-avatar.png') }}"
                            alt="Preview Foto"
                            class="w-24 h-24 rounded-full object-cover border-4 border-gray-200">
                        <label for="edit_photo" class="absolute bottom-0 right-0 bg-emerald-600 text-white p-2 rounded-full cursor-pointer hover:bg-emerald-700 transition-colors">
                            <span class="material-icons text-sm">camera_alt</span>
                        </label>
                    </div>
                    <input type="file" name="photo" id="edit_photo" accept="image/*" class="hidden" onchange="previewPhoto(this)">
                    <p class="text-xs text-gray-500 mt-2">Klik ikon kamera untuk ubah foto</p>
                </div>

                <div>
                    <label for="edit_nis" class="block text-sm font-medium text-gray-700">NIS</label>
                    <input type="text" name="nis" id="edit_nis" required
                        placeholder="Masukkan NIS"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label for="edit_nama" class="block text-sm font-medium text-gray-700">Nama</label>
                    <input type="text" name="nama" id="edit_nama" required
                        placeholder="Masukkan nama lengkap"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div>
                    <label for="edit_kelas" class="block text-sm font-medium text-gray-700">Kelas</label>
                    <select name="grade_id" id="edit_kelas"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-emerald-500 focus:border-emerald-500">
                        <option value="">-- Pilih Kelas --</option>
                        @foreach($grades as $grade)
                            <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="edit_phone_number" class="block text-sm font-medium text-gray-700">No. Telepon Orang Tua</label>
                    <input type="text" name="phone_number" id="edit_phone_number"
                        placeholder="Contoh: +6281234567890"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
                <div class="flex justify-end gap-2 pt-4">
                    <button type="button"
                        onclick="document.getElementById('modal-edit-user').classList.add('hidden')"
                        class="bg-gray-300 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors cursor-pointer">Batal</button>
                    <button type="submit" class="bg-emerald-600 text-white px-4 py-2 rounded-lg hover:bg-emerald-700 transition-colors cursor-pointer">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL KONFIRMASI HAPUS --}}
    <div id="modal-delete-user" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden z-50 p-4">
        <div class="bg-white p-6 rounded-xl shadow-xl max-w-md w-full relative">
            <div class="flex items-center justify-center mb-4">
                <div class="bg-red-100 p-4 rounded-full">
                    <span class="material-icons text-red-600 text-4xl">warning</span>
                </div>
            </div>
            <h3 class="text-xl font-bold text-center text-gray-800 mb-2">Hapus Data Siswa?</h3>
            <p class="text-center text-gray-600 mb-6">
                Anda yakin ingin menghapus siswa <strong id="delete-user-name"></strong>? Tindakan ini tidak dapat dibatalkan.
            </p>
            <form id="deleteUserForm" method="POST" class="space-y-4">
                @csrf
                @method('DELETE')
                <div class="flex justify-center gap-3">
                    <button type="button"
                        onclick="document.getElementById('modal-delete-user').classList.add('hidden')"
                        class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-lg transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors cursor-pointer">
                        Ya, Hapus
                    </button>
                </div>
            </form>
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

    {{-- MODAL IMPORT FOTO ZIP --}}
    <div id="modal-import-zip" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden z-50">
        <div class="bg-white p-6 rounded-xl max-w-md w-full relative shadow-xl mx-4">
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
@endsection

@push('scripts')
    <script>
        // Change per page
        function changePerPage(value) {
            const url = new URL(window.location.href);
            url.searchParams.set('per_page', value);
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }

        // Server-side search with debounce
        let searchTimeout;
        document.getElementById('searchInput').addEventListener('keyup', function(e) {
            // Submit on Enter key
            if (e.key === 'Enter') {
                this.closest('form').submit();
                return;
            }

            // Debounce auto-submit (wait 500ms after typing stops)
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                this.closest('form').submit();
            }, 500);
        });

        // Open edit modal
        function openEditModal(id, nis, nama, gradeId, photoUrl, phoneNumber) {
            document.getElementById('edit_nis').value = nis;
            document.getElementById('edit_nama').value = nama;
            document.getElementById('edit_kelas').value = gradeId || '';
            document.getElementById('edit_phone_number').value = phoneNumber || '';
            document.getElementById('edit_photo_preview').src = photoUrl || '{{ asset("static/img/default-avatar.png") }}';
            document.getElementById('edit_photo').value = ''; // Reset file input
            document.getElementById('editUserForm').action = '{{ url("admin/users") }}/' + id;
            document.getElementById('modal-edit-user').classList.remove('hidden');
        }

        // Preview photo before upload
        function previewPhoto(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('edit_photo_preview').src = e.target.result;
                }
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Confirm delete user
        function confirmDelete(userId, nama) {
            document.getElementById('delete-user-name').textContent = nama;
            document.getElementById('deleteUserForm').action = '{{ url("admin/users") }}/' + userId;
            document.getElementById('modal-delete-user').classList.remove('hidden');
        }

        // Close modal on click outside
        document.getElementById('modal-edit-user').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });

        document.getElementById('modal-delete-user').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });
    </script>
@endpush
