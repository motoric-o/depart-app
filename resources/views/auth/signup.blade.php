<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Daftar - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen py-10 px-4">
    <div class="w-full max-w-lg">
        <!-- Logo/Header Section -->
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold mb-2" style="color: #0085CD;">{{ config('app.name') }}</h1>
            <p class="text-gray-600">Buat akun baru</p>
        </div>

        <!-- Signup Card -->
        <div class="bg-white rounded-lg shadow-sm p-8 border border-gray-100">
            <h2 class="text-2xl font-bold text-center text-gray-900 mb-6">Buat Akun</h2>
            
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6" role="alert">
                    <div class="flex items-start">
                        <svg class="w-5 h-5 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                        </svg>
                        <div>
                            <ul class="list-disc list-inside space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('signup') }}" class="space-y-5">
                @csrf
                
                <!-- Name Fields -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="first_name" class="block text-gray-700 text-sm font-medium mb-2">
                            Nama Depan
                        </label>
                        <input 
                            type="text" 
                            id="first_name" 
                            name="first_name" 
                            value="{{ old('first_name') }}" 
                            required
                            placeholder="John"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:border-transparent transition duration-150"
                            style="--tw-ring-color: #0085CD;"
                        >
                    </div>
                    <div>
                        <label for="last_name" class="block text-gray-700 text-sm font-medium mb-2">
                            Nama Belakang
                        </label>
                        <input 
                            type="text" 
                            id="last_name" 
                            name="last_name" 
                            value="{{ old('last_name') }}" 
                            required
                            placeholder="Doe"
                            class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:border-transparent transition duration-150"
                            style="--tw-ring-color: #0085CD;"
                        >
                    </div>
                </div>

                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-gray-700 text-sm font-medium mb-2">
                        Email Address
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"/>
                            </svg>
                        </div>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            value="{{ old('email') }}" 
                            required
                            placeholder="nama@example.com"
                            class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:border-transparent transition duration-150"
                            style="--tw-ring-color: #0085CD;"
                        >
                    </div>
                </div>

                <!-- Phone Field -->
                <div>
                    <label for="phone" class="block text-gray-700 text-sm font-medium mb-2">
                        Nomor Telepon <span class="text-gray-400 text-xs">(opsional)</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </div>
                        <input 
                            type="text" 
                            id="phone" 
                            name="phone" 
                            value="{{ old('phone') }}"
                            placeholder="081234567890"
                            class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:border-transparent transition duration-150"
                            style="--tw-ring-color: #0085CD;"
                        >
                    </div>
                </div>

                <!-- Birthdate Field -->
                <div>
                    <label for="birthdate" class="block text-gray-700 text-sm font-medium mb-2">
                        Tanggal Lahir
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <input 
                            type="date" 
                            id="birthdate" 
                            name="birthdate" 
                            value="{{ old('birthdate') }}"
                            required
                            class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:border-transparent transition duration-150"
                            style="--tw-ring-color: #0085CD;"
                        >
                    </div>
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-gray-700 text-sm font-medium mb-2">
                        Password
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required
                            placeholder="Minimal 8 karakter"
                            class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:border-transparent transition duration-150"
                            style="--tw-ring-color: #0085CD;"
                        >
                    </div>
                    <p class="mt-1 text-xs text-gray-500">Minimal 8 karakter</p>
                </div>

                <!-- Confirm Password Field -->
                <div>
                    <label for="password_confirmation" class="block text-gray-700 text-sm font-medium mb-2">
                        Konfirmasi Password
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input 
                            type="password" 
                            id="password_confirmation" 
                            name="password_confirmation" 
                            required
                            placeholder="Masukkan ulang password"
                            class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:border-transparent transition duration-150"
                            style="--tw-ring-color: #0085CD;"
                        >
                    </div>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit"
                    class="w-full text-white font-medium py-2.5 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 transition duration-150 shadow-sm hover:shadow-md"
                    style="background-color: #0085CD; --tw-ring-color: #0085CD;"
                >
                    Daftar
                </button>
            </form>

            <!-- Login Link -->
            <div class="mt-6 text-center pt-6 border-t border-gray-200">
                <p class="text-gray-600 text-sm">
                    Sudah punya akun? 
                    <a href="{{ route('login') }}" class="font-medium transition duration-150" style="color: #0085CD;">
                        Masuk
                    </a>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-6 text-center">
            <p class="text-gray-500 text-sm">
                &copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
            </p>
        </div>
    </div>
</body>
</html>
