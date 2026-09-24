<section class="space-y-6">
    <header>
        <h2 class="text-lg font-bold text-rose-700 tracking-tight">
            {{ __('Hapus Akun Permanen') }}
        </h2>

        <p class="mt-1 text-xs text-slate-500">
            {{ __('Setelah akun Anda dihapus, semua sumber daya dan data yang terkait akan dihapus secara permanen. Harap pastikan Anda telah mengunduh semua data atau laporan yang ingin Anda simpan sebelum menghapus akun.') }}
        </p>
    </header>

    <button
        type="button"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="px-5 py-2 rounded-xl bg-rose-600 text-white text-xs font-bold hover:bg-rose-700 transition shadow-xs flex items-center gap-1.5"
    >
        <span class="material-symbols-outlined text-[16px]">warning</span>
        {{ __('Hapus Akun Saya') }}
    </button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-bold text-slate-900 tracking-tight">
                {{ __('Apakah Anda yakin ingin menghapus akun ini secara permanen?') }}
            </h2>

            <p class="mt-1 text-xs text-slate-500">
                {{ __('Setelah dihapus, semua data dan riwayat akan hilang. Silakan masukkan kata sandi Anda untuk mengonfirmasi bahwa Anda benar-benar ingin menghapus akun ini.') }}
            </p>

            <div class="mt-6">
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2 sr-only">{{ __('Kata Sandi') }}</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    class="w-full h-11 px-3.5 bg-slate-50 rounded-xl text-sm font-medium text-slate-800 border border-slate-200 focus:border-slate-900 focus:bg-white focus:outline-none transition"
                    placeholder="{{ __('Kata Sandi Anda') }}"
                />

                @error('password', 'userDeletion')
                    <p class="mt-1.5 text-xs text-rose-600 flex items-center gap-1 font-medium">
                        <span class="material-symbols-outlined text-[14px]">error</span>
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>

            <div class="mt-6 flex justify-end gap-3">
                <button type="button" x-on:click="$dispatch('close')" class="px-5 py-2 rounded-xl bg-slate-100 text-slate-700 border border-slate-200/80 text-xs font-bold hover:bg-slate-200 transition shadow-xs">
                    {{ __('Batal') }}
                </button>

                <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 text-white text-xs font-bold hover:bg-rose-700 transition shadow-xs flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">delete_forever</span>
                    {{ __('Hapus Permanen') }}
                </button>
            </div>
        </form>
    </x-modal>
</section>
