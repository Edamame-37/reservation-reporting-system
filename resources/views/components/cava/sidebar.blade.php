{{-- 
  NAMA FILE      : sidebar.blade.php
  FUNGSIONALITAS : Sidebar Navigasi Kiri CAVA (Role-Aware Minimalist Navigation Rail)
  DESKRIPSI      : Menampilkan menu navigasi modul operasional kampus sesuai dengan hak wewenang peran (Petugas/Admin/Pengguna/Publik) dengan offset 64px.
  CARA KERJA     : Menerima props 'role' (petugas|admin|user|public) dan 'active' (nama halaman aktif) untuk menentukan daftar rute dan state highlight.
--}}

@props([
    'role' => 'petugas',
    'active' => 'dashboard'
])

<!-- 
  ELEMEN       : Sidebar Navigasi Structural Left Rail (Lebar 256px / w-64, Top 64px / top-16)
  KEGUNAAN     : Menyediakan akses instan ke modul sistem sesuai otorisasi peran pengguna.
  CARA KERJA     : Menerapkan kelas aktif bg-slate-900 text-white pada rute yang sesuai props 'active'.
-->
<aside class="fixed left-0 top-16 bottom-0 w-64 bg-white border-r border-slate-200/80 flex flex-col z-30">
    {{-- Header Sidebar --}}
    <div class="p-5 border-b border-slate-100">
        <span class="text-[11px] font-bold uppercase tracking-wider text-slate-400">Navigasi Modul</span>
        <div class="mt-1 text-sm font-bold text-slate-900">
            @if($role === 'admin')
                Konsol Biro Sarpras & TIK
            @elseif($role === 'petugas')
                Pusat Operasional Sarpras
            @elseif($role === 'user')
                Portal Mahasiswa & Dosen
            @else
                Direktori Fasilitas Publik
            @endif
        </div>
    </div>

    {{-- Menu List per Role --}}
    <nav class="flex-1 p-3 flex flex-col gap-1 overflow-y-auto">
        @if($role === 'petugas')
            {{-- Menu Petugas Sarpras --}}
            <a href="{{ url('/petugas/dashboard') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg text-xs sm:text-sm transition-all {{ $active === 'dashboard' ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[18px]">dashboard</span>
                    <span>Dasbor Operasional</span>
                </span>
                <span class="text-[10px] px-1.5 py-0.5 rounded font-bold {{ $active === 'dashboard' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-600' }}">LIVE</span>
            </a>

            <a href="{{ url('/petugas/reservation-management') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg text-xs sm:text-sm transition-all {{ $active === 'reservation-management' ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[18px]">approval</span>
                    <span>Antrean Reservasi</span>
                </span>
                <span class="text-[11px] px-2 py-0.5 rounded-full font-bold bg-amber-100 text-amber-800">8</span>
            </a>

            <a href="{{ url('/petugas/report-management') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg text-xs sm:text-sm transition-all {{ $active === 'report-management' ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[18px]">build</span>
                    <span>Penanganan Laporan</span>
                </span>
                <span class="text-[11px] px-2 py-0.5 rounded-full font-bold bg-rose-100 text-rose-800">5</span>
            </a>

            <a href="{{ url('/public/availability') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg text-xs sm:text-sm transition-all {{ $active === 'availability' ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[18px]">calendar_month</span>
                    <span>Matriks Jadwal 30m</span>
                </span>
            </a>

        @elseif($role === 'admin')
            {{-- Menu Super Admin --}}
            <a href="{{ url('/admin/dashboard') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg text-xs sm:text-sm transition-all {{ $active === 'dashboard' ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[18px]">insights</span>
                    <span>Konsol Eksekutif</span>
                </span>
            </a>

            <a href="{{ url('/admin/user-management') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg text-xs sm:text-sm transition-all {{ $active === 'user-management' ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[18px]">manage_accounts</span>
                    <span>Manajemen Akun Sivitas</span>
                </span>
                <span class="text-[11px] px-2 py-0.5 rounded-full font-bold bg-amber-100 text-amber-800">5</span>
            </a>

            <a href="{{ url('/admin/facility-master') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg text-xs sm:text-sm transition-all {{ $active === 'facility-master' ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[18px]">domain</span>
                    <span>Master Fasilitas</span>
                </span>
            </a>

            <a href="{{ url('/admin/export-report') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg text-xs sm:text-sm transition-all {{ $active === 'export-report' ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[18px]">file_download</span>
                    <span>Laporan & Analitik</span>
                </span>
            </a>

        @elseif($role === 'user')
            {{-- Menu Mahasiswa / Dosen --}}
            <a href="{{ url('/user/dashboard') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg text-xs sm:text-sm transition-all {{ $active === 'dashboard' ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[18px]">home</span>
                    <span>Dasbor Saya</span>
                </span>
            </a>

            <a href="{{ url('/user/reservation-form') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg text-xs sm:text-sm transition-all {{ $active === 'reservation-form' ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[18px]">add_circle</span>
                    <span>Ajukan Reservasi</span>
                </span>
            </a>

            <a href="{{ url('/user/reservation-history') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg text-xs sm:text-sm transition-all {{ $active === 'reservation-history' ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[18px]">history</span>
                    <span>Riwayat Reservasi</span>
                </span>
                <span class="text-[11px] px-2 py-0.5 rounded-full font-bold bg-slate-100 text-slate-700">12</span>
            </a>

            <a href="{{ url('/user/report-form') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg text-xs sm:text-sm transition-all {{ $active === 'report-form' ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[18px]">report_problem</span>
                    <span>Lapor Kerusakan</span>
                </span>
            </a>

            <a href="{{ url('/user/report-history') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg text-xs sm:text-sm transition-all {{ $active === 'report-history' ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[18px]">checklist</span>
                    <span>Status Laporan</span>
                </span>
                <span class="text-[11px] px-2 py-0.5 rounded-full font-bold bg-amber-100 text-amber-800">2</span>
            </a>

        @else
            {{-- Menu Publik / Pengunjung --}}
            <a href="{{ url('/') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg text-xs sm:text-sm transition-all {{ $active === 'home' ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[18px]">explore</span>
                    <span>Beranda Publik</span>
                </span>
            </a>

            <a href="{{ url('/public/catalog') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg text-xs sm:text-sm transition-all {{ $active === 'catalog' ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[18px]">domain</span>
                    <span>Direktori Fasilitas</span>
                </span>
            </a>

            <a href="{{ url('/public/availability') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg text-xs sm:text-sm transition-all {{ $active === 'availability' ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[18px]">calendar_month</span>
                    <span>Matriks Jadwal</span>
                </span>
            </a>
        @endif
    </nav>

    {{-- Footer Sidebar: Keluar Sesi --}}
    <div class="p-3 border-t border-slate-100 bg-slate-50/50">
        @if($role !== 'public')
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold text-rose-600 hover:bg-rose-50 transition">
                    <span class="material-symbols-outlined text-[16px]">logout</span>
                    <span>Keluar Sesi</span>
                </button>
            </form>
        @else
            <div class="text-center py-1">
                <span class="text-[11px] text-slate-400">CAVA Universitas &copy; 2024</span>
            </div>
        @endif
    </div>
</aside>
