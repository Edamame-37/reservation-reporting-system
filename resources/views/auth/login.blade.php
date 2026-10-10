<x-guest-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-6" :status="session('status')" />

    @if (session('success'))
        <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-100 flex items-start gap-3">
            <span class="material-symbols-outlined text-emerald-600">check_circle</span>
            <div>
                <h3 class="text-[14px] font-semibold text-emerald-800">Informasi</h3>
                <p class="text-[13px] text-emerald-600 mt-1">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    <a href="{{ url('/') }}" class="inline-flex items-center gap-2 text-[14px] text-[#86868B] hover:text-[#1D1D1F] font-medium transition-colors mb-10 group">
        <span class="material-symbols-outlined text-[18px] group-hover:-translate-x-1 transition-transform">arrow_back</span>
        <span>Kembali ke Beranda</span>
    </a>

    <div class="mb-10">
        <h1 class="text-[32px] md:text-[36px] font-extrabold tracking-tight text-[#1D1D1F] mb-2 leading-tight">Welcome Back</h1>
        <p class="text-[15px] text-[#86868B] leading-relaxed max-w-sm">Akses sistem reservasi dan laporkan kendala fasilitas kampus dengan mudah.</p>
    </div>

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-[14px] font-semibold text-[#1D1D1F] mb-2">Email Sivitas</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" class="w-full bg-[#F5F5F7] text-[#1D1D1F] placeholder:text-[#86868B] px-5 py-4 rounded-2xl focus:outline-none focus:ring-[3px] focus:ring-blue-500/20 focus:bg-white transition-all text-[15px]" placeholder="nama@kampus.ac.id">
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-500 text-sm font-medium" />
        </div>

        <!-- Password -->
        <div>
            <div class="flex justify-between items-center mb-2">
                <label for="password" class="block text-[14px] font-semibold text-[#1D1D1F]">Kata Sandi</label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="text-[13px] text-[#1D1D1F] font-semibold hover:underline">Lupa Sandi?</a>
                @endif
            </div>
            <input id="password" type="password" name="password" required autocomplete="current-password" class="w-full bg-[#F5F5F7] text-[#1D1D1F] placeholder:text-[#86868B] px-5 py-4 rounded-2xl focus:outline-none focus:ring-[3px] focus:ring-blue-500/20 focus:bg-white transition-all text-[15px]" placeholder="Minimal 8 karakter">
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-500 text-sm font-medium" />
        </div>

        <!-- Remember Me -->
        <div class="flex items-center gap-3 pt-2">
            <input id="remember_me" type="checkbox" name="remember" class="rounded-[4px] border-gray-300 text-[#1D1D1F] focus:ring-[#1D1D1F]/20 cursor-pointer w-4 h-4">
            <label for="remember_me" class="text-[14px] font-medium text-[#86868B] cursor-pointer select-none">Ingat saya di perangkat ini</label>
        </div>

        <button type="submit" class="w-full bg-[#1D1D1F] text-white px-8 py-4 rounded-2xl font-semibold hover:bg-gray-800 transition shadow-[0_8px_20px_rgb(0,0,0,0.08)] mt-6 text-[15px]">
            Masuk ke Akun
        </button>
        
        <div class="mt-10 pt-6 border-t border-gray-100/50 flex items-center justify-center gap-1.5 text-[14px] text-[#86868B]">
            <span>Belum memiliki akun?</span>
            <a href="{{ route('register') }}" class="text-[#1D1D1F] font-semibold hover:underline">Daftar sekarang</a>
        </div>
    </form>
</x-guest-layout>