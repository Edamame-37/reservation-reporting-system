{{-- 
  NAMA FILE      : home.blade.php
  FUNGSIONALITAS : Halaman Antarmuka Beranda (Landing Page) Publik CAVA
  DESKRIPSI      : Menampilkan masthead sambutan, ikhtisar fasilitas kampus, pencarian cepat, dan matriks slot ketersediaan 30 menit publik.
  CARA KERJA     : Menggunakan layout <x-public-layout active="home">, merender fitur pencarian fasilitas dan status ketersediaan terbuka.
--}}

<x-public-layout title="Beranda Publik" active="home">
    <!-- 
      ELEMEN       : Sub-Header / Portal Masthead Publik
      KEGUNAAN     : Menyambut pengunjung dan sivitas dengan identitas resmi kampus dan kepatuhan privasi UR-01.
      CARA KERJA   : Menampilkan banner statis dengan tautan cepat menuju Login SSO Universitas.
    -->
    <section class="bg-surface-container-lowest shadow-sm rounded-xl p-space-lg mb-space-xl">
        <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-space-lg">
            <div class="flex items-center gap-space-lg">
                <div class="w-14 h-14 rounded-xl bg-primary flex items-center justify-center text-on-primary shadow-sm">
                    <span class="material-symbols-outlined text-[32px]">domain</span>
                </div>
                <div class="h-10 w-[1px] bg-outline-variant hidden sm:block"></div>
                <div>
                    <div class="flex items-center gap-space-sm">
                        <span class="font-data-mono text-data-mono uppercase tracking-widest text-secondary font-bold text-[11px]">PORTAL PUBLIK TERBUKA</span>
                        <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span>
                        <span class="font-label-sm text-label-sm text-on-surface-variant font-data-mono">UR-01 PRIVACY COMPLIANT</span>
                    </div>
                    <h1 class="font-headline-lg text-headline-lg text-primary tracking-tight">Portal Informasi Fasilitas & Jadwal Kampus</h1>
                    <p class="font-body-sm text-body-sm text-on-surface-variant">Informasi ketersediaan ruang, laboratorium, dan auditorium secara real-time untuk sivitas akademika & publik.</p>
                </div>
            </div>
            <div class="flex items-center gap-space-md w-full sm:w-auto justify-end">
                <div class="hidden xl:flex flex-col text-right">
                    <span class="font-label-sm text-label-sm text-on-surface-variant">Autentikasi Terpusat</span>
                    <span class="font-data-mono text-data-mono text-primary font-semibold text-[11px]">SSO UNIVERSITAS ACTIVE</span>
                </div>
                <a href="{{ route('login') }}" class="inline-flex items-center justify-center gap-space-xs px-space-xl py-space-sm rounded-lg bg-primary text-on-primary hover:bg-primary-container transition-colors shadow-sm font-label-lg text-label-lg whitespace-nowrap">
                    <span class="material-symbols-outlined text-[18px]">key</span>
                    <span>Masuk / Login SSO</span>
                </a>
            </div>
        </div>
    </section>

    <!-- 
      ELEMEN       : Parameter Pencarian & Filter Fasilitas (UR02)
      KEGUNAAN     : Memungkinkan pengunjung menyaring daftar fasilitas berdasarkan kata kunci, tipe, gedung, dan kapasitas.
      CARA KERJA   : Mengirim form GET ke /public/catalog untuk memfilter daftar ruang tanpa reload berat.
    -->
    <section class="bg-surface-container-lowest shadow-sm rounded-xl p-space-lg mb-space-xl">
        <div class="flex items-center justify-between pb-space-sm mb-space-md border-b border-outline-variant">
            <div class="flex items-center gap-space-xs">
                <span class="material-symbols-outlined text-[18px] text-primary">filter_alt</span>
                <span class="font-headline-sm text-headline-sm text-primary">Pencarian Cepat Fasilitas Kampus</span>
                <span class="font-data-mono text-data-mono text-on-surface-variant ml-space-sm text-[11px]">MODUL UR02</span>
            </div>
            <span class="font-label-sm text-label-sm text-on-surface-variant font-data-mono text-[11px]">TAMPILAN PUBLIK TERFILTER</span>
        </div>

        <!-- 
          ROUTE: Form filter pencarian via GET ke /public/catalog
          FUNGSI: Menyaring fasilitas kampus sesuai parameter yang ditentukan pengunjung
        -->
        <form action="{{ url('/public/catalog') }}" method="GET" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-space-md items-end">
            {{-- Kata Kunci --}}
            <div class="lg:col-span-4 flex flex-col gap-1">
                <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant" for="search-query">Kata Kunci Ruangan / Kode</label>
                <div class="relative flex items-center">
                    <span class="material-symbols-outlined absolute left-space-md text-on-surface-variant text-[18px]">search</span>
                    <input type="text" id="search-query" name="q" placeholder="Mis: Auditorium, Lab Komputer, Smart Classroom..." class="w-full h-10 pl-10 pr-space-md rounded-lg bg-surface-container-low text-on-surface font-body-sm text-body-sm border border-outline-variant/50 focus:border-primary focus:bg-surface-container-lowest focus:outline-none">
                </div>
            </div>

            {{-- Tipe Fasilitas --}}
            <div class="lg:col-span-2 flex flex-col gap-1">
                <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant" for="filter-type">Tipe Fasilitas</label>
                <select id="filter-type" name="type" class="w-full h-10 px-space-md rounded-lg bg-surface-container-low text-on-surface font-body-sm text-body-sm border border-outline-variant/50 focus:border-primary focus:bg-surface-container-lowest focus:outline-none">
                    <option value="all">Semua Tipe</option>
                    <option value="auditorium">Auditorium & Hall</option>
                    <option value="kelas">Ruang Kelas</option>
                    <option value="lab">Lab Komputer / Riset</option>
                    <option value="olahraga">Lapangan Olahraga</option>
                </select>
            </div>

            {{-- Lokasi / Gedung --}}
            <div class="lg:col-span-3 flex flex-col gap-1">
                <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant" for="filter-location">Lokasi / Gedung</label>
                <select id="filter-location" name="location" class="w-full h-10 px-space-md rounded-lg bg-surface-container-low text-on-surface font-body-sm text-body-sm border border-outline-variant/50 focus:border-primary focus:bg-surface-container-lowest focus:outline-none">
                    <option value="all">Semua Gedung Kampus</option>
                    <option value="rektorat">Gedung Rektorat Baru</option>
                    <option value="gedung-a">Gedung Kuliah Terpadu A</option>
                    <option value="gedung-b">Gedung Kuliah B</option>
                    <option value="lab-barat">Gedung Lab Barat</option>
                    <option value="pkm">Student Center (PKM)</option>
                </select>
            </div>

            {{-- Kapasitas --}}
            <div class="lg:col-span-2 flex flex-col gap-1">
                <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant" for="filter-capacity">Kapasitas</label>
                <select id="filter-capacity" name="capacity" class="w-full h-10 px-space-md rounded-lg bg-surface-container-low text-on-surface font-body-sm text-body-sm border border-outline-variant/50 focus:border-primary focus:bg-surface-container-lowest focus:outline-none">
                    <option value="all">Semua Kapasitas</option>
                    <option value="small">&lt; 30 Orang</option>
                    <option value="medium">30 - 60 Orang</option>
                    <option value="large">&gt; 60 Orang</option>
                </select>
            </div>

            {{-- Tombol Filter --}}
            <div class="lg:col-span-1">
                <button type="submit" class="w-full h-10 inline-flex items-center justify-center gap-space-xs rounded-lg bg-primary text-on-primary hover:bg-primary-container transition-colors shadow-sm font-label-lg text-label-lg">
                    <span class="material-symbols-outlined text-[18px]">tune</span>
                    <span class="hidden md:inline lg:hidden xl:inline">Cari</span>
                </button>
            </div>
        </form>
    </section>

    <!-- 
      ELEMEN       : Live Time-Slot Directory Header Notice
      KEGUNAAN     : Indikator sinkronisasi berkala data slot ketersediaan 30 menit.
      CARA KERJA   : Menampilkan label status privasi aktif dan pulsasi waktu.
    -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-space-sm bg-surface-container-high px-space-lg py-space-sm rounded-lg mb-space-md">
        <div class="flex items-center gap-space-sm">
            <span class="material-symbols-outlined text-secondary text-[20px]">calendar_today</span>
            <span class="font-label-lg text-label-lg text-on-surface font-semibold">Matriks Slot Waktu 30 Menitan Hari Ini</span>
            <span class="font-data-mono text-data-mono text-on-surface-variant text-[11px]">(07:00 - 20:00 WIB)</span>
        </div>
        <div class="flex items-center gap-space-md">
            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded bg-surface-container text-on-surface font-data-mono text-data-mono text-[11px]">
                <span class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span>
                SINKRON OTOMATIS: REAL-TIME
            </span>
            <span class="font-label-sm text-label-sm text-on-surface-variant hidden md:inline">Privasi Aktif: Data Pengaju Disembunyikan</span>
        </div>
    </div>

    <!-- 
      ELEMEN       : Daftar Kartu Fasilitas & Matriks Ketersediaan (UR01, SFR03, SFR04)
      KEGUNAAN     : Menampilkan ringkasan spesifikasi fasilitas kampus dan matriks ketersediaan per slot 30 menit.
      CARA KERJA   : Merender kartu fasilitas dengan data spesifikasi teks murni dan grid visual slot 30 menit.
    -->
    <div class="flex flex-col gap-space-lg mb-space-xl">
        {{-- Card 1: Auditorium Utama B.J. Habibie --}}
        <article class="bg-surface-container-lowest rounded-xl p-space-lg shadow-sm flex flex-col gap-space-md">
            <div class="flex flex-col xl:flex-row items-start xl:items-center justify-between gap-space-md pb-space-md border-b border-outline-variant">
                <div class="flex flex-col gap-space-xs">
                    <div class="flex flex-wrap items-center gap-space-xs">
                        <span class="px-2 py-0.5 rounded font-data-mono text-data-mono bg-primary-container text-on-primary text-[11px]">AUD-H01</span>
                        <span class="px-2 py-0.5 rounded font-label-sm text-label-sm bg-surface-container-high text-on-surface">Gedung Rektorat - Lt. 1 & 2</span>
                        <span class="px-2 py-0.5 rounded font-label-sm text-label-sm bg-surface-container-high text-on-surface">Auditorium & Hall Utama</span>
                        <span class="px-2 py-0.5 rounded font-label-sm text-label-sm bg-secondary-container text-on-secondary-container font-semibold">Buka Operasional 07:00 - 20:00 WIB</span>
                    </div>
                    <h2 class="font-headline-lg text-headline-lg text-primary">Auditorium Utama B.J. Habibie</h2>
                    <div class="flex items-center gap-space-sm text-on-surface-variant font-label-md text-label-md">
                        <span class="material-symbols-outlined text-[16px]">groups</span>
                        <span>450 Kursi Bertingkat • AC Central • Dual Screen Laser Projector • 8 Wireless Mic • Audio Mixer Yamaha</span>
                    </div>
                </div>
                <div class="flex items-center gap-space-md self-stretch xl:self-auto justify-between xl:justify-end shrink-0">
                    <div class="text-right">
                        <div class="font-label-sm text-label-sm text-on-surface-variant">Okupansi Slot Hari Ini</div>
                        <div class="font-data-mono text-headline-sm text-primary font-bold">14 / 27 TERSEDIA</div>
                    </div>
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-space-xs px-space-lg py-2.5 rounded-lg bg-primary text-on-primary hover:bg-primary-container transition-colors shadow-sm font-label-lg text-label-lg whitespace-nowrap">
                        <span class="material-symbols-outlined text-[16px]">lock</span>
                        <span>Login untuk Mengajukan</span>
                    </a>
                </div>
            </div>

            {{-- Matriks Slot 30 Menit --}}
            <x-cava.slot-matrix :selectable="false" venueName="Auditorium B.J. Habibie" />
        </article>

        {{-- Card 2: Smart Lab Multimedia & RPL --}}
        <article class="bg-surface-container-lowest rounded-xl p-space-lg shadow-sm flex flex-col gap-space-md">
            <div class="flex flex-col xl:flex-row items-start xl:items-center justify-between gap-space-md pb-space-md border-b border-outline-variant">
                <div class="flex flex-col gap-space-xs">
                    <div class="flex flex-wrap items-center gap-space-xs">
                        <span class="px-2 py-0.5 rounded font-data-mono text-data-mono bg-primary-container text-on-primary text-[11px]">LAB-C204</span>
                        <span class="px-2 py-0.5 rounded font-label-sm text-label-sm bg-surface-container-high text-on-surface">Gedung Lab Barat - Lt. 2</span>
                        <span class="px-2 py-0.5 rounded font-label-sm text-label-sm bg-surface-container-high text-on-surface">Laboratorium Komputer</span>
                        <span class="px-2 py-0.5 rounded font-label-sm text-label-sm bg-secondary-container text-on-secondary-container font-semibold">Buka Operasional 07:00 - 20:00 WIB</span>
                    </div>
                    <h2 class="font-headline-lg text-headline-lg text-primary">Smart Lab Multimedia & Rekayasa Perangkat Lunak</h2>
                    <div class="flex items-center gap-space-sm text-on-surface-variant font-label-md text-label-md">
                        <span class="material-symbols-outlined text-[16px]">computer</span>
                        <span>40 PC Core i7 RAM 32GB RTX 4060 • Gigabit LAN 1 Gbps • Smart Interactive Whiteboard • 2 AC Split</span>
                    </div>
                </div>
                <div class="flex items-center gap-space-md self-stretch xl:self-auto justify-between xl:justify-end shrink-0">
                    <div class="text-right">
                        <div class="font-label-sm text-label-sm text-on-surface-variant">Okupansi Slot Hari Ini</div>
                        <div class="font-data-mono text-headline-sm text-secondary font-bold">20 / 27 TERSEDIA</div>
                    </div>
                    <a href="{{ route('login') }}" class="inline-flex items-center gap-space-xs px-space-lg py-2.5 rounded-lg bg-primary text-on-primary hover:bg-primary-container transition-colors shadow-sm font-label-lg text-label-lg whitespace-nowrap">
                        <span class="material-symbols-outlined text-[16px]">lock</span>
                        <span>Login untuk Mengajukan</span>
                    </a>
                </div>
            </div>

            {{-- Matriks Slot 30 Menit --}}
            <x-cava.slot-matrix :selectable="false" venueName="Smart Lab Multimedia" />
        </article>
    </div>
</x-public-layout>
