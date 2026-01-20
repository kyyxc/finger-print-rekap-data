<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>@yield('title')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
    @stack('styles')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 overflow-hidden">
    <div class="flex h-screen">
        {{-- MOBILE HEADER --}}
        <div class="lg:hidden fixed top-0 left-0 right-0 z-40 bg-slate-800 text-white px-4 py-3 flex items-center justify-between shadow-lg">
            <div class="flex items-center gap-2">
                <span class="material-icons text-blue-400">fingerprint</span>
                <span class="text-lg font-bold">Rekap Absensi</span>
            </div>
            <button id="mobile-menu-btn" type="button" class="p-2 rounded-lg hover:bg-slate-700 transition-colors">
                <span class="material-icons">menu</span>
            </button>
        </div>

        {{-- OVERLAY --}}
        <div id="sidebar-overlay" class="fixed inset-0 bg-black/50 z-40 lg:hidden hidden"></div>

        {{-- SIDEBAR --}}
        <aside id="sidebar" class="w-64 bg-slate-800 text-white flex flex-col fixed h-full z-50 transition-transform duration-300 -translate-x-full lg:translate-x-0 overflow-hidden">
            {{-- APP TITLE --}}
            <div class="p-6 border-b border-slate-700 flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <span class="material-icons text-blue-400 text-3xl">fingerprint</span>
                    <div>
                        <h1 class="text-xl font-bold">Rekap Absensi</h1>
                        <p class="text-xs text-slate-400">Fingerprint System</p>
                    </div>
                </div>
                {{-- CLOSE BUTTON (Mobile Only) --}}
                <button id="close-sidebar-btn" type="button" class="lg:hidden p-2 rounded-lg hover:bg-slate-700 transition-colors">
                    <span class="material-icons">close</span>
                </button>
            </div>

            {{-- NAVIGATION --}}
            <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
                <a href="{{ route('admin.dashboard') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white' : 'hover:bg-slate-700 text-slate-300' }}">
                    <span class="material-icons text-xl">dashboard</span>
                    <span class="font-medium">Dashboard</span>
                </a>

                <a href="{{ route('admin.users') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('admin.users') ? 'bg-blue-600 text-white' : 'hover:bg-slate-700 text-slate-300' }}">
                    <span class="material-icons text-xl">groups</span>
                    <span class="font-medium">Users</span>
                </a>

                <a href="{{ route('admin.profile') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('admin.profile') ? 'bg-blue-600 text-white' : 'hover:bg-slate-700 text-slate-300' }}">
                    <span class="material-icons text-xl">account_circle</span>
                    <span class="font-medium">Profile</span>
                </a>

                {{-- DIVIDER --}}
                <div class="border-t border-slate-700 my-3"></div>

                {{-- KELOLA ADMIN --}}
                <a href="{{ route('admin.admins') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('admin.admins') ? 'bg-blue-600 text-white' : 'hover:bg-slate-700 text-slate-300' }}">
                    <span class="material-icons text-xl">admin_panel_settings</span>
                    <span class="font-medium">Kelola Admin</span>
                </a>

                {{-- KELOLA SEKRETARIS --}}
                <a href="{{ route('admin.sekretaris') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('admin.sekretaris') ? 'bg-blue-600 text-white' : 'hover:bg-slate-700 text-slate-300' }}">
                    <span class="material-icons text-xl">supervised_user_circle</span>
                    <span class="font-medium">Kelola Sekretaris</span>
                </a>

                {{-- KELOLA KELAS --}}
                <a href="{{ route('admin.grades') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('admin.grades') ? 'bg-blue-600 text-white' : 'hover:bg-slate-700 text-slate-300' }}">
                    <span class="material-icons text-xl">school</span>
                    <span class="font-medium">Kelola Kelas</span>
                </a>

                {{-- KELOLA KELAS --}}
                <a href="{{ route('admin.grades') }}"
                    class="flex items-center gap-3 px-4 py-3 rounded-lg transition-colors {{ request()->routeIs('admin.date') ? 'bg-blue-600 text-white' : 'hover:bg-slate-700 text-slate-300' }}">
                    <span class="material-icons text-xl">date_range</span>
                    <span class="font-medium">Kelola Jadwal</span>
                </a>
            </nav>

            {{-- LOGOUT BUTTON --}}
            <div class="p-4 border-t border-slate-700">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center justify-center gap-3 px-4 py-3 bg-red-600 hover:bg-red-700 rounded-lg transition-colors font-medium">
                        <span class="material-icons text-xl">logout</span>
                        <span>Logout</span>
                    </button>
                </form>
            </div>
        </aside>

        {{-- MAIN CONTENT --}}
        <main class="flex-1 lg:ml-64 p-4 lg:p-6 pt-20 lg:pt-6 overflow-y-auto h-screen">
            @yield('content')
        </main>
    </div>

    {{-- SIDEBAR TOGGLE SCRIPT --}}
    <script>
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('sidebar-overlay');
        const mobileMenuBtn = document.getElementById('mobile-menu-btn');
        const closeSidebarBtn = document.getElementById('close-sidebar-btn');

        function openSidebar() {
            sidebar.classList.remove('-translate-x-full');
            overlay.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeSidebar() {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }

        mobileMenuBtn.addEventListener('click', openSidebar);
        closeSidebarBtn.addEventListener('click', closeSidebar);
        overlay.addEventListener('click', closeSidebar);
    </script>

    @stack('scripts')
</body>

</html>
