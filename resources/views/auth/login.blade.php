<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    @if (session('success'))
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-900/30 border border-emerald-200 dark:border-emerald-800 flex items-start gap-3">
            <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <h3 class="text-sm font-semibold text-emerald-800 dark:text-emerald-200">Informasi</h3>
                <p class="text-sm text-emerald-600 dark:text-emerald-400 mt-1">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <div class="mb-6">
        <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-sm text-slate-500 hover:text-slate-800 transition-colors">
            <span class="material-symbols-outlined text-[18px]">arrow_back</span>
            <span>Kembali</span>
        </a>
    </div>

    <form method="POST" action="{{ route('login') }}" class="flex flex-col gap-4">
        @csrf

        <div class="text-center mb-6">
            <h2 class="text-2xl font-bold text-slate-900 dark:text-white">Masuk</h2>
            <p class="text-slate-600 dark:text-slate-400 mt-2">Silakan masuk menggunakan kredensial Anda.</p>
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-500 font-medium" />
        </div>

        <!-- Password -->
        <div class="mt-2">
            <x-input-label for="password" value="Password" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" />
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-500 font-medium" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-2">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800" name="remember">
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">Ingat Saya</span>
            </label>
        </div>

        <div class="flex flex-col items-center mt-6 gap-4">
            <x-primary-button class="w-full justify-center max-w-[200px]">
                Masuk
            </x-primary-button>

            @if (Route::has('password.request'))
                <a class="underline text-sm text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-slate-900 dark:focus:ring-offset-slate-800" href="{{ route('password.request') }}">
                    Lupa password?
                </a>
            @endif
        </div>
        
        <div class="mt-6 text-center">
            <p class="text-sm text-slate-600 dark:text-slate-400">Belum punya akun? <a href="{{ route('register') }}" class="font-medium text-slate-900 hover:text-slate-700">Daftar sekarang</a></p>
        </div>
    </form>
</x-guest-layout>
