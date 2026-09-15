<x-guest-layout>
    <!-- MOCKUP LOGIN INTERAKTIF BERBASIS ROLE (TANPA BACKEND) -->
    <div x-data="{
        selectedRole: 'user',
        login() {
            window.location.href = '/' + this.selectedRole + '/dashboard';
        }
    }" class="flex flex-col gap-space-md">

        <!-- Role Selection -->
        <div class="mb-4">
            <h3 class="font-label-md text-label-md text-on-surface font-semibold mb-2 text-center">Simulasi Login (Pilih Role Anda)</h3>
            <div class="grid grid-cols-3 gap-2">
                <button type="button" @click="selectedRole = 'user'" 
                    :class="selectedRole === 'user' ? 'bg-primary text-on-primary shadow-md border-transparent' : 'bg-surface-container text-on-surface hover:bg-surface-container-high border-outline-variant'"
                    class="py-2 px-1 rounded-lg border font-label-sm text-label-sm font-semibold transition-all flex flex-col items-center justify-center gap-1">
                    <span class="material-symbols-outlined text-[20px]">person</span>
                    Pengguna
                </button>
                <button type="button" @click="selectedRole = 'petugas'" 
                    :class="selectedRole === 'petugas' ? 'bg-primary text-on-primary shadow-md border-transparent' : 'bg-surface-container text-on-surface hover:bg-surface-container-high border-outline-variant'"
                    class="py-2 px-1 rounded-lg border font-label-sm text-label-sm font-semibold transition-all flex flex-col items-center justify-center gap-1">
                    <span class="material-symbols-outlined text-[20px]">support_agent</span>
                    Petugas
                </button>
                <button type="button" @click="selectedRole = 'admin'" 
                    :class="selectedRole === 'admin' ? 'bg-primary text-on-primary shadow-md border-transparent' : 'bg-surface-container text-on-surface hover:bg-surface-container-high border-outline-variant'"
                    class="py-2 px-1 rounded-lg border font-label-sm text-label-sm font-semibold transition-all flex flex-col items-center justify-center gap-1">
                    <span class="material-symbols-outlined text-[20px]">admin_panel_settings</span>
                    Admin
                </button>
            </div>
            
            <div class="mt-3 p-2 bg-secondary-container text-on-secondary-container rounded text-[11px] text-center font-data-mono">
                Destinasi Navigasi: <span class="font-bold text-primary" x-text="'/' + selectedRole + '/dashboard'"></span>
            </div>
        </div>

        <div class="border-t border-outline-variant/50 pt-space-md mb-2">
            <h4 class="font-label-sm text-label-sm text-on-surface-variant text-center uppercase tracking-wider mb-4">Informasi Login Visual</h4>
            
            <!-- Email Address (Dummy) -->
            <div>
                <x-input-label for="email" :value="__('Email (Visual Only)')" />
                <x-text-input id="email" class="block mt-1 w-full bg-surface-container-lowest text-on-surface-variant" type="email" name="email" value="mockup@univ.ac.id" readonly />
            </div>

            <!-- Password (Dummy) -->
            <div class="mt-4">
                <x-input-label for="password" :value="__('Password (Visual Only)')" />
                <x-text-input id="password" class="block mt-1 w-full bg-surface-container-lowest text-on-surface-variant" type="password" name="password" value="password" readonly />
            </div>

            <!-- Remember Me -->
            <div class="block mt-4 flex items-center justify-between">
                <label for="remember_me" class="inline-flex items-center cursor-pointer">
                    <input id="remember_me" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-primary shadow-sm focus:ring-primary dark:focus:ring-offset-gray-800" name="remember" checked disabled>
                    <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('Ingat Saya') }}</span>
                </label>
                <a class="underline text-sm text-primary hover:text-primary-container" href="#">
                    {{ __('Lupa password?') }}
                </a>
            </div>
        </div>

        <div class="flex items-center justify-between mt-4">
            <a class="text-sm font-semibold text-on-surface-variant hover:text-primary transition-colors flex items-center gap-1" href="{{ route('register') }}">
                <span class="material-symbols-outlined text-[16px]">person_add</span> Daftar Akun Baru
            </a>

            <!-- MOCKUP LOGIN BUTTON (No form submission, pure client-side redirect) -->
            <button type="button" @click="login()" class="inline-flex items-center gap-space-xs px-space-xl py-space-sm bg-primary border border-transparent rounded-lg font-semibold text-xs text-on-primary uppercase tracking-widest hover:bg-primary-container focus:bg-primary-container active:bg-primary-container focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150 shadow-sm">
                {{ __('Masuk (Mockup)') }}
                <span class="material-symbols-outlined text-[16px]">login</span>
            </button>
        </div>
    </div>
</x-guest-layout>
