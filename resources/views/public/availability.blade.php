{{-- 
  NAMA FILE      : availability.blade.php
  FUNGSIONALITAS : Halaman Matriks Jadwal Ketersediaan 30 Menit (07:00 - 20:00 WIB)
  DESKRIPSI      : Menyajikan matriks jadwal ketersediaan seluruh fasilitas per slot 30 menit dengan pemilih tanggal, filter gedung, dan kepatuhan privasi UR01.
  CARA KERJA     : Menggunakan layout <x-public-layout active="availability">, menyajikan visualisasi slot ketersediaan interaktif dengan Alpine.js.
--}}

<x-public-layout title="Matriks Jadwal Ketersediaan Ruang" active="availability">
    <div x-data="{
        selectedDate: '{{ date('Y-m-d') }}',
        selectedBuilding: 'semua',
        timeSlots: [
            '07:00','07:30','08:00','08:30','09:00','09:30','10:00','10:30',
            '11:00','11:30','12:00','12:30','13:00','13:30','14:00','14:30',
            '15:00','15:30','16:00','16:30','17:00','17:30','18:00','18:30',
            '19:00','19:30','20:00'
        ],
        rooms: [
            {
                id: 'AUD-H01',
                name: 'Auditorium Utama B.J. Habibie',
                building: 'Gedung Rektorat (Lt. 1 & 2)',
                capacity: 450,
                occupied: ['09:00','09:30','10:00','10:30','11:00','11:30','12:00','12:30','15:30','16:00','16:30','17:00'],
                locked: false
            },
            {
                id: 'LAB-C204',
                name: 'Lab Komputasi Cloud & Jaringan',
                building: 'Gedung Lab Terpadu C (Lt. 2)',
                capacity: 45,
                occupied: ['08:00','08:30','13:00','13:30','14:00','14:30'],
                locked: false
            },
            {
                id: 'CLS-B302',
                name: 'Smart Classroom 302',
                building: 'Gedung Kuliah Bersama B (Lt. 3)',
                capacity: 60,
                occupied: ['07:30','08:00','08:30','09:00','09:30','10:00','13:30','14:00','14:30'],
                locked: false
            },
            {
                id: 'SPT-PKM01',
                name: 'Aula Serbaguna & Olahraga PKM',
                building: 'Pusat Kegiatan Mahasiswa (Lt. 1)',
                capacity: 500,
                occupied: ['13:00','13:30','14:00','14:30','15:00','15:30','16:00','16:30','17:00','17:30','18:00'],
                locked: false
            },
            {
                id: 'RPT-SENAT',
                name: 'Ruang Rapat Senat Akademik',
                building: 'Gedung Rektorat (Lt. 3)',
                capacity: 35,
                occupied: [],
                locked: true
            }
        ],
        get filteredRooms() {
            return this.rooms.filter(r => {
                return this.selectedBuilding === 'semua' || r.building.includes(this.selectedBuilding);
            });
        },
        isBooked(room, slot) {
            return room.occupied.includes(slot);
        }
    }">

        {{-- Breadcrumb & Judul --}}
        <div class="mb-6">
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-2 font-medium">
                <a href="{{ url('/') }}" class="hover:text-slate-900 transition">Beranda</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-slate-900">Matriks Jadwal Ketersediaan Ruang</span>
            </div>
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Matriks Ketersediaan Slot Waktu 30 Menit</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Pantau ketersediaan seluruh ruang operasional kampus (07:00 - 20:00 WIB) secara transparan.</p>
                </div>
                {{-- Legend Indicator --}}
                <div class="flex items-center gap-3 text-xs bg-white px-3.5 py-2 rounded-xl border border-slate-200/80 shadow-xs">
                    <span class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded bg-emerald-100 border border-emerald-400"></span>
                        <span class="text-slate-700">Tersedia</span>
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded bg-rose-100 border border-rose-400"></span>
                        <span class="text-slate-700">Terpakai</span>
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded bg-slate-200 border border-slate-400"></span>
                        <span class="text-slate-700">Dalam Perbaikan</span>
                    </span>
                </div>
            </div>
        </div>

        {{-- Filter Control Bar --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                <div class="flex items-center gap-2">
                    <label for="matrix-date" class="text-xs font-bold text-slate-600">Tanggal:</label>
                    <input type="date" id="matrix-date" x-model="selectedDate" class="text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-slate-900">
                </div>

                <div class="flex items-center gap-2">
                    <label for="matrix-building" class="text-xs font-bold text-slate-600">Gedung:</label>
                    <select id="matrix-building" x-model="selectedBuilding" class="text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-slate-900">
                        <option value="semua">Semua Gedung Kampus</option>
                        <option value="Rektorat">Gedung Rektorat</option>
                        <option value="Lab Terpadu C">Gedung Lab Terpadu C</option>
                        <option value="Kuliah Bersama B">Gedung Kuliah Bersama B</option>
                        <option value="PKM">Student Center (PKM)</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-2 text-xs text-slate-500">
                <span class="material-symbols-outlined text-[16px] text-blue-900">shield</span>
                <span>Mode Privasi UR-01: Identitas peminjam dan tujuan dirahasiakan untuk publik</span>
            </div>
        </div>

        {{-- Matriks Jadwal Table --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden mb-8">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-700">
                            <th class="py-3 px-4 font-bold uppercase tracking-wider min-w-[220px] sticky left-0 bg-slate-50 z-20 shadow-[2px_0_4px_-2px_rgba(0,0,0,0.08)]">
                                Ruang / Fasilitas
                            </th>
                            <template x-for="slot in timeSlots" :key="slot">
                                <th class="py-2.5 px-2 text-center font-semibold text-slate-600 border-l border-slate-200/60 min-w-[58px]" x-text="slot"></th>
                            </template>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="room in filteredRooms" :key="room.id">
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3 px-4 sticky left-0 bg-white z-10 shadow-[2px_0_4px_-2px_rgba(0,0,0,0.06)] border-b border-slate-100">
                                    <div class="font-bold text-slate-900 text-sm" x-text="room.name"></div>
                                    <div class="text-[11px] text-slate-500 flex items-center gap-1 mt-0.5">
                                        <span x-text="room.id" class="font-semibold text-blue-950 font-mono"></span>
                                        <span>•</span>
                                        <span x-text="room.capacity + ' Kursi'"></span>
                                    </div>
                                </td>

                                <template x-for="slot in timeSlots" :key="slot">
                                    <td class="p-1 border-l border-slate-100 text-center align-middle">
                                        <div x-show="room.locked" class="w-full h-8 rounded-lg bg-slate-200 border border-slate-300 flex items-center justify-center text-[10px] text-slate-600" title="Ruang Dalam Perbaikan">
                                            <span class="material-symbols-outlined text-[13px]">build</span>
                                        </div>

                                        <div x-show="!room.locked && isBooked(room, slot)" class="w-full h-8 rounded-lg bg-rose-50 border border-rose-200 text-rose-700 flex items-center justify-center text-[9px] font-bold" title="Slot Waktu Terpakai">
                                            <span>Terisi</span>
                                        </div>

                                        <div x-show="!room.locked && !isBooked(room, slot)" class="w-full h-8 rounded-lg bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 transition-colors text-emerald-800 flex items-center justify-center text-[9px] font-semibold cursor-pointer" title="Slot Waktu Tersedia">
                                            <span>Bebas</span>
                                        </div>
                                    </td>
                                </template>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- CTA Banner --}}
        <div class="bg-gradient-to-r from-slate-900 to-slate-800 rounded-3xl p-6 text-white shadow-md flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold">Siap Mengajukan Permohonan Reservasi?</h3>
                <p class="text-xs text-slate-300 mt-0.5">Masuk dengan akun SSO Mahasiswa/Dosen untuk memilih slot dan mengisi formulir kegiatan.</p>
            </div>
            <a href="{{ route('login') }}" class="px-5 py-2.5 rounded-xl bg-white text-slate-900 text-xs sm:text-sm font-bold hover:bg-slate-100 transition shadow-xs flex items-center gap-1.5 shrink-0">
                <span class="material-symbols-outlined text-[18px]">calendar_add_on</span>
                <span>Masuk SSO & Reservasi</span>
            </a>
        </div>

    </div>
</x-public-layout>
