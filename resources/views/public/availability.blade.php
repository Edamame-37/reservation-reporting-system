{{-- 
  NAMA FILE      : availability.blade.php
  FUNGSIONALITAS : Halaman Antarmuka Matriks Ketersediaan Real-Time 30 Menit (Publik & Pengguna)
  DESKRIPSI      : Menampilkan kalender grid matriks 27 slot (07:00 - 20:00 WIB) per fasilitas tanpa membocorkan identitas peminjam.
  CARA KERJA     : Memanfaatkan layout <x-public-layout active="availability">, menyajikan visualisasi slot ketersediaan 30 menitan secara live.
--}}

<x-public-layout title="Matriks Ketersediaan Slot 30 Menit" active="availability">
    {{-- Header --}}
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-space-md mb-space-lg">
        <div>
            <div class="flex items-center gap-space-xs">
                <span class="font-data-mono text-data-mono text-secondary font-bold text-[11px]">REAL-TIME AVAILABILITY MATRIX</span>
                <span>•</span>
                <span class="font-label-sm text-label-sm text-on-surface-variant font-data-mono">UR01 • SFR04</span>
            </div>
            <h1 class="font-headline-lg text-headline-lg text-primary tracking-tight">Matriks Ketersediaan Slot Waktu 30 Menit</h1>
            <p class="font-body-sm text-body-sm text-on-surface-variant">Pantau slot kosong atau terisi secara transparan dengan jaminan privasi data sivitas.</p>
        </div>
        <div class="flex items-center gap-space-sm">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded bg-surface-container-high text-primary font-data-mono text-data-mono text-[11px]">
                <span class="w-2 h-2 rounded-full bg-secondary animate-pulse"></span>
                LIVE SYNC ENGINE
            </span>
        </div>
    </div>

    {{-- Filter Tanggal & Gedung --}}
    <section class="bg-surface-container-lowest shadow-sm rounded-xl p-space-lg mb-space-xl">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-space-md items-end">
            <div>
                <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant" for="matrix-date">Pilih Tanggal</label>
                <input type="date" id="matrix-date" value="{{ date('Y-m-d') }}" class="w-full h-10 px-space-md rounded-lg bg-surface-container-low text-on-surface font-body-sm border border-outline-variant/50 focus:border-primary focus:bg-surface-container-lowest focus:outline-none">
            </div>
            <div>
                <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant" for="matrix-venue">Filter Spesifik Fasilitas</label>
                <select id="matrix-venue" class="w-full h-10 px-space-md rounded-lg bg-surface-container-low text-on-surface font-body-sm border border-outline-variant/50 focus:border-primary focus:bg-surface-container-lowest focus:outline-none">
                    <option value="all">Tampilkan Semua Fasilitas (5 Aktif)</option>
                    <option value="auditorium">Auditorium B.J. Habibie</option>
                    <option value="lab-c204">Smart Lab Komputasi Awan</option>
                    <option value="cls-302">Smart Classroom 302</option>
                    <option value="hall-pkm">Aula Kemahasiswaan PKM</option>
                </select>
            </div>
            <div>
                <a href="{{ route('login') }}" class="w-full h-10 inline-flex items-center justify-center gap-space-xs rounded-lg bg-primary text-on-primary hover:bg-primary-container transition-colors shadow-sm font-label-lg text-label-lg">
                    <span class="material-symbols-outlined text-[18px]">add_circle</span>
                    <span>Ajukan Reservasi (Login)</span>
                </a>
            </div>
        </div>
    </section>

    {{-- Daftar Matriks Tiap Fasilitas --}}
    <div class="flex flex-col gap-space-xl">
        {{-- Fasilitas 1 --}}
        <div class="bg-surface-container-lowest rounded-xl p-space-lg shadow-sm">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between pb-space-sm mb-space-md border-b border-outline-variant gap-2">
                <div>
                    <span class="px-2 py-0.5 rounded font-data-mono text-data-mono bg-primary-container text-on-primary text-[11px]">AUD-H01</span>
                    <h2 class="font-headline-md text-headline-md text-primary font-bold inline-block ml-2">Auditorium B.J. Habibie</h2>
                    <span class="text-on-surface-variant text-body-sm ml-2">• Gedung Rektorat (Kapasitas: 450)</span>
                </div>
                <span class="font-data-mono text-secondary font-bold text-headline-sm">14 / 27 TERSEDIA</span>
            </div>
            <x-cava.slot-matrix :selectable="false" venueName="Auditorium B.J. Habibie" />
        </div>

        {{-- Fasilitas 2 --}}
        <div class="bg-surface-container-lowest rounded-xl p-space-lg shadow-sm">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between pb-space-sm mb-space-md border-b border-outline-variant gap-2">
                <div>
                    <span class="px-2 py-0.5 rounded font-data-mono text-data-mono bg-primary-container text-on-primary text-[11px]">LAB-C204</span>
                    <h2 class="font-headline-md text-headline-md text-primary font-bold inline-block ml-2">Smart Lab Komputasi Awan</h2>
                    <span class="text-on-surface-variant text-body-sm ml-2">• Gedung Lab Barat (Kapasitas: 45)</span>
                </div>
                <span class="font-data-mono text-secondary font-bold text-headline-sm">20 / 27 TERSEDIA</span>
            </div>
            <x-cava.slot-matrix :selectable="false" venueName="Lab Komputasi Awan" />
        </div>

        {{-- Fasilitas 3 --}}
        <div class="bg-surface-container-lowest rounded-xl p-space-lg shadow-sm">
            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between pb-space-sm mb-space-md border-b border-outline-variant gap-2">
                <div>
                    <span class="px-2 py-0.5 rounded font-data-mono text-data-mono bg-primary-container text-on-primary text-[11px]">CLS-B302</span>
                    <h2 class="font-headline-md text-headline-md text-primary font-bold inline-block ml-2">Smart Classroom 302</h2>
                    <span class="text-on-surface-variant text-body-sm ml-2">• Gedung B Lt. 3 (Kapasitas: 60)</span>
                </div>
                <span class="font-data-mono text-secondary font-bold text-headline-sm">18 / 27 TERSEDIA</span>
            </div>
            <x-cava.slot-matrix :selectable="false" venueName="Smart Classroom 302" />
        </div>
    </div>
</x-public-layout>
