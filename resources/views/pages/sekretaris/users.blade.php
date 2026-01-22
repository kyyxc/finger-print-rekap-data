@extends('layouts.sidebar-sekretaris')

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
                <p class="text-gray-500 mt-1">Lihat data siswa sistem</p>
                @if(isset($kelasName) && $kelasName)
                    <span class="inline-flex items-center px-3 py-1 mt-2 rounded-full text-sm font-medium bg-emerald-100 text-emerald-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Kelas: {{ $kelasName }}
                    </span>
                @else
                    <span class="inline-flex items-center px-3 py-1 mt-2 rounded-full text-sm font-medium bg-red-100 text-red-800">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        Belum ditugaskan ke kelas
                    </span>
                @endif
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
                <form method="GET" action="{{ route('sekretaris.users') }}" class="relative w-full sm:max-w-md">
                    <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                        <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                            fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <input type="text" name="search" id="searchInput" placeholder="Cari NIS, nama, atau no telepon..."
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
                            </tr>
                        @empty
                            <tr id="emptyDataRow">
                                <td colspan="6" class="py-12 sm:py-16 px-4 sm:px-6 text-center">
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
                            <td colspan="6" class="py-12 sm:py-16 px-4 sm:px-6 text-center">
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
    </script>
@endpush
