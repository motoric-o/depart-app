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

    <!-- AlpineJS -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.3/dist/cdn.min.js"></script>
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

                                    <a href="{{ route('booking.history') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        Riwayat Pesanan
                                    </a>

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
    @if(!request()->is('admin*') && !request()->is('owner*'))
    <!-- Chatbot Widget -->
    <div id="chatbot-widget" style="position: fixed; bottom: 24px; right: 24px; z-index: 9999; font-family: 'Roboto', sans-serif;">
        <!-- Chat Button -->
        <button id="chatbot-toggle" class="bg-blue-600 text-white hover:bg-blue-700 transition" style="border-radius: 9999px; padding: 16px; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); display: flex; align-items: center; justify-content: center; border: none; cursor: pointer; background: linear-gradient(to right, #2563EB, #4F46E5);">
            <svg class="w-8 h-8" style="width: 32px; height: 32px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path>
            </svg>
        </button>

        <!-- Chat Window -->
        <div id="chatbot-window" class="hidden bg-white shadow-2xl border border-gray-100" style="position: absolute; bottom: 80px; right: 0; width: 320px; border-radius: 16px; display: flex; flex-direction: column; overflow: hidden; transition: all 0.3s ease; transform-origin: bottom right; opacity: 0; transform: scale(0.95); display: none;">
            <!-- Header -->
            <div style="background: linear-gradient(to right, #2563EB, #4F46E5); padding: 16px; display: flex; justify-content: space-between; align-items: center; color: white;">
                <div class="flex items-center" style="display: flex; align-items: center;">
                    <div style="width: 32px; height: 32px; background-color: rgba(255,255,255,0.2); border-radius: 9999px; display: flex; align-items: center; justify-content: center; margin-right: 12px;">
                        <svg class="w-5 h-5 text-white" style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm" style="font-weight: 700; font-size: 14px;">Travel Assistant</h3>
                        <p class="text-xs text-blue-100" style="font-size: 12px; color: #DBEAFE;">Online</p>
                    </div>
                </div>
                <button id="chatbot-close" class="text-white hover:text-gray-200 focus:outline-none" style="background: none; border: none; cursor: pointer; color: rgba(255,255,255,0.8);">
                    <svg class="w-5 h-5" style="width: 20px; height: 20px;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                </button>
            </div>

            <!-- Messages Area -->
            <div id="chatbot-messages" class="bg-gray-50" style="flex: 1; padding: 16px; overflow-y: auto; max-height: 320px; min-height: 300px; background-color: #F9FAFB;">
                <!-- Welcome Message -->
                <div class="flex items-start" style="display: flex; align-items: flex-start; margin-bottom: 12px;">
                    <div class="flex-shrink-0" style="width: 32px; height: 32px; background-color: #DBEAFE; border-radius: 9999px; display: flex; align-items: center; justify-content: center; margin-right: 8px;">
                        <svg class="w-5 h-5 text-blue-600" style="width: 20px; height: 20px; color: #2563EB;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    </div>
                    <div class="bg-white shadow-sm text-sm text-gray-700" style="background-color: white; padding: 12px; border-radius: 8px; border-top-left-radius: 0; max-width: 80%; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);">
                        Halo! Saya asisten virtual Anda. Tanyakan tentang pemesanan tiket, refund, atau rekomendasi wisata!
                    </div>
                </div>
            </div>

            <!-- Input Area -->
            <div class="bg-white border-t border-gray-100" style="padding: 16px; background-color: white; border-top: 1px solid #F3F4F6;">
                <form id="chatbot-form" class="flex items-center" style="display: flex; align-items: center;">
                    <input type="text" id="chatbot-input" class="border-gray-300 focus:border-blue-500 focus:ring-blue-500" style="flex: 1; border: 1px solid #D1D5DB; border-radius: 9999px; padding: 8px 16px; font-size: 14px; outline: none;" placeholder="Type a message..." required autocomplete="off">
                    <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white" style="margin-left: 8px; background-color: #2563EB; color: white; border-radius: 9999px; padding: 8px; border: none; cursor: pointer; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); display: flex; align-items: center; justify-content: center;">
                        <svg class="w-5 h-5" style="width: 20px; height: 20px; transform: rotate(90deg);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path></svg>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('chatbot-toggle');
            const closeBtn = document.getElementById('chatbot-close');
            const widgetWindow = document.getElementById('chatbot-window');
            const form = document.getElementById('chatbot-form');
            const input = document.getElementById('chatbot-input');
            const messagesContainer = document.getElementById('chatbot-messages');

            function toggleChat() {
                if (widgetWindow.style.display === 'none' || widgetWindow.classList.contains('hidden')) {
                    widgetWindow.classList.remove('hidden');
                    widgetWindow.style.display = 'flex';
                    // Small delay to allow display:block to apply before opacity transition
                    setTimeout(() => {
                        widgetWindow.style.opacity = '1';
                        widgetWindow.style.transform = 'scale(1)';
                        input.focus();
                    }, 10);
                } else {
                    widgetWindow.style.opacity = '0';
                    widgetWindow.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        widgetWindow.style.display = 'none';
                        widgetWindow.classList.add('hidden');
                    }, 300);
                }
            }

            toggleBtn.addEventListener('click', toggleChat);
            closeBtn.addEventListener('click', toggleChat);

            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const message = input.value.trim();
                if (!message) return;

                addMessage(message, 'user');
                input.value = '';

                const loadingId = addLoadingIndicator();

                fetch('{{ route("chat") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({ message: message })
                })
                .then(response => response.json())
                .then(data => {
                    removeMessage(loadingId);
                    addMessage(data.response, 'bot');
                })
                .catch(error => {
                    console.error('Error:', error);
                    removeMessage(loadingId);
                    addMessage("Sorry, something went wrong. Please try again.", 'bot');
                });
            });

            function addMessage(text, sender) {
                const div = document.createElement('div');
                div.style.display = 'flex';
                div.style.marginBottom = '12px';
                
                if (sender === 'user') {
                    div.style.justifyContent = 'flex-end';
                    div.style.alignItems = 'flex-end';
                    div.innerHTML = `
                         <div style="background-color: #2563EB; color: white; padding: 12px; border-radius: 8px; border-top-right-radius: 0; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); max-width: 80%; font-size: 14px;">
                            ${text}
                        </div>
                    `;
                } else {
                    div.style.justifyContent = 'flex-start';
                    div.style.alignItems = 'flex-start';
                    div.innerHTML = `
                        <div style="width: 32px; height: 32px; background-color: #DBEAFE; border-radius: 9999px; display: flex; align-items: center; justify-content: center; margin-right: 8px; flex-shrink: 0;">
                            <svg class="w-5 h-5 text-blue-600" style="width: 20px; height: 20px; color: #2563EB;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                        </div>
                        <div style="background-color: white; padding: 12px; border-radius: 8px; border-top-left-radius: 0; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); max-width: 80%; font-size: 14px;">
                            ${text}
                        </div>
                    `;
                }
                
                messagesContainer.appendChild(div);
                scrollToBottom();
            }

            function addLoadingIndicator() {
                const id = 'loading-' + Date.now();
                const div = document.createElement('div');
                div.id = id;
                div.style.display = 'flex';
                div.style.alignItems = 'flex-start';
                div.style.marginBottom = '12px';
                
                div.innerHTML = `
                    <div style="width: 32px; height: 32px; background-color: #DBEAFE; border-radius: 9999px; display: flex; align-items: center; justify-content: center; margin-right: 8px; flex-shrink: 0;">
                         <svg class="w-5 h-5 text-blue-600" style="width: 20px; height: 20px; color: #2563EB;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                    </div>
                    <div style="background-color: white; padding: 12px; border-radius: 8px; border-top-left-radius: 0; box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); color: #9CA3AF; font-size: 14px;">
                        Loading...
                    </div>
                `;
                messagesContainer.appendChild(div);
                scrollToBottom();
                return id;
            }

            function removeMessage(id) {
                const el = document.getElementById(id);
                if (el) el.remove();
            }

            function scrollToBottom() {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }
        });
    </script>
    @endif
    @stack('scripts')
</body>
</html>
