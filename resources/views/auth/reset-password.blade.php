<x-guest-layout>
    <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-[14px] text-[#86868B] hover:text-[#1D1D1F] font-medium transition-colors mb-10 group">
        <span class="material-symbols-outlined text-[18px] group-hover:-translate-x-1 transition-transform">arrow_back</span>
        <span>Kembali ke Login</span>
    </a>

    <div class="mb-10">
        <h1 class="text-[32px] md:text-[36px] font-extrabold tracking-tight text-[#1D1D1F] mb-2 leading-tight">Reset Sandi</h1>
        <p class="text-[15px] text-[#86868B] leading-relaxed max-w-sm">Silakan atur kata sandi baru Anda untuk memulihkan akses ke sistem.</p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
        @csrf

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        <!-- Email Address -->
        <div>
            <label for="email" class="block text-[14px] font-semibold text-[#1D1D1F] mb-2">{{ __('Email') }}</label>
            <input id="email" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username" class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] placeholder:text-[#86868B] px-5 py-4 rounded-2xl focus:outline-none focus:ring-[3px] focus:ring-blue-500/20 focus:bg-white transition-all text-[15px]">
            <x-input-error :messages="$errors->get('email')" class="mt-2 text-red-500 text-sm font-medium" />
        </div>

        <!-- Password -->
        <div>
            <label for="password" class="block text-[14px] font-semibold text-[#1D1D1F] mb-2">{{ __('Password') }}</label>
            <input id="password" type="password" name="password" required autocomplete="new-password" class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] placeholder:text-[#86868B] px-5 py-4 rounded-2xl focus:outline-none focus:ring-[3px] focus:ring-blue-500/20 focus:bg-white transition-all text-[15px]" placeholder="Minimal 8 karakter">
            <x-input-error :messages="$errors->get('password')" class="mt-2 text-red-500 text-sm font-medium" />
        </div>

        <!-- Confirm Password -->
        <div>
            <label for="password_confirmation" class="block text-[14px] font-semibold text-[#1D1D1F] mb-2">{{ __('Confirm Password') }}</label>
            <input id="password_confirmation" type="password" name="password_confirmation" required autocomplete="new-password" class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] placeholder:text-[#86868B] px-5 py-4 rounded-2xl focus:outline-none focus:ring-[3px] focus:ring-blue-500/20 focus:bg-white transition-all text-[15px]" placeholder="Ulangi kata sandi">
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2 text-red-500 text-sm font-medium" />
        </div>

        <button type="submit" class="w-full bg-[#1D1D1F] text-white px-8 py-4 rounded-2xl font-semibold hover:bg-gray-800 transition shadow-[0_8px_20px_rgb(0,0,0,0.08)] mt-6 text-[15px]">
            {{ __('Reset Password') }}
        </button>
    </form>
</x-guest-layout>