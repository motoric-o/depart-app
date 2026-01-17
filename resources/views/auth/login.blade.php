<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen py-10 px-4">
    <div class="w-full max-w-md">
        <!-- Logo/Header Section -->
        <div class="text-center mb-10">
            <h1 class="text-3xl font-bold mb-2" style="color: #0085CD;">{{ config('app.name') }}</h1>
            <p class="text-gray-600">Selamat datang kembali</p>
        </div>

        <!-- Login Card -->
        <div class="bg-white rounded-lg shadow-sm p-8 border border-gray-100">
            <h2 class="text-2xl font-bold text-center text-gray-900 mb-6">Masuk ke Akun</h2>
            
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6" role="alert">
                    <span class="block sm:inline">{{ $errors->first() }}</span>
                </div>
            @endif

            @if (session('status'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6" role="alert">
                    <span class="block sm:inline">{{ session('status') }}</span>
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-5">
                @csrf
                
                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-gray-700 text-sm font-medium mb-2">
                        Alamat Email
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="{{ old('email') }}" 
                        required 
                        autofocus
                        placeholder="nama@example.com"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:border-transparent transition duration-150"
                        style="--tw-ring-color: #0085CD;"
                    >
                </div>

                <!-- Password Field -->
                <div>
                    <label for="password" class="block text-gray-700 text-sm font-medium mb-2">
                        Password
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        required
                        placeholder="Masukkan password"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:border-transparent transition duration-150"
                        style="--tw-ring-color: #0085CD;"
                    >
                </div>

                <!-- Remember Me & Forgot Password -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center cursor-pointer">
                        <input 
                            type="checkbox" 
                            name="remember" 
                            class="w-4 h-4 rounded border-gray-300 cursor-pointer"
                            style="accent-color: #0085CD;"
                        >
                        <span class="ml-2 text-sm text-gray-600">Ingat saya</span>
                    </label>
                    <a href="{{ route('password.request') }}" class="text-sm font-medium transition duration-150" style="color: #0085CD;">
                        Lupa password?
                    </a>
                </div>

                <!-- Submit Button -->
                <button 
                    type="submit"
                    class="w-full text-white font-medium py-2.5 px-4 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 transition duration-150 shadow-sm hover:shadow-md"
                    style="background-color: #0085CD; --tw-ring-color: #0085CD;"
                >
                    Masuk
                </button>
            </form>

            <!-- Sign Up Link -->
            <div class="mt-6 text-center pt-6 border-t border-gray-200">
                <p class="text-gray-600 text-sm">
                    Belum punya akun? 
                    <a href="{{ route('signup') }}" class="font-medium transition duration-150" style="color: #0085CD;">
                        Daftar Sekarang
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
