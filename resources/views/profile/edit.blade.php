@php
    $layout = 'app-layout';
    if(auth()->user()->hasRole('admin')) $layout = 'admin-layout';
    elseif(auth()->user()->hasRole('petugas')) $layout = 'petugas-layout';
@endphp

<x-dynamic-component :component="$layout" title="Pengaturan Profil Akun">
    <div class="mb-6">
        <div class="flex items-center gap-2 text-xs text-slate-500 mb-2 font-medium">
            <a href="{{ url('/dashboard') }}" class="hover:text-slate-900 transition">Dasbor</a>
            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
            <span class="text-slate-900">Manajemen Profil</span>
        </div>
        <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Manajemen Profil & Keamanan Akun</h1>
        <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Perbarui informasi identitas profil dan ubah kata sandi untuk menjaga keamanan akun Anda.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 items-start">
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-8 shadow-xs">
                @include('profile.partials.update-profile-information-form')
            </div>

            <div class="bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-8 shadow-xs border-l-4 border-l-rose-500">
                @include('profile.partials.delete-user-form')
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200/80 p-6 sm:p-8 shadow-xs">
            @include('profile.partials.update-password-form')
        </div>
    </div>
</x-dynamic-component>
