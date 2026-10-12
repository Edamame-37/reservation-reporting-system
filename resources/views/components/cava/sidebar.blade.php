{{-- 
  ====================================================================================================
  NAMA FILE      : sidebar.blade.php
  LOKASI         : resources/views/components/cava/sidebar.blade.php
  FUNGSIONALITAS : Komponen Navigasi Samping Kiri Multi-Role (Role-Aware Minimalist Navigation Rail)
  MODUL CAVA     : 9. Portal Pengguna (Sivitas): Dasbor Pribadi & Ringkasan Tiket Aktif (Serta Multi-Role)
  ====================================================================================================

  DESKRIPSI ARSITEKTUR & CARA KERJA SISTEM:
  1. Role-Aware Dynamic Rendering:
     - Komponen ini mengevaluasi nilai properti `$role` ('petugas', 'admin', 'user', atau 'public')
       untuk merender kumpulan tautan navigasi yang relevan sesuai wewenang dan modul pengguna.
     - Khusus peran sivitas akademika (`role="user"`), menu difokuskan secara terintegrasi pada:
       * Dasbor Saya (`/user/dashboard`)
       * Reservasi Ruangan (`/user/reservation-history` & `/user/reservation-form`)
       * Pelaporan Kerusakan (`/user/report-history` & `/user/report-form`)
  
  2. Kueri Agregasi Badge Selektif & Efisiensi:
     - Blok `@php` menghitung jumlah antrean secara real-time hanya untuk peran operasional ('petugas'
       dan 'admin'). Untuk peran 'user', kueri badge dieliminasi guna menghindari overhead kueri
       pada setiap pemuatan halaman serta menerapkan prinsip desain Pure Minimalist Navigation.
  
  3. Desain Responsif & Sinkronisasi Alpine.js:
     - Memanfaatkan variabel reactive `sidebarOpen` dari master layout (`layouts.app` / `layouts.admin`):
       * Layar Mobile (< md): Berupa *off-canvas drawer* dengan backdrop overlay semi-transparan (`bg-slate-900/50`).
         Mengetuk backdrop akan menutup sidebar (`@click="sidebarOpen = false"`).
       * Layar Desktop (>= md): Menempati rel navigasi kiri tetap selebar 256px (`w-64`) dengan border pembatas.
  
  4. Penanda Rute Aktif (Active State Highlighting):
     - Membandingkan prop `$active` dengan slug halaman saat ini. Menu aktif menerima penataan kontras
       tinggi (`bg-slate-900 text-white font-semibold shadow-xs`) sementara menu tidak aktif bergaya netral
       (`text-slate-600 hover:bg-slate-50 hover:text-slate-900`).
  ====================================================================================================
--}}

{{-- Direktif @props: Menerima parameter peran pengguna dan penanda slug rute yang sedang aktif --}}
@props([
    // Peran pengguna: 'petugas', 'admin', 'user', atau 'public' (default: 'petugas')
    'role' => 'petugas',
    // Slug nama halaman yang sedang diakses untuk penanda sorotan visual (default: 'dashboard')
    'active' => 'dashboard'
])

{{-- 
  Blok @php: Perhitungan Agregasi Badge Notifikasi
  - Menghitung jumlah permohonan tertunda untuk petugas dan verifikasi akun untuk admin secara efisien.
--}}
@php
    // Inisialisasi array penampung jumlah badge untuk seluruh peran sistem
    $sidebarCounts = [
        'petugas_reservations' => 0,
        'petugas_reports'      => 0,
        'admin_users'          => 0,
        'user_reservations'    => 0,
        'user_reports'         => 0,
    ];

    // Jika pengguna memiliki peran petugas sarpras, hitung antrean reservasi & laporan yang butuh penanganan
    if ($role === 'petugas') {
        // Menghitung reservasi dengan status pending (antrean persetujuan)
        $sidebarCounts['petugas_reservations'] = \App\Models\Reservation::where('status', 'pending')->count();
        // Menghitung laporan kerusakan dengan status baru atau sedang diproses
        $sidebarCounts['petugas_reports'] = \App\Models\DamageReport::whereIn('status', ['baru', 'diproses'])->count();
    } elseif ($role === 'admin') {
        // Jika peran admin, hitung jumlah registrasi mahasiswa/dosen yang masih berstatus pending
        $sidebarCounts['admin_users'] = \App\Models\User::where('status', 'pending')->count();
    }
    // Catatan Arsitektur: Query badge untuk peran 'user' ditiadakan agar antarmuka portal sivitas tetap bersih dan cepat
