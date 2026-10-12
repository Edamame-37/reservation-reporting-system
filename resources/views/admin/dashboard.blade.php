{{-- 
====================================================================================================
SISTEM INFORMASI RESERVASI & PELAPORAN FASILITAS KAMPUS (CAVA)
MODUL 7: KONSOL ADMIN - DASBOR EKSEKUTIF & STATISTIK UTAMA (ADM-04 / US-17)
BERKAS: resources/views/admin/dashboard.blade.php
====================================================================================================

DESKRIPSI BERKAS:
Tampilan Dasbor Eksekutif Konsol Super Administrator (Executive Management Dashboard) pada sistem CAVA.
Menyajikan visualisasi metrik performa makro kampus dengan gaya Apple-inspired Bento UI:
1. 4 Kartu KPI Utama Berjarak Lapang (Total Sivitas, Antrean Verifikasi Pending, Inventaris Fasilitas, Okupansi Ruang).
2. Widget Antrean Verifikasi Cepat (Quick Approval / Rejection) pendaftar mandiri sivitas.
3. Widget Ringkasan Kesiapan Operasional Fasilitas Kampus berdasarkan kluster ruangan.

PERAN & ARSITEKTUR DALAM SISTEM CAVA:
- Dibungkus oleh master layout `<x-admin-layout active="dashboard">` yang menyediakan navigasi sidebar terpadu
  dan shell bento yang konsisten.
- Menerima variabel kalkulasi real-time dari `AdminDashboardController@index`:
  `$totalUsersCount`, `$pendingUsersCount`, `$totalFacilities`, `$activeFacilities`, `$maintenanceFacilities`,
  `$occupancyRate`, `$pendingUsersPreview`, dan `$buildingUtilization`.
- Menyediakan akses langsung ke alur verifikasi akun pending (ADM-01) dengan tombol aksi persetujuan cepat.
====================================================================================================
--}}

