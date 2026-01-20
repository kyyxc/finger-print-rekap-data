@extends('layouts.sidebar')

@section('title', 'Kelola Sekretaris')

@push('styles')
    <style>
        .table-row-hover {
            transition: all 0.2s ease;
        }

        .table-row-hover:hover {
            transform: translateX(4px);
            box-shadow: -4px 0 0 0 #8b5cf6;
        }

        .search-input:focus {
            box-shadow: 0 0 0 3px rgba(139, 92, 246, 0.15);
        }
    </style>
@endpush

@section('content')
    <div class="max-w-7xl mx-auto h-full flex flex-col">
        {{-- HEADER --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8 flex-shrink-0">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Kelola Sekretaris</h1>
                <p class="text-gray-500 mt-1">Manajemen data sekretaris sistem</p>
            </div>
            <button onclick="document.getElementById('modal-create-sekretaris').classList.remove('hidden')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-lg font-medium transition-colors cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Sekretaris
            </button>
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
                    <input type="text" id="searchInput" placeholder="Cari username atau kelas..."
                        class="search-input w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:border-purple-500 focus:bg-white transition-all duration-200">
                </div>

                {{-- Limit Selector --}}
                <div class="flex items-center gap-3">
                    <label for="perPageSelect" class="text-sm text-gray-600 font-medium whitespace-nowrap">Tampilkan:</label>
                    <select id="perPageSelect" onchange="changePerPage(this.value)"
                        class="px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-700 focus:outline-none focus:border-purple-500 focus:bg-white transition-all duration-200 cursor-pointer">
                        <option value="10" {{ ($perPage ?? 10) == 10 ? 'selected' : '' }}>10</option>
                        <option value="25" {{ ($perPage ?? 10) == 25 ? 'selected' : '' }}>25</option>
                        <option value="50" {{ ($perPage ?? 10) == 50 ? 'selected' : '' }}>50</option>
                        <option value="100" {{ ($perPage ?? 10) == 100 ? 'selected' : '' }}>100</option>
                    </select>
                    <span class="text-sm text-gray-500">data</span>
                </div>
            </div>

            {{-- Table --}}
            <div class="overflow-hidden rounded-xl border border-gray-200 shadow-sm">
                <div class="overflow-x-auto">
                    <table id="sekretarisTable" class="w-full">
                        <thead>
                            <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
                                <th class="py-4 px-6 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">No</th>
                                <th class="py-4 px-6 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Username</th>
                                <th class="py-4 px-6 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Kelas</th>
                                <th class="py-4 px-6 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Role</th>
                                <th class="py-4 px-6 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Dibuat</th>
                                <th class="py-4 px-6 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($sekretaris as $index => $item)
                                <tr class="table-row-hover bg-white hover:bg-purple-50/50 transition-all duration-200"
                                    data-search="{{ strtolower($item->username . ' ' . ($item->grade->name ?? '')) }}">
                                    <td class="py-4 px-6 text-sm text-gray-700 row-number">{{ $sekretaris->firstItem() + $index }}</td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-3">
                                            <span class="text-sm font-medium text-gray-800">{{ $item->username }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                            {{ $item->grade->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-purple-100 text-purple-800">
                                            {{ ucfirst($item->role) }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-sm text-gray-500">
                                        <div class="flex items-center gap-2">
                                            <svg class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                            </svg>
                                            {{ $item->created_at->format('d M Y') }}
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <button type="button" onclick="openEditModal({{ $item->id }}, '{{ $item->username }}', {{ $item->grade_id ?? 'null' }})"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-purple-600 hover:bg-purple-50 hover:text-purple-800 transition-colors cursor-pointer" title="Edit">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                <span class="text-xs font-medium">Edit</span>
                                            </button>
                                            <button type="button" onclick="confirmDelete({{ $item->id }}, '{{ $item->username }}')"
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
                                    <td colspan="6" class="py-12 sm:py-16 px-4 sm:px-6 text-center">
                                        <div class="flex flex-col items-center gap-2 sm:gap-3">
                                            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-purple-50 flex items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 sm:h-8 sm:w-8 text-purple-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                                                </svg>
                                            </div>
                                            <p class="text-gray-400 text-sm sm:text-base font-medium">Tidak ada data sekretaris</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                            {{-- Hidden row for no search results --}}
                            <tr id="noSearchResultsRow" class="hidden">
                                <td colspan="6" class="py-12 sm:py-16 px-4 sm:px-6 text-center">
                                    <div class="flex flex-col items-center gap-2 sm:gap-3">
                                        <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-purple-50 flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 sm:h-8 sm:w-8 text-purple-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
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
            </div>

            {{-- Pagination --}}
            @if ($sekretaris->total() > 0)
                <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <p class="text-sm text-gray-500">
                        Menampilkan <span class="font-semibold text-gray-700">{{ $sekretaris->firstItem() }}-{{ $sekretaris->lastItem() }}</span> dari
                        <span class="font-semibold text-gray-700">{{ $sekretaris->total() }}</span> data
                    </p>

                    @if ($sekretaris->hasPages())
                        <nav class="flex items-center gap-1">
                            {{-- Previous --}}
                            @if ($sekretaris->onFirstPage())
                                <span class="px-3 py-2 rounded-lg text-gray-400 bg-gray-100 cursor-not-allowed">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                    </svg>
                                </span>
                            @else
                                <a href="{{ $sekretaris->previousPageUrl() }}"
                                    class="px-3 py-2 rounded-lg text-gray-600 bg-white border border-gray-200 hover:bg-purple-50 hover:border-purple-300 hover:text-purple-600 transition-all duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                    </svg>
                                </a>
                            @endif

                            {{-- Page Numbers --}}
                            @php
                                $start = max($sekretaris->currentPage() - 1, 1);
                                $end = min($sekretaris->currentPage() + 1, $sekretaris->lastPage());
                            @endphp

                            @if ($start > 1)
                                <a href="{{ $sekretaris->url(1) }}"
                                    class="px-3.5 py-2 rounded-lg text-sm text-gray-600 bg-white border border-gray-200 hover:bg-purple-50 hover:border-purple-300 hover:text-purple-600 transition-all duration-200">1</a>
                                @if ($start > 2)
                                    <span class="px-2 text-gray-400 text-sm">...</span>
                                @endif
                            @endif

                            @for ($i = $start; $i <= $end; $i++)
                                @if ($i == $sekretaris->currentPage())
                                    <span class="px-3.5 py-2 rounded-lg text-sm font-semibold text-white bg-purple-600 shadow-sm">{{ $i }}</span>
                                @else
                                    <a href="{{ $sekretaris->url($i) }}"
                                        class="px-3.5 py-2 rounded-lg text-sm text-gray-600 bg-white border border-gray-200 hover:bg-purple-50 hover:border-purple-300 hover:text-purple-600 transition-all duration-200">{{ $i }}</a>
                                @endif
                            @endfor

                            @if ($end < $sekretaris->lastPage())
                                @if ($end < $sekretaris->lastPage() - 1)
                                    <span class="px-2 text-gray-400 text-sm">...</span>
                                @endif
                                <a href="{{ $sekretaris->url($sekretaris->lastPage()) }}"
                                    class="px-3.5 py-2 rounded-lg text-sm text-gray-600 bg-white border border-gray-200 hover:bg-purple-50 hover:border-purple-300 hover:text-purple-600 transition-all duration-200">{{ $sekretaris->lastPage() }}</a>
                            @endif

                            {{-- Next --}}
                            @if ($sekretaris->hasMorePages())
                                <a href="{{ $sekretaris->nextPageUrl() }}"
                                    class="px-3 py-2 rounded-lg text-gray-600 bg-white border border-gray-200 hover:bg-purple-50 hover:border-purple-300 hover:text-purple-600 transition-all duration-200">
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

    {{-- MODAL TAMBAH SEKRETARIS --}}
    <div id="modal-create-sekretaris" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden z-50 p-4">
        <div class="bg-white p-6 rounded-xl shadow-xl max-w-md w-full relative">
            <h3 class="text-xl font-semibold mb-4">Tambah Sekretaris Baru</h3>
            <form action="{{ route('admin.sekretaris.store') }}" method="POST" class="space-y-4" id="createSekretarisForm">
                @csrf
                <input type="hidden" name="role" value="sekretaris">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                    <input type="text" name="username" id="username" value="{{ old('username') }}" required
                        placeholder="Masukkan username"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-purple-500 focus:border-purple-500">
                    @error('username')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label for="grade_id" class="block text-sm font-medium text-gray-700">Kelas</label>
                    <select name="grade_id" id="grade_id" required
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="">Pilih Kelas</option>
                        @foreach ($grades as $grade)
                            <option value="{{ $grade->id }}" {{ old('grade_id') == $grade->id ? 'selected' : '' }}>{{ $grade->name }}</option>
                        @endforeach
                    </select>
                    @error('grade_id')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                    <div class="relative flex items-center mt-1">
                        <input type="password" name="password" id="password" required
                            placeholder="Masukkan password (min. 6 karakter)"
                            class="block w-full border border-gray-300 rounded-md shadow-sm p-2 pr-10 focus:ring-purple-500 focus:border-purple-500">
                        <button type="button" onclick="togglePassword('password', this)"
                            class="absolute right-2 text-gray-400 hover:text-gray-600 transition-colors focus:outline-none cursor-pointer">
                            <span class="material-icons text-xl">visibility_off</span>
                        </button>
                    </div>
                    @error('password')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Password</label>
                    <div class="relative flex items-center mt-1">
                        <input type="password" name="password_confirmation" id="password_confirmation" required
                            placeholder="Konfirmasi password"
                            class="block w-full border border-gray-300 rounded-md shadow-sm p-2 pr-10 focus:ring-purple-500 focus:border-purple-500">
                        <button type="button" onclick="togglePassword('password_confirmation', this)"
                            class="absolute right-2 text-gray-400 hover:text-gray-600 transition-colors focus:outline-none cursor-pointer">
                            <span class="material-icons text-xl">visibility_off</span>
                        </button>
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-4">
                    <button type="button"
                        onclick="document.getElementById('modal-create-sekretaris').classList.add('hidden')"
                        class="bg-gray-300 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors cursor-pointer">Batal</button>
                    <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors cursor-pointer">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL EDIT SEKRETARIS --}}
    <div id="modal-edit-sekretaris" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden z-50 p-4">
        <div class="bg-white p-6 rounded-xl shadow-xl max-w-md w-full relative">
            <h3 class="text-xl font-semibold mb-4">Edit Sekretaris</h3>
            <form id="editSekretarisForm" method="POST" class="space-y-4">
                @csrf
                @method('PATCH')
                <div>
                    <label for="edit_username" class="block text-sm font-medium text-gray-700">Username</label>
                    <input type="text" name="username" id="edit_username" required
                        placeholder="Masukkan username"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-purple-500 focus:border-purple-500">
                </div>
                <div>
                    <label for="edit_grade_id" class="block text-sm font-medium text-gray-700">Kelas</label>
                    <select name="grade_id" id="edit_grade_id" required
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-purple-500 focus:border-purple-500">
                        <option value="">Pilih Kelas</option>
                        @foreach ($grades as $grade)
                            <option value="{{ $grade->id }}">{{ $grade->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="edit_password" class="block text-sm font-medium text-gray-700">Password Baru <span class="text-gray-400 font-normal">(Kosongkan jika tidak ingin mengubah)</span></label>
                    <div class="relative flex items-center mt-1">
                        <input type="password" name="password" id="edit_password"
                            placeholder="Masukkan password baru (min. 6 karakter)"
                            class="block w-full border border-gray-300 rounded-md shadow-sm p-2 pr-10 focus:ring-purple-500 focus:border-purple-500">
                        <button type="button" onclick="togglePasswordEdit('edit_password', this)"
                            class="absolute right-2 text-gray-400 hover:text-gray-600 transition-colors focus:outline-none cursor-pointer">
                            <span class="material-icons text-xl">visibility_off</span>
                        </button>
                    </div>
                </div>
                <div>
                    <label for="edit_password_confirmation" class="block text-sm font-medium text-gray-700">Konfirmasi Password Baru</label>
                    <div class="relative flex items-center mt-1">
                        <input type="password" name="password_confirmation" id="edit_password_confirmation"
                            placeholder="Konfirmasi password baru"
                            class="block w-full border border-gray-300 rounded-md shadow-sm p-2 pr-10 focus:ring-purple-500 focus:border-purple-500">
                        <button type="button" onclick="togglePasswordEdit('edit_password_confirmation', this)"
                            class="absolute right-2 text-gray-400 hover:text-gray-600 transition-colors focus:outline-none cursor-pointer">
                            <span class="material-icons text-xl">visibility_off</span>
                        </button>
                    </div>
                </div>
                <div class="flex justify-end gap-2 pt-4">
                    <button type="button"
                        onclick="document.getElementById('modal-edit-sekretaris').classList.add('hidden')"
                        class="bg-gray-300 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors cursor-pointer">Batal</button>
                    <button type="submit" class="bg-purple-600 text-white px-4 py-2 rounded-lg hover:bg-purple-700 transition-colors cursor-pointer">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL KONFIRMASI HAPUS --}}
    <div id="modal-delete-sekretaris" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden z-50 p-4">
        <div class="bg-white p-6 rounded-xl shadow-xl max-w-md w-full relative">
            <div class="flex items-center justify-center mb-4">
                <div class="bg-red-100 p-4 rounded-full">
                    <span class="material-icons text-red-600 text-4xl">warning</span>
                </div>
            </div>
            <h3 class="text-xl font-bold text-center text-gray-800 mb-2">Hapus Sekretaris?</h3>
            <p class="text-center text-gray-600 mb-6">
                Anda yakin ingin menghapus sekretaris <strong id="delete-sekretaris-name"></strong>? Tindakan ini tidak dapat dibatalkan.
            </p>
            <form id="deleteSekretarisForm" method="POST" class="space-y-4">
                @csrf
                @method('DELETE')
                <div class="flex justify-center gap-3">
                    <button type="button"
                        onclick="document.getElementById('modal-delete-sekretaris').classList.add('hidden')"
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

        // Search functionality
        document.getElementById('searchInput').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const rows = document.querySelectorAll('#sekretarisTable tbody tr[data-search]');
            const noSearchResultsRow = document.getElementById('noSearchResultsRow');
            const emptyDataRow = document.getElementById('emptyDataRow');
            let visibleIndex = 1;
            let visibleCount = 0;

            rows.forEach(row => {
                const searchData = row.getAttribute('data-search');
                if (searchData.includes(searchTerm)) {
                    row.style.display = '';
                    // Update row number for visible rows
                    const numberCell = row.querySelector('.row-number');
                    if (numberCell) {
                        numberCell.textContent = visibleIndex;
                    }
                    visibleIndex++;
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            // Show/hide no results message
            if (noSearchResultsRow) {
                if (visibleCount === 0 && rows.length > 0) {
                    noSearchResultsRow.classList.remove('hidden');
                } else {
                    noSearchResultsRow.classList.add('hidden');
                }
            }

            // Hide empty data row when searching
            if (emptyDataRow && rows.length > 0) {
                emptyDataRow.style.display = 'none';
            }
        });

        // Toggle password visibility
        function togglePassword(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('.material-icons');

            if (input.type === 'password') {
                input.type = 'text';
                icon.textContent = 'visibility';
            } else {
                input.type = 'password';
                icon.textContent = 'visibility_off';
            }
        }

        // Hide all passwords when form is submitted
        function hideAllPasswords() {
            const passwordFields = document.querySelectorAll('input[type="text"][name*="password"], input[type="text"][id*="password"]');
            passwordFields.forEach(field => {
                field.type = 'password';
                const button = field.parentElement.querySelector('button .material-icons');
                if (button) {
                    button.textContent = 'visibility_off';
                }
            });
        }

        // Add submit event listener to create sekretaris form
        const createSekretarisForm = document.getElementById('createSekretarisForm');
        if (createSekretarisForm) {
            createSekretarisForm.addEventListener('submit', function() {
                hideAllPasswords();
            });
        }

        // Confirm delete sekretaris
        function confirmDelete(sekretarisId, username) {
            document.getElementById('delete-sekretaris-name').textContent = username;
            document.getElementById('deleteSekretarisForm').action = '{{ url("admin/sekretaris") }}/' + sekretarisId;
            document.getElementById('modal-delete-sekretaris').classList.remove('hidden');
        }

        // Open edit modal
        function openEditModal(id, username, gradeId) {
            document.getElementById('edit_username').value = username;
            document.getElementById('edit_grade_id').value = gradeId || '';
            document.getElementById('edit_password').value = '';
            document.getElementById('edit_password_confirmation').value = '';
            document.getElementById('editSekretarisForm').action = '{{ url("admin/sekretaris") }}/' + id;
            document.getElementById('modal-edit-sekretaris').classList.remove('hidden');
        }

        // Toggle password visibility for edit form
        function togglePasswordEdit(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('.material-icons');

            if (input.type === 'password') {
                input.type = 'text';
                icon.textContent = 'visibility';
            } else {
                input.type = 'password';
                icon.textContent = 'visibility_off';
            }
        }

        // Add submit event listener to edit sekretaris form
        const editSekretarisForm = document.getElementById('editSekretarisForm');
        if (editSekretarisForm) {
            editSekretarisForm.addEventListener('submit', function() {
                // Reset password fields to password type before submit
                document.getElementById('edit_password').type = 'password';
                document.getElementById('edit_password_confirmation').type = 'password';
            });
        }

        // Close modal on click outside
        document.getElementById('modal-create-sekretaris').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });

        document.getElementById('modal-edit-sekretaris').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });

        document.getElementById('modal-delete-sekretaris').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });
    </script>
@endpush
