{{-- 
  NAMA FILE      : catalog.blade.php
  FUNGSIONALITAS : Halaman Katalog Fasilitas & Ruang Kampus (Visitor & Pengguna)
  DESKRIPSI      : Menampilkan inventaris fasilitas secara terperinci dengan filter tipe, gedung, kapasitas, dan spesifikasi peralatan.
  CARA KERJA     : Memanfaatkan layout <x-public-layout active="catalog">, menyajikan daftar kartu fasilitas berdensitas tinggi tanpa foto berat.
--}}

<x-public-layout title="Katalog Fasilitas Kampus" active="catalog">
    {{-- Header Modul --}}
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-space-md mb-space-lg">
        <div>
            <div class="flex items-center gap-space-xs">
                <span class="font-data-mono text-data-mono text-secondary font-bold text-[11px]">DIREKTORI FASILITAS & SARANA</span>
                <span>•</span>
                <span class="font-label-sm text-label-sm text-on-surface-variant font-data-mono">UR01 • UR02</span>
            </div>
            <h1 class="font-headline-lg text-headline-lg text-primary tracking-tight">Katalog Master Inventaris Ruang Kampus</h1>
            <p class="font-body-sm text-body-sm text-on-surface-variant">Daftar lengkap auditorium, laboratorium riset, smart classroom, dan sarana olahraga berstandar universitas.</p>
        </div>
        <div class="flex items-center gap-space-sm">
            <span class="px-space-md py-1 rounded bg-surface-container-high font-data-mono text-data-mono text-primary font-bold">TOTAL: 6 FASILITAS UTAMA</span>
        </div>
    </div>

    {{-- Filter Bar --}}
    <section class="bg-surface-container-lowest shadow-sm rounded-xl p-space-lg mb-space-xl">
        <!-- 
          ROUTE: Form pencarian GET ke /public/catalog
          FUNGSI: Mengurangi/menyaring daftar fasilitas berdasarkan kata kunci dan tipe
        -->
        <form action="{{ url('/public/catalog') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-space-md items-end">
            <div>
                <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant" for="cat-search">Cari Nama / Kode Fasilitas</label>
                <input type="text" id="cat-search" name="q" placeholder="Cari nama ruang..." class="w-full h-10 px-space-md rounded-lg bg-surface-container-low text-on-surface font-body-sm border border-outline-variant/50 focus:border-primary focus:bg-surface-container-lowest focus:outline-none">
            </div>
            <div>
                <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant" for="cat-type">Tipe Fasilitas</label>
                <select id="cat-type" name="type" class="w-full h-10 px-space-md rounded-lg bg-surface-container-low text-on-surface font-body-sm border border-outline-variant/50 focus:border-primary focus:bg-surface-container-lowest focus:outline-none">
                    <option value="all">Semua Tipe</option>
                    <option value="auditorium">Auditorium & Hall</option>
                    <option value="kelas">Ruang Kelas / Teater</option>
                    <option value="lab">Laboratorium Komputer</option>
                    <option value="olahraga">Lapangan Olahraga</option>
                </select>
            </div>
            <div>
                <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant" for="cat-location">Lokasi Gedung</label>
                <select id="cat-location" name="location" class="w-full h-10 px-space-md rounded-lg bg-surface-container-low text-on-surface font-body-sm border border-outline-variant/50 focus:border-primary focus:bg-surface-container-lowest focus:outline-none">
                    <option value="all">Semua Gedung</option>
                    <option value="rektorat">Gedung Rektorat</option>
                    <option value="gedung-a">Gedung Kuliah Terpadu A</option>
                    <option value="gedung-b">Gedung B</option>
                    <option value="lab-barat">Gedung Lab Barat</option>
                    <option value="pkm">Student Center PKM</option>
                </select>
            </div>
            <div>
                <button type="submit" class="w-full h-10 inline-flex items-center justify-center gap-space-xs rounded-lg bg-primary text-on-primary hover:bg-primary-container transition-colors shadow-sm font-label-lg text-label-lg">
                    <span class="material-symbols-outlined text-[18px]">search</span>
                    <span>Terapkan Filter</span>
                </button>
            </div>
        </form>
    </section>

    {{-- Grid Fasilitas --}}
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-space-lg mb-space-xl">
        {{-- Item 1 --}}
        <div class="bg-surface-container-lowest rounded-xl p-space-lg shadow-sm border border-outline-variant/60 flex flex-col justify-between gap-space-md">
            <div class="flex flex-col gap-space-xs">
                <div class="flex items-center justify-between">
                    <span class="px-2 py-0.5 rounded font-data-mono text-data-mono bg-primary-container text-on-primary text-[11px]">AUD-H01</span>
                    <span class="px-2 py-0.5 rounded font-label-sm text-label-sm bg-secondary-container text-on-secondary-container font-semibold">Tersedia</span>
                </div>
                <h3 class="font-headline-md text-headline-md text-primary font-bold mt-1">Auditorium B.J. Habibie</h3>
                <p class="font-body-sm text-body-sm text-on-surface-variant">Gedung Rektorat Baru, Lantai 1 & 2</p>
                <div class="mt-2 text-on-surface-variant font-label-sm text-label-sm">
                    <div class="flex items-center gap-1"><span class="material-symbols-outlined text-[15px]">groups</span> Kapasitas: 450 Orang</div>
                    <div class="flex items-center gap-1 mt-1"><span class="material-symbols-outlined text-[15px]">videocam</span> Dual Laser Projector, Sound Yamaha 5000W</div>
                </div>
            </div>
            <div class="pt-space-sm border-t border-outline-variant/40 flex items-center justify-between">
                <span class="font-data-mono text-[11px] text-secondary font-bold">14/27 Slot Kosong Hari Ini</span>
                <a href="{{ url('/public/availability') }}" class="px-space-md py-1.5 rounded bg-surface-container text-primary font-label-md text-label-md hover:bg-primary hover:text-on-primary transition-colors flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">calendar_today</span> Matriks
                </a>
            </div>
        </div>

        {{-- Item 2 --}}
        <div class="bg-surface-container-lowest rounded-xl p-space-lg shadow-sm border border-outline-variant/60 flex flex-col justify-between gap-space-md">
            <div class="flex flex-col gap-space-xs">
                <div class="flex items-center justify-between">
                    <span class="px-2 py-0.5 rounded font-data-mono text-data-mono bg-primary-container text-on-primary text-[11px]">LAB-C204</span>
                    <span class="px-2 py-0.5 rounded font-label-sm text-label-sm bg-secondary-container text-on-secondary-container font-semibold">Tersedia</span>
                </div>
                <h3 class="font-headline-md text-headline-md text-primary font-bold mt-1">Lab Jaringan & Cloud Komputasi</h3>
                <p class="font-body-sm text-body-sm text-on-surface-variant">Gedung Lab Barat, Lantai 2</p>
                <div class="mt-2 text-on-surface-variant font-label-sm text-label-sm">
                    <div class="flex items-center gap-1"><span class="material-symbols-outlined text-[15px]">groups</span> Kapasitas: 45 Mahasiswa</div>
                    <div class="flex items-center gap-1 mt-1"><span class="material-symbols-outlined text-[15px]">lan</span> 45 PC Core i7 RTX 4060, Switch Cisco Gigabit</div>
                </div>
            </div>
            <div class="pt-space-sm border-t border-outline-variant/40 flex items-center justify-between">
                <span class="font-data-mono text-[11px] text-secondary font-bold">20/27 Slot Kosong Hari Ini</span>
                <a href="{{ url('/public/availability') }}" class="px-space-md py-1.5 rounded bg-surface-container text-primary font-label-md text-label-md hover:bg-primary hover:text-on-primary transition-colors flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">calendar_today</span> Matriks
                </a>
            </div>
        </div>

        {{-- Item 3 --}}
        <div class="bg-surface-container-lowest rounded-xl p-space-lg shadow-sm border border-outline-variant/60 flex flex-col justify-between gap-space-md">
            <div class="flex flex-col gap-space-xs">
                <div class="flex items-center justify-between">
                    <span class="px-2 py-0.5 rounded font-data-mono text-data-mono bg-primary-container text-on-primary text-[11px]">CLS-B302</span>
                    <span class="px-2 py-0.5 rounded font-label-sm text-label-sm bg-secondary-container text-on-secondary-container font-semibold">Tersedia</span>
                </div>
                <h3 class="font-headline-md text-headline-md text-primary font-bold mt-1">Smart Classroom 302</h3>
                <p class="font-body-sm text-body-sm text-on-surface-variant">Gedung Kuliah Terpadu B, Lantai 3</p>
                <div class="mt-2 text-on-surface-variant font-label-sm text-label-sm">
                    <div class="flex items-center gap-1"><span class="material-symbols-outlined text-[15px]">groups</span> Kapasitas: 60 Kursi Kuliah</div>
                    <div class="flex items-center gap-1 mt-1"><span class="material-symbols-outlined text-[15px]">tv</span> Smart Board Touchscreen 85 Inch, Hybrid Cam</div>
                </div>
            </div>
            <div class="pt-space-sm border-t border-outline-variant/40 flex items-center justify-between">
                <span class="font-data-mono text-[11px] text-secondary font-bold">18/27 Slot Kosong Hari Ini</span>
                <a href="{{ url('/public/availability') }}" class="px-space-md py-1.5 rounded bg-surface-container text-primary font-label-md text-label-md hover:bg-primary hover:text-on-primary transition-colors flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">calendar_today</span> Matriks
                </a>
            </div>
        </div>

        {{-- Item 4 --}}
        <div class="bg-surface-container-lowest rounded-xl p-space-lg shadow-sm border border-outline-variant/60 flex flex-col justify-between gap-space-md">
            <div class="flex flex-col gap-space-xs">
                <div class="flex items-center justify-between">
                    <span class="px-2 py-0.5 rounded font-data-mono text-data-mono bg-primary-container text-on-primary text-[11px]">SPT-PKM01</span>
                    <span class="px-2 py-0.5 rounded font-label-sm text-label-sm bg-secondary-container text-on-secondary-container font-semibold">Tersedia</span>
                </div>
                <h3 class="font-headline-md text-headline-md text-primary font-bold mt-1">Aula Kemahasiswaan & Olahraga</h3>
                <p class="font-body-sm text-body-sm text-on-surface-variant">Gedung PKM Student Center, Lantai 1</p>
                <div class="mt-2 text-on-surface-variant font-label-sm text-label-sm">
                    <div class="flex items-center gap-1"><span class="material-symbols-outlined text-[15px]">groups</span> Kapasitas: 500 Orang / Lapangan Indoor</div>
                    <div class="flex items-center gap-1 mt-1"><span class="material-symbols-outlined text-[15px]">sports_soccer</span> Lapangan Futsal Vinyl & 2 Lapangan Badminton</div>
                </div>
            </div>
            <div class="pt-space-sm border-t border-outline-variant/40 flex items-center justify-between">
                <span class="font-data-mono text-[11px] text-secondary font-bold">11/27 Slot Kosong Hari Ini</span>
                <a href="{{ url('/public/availability') }}" class="px-space-md py-1.5 rounded bg-surface-container text-primary font-label-md text-label-md hover:bg-primary hover:text-on-primary transition-colors flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">calendar_today</span> Matriks
                </a>
            </div>
        </div>

        {{-- Item 5 --}}
        <div class="bg-surface-container-lowest rounded-xl p-space-lg shadow-sm border border-outline-variant/60 flex flex-col justify-between gap-space-md">
            <div class="flex flex-col gap-space-xs">
                <div class="flex items-center justify-between">
                    <span class="px-2 py-0.5 rounded font-data-mono text-data-mono bg-primary-container text-on-primary text-[11px]">SEM-A301</span>
                    <span class="px-2 py-0.5 rounded font-label-sm text-label-sm bg-secondary-container text-on-secondary-container font-semibold">Tersedia</span>
                </div>
                <h3 class="font-headline-md text-headline-md text-primary font-bold mt-1">Ruang Seminar Lantai 3</h3>
                <p class="font-body-sm text-body-sm text-on-surface-variant">Gedung Kuliah Terpadu A, Lantai 3</p>
                <div class="mt-2 text-on-surface-variant font-label-sm text-label-sm">
                    <div class="flex items-center gap-1"><span class="material-symbols-outlined text-[15px]">groups</span> Kapasitas: 120 Kursi Bertingkat</div>
                    <div class="flex items-center gap-1 mt-1"><span class="material-symbols-outlined text-[15px]">mic</span> Acoustic Wall Panel, Sound System & Wireless Mic</div>
                </div>
            </div>
            <div class="pt-space-sm border-t border-outline-variant/40 flex items-center justify-between">
                <span class="font-data-mono text-[11px] text-secondary font-bold">16/27 Slot Kosong Hari Ini</span>
                <a href="{{ url('/public/availability') }}" class="px-space-md py-1.5 rounded bg-surface-container text-primary font-label-md text-label-md hover:bg-primary hover:text-on-primary transition-colors flex items-center gap-1">
                    <span class="material-symbols-outlined text-[16px]">calendar_today</span> Matriks
                </a>
            </div>
        </div>

        {{-- Item 6: Under Maintenance --}}
        <div class="bg-surface-container-lowest rounded-xl p-space-lg shadow-sm border border-outline-variant/60 flex flex-col justify-between gap-space-md opacity-85">
            <div class="flex flex-col gap-space-xs">
                <div class="flex items-center justify-between">
                    <span class="px-2 py-0.5 rounded font-data-mono text-data-mono bg-primary-container text-on-primary text-[11px]">LAB-HW02</span>
                    <span class="px-2 py-0.5 rounded font-label-sm text-label-sm bg-error-container text-error font-semibold">Dalam Perbaikan</span>
                </div>
                <h3 class="font-headline-md text-headline-md text-primary font-bold mt-1">Lab Hardware & Robotika 2</h3>
                <p class="font-body-sm text-body-sm text-on-surface-variant">Gedung Lab Barat, Lantai 1</p>
                <div class="mt-2 text-on-surface-variant font-label-sm text-label-sm">
                    <div class="flex items-center gap-1"><span class="material-symbols-outlined text-[15px]">groups</span> Kapasitas: 30 Meja Praktikum</div>
                    <div class="flex items-center gap-1 mt-1 text-error"><span class="material-symbols-outlined text-[15px]">build</span> Perbaikan Instalasi Jalur Daya 3-Phase</div>
                </div>
            </div>
            <div class="pt-space-sm border-t border-outline-variant/40 flex items-center justify-between">
                <span class="font-data-mono text-[11px] text-error font-bold">Terkunci (Maintenance Mode)</span>
                <span class="px-space-md py-1.5 rounded bg-surface-container-high text-outline text-[12px] font-semibold cursor-not-allowed">
                    Non-Aktif
                </span>
            </div>
        </div>
    </div>
</x-public-layout>
