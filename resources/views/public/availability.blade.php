{{-- 
  NAMA FILE      : availability.blade.php
  FUNGSIONALITAS : Halaman Matriks Jadwal Ketersediaan Ruang (Bento UI & Paginasi 10 Data)
  DESKRIPSI      : Menampilkan matriks jadwal slot 30 menit seluruh fasilitas kampus (07:00 - 20:00 WIB) dengan Bento UI modular, filter tanggal & gedung, legenda status, dan paginasi interaktif dibatasi 10 ruangan per halaman.
  CARA KERJA     : Menggunakan layout <x-public-layout active="availability">, menyajikan visualisasi slot ketersediaan interaktif dengan Alpine.js tanpa reload.
--}}

<x-public-layout title="Matriks Jadwal Ketersediaan Ruang" active="availability">
    <div x-data="availabilityData()" class="space-y-6">

        {{-- ========================================================================= --}}
        {{-- BENTO GRID 1: HEADER & PANEL KONTROL MATRIKS JADWAL                       --}}
        {{-- ========================================================================= --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
            
            {{-- Bento Card 1: Headline & Keterangan Operasional (7 Kolom) --}}
            <div class="lg:col-span-7 bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-7 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-slate-100 text-slate-700 text-xs font-semibold mb-3">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>Jadwal Operasional Kampus: 07:00 - 20:00 WIB</span>
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-slate-900 leading-tight">
                        Matriks Ketersediaan Slot Waktu Ruang
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-2 leading-relaxed">
                        Transparansi status seluruh auditorium, lab komputer, dan ruang kelas interaktif per interval 30 menit. Silakan pilih tanggal dan gedung untuk memantau waktu luang fasilitas sebelum mengajukan peminjaman.
                    </p>
                </div>

                {{-- Indikator Legenda Status --}}
                <div class="mt-5 pt-4 border-t border-slate-100 flex flex-wrap items-center gap-4 text-xs">
                    <span class="flex items-center gap-2">
                        <span class="w-3.5 h-3.5 rounded-md bg-emerald-50 border border-emerald-300"></span>
                        <span class="text-slate-700 font-medium">Bebas / Tersedia</span>
                    </span>
                    <span class="flex items-center gap-2">
                        <span class="w-3.5 h-3.5 rounded-md bg-rose-50 border border-rose-300"></span>
                        <span class="text-slate-700 font-medium">Terisi (Approved)</span>
                    </span>
                    <span class="flex items-center gap-2">
                        <span class="w-3.5 h-3.5 rounded-md bg-slate-200 border border-slate-300"></span>
                        <span class="text-slate-700 font-medium">Dalam Perbaikan</span>
                    </span>
                </div>
            </div>

            {{-- Bento Card 2: Filter Toolbar Tanggal & Gedung (5 Kolom) --}}
            <div class="lg:col-span-5 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white rounded-3xl p-6 sm:p-7 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <span class="text-xs font-semibold text-slate-300 uppercase tracking-wider">Filter Pemantauan</span>
                        <span class="inline-flex items-center gap-1 text-[11px] text-blue-200 bg-white/10 px-2.5 py-0.5 rounded-full font-medium">
                            <span>Privasi UR-01 Aktif</span>
                        </span>
                    </div>
                    <h3 class="text-lg font-bold text-white mb-4">Pilih Jadwal & Lokasi</h3>

                    <div class="space-y-3.5">
                        {{-- Pemilih Tanggal --}}
                        <div>
                            <label for="matrix-date" class="block text-xs font-semibold text-slate-300 mb-1.5">Tanggal Pelaksanaan:</label>
                            <div class="relative">
                                <input type="date" 
                                       id="matrix-date" 
                                       x-model="selectedDate" 
                                       class="w-full text-xs sm:text-sm bg-white/10 border border-white/20 rounded-xl px-3.5 py-2.5 text-white font-medium focus:outline-none focus:ring-2 focus:ring-blue-400 placeholder-slate-400">
                            </div>
                        </div>

                        {{-- Pemilih Gedung --}}
                        <div>
                            <label for="matrix-building" class="block text-xs font-semibold text-slate-300 mb-1.5">Gedung Kampus:</label>
                            <select id="matrix-building" 
                                    x-model="selectedBuilding" 
                                    class="w-full text-xs sm:text-sm bg-white/10 border border-white/20 rounded-xl px-3.5 py-2.5 text-white font-medium focus:outline-none focus:ring-2 focus:ring-blue-400 [&>option]:text-slate-900 [&>option]:bg-white">
                                <option value="semua">Semua Gedung Kampus</option>
                                <option value="Rektorat">Gedung Rektorat</option>
                                <option value="Lab Terpadu C">Gedung Lab Terpadu C</option>
                                <option value="Kuliah Bersama B">Gedung Kuliah Bersama B</option>
                                <option value="PKM">Student Center (PKM)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="mt-4 pt-3 border-t border-white/10 text-[11px] text-slate-400 flex items-center justify-between">
                    <span>Transparansi publik terverifikasi</span>
                    <span x-show="isLoading" class="text-emerald-400 font-semibold animate-pulse">Memuat ketersediaan...</span>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- BENTO GRID 2: TABEL MATRIKS JADWAL DENGAN PAGINASI 10 RUANGAN             --}}
        {{-- ========================================================================= --}}
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
            
            {{-- Toolbar Status Paginasi Atas --}}
            <div class="px-6 py-4 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-slate-50/50">
                <div class="text-xs text-slate-600 font-medium">
                    Menampilkan <strong class="text-slate-900" x-text="filteredRooms.length > 0 ? ((currentPage - 1) * perPage) + 1 : 0"></strong>
                    - <strong class="text-slate-900" x-text="Math.min(currentPage * perPage, filteredRooms.length)"></strong> 
                    dari <strong class="text-slate-900" x-text="filteredRooms.length"></strong> Fasilitas Kampus
                    <span class="text-slate-400 ml-1">(Dibatasi 10 ruang per halaman)</span>
                </div>

                <div class="flex items-center gap-1.5 text-xs text-slate-500 font-medium">
                    <span>Halaman</span>
                    <span class="px-2 py-0.5 rounded-md bg-white border border-slate-200 font-bold text-slate-900 font-mono" x-text="currentPage"></span>
                    <span>dari</span>
                    <span class="font-bold text-slate-900 font-mono" x-text="totalPages"></span>
                </div>
            </div>

            {{-- Container Tabel Gulir Horizontal --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-100/70 border-b border-slate-200 text-slate-700">
                            <th class="py-3 px-4 font-bold uppercase tracking-wider min-w-[240px] sticky left-0 bg-slate-100/95 backdrop-blur-xs z-20 shadow-[2px_0_4px_-2px_rgba(0,0,0,0.08)]">
                                Ruang / Fasilitas
                            </th>
                            <template x-for="slot in timeSlots" :key="slot">
                                <th class="py-2.5 px-2 text-center font-bold text-slate-600 border-l border-slate-200/70 min-w-[58px]" x-text="slot"></th>
                            </template>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="room in paginatedRooms" :key="room.id">
                            <tr class="group hover:bg-slate-50/90 transition-all duration-300">
                                {{-- Kolom Kiri Sticky: Nama Ruang & Detail Hanya Total Kursi dengan Animasi Pop-Up --}}
                                <td class="py-3 px-4 sticky left-0 bg-white group-hover:bg-slate-50/95 z-10 shadow-[2px_0_4px_-2px_rgba(0,0,0,0.06)] border-b border-slate-100 transition-colors">
                                    <div class="transition-all duration-300 transform group-hover:scale-[1.03] group-hover:translate-x-1 origin-left">
                                        <div class="font-bold text-slate-900 text-sm leading-snug group-hover:text-blue-950 transition-colors" x-text="room.name"></div>
                                        <div class="text-[11px] text-slate-500 flex items-center gap-1.5 mt-1">
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-slate-100/90 font-semibold text-slate-700 shadow-2xs">
                                                <span class="material-symbols-outlined text-[13px] text-slate-500">group</span>
                                                <span x-text="room.capacity + ' Kursi'"></span>
                                            </span>
                                        </div>
                                    </div>
                                </td>

                                {{-- Slot 30 Menit dengan Efek Animasi Pop-Up Mikro saat Hover --}}
                                <template x-for="slot in timeSlots" :key="slot">
                                    <td class="p-1 border-l border-slate-100 text-center align-middle">
                                        {{-- Kondisi 1: Ruang Dalam Perbaikan --}}
                                        <div x-show="room.locked" 
                                             class="w-full h-8 rounded-lg bg-slate-200 border border-slate-300 flex items-center justify-center text-[10px] text-slate-600 transition-all duration-200 transform hover:scale-110 hover:shadow-xs" 
                                             title="Ruang Dalam Perbaikan">
                                            <span class="material-symbols-outlined text-[13px]">build</span>
                                        </div>

                                        {{-- Kondisi 2: Slot Terisi --}}
                                        <div x-show="!room.locked && isBooked(room, slot)" 
                                             class="w-full h-8 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 flex items-center justify-center text-[9px] font-bold transition-all duration-200 transform hover:scale-110 hover:shadow-xs hover:z-30 relative cursor-default" 
                                             title="Slot Waktu Terpakai (Approved)">
                                            <span>Terisi</span>
                                        </div>

                                        {{-- Kondisi 3: Slot Bebas/Tersedia --}}
                                        <div x-show="!room.locked && !isBooked(room, slot)" 
                                             class="w-full h-8 rounded-lg bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 transition-colors text-emerald-800 flex items-center justify-center text-[9px] font-semibold transition-all duration-200 transform hover:scale-110 hover:shadow-xs hover:z-30 relative cursor-pointer" 
                                             title="Slot Waktu Tersedia">
                                            <span>Bebas</span>
                                        </div>
                                    </td>
                                </template>
                            </tr>
                        </template>

                        {{-- State Kosong Jika Filter Tidak Menemukan Ruang --}}
                        <template x-if="paginatedRooms.length === 0">
                            <tr>
                                <td :colspan="timeSlots.length + 1" class="py-12 text-center text-slate-500">
                                    <span class="material-symbols-outlined text-[36px] text-slate-300 mb-1">domain_disabled</span>
                                    <p class="text-sm font-semibold text-slate-800">Tidak ada fasilitas yang sesuai dengan filter gedung ini.</p>
                                    <p class="text-xs text-slate-400 mt-0.5">Silakan pilih 'Semua Gedung Kampus' untuk melihat seluruh ruangan.</p>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            {{-- Toolbar Navigasi Paginasi Bento Bawah (Show Only 10 Items per Page) --}}
            <div class="px-6 py-4 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-slate-50/40">
                <div class="text-xs text-slate-500">
                    Menampilkan halaman <strong class="text-slate-800" x-text="currentPage"></strong> dari <strong class="text-slate-800" x-text="totalPages"></strong>
                </div>

                {{-- Tombol Navigasi Paginasi --}}
                <div class="flex items-center gap-1.5">
                    {{-- Tombol Sebelumnya --}}
                    <button type="button" 
                            @click="prevPage()" 
                            :disabled="currentPage === 1"
                            :class="currentPage === 1 ? 'opacity-40 cursor-not-allowed bg-slate-100 text-slate-400 border-slate-200' : 'bg-white text-slate-700 hover:bg-slate-100 hover:text-slate-900 border-slate-200 shadow-2xs'"
                            class="px-3 py-1.5 rounded-xl border text-xs font-semibold transition flex items-center gap-1">
                        <span class="material-symbols-outlined text-[14px]">arrow_back</span>
                        <span>Sebelumnya</span>
                    </button>

                    {{-- Tombol Nomor Halaman --}}
                    <div class="flex items-center gap-1">
                        <template x-for="p in totalPages" :key="p">
                            <button type="button" 
                                    @click="goToPage(p)"
                                    :class="currentPage === p 
                                        ? 'bg-slate-900 text-white font-bold shadow-xs' 
                                        : 'bg-white text-slate-700 hover:bg-slate-100 border border-slate-200 font-semibold'"
                                    class="w-8 h-8 rounded-xl text-xs transition flex items-center justify-center font-mono"
                                    x-text="p">
                            </button>
                        </template>
                    </div>

                    {{-- Tombol Selanjutnya --}}
                    <button type="button" 
                            @click="nextPage()" 
                            :disabled="currentPage === totalPages"
                            :class="currentPage === totalPages ? 'opacity-40 cursor-not-allowed bg-slate-100 text-slate-400 border-slate-200' : 'bg-white text-slate-700 hover:bg-slate-100 hover:text-slate-900 border-slate-200 shadow-2xs'"
                            class="px-3 py-1.5 rounded-xl border text-xs font-semibold transition flex items-center gap-1">
                        <span>Selanjutnya</span>
                        <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- BENTO GRID 3: QUICK CTA & REGULASI PEMINJAMAN                             --}}
        {{-- ========================================================================= --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            
            {{-- Card CTA: Masuk SSO untuk Reservasi --}}
            <div class="bg-gradient-to-r from-slate-900 to-slate-800 rounded-3xl p-6 sm:p-7 text-white shadow-xs flex flex-col justify-between">
                <div>
                    <h3 class="text-lg font-bold">Sudah Menemukan Slot Waktu yang Cocok?</h3>
                    <p class="text-xs text-slate-300 mt-1.5 leading-relaxed">
                        Masuk dengan akun SSO Mahasiswa atau Dosen untuk mengajukan permohonan reservasi secara resmi, bebas bentrok jadwal, dan tercatat otomatis.
                    </p>
                </div>
                <div class="mt-5 pt-4 border-t border-white/10 flex items-center justify-between">
                    <span class="text-xs text-slate-400">Verifikasi cepat staf sarpras</span>
                    <a href="{{ route('login') }}" class="px-5 py-2.5 rounded-xl bg-white text-slate-900 text-xs font-bold hover:bg-slate-100 transition shadow-xs flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">calendar_add_on</span>
                        <span>Masuk SSO & Ajukan</span>
                    </a>
                </div>
            </div>

            {{-- Card Info: Regulasi Peminjaman & Pembatalan --}}
            <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-7 shadow-xs flex flex-col justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Tata Tertib Peminjaman Fasilitas</h3>
                    <p class="text-xs text-slate-500 mt-1.5 leading-relaxed">
                        Permohonan reservasi diajukan minimal <strong>H-2</strong> sebelum acara. Pembatalan mandiri dapat dilakukan hingga <strong>H-1</strong> sebelum slot waktu dimulai.
                    </p>
                </div>
                <div class="mt-5 pt-4 border-t border-slate-100 flex items-center justify-between">
                    <a href="{{ route('public.information', ['section' => 'syarat-ketentuan']) }}" class="text-xs font-semibold text-slate-900 hover:text-blue-900 transition flex items-center gap-1">
                        <span>Baca Tata Tertib & S&K Selengkapnya</span>
                        <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                    </a>
                </div>
            </div>
        </div>

    </div>

    {{-- Script Alpine.js Matriks Ketersediaan dengan Paginasi 10 Item --}}
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('availabilityData', () => ({
                selectedDate: '{{ date('Y-m-d') }}',
                selectedBuilding: 'semua',
                currentPage: 1,
                perPage: 10,
                timeSlots: [
                    '07:00','07:30','08:00','08:30','09:00','09:30','10:00','10:30',
                    '11:00','11:30','12:00','12:30','13:00','13:30','14:00','14:30',
                    '15:00','15:30','16:00','16:30','17:00','17:30','18:00','18:30',
                    '19:00','19:30','20:00'
                ],
                rooms: @json($facilities),
                isLoading: false,
                
                async fetchAvailability() {
                    this.isLoading = true;
                    try {
                        const response = await fetch(`/api/availability/${this.selectedDate}`);
                        if (response.ok) {
                            const data = await response.json();
                            this.rooms = this.rooms.map(room => {
                                room.occupied = data[room.id] || [];
                                return room;
                            });
                        }
                    } catch (error) {
                        console.error('Error fetching availability:', error);
                    } finally {
                        this.isLoading = false;
                    }
                },

                init() {
                    this.fetchAvailability();
                    
                    this.$watch('selectedDate', () => {
                        this.fetchAvailability();
                    });

                    this.$watch('selectedBuilding', () => {
                        this.currentPage = 1;
                    });
                },

                get filteredRooms() {
                    return this.rooms.filter(r => {
                        return this.selectedBuilding === 'semua' || r.building.includes(this.selectedBuilding);
                    });
                },

                get totalPages() {
                    return Math.max(1, Math.ceil(this.filteredRooms.length / this.perPage));
                },

                get paginatedRooms() {
                    const start = (this.currentPage - 1) * this.perPage;
                    return this.filteredRooms.slice(start, start + this.perPage);
                },

                nextPage() {
                    if (this.currentPage < this.totalPages) {
                        this.currentPage++;
                    }
                },

                prevPage() {
                    if (this.currentPage > 1) {
                        this.currentPage--;
                    }
                },

                goToPage(p) {
                    if (p >= 1 && p <= this.totalPages) {
                        this.currentPage = p;
                    }
                },

                isBooked(room, slot) {
                    return room.occupied && room.occupied.includes(slot);
                }
            }));
        });
    </script>
</x-public-layout>
