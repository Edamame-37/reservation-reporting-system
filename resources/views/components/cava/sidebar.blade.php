{{-- 
  NAMA FILE      : sidebar.blade.php
  FUNGSIONALITAS : Sidebar Navigasi Kiri CAVA (Structural Left Rail)
  DESKRIPSI      : Menampilkan menu navigasi modul operasional kampus sesuai dengan hak wewenang peran (Petugas/Admin/Pengguna/Publik).
  CARA KERJA     : Menerima props 'role' (petugas|admin|user|public) dan 'active' (nama halaman aktif) untuk menentukan daftar rute dan state highlight.
--}}

@props([
    'role' => 'petugas',
    'active' => 'dashboard'
])

<!-- 
  ELEMEN       : Sidebar Navigasi Structural Left Rail (Lebar 256px / w-64, Top 100px)
  KEGUNAAN     : Menyediakan akses instan ke seluruh modul sistem reservasi dan pelaporan sarpras.
  CARA KERJA   : Menerapkan kelas aktif bg-primary-container text-on-primary pada menu yang sesuai props 'active'.
-->
<aside class="fixed left-0 top-[100px] bottom-0 w-64 bg-surface-container-lowest border-r border-outline-variant flex flex-col z-40">
    {{-- Header Sidebar --}}
    <div class="p-space-lg border-b border-outline-variant">
        <span class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant">Navigasi Ruang & Sesi</span>
        <div class="mt-1 font-headline-sm text-headline-sm text-primary font-bold">
            @if($role === 'admin')
                Konsol Biro Sarpras
            @elseif($role === 'petugas')
                Operasional Sarpras
            @elseif($role === 'user')
                Portal Mahasiswa/Dosen
            @else
                Direktori Kampus
            @endif
        </div>
    </div>

    {{-- Menu List --}}
    <nav class="flex-1 p-space-md flex flex-col gap-1 overflow-y-auto">
        @if($role === 'petugas')
            {{-- Menu Petugas Sarpras --}}
            <a href="{{ url('/petugas/dashboard') }}" class="flex items-center justify-between px-space-md py-space-sm rounded font-label-lg text-label-lg transition-colors {{ $active === 'dashboard' ? 'bg-primary-container text-on-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' }}">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">dashboard</span>
                    Dashboard Monitoring
                </span>
                <span class="font-data-mono text-[10px] px-1.5 py-0.5 bg-secondary-container text-on-secondary-container rounded font-bold">LIVE</span>
            </a>

            <a href="{{ url('/petugas/reservation-management') }}" class="flex items-center justify-between px-space-md py-space-sm rounded font-label-lg text-label-lg transition-colors {{ $active === 'reservation-management' ? 'bg-primary-container text-on-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' }}">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">approval</span>
                    Verifikasi & Approval
                </span>
                <span class="font-label-sm text-label-sm px-1.5 py-0.2 bg-error-container text-on-error-container rounded-full font-bold">8</span>
            </a>

            <a href="{{ url('/petugas/report-management') }}" class="flex items-center justify-between px-space-md py-space-sm rounded font-label-lg text-label-lg transition-colors {{ $active === 'report-management' ? 'bg-primary-container text-on-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' }}">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">build</span>
                    Tiket Perbaikan & Aset
                </span>
                <span class="font-label-sm text-label-sm px-1.5 py-0.2 bg-tertiary-fixed text-on-tertiary-fixed rounded-full font-bold">5</span>
            </a>

            <a href="{{ url('/public/availability') }}" class="flex items-center justify-between px-space-md py-space-sm rounded font-label-lg text-label-lg transition-colors {{ $active === 'availability' ? 'bg-primary-container text-on-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' }}">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">calendar_month</span>
                    Matriks Jadwal 30m
                </span>
            </a>

        @elseif($role === 'admin')
            {{-- Menu Super Admin --}}
            <a href="{{ url('/admin/dashboard') }}" class="flex items-center justify-between px-space-md py-space-sm rounded font-label-lg text-label-lg transition-colors {{ $active === 'dashboard' ? 'bg-primary-container text-on-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' }}">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">analytics</span>
                    Statistik & Analitik
                </span>
                <span class="font-data-mono text-[10px] px-1.5 py-0.5 bg-surface-container rounded">LIVE</span>
            </a>

            <a href="{{ url('/admin/user-management') }}" class="flex items-center justify-between px-space-md py-space-sm rounded font-label-lg text-label-lg transition-colors {{ $active === 'user-management' ? 'bg-primary-container text-on-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' }}">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">how_to_reg</span>
                    Verifikasi User (UR15)
                </span>
                <span class="font-label-sm text-label-sm px-1.5 py-0.2 bg-error-container text-on-error-container rounded-full font-bold">5</span>
            </a>

            <a href="{{ url('/admin/facility-master') }}" class="flex items-center justify-between px-space-md py-space-sm rounded font-label-lg text-label-lg transition-colors {{ $active === 'facility-master' ? 'bg-primary-container text-on-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' }}">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">domain</span>
                    Master Fasilitas (CRUD)
                </span>
            </a>

            <a href="{{ url('/admin/export-report') }}" class="flex items-center justify-between px-space-md py-space-sm rounded font-label-lg text-label-lg transition-colors {{ $active === 'export-report' ? 'bg-primary-container text-on-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' }}">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">download</span>
                    Ekspor Laporan Resmi
                </span>
            </a>

        @elseif($role === 'user')
            {{-- Menu Mahasiswa / Dosen --}}
            <a href="{{ url('/dashboard') }}" class="flex items-center justify-between px-space-md py-space-sm rounded font-label-lg text-label-lg transition-colors {{ $active === 'dashboard' ? 'bg-primary-container text-on-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' }}">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">space_dashboard</span>
                    Dasbor Utama
                </span>
            </a>

            <a href="{{ url('/user/reservation-form') }}" class="flex items-center justify-between px-space-md py-space-sm rounded font-label-lg text-label-lg transition-colors {{ $active === 'reservation-form' ? 'bg-primary-container text-on-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' }}">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">add_circle</span>
                    Pengajuan Reservasi
                </span>
            </a>

            <a href="{{ url('/user/reservation-history') }}" class="flex items-center justify-between px-space-md py-space-sm rounded font-label-lg text-label-lg transition-colors {{ $active === 'reservation-history' ? 'bg-primary-container text-on-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' }}">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">history</span>
                    Riwayat Pinjaman
                </span>
            </a>

            <a href="{{ url('/user/report-form') }}" class="flex items-center justify-between px-space-md py-space-sm rounded font-label-lg text-label-lg transition-colors {{ $active === 'report-form' ? 'bg-primary-container text-on-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' }}">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">report_problem</span>
                    Lapor Kerusakan (UR06)
                </span>
            </a>

            <a href="{{ url('/user/report-history') }}" class="flex items-center justify-between px-space-md py-space-sm rounded font-label-lg text-label-lg transition-colors {{ $active === 'report-history' ? 'bg-primary-container text-on-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' }}">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">checklist</span>
                    Status Laporan Saya
                </span>
            </a>

        @else
            {{-- Menu Publik / Visitor --}}
            <a href="{{ url('/') }}" class="flex items-center justify-between px-space-md py-space-sm rounded font-label-lg text-label-lg transition-colors {{ $active === 'home' ? 'bg-primary-container text-on-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' }}">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">home</span>
                    Beranda Publik
                </span>
            </a>

            <a href="{{ url('/public/catalog') }}" class="flex items-center justify-between px-space-md py-space-sm rounded font-label-lg text-label-lg transition-colors {{ $active === 'catalog' ? 'bg-primary-container text-on-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' }}">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">inventory_2</span>
                    Katalog Fasilitas
                </span>
            </a>

            <a href="{{ url('/public/availability') }}" class="flex items-center justify-between px-space-md py-space-sm rounded font-label-lg text-label-lg transition-colors {{ $active === 'availability' ? 'bg-primary-container text-on-primary font-bold' : 'text-on-surface-variant hover:bg-surface-container hover:text-on-surface' }}">
                <span class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">event_available</span>
                    Matriks Slot 30 Menit
                </span>
            </a>
        @endif
    </nav>

    {{-- Footer Sidebar / Sesi Info --}}
    <div class="p-space-md border-t border-outline-variant bg-surface-container-low">
        <div class="flex items-center justify-between text-on-surface-variant">
            <span class="font-label-sm text-label-sm font-data-mono text-data-mono">v2.4-BUILD-92</span>
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="font-label-sm text-label-sm text-error hover:underline flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">logout</span>
                    Keluar Sesi
                </button>
            </form>
        </div>
    </div>
</aside>