@endphp

{{-- 
  1. Backdrop Overlay Layar Mobile (Viewport < md):
  - x-show="sidebarOpen": Tampil hanya ketika state Alpine sidebarOpen bernilai true.
  - x-cloak: Mencegah kedipan overlay saat inisialisasi awal skrip Alpine.
  - @click="sidebarOpen = false": Menutup drawer navigasi ketika pengguna mengetuk area luar.
--}}
<div x-show="sidebarOpen" x-cloak class="fixed inset-0 bg-slate-900/50 z-40 md:hidden" @click="sidebarOpen = false"></div>

{{-- 
  2. Elemen <aside>: Struktur Utama Rail Navigasi Kiri (Lebar 256px / w-64, Full Height)
  - :class translate-x: Menggeser panel masuk/keluar layar secara halus via CSS hardware-accelerated transform.
  - z-50: Memastikan posisi sidebar berada di atas elemen konten utama lainnya.
--}}
<aside :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen, 'transition-transform duration-300': isLoaded}" class="fixed left-0 top-0 bottom-0 w-64 bg-white border-r border-slate-200/80 flex flex-col z-50">
    
    {{-- Header Sidebar: Logo Resmi Kampus & Identitas Modul Sistem --}}
    @if($role !== 'public')
    <div class="h-16 px-4 sm:px-5 flex items-center border-b border-slate-200/80 bg-white/95">
        {{-- Tautan mengarah kembali ke beranda utama dengan efek hover visual pada nama modul --}}
        <a href="{{ url('/') }}" class="flex items-center gap-3 group">
            <img src="{{ asset('assets/images/logo.webp') }}" alt="CAVA Logo" class="h-8 sm:h-9 w-auto">
            <div class="flex flex-col">
                {{-- Penamaan Brand Dinamis Sesuai Peran Pengguna --}}
                <span class="font-bold text-slate-900 text-sm leading-tight tracking-tight group-hover:text-blue-900 transition-colors">
                    @if($role === 'admin') CAVA Admin
                    @elseif($role === 'petugas') CAVA Operasional
                    @elseif($role === 'user') CAVA Portal
                    @else CAVA @endif
                </span>
                <span class="text-[10px] text-slate-500 font-medium">Campus Venue Access</span>
            </div>
        </a>
    </div>
    @endif

    {{-- Kategori Label Navigasi: Menampilkan konteks konsol operasional yang sedang aktif --}}
    <div class="p-4 border-b border-slate-100 bg-slate-50/30">
        <span class="text-[10px] font-bold uppercase tracking-wider text-slate-400">Navigasi Modul</span>
        <div class="mt-1 text-xs font-bold text-slate-900">
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

    {{-- 
      Daftar Navigasi Peran (Navigation Menu Items):
      - flex-1 & overflow-y-auto memastikan menu dapat di-scroll vertikal jika daftar link melampaui tinggi layar.
    --}}
    <nav class="flex-1 p-3 flex flex-col gap-1 overflow-y-auto">
        
        {{-- ========================================================================== --}}
        {{-- SEKSI 1: MENU KHUSUS PERAN PETUGAS SARPRAS                                 --}}
        {{-- ========================================================================== --}}
        @if($role === 'petugas')
            {{-- Menu Dasbor Operasional dengan Indikator Status Denyut Real-Time (Live Pulse) --}}
            <a href="{{ url('/petugas/dashboard') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs sm:text-sm transition-all {{ $active === 'dashboard' ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[18px]">dashboard</span>
                    <span>Dasbor Operasional</span>
                </span>
                <span class="inline-flex items-center gap-1.5 text-[10px] font-medium px-2 py-0.5 rounded-full {{ $active === 'dashboard' ? 'bg-white/15 text-white' : 'bg-slate-100 text-slate-600' }}">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Live</span>
                </span>
            </a>

            {{-- Menu Antrean Reservasi Ruangan Kampus --}}
            <a href="{{ url('/petugas/reservation-management') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs sm:text-sm transition-all {{ $active === 'reservation-management' ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[18px]">inbox_customize</span>
                    <span>Antrean Reservasi</span>
                </span>
                {{-- Badge counter jumlah permohonan yang menunggu persetujuan --}}
                @if($sidebarCounts['petugas_reservations'] > 0)
                    <span class="text-[11px] px-2 py-0.5 rounded-full font-bold {{ $active === 'reservation-management' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-700' }}">{{ $sidebarCounts['petugas_reservations'] }}</span>
                @endif
            </a>

            {{-- Menu Penanganan Tiket Laporan Kerusakan --}}
            <a href="{{ url('/petugas/report-management') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs sm:text-sm transition-all {{ $active === 'report-management' ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[18px]">build</span>
                    <span>Penanganan Laporan</span>
                </span>
                {{-- Badge counter jumlah laporan kerusakan aktif --}}
                @if($sidebarCounts['petugas_reports'] > 0)
                    <span class="text-[11px] px-2 py-0.5 rounded-full font-bold {{ $active === 'report-management' ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-700' }}">{{ $sidebarCounts['petugas_reports'] }}</span>
                @endif
            </a>

            {{-- Menu Matriks Jadwal Pemakaian Ruangan Per 30 Menit --}}
            <a href="{{ route('petugas.schedule-matrix') }}" class="flex items-center justify-between px-3 py-2.5 rounded-xl text-xs sm:text-sm transition-all {{ $active === 'schedule-matrix' ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[18px]">calendar_month</span>
                    <span>Matriks Jadwal 30m</span>
                </span>
            </a>

        {{-- ========================================================================== --}}
        {{-- SEKSI 2: MENU KHUSUS PERAN SUPER ADMIN BIRO SARPRAS                        --}}
        {{-- ========================================================================== --}}
        @elseif($role === 'admin')
            {{-- Menu Konsol Dasbor Eksekutif & Statistik --}}
            <a href="{{ url('/admin/dashboard') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg text-xs sm:text-sm transition-all {{ $active === 'dashboard' ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[18px]">insights</span>
                    <span>Konsol Eksekutif</span>
                </span>
            </a>

            {{-- Menu Manajemen Pengguna & Verifikasi Akun Pending --}}
            <a href="{{ url('/admin/user-management') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg text-xs sm:text-sm transition-all {{ $active === 'user-management' ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[18px]">manage_accounts</span>
                    <span>Manajemen Akun Sivitas</span>
                </span>
                {{-- Badge counter jumlah akun pending yang membutuhkan verifikasi admin --}}
                @if($sidebarCounts['admin_users'] > 0)
                    <span class="text-[11px] px-2 py-0.5 rounded-full font-bold bg-amber-100 text-amber-800">{{ $sidebarCounts['admin_users'] }}</span>
                @endif
            </a>

            {{-- Menu Master Data Fasilitas Ruangan & Gedung --}}
            <a href="{{ url('/admin/facility-master') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg text-xs sm:text-sm transition-all {{ $active === 'facility-master' ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[18px]">domain</span>
                    <span>Master Fasilitas</span>
                </span>
            </a>

            {{-- Menu Rekapitulasi Laporan & Ekspor Resmi (PDF / Excel) --}}
            <a href="{{ url('/admin/export-report') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg text-xs sm:text-sm transition-all {{ $active === 'export-report' ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[18px]">file_download</span>
                    <span>Laporan & Analitik</span>
                </span>
            </a>

        {{-- ========================================================================== --}}
        {{-- SEKSI 3: MENU KHUSUS PERAN SIVITAS AKADEMIKA (MAHASISWA & DOSEN)           --}}
        {{-- ========================================================================== --}}
        @elseif($role === 'user')
            {{-- 
              1. Menu Dasbor Saya (/user/dashboard):
              - Titik masuk sentral sivitas untuk melihat agenda ruang terdekat dan rangkuman tiket
            --}}
            <a href="{{ url('/user/dashboard') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg text-xs sm:text-sm transition-all {{ $active === 'dashboard' ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[18px]">home</span>
                    <span>Dasbor Saya</span>
                </span>
            </a>

            {{-- 
              2. Menu Reservasi Ruangan (/user/reservation-history):
              - in_array($active, ['reservation-history', 'reservation-form']): Memberikan status aktif
                baik saat pengguna berada di halaman riwayat maupun sedang mengisi formulir pengajuan baru
            --}}
            <a href="{{ url('/user/reservation-history') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg text-xs sm:text-sm transition-all {{ in_array($active, ['reservation-history', 'reservation-form']) ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[18px]">calendar_month</span>
                    <span>Reservasi Ruangan</span>
                </span>
            </a>

            {{-- 
              3. Menu Pelaporan Kerusakan (/user/report-history):
              - in_array($active, ['report-history', 'report-form']): Memberikan status aktif
                baik saat membuka riwayat pengaduan maupun saat mengisi form pelaporan baru
            --}}
            <a href="{{ url('/user/report-history') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg text-xs sm:text-sm transition-all {{ in_array($active, ['report-history', 'report-form']) ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[18px]">handyman</span>
                    <span>Pelaporan Kerusakan</span>
                </span>
            </a>

        {{-- ========================================================================== --}}
        {{-- SEKSI 4: MENU PUBLIK & PENGUNJUNG TAMU                                     --}}
        {{-- ========================================================================== --}}
        @else
            {{-- Menu Beranda Portal Publik --}}
            <a href="{{ url('/') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg text-xs sm:text-sm transition-all {{ $active === 'home' ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[18px]">explore</span>
                    <span>Beranda Publik</span>
                </span>
            </a>

            {{-- Menu Direktori & Katalog Fasilitas Kampus --}}
            <a href="{{ url('/public/catalog') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg text-xs sm:text-sm transition-all {{ $active === 'catalog' ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[18px]">domain</span>
                    <span>Direktori Fasilitas</span>
                </span>
            </a>

            {{-- Menu Matriks Ketersediaan Ruangan Publik --}}
            <a href="{{ url('/public/availability') }}" class="flex items-center justify-between px-3 py-2.5 rounded-lg text-xs sm:text-sm transition-all {{ $active === 'availability' ? 'bg-slate-900 text-white font-semibold shadow-xs' : 'text-slate-600 hover:bg-slate-50 hover:text-slate-900' }}">
                <span class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[18px]">calendar_month</span>
                    <span>Matriks Jadwal</span>
                </span>
            </a>
        @endif
    </nav>

    {{-- 
      Footer Navigasi Sidebar (Akses Pengaturan Profil & Keluar Sesi):
      - Menampilkan tombol pengaturan profil dan tombol logout aman dengan perlindungan CSRF.
    --}}
    <div class="p-3 border-t border-slate-100 bg-slate-50/50">
        @if($role !== 'public')
            {{-- Tautan Pengaturan Profil Pengguna --}}
            <a href="{{ route('profile.edit') }}" class="w-full flex items-center justify-center gap-2 px-3 py-2 mb-2 rounded-lg text-xs font-semibold text-slate-700 hover:bg-slate-200 transition">
                <span class="material-symbols-outlined text-[16px]">manage_accounts</span>
                <span>Pengaturan Profil</span>
            </a>
            
            {{-- Form Logout dengan Metrik POST & Token CSRF Laravel --}}
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-xs font-semibold text-rose-600 hover:bg-rose-50 transition">
                    <span class="material-symbols-outlined text-[16px]">logout</span>
                    <span>Keluar Sesi</span>
                </button>
            </form>
        @else
            {{-- Label Hak Cipta untuk Pengunjung Publik --}}
            <div class="text-center py-1">
                <span class="text-[11px] text-slate-400">CAVA Universitas &copy; 2024</span>
            </div>
        @endif
    </div>
</aside>