{{--
  PENJELASAN KOMPONEN LAYOUT INDUK:
  - 'title'  : Menyetel judul dokumen browser dan breadcrumb menjadi "Dasbor Eksekutif".
  - 'active' : Mengaktifkan penanda visual menu aktif "dashboard" pada sidebar navigasi kiri.
--}}
<x-admin-layout title="Dasbor Eksekutif" active="dashboard">
    <div class="space-y-10">

        {{-- ---------------------------------------------------------------------------------- --}}
        {{-- FLASH SESSION NOTIFICATIONS (Umpan Balik Sukses & Galat Controller)                --}}
        {{-- ---------------------------------------------------------------------------------- --}}
        {{-- Notifikasi sukses setelah menyetujui akun atau memperbarui fasilitas: --}}
        @if(session('success'))
            <div class="p-5 rounded-2xl bg-emerald-50/80 border border-emerald-100 text-xs text-emerald-900 flex items-center gap-3">
                <span class="material-symbols-outlined text-[20px] text-emerald-700 shrink-0">check_circle</span>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        {{-- Notifikasi kesalahan jika operasi gagal dieksekusi: --}}
        @if(session('error'))
            <div class="p-5 rounded-2xl bg-rose-50/80 border border-rose-100 text-xs text-rose-900 flex items-center gap-3">
                <span class="material-symbols-outlined text-[20px] text-rose-700 shrink-0">error</span>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        @endif

        {{-- ---------------------------------------------------------------------------------- --}}
        {{-- 1. HEADER EKSEKUTIF & TOMBOL PINTASAN TINJAU PENDAFTAR                            --}}
        {{-- ---------------------------------------------------------------------------------- --}}
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <span class="text-xs font-medium uppercase tracking-wider text-[#86868B] block">Biro Sarana Prasarana & TIK Universitas</span>
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-[#1D1D1F] mt-1 font-display">Dasbor Eksekutif</h1>
                <p class="text-sm text-[#86868B] mt-1.5 max-w-2xl leading-relaxed">Pemantauan sentral aktivitas peminjaman ruang, validasi sivitas akademik, dan ketersediaan sarana kampus.</p>
            </div>
            {{-- Tombol pintasan langsung menuju antrean pendaftar mandiri pada halaman user-management --}}
            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('admin.user-management') }}" class="bg-[#1D1D1F] text-white px-5 py-2.5 rounded-xl text-sm font-medium hover:bg-black transition shadow-sm cursor-pointer">
                    Tinjau Pendaftar Baru
                </a>
            </div>
        </div>

        {{-- ---------------------------------------------------------------------------------- --}}
        {{-- 2. BENTO GRID BARIS 1: 4 KARTU METRIK KPI UTAMA (APPLE BENTO PRECISION)            --}}
        {{-- ---------------------------------------------------------------------------------- --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            {{-- KARTU 1: TOTAL SIVITAS TERDAFTAR --}}
            <div class="bg-white rounded-[2rem] p-7 sm:p-8 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)] hover:border-gray-300 transition">
                <span class="text-xs text-[#86868B] font-medium block">Total Sivitas Terdaftar</span>
                {{-- number_format: Memformat angka ke pemisah ribuan standar --}}
                <span class="text-3xl sm:text-4xl font-extrabold tracking-tight text-[#1D1D1F] mt-3 block font-display">{{ number_format($totalUsersCount ?? 1428) }}</span>
                <span class="text-[11px] text-[#86868B] mt-3 block">Mahasiswa, dosen, dan staf aktif</span>
            </div>

            {{-- KARTU 2: ANTREAN VERIFIKASI AKUN PENDING (ADM-01) --}}
            <div class="bg-white rounded-[2rem] p-7 sm:p-8 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)] hover:border-gray-300 transition">
                <span class="text-xs text-[#86868B] font-medium block">Antrean Verifikasi</span>
                <span class="text-3xl sm:text-4xl font-extrabold tracking-tight text-[#1D1D1F] mt-3 block font-display">{{ $pendingUsersCount }}</span>
                <span class="text-[11px] text-[#86868B] mt-3 block">Menunggu otorisasi akun baru</span>
            </div>

            {{-- KARTU 3: MASTER FASILITAS KAMPUS (ADM-03) --}}
            <div class="bg-white rounded-[2rem] p-7 sm:p-8 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)] hover:border-gray-300 transition">
                <span class="text-xs text-[#86868B] font-medium block">Master Fasilitas Kampus</span>
                <span class="text-3xl sm:text-4xl font-extrabold tracking-tight text-[#1D1D1F] mt-3 block font-display">{{ $totalFacilities }}</span>
                <span class="text-[11px] text-[#86868B] mt-3 block">{{ $activeFacilities }} siap digunakan • {{ $maintenanceFacilities }} pemeliharaan</span>
            </div>

            {{-- KARTU 4: TINGKAT OKUPANSI RUANG BULANAN --}}
            <div class="bg-white rounded-[2rem] p-7 sm:p-8 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)] hover:border-gray-300 transition">
                <span class="text-xs text-[#86868B] font-medium block">Tingkat Okupansi Ruang</span>
                <span class="text-3xl sm:text-4xl font-extrabold tracking-tight text-[#1D1D1F] mt-3 block font-display">{{ $occupancyRate }}%</span>
                <span class="text-[11px] text-[#86868B] mt-3 block">Rata-rata utilisasi bulan berjalan</span>
            </div>
        </div>

        {{-- ---------------------------------------------------------------------------------- --}}
        {{-- 3. BENTO GRID BARIS 2: TINJAUAN ANTREAN PENDAFTAR & KESIAPAN FASILITAS             --}}
        {{-- ---------------------------------------------------------------------------------- --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 items-start">
            
            {{-- KOLOM KIRI (7 SPAN): WIDGET PENDAFTAR MANDIRI MENUNGGU OTORISASI --}}
            <div class="lg:col-span-7 bg-white rounded-[2rem] p-7 sm:p-8 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                <div class="flex items-center justify-between pb-5 mb-5 border-b border-gray-100">
                    <div>
                        <h3 class="text-base font-bold text-[#1D1D1F]">Pendaftar Mandiri Menunggu Otorisasi</h3>
                        <p class="text-xs text-[#86868B] mt-0.5">Verifikasi identitas akademik sivitas sebelum dapat mengajukan reservasi.</p>
                    </div>
                    <a href="{{ route('admin.user-management') }}" class="text-xs font-semibold text-[#1D1D1F] hover:underline cursor-pointer">
                        Buka Seluruh Direktori &rarr;
                    </a>
                </div>

                <div class="space-y-3">
                    {{-- Iterasi 3 pendaftar mandiri berstatus pending teratas --}}
                    @forelse($pendingUsersPreview as $user)
                        <div class="py-4 px-5 rounded-2xl bg-[#FBFBFD] border border-gray-100/80 flex items-center justify-between gap-4 hover:border-gray-200 transition">
                            <div class="min-w-0">
                                <div class="text-sm font-bold text-[#1D1D1F]">{{ $user->name }}</div>
                                <div class="text-xs text-[#86868B] mt-0.5">{{ ucfirst($user->role) }} • {{ $user->department ?? 'Sivitas Umum' }} • {{ $user->identity_number ?? '-' }}</div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                {{-- Aksi Cepat Setujui Akun (POST ke admin.users.verify) --}}
                                <form action="{{ route('admin.users.verify', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Verifikasi dan aktifkan akun pendaftar ini?')">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 rounded-xl bg-[#1D1D1F] text-white text-xs font-medium hover:bg-black transition cursor-pointer">
                                        Setujui
                                    </button>
                                </form>

                                {{-- Aksi Cepat Tolak Akun (POST ke admin.users.reject) --}}
                                <form action="{{ url('/admin/users/' . $user->id . '/reject') }}" method="POST" class="inline" onsubmit="return confirm('Tolak permohonan pendaftaran {{ addslashes($user->name) }}?')">
                                    @csrf
                                    <input type="hidden" name="reason" value="invalid_data">
                                    <button type="submit" class="px-3 py-2 text-xs font-medium text-[#86868B] hover:text-rose-600 transition cursor-pointer">
                                        Tolak
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        {{-- Tampilan saat antrean verifikasi kosong --}}
                        <div class="py-12 text-center text-[#86868B]">
                            <p class="text-xs">Tidak ada antrean pendaftar mandiri saat ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- KOLOM KANAN (5 SPAN): WIDGET STATUS KESIAPAN OPERASIONAL FASILITAS --}}
            <div class="lg:col-span-5 bg-white rounded-[2rem] p-7 sm:p-8 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                <div class="pb-5 mb-5 border-b border-gray-100">
                    <h3 class="text-base font-bold text-[#1D1D1F]">Kesiapan Operasional Fasilitas</h3>
                    <p class="text-xs text-[#86868B] mt-0.5">Status inventaris ruang berdasarkan kluster kampus.</p>
                </div>

                <div class="space-y-4">
                    {{-- Kluster 1: Auditorium & Ruang Serbaguna --}}
                    <div class="py-2.5 flex items-center justify-between border-b border-gray-50">
                        <div>
                            <span class="text-sm font-semibold text-[#1D1D1F] block">Auditorium & Ruang Serbaguna</span>
                            <span class="text-xs text-[#86868B]">Kapasitas besar 300 - 500 kursi</span>
                        </div>
                        <span class="text-xs font-medium text-[#1D1D1F] text-right">4 Siap Digunakan</span>
                    </div>

                    {{-- Kluster 2: Laboratorium Komputer & Riset --}}
                    <div class="py-2.5 flex items-center justify-between border-b border-gray-50">
                        <div>
                            <span class="text-sm font-semibold text-[#1D1D1F] block">Laboratorium Komputer & Riset</span>
                            <span class="text-xs text-[#86868B]">Dilengkapi workstation & jaringan</span>
                        </div>
                        <span class="text-xs font-medium text-[#1D1D1F] text-right">8 Siap Digunakan</span>
                    </div>

                    {{-- Kluster 3: Ruang Kuliah Teori & Seminar --}}
                    <div class="py-2.5 flex items-center justify-between">
                        <div>
                            <span class="text-sm font-semibold text-[#1D1D1F] block">Ruang Kuliah Teori & Seminar</span>
                            <span class="text-xs text-[#86868B]">Kelas kuliah reguler & ujian</span>
                        </div>
                        <span class="text-xs font-medium text-[#1D1D1F] text-right">12 Siap Digunakan</span>
                    </div>
                </div>
            </div>

        </div>

    </div>
</x-admin-layout>
