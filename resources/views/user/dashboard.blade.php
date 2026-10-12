{{-- 
  ====================================================================================================
  NAMA FILE      : dashboard.blade.php
  LOKASI         : resources/views/user/dashboard.blade.php
  FUNGSIONALITAS : Antarmuka Dasbor Utama Pengguna Sivitas Akademika (Mahasiswa & Dosen)
  MODUL CAVA     : 9. Portal Pengguna (Sivitas): Dasbor Pribadi & Ringkasan Tiket Aktif
  ====================================================================================================

  DESKRIPSI ARSITEKTUR & KONSEP DESAIN SISTEM:
  1. Integrasi Master Layout:
     - Menggunakan komponen Blade `<x-app-layout>` dengan properti `title="Dasbor Pengguna"` dan 
       `active="dashboard"` untuk menyalakan sorotan aktif pada navigasi samping.
  
  2. Filosofi Desain Apple-esque Bento UI:
     - Menggunakan tata letak modular dengan sudut lengkung membulat halus (`rounded-[2rem]`), 
       pembatas garis netral tipis (`border-gray-100`), dan bayangan lembut (`shadow-[0_8px_30px_rgb(0,0,0,0.03)]`).
     - Tipografi modern bersih bernuansa gelap lembut khas antarmuka Apple (`text-[#1D1D1F]`, subtitle `text-[#86868B]`).
  
  3. Status Opsi B: Pure Typographic Indicators:
     - Sesuai dengan spesifikasi desain CAVA, badge status tidak menggunakan bulatan warna (dot indicator) 
       maupun micro-icon hiasan, melainkan murni mengandalkan tipografi berbobot medium/semibold dengan
       warna teks kontekstual (Emerald untuk disetujui, Amber untuk menunggu/baru, Sky untuk proses, Rose untuk ditolak).
  
  4. Struktur 4 Bagian Utama Antarmuka:
     - Bagian 1: Spotlight Hero Card (Agenda terdekat yang disetujui atau banner ramah jadwal kosong).
     - Bagian 2: 4 Bento Shortcut Cards (Ajukan Reservasi, Riwayat Reservasi, Lapor Kerusakan, Status Perbaikan).
     - Bagian 3: Riwayat Permohonan Terkini (Maksimal 3 item terbaru dengan status dan waktu pemakaian).
     - Bagian 4: Status Penanganan Kerusakan Fasilitas (Maksimal 2 tiket keluhan yang sedang aktif ditangani sarpras).
  ====================================================================================================
--}}

