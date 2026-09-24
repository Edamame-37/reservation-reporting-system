<section>
    <header>
        <h2 class="text-lg font-bold text-slate-900 tracking-tight">
            {{ __('Perbarui Kata Sandi') }}
        </h2>

        <p class="mt-1 text-xs text-slate-500">
            {{ __('Pastikan akun Anda menggunakan kata sandi yang panjang dan acak demi keamanan.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('put')

        <div>
            <label for="update_password_current_password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">{{ __('Kata Sandi Saat Ini') }}</label>
            <input id="update_password_current_password" name="current_password" type="password" class="w-full h-11 px-3.5 bg-slate-50 rounded-xl text-sm font-medium text-slate-800 border border-slate-200 focus:border-slate-900 focus:bg-white focus:outline-none transition" autocomplete="current-password" />
            @error('current_password', 'updatePassword')
                <p class="mt-1.5 text-xs text-rose-600 flex items-center gap-1 font-medium">
                    <span class="material-symbols-outlined text-[14px]">error</span>
                    <span>{{ $message }}</span>
                </p>
            @enderror
        </div>

        <div>
            <label for="update_password_password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">{{ __('Kata Sandi Baru') }}</label>
            <input id="update_password_password" name="password" type="password" class="w-full h-11 px-3.5 bg-slate-50 rounded-xl text-sm font-medium text-slate-800 border border-slate-200 focus:border-slate-900 focus:bg-white focus:outline-none transition" autocomplete="new-password" />
            @error('password', 'updatePassword')
                <p class="mt-1.5 text-xs text-rose-600 flex items-center gap-1 font-medium">
                    <span class="material-symbols-outlined text-[14px]">error</span>
                    <span>{{ $message }}</span>
                </p>
            @enderror
        </div>

        <div>
            <label for="update_password_password_confirmation" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">{{ __('Konfirmasi Kata Sandi Baru') }}</label>
            <input id="update_password_password_confirmation" name="password_confirmation" type="password" class="w-full h-11 px-3.5 bg-slate-50 rounded-xl text-sm font-medium text-slate-800 border border-slate-200 focus:border-slate-900 focus:bg-white focus:outline-none transition" autocomplete="new-password" />
            @error('password_confirmation', 'updatePassword')
                <p class="mt-1.5 text-xs text-rose-600 flex items-center gap-1 font-medium">
                    <span class="material-symbols-outlined text-[14px]">error</span>
                    <span>{{ $message }}</span>
                </p>
            @enderror
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button type="submit" class="px-5 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition shadow-xs">
                {{ __('Ubah Sandi') }}
            </button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2500)"
                    class="text-xs font-semibold text-emerald-600 flex items-center gap-1 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-200/60"
                >
                    <span class="material-symbols-outlined text-[16px]">check_circle</span>
                    {{ __('Sandi Berhasil Diubah.') }}
                </p>
            @endif
        </div>
    </form>
</section>
