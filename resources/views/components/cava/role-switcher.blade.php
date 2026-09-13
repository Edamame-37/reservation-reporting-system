{{-- 
  NAMA FILE      : role-switcher.blade.php
  FUNGSIONALITAS : Bar Pengalih Peran CAVA (Testing & Role Navigation Strip)
  DESKRIPSI      : Menampilkan strip tab navigasi cepat untuk beralih antar peran (Publik, Pengguna/Mahasiswa, Petugas Sarpras, Super Admin).
  CARA KERJA     : Props 'activeRole' menentukan tab peran mana yang di-highlight aktif.
--}}

@props([
    'activeRole' => 'public'
])

<!-- 
  ELEMEN       : Role Switcher Bar Navigasi Antar Peran
  KEGUNAAN     : Mempermudah peninjauan dan pengujian mockup/view di semua peran hak akses.
-->
<div class="bg-primary px-margin-lg py-2 flex flex-wrap items-center justify-between text-on-primary border-b border-outline-variant/30">
    <div class="flex items-center gap-space-md flex-wrap">
        <span class="font-label-sm text-label-sm uppercase tracking-wider text-on-primary-container flex items-center gap-1 font-semibold">
            <span class="material-symbols-outlined text-[15px]">badge</span> Mode Peran:
        </span>
        <div class="flex items-center gap-space-xs flex-wrap text-xs">
            <a href="{{ url('/') }}" class="px-space-md py-0.5 rounded-full font-label-sm text-label-sm transition-all {{ $activeRole === 'public' ? 'bg-surface-container-lowest text-primary font-bold shadow-sm' : 'bg-surface-container-high/30 text-on-primary hover:bg-surface-container-high/50' }}">
                1. Publik
            </a>
            <a href="{{ url('/dashboard') }}" class="px-space-md py-0.5 rounded-full font-label-sm text-label-sm transition-all {{ $activeRole === 'user' ? 'bg-surface-container-lowest text-primary font-bold shadow-sm' : 'bg-surface-container-high/30 text-on-primary hover:bg-surface-container-high/50' }}">
                2. Pengguna (Mhs/Dosen)
            </a>
            <a href="{{ url('/petugas/dashboard') }}" class="px-space-md py-0.5 rounded-full font-label-sm text-label-sm transition-all {{ $activeRole === 'petugas' ? 'bg-surface-container-lowest text-primary font-bold shadow-sm' : 'bg-surface-container-high/30 text-on-primary hover:bg-surface-container-high/50' }}">
                3. Petugas Sarpras
            </a>
            <a href="{{ url('/admin/dashboard') }}" class="px-space-md py-0.5 rounded-full font-label-sm text-label-sm transition-all {{ $activeRole === 'admin' ? 'bg-surface-container-lowest text-primary font-bold shadow-sm' : 'bg-surface-container-high/30 text-on-primary hover:bg-surface-container-high/50' }}">
                4. Super Admin
            </a>
        </div>
    </div>
    <div class="hidden sm:flex items-center gap-space-md">
        <span class="font-data-mono text-[11px] text-secondary-fixed flex items-center gap-1.5 font-medium">
            <span class="w-2 h-2 rounded-full bg-secondary-fixed animate-pulse"></span>
            SIM-SARPRAS ROLE SWITCHER READY
        </span>
    </div>
</div>