{{-- Memanggil komponen master layout khusus portal pengguna dengan prop active="dashboard" --}}
<x-app-layout title="Dasbor Pengguna" active="dashboard">
    
    {{-- Container utama dasbor dengan jarak vertikal antar seksi (space-y-10) dan warna font #1D1D1F --}}
    <div class="space-y-10 font-sans text-[#1D1D1F]">
        
        {{-- ========================================================================================== --}}
        {{-- SEKSI 1: SPOTLIGHT HERO CARD (AGENDA TERDEKAT ATAU EMPTY CALL-TO-ACTION)                   --}}
        {{-- ========================================================================================== --}}
        <section class="bg-white rounded-[2rem] p-8 md:p-10 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.03)] flex flex-col md:flex-row md:items-center justify-between gap-6">
            
            {{-- Pemeriksaan Kondisional: Jika pengguna memiliki reservasi yang disetujui di masa depan --}}
            @if($upcomingReservation)
                <div class="space-y-3 flex-1">
                    {{-- Label Status Opsi B: Pure Typographic tanpa bullet/dot --}}
                    <div>
                        <span class="text-xs font-semibold tracking-wide text-emerald-800">
                            Jadwal Terdekat • Disetujui
                        </span>
                    </div>

                    {{-- Judul Keperluan Peminjaman Ruangan --}}
                    <h2 class="text-2xl font-bold tracking-tight text-[#1D1D1F]">
                        {{ $upcomingReservation->purpose }}
                    </h2>

                    {{-- Informasi Lokasi Fasilitas, Tanggal dalam Bahasa Indonesia, dan Rentang Jam Pemakaian --}}
                    <p class="text-sm text-[#86868B]">
                        {{ $upcomingReservation->facility->name ?? 'Fasilitas' }}, {{ $upcomingReservation->facility->building ?? 'Gedung' }} • {{ \Carbon\Carbon::parse($upcomingReservation->reservation_date)->isoFormat('dddd, D MMMM Y') }} ({{ substr($upcomingReservation->start_time, 0, 5) }} - {{ substr($upcomingReservation->end_time, 0, 5) }} WIB)
                    </p>
                </div>

                {{-- Tombol Aksi Menuju Halaman Riwayat Reservasi untuk Membaca Rincian Tiket --}}
                <div class="shrink-0 self-start md:self-center">
                    <a href="{{ url('/user/reservation-history') }}" class="inline-flex items-center gap-2 bg-[#1D1D1F] text-white px-6 py-3 rounded-xl font-medium hover:bg-gray-800 transition text-sm">
                        <span>Buka Detail Tiket</span>
                        <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                    </a>
                </div>
            @else
                {{-- Tampilan Alternatif: Jika belum ada reservasi aktif yang disetujui dalam waktu dekat --}}
                <div class="space-y-2 flex-1">
                    <div>
                        <span class="text-xs font-semibold tracking-wide text-slate-500">
                            Jadwal Terdekat
                        </span>
                    </div>

                    <h2 class="text-2xl font-bold tracking-tight text-[#1D1D1F]">
                        Belum Ada Jadwal Terdekat
                    </h2>

                    <p class="text-sm text-[#86868B]">
                        Anda belum memiliki permohonan reservasi ruangan yang disetujui untuk waktu dekat.
                    </p>
                </div>

                {{-- Tombol Aksi Cepat Mengisi Formulir Pengajuan Reservasi Baru --}}
                <div class="shrink-0 self-start md:self-center">
                    <a href="{{ url('/user/reservation-form') }}" class="inline-flex items-center gap-2 bg-[#1D1D1F] text-white px-6 py-3 rounded-xl font-medium hover:bg-gray-800 transition text-sm">
                        <span>Ajukan Reservasi Baru</span>
                        <span class="material-symbols-outlined text-[16px]">add</span>
                    </a>
                </div>
            @endif
        </section>

        {{-- ========================================================================================== --}}
        {{-- SEKSI 2: 4 PINTASAN CEPAT BENTO (BENTO SHORTCUT CARDS)                                     --}}
        {{-- Grid responsif 1 kolom di mobile, 2 kolom di tablet, dan 4 kolom di desktop (lg)          --}}
        {{-- ========================================================================================== --}}
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            
            {{-- Kartu Pintasan 1: Ajukan Reservasi Ruangan Mandiri --}}
            <a href="{{ url('/user/reservation-form') }}" class="group relative overflow-hidden bg-[#F9F9FB] rounded-[2rem] p-8 hover:bg-white hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all duration-500 flex flex-col justify-between min-h-[160px] cursor-pointer">
                <div class="relative z-10">
                    <h3 class="text-xl font-bold text-[#1D1D1F] mb-1 group-hover:text-black transition-colors">Ajukan Reservasi</h3>
                    <p class="text-sm text-[#86868B]">Peminjaman ruang kuliah atau lab.</p>
                </div>
                {{-- Tautan Aksi Bawah dengan Animasi Micro-interaction Panah Bergerak ke Kanan saat Hover --}}
                <div class="relative z-10 inline-flex items-center gap-1 text-sm font-medium text-[#1D1D1F] mt-4">
                    <span>Buka Formulir</span>
                    <span class="material-symbols-outlined text-[16px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
                </div>
                {{-- Ikon Watermark Latar Belakang (meeting_room) yang Membesar/Mengecil Halus saat Hover --}}
                <div class="absolute right-0 inset-y-0 flex items-center pr-2 transform scale-[1.6] translate-x-4 group-hover:scale-100 group-hover:-translate-x-2 transition-all duration-700 ease-out pointer-events-none select-none">
                    <span class="material-symbols-outlined text-[90px] text-gray-200/50 group-hover:text-gray-300/40 transition-colors duration-700 font-light">meeting_room</span>
                </div>
            </a>

            {{-- Kartu Pintasan 2: Riwayat Reservasi & Pembatalan Mandiri --}}
            <a href="{{ url('/user/reservation-history') }}" class="group relative overflow-hidden bg-[#F9F9FB] rounded-[2rem] p-8 hover:bg-white hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all duration-500 flex flex-col justify-between min-h-[160px] cursor-pointer">
                <div class="relative z-10">
                    <h3 class="text-xl font-bold text-[#1D1D1F] mb-1 group-hover:text-black transition-colors">Riwayat Reservasi</h3>
                    <p class="text-sm text-[#86868B]">Pantau tiket dan pembatalan H-1.</p>
                </div>
                <div class="relative z-10 inline-flex items-center gap-1 text-sm font-medium text-[#1D1D1F] mt-4">
                    <span>Lihat Semua Tiket</span>
                    <span class="material-symbols-outlined text-[16px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
                </div>
                {{-- Ikon Watermark Latar Belakang (calendar_month) --}}
                <div class="absolute right-0 inset-y-0 flex items-center pr-2 transform scale-[1.6] translate-x-4 group-hover:scale-100 group-hover:-translate-x-2 transition-all duration-700 ease-out pointer-events-none select-none">
                    <span class="material-symbols-outlined text-[90px] text-gray-200/50 group-hover:text-gray-300/40 transition-colors duration-700 font-light">calendar_month</span>
                </div>
            </a>

            {{-- Kartu Pintasan 3: Formulir Pelaporan Kerusakan Fasilitas --}}
            <a href="{{ url('/user/report-form') }}" class="group relative overflow-hidden bg-[#F9F9FB] rounded-[2rem] p-8 hover:bg-white hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all duration-500 flex flex-col justify-between min-h-[160px] cursor-pointer">
                <div class="relative z-10">
                    <h3 class="text-xl font-bold text-[#1D1D1F] mb-1 group-hover:text-black transition-colors">Lapor Kerusakan</h3>
                    <p class="text-sm text-[#86868B]">Pusat bantuan teknis fasilitas.</p>
                </div>
                <div class="relative z-10 inline-flex items-center gap-1 text-sm font-medium text-[#1D1D1F] mt-4">
                    <span>Kirim Laporan</span>
                    <span class="material-symbols-outlined text-[16px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
                </div>
                {{-- Ikon Watermark Latar Belakang (support_agent) --}}
                <div class="absolute right-0 inset-y-0 flex items-center pr-2 transform scale-[1.6] translate-x-4 group-hover:scale-100 group-hover:-translate-x-2 transition-all duration-700 ease-out pointer-events-none select-none">
                    <span class="material-symbols-outlined text-[90px] text-gray-200/50 group-hover:text-gray-300/40 transition-colors duration-700 font-light">support_agent</span>
                </div>
            </a>

            {{-- Kartu Pintasan 4: Status Penanganan & Progres Perbaikan --}}
            <a href="{{ url('/user/report-history') }}" class="group relative overflow-hidden bg-[#F9F9FB] rounded-[2rem] p-8 hover:bg-white hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all duration-500 flex flex-col justify-between min-h-[160px] cursor-pointer">
                <div class="relative z-10">
                    <h3 class="text-xl font-bold text-[#1D1D1F] mb-1 group-hover:text-black transition-colors">Status Perbaikan</h3>
                    <p class="text-sm text-[#86868B]">Tindak lanjut oleh teknisi.</p>
                </div>
                <div class="relative z-10 inline-flex items-center gap-1 text-sm font-medium text-[#1D1D1F] mt-4">
                    <span>Pantau Progres</span>
                    <span class="material-symbols-outlined text-[16px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
                </div>
                {{-- Ikon Watermark Latar Belakang (verified) --}}
                <div class="absolute right-0 inset-y-0 flex items-center pr-2 transform scale-[1.6] translate-x-4 group-hover:scale-100 group-hover:-translate-x-2 transition-all duration-700 ease-out pointer-events-none select-none">
                    <span class="material-symbols-outlined text-[90px] text-gray-200/50 group-hover:text-gray-300/40 transition-colors duration-700 font-light">verified</span>
                </div>
            </a>

        </section>

        {{-- ========================================================================================== --}}
        {{-- SEKSI 3: DUA KOLOM REKAPITULASI (RIWAYAT RESERVASI TERKINI & LAPORAN KERUSAKAN AKTIF)       --}}
        {{-- Layout 2 kolom lega (gap-8 lg:gap-10) untuk ruang visual yang proporsional dan tidak padat --}}
        {{-- ========================================================================================== --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-10">
            
            {{-- -------------------------------------------------------------------------------------- --}}
            {{-- KOLOM KIRI: RIWAYAT PERMOHONAN RESERVASI TERKINI (MAKS 3 DATA TERBARU)                 --}}
            {{-- -------------------------------------------------------------------------------------- --}}
            <section class="bg-white rounded-[2rem] p-8 sm:p-10 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.03)] space-y-6">
                
                {{-- Header Seksi dengan Ringkasan Total Data dan Tombol Tautan Selengkapnya --}}
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <div>
                        <h3 class="text-xl font-bold text-[#1D1D1F]">Riwayat Permohonan Terkini</h3>
                        <p class="text-xs text-[#86868B] mt-0.5">Menampilkan {{ $recentReservations->take(3)->count() }} dari total {{ $totalReservations }} pengajuan Anda</p>
                    </div>
                    <a href="{{ url('/user/reservation-history') }}" class="text-sm font-semibold text-[#1D1D1F] hover:text-blue-600 flex items-center gap-1 transition">
                        <span>Lihat Semua ({{ $totalReservations }})</span>
                        <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                    </a>
                </div>

                {{-- Daftar Kartu Item Riwayat Reservasi (Looping @forelse) --}}
                <div class="space-y-4">
                    @forelse($recentReservations->take(3) as $reservation)
                        <a href="{{ url('/user/reservation-history') }}" class="bg-[#F9F9FB] rounded-2xl p-5 sm:p-6 border border-transparent hover:border-gray-200/80 hover:bg-white hover:shadow-[0_8px_24px_rgba(0,0,0,0.05)] transition-all duration-300 cursor-pointer group space-y-3 block">
                            <div class="flex items-start justify-between gap-4">
                                {{-- Nama Fasilitas / Ruangan Kampus --}}
                                <h4 class="font-bold text-[#1D1D1F] text-base group-hover:text-blue-600 transition leading-snug">
                                    {{ $reservation->facility->name ?? 'Fasilitas Kampus' }}
                                </h4>
                                
                                {{-- Indikator Status Opsi B: Pure Typographic (Tanpa Dot, Tanpa Icon) --}}
                                <div class="shrink-0 pt-0.5">
                                    @if ($reservation->status === 'approved')
                                        <span class="text-xs font-semibold tracking-wide text-emerald-800">Disetujui</span>
                                    @elseif ($reservation->status === 'pending')
                                        <span class="text-xs font-semibold tracking-wide text-amber-800">Menunggu</span>
                                    @elseif ($reservation->status === 'rejected')
                                        <span class="text-xs font-semibold tracking-wide text-rose-700">Ditolak</span>
                                    @elseif ($reservation->status === 'cancelled')
                                        <span class="text-xs font-semibold tracking-wide text-slate-500">Dibatalkan</span>
                                    @else
                                        <span class="text-xs font-semibold tracking-wide text-slate-600 capitalize">{{ $reservation->status }}</span>
                                    @endif
                                </div>
                            </div>
                            
                            {{-- Baris Detail Informasi: Tanggal Pelaksanaan, Rentang Waktu, dan Keperluan --}}
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-[#86868B] pt-1 border-t border-gray-100/70">
                                <span>{{ \Carbon\Carbon::parse($reservation->reservation_date)->isoFormat('dddd, D MMMM Y') }}</span>
                                <span>•</span>
                                <span>{{ substr($reservation->start_time, 0, 5) }} - {{ substr($reservation->end_time, 0, 5) }} WIB</span>
                                <span>•</span>
                                <span class="text-[#1D1D1F] font-medium truncate max-w-[220px]">{{ $reservation->purpose }}</span>
                            </div>
                        </a>
                    @empty
                        {{-- Keadaan Kosong (Empty State) Jika Pengguna Belum Memiliki Pengajuan Reservasi --}}
                        <div class="py-10 text-center text-sm text-[#86868B] rounded-2xl border border-dashed border-gray-200 bg-[#F9F9FB]">
                            Belum ada riwayat permohonan reservasi ruangan.
                        </div>
                    @endforelse
                </div>
            </section>

            {{-- -------------------------------------------------------------------------------------- --}}
            {{-- KOLOM KANAN: STATUS LAPORAN KERUSAKAN AKTIF (MAKS 2 TIKET PENANGANAN TERBARU)          --}}
            {{-- -------------------------------------------------------------------------------------- --}}
            <section class="bg-white rounded-[2rem] p-8 sm:p-10 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.03)] flex flex-col justify-between space-y-6">
                <div class="space-y-6">
                    
                    {{-- Header Seksi Laporan Kerusakan --}}
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                        <div>
                            <h3 class="text-xl font-bold text-[#1D1D1F]">Status Laporan Kerusakan</h3>
                            <p class="text-xs text-[#86868B] mt-0.5">Penanganan fasilitas aktif oleh tim sarpras</p>
                        </div>
                        <a href="{{ url('/user/report-history') }}" class="text-sm font-semibold text-[#1D1D1F] hover:text-blue-600 flex items-center gap-1 transition">
                            <span>Lihat Semua ({{ $totalDamageReports }})</span>
                            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                        </a>
                    </div>

                    {{-- Daftar Item Pengaduan Kerusakan (Looping @forelse) --}}
                    <div class="space-y-4">
                        @forelse($activeDamageReports->take(2) as $report)
                            <a href="{{ url('/user/report-history') }}" class="bg-[#F9F9FB] rounded-2xl p-5 sm:p-6 border border-transparent hover:border-gray-200/80 hover:bg-white hover:shadow-[0_8px_24px_rgba(0,0,0,0.05)] transition-all duration-300 cursor-pointer group space-y-3 block">
                                <div class="flex items-start justify-between gap-4">
                                    {{-- Judul Permasalahan / Keluhan Kerusakan --}}
                                    <h4 class="font-bold text-[#1D1D1F] text-base group-hover:text-blue-600 transition leading-snug">
                                        {{ $report->title ?? 'Laporan Kerusakan' }}
                                    </h4>
                                    
                                    {{-- Indikator Status Opsi B: Pure Typographic --}}
                                    <div class="shrink-0 pt-0.5">
                                        @if ($report->status === 'baru')
                                            <span class="text-xs font-semibold tracking-wide text-amber-800">Laporan Baru</span>
                                        @elseif ($report->status === 'diproses')
                                            <span class="text-xs font-semibold tracking-wide text-sky-800">Sedang Ditangani</span>
                                        @elseif ($report->status === 'selesai')
                                            <span class="text-xs font-semibold tracking-wide text-emerald-800">Selesai Diperbaiki</span>
                                        @elseif ($report->status === 'ditolak')
                                            <span class="text-xs font-semibold tracking-wide text-rose-700">Ditolak</span>
                                        @else
                                            <span class="text-xs font-semibold tracking-wide text-slate-600 capitalize">{{ $report->status }}</span>
                                        @endif
                                    </div>
                                </div>
                                
                                {{-- Keterangan Fasilitas, Gedung, dan Waktu Pembaruan Terakhir (diffForHumans) --}}
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-[#86868B] pt-1 border-t border-gray-100/70">
                                    <span>{{ $report->facility->name ?? 'Fasilitas' }} ({{ $report->facility->building ?? 'Gedung' }})</span>
                                    <span>•</span>
                                    <span>Diperbarui {{ $report->updated_at->diffForHumans() }}</span>
                                </div>
                            </a>
                        @empty
                            {{-- Keadaan Kosong (Empty State) Jika Tidak Ada Laporan Kerusakan Aktif --}}
                            <div class="py-10 text-center text-sm text-[#86868B] rounded-2xl border border-dashed border-gray-200 bg-[#F9F9FB]">
                                Tidak ada laporan kerusakan fasilitas yang sedang aktif.
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Tombol Bawah Penuh: Laporkan Kerusakan Baru --}}
                <div class="pt-2">
                    <a href="{{ url('/user/report-form') }}" class="w-full py-3.5 rounded-xl bg-[#F9F9FB] hover:bg-gray-100 text-[#1D1D1F] text-sm font-semibold transition flex items-center justify-center gap-2 border border-gray-200/60">
                        <span>Laporkan Kerusakan Baru</span>
                        <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                    </a>
                </div>
            </section>

        </div>

    </div>
</x-app-layout>
