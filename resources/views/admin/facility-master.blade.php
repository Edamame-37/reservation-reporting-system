{{-- 
  NAMA FILE      : facility-master.blade.php
  FUNGSIONALITAS : Halaman Master Data Inventaris Fasilitas Kampus (Super Admin)
  DESKRIPSI      : Menampilkan seluruh inventaris ruangan kampus, fitur CRUD (Tambah, Edit, Non-aktifkan fasilitas), dan penguncian status operasional fasilitas (UR16).
  CARA KERJA     : Memanfaatkan layout <x-admin-layout active="facility-master"> dengan Alpine.js untuk search filtering berdasarkan gedung/kategori dan dialog modal manajemen inventaris.
--}}

<x-admin-layout title="Master Data Fasilitas Kampus" active="facility-master">
    <div x-data="{
        showFacilityModal: false,
        modalTitle: 'Tambah Fasilitas Baru (UR16)',
        searchQuery: '',
        categoryFilter: 'all',
        buildingFilter: 'all',
        facility: { id: '', name: '', code: '', type: 'auditorium', location: '', capacity: 100, openHours: '07:00 - 20:00 WIB', status: 'aktif', equipment: [] },
        openAdd() {
            this.modalTitle = 'Tambah Fasilitas Kampus Baru (UR16)';
            this.facility = { id: '', name: '', code: '', type: 'auditorium', location: '', capacity: 100, openHours: '07:00 - 20:00 WIB', status: 'aktif', equipment: ['Proyektor HD', 'AC Sentral'] };
            this.showFacilityModal = true;
        },
        openEdit(item) {
            this.modalTitle = 'Perbarui Data Fasilitas: ' + item.name;
            this.facility = Object.assign({}, item);
            this.showFacilityModal = true;
        }
    }" class="space-y-6">

        {{-- Page Header & Top Stats --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <nav class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                    <a href="{{ url('/admin/dashboard') }}" class="hover:text-navy transition-colors">Admin Console</a>
                    <span>/</span>
                    <span class="text-slate-800 font-medium">Master Fasilitas</span>
                </nav>
                <h1 class="text-2xl font-bold text-navy tracking-tight">Master Data Fasilitas & Ruang Kampus</h1>
                <p class="text-sm text-slate-500 mt-0.5">Kelola data inventaris venue, alokasi kapasitas, sarana kelistrikan/audio, dan status operasional sistem (UR16).</p>
            </div>

            <div class="flex items-center gap-2.5">
                <button type="button" @click="openAdd()" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-navy text-white text-xs font-semibold hover:bg-navy-light shadow-sm transition-all">
                    <span class="material-symbols-outlined text-[16px]">add_circle</span>
                    <span>+ Tambah Fasilitas (UR16)</span>
                </button>
            </div>
        </div>

        {{-- Quick Stat Summary Cards --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="text-xs font-medium text-slate-500 mb-1">Total Ruangan Terdata</div>
                <div class="text-2xl font-bold text-slate-800">24 <span class="text-xs font-normal text-slate-400">venue</span></div>
                <div class="text-[11px] text-emerald-600 mt-1 font-medium flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">domain</span> 4 Gedung Utama
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="text-xs font-medium text-slate-500 mb-1">Status Operasional Aktif</div>
                <div class="text-2xl font-bold text-emerald-600">22 <span class="text-xs font-normal text-slate-400">venue</span></div>
                <div class="text-[11px] text-slate-500 mt-1">Dapat direservasi oleh sivitas</div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="text-xs font-medium text-slate-500 mb-1">Terkunci / Perbaikan</div>
                <div class="text-2xl font-bold text-amber-600">2 <span class="text-xs font-normal text-slate-400">venue</span></div>
                <div class="text-[11px] text-amber-700 mt-1 font-medium">Dalam pemeliharaan teknisi</div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="text-xs font-medium text-slate-500 mb-1">Total Kapasitas Kursi</div>
                <div class="text-2xl font-bold text-slate-800">1.850 <span class="text-xs font-normal text-slate-400">kursi</span></div>
                <div class="text-[11px] text-slate-500 mt-1">Akumulasi seluruh kampus</div>
            </div>
        </div>

        {{-- Main Table Container --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            {{-- Search & Filter Bar --}}
            <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                {{-- Category Filter Pills --}}
                <div class="flex flex-wrap items-center gap-1.5">
                    <button @click="categoryFilter = 'all'" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all" :class="categoryFilter === 'all' ? 'bg-navy text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                        Semua Kategori (24)
                    </button>
                    <button @click="categoryFilter = 'auditorium'" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all" :class="categoryFilter === 'auditorium' ? 'bg-navy text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                        Auditorium (4)
                    </button>
                    <button @click="categoryFilter = 'kelas'" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all" :class="categoryFilter === 'kelas' ? 'bg-navy text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                        Ruang Kelas (12)
                    </button>
                    <button @click="categoryFilter = 'lab'" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all" :class="categoryFilter === 'lab' ? 'bg-navy text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                        Laboratorium (6)
                    </button>
                    <button @click="categoryFilter = 'olahraga'" class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all" :class="categoryFilter === 'olahraga' ? 'bg-navy text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'">
                        Olahraga (2)
                    </button>
                </div>

                {{-- Search & Building Filter --}}
                <div class="flex items-center gap-2.5">
                    <select x-model="buildingFilter" class="h-9 px-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 focus:bg-white focus:border-navy focus:outline-none">
                        <option value="all">Semua Gedung</option>
                        <option value="rektorat">Gedung Rektorat Baru</option>
                        <option value="lab">Gedung Lab Terpadu</option>
                        <option value="kuliah">Gedung Kuliah Terpadu</option>
                    </select>

                    <div class="relative w-full sm:w-56">
                        <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-[18px]">search</span>
                        <input type="text" x-model="searchQuery" placeholder="Cari nama / kode ruang..." class="w-full h-9 pl-9 pr-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-navy focus:outline-none transition-colors">
                    </div>
                </div>
            </div>

            {{-- Table of Facilities (UR16) --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-100">
                            <th class="py-3 px-5">Kode & Nama Ruangan</th>
                            <th class="py-3 px-4">Tipe & Lokasi Gedung</th>
                            <th class="py-3 px-4">Kapasitas</th>
                            <th class="py-3 px-4">Perlengkapan Standar</th>
                            <th class="py-3 px-4">Jam Operasional</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-5 text-right">Aksi Manajemen (UR16)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        {{-- Row 1 --}}
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-5">
                                <div class="font-semibold text-slate-800 text-sm">Auditorium B.J. Habibie</div>
                                <div class="font-mono text-[11px] text-slate-500">AUD-H01</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-slate-800">Auditorium & Hall</div>
                                <div class="text-[11px] text-slate-500">Gedung Rektorat Lt. 1 & 2</div>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800">450 Orang</td>
                            <td class="py-3.5 px-4">
                                <div class="flex flex-wrap gap-1">
                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 text-[10px] text-slate-600">Sound 5000W</span>
                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 text-[10px] text-slate-600">Videotron</span>
                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 text-[10px] text-slate-600">AC Sentral</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-600 text-[11px]">07:00 - 20:00 WIB</td>
                            <td class="py-3.5 px-4">
                                <x-cava.status-badge status="Aktif" />
                            </td>
                            <td class="py-3.5 px-5 text-right">
                                <div class="inline-flex items-center gap-1.5 justify-end">
                                    <button type="button" @click="openEdit({
                                        id: 1, name: 'Auditorium B.J. Habibie', code: 'AUD-H01', type: 'auditorium', location: 'Gedung Rektorat Lt. 1 & 2', capacity: 450, openHours: '07:00 - 20:00 WIB', status: 'aktif', equipment: ['Sound 5000W', 'Videotron', 'AC Sentral']
                                    })" class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 font-medium transition-colors">
                                        Edit
                                    </button>

                                    {{-- ROUTE: POST /admin/facilities/{id}/toggle (UR16) --}}
                                    <form action="{{ url('/admin/facilities/1/toggle') }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1.5 rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 font-medium transition-colors">
                                            Kunci
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        {{-- Row 2 --}}
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-5">
                                <div class="font-semibold text-slate-800 text-sm">Lab Jaringan & Komputasi Awan</div>
                                <div class="font-mono text-[11px] text-slate-500">LAB-C204</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-slate-800">Laboratorium</div>
                                <div class="text-[11px] text-slate-500">Gedung Lab Barat Lt. 2</div>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800">45 Orang</td>
                            <td class="py-3.5 px-4">
                                <div class="flex flex-wrap gap-1">
                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 text-[10px] text-slate-600">45 PC Core i7</span>
                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 text-[10px] text-slate-600">Gigabit Switch</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-600 text-[11px]">07:00 - 20:00 WIB</td>
                            <td class="py-3.5 px-4">
                                <x-cava.status-badge status="Aktif" />
                            </td>
                            <td class="py-3.5 px-5 text-right">
                                <div class="inline-flex items-center gap-1.5 justify-end">
                                    <button type="button" @click="openEdit({
                                        id: 2, name: 'Lab Jaringan & Komputasi Awan', code: 'LAB-C204', type: 'lab', location: 'Gedung Lab Barat Lt. 2', capacity: 45, openHours: '07:00 - 20:00 WIB', status: 'aktif', equipment: ['45 PC Core i7', 'Gigabit Switch']
                                    })" class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 font-medium transition-colors">
                                        Edit
                                    </button>

                                    <form action="{{ url('/admin/facilities/2/toggle') }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1.5 rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 font-medium transition-colors">
                                            Kunci
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        {{-- Row 3: Maintenance Status --}}
                        <tr class="hover:bg-slate-50/60 transition-colors bg-amber-50/30">
                            <td class="py-3.5 px-5">
                                <div class="font-semibold text-slate-800 text-sm">Lab Hardware & Robotika 2</div>
                                <div class="font-mono text-[11px] text-slate-500">LAB-HW02</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-slate-800">Laboratorium</div>
                                <div class="text-[11px] text-slate-500">Gedung Lab Barat Lt. 1</div>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800">30 Orang</td>
                            <td class="py-3.5 px-4">
                                <div class="flex flex-wrap gap-1">
                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 text-[10px] text-slate-600">Meja Solder</span>
                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 text-[10px] text-slate-600">Oscilloscope</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-600 text-[11px]">07:00 - 20:00 WIB</td>
                            <td class="py-3.5 px-4">
                                <x-cava.status-badge status="Dalam Perbaikan" />
                            </td>
                            <td class="py-3.5 px-5 text-right">
                                <div class="inline-flex items-center gap-1.5 justify-end">
                                    <button type="button" @click="openEdit({
                                        id: 3, name: 'Lab Hardware & Robotika 2', code: 'LAB-HW02', type: 'lab', location: 'Gedung Lab Barat Lt. 1', capacity: 30, openHours: '07:00 - 20:00 WIB', status: 'dalam perbaikan', equipment: ['Meja Solder', 'Oscilloscope']
                                    })" class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 font-medium transition-colors">
                                        Edit
                                    </button>

                                    <form action="{{ url('/admin/facilities/3/toggle') }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1.5 rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 font-medium transition-colors">
                                            Aktifkan
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        {{-- Row 4 --}}
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-5">
                                <div class="font-semibold text-slate-800 text-sm">Smart Classroom Teater 301</div>
                                <div class="font-mono text-[11px] text-slate-500">CLS-T301</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-slate-800">Ruang Kelas</div>
                                <div class="text-[11px] text-slate-500">Gedung Kuliah Terpadu Lt. 3</div>
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-slate-800">80 Orang</td>
                            <td class="py-3.5 px-4">
                                <div class="flex flex-wrap gap-1">
                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 text-[10px] text-slate-600">Smart TV 85"</span>
                                    <span class="px-1.5 py-0.5 rounded bg-slate-100 text-[10px] text-slate-600">Mic Wireless</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-600 text-[11px]">07:00 - 20:00 WIB</td>
                            <td class="py-3.5 px-4">
                                <x-cava.status-badge status="Aktif" />
                            </td>
                            <td class="py-3.5 px-5 text-right">
                                <div class="inline-flex items-center gap-1.5 justify-end">
                                    <button type="button" @click="openEdit({
                                        id: 4, name: 'Smart Classroom Teater 301', code: 'CLS-T301', type: 'kelas', location: 'Gedung Kuliah Terpadu Lt. 3', capacity: 80, openHours: '07:00 - 20:00 WIB', status: 'aktif', equipment: ['Smart TV 85', 'Mic Wireless']
                                    })" class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 font-medium transition-colors">
                                        Edit
                                    </button>

                                    <form action="{{ url('/admin/facilities/4/toggle') }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1.5 rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 font-medium transition-colors">
                                            Kunci
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Table Footer Pagination --}}
            <div class="p-4 bg-slate-50/50 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between text-xs text-slate-500 gap-2">
                <div>Menampilkan <strong>4</strong> dari <strong>24</strong> ruangan kampus</div>
                <div class="flex items-center gap-1">
                    <button class="px-2 py-1 rounded border border-slate-200 bg-white text-slate-400 cursor-not-allowed">Sebelumnya</button>
                    <button class="px-2.5 py-1 rounded bg-navy text-white font-semibold">1</button>
                    <button class="px-2.5 py-1 rounded border border-slate-200 bg-white text-slate-600 hover:bg-slate-100">2</button>
                    <button class="px-2.5 py-1 rounded border border-slate-200 bg-white text-slate-600 hover:bg-slate-100">3</button>
                    <button class="px-2 py-1 rounded border border-slate-200 bg-white text-slate-600 hover:bg-slate-100">Berikutnya</button>
                </div>
            </div>
        </div>

        {{-- MODAL: Tambah / Edit Fasilitas (UR16) --}}
        <div x-show="showFacilityModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="showFacilityModal = false" class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-lg w-full p-6 flex flex-col gap-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2 text-navy">
                        <span class="material-symbols-outlined text-[22px]">domain</span>
                        <h3 class="font-bold text-base" x-text="modalTitle"></h3>
                    </div>
                    <button type="button" @click="showFacilityModal = false" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                {{-- ROUTE: POST /admin/facilities/save (UR16) --}}
                <form action="{{ url('/admin/facilities/save') }}" method="POST" class="space-y-3.5">
                    @csrf
                    <input type="hidden" name="id" :value="facility.id">

                    <div class="grid grid-cols-3 gap-3">
                        <div class="col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 mb-1" for="fac-name">Nama Ruangan</label>
                            <input type="text" id="fac-name" name="name" x-model="facility.name" required placeholder="Contoh: Auditorium B.J. Habibie" class="w-full h-9 px-3 bg-slate-50 rounded-xl text-xs border border-slate-200 focus:bg-white focus:border-navy focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1" for="fac-code">Kode Ruang</label>
                            <input type="text" id="fac-code" name="code" x-model="facility.code" required placeholder="AUD-H01" class="w-full h-9 px-3 bg-slate-50 rounded-xl text-xs font-mono border border-slate-200 focus:bg-white focus:border-navy focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1" for="fac-type">Tipe Fasilitas</label>
                            <select id="fac-type" name="type" x-model="facility.type" class="w-full h-9 px-3 bg-slate-50 rounded-xl text-xs border border-slate-200 focus:bg-white focus:border-navy focus:outline-none">
                                <option value="auditorium">Auditorium & Hall</option>
                                <option value="kelas">Ruang Kelas</option>
                                <option value="lab">Laboratorium</option>
                                <option value="olahraga">Lapangan Olahraga</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1" for="fac-cap">Kapasitas (Orang)</label>
                            <input type="number" id="fac-cap" name="capacity" x-model="facility.capacity" required class="w-full h-9 px-3 bg-slate-50 rounded-xl text-xs border border-slate-200 focus:bg-white focus:border-navy focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1" for="fac-loc">Lokasi Gedung & Lantai</label>
                        <input type="text" id="fac-loc" name="location" x-model="facility.location" required placeholder="Gedung Rektorat Baru Lt. 1 & 2" class="w-full h-9 px-3 bg-slate-50 rounded-xl text-xs border border-slate-200 focus:bg-white focus:border-navy focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1" for="fac-hours">Jam Operasional</label>
                            <input type="text" id="fac-hours" name="openHours" x-model="facility.openHours" placeholder="07:00 - 20:00 WIB" class="w-full h-9 px-3 bg-slate-50 rounded-xl text-xs border border-slate-200 focus:bg-white focus:border-navy focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1" for="fac-status">Status Operasional Awal</label>
                            <select id="fac-status" name="status" x-model="facility.status" class="w-full h-9 px-3 bg-slate-50 rounded-xl text-xs border border-slate-200 focus:bg-white focus:border-navy focus:outline-none">
                                <option value="aktif">Aktif (Dapat Direservasi)</option>
                                <option value="dalam perbaikan">Dalam Perbaikan (Terkunci)</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="showFacilityModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-navy text-white text-xs font-semibold hover:bg-navy-light shadow-sm transition-colors">Simpan Fasilitas (UR16)</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-admin-layout>
