{{-- 
  NAMA FILE      : home.blade.php
  FUNGSIONALITAS : Halaman Beranda (Landing Page) Publik CAVA
  DESKRIPSI      : Menampilkan sambutan portal kampus, pencarian cepat, 3 fasilitas unggulan dengan batas data preview, dan tautan 'Lihat Selengkapnya' ke katalog dan jadwal lengkap.
  CARA KERJA     : Menggunakan layout <x-public-layout active="home">, menyajikan 3 data preview terkurasi dan tombol redirect ke halaman detail.
--}}

<x-public-layout title="Beranda Publik Fasilitas Kampus" active="home">
    <!-- 
      ELEMEN       : Hero Banner Pencarian Fasilitas Kampus
      KEGUNAAN     : Menyambut pengunjung dan sivitas dengan pencarian langsung ruang universitas.
      CARA KERJA   : Menerima input kata kunci dan mengirimkan parameter filter ke katalog.
    -->
    <section class="bg-gradient-to-br from-slate-900 via-slate-800 to-blue-950 rounded-3xl p-6 sm:p-10 text-white shadow-md mb-10 relative overflow-hidden">
        <div class="max-w-2xl relative z-10">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md text-xs font-semibold text-blue-200 border border-white/10 mb-4">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                <span>Sistem Otomasi Reservasi Fasilitas Kampus Terpadu</span>
            </div>
            <h1 class="text-2xl sm:text-4xl font-bold tracking-tight text-white leading-tight mb-3">
                Temukan & Cek Ketersediaan Fasilitas Akademik Kampus
            </h1>
            <p class="text-sm sm:text-base text-slate-300 mb-6 font-normal leading-relaxed">
                Akses informasi ruang auditorium, laboratorium komputer, dan smart classroom secara transparan per slot 30 menit dari pukul 07:00 hingga 20:00 WIB.
            </p>

            <!-- 
              ROUTE: Form pencarian GET ke /public/catalog
              FUNGSI: Membawa kata kunci pencarian pengunjung menuju katalog lengkap
            -->
            <form action="{{ url('/public/catalog') }}" method="GET" class="bg-white p-2 rounded-2xl shadow-xl flex flex-col sm:flex-row items-center gap-2 text-slate-800">
                <div class="relative flex-1 w-full">
                    <span class="material-symbols-outlined absolute left-3.5 top-2.5 text-slate-400 text-[20px]">search</span>
                    <input type="text" name="q" placeholder="Cari nama ruang (Auditorium, Lab Komputer, Smart Classroom)..." class="w-full pl-11 pr-4 py-2.5 text-sm rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-600 bg-transparent text-slate-800 placeholder:text-slate-400">
                </div>
                <button type="submit" class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800 transition shadow-xs flex items-center justify-center gap-1.5 shrink-0">
                    <span>Cari Ruang</span>
                    <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                </button>
            </form>
        </div>
    </section>

    <!-- 
      ELEMEN       : Fasilitas Kampus Unggulan (Batas 3 Data Preview + Lihat Selengkapnya)
      KEGUNAAN     : Menampilkan 3 fasilitas utama agar antarmuka tidak berantakan, disertai tombol 'Lihat Seluruh Fasilitas' ke halaman katalog.
      CARA KERJA   : Merender 3 artikel fasilitas terkurasi dengan ketersediaan visual dan tautan detail.
    -->
    <section class="mb-12">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 pb-3 mb-6 border-b border-slate-200">
            <div>
                <h2 class="text-xl font-bold text-slate-900 tracking-tight">Fasilitas Kampus Unggulan</h2>
                <p class="text-xs text-slate-500 mt-0.5">Menampilkan 3 dari 24 fasilitas akademik dan ruang pertemuan universitas.</p>
            </div>
            {{-- Tombol Lihat Selengkapnya menuju Katalog Lengkap --}}
            <a href="{{ url('/public/catalog') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-blue-950 hover:text-blue-700 transition">
                <span>Lihat Seluruh Fasilitas (24 Ruang)</span>
                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            {{-- Card 1: Auditorium B.J. Habibie --}}
            <article class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs hover:shadow-md transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">AUD-H01</span>
                        <x-cava.status-badge status="approved" label="Tersedia Hari Ini" />
                    </div>
                    <h3 class="text-base font-bold text-slate-900 leading-snug">Auditorium Utama B.J. Habibie</h3>
                    <p class="text-xs text-slate-500 flex items-center gap-1 mt-1 mb-3">
                        <span class="material-symbols-outlined text-[15px]">location_on</span>
                        <span>Gedung Rektorat (Lt. 1 & 2)</span>
                    </p>
                    <div class="flex items-center gap-2 text-xs text-slate-600 pb-3 mb-3 border-b border-slate-100">
                        <span class="font-semibold text-slate-800">450 Kursi</span>
                        <span>•</span>
                        <span class="truncate">AC Central, Laser Projector, Sound 5000W</span>
                    </div>
                </div>
                <div>
                    <x-cava.slot-matrix :interactive="false" :availableCount="14" :totalCount="27" />
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                        <a href="{{ url('/public/catalog') }}" class="text-xs font-semibold text-blue-900 hover:underline">
                            Detail Spesifikasi →
                        </a>
                        <a href="{{ route('login') }}" class="px-3 py-1.5 rounded-lg bg-slate-900 text-white text-xs font-medium hover:bg-slate-800 transition">
                            Reservasi
                        </a>
                    </div>
                </div>
            </article>

            {{-- Card 2: Lab Komputasi Cloud --}}
            <article class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs hover:shadow-md transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">LAB-C201</span>
                        <x-cava.status-badge status="approved" label="Tersedia Hari Ini" />
                    </div>
                    <h3 class="text-base font-bold text-slate-900 leading-snug">Lab Komputasi Cloud & Jaringan</h3>
                    <p class="text-xs text-slate-500 flex items-center gap-1 mt-1 mb-3">
                        <span class="material-symbols-outlined text-[15px]">location_on</span>
                        <span>Gedung Lab Terpadu C (Lt. 2)</span>
                    </p>
                    <div class="flex items-center gap-2 text-xs text-slate-600 pb-3 mb-3 border-b border-slate-100">
                        <span class="font-semibold text-slate-800">45 PC Core i7</span>
                        <span>•</span>
                        <span class="truncate">Gigabit LAN, Smart Screen, AC Dual</span>
                    </div>
                </div>
                <div>
                    <x-cava.slot-matrix :interactive="false" :availableCount="19" :totalCount="27" />
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                        <a href="{{ url('/public/catalog') }}" class="text-xs font-semibold text-blue-900 hover:underline">
                            Detail Spesifikasi →
                        </a>
                        <a href="{{ route('login') }}" class="px-3 py-1.5 rounded-lg bg-slate-900 text-white text-xs font-medium hover:bg-slate-800 transition">
                            Reservasi
                        </a>
                    </div>
                </div>
            </article>

            {{-- Card 3: Smart Classroom 302 --}}
            <article class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs hover:shadow-md transition-all flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">SMR-B302</span>
                        <x-cava.status-badge status="approved" label="Tersedia Hari Ini" />
                    </div>
                    <h3 class="text-base font-bold text-slate-900 leading-snug">Smart Classroom 302</h3>
                    <p class="text-xs text-slate-500 flex items-center gap-1 mt-1 mb-3">
                        <span class="material-symbols-outlined text-[15px]">location_on</span>
                        <span>Gedung Kuliah Bersama B (Lt. 3)</span>
                    </p>
                    <div class="flex items-center gap-2 text-xs text-slate-600 pb-3 mb-3 border-b border-slate-100">
                        <span class="font-semibold text-slate-800">60 Mahasiswa</span>
                        <span>•</span>
                        <span class="truncate">Interactive Whiteboard, Collab Desk</span>
                    </div>
                </div>
                <div>
                    <x-cava.slot-matrix :interactive="false" :availableCount="11" :totalCount="27" />
                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                        <a href="{{ url('/public/catalog') }}" class="text-xs font-semibold text-blue-900 hover:underline">
                            Detail Spesifikasi →
                        </a>
                        <a href="{{ route('login') }}" class="px-3 py-1.5 rounded-lg bg-slate-900 text-white text-xs font-medium hover:bg-slate-800 transition">
                            Reservasi
                        </a>
                    </div>
                </div>
            </article>
        </div>

        {{-- Action Button: Lihat Seluruh Fasilitas --}}
        <div class="mt-8 text-center">
            <a href="{{ url('/public/catalog') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-white border border-slate-200 text-sm font-semibold text-slate-800 hover:bg-slate-50 transition shadow-xs">
                <span>Buka Katalog Lengkap Seluruh Fasilitas Kampus</span>
                <span class="material-symbols-outlined text-[18px]">east</span>
            </a>
        </div>
    </section>

    <!-- 
      ELEMEN       : Card Ringkasan Jadwal 30 Menit & Redirect ke Availability
      KEGUNAAN     : Memberikan ringkasan cepat kalender tanpa menampilkan ratusan slot, disertai tombol ke matriks jadwal lengkap.
    -->
    <section class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs flex flex-col sm:flex-row items-center justify-between gap-6">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-900 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[28px]">calendar_month</span>
            </div>
            <div>
                <h3 class="text-base font-bold text-slate-900">Perlu Memeriksa Ketersediaan Slot Waktu Tertentu?</h3>
                <p class="text-xs text-slate-500 mt-0.5">Lihat matriks lengkap jadwal penggunaan per interval 30 menit (07:00 - 20:00 WIB) untuk seluruh ruang.</p>
            </div>
        </div>
        <a href="{{ url('/public/availability') }}" class="px-5 py-2.5 rounded-xl bg-slate-900 text-white text-xs sm:text-sm font-semibold hover:bg-slate-800 transition shadow-xs flex items-center gap-1.5 shrink-0">
            <span>Buka Matriks Jadwal Lengkap</span>
            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
        </a>
    </section>
</x-public-layout>
