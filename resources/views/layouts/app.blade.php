<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        .font-roboto { font-family: 'Roboto', sans-serif; }
    </style>

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-100 text-gray-900">
    <div class="min-h-screen flex flex-col">
        <!-- Navigation -->
        <header class="bg-white shadow relative z-50">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900">
                    <a href="{{ url('/') }}">{{ config('app.name', 'Laravel') }}</a>
                </h1>
                <nav>
                    @auth
                        <div id="user-menu-container" class="relative ml-4">
                            <button id="user-menu-button" class="flex items-center focus:outline-none py-2">
                                <img src="https://ui-avatars.com/api/?name={{ urlencode(Auth::user()->first_name . ' ' . Auth::user()->last_name) }}&background=0D8ABC&color=fff" class="h-10 w-10 rounded-full border-2 border-gray-200" alt="Avatar">
                            </button>
                            <!-- Dropdown Container -->
                            <div id="user-menu-dropdown" class="absolute right-0 top-full pt-1 w-80 z-[100] hidden">
                                <div class="bg-white rounded-md shadow-lg py-1 border border-gray-100">
                                    <div class="px-4 py-2 border-b border-gray-100">
                                        <p class="text-xs text-gray-500">Signed in as</p>
                                        <p class="text-sm font-semibold truncate">{{ Auth::user()->first_name }}</p>
                                    </div>
                                    
                                    @if(Auth::user()->accountType->name !== 'Customer')
                                        <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            Dashboard
                                        </a>
                                    @endif

                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                            {{ __('Log Out') }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const container = document.getElementById('user-menu-container');
                                const dropdown = document.getElementById('user-menu-dropdown');
                                let timeoutId;

                                container.addEventListener('mouseenter', function() {
                                    clearTimeout(timeoutId);
                                    dropdown.classList.remove('hidden');
                                });

                                container.addEventListener('mouseleave', function() {
                                    // Small delay to prevent flickering if moving mouse quickly
                                    timeoutId = setTimeout(() => {
                                        dropdown.classList.add('hidden');
                                    }, 100);
                                });

                                // Also close on Escape key
                                document.addEventListener('keydown', function(event) {
                                    if (event.key === 'Escape') {
                                        dropdown.classList.add('hidden');
                                    }
                                });
                            });
                        </script>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-medium text-gray-700 hover:text-blue-600 mr-4 transition duration-150 ease-in-out">
                            Log in
                        </a>
                        <a href="{{ route('signup') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-150 ease-in-out">
                            Daftar
                        </a>
                    @endauth
                </nav>
            </div>
        </header>

        <!-- Page Content -->
        <main class="flex-grow">
            @yield('content')
        </main>

        <footer class="bg-white border-t border-gray-200 mt-auto">
            <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 text-center text-sm text-gray-500">
                &copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. All rights reserved.
            </div>
        </footer>
    </div>
</body>
</html>
