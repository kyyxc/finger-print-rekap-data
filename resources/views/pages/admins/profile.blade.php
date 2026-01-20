@extends('layouts.sidebar')

@section('title', 'Profile')

@section('content')
    <div class="max-w-4xl mx-auto">
        {{-- HEADER --}}
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Profile</h1>
            <p class="text-gray-500 mt-1">Kelola informasi akun Anda</p>
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

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- INFORMASI AKUN --}}
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center gap-3 mb-6">
                    <div class="bg-blue-100 p-3 rounded-full">
                        <span class="material-icons text-blue-600 text-2xl">account_circle</span>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">Informasi Akun</h2>
                        <p class="text-sm text-gray-500">Detail akun yang sedang login</p>
                    </div>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Username</label>
                        <div class="flex items-center gap-2 px-4 py-3 bg-gray-50 rounded-lg border border-gray-200">
                            <span class="material-icons text-gray-400 text-xl">person</span>
                            <span class="text-gray-800 font-medium">{{ auth()->guard('role')->user()->username }}</span>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-600 mb-1">Role</label>
                        <div class="flex items-center gap-2 px-4 py-3 bg-gray-50 rounded-lg border border-gray-200">
                            <span class="material-icons text-gray-400 text-xl">badge</span>
                            <span class="text-gray-800 font-medium capitalize">
                                @php
                                    $role = auth()->guard('role')->user()->role ?? 'admin';
                                @endphp
                                @if ($role === 'admin')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Admin
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        Sekretaris
                                    </span>
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- GANTI PASSWORD --}}
            <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                <div class="flex items-center gap-3 mb-6">
                    <div class="bg-yellow-100 p-3 rounded-full">
                        <span class="material-icons text-yellow-600 text-2xl">lock</span>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-800">Ganti Password</h2>
                        <p class="text-sm text-gray-500">Perbarui password akun Anda</p>
                    </div>
                </div>

                <form action="{{ route('admin.change-password') }}" method="POST" class="space-y-4" id="changePasswordForm">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label for="current_password" class="block text-sm font-medium text-gray-600 mb-1">Password Saat Ini</label>
                        <div class="relative flex items-center">
                            <span class="material-icons absolute left-3 text-gray-400 pointer-events-none">lock_outline</span>
                            <input type="password" name="current_password" id="current_password" required
                                class="w-full pl-11 pr-11 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                placeholder="Masukkan password saat ini">
                            <button type="button" onclick="togglePassword('current_password', this)"
                                class="absolute right-3 text-gray-400 hover:text-gray-600 transition-colors focus:outline-none cursor-pointer">
                                <span class="material-icons">visibility_off</span>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label for="new_password" class="block text-sm font-medium text-gray-600 mb-1">Password Baru</label>
                        <div class="relative flex items-center">
                            <span class="material-icons absolute left-3 text-gray-400 pointer-events-none">lock</span>
                            <input type="password" name="new_password" id="new_password" required minlength="6"
                                class="w-full pl-11 pr-11 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                placeholder="Masukkan password baru (min. 6 karakter)">
                            <button type="button" onclick="togglePassword('new_password', this)"
                                class="absolute right-3 text-gray-400 hover:text-gray-600 transition-colors focus:outline-none cursor-pointer">
                                <span class="material-icons">visibility_off</span>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label for="new_password_confirmation" class="block text-sm font-medium text-gray-600 mb-1">Konfirmasi Password Baru</label>
                        <div class="relative flex items-center">
                            <span class="material-icons absolute left-3 text-gray-400 pointer-events-none">lock_reset</span>
                            <input type="password" name="new_password_confirmation" id="new_password_confirmation" required minlength="6"
                                class="w-full pl-11 pr-11 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors"
                                placeholder="Konfirmasi password baru">
                            <button type="button" onclick="togglePassword('new_password_confirmation', this)"
                                class="absolute right-3 text-gray-400 hover:text-gray-600 transition-colors focus:outline-none cursor-pointer">
                                <span class="material-icons">visibility_off</span>
                            </button>
                        </div>
                    </div>

                    <button type="submit"
                        class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition-colors font-medium cursor-pointer">
                        <span class="material-icons text-xl">save</span>
                        <span>Simpan Password</span>
                    </button>
                </form>
            </div>
        </div>

        {{-- HAPUS AKUN (HANYA UNTUK ADMIN) --}}
        @php
            $userRole = auth()->guard('role')->user()->role ?? 'admin';
        @endphp

        @if ($userRole === 'admin')
            <div class="mt-6 bg-white rounded-xl shadow-sm p-6 border border-red-200">
                <div class="flex items-center gap-3 mb-6">
                    <div class="bg-red-100 p-3 rounded-full">
                        <span class="material-icons text-red-600 text-2xl">warning</span>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-red-800">Zona Berbahaya</h2>
                        <p class="text-sm text-red-500">Tindakan di bawah ini tidak dapat dibatalkan</p>
                    </div>
                </div>

                <div class="bg-red-50 rounded-lg p-4 mb-4">
                    <p class="text-sm text-red-700">
                        <strong>Peringatan:</strong> Menghapus akun akan menghapus semua data yang terkait dengan akun Anda secara permanen.
                        Pastikan Anda yakin sebelum melanjutkan.
                    </p>
                </div>

                <button type="button" id="deleteAccountBtn"
                    class="flex items-center justify-center gap-2 px-4 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors font-medium cursor-pointer">
                    <span class="material-icons text-xl">delete_forever</span>
                    <span>Hapus Akun Saya</span>
                </button>
            </div>

            {{-- MODAL KONFIRMASI HAPUS AKUN --}}
            <div id="deleteAccountModal" class="fixed inset-0 z-50 hidden">
                <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" id="modalOverlay"></div>
                <div class="flex items-center justify-center min-h-screen p-4">
                    <div class="relative bg-white rounded-xl shadow-2xl max-w-md w-full p-6 z-10">
                        <div class="flex items-center justify-center mb-4">
                            <div class="bg-red-100 p-4 rounded-full">
                                <span class="material-icons text-red-600 text-4xl">warning</span>
                            </div>
                        </div>

                        <h3 class="text-xl font-bold text-center text-gray-800 mb-2">Hapus Akun?</h3>
                        <p class="text-center text-gray-600 mb-6">
                            Tindakan ini tidak dapat dibatalkan. Masukkan password Anda untuk mengkonfirmasi penghapusan akun.
                        </p>

                        <form action="{{ route('admin.delete') }}" method="POST" class="space-y-4" id="deleteAccountForm">
                            @csrf
                            @method('DELETE')

                            <div>
                                <label for="delete_password" class="block text-sm font-medium text-gray-600 mb-1">Password</label>
                                <div class="relative flex items-center">
                                    <span class="material-icons absolute left-3 text-gray-400 pointer-events-none">lock</span>
                                    <input type="password" name="password" id="delete_password" required
                                        class="w-full pl-11 pr-11 py-3 border border-gray-200 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500 transition-colors"
                                        placeholder="Masukkan password Anda">
                                    <button type="button" onclick="togglePassword('delete_password', this)"
                                        class="absolute right-3 text-gray-400 hover:text-gray-600 transition-colors focus:outline-none cursor-pointer">
                                        <span class="material-icons">visibility_off</span>
                                    </button>
                                </div>
                            </div>

                            <div class="flex gap-3">
                                <button type="button" id="cancelDeleteBtn"
                                    class="flex-1 px-4 py-3 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors font-medium cursor-pointer">
                                    Batal
                                </button>
                                <button type="submit"
                                    class="flex-1 px-4 py-3 bg-red-600 hover:bg-red-700 text-white rounded-lg transition-colors font-medium cursor-pointer">
                                    Ya, Hapus Akun
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @else
            {{-- INFO UNTUK SEKRETARIS --}}
            <div class="mt-6 bg-white rounded-xl shadow-sm p-6 border border-gray-200">
                <div class="flex items-center gap-3">
                    <div class="bg-gray-100 p-3 rounded-full">
                        <span class="material-icons text-gray-500 text-2xl">info</span>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-gray-700">Informasi</h2>
                        <p class="text-sm text-gray-500">Hanya admin yang dapat menghapus akun. Hubungi admin jika Anda ingin menghapus akun Anda.</p>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
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

    // Add submit event listener to change password form
    const changePasswordForm = document.getElementById('changePasswordForm');
    if (changePasswordForm) {
        changePasswordForm.addEventListener('submit', function() {
            hideAllPasswords();
        });
    }

    // Add submit event listener to delete account form
    const deleteAccountForm = document.getElementById('deleteAccountForm');
    if (deleteAccountForm) {
        deleteAccountForm.addEventListener('submit', function() {
            hideAllPasswords();
        });
    }

    // Modal handling
    const deleteAccountBtn = document.getElementById('deleteAccountBtn');
    const deleteAccountModal = document.getElementById('deleteAccountModal');
    const modalOverlay = document.getElementById('modalOverlay');
    const cancelDeleteBtn = document.getElementById('cancelDeleteBtn');

    if (deleteAccountBtn) {
        deleteAccountBtn.addEventListener('click', function() {
            deleteAccountModal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        });
    }

    if (cancelDeleteBtn) {
        cancelDeleteBtn.addEventListener('click', function() {
            deleteAccountModal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        });
    }

    if (modalOverlay) {
        modalOverlay.addEventListener('click', function() {
            deleteAccountModal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        });
    }

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && deleteAccountModal && !deleteAccountModal.classList.contains('hidden')) {
            deleteAccountModal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    });
</script>
@endpush
