<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'CAVA - Reservasi') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-slate-800 antialiased selection:bg-slate-800 selection:text-white">
        <div class="min-h-screen flex lg:justify-end w-full bg-slate-50 dark:bg-slate-900">
            <!-- Left Side: Cover Image & Branding (Hidden on mobile) -->
            <div class="hidden lg:flex lg:w-1/2 lg:fixed lg:inset-y-0 lg:left-0 bg-slate-900 overflow-hidden items-center justify-center z-0">
                <!-- Background Image -->
                <div class="absolute inset-0 bg-cover bg-center opacity-40 mix-blend-overlay" style="background-image: url('https://images.unsplash.com/photo-1541339907198-e08756dedf3f?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');"></div>
                <!-- Gradient Overlay -->
                <div class="absolute inset-0 bg-gradient-to-br from-slate-800/90 to-slate-900/90"></div>
                
                <div class="relative z-10 p-12 text-center text-white max-w-xl">
                    <h1 class="text-4xl font-extrabold tracking-tight mb-4 text-white">Sistem Cerdas Reservasi</h1>
                    <p class="text-lg text-slate-200 font-medium leading-relaxed">Platform terpadu untuk mengelola peminjaman ruang, fasilitas kampus, dan pelaporan kerusakan sarana prasarana secara cepat dan transparan.</p>
                </div>
            </div>

            <!-- Right Side: Auth Form -->
            <div class="flex flex-col justify-center items-center w-full lg:w-1/2 p-6 sm:p-12 relative">
                <div class="w-full max-w-md">
                    <!-- Mobile Logo (Removed CAVA Logo) -->
                    <div class="flex justify-center mb-8 lg:hidden">
                        <h1 class="text-2xl font-extrabold tracking-tight text-slate-800 dark:text-white text-center">Sistem Cerdas Reservasi</h1>
                    </div>

                    <div class="bg-white dark:bg-slate-800 px-8 py-10 shadow-[0_8px_30px_rgb(0,0,0,0.04)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] rounded-2xl border border-slate-100 dark:border-slate-700">
                        {{ $slot }}
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
