<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'CAVA - Reservasi') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased selection:bg-indigo-500 selection:text-white">
        <div class="min-h-screen flex w-full bg-gray-50 dark:bg-gray-900">
            <!-- Left Side: Cover Image & Branding (Hidden on mobile) -->
            <div class="hidden lg:flex lg:w-1/2 relative bg-indigo-900 overflow-hidden items-center justify-center">
                <!-- Background Image -->
                <div class="absolute inset-0 bg-cover bg-center opacity-40 mix-blend-overlay" style="background-image: url('https://images.unsplash.com/photo-1541339907198-e08756dedf3f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');"></div>
                <!-- Gradient Overlay -->
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-600/90 to-purple-900/90"></div>
                
                <div class="relative z-10 p-12 text-center text-white max-w-xl">
                    <x-application-logo class="w-48 h-auto mx-auto mb-6 drop-shadow-md" />
                    <h1 class="text-4xl font-extrabold tracking-tight mb-4 text-white">Sistem Cerdas Reservasi</h1>
                    <p class="text-lg text-indigo-100 font-medium leading-relaxed">Platform terpadu untuk mengelola peminjaman ruang, fasilitas kampus, dan pelaporan kerusakan sarana prasarana secara cepat dan transparan.</p>
                </div>
            </div>

            <!-- Right Side: Auth Form -->
            <div class="flex flex-col justify-center items-center w-full lg:w-1/2 p-6 sm:p-12 relative">
                <div class="w-full max-w-md">
                    <!-- Mobile Logo -->
                    <div class="flex justify-center mb-8 lg:hidden">
                        <a href="/">
                            <x-application-logo class="w-32 h-auto" />
                        </a>
                    </div>

                    <div class="bg-white dark:bg-gray-800 px-8 py-10 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] rounded-2xl border border-gray-100 dark:border-gray-700">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
