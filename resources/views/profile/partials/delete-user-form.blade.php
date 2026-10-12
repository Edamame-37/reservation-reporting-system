{{-- 
====================================================================================================
SISTEM INFORMASI RESERVASI & PELAPORAN FASILITAS KAMPUS (CAVA)
MODUL 17: MANAJEMEN AKUN PROFIL PENGGUNA (PROFILE SETTINGS & PASSWORD)
BERKAS: resources/views/profile/partials/delete-user-form.blade.php
====================================================================================================

DESKRIPSI BERKAS:
Parsial formulir Blade dan modal konfirmasi untuk penghapusan akun permanen atas kehendak mandiri pengguna.
Menyediakan dialog peringatan risiko tinggi (Destructive Action Modal) dengan validasi kata sandi saat ini
sebelum akun dihapus secara tuntas dari basis data CAVA.

PERAN ARSITEKTURAL DALAM SISTEM CAVA:
1. Perlindungan Penghapusan Tak Disengaja (Accidental Deletion Prevention):
   Mewajibkan pengguna memasukkan kata sandi saat ini (`current_password`) untuk mengonfirmasi identitas
   sebelum sistem mencabut hak akses akun.
2. Error Bag Terisolasi 'userDeletion':
   Pesan kesalahan kata sandi salah diarahkan ke error bag 'userDeletion', sehingga modal konfirmasi
   dapat otomatis terbuka kembali (`:show="$errors->userDeletion->isNotEmpty()"`) jika validasi gagal.
3. Event Dispatching Alpine.js:
   Memanfaatkan event bus kustom `$dispatch('open-modal', 'confirm-user-deletion')` dan `$dispatch('close')`
   untuk membuka dan menutup modal secara deklaratif tanpa menulis script vanilla JavaScript tambahan.
====================================================================================================
--}}

<section class="space-y-6">
    {{-- Header Peringatan Zona Bahaya --}}
    <header>
        <h2 class="text-lg font-bold text-rose-700 tracking-tight">
            {{ __('Hapus Akun Permanen') }}
        </h2>

        <p class="mt-1 text-xs text-slate-500">
            {{ __('Setelah akun Anda dihapus, semua sumber daya dan data yang terkait akan dihapus secara permanen. Harap pastikan Anda telah mengunduh semua data atau laporan yang ingin Anda simpan sebelum menghapus akun.') }}
        </p>
    </header>

    {{-- Tombol Pemicu Modal: Memancarkan Event 'open-modal' Alpine.js --}}
    <button
        type="button"
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        class="px-5 py-2 rounded-xl bg-rose-600 text-white text-xs font-bold hover:bg-rose-700 transition shadow-xs flex items-center gap-1.5 cursor-pointer"
    >
        <span class="material-symbols-outlined text-[16px]">warning</span>
        {{ __('Hapus Akun Saya') }}
    </button>

    {{-- Modal Konfirmasi Penghapusan Akun: Mengikat Status Error Bag 'userDeletion' --}}
    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        {{-- Formulir HTTP POST dengan Method Spoofing DELETE ke profile.destroy --}}
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-bold text-slate-900 tracking-tight">
                {{ __('Apakah Anda yakin ingin menghapus akun ini secara permanen?') }}
            </h2>

            <p class="mt-1 text-xs text-slate-500">
                {{ __('Setelah dihapus, semua data dan riwayat akan hilang. Silakan masukkan kata sandi Anda untuk mengonfirmasi bahwa Anda benar-benar ingin menghapus akun ini.') }}
            </p>

            {{-- Input Kata Sandi Konfirmasi --}}
            <div class="mt-6">
                <label for="password" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2 sr-only">{{ __('Kata Sandi') }}</label>
                <input
                    id="password"
                    name="password"
                    type="password"
                    required
                    class="w-full h-11 px-3.5 bg-slate-50 rounded-xl text-sm font-medium text-slate-800 border border-slate-200 focus:border-slate-900 focus:bg-white focus:outline-none transition"
                    placeholder="{{ __('Kata Sandi Anda') }}"
                />

                {{-- Pesan Kesalahan Khusus Bag userDeletion --}}
                @error('password', 'userDeletion')
                    <p class="mt-1.5 text-xs text-rose-600 flex items-center gap-1 font-medium">
                        <span class="material-symbols-outlined text-[14px]">error</span>
                        <span>{{ $message }}</span>
                    </p>
                @enderror
            </div>

            {{-- Footer Tombol Batal & Konfirmasi Hapus --}}
            <div class="mt-6 flex justify-end gap-3">
                <button type="button" x-on:click="$dispatch('close')" class="px-5 py-2 rounded-xl bg-slate-100 text-slate-700 border border-slate-200/80 text-xs font-bold hover:bg-slate-200 transition shadow-xs cursor-pointer">
                    {{ __('Batal') }}
                </button>

                <button type="submit" class="px-5 py-2 rounded-xl bg-rose-600 text-white text-xs font-bold hover:bg-rose-700 transition shadow-xs flex items-center gap-1.5 cursor-pointer">
                    <span class="material-symbols-outlined text-[16px]">delete_forever</span>
                    {{ __('Hapus Permanen') }}
                </button>
            </div>
        </form>
    </x-modal>
</section>
