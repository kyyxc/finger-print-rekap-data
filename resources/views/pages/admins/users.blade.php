@extends('layouts.main')

@section('title', 'Data Siswa')

@push('styles')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <style>
        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            padding: 0.5rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            background-color: #fff;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0.5em 1em;
        }
    </style>
@endpush

@section('content')
    <div class="min-h-screen bg-gray-100 p-6">
        <div class="max-w-6xl mx-auto bg-white shadow-xl rounded-2xl p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold mb-4">Data Siswa</h1>
                <div class="flex gap-4">
                    {{-- <form action="{{ route('admins.users.destroy.all') }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button class="bg-red-500 text-white px-4 py-2 rounded-xl hover:bg-red-600 w-fit">Delete All
                            Users</button>
                    </form> --}}
                    <a href="{{ route('admins.dashboard') }}"
                        class="bg-yellow-500 text-white px-4 py-2 rounded-xl hover:bg-yellow-600 w-fit">Back</a>
                </div>
            </div>

            @if (session('message'))
                <div class="mb-4 p-3 rounded-xl bg-green-100 text-green-800 shadow">
                    {{ session('message') }}
                </div>
            @endif

            <div class="overflow-x-auto">
                <table id="usersTable" class="display w-full">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="px-4 py-2 text-left">NIS</th>
                            <th class="px-4 py-2 text-left">Nama</th>
                            <th class="px-4 py-2 text-left">Kelas</th>
                            <th class="px-4 py-2 text-left">Photo</th>
                            <th class="px-4 py-2 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                            <tr class="border-t">
                                <td class="px-4 py-2">{{ $user->nis }}</td>
                                <td class="px-4 py-2">{{ $user->nama ?? '-' }}</td>
                                <td class="px-4 py-2">{{ $user->kelas ?? '-' }}</td>
                                <td class="px-4 py-2">
                                    @if ($user->photo)
                                        <a href="{{ asset('storage/' . $user->photo) }}" target="_blank">
                                            <img src="{{ asset('storage/' . $user->photo) }}"
                                                class="w-12 h-12 rounded-full object-cover">
                                        </a>
                                    @else
                                        <a href="{{ asset('default/default.jpg') }}" target="_blank">
                                            <img src="{{ asset('default/default.jpg') }}"
                                                class="w-12 h-12 rounded-full object-cover">
                                        </a>
                                    @endif
                                </td>
                                <td class="px-4 py-2">
                                    <form method="POST" action="{{ route('admins.users.destroy', $user->id) }}"
                                        onsubmit="return confirm('Yakin ingin hapus user ini?')" class="flex justify-center">
                                        @csrf
                                        @method('DELETE')
                                        <button class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 text-sm">
                                            Hapus
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-gray-500">Tidak ada data siswa ditemukan</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#usersTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
                },
            });
        });
    </script>
@endpush
