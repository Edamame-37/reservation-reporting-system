{{-- 
  NAMA FILE      : home.blade.php
  FUNGSIONALITAS : Halaman Beranda (Landing Page) Publik CAVA - Bento UI Modern
  DESKRIPSI      : Menampilkan Bento Hero Ecosystem 12-kolom, live snapshot operasional kampus, showcase fasilitas unggulan bento cards dengan hover pop-up, serta seksi regulasi & pusat layanan terpadu bebas kesan generik.
  CARA KERJA     : Menggunakan master layout <x-public-layout active="home">, menyajikan navigasi cepat dan tautan terpadu menuju katalog fasilitas dan matriks jadwal.
--}}

<x-public-layout title="Beranda Publik Fasilitas Kampus" active="home">
    <div class="space-y-10 sm:space-y-12">

        {{-- ========================================================================= --}}
        {{-- BENTO GRID 1: HERO ECOSYSTEM (12-KOLOM DENGAN HIERARKI VISUAL KUAT)       --}}
        {{-- ========================================================================= --}}
        <section class="grid grid-cols-1 lg:grid-cols-12 gap-5">
            
            {{-- Bento Card 1: Hero Utama dengan Form Pencarian Terpadu (8 Kolom) --}}
            <div class="lg:col-span-8 bg-slate-900 text-white rounded-3xl p-6 sm:p-10 border border-slate-800 shadow-md flex flex-col justify-between relative overflow-hidden">
                <div class="relative z-10">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-blue-200 text-xs font-semibold mb-4 backdrop-blur-xs">
                        <span class="w-2 h-2 rounded-full bg-blue-400"></span>
                        <span>CAVA • Campus Venue Access & Integrated Facilities</span>
                    </div>

                    <h1 class="text-2xl sm:text-4xl font-bold tracking-tight text-white leading-tight mb-3">
                        Akses Transparan & Cek Ketersediaan Fasilitas Akademik Kampus
                    </h1>

                    <p class="text-xs sm:text-sm text-slate-300 font-normal leading-relaxed max-w-2xl mb-6">
                        Pantau ketersediaan auditorium, laboratorium komputer, dan smart classroom secara transparan per slot 30 menit (07:00 - 20:00 WIB) sebelum mengajukan permohonan kegiatan kampus.
                    </p>

                    <!-- 
                      ROUTE: Form pencarian GET ke /public/catalog
                      FUNGSI: Membawa kata kunci pencarian pengunjung langsung menuju katalog fasilitas
                    -->
                    <form action="{{ url('/public/catalog') }}" method="GET" class="bg-white p-2 rounded-2xl shadow-xl flex flex-col sm:flex-row items-center gap-2 text-slate-800">
                        <div class="relative flex-1 w-full">
                            <span class="material-symbols-outlined absolute left-3.5 top-2.5 text-slate-400 text-[20px]">search</span>
                            <input type="text" 
                                   name="search" 
                                   placeholder="Cari ruang: Auditorium, Lab Komputer, Smart Classroom..." 
                                   class="w-full pl-11 pr-4 py-2.5 text-xs sm:text-sm rounded-xl focus:outline-none focus:ring-2 focus:ring-slate-900 bg-transparent text-slate-800 placeholder:text-slate-400 font-medium">
                        </div>
                        <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-slate-900 text-white text-xs sm:text-sm font-semibold hover:bg-slate-800 transition shadow-xs flex items-center justify-center gap-1.5 shrink-0">
                            <span>Telusuri</span>
                            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                        </button>
                    </form>

                    {{-- Chip Rekomendasi Ruang Populer --}}
                    <div class="flex flex-wrap items-center gap-2 mt-4 text-xs text-slate-300">
                        <span class="text-slate-400">Paling Sering Dicari:</span>
                        <a href="{{ url('/public/catalog?search=Auditorium') }}" class="px-2.5 py-1 rounded-lg bg-white/10 hover:bg-white/20 transition text-[11px] font-medium text-slate-200">
                            Auditorium Habibie
                        </a>
                        <a href="{{ url('/public/catalog?search=Lab') }}" class="px-2.5 py-1 rounded-lg bg-white/10 hover:bg-white/20 transition text-[11px] font-medium text-slate-200">
                            Lab Cloud C201
                        </a>
                        <a href="{{ url('/public/catalog?search=Smart') }}" class="px-2.5 py-1 rounded-lg bg-white/10 hover:bg-white/20 transition text-[11px] font-medium text-slate-200">
                            Smart Classroom B302
                        </a>
                    </div>
                </div>

                <div class="mt-8 pt-5 border-t border-white/10 relative z-10 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-400">
                    <span class="flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px] text-emerald-400">verified</span>
                        <span>Terintegrasi SIM-SARPRAS Universitas</span>
                    </span>
                    <a href="{{ url('/public/catalog') }}" class="text-white hover:text-blue-300 font-semibold flex items-center gap-1">
                        <span>Buka Katalog Lengkap</span>
                        <span class="material-symbols-outlined text-[14px]">east</span>
                    </a>
                </div>
            </div>

            {{-- Kolom Kanan: 2 Kartu Bento Modular (4 Kolom) --}}
            <div class="lg:col-span-4 flex flex-col gap-5">
                
                {{-- Bento Card 2: Live Snapshot Kampus --}}
                <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs flex-1 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Snapshot Operasional</span>
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 text-[11px] font-semibold">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span>Live</span>
                            </span>
                        </div>
                        <h3 class="text-base font-bold text-slate-900 mb-4">Statistik Ruang Kampus</h3>

                        <div class="grid grid-cols-2 gap-3">
                            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                                <span class="text-2xl font-bold font-mono text-slate-900 block">24+</span>
                                <span class="text-[11px] text-slate-500">Ruang Terdaftar</span>
                            </div>
                            <div class="p-3.5 rounded-2xl bg-slate-50 border border-slate-100">
                                <span class="text-2xl font-bold font-mono text-slate-900 block">30m</span>
                                <span class="text-[11px] text-slate-500">Interval Sesi</span>
                            </div>
                        </div>

                        <div class="mt-3.5 p-3 rounded-2xl bg-blue-50/70 border border-blue-100 text-xs text-blue-950 flex items-center gap-2">
                            <span class="material-symbols-outlined text-[18px] text-blue-900">schedule</span>
                            <span>Operasional: <strong>07:00 - 20:00 WIB</strong></span>
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                        <span class="flex items-center gap-1">
                            <span class="material-symbols-outlined text-[15px] text-blue-900">shield</span>
                            <span>Mode Privasi UR-01</span>
                        </span>
                        <span class="font-semibold text-slate-700">Aktif</span>
                    </div>
                </div>

                {{-- Bento Card 3: Pintasan Cepat Matriks Ketersediaan Ruang --}}
                <div class="bg-gradient-to-br from-blue-950 via-slate-900 to-slate-900 text-white rounded-3xl p-6 shadow-xs flex-1 flex flex-col justify-between">
                    <div>
                        <div class="w-10 h-10 rounded-2xl bg-white/10 text-white flex items-center justify-center mb-3">
                            <span class="material-symbols-outlined text-[22px]">calendar_month</span>
                        </div>
                        <h3 class="text-base font-bold text-white">Matriks Jadwal 30 Menit</h3>
                        <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                            Cek ketersediaan seluruh ruang kuliah, laboratorium, dan aula tanpa perlu login.
                        </p>
                    </div>

                    <div class="mt-4 pt-3 border-t border-white/10">
                        <a href="{{ url('/public/availability') }}" class="w-full px-4 py-2.5 rounded-xl bg-white text-slate-900 text-xs font-bold hover:bg-slate-100 transition shadow-xs flex items-center justify-center gap-1.5">
                            <span>Buka Matriks Jadwal</span>
                            <span class="material-symbols-outlined text-[15px]">arrow_forward</span>
                        </a>
                    </div>
                </div>
            </div>
        </section>

        {{-- ========================================================================= --}}
        {{-- BENTO GRID 2: SHOWCASE FASILITAS KAMPUS UNGGULAN (ASIMETRIS & TAKTIL)     --}}
        {{-- ========================================================================= --}}
        <section class="space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-200">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 tracking-tight">Fasilitas Kampus Unggulan</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Pilihan ruang pertemuan berkapasitas besar dan laboratorium berteknologi tinggi.</p>
                </div>
                <a href="{{ url('/public/catalog') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-blue-950 hover:text-blue-700 transition">
                    <span>Lihat Seluruh Fasilitas (24 Ruang)</span>
                    <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                
                {{-- Fasilitas 1: Auditorium B.J. Habibie --}}
                <div class="group bg-white rounded-3xl border border-slate-200/80 hover:border-slate-300 p-6 shadow-xs hover:shadow-md transition-all duration-300 transform hover:scale-[1.015] hover:-translate-y-0.5 flex flex-col justify-between cursor-pointer"
                     onclick="window.location.href='{{ url('/public/catalog?search=Auditorium') }}'">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="px-2.5 py-0.5 rounded-lg bg-slate-100 font-mono text-xs font-bold text-slate-700">AUD-H01</span>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span>Tersedia</span>
                            </span>
                        </div>

                        <h3 class="text-base font-bold text-slate-900 group-hover:text-blue-950 transition">Auditorium Utama B.J. Habibie</h3>
                        <p class="text-xs text-slate-500 flex items-center gap-1 mt-1 mb-4">
                            <span class="material-symbols-outlined text-[15px]">location_on</span>
                            <span>Gedung Rektorat (Lt. 1 & 2)</span>
                        </p>

                        <div class="space-y-2 text-xs text-slate-600 mb-5">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-[16px] text-slate-400">group</span>
                                <span class="font-bold text-slate-800">450 Kursi Peserta</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-[16px] text-slate-400">tune</span>
                                <span class="truncate">Sound System 5000W, Laser Projector, AC Central</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xs font-semibold text-blue-900 group-hover:underline">Buka Spesifikasi →</span>
                        <a href="{{ route('login') }}" class="px-3.5 py-1.5 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition" onclick="event.stopPropagation()">
                            Reservasi
                        </a>
                    </div>
                </div>

                {{-- Fasilitas 2: Lab Komputasi Cloud --}}
                <div class="group bg-white rounded-3xl border border-slate-200/80 hover:border-slate-300 p-6 shadow-xs hover:shadow-md transition-all duration-300 transform hover:scale-[1.015] hover:-translate-y-0.5 flex flex-col justify-between cursor-pointer"
                     onclick="window.location.href='{{ url('/public/catalog?search=Cloud') }}'">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="px-2.5 py-0.5 rounded-lg bg-slate-100 font-mono text-xs font-bold text-slate-700">LAB-C201</span>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span>Tersedia</span>
                            </span>
                        </div>

                        <h3 class="text-base font-bold text-slate-900 group-hover:text-blue-950 transition">Lab Komputasi Cloud & Jaringan</h3>
                        <p class="text-xs text-slate-500 flex items-center gap-1 mt-1 mb-4">
                            <span class="material-symbols-outlined text-[15px]">location_on</span>
                            <span>Gedung Lab Terpadu C (Lt. 2)</span>
                        </p>

                        <div class="space-y-2 text-xs text-slate-600 mb-5">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-[16px] text-slate-400">desktop_windows</span>
                                <span class="font-bold text-slate-800">45 PC High-End Core i7</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-[16px] text-slate-400">wifi</span>
                                <span class="truncate">Gigabit LAN, Smart Screen, AC Dual-Split</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xs font-semibold text-blue-900 group-hover:underline">Buka Spesifikasi →</span>
                        <a href="{{ route('login') }}" class="px-3.5 py-1.5 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition" onclick="event.stopPropagation()">
                            Reservasi
                        </a>
                    </div>
                </div>

                {{-- Fasilitas 3: Smart Classroom 302 --}}
                <div class="group bg-white rounded-3xl border border-slate-200/80 hover:border-slate-300 p-6 shadow-xs hover:shadow-md transition-all duration-300 transform hover:scale-[1.015] hover:-translate-y-0.5 flex flex-col justify-between cursor-pointer"
                     onclick="window.location.href='{{ url('/public/catalog?search=Smart') }}'">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-3">
                            <span class="px-2.5 py-0.5 rounded-lg bg-slate-100 font-mono text-xs font-bold text-slate-700">SMR-B302</span>
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-50 text-emerald-800 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span>Tersedia</span>
                            </span>
                        </div>

                        <h3 class="text-base font-bold text-slate-900 group-hover:text-blue-950 transition">Smart Classroom 302</h3>
                        <p class="text-xs text-slate-500 flex items-center gap-1 mt-1 mb-4">
                            <span class="material-symbols-outlined text-[15px]">location_on</span>
                            <span>Gedung Kuliah Bersama B (Lt. 3)</span>
                        </p>

                        <div class="space-y-2 text-xs text-slate-600 mb-5">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-[16px] text-slate-400">group</span>
                                <span class="font-bold text-slate-800">60 Mahasiswa</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-[16px] text-slate-400">co_present</span>
                                <span class="truncate">Interactive Whiteboard, Collab Desk, Mic Wireless</span>
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 border-t border-slate-100 flex items-center justify-between">
                        <span class="text-xs font-semibold text-blue-900 group-hover:underline">Buka Spesifikasi →</span>
                        <a href="{{ route('login') }}" class="px-3.5 py-1.5 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition" onclick="event.stopPropagation()">
                            Reservasi
                        </a>
                    </div>
                </div>

            </div>
        </section>

        {{-- ========================================================================= --}}
        {{-- BENTO GRID 3: REGULASI, TATA TERTIB & PUSAT LAYANAN BENTO                 --}}
        {{-- ========================================================================= --}}
        <section class="space-y-5">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-slate-200">
                <div>
                    <h2 class="text-xl font-bold text-slate-900 tracking-tight">Regulasi, Tata Tertib & Layanan Kampus</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Panduan resmi tata kelola sarana prasarana sebelum melakukan peminjaman fasilitas.</p>
                </div>
                <a href="{{ route('public.information') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-blue-950 hover:text-blue-700 transition">
                    <span>Seluruh Pusat Informasi</span>
                    <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                </a>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                
                {{-- Card 1: Kebijakan Privasi & Aset BMN --}}
                <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs hover:border-slate-300 transition-all flex flex-col justify-between">
                    <div>
                        <div class="w-11 h-11 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center mb-4">
                            <span class="material-symbols-outlined text-[24px]">shield</span>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-100/70 text-emerald-800">Privasi & Keamanan</span>
                        <h3 class="text-base font-bold text-slate-900 mt-2 mb-1.5">Kebijakan Fasilitas & Data</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Jaminan perlindungan data pribadi sivitas, kerahasiaan identitas pelapor kerusakan fasilitas, serta akuntabilitas aset BMN universitas.
                        </p>
                    </div>
                    <div class="mt-5 pt-3.5 border-t border-slate-100">
                        <a href="{{ route('public.information', ['section' => 'kebijakan']) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-900 hover:text-blue-900 transition">
                            <span>Baca Kebijakan Privasi</span>
                            <span class="material-symbols-outlined text-[15px]">arrow_forward</span>
                        </a>
                    </div>
                </div>

                {{-- Card 2: Tata Tertib Peminjaman & S&K --}}
                <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs hover:border-slate-300 transition-all flex flex-col justify-between">
                    <div>
                        <div class="w-11 h-11 rounded-2xl bg-blue-50 text-blue-900 flex items-center justify-center mb-4">
                            <span class="material-symbols-outlined text-[24px]">gavel</span>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-blue-100/70 text-blue-800">Tata Tertib Kampus</span>
                        <h3 class="text-base font-bold text-slate-900 mt-2 mb-1.5">Syarat & Ketentuan</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Ketentuan pengajuan minimal H-2, pembatalan mandiri H-1, pemanfaatan kelipatan slot 30 menit, serta tanggung jawab kebersihan.
                        </p>
                    </div>
                    <div class="mt-5 pt-3.5 border-t border-slate-100">
                        <a href="{{ route('public.information', ['section' => 'syarat-ketentuan']) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-900 hover:text-blue-900 transition">
                            <span>Pelajari Ketentuan</span>
                            <span class="material-symbols-outlined text-[15px]">arrow_forward</span>
                        </a>
                    </div>
                </div>

                {{-- Card 3: Pelaporan Kerusakan & Helpdesk --}}
                <div class="bg-white rounded-3xl border border-slate-200/80 p-6 shadow-xs hover:border-slate-300 transition-all flex flex-col justify-between">
                    <div>
                        <div class="w-11 h-11 rounded-2xl bg-purple-50 text-purple-900 flex items-center justify-center mb-4">
                            <span class="material-symbols-outlined text-[24px]">support_agent</span>
                        </div>
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-purple-100/70 text-purple-800">Layanan Sarpras</span>
                        <h3 class="text-base font-bold text-slate-900 mt-2 mb-1.5">Lapor Kendala & Helpdesk</h3>
                        <p class="text-xs text-slate-500 leading-relaxed">
                            Laporan kendala fasilitas AC, proyektor, atau kelistrikan direspon petugas dalam &lt; 24 jam. Tersedia kontak resmi biro sarpras.
                        </p>
                    </div>
                    <div class="mt-5 pt-3.5 border-t border-slate-100">
                        <a href="{{ route('public.information', ['section' => 'bantuan']) }}" class="inline-flex items-center gap-1 text-xs font-semibold text-slate-900 hover:text-blue-900 transition">
                            <span>Pusat Bantuan & Kontak</span>
                            <span class="material-symbols-outlined text-[15px]">arrow_forward</span>
                        </a>
                    </div>
                </div>

            </div>
        </section>

    </div>
</x-public-layout>
