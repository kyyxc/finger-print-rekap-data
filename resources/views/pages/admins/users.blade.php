@extends('layouts.main')

@section('title', 'Data Siswa')

@section('content')
    <div class="min-h-screen bg-gray-100 p-6">
        <div class="max-w-6xl mx-auto bg-white shadow-xl rounded-2xl p-6">
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold mb-4">Data Siswa</h1>
                <a href="{{ route('admins.dashboard') }}"
                    class="bg-red-500 text-white px-4 py-2 rounded-xl hover:bg-red-600 w-fit">Back</a>
            </div>

            {{-- Flash Message --}}
            @if (session('message'))
                <div class="mb-4 p-3 rounded-xl bg-green-100 text-green-800 shadow">
                    {{ session('message') }}
                </div>
            @endif

            {{-- Search Form --}}
            <form method="GET" class="flex flex-col md:flex-row gap-4 mb-6">
                <input type="text" name="search" placeholder="Cari NIS, Nama, atau Kelas" value="{{ request('search') }}"
                    class="p-2 border border-gray-300 rounded-xl flex-1">

                <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-xl hover:bg-blue-600 w-fit">
                    Cari
                </button>
            </form>

            {{-- Table --}}
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-300 rounded-xl">
                    <thead class="bg-gray-200">
                        <tr>
                            <th class="px-4 py-2 text-left">NIS</th>
                            <th class="px-4 py-2 text-left">Nama</th>
                            <th class="px-4 py-2 text-left">Kelas</th>
                            <th class="px-4 py-2 text-left">Photo</th>
                            <th class="px-4 py-2 text-left">Aksi</th>
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
                                        onsubmit="return confirm('Yakin ingin hapus user ini?')">
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

            {{-- Pagination --}}
            <div class="mt-6">
                {{ $users->appends(request()->query())->links() }}
            </div>
        </div>
    </div>
@endsection
