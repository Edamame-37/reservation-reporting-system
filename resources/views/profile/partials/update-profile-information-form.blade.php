<section>
    <header>
        <h2 class="text-lg font-bold text-slate-900 tracking-tight">
            {{ __('Informasi Dasar') }}
        </h2>

        <p class="mt-1 text-xs text-slate-500">
            {{ __("Perbarui nama lengkap Anda. Alamat email tidak dapat diubah karena merupakan identitas utama SSO Institusi.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('patch')

        <div>
            <label for="name" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">{{ __('Nama Lengkap') }}</label>
            <input id="name" name="name" type="text" class="w-full h-11 px-3.5 bg-slate-50 rounded-xl text-sm font-medium text-slate-800 border border-slate-200 focus:border-slate-900 focus:bg-white focus:outline-none transition" value="{{ old('name', $user->name) }}" required autofocus autocomplete="name" />
            @error('name')
                <p class="mt-1.5 text-xs text-rose-600 flex items-center gap-1 font-medium">
                    <span class="material-symbols-outlined text-[14px]">error</span>
                    <span>{{ $message }}</span>
                </p>
            @enderror
        </div>

        <div>
            <label for="email" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">{{ __('Alamat Email') }}</label>
            <input id="email" name="email" type="email" class="w-full h-11 px-3.5 bg-slate-100 rounded-xl text-sm font-medium text-slate-500 border border-slate-200 focus:outline-none cursor-not-allowed" value="{{ old('email', $user->email) }}" required readonly autocomplete="username" />
            @error('email')
                <p class="mt-1.5 text-xs text-rose-600 flex items-center gap-1 font-medium">
                    <span class="material-symbols-outlined text-[14px]">error</span>
                    <span>{{ $message }}</span>
                </p>
            @enderror

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-slate-800">
                        {{ __('Alamat email Anda belum terverifikasi.') }}

                        <button form="send-verification" class="underline text-sm text-slate-600 hover:text-slate-900 rounded-md focus:outline-none">
                            {{ __('Klik di sini untuk mengirim ulang tautan verifikasi.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-emerald-600">
                            {{ __('Tautan verifikasi baru telah dikirimkan ke alamat email Anda.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4 pt-2">
            <button type="submit" class="px-5 py-2 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 transition shadow-xs">
                {{ __('Simpan Perubahan') }}
            </button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2500)"
                    class="text-xs font-semibold text-emerald-600 flex items-center gap-1 bg-emerald-50 px-3 py-1.5 rounded-lg border border-emerald-200/60"
                >
                    <span class="material-symbols-outlined text-[16px]">check_circle</span>
                    {{ __('Tersimpan.') }}
                </p>
            @endif
        </div>
    </form>
</section>
