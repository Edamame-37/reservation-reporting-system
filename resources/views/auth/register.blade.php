<x-guest-layout>
    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data">
        @csrf

        <div class="mb-4 p-3 bg-surface-container-low text-on-surface-variant text-sm rounded-lg border border-outline-variant/50">
            <strong>Informasi Pendaftaran:</strong> Akun Anda akan berstatus <em>Pending</em> dan membutuhkan verifikasi dari Admin Sarpras (maks. 1x24 jam) sebelum dapat digunakan untuk login.
        </div>

        <!-- Name -->
        <div>
            <x-input-label for="name" value="Nama Lengkap" />
            <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Identifier (NIM/NIP) -->
        <div class="mt-4">
            <x-input-label for="identifier" value="NIM / NIP / NIDN" />
            <x-text-input id="identifier" class="block mt-1 w-full" type="text" name="identifier" :value="old('identifier')" required placeholder="Contoh: 2108561044" />
            <x-input-error :messages="$errors->get('identifier')" class="mt-2" />
        </div>

        <!-- Kategori Sivitas -->
        <div class="mt-4">
            <x-input-label for="role_type" value="Kategori Sivitas" />
            <select id="role_type" name="role_type" class="block mt-1 w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm" required>
                <option value="" disabled {{ old('role_type') ? '' : 'selected' }}>-- Pilih Kategori --</option>
                <option value="mahasiswa" {{ old('role_type') == 'mahasiswa' ? 'selected' : '' }}>Mahasiswa</option>
                <option value="dosen" {{ old('role_type') == 'dosen' ? 'selected' : '' }}>Dosen Tetap</option>
                <option value="staf" {{ old('role_type') == 'staf' ? 'selected' : '' }}>Staf Akademik / BEM</option>
            </select>
            <x-input-error :messages="$errors->get('role_type')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email Kampus')" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Bukti KTM/SK (Upload) -->
        <div class="mt-4">
            <x-input-label for="identity_proof" value="Unggah Bukti Identitas (KTM / SK Penugasan)" />
            <div class="mt-1 flex items-center justify-center w-full" x-data="{ fileName: '' }">
                <label for="identity_proof" class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-lg cursor-pointer bg-gray-50 dark:bg-gray-800 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                        <svg class="w-8 h-8 mb-4 text-gray-500 dark:text-gray-400" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 16">
                            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 13h3a3 3 0 0 0 0-6h-.025A5.56 5.56 0 0 0 16 6.5 5.5 5.5 0 0 0 5.207 5.021C5.137 5.017 5.071 5 5 5a4 4 0 0 0 0 8h2.167M10 15V6m0 0L8 8m2-2 2 2"/>
                        </svg>
                        <p class="mb-2 text-sm text-gray-500 dark:text-gray-400" x-show="!fileName"><span class="font-semibold">Klik untuk mengunggah</span> atau drag & drop</p>
                        <p class="mb-2 text-sm text-indigo-600 dark:text-indigo-400 font-semibold text-center px-4 break-all" x-show="fileName" x-text="fileName" style="display: none;"></p>
                        <p class="text-xs text-gray-500 dark:text-gray-400">PDF, JPG, atau PNG (Maks. 2MB)</p>
                    </div>
                    <input id="identity_proof" name="identity_proof" type="file" accept=".pdf,image/jpeg,image/png" class="hidden" required @change="fileName = $event.target.files[0].name" />
                </label>
            </div>
            <x-input-error :messages="$errors->get('identity_proof')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />
            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="new-password" minlength="8" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="password_confirmation" class="block mt-1 w-full"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" minlength="8" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-6">
            <a class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('login') }}">
                {{ __('Sudah punya akun?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Daftar & Ajukan Verifikasi') }}
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
