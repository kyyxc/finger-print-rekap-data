@extends('layouts.main')

@section('title', 'Login Admin')

@section('content')
    <div class="min-h-screen flex items-center justify-center bg-gray-100 p-4">
        <form method="POST" action="{{ route('authenticate') }}"
            class="bg-white p-6 rounded-xl shadow-lg w-full max-w-sm border">
            @csrf
            <h2 class="text-2xl font-bold mb-5 text-center text-gray-800">Login</h2>

            @if (session('message'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('message') }}</span>
                </div>
            @endif

            @error('login')
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-4" role="alert">
                    <span class="block sm:inline">{{ $message }}</span>
                </div>
            @enderror

            <div class="mb-4">
                <label for="username" class="sr-only">Username</label>
                <input type="text" name="username" id="username" placeholder="Username" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" value="{{ old('username') }}" required>
            </div>

            <div class="mb-4">
                <label for="password" class="sr-only">Password</label>
                <input type="password" name="password" id="password" placeholder="Password" class="w-full p-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" required>
            </div>


            <div class="flex items-center justify-between mb-5">
                <label class="flex items-center space-x-2 text-sm text-gray-600">
                    <input type="checkbox" name="remember" class="form-checkbox h-4 w-4 text-blue-600 rounded">
                    <span>Ingat saya</span>
                </label>
            </div>

            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white w-full py-3 rounded-lg font-semibold transition-colors">Login</button>
        </form>
    </div>
@endsection
