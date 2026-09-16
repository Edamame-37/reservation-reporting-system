{{-- 
  NAMA FILE      : catalog.blade.php
  FUNGSIONALITAS : Halaman Katalog Lengkap Fasilitas & Ruang Kampus (Visitor & Sivitas)
  DESKRIPSI      : Menampilkan seluruh daftar fasilitas kampus secara komprehensif dengan filter tipe, gedung, kapasitas, modal spesifikasi alat, dan tombol aksi pemesanan.
  CARA KERJA     : Menggunakan layout <x-public-layout active="catalog">, menyediakan pencarian instan dan modal detail interaktif dengan Alpine.js.
--}}

<x-public-layout title="Katalog Lengkap Fasilitas Kampus" active="catalog">
    <div x-data="{
        search: '',
        selectedCategory: 'semua',
        selectedBuilding: 'semua',
        modalDetail: false,
        activeVenue: null,
        venues: [
            { id: 'AUD-H01', name: 'Auditorium Utama B.J. Habibie', category: 'auditorium', building: 'Gedung Rektorat (Lt. 1 & 2)', capacity: 450, status: 'approved', availableSlots: 14, totalSlots: 27, equipment: ['AC Central', 'Dual Laser Projector', 'Sound Yamaha 5000W', '8 Mic Wireless', 'Podium VIP'], desc: 'Auditorium utama universitas berstandar internasional untuk wisuda, seminar internasional, dan orasi ilmiah.' },
            { id: 'LAB-C204', name: 'Lab Komputasi Cloud & Jaringan', category: 'lab', building: 'Gedung Lab Terpadu C (Lt. 2)', capacity: 45, status: 'approved', availableSlots: 19, totalSlots: 27, equipment: ['45 PC Core i7 RTX 4060', 'Gigabit Switch Cisco', 'AC Dual 2PK', 'Smart Display', 'Whiteboard'], desc: 'Laboratorium riset jaringan, cloud virtualization, dan praktikum mahasiswa informatika.' },
            { id: 'CLS-B302', name: 'Smart Classroom 302', category: 'kelas', building: 'Gedung Kuliah Bersama B (Lt. 3)', capacity: 60, status: 'approved', availableSlots: 11, totalSlots: 27, equipment: ['Interactive Whiteboard', 'Video Conference Cam', 'Collab Desks', 'Audio Mic'], desc: 'Ruang kelas multimedia modern dengan meja kolaborasi ergonomis dan sistem video conference untuk kuliah hybrid.' },
            { id: 'SPT-PKM01', name: 'Aula Serbaguna & Olahraga PKM', category: 'olahraga', building: 'Pusat Kegiatan Mahasiswa (Lt. 1)', capacity: 500, status: 'approved', availableSlots: 8, totalSlots: 27, equipment: ['Lapangan Futsal Vinyl', '2 Lapangan Badminton', 'Sound System', 'Ruang Ganti', 'Tribun'], desc: 'Fasilitas serbaguna untuk kegiatan ormawa kampus, turnamen olahraga antar fakultas, dan pameran kewirausahaan.' },
            { id: 'SEM-A301', name: 'Ruang Seminar Lantai 3', category: 'kelas', building: 'Gedung Kuliah Terpadu A (Lt. 3)', capacity: 120, status: 'approved', availableSlots: 16, totalSlots: 27, equipment: ['Acoustic Wall Panel', 'Sound System', 'Wireless Mic', 'Dual Screen Projector'], desc: 'Ruang teater bertingkat untuk presentasi seminar skripsi, kuliah umum fakultas, dan lokakarya.' },
            { id: 'RPT-SENAT', name: 'Ruang Rapat Senat Akademik', category: 'rapat', building: 'Gedung Rektorat (Lt. 3)', capacity: 35, status: 'locked', availableSlots: 0, totalSlots: 27, equipment: ['Meja Oval Konferensi', 'Delegate Mic Units', 'Display LCD 85 Inch', 'Executive Chairs'], desc: 'Ruang sidang formal para pimpinan universitas dan dewan senat. Saat ini sedang dalam perbaikan tata suara.' }
        ],
        get filteredVenues() {
            return this.venues.filter(v => {
                const matchSearch = v.name.toLowerCase().includes(this.search.toLowerCase()) || v.id.toLowerCase().includes(this.search.toLowerCase()) || v.building.toLowerCase().includes(this.search.toLowerCase());
                const matchCat = this.selectedCategory === 'semua' || v.category === this.selectedCategory;
                const matchBld = this.selectedBuilding === 'semua' || v.building.includes(this.selectedBuilding);
                return matchSearch && matchCat && matchBld;
            });
        },
        openDetail(v) {
            this.activeVenue = v;
            this.modalDetail = true;
        }
    }">

        {{-- Header Breadcrumb & Judul Halaman --}}
        <div class="mb-6">
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-2 font-medium">
                <a href="{{ url('/') }}" class="hover:text-slate-900 transition">Beranda</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-slate-900">Katalog Lengkap Fasilitas Kampus</span>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Katalog Seluruh Fasilitas & Ruang Kampus</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Daftar lengkap inventaris ruang universitas dengan spesifikasi peralatan, kapasitas, dan status terkini.</p>
                </div>
                <a href="{{ url('/public/availability') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-white border border-slate-200 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50 transition shadow-xs">
                    <span class="material-symbols-outlined text-[18px]">calendar_month</span>
                    <span>Buka Matriks Jadwal 30m</span>
                </a>
            </div>
        </div>

        {{-- Parameter Filter & Pencarian --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="relative w-full md:w-80">
                <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-[20px]">search</span>
                <input type="text" x-model="search" placeholder="Cari nama ruang, kode, gedung..." class="w-full pl-10 pr-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-slate-900 focus:bg-white transition">
            </div>

            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                <div class="flex items-center gap-2">
                    <label for="filter-cat" class="text-xs font-semibold text-slate-500">Tipe:</label>
                    <select id="filter-cat" x-model="selectedCategory" class="text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-900">
                        <option value="semua">Semua Tipe</option>
                        <option value="auditorium">Auditorium & Hall</option>
                        <option value="lab">Laboratorium</option>
                        <option value="kelas">Ruang Kelas / Teater</option>
                        <option value="olahraga">Aula & Olahraga</option>
                        <option value="rapat">Ruang Rapat</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <label for="filter-bld" class="text-xs font-semibold text-slate-500">Gedung:</label>
                    <select id="filter-bld" x-model="selectedBuilding" class="text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-900">
                        <option value="semua">Semua Gedung</option>
                        <option value="Rektorat">Gedung Rektorat</option>
                        <option value="Lab Terpadu C">Gedung Lab Terpadu C</option>
                        <option value="Kuliah Bersama B">Gedung Kuliah Bersama B</option>
                        <option value="PKM">Student Center (PKM)</option>
                    </select>
                </div>

                <button type="button" @click="search = ''; selectedCategory = 'semua'; selectedBuilding = 'semua';" class="text-xs font-medium text-slate-500 hover:text-slate-900 underline px-1">
                    Reset
                </button>
            </div>
        </div>

        {{-- Indikator Jumlah Data --}}
        <div class="flex items-center justify-between text-xs text-slate-500 mb-4 px-1">
            <span>Menampilkan <strong class="text-slate-900" x-text="filteredVenues.length"></strong> fasilitas kampus</span>
            <span>Jam Operasional: 07:00 - 20:00 WIB</span>
        </div>

        {{-- Grid Seluruh Fasilitas Kampus --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <template x-for="venue in filteredVenues" :key="venue.id">
                <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs hover:shadow-md transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700" x-text="venue.id"></span>
                            <span x-show="venue.status === 'approved'" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs bg-emerald-50 text-emerald-800 border border-emerald-200/60 font-semibold">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Tersedia
                            </span>
                            <span x-show="venue.status === 'locked'" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs bg-rose-50 text-rose-700 border border-rose-200/60 font-semibold">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                Dalam Perbaikan
                            </span>
                        </div>

                        <h3 class="text-base font-bold text-slate-900 leading-snug" x-text="venue.name"></h3>
                        <p class="text-xs text-slate-500 flex items-center gap-1 mt-1 mb-3">
                            <span class="material-symbols-outlined text-[15px]">location_on</span>
                            <span x-text="venue.building"></span>
                        </p>

                        <div class="flex items-center gap-2 text-xs text-slate-600 pb-3 mb-3 border-b border-slate-100">
                            <span class="font-semibold text-slate-800" x-text="venue.capacity + ' Kursi'"></span>
                            <span>•</span>
                            <span class="truncate" x-text="venue.equipment.slice(0, 2).join(', ') + '...'"></span>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between text-xs text-slate-600 mb-1">
                            <span>Okupansi Hari Ini:</span>
                            <span class="font-semibold" x-text="venue.status === 'approved' ? (venue.availableSlots + ' / ' + venue.totalSlots + ' Slot Bebas') : 'Terkunci'"></span>
                        </div>
                        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden mb-4">
                            <div class="h-full rounded-full transition-all"
                                 :class="venue.status === 'approved' ? 'bg-emerald-500' : 'bg-slate-400'"
                                 :style="`width: ${(venue.availableSlots / venue.totalSlots) * 100}%`"></div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" @click="openDetail(venue)" class="flex-1 py-2 px-3 rounded-xl border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50 transition text-center flex items-center justify-center gap-1">
                                <span class="material-symbols-outlined text-[16px]">info</span>
                                <span>Detail</span>
                            </button>
                            <a href="{{ route('login') }}" class="flex-1 py-2 px-3 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition text-center shadow-xs">
                                Reservasi
                            </a>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Modal Detail Spesifikasi Fasilitas --}}
        <div x-show="modalDetail" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="modalDetail = false" class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 relative">
                <button type="button" @click="modalDetail = false" class="absolute top-4 right-4 p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100">
                    <span class="material-symbols-outlined">close</span>
                </button>

                <template x-if="activeVenue">
                    <div>
                        <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-1">
                            <span class="px-2 py-0.5 rounded bg-slate-100 font-mono text-slate-800" x-text="activeVenue.id"></span>
                            <span class="uppercase tracking-wider" x-text="activeVenue.category"></span>
                        </div>
                        <h2 class="text-xl font-bold text-slate-900 mb-1" x-text="activeVenue.name"></h2>
                        <p class="text-xs text-slate-500 flex items-center gap-1 mb-4">
                            <span class="material-symbols-outlined text-[16px]">location_on</span>
                            <span x-text="activeVenue.building"></span>
                        </p>

                        <p class="text-xs text-slate-600 mb-4 leading-relaxed" x-text="activeVenue.desc"></p>

                        <div class="grid grid-cols-2 gap-3 bg-slate-50 p-3.5 rounded-2xl mb-4 border border-slate-100 text-xs">
                            <div>
                                <span class="text-slate-400 block">Kapasitas Maksimal:</span>
                                <span class="text-sm font-bold text-slate-900" x-text="activeVenue.capacity + ' Orang'"></span>
                            </div>
                            <div>
                                <span class="text-slate-400 block">Jam Operasional:</span>
                                <span class="text-sm font-bold text-slate-900">07:00 - 20:00 WIB</span>
                            </div>
                        </div>

                        <div class="mb-6">
                            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Peralatan Tersedia:</h4>
                            <div class="flex flex-wrap gap-1.5">
                                <template x-for="item in activeVenue.equipment" :key="item">
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200/80" x-text="item"></span>
                                </template>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                            <button type="button" @click="modalDetail = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50">
                                Tutup
                            </button>
                            <a href="{{ route('login') }}" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 shadow-xs">
                                Masuk untuk Reservasi
                            </a>
                        </div>
                    </div>
                </template>
            </div>
        </div>

    </div>
</x-public-layout>
