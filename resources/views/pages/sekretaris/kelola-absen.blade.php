@extends('layouts.sidebar-sekretaris')

@section('title', 'Kelola Absen Kelas')

@push('styles')
    <style>
        .table-row-hover {
            transition: all 0.2s ease;
        }

        .table-row-hover:hover {
            transform: translateX(4px);
            box-shadow: -4px 0 0 0 #3b82f6;
        }

        .status-badge {
            transition: all 0.2s ease;
        }
    </style>
@endpush

@section('content')
    <div class="max-w-7xl mx-auto h-full flex flex-col">
        {{-- HEADER --}}
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 flex-shrink-0">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Kelola Absen Kelas</h1>
                <p class="text-gray-500 mt-1">Kelola kehadiran siswa per kelas dan tanggal</p>
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

        {{-- FILTER SECTION --}}
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6 flex-shrink-0">
            <div class="flex flex-col sm:flex-row items-start sm:items-end gap-4">
                <div class="flex-1">
                    <div class="mb-2">
                        <span class="text-sm font-medium text-gray-700">Kelas Anda:</span>
                        <span class="ml-2 inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-blue-100 text-blue-800">
                            {{ $kelasName }}
                        </span>
                    </div>
                </div>
                
                <form action="{{ route('sekretaris.kelola-absen') }}" method="GET" class="flex flex-col sm:flex-row gap-4 items-end flex-1">
                    <div class="flex-1 w-full">
                        <label for="tanggal" class="block text-sm font-medium text-gray-700 mb-2">Pilih Tanggal</label>
                        <input type="date" name="tanggal" id="tanggal" value="{{ $selectedDate }}" required
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    </div>

                    <div>
                        <button type="submit"
                            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-lg font-semibold transition-colors shadow-sm">
                            <span class="material-icons text-xl">search</span>
                            Tampilkan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- DATA TABLE --}}
        @if ($students->count() > 0)
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 flex-1 min-h-0 flex flex-col overflow-hidden p-6">
                <div class="mb-4 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-800">
                        Daftar Siswa Kelas {{ $kelasName }}
                    </h3>
                    <span class="text-sm text-gray-500">
                        Total: {{ $students->count() }} siswa
                    </span>
                </div>

                {{-- Table --}}
                <div class="flex-1 min-h-0 overflow-auto rounded-xl border border-gray-200 shadow-sm">
                    <table class="w-full">
                        <thead class="sticky top-0 z-10 bg-gray-100 shadow-sm">
                            <tr class="bg-gradient-to-r from-gray-50 to-gray-100">
                                <th class="py-4 px-6 text-left text-xs font-bold text-gray-600 uppercase tracking-wider bg-gray-100">No</th>
                                <th class="py-4 px-6 text-left text-xs font-bold text-gray-600 uppercase tracking-wider bg-gray-100">NIS</th>
                                <th class="py-4 px-6 text-left text-xs font-bold text-gray-600 uppercase tracking-wider bg-gray-100">Nama</th>
                                <th class="py-4 px-6 text-center text-xs font-bold text-gray-600 uppercase tracking-wider bg-gray-100">Status</th>
                                <th class="py-4 px-6 text-center text-xs font-bold text-gray-600 uppercase tracking-wider bg-gray-100">Waktu</th>
                                <th class="py-4 px-6 text-center text-xs font-bold text-gray-600 uppercase tracking-wider bg-gray-100">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($students as $index => $student)
                                <tr class="table-row-hover bg-white hover:bg-blue-50/50 transition-all duration-200">
                                    <td class="py-4 px-6 text-sm text-gray-700">{{ $index + 1 }}</td>
                                    <td class="py-4 px-6">
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-800">
                                            {{ $student->nis }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-6">
                                        <span class="text-sm font-medium text-gray-800">{{ $student->nama ?? '-' }}</span>
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        @if ($student->attendance_status)
                                            @php
                                                $statusColors = [
                                                    'hadir' => 'bg-green-100 text-green-800',
                                                    'izin' => 'bg-yellow-100 text-yellow-800',
                                                    'sakit' => 'bg-blue-100 text-blue-800',
                                                    'alpha' => 'bg-red-100 text-red-800',
                                                ];
                                                $colorClass = $statusColors[$student->attendance_status] ?? 'bg-gray-100 text-gray-800';
                                            @endphp
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $colorClass }} status-badge">
                                                {{ ucfirst($student->attendance_status) }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">
                                                Belum Absen
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-4 px-6 text-center text-sm text-gray-600">
                                        {{ $student->record_time ? \Carbon\Carbon::parse($student->record_time)->format('H:i') : '-' }}
                                    </td>
                                    <td class="py-4 px-6 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <button type="button" 
                                                onclick="openAbsenModal({{ $student->id }}, '{{ $student->nama }}', '{{ $student->attendance_status }}', '{{ $selectedDate }}', '{{ $student->record_time ? \Carbon\Carbon::parse($student->record_time)->format('H:i') : '' }}')"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-blue-600 hover:bg-blue-50 hover:text-blue-800 transition-colors cursor-pointer" 
                                                title="{{ $student->attendance_status ? 'Edit' : 'Tambah' }} Absen">
                                                <span class="material-icons text-sm">{{ $student->attendance_status ? 'edit' : 'add_circle' }}</span>
                                                <span class="text-xs font-medium">{{ $student->attendance_status ? 'Edit' : 'Absen' }}</span>
                                            </button>
                                            
                                            @if ($student->attendance_id)
                                                <form action="{{ route('sekretaris.kelola-absen.delete', $student->attendance_id) }}" 
                                                    method="POST" 
                                                    onsubmit="return confirm('Yakin ingin menghapus data absensi ini?')"
                                                    class="inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-red-600 hover:bg-red-50 hover:text-red-800 transition-colors cursor-pointer" 
                                                        title="Hapus Absen">
                                                        <span class="material-icons text-sm">delete</span>
                                                        <span class="text-xs font-medium">Hapus</span>
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
                <div class="flex flex-col items-center gap-3">
                    <div class="w-16 h-16 rounded-full bg-blue-50 flex items-center justify-center">
                        <span class="material-icons text-blue-300 text-4xl">school</span>
                    </div>
                    <p class="text-gray-400 text-base font-medium">Tidak ada siswa di kelas {{ $kelasName }}</p>
                </div>
            </div>
        @endif
    </div>

    {{-- MODAL ABSEN --}}
    <div id="modal-absen" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden z-50 p-4">
        <div class="bg-white p-6 rounded-xl shadow-xl max-w-md w-full relative">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-xl font-semibold flex items-center gap-2">
                    <span class="material-icons text-blue-600">edit_calendar</span>
                    <span id="modal-title">Kelola Absensi</span>
                </h3>
                <button type="button" onclick="closeAbsenModal()"
                    class="text-gray-400 hover:text-gray-600">
                    <span class="material-icons">close</span>
                </button>
            </div>

            <div class="mb-4 p-3 bg-blue-50 rounded-lg">
                <p class="text-sm text-gray-700">
                    <span class="font-semibold">Siswa:</span> <span id="modal-student-name"></span>
                </p>
            </div>

            <form id="absenForm" action="{{ route('sekretaris.kelola-absen.update') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="user_id" id="modal-user-id">
                <input type="hidden" name="tanggal" id="modal-tanggal">

                <div>
                    <label for="modal-status" class="block text-sm font-medium text-gray-700 mb-2">Status Kehadiran</label>
                    <select name="status" id="modal-status" required
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        <option value="">-- Pilih Status --</option>
                        <option value="hadir">Hadir</option>
                        <option value="izin">Izin</option>
                        <option value="sakit">Sakit</option>
                        <option value="alpha">Alpha</option>
                    </select>
                </div>

                <div>
                    <label for="modal-waktu" class="block text-sm font-medium text-gray-700 mb-2">Waktu</label>
                    <input type="time" name="waktu" id="modal-waktu" 
                        class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Kosongkan untuk menggunakan waktu saat ini</p>
                </div>

                <div class="flex justify-end gap-2 pt-4">
                    <button type="button" onclick="closeAbsenModal()"
                        class="px-4 py-2 rounded-lg bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium transition-colors">
                        Batal
                    </button>
                    <button type="submit" 
                        class="px-4 py-2 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-medium transition-colors">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openAbsenModal(userId, studentName, currentStatus, tanggal, waktu) {
            document.getElementById('modal-user-id').value = userId;
            document.getElementById('modal-student-name').textContent = studentName;
            document.getElementById('modal-tanggal').value = tanggal;
            document.getElementById('modal-status').value = currentStatus || '';
            document.getElementById('modal-waktu').value = waktu || '';
            
            const modalTitle = currentStatus ? 'Edit Absensi' : 'Tambah Absensi';
            document.getElementById('modal-title').textContent = modalTitle;
            
            document.getElementById('modal-absen').classList.remove('hidden');
        }

        function closeAbsenModal() {
            document.getElementById('modal-absen').classList.add('hidden');
            document.getElementById('absenForm').reset();
        }

        // Close modal on click outside
        document.getElementById('modal-absen').addEventListener('click', function(e) {
            if (e.target === this) {
                closeAbsenModal();
            }
        });
    </script>
@endpush
