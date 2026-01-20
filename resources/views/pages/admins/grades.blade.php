@extends('layouts.sidebar')

@section('title', 'Kelola Kelas')

@push('styles')
    <style>
        .table-row-hover {
            transition: all 0.2s ease;
        }

        .table-row-hover:hover {
            transform: translateX(4px);
            box-shadow: -4px 0 0 0 #22c55e;
        }

        .search-input:focus {
            box-shadow: 0 0 0 3px rgba(34, 197, 94, 0.15);
        }
    </style>
@endpush

@section('content')
    <div class="max-w-7xl mx-auto h-full flex flex-col">
        {{-- HEADER --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8 flex-shrink-0">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Kelola Kelas</h1>
                <p class="text-gray-500 mt-1">Manajemen data kelas/grade</p>
            </div>
            <button onclick="document.getElementById('modal-create-grade').classList.remove('hidden')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium transition-colors cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                </svg>
                Tambah Kelas
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
                    <input type="text" id="searchInput" placeholder="Cari nama kelas..."
                        class="search-input w-full pl-12 pr-4 py-3 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-700 placeholder-gray-400 focus:outline-none focus:border-green-500 focus:bg-white transition-all duration-200">
                </div>

                {{-- Limit Selector --}}
                <div class="flex items-center gap-3">
                    <label for="perPageSelect" class="text-sm text-gray-600 font-medium whitespace-nowrap">Tampilkan:</label>
                    <select id="perPageSelect" onchange="changePerPage(this.value)"
                        class="px-4 py-2.5 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-700 focus:outline-none focus:border-green-500 focus:bg-white transition-all duration-200 cursor-pointer">
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
                    <table id="gradesTable" class="w-full">
                        <thead>
                            <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
                                <th class="py-4 px-6 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">No</th>
                                <th class="py-4 px-6 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Nama Kelas</th>
                                <th class="py-4 px-6 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Jumlah Siswa</th>
                                <th class="py-4 px-6 text-left text-xs font-bold text-gray-600 uppercase tracking-wider">Dibuat</th>
                                <th class="py-4 px-6 text-center text-xs font-bold text-gray-600 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse ($grades as $index => $grade)
                                <tr class="table-row-hover bg-white hover:bg-green-50/50 transition-all duration-200"
                                    data-search="{{ strtolower($grade->name) }}">
                                    <td class="py-4 px-6 text-sm text-gray-700 row-number">{{ $grades->firstItem() + $index }}</td>
                                    <td class="py-4 px-6">
                                        <div class="flex items-center gap-3">
                                            <span class="text-sm font-medium text-gray-800">{{ $grade->name }}</span>
                                        </div>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">
                                            {{ $grade->users_count }} Siswa
                                        </span>
                                    </td>
                                    <td class="py-4 px-6 text-sm text-gray-500">
                                        <div class="flex items-center gap-2">
                                            <svg class="h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M6 2a1 1 0 00-1 1v1H4a2 2 0 00-2 2v10a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2h-1V3a1 1 0 10-2 0v1H7V3a1 1 0 00-1-1zm0 5a1 1 0 000 2h8a1 1 0 100-2H6z" clip-rule="evenodd" />
                                            </svg>
                                            {{ $grade->created_at->format('d M Y') }}
                                        </div>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <button type="button" onclick="openEditModal({{ $grade->id }}, '{{ $grade->name }}')"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-blue-600 hover:bg-blue-50 hover:text-blue-800 transition-colors cursor-pointer" title="Edit">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                                                    stroke="currentColor" stroke-width="2">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                <span class="text-xs font-medium">Edit</span>
                                            </button>
                                            <button type="button" onclick="confirmDelete({{ $grade->id }}, '{{ $grade->name }}', {{ $grade->users_count }})"
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
                                    <td colspan="5" class="py-12 sm:py-16 px-4 sm:px-6 text-center">
                                        <div class="flex flex-col items-center gap-2 sm:gap-3">
                                            <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-green-50 flex items-center justify-center">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 sm:h-8 sm:w-8 text-green-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                                </svg>
                                            </div>
                                            <p class="text-gray-400 text-sm sm:text-base font-medium">Tidak ada data kelas</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                            {{-- Hidden row for no search results --}}
                            <tr id="noSearchResultsRow" class="hidden">
                                <td colspan="5" class="py-12 sm:py-16 px-4 sm:px-6 text-center">
                                    <div class="flex flex-col items-center gap-2 sm:gap-3">
                                        <div class="w-14 h-14 sm:w-16 sm:h-16 rounded-full bg-green-50 flex items-center justify-center">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-7 w-7 sm:h-8 sm:w-8 text-green-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
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
            @if ($grades->total() > 0)
                <div class="mt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <p class="text-sm text-gray-500">
                        Menampilkan <span class="font-semibold text-gray-700">{{ $grades->firstItem() }}-{{ $grades->lastItem() }}</span> dari
                        <span class="font-semibold text-gray-700">{{ $grades->total() }}</span> data
                    </p>

                    @if ($grades->hasPages())
                        <nav class="flex items-center gap-1">
                            {{-- Previous --}}
                            @if ($grades->onFirstPage())
                                <span class="px-3 py-2 rounded-lg text-gray-400 bg-gray-100 cursor-not-allowed">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                    </svg>
                                </span>
                            @else
                                <a href="{{ $grades->previousPageUrl() }}"
                                    class="px-3 py-2 rounded-lg text-gray-600 bg-white border border-gray-200 hover:bg-green-50 hover:border-green-300 hover:text-green-600 transition-all duration-200">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                                    </svg>
                                </a>
                            @endif

                            {{-- Page Numbers --}}
                            @php
                                $start = max($grades->currentPage() - 1, 1);
                                $end = min($grades->currentPage() + 1, $grades->lastPage());
                            @endphp

                            @if ($start > 1)
                                <a href="{{ $grades->url(1) }}"
                                    class="px-3.5 py-2 rounded-lg text-sm text-gray-600 bg-white border border-gray-200 hover:bg-green-50 hover:border-green-300 hover:text-green-600 transition-all duration-200">1</a>
                                @if ($start > 2)
                                    <span class="px-2 text-gray-400 text-sm">...</span>
                                @endif
                            @endif

                            @for ($i = $start; $i <= $end; $i++)
                                @if ($i == $grades->currentPage())
                                    <span class="px-3.5 py-2 rounded-lg text-sm font-semibold text-white bg-green-600 shadow-sm">{{ $i }}</span>
                                @else
                                    <a href="{{ $grades->url($i) }}"
                                        class="px-3.5 py-2 rounded-lg text-sm text-gray-600 bg-white border border-gray-200 hover:bg-green-50 hover:border-green-300 hover:text-green-600 transition-all duration-200">{{ $i }}</a>
                                @endif
                            @endfor

                            @if ($end < $grades->lastPage())
                                @if ($end < $grades->lastPage() - 1)
                                    <span class="px-2 text-gray-400 text-sm">...</span>
                                @endif
                                <a href="{{ $grades->url($grades->lastPage()) }}"
                                    class="px-3.5 py-2 rounded-lg text-sm text-gray-600 bg-white border border-gray-200 hover:bg-green-50 hover:border-green-300 hover:text-green-600 transition-all duration-200">{{ $grades->lastPage() }}</a>
                            @endif

                            {{-- Next --}}
                            @if ($grades->hasMorePages())
                                <a href="{{ $grades->nextPageUrl() }}"
                                    class="px-3 py-2 rounded-lg text-gray-600 bg-white border border-gray-200 hover:bg-green-50 hover:border-green-300 hover:text-green-600 transition-all duration-200">
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

    {{-- MODAL TAMBAH KELAS --}}
    <div id="modal-create-grade" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden z-50 p-4">
        <div class="bg-white p-6 rounded-xl shadow-xl max-w-md w-full relative">
            <h3 class="text-xl font-semibold mb-4">Tambah Kelas Baru</h3>
            <form action="{{ route('admin.grades.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nama Kelas</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        placeholder="Contoh: X RPL 1"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-green-500 focus:border-green-500">
                    @error('name')<span class="text-red-500 text-xs">{{ $message }}</span>@enderror
                </div>
                <div class="flex justify-end gap-2 pt-4">
                    <button type="button"
                        onclick="document.getElementById('modal-create-grade').classList.add('hidden')"
                        class="bg-gray-300 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors cursor-pointer">Batal</button>
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors cursor-pointer">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL EDIT KELAS --}}
    <div id="modal-edit-grade" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden z-50 p-4">
        <div class="bg-white p-6 rounded-xl shadow-xl max-w-md w-full relative">
            <h3 class="text-xl font-semibold mb-4">Edit Kelas</h3>
            <form id="editGradeForm" method="POST" class="space-y-4">
                @csrf
                @method('PATCH')
                <div>
                    <label for="edit_name" class="block text-sm font-medium text-gray-700">Nama Kelas</label>
                    <input type="text" name="name" id="edit_name" required
                        placeholder="Contoh: X RPL 1"
                        class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm p-2 focus:ring-green-500 focus:border-green-500">
                </div>
                <div class="flex justify-end gap-2 pt-4">
                    <button type="button"
                        onclick="document.getElementById('modal-edit-grade').classList.add('hidden')"
                        class="bg-gray-300 px-4 py-2 rounded-lg hover:bg-gray-400 transition-colors cursor-pointer">Batal</button>
                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition-colors cursor-pointer">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL KONFIRMASI HAPUS --}}
    <div id="modal-delete-grade" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden z-50 p-4">
        <div class="bg-white p-6 rounded-xl shadow-xl max-w-md w-full relative">
            <div class="flex items-center justify-center mb-4">
                <div class="bg-red-100 p-4 rounded-full">
                    <span class="material-icons text-red-600 text-4xl">warning</span>
                </div>
            </div>
            <h3 class="text-xl font-bold text-center text-gray-800 mb-2">Hapus Kelas?</h3>
            <p class="text-center text-gray-600 mb-6" id="delete-message">
                Anda yakin ingin menghapus kelas <strong id="delete-grade-name"></strong>? Tindakan ini tidak dapat dibatalkan.
            </p>
            <form id="deleteGradeForm" method="POST" class="space-y-4">
                @csrf
                @method('DELETE')
                <div class="flex justify-center gap-3">
                    <button type="button"
                        onclick="document.getElementById('modal-delete-grade').classList.add('hidden')"
                        class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-700 rounded-lg transition-colors cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" id="delete-submit-btn"
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
            const rows = document.querySelectorAll('#gradesTable tbody tr[data-search]');
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

        // Open edit modal
        function openEditModal(gradeId, gradeName) {
            document.getElementById('edit_name').value = gradeName;
            document.getElementById('editGradeForm').action = '{{ url("admin/grades") }}/' + gradeId;
            document.getElementById('modal-edit-grade').classList.remove('hidden');
        }

        // Confirm delete grade
        function confirmDelete(gradeId, gradeName, usersCount) {
            document.getElementById('delete-grade-name').textContent = gradeName;
            document.getElementById('deleteGradeForm').action = '{{ url("admin/grades") }}/' + gradeId;

            const deleteBtn = document.getElementById('delete-submit-btn');
            const deleteMessage = document.getElementById('delete-message');

            if (usersCount > 0) {
                deleteMessage.innerHTML = 'Kelas <strong>' + gradeName + '</strong> memiliki <strong>' + usersCount + ' siswa</strong>. Anda harus memindahkan atau menghapus siswa terlebih dahulu sebelum menghapus kelas ini.';
                deleteBtn.disabled = true;
                deleteBtn.classList.add('opacity-50', 'cursor-not-allowed');
                deleteBtn.classList.remove('hover:bg-red-700');
            } else {
                deleteMessage.innerHTML = 'Anda yakin ingin menghapus kelas <strong>' + gradeName + '</strong>? Tindakan ini tidak dapat dibatalkan.';
                deleteBtn.disabled = false;
                deleteBtn.classList.remove('opacity-50', 'cursor-not-allowed');
                deleteBtn.classList.add('hover:bg-red-700');
            }

            document.getElementById('modal-delete-grade').classList.remove('hidden');
        }

        // Close modal on click outside
        document.getElementById('modal-create-grade').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });

        document.getElementById('modal-edit-grade').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });

        document.getElementById('modal-delete-grade').addEventListener('click', function(e) {
            if (e.target === this) {
                this.classList.add('hidden');
            }
        });
    </script>
@endpush
