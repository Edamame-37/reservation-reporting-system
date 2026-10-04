{{-- 
  NAMA FILE      : schedule-matrix.blade.php
  FUNGSIONALITAS : Matriks Jadwal 30 Menit & Peninjauan Detail Reservasi Petugas Sarpras
  DESKRIPSI      : Menampilkan kalender operasional slot 30 menit (07:00 - 20:00 WIB) untuk seluruh fasilitas
                   dengan akses peninjauan identitas pemohon, agenda kegiatan, surat izin, dan status tiket secara mendalam berdesain Monochrome Bento UI.
  CARA KERJA     : Memanfaatkan <x-petugas-layout active="schedule-matrix">, fetching data asinkron via Alpine.js ke endpoint API /api/petugas/matrix/{date},
                   serta menyajikan Bento UI cards dan modal interaktif.
--}}

<x-petugas-layout title="Matriks Jadwal 30 Menit & Peninjauan Reservasi" active="schedule-matrix">
    <div x-data="petugasScheduleMatrix({
        initialDate: '{{ $selectedDate }}',
        initialBuilding: '{{ $selectedBuilding }}',
        rooms: {{ Js::from($facilities) }}
    })" x-init="init()" class="flex flex-col gap-6">

        {{-- 1. Header Halaman Profesional --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200/80">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Matriks Jadwal 30 Menit & Peninjauan Detail</h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    Inspeksi visual ketersediaan ruangan kampus dan agenda pemohon per interval 30 menit. Klik slot terisi untuk melihat rincian pemohon.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="{{ route('petugas.reservation-management') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-xs font-medium text-slate-700 hover:bg-slate-50 transition shadow-2xs">
                    <span class="material-symbols-outlined text-[16px] text-slate-500">inbox_customize</span>
                    <span>Lembar Antrean Reservasi</span>
                </a>
            </div>
        </div>

        {{-- 2. Bento Statistics Cards (Monochrome Editorial) --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Bento 1: Slot Terjadwal --}}
            <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-2xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500">Slot Terjadwal</span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full font-medium bg-slate-100 text-slate-600">30 Menit</span>
                </div>
                <div class="text-2xl font-bold font-mono text-slate-900 mt-2" x-text="summary.booked_slots_count">0</div>
                <div class="text-[11px] text-slate-500 mt-2 pt-2 border-t border-slate-100">
                    Pada tanggal terpilih
                </div>
            </div>

            {{-- Bento 2: Ruang Terpakai --}}
            <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-2xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500">Ruang Terpakai</span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full font-medium bg-slate-100 text-slate-600">Aktif</span>
                </div>
                <div class="text-2xl font-bold font-mono text-slate-900 mt-2">
                    <span x-text="summary.active_rooms_count">0</span>
                    <span class="text-xs font-normal text-slate-400 font-sans">/ {{ $totalFacilities }} Ruang</span>
                </div>
                <div class="text-[11px] text-slate-500 mt-2 pt-2 border-t border-slate-100">
                    Terdapat agenda kegiatan
                </div>
            </div>

            {{-- Bento 3: Total Reservasi --}}
            <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-2xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500">Total Permohonan</span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full font-medium bg-slate-100 text-slate-600">Tiket</span>
                </div>
                <div class="text-2xl font-bold font-mono text-slate-900 mt-2" x-text="summary.total_reservations">0</div>
                <div class="text-[11px] text-slate-500 mt-2 pt-2 border-t border-slate-100">
                    Disetujui & Menunggu
                </div>
            </div>

            {{-- Bento 4: Mode Perbaikan --}}
            <div class="bg-white rounded-xl border border-slate-200/80 p-4 shadow-2xs flex flex-col justify-between">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium text-slate-500">Mode Perbaikan</span>
                    <span class="text-[10px] px-2 py-0.5 rounded-full font-medium bg-slate-100 text-slate-600">Kunci</span>
                </div>
                <div class="text-2xl font-bold font-mono text-slate-900 mt-2">{{ $maintenanceCount }}</div>
                <div class="text-[11px] text-slate-500 mt-2 pt-2 border-t border-slate-100">
                    Fasilitas dinonaktifkan
                </div>
            </div>
        </div>

        {{-- 3. Bento Filter & Toolbar --}}
        <div class="bg-white rounded-xl border border-slate-200/80 p-3 shadow-2xs flex flex-col lg:flex-row items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-2.5 w-full lg:w-auto">
                {{-- Pemilih Tanggal --}}
                <div class="flex items-center gap-1.5">
                    <label for="matrix-date" class="text-xs font-medium text-slate-600">Tanggal:</label>
                    <input type="date" id="matrix-date" x-model="selectedDate" @change="fetchMatrix()" class="text-xs bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1 text-slate-800 font-medium focus:outline-none focus:ring-1 focus:ring-slate-900 focus:bg-white transition cursor-pointer">
                </div>

                {{-- Pintasan Tanggal --}}
                <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-lg text-xs font-medium">
                    <button type="button" @click="setDate('{{ now()->format('Y-m-d') }}')" :class="selectedDate === '{{ now()->format('Y-m-d') }}' ? 'bg-white text-slate-900 font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900'" class="px-2.5 py-1 rounded-md transition text-[11px]">
                        Hari Ini
                    </button>
                    <button type="button" @click="setDate('{{ now()->addDay()->format('Y-m-d') }}')" :class="selectedDate === '{{ now()->addDay()->format('Y-m-d') }}' ? 'bg-white text-slate-900 font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900'" class="px-2.5 py-1 rounded-md transition text-[11px]">
                        Besok
                    </button>
                    <button type="button" @click="setDate('{{ now()->addDays(2)->format('Y-m-d') }}')" :class="selectedDate === '{{ now()->addDays(2)->format('Y-m-d') }}' ? 'bg-white text-slate-900 font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900'" class="px-2.5 py-1 rounded-md transition text-[11px]">
                        Lusa
                    </button>
                </div>

                {{-- Filter Gedung --}}
                <div class="flex items-center gap-1.5">
                    <label for="matrix-building" class="text-xs font-medium text-slate-600">Gedung:</label>
                    <select id="matrix-building" x-model="selectedBuilding" class="text-xs bg-slate-50 border border-slate-200 rounded-lg px-2.5 py-1 text-slate-800 font-medium focus:outline-none focus:ring-1 focus:ring-slate-900 focus:bg-white transition">
                        <option value="semua">Semua Gedung</option>
                        @foreach($buildings as $b)
                            <option value="{{ $b }}">{{ $b }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Pencarian Ruang --}}
                <div class="relative w-full sm:w-44">
                    <span class="material-symbols-outlined absolute left-2.5 top-1.5 text-slate-400 text-[16px]">search</span>
                    <input type="text" x-model="searchRoom" placeholder="Cari nama ruang..." class="w-full pl-8 pr-2.5 py-1 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-slate-900 focus:bg-white transition">
                </div>
            </div>

            {{-- Legend Status --}}
            <div class="flex flex-wrap items-center gap-3 text-xs bg-slate-50 px-3 py-1.5 rounded-lg border border-slate-200/60">
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-xs border border-dashed border-slate-300 bg-white"></span>
                    <span class="text-slate-600 text-[11px]">Bebas</span>
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-xs bg-slate-900"></span>
                    <span class="text-slate-600 text-[11px]">Disetujui</span>
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-xs bg-slate-200 border border-slate-300"></span>
                    <span class="text-slate-600 text-[11px]">Menunggu</span>
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-2.5 h-2.5 rounded-xs bg-slate-100 border border-slate-200"></span>
                    <span class="text-slate-600 text-[11px]">Perbaikan</span>
                </span>
            </div>
        </div>

        {{-- 4. Matriks Jadwal 30m Bento Table --}}
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-2xs overflow-hidden">
            <div class="p-3 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between text-xs">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[16px] text-slate-500">info</span>
                    <span class="text-slate-600">
                        Klik pada slot <strong class="text-slate-900">Disetujui</strong> atau <strong class="text-slate-700">Menunggu</strong> untuk membuka detail pemohon, agenda, dan surat izin.
                    </span>
                </div>
                <div x-show="loading" class="flex items-center gap-1.5 text-slate-700 font-medium">
                    <span class="inline-block animate-spin material-symbols-outlined text-[15px]">sync</span>
                    <span>Memperbarui data...</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-700">
                            <th class="py-2.5 px-4 font-semibold text-xs min-w-[220px] sticky left-0 bg-slate-50 z-20 shadow-[2px_0_4px_-2px_rgba(0,0,0,0.06)]">
                                Ruang / Fasilitas
                            </th>
                            <template x-for="slot in timeSlots" :key="slot">
                                <th class="py-2 px-1 text-center font-mono font-medium text-slate-500 border-l border-slate-200/60 min-w-[54px] text-[10px]" x-text="slot"></th>
                            </template>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="room in filteredRooms" :key="room.id">
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                {{-- Kolom Ruang Sticky --}}
                                <td class="py-2.5 px-4 sticky left-0 bg-white z-10 shadow-[2px_0_4px_-2px_rgba(0,0,0,0.06)] border-b border-slate-100">
                                    <div class="flex items-start justify-between gap-1">
                                        <div>
                                            <div class="font-semibold text-slate-900 text-xs" x-text="room.name"></div>
                                            <div class="text-[11px] text-slate-500 flex items-center gap-1 mt-0.5">
                                                <span x-text="room.building"></span>
                                                <template x-if="room.floor_location">
                                                    <span x-text="'(Lt. ' + room.floor_location + ')'"></span>
                                                </template>
                                                <span>&bull;</span>
                                                <span x-text="(room.capacity || 0) + ' Kursi'"></span>
                                            </div>
                                        </div>
                                        <template x-if="room.status === 'dalam perbaikan'">
                                            <span class="px-1.5 py-0.2 rounded text-[10px] font-medium bg-slate-100 text-slate-700 shrink-0 border border-slate-200">
                                                Perbaikan
                                            </span>
                                        </template>
                                    </div>
                                </td>

                                {{-- Sel Slot 30 Menit --}}
                                <template x-for="slot in timeSlots" :key="slot">
                                    <td class="p-0.5 border-l border-slate-100 text-center align-middle">
                                        {{-- 1. Kondisi Ruang Dalam Perbaikan --}}
                                        <template x-if="room.status === 'dalam perbaikan'">
                                            <div class="w-full h-7 rounded bg-slate-100 border border-slate-200 flex items-center justify-center text-[10px] text-slate-400" title="Ruang Dalam Perbaikan (Terkunci)">
                                                <span class="material-symbols-outlined text-[13px]">lock</span>
                                            </div>
                                        </template>

                                        {{-- 2. Kondisi Ruang Normal --}}
                                        <template x-if="room.status !== 'dalam perbaikan'">
                                            <div>
                                                {{-- 2a. Slot Terisi Disetujui (Approved) --}}
                                                <template x-if="getSlot(room.id, slot) && getSlot(room.id, slot).status === 'approved'">
                                                    <button type="button" @click="openDetail(getSlot(room.id, slot))" class="w-full h-7 rounded bg-slate-900 text-white hover:bg-slate-800 transition flex flex-col items-center justify-center px-0.5 cursor-pointer shadow-2xs group" :title="'Disetujui: ' + getSlot(room.id, slot).applicant_name + ' (' + getSlot(room.id, slot).purpose + ')'">
                                                        <span class="text-[9px] font-medium tracking-tight block truncate max-w-full leading-tight" x-text="getSlot(room.id, slot).applicant_name.split(' ')[0]"></span>
                                                        <span class="text-[8px] text-slate-300 font-mono leading-tight" x-text="getSlot(room.id, slot).ticket_code.replace('TKT-', '')"></span>
                                                    </button>
                                                </template>

                                                {{-- 2b. Slot Menunggu Persetujuan (Pending) --}}
                                                <template x-if="getSlot(room.id, slot) && getSlot(room.id, slot).status === 'pending'">
                                                    <button type="button" @click="openDetail(getSlot(room.id, slot))" class="w-full h-7 rounded bg-slate-100 border border-slate-300 hover:bg-slate-200 transition text-slate-800 flex flex-col items-center justify-center px-0.5 cursor-pointer shadow-2xs group" :title="'Menunggu: ' + getSlot(room.id, slot).applicant_name">
                                                        <span class="text-[9px] font-medium tracking-tight block truncate max-w-full leading-tight" x-text="getSlot(room.id, slot).applicant_name.split(' ')[0]"></span>
                                                        <span class="text-[8px] text-slate-500 font-mono leading-tight">Menunggu</span>
                                                    </button>
                                                </template>

                                                {{-- 2c. Slot Bebas/Tersedia --}}
                                                <template x-if="!getSlot(room.id, slot)">
                                                    <div class="w-full h-7 rounded bg-slate-50/60 border border-slate-100 hover:bg-slate-100/70 transition text-slate-400 flex items-center justify-center text-[9px]" title="Slot Tersedia">
                                                        <span>-</span>
                                                    </div>
                                                </template>
                                            </div>
                                        </template>
                                    </td>
                                </template>
                            </tr>
                        </template>

                        <template x-if="filteredRooms.length === 0">
                            <tr>
                                <td colspan="28" class="py-12 px-4 text-center text-xs text-slate-400">
                                    Tidak ada ruang yang sesuai dengan filter atau kata kunci pencarian.
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 5. Detail Modal Peninjauan Lengkap Reservasi --}}
        <div x-show="showDetailModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="showDetailModal = false" class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-xl border border-slate-200 relative">
                <button type="button" @click="showDetailModal = false" class="absolute top-5 right-5 p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>

                <template x-if="selectedDetail">
                    <div class="flex flex-col gap-5">
                        {{-- Header Modal --}}
                        <div class="pr-8">
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-base font-bold text-slate-900" x-text="selectedDetail.ticket_code"></span>
                                <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-700" x-text="selectedDetail.status === 'approved' ? 'Disetujui' : 'Menunggu Verifikasi'"></span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">Peninjauan permohonan reservasi fasilitas kampus</p>
                        </div>

                        {{-- Rincian Bento Grid --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Kolom 1: Identitas Pemohon --}}
                            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80">
                                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-2">Identitas Pemohon</div>
                                <div class="font-semibold text-slate-900 text-sm" x-text="selectedDetail.applicant_name"></div>
                                <div class="text-xs text-slate-600 mt-1.5 space-y-1">
                                    <p><span class="text-slate-500">NIM / NIP:</span> <span class="font-mono text-slate-800" x-text="selectedDetail.applicant_identity"></span></p>
                                    <p><span class="text-slate-500">Prodi / Unit:</span> <span class="text-slate-800" x-text="selectedDetail.applicant_department"></span></p>
                                    <p><span class="text-slate-500">Email:</span> <span class="text-slate-800" x-text="selectedDetail.applicant_email"></span></p>
                                </div>
                            </div>

                            {{-- Kolom 2: Fasilitas & Waktu --}}
                            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80">
                                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-2">Fasilitas & Waktu</div>
                                <div class="font-semibold text-slate-900 text-sm" x-text="selectedDetail.facility_name"></div>
                                <div class="text-xs text-slate-600 mt-1.5 space-y-1">
                                    <p><span class="text-slate-500">Lokasi:</span> <span class="text-slate-800" x-text="selectedDetail.facility_building + (selectedDetail.facility_floor ? ' (Lt. ' + selectedDetail.facility_floor + ')' : '')"></span></p>
                                    <p><span class="text-slate-500">Kapasitas:</span> <span class="text-slate-800" x-text="selectedDetail.facility_capacity + ' Kursi'"></span></p>
                                    <p><span class="text-slate-500">Jadwal:</span> <span class="font-mono font-medium text-slate-900" x-text="selectedDetail.reservation_date + ', ' + selectedDetail.start_time + ' - ' + selectedDetail.end_time + ' WIB'"></span> (<span x-text="selectedDetail.total_slots + ' slot'"></span>)</p>
                                </div>
                            </div>

                            {{-- Kolom 3: Tujuan Kegiatan & Berkas --}}
                            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 md:col-span-2">
                                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Tujuan Kegiatan</div>
                                <p class="text-xs text-slate-800 leading-relaxed bg-white p-3 rounded-lg border border-slate-200/80" x-text="selectedDetail.purpose || 'Tidak ada keterangan khusus.'"></p>
                                
                                <div class="mt-3 flex flex-wrap items-center justify-between gap-3 text-xs">
                                    <div class="text-slate-600">
                                        Estimasi Peserta: <strong class="text-slate-900" x-text="(selectedDetail.participants_count || 1) + ' Orang'"></strong>
                                    </div>
                                    <div>
                                        <template x-if="selectedDetail.permit_letter_path">
                                            <a :href="selectedDetail.permit_letter_path" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-900 text-white font-medium hover:bg-slate-800 transition shadow-2xs">
                                                <span class="material-symbols-outlined text-[14px]">attachment</span>
                                                <span>Buka Berkas Surat Izin</span>
                                            </a>
                                        </template>
                                        <template x-if="!selectedDetail.permit_letter_path">
                                            <span class="text-slate-400 italic">Tidak melampirkan berkas fisik</span>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- Kolom 4: Log Verifikasi --}}
                            <template x-if="selectedDetail.reviewed_by_name || selectedDetail.reviewed_at">
                                <div class="p-3 rounded-xl bg-slate-100 border border-slate-200/80 md:col-span-2 text-xs flex items-center justify-between text-slate-600">
                                    <span>Diverifikasi oleh: <strong class="text-slate-900" x-text="selectedDetail.reviewed_by_name"></strong></span>
                                    <span x-text="'Waktu: ' + selectedDetail.reviewed_at"></span>
                                </div>
                            </template>
                        </div>

                        {{-- Footer Aksi Modal --}}
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-slate-100">
                            <div class="flex items-center gap-3">
                                <button type="button" @click="showDetailModal = false" class="px-3.5 py-1.5 rounded-lg border border-slate-200 text-slate-700 text-xs font-medium hover:bg-slate-50 transition cursor-pointer">
                                    Tutup
                                </button>
                                <a :href="'{{ url('/petugas/reservation-management') }}?status=' + selectedDetail.status + '&search=' + selectedDetail.ticket_code" class="text-xs font-medium text-slate-500 hover:text-slate-900">
                                    Buka di Lembar Antrean &rarr;
                                </a>
                            </div>

                            <div class="flex items-center gap-2">
                                <template x-if="selectedDetail.status === 'pending'">
                                    <form :action="'{{ url('/petugas/reservations') }}/' + selectedDetail.id + '/approve'" method="POST" class="inline">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" onclick="return confirm('Konfirmasi persetujuan reservasi ini? Slot jadwal akan resmi dialokasikan.')" class="px-4 py-1.5 rounded-lg bg-slate-900 text-white text-xs font-medium hover:bg-slate-800 transition shadow-2xs cursor-pointer">
                                            Setujui Permohonan
                                        </button>
                                    </form>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

    </div>

    {{-- Script Alpine Data --}}
    <script>
        function petugasScheduleMatrix(config) {
            return {
                selectedDate: config.initialDate,
                selectedBuilding: config.initialBuilding,
                searchRoom: '',
                rooms: config.rooms || [],
                matrix: {},
                summary: {
                    booked_slots_count: 0,
                    active_rooms_count: 0,
                    total_reservations: 0
                },
                loading: false,
                showDetailModal: false,
                selectedDetail: null,
                timeSlots: [
                    '07:00', '07:30', '08:00', '08:30', '09:00', '09:30',
                    '10:00', '10:30', '11:00', '11:30', '12:00', '12:30',
                    '13:00', '13:30', '14:00', '14:30', '15:00', '15:30',
                    '16:00', '16:30', '17:00', '17:30', '18:00', '18:30',
                    '19:00', '19:30', '20:00'
                ],

                init() {
                    this.fetchMatrix();
                },

                setDate(dateStr) {
                    this.selectedDate = dateStr;
                    this.fetchMatrix();
                },

                async fetchMatrix() {
                    this.loading = true;
                    try {
                        const response = await fetch(`/api/petugas/matrix/${this.selectedDate}`);
                        if (!response.ok) throw new Error('Gagal mengambil data matriks');
                        const data = await response.json();
                        this.matrix = data.matrix || {};
                        this.summary = data.summary || { booked_slots_count: 0, active_rooms_count: 0, total_reservations: 0 };
                    } catch (e) {
                        console.error('Error fetching petugas matrix:', e);
                    } finally {
                        this.loading = false;
                    }
                },

                get filteredRooms() {
                    return this.rooms.filter(room => {
                        const matchBuilding = this.selectedBuilding === 'semua' || room.building === this.selectedBuilding;
                        const matchSearch = !this.searchRoom || room.name.toLowerCase().includes(this.searchRoom.toLowerCase());
                        return matchBuilding && matchSearch;
                    });
                },

                getSlot(facilityId, slotTime) {
                    if (this.matrix && this.matrix[facilityId] && this.matrix[facilityId][slotTime]) {
                        return this.matrix[facilityId][slotTime];
                    }
                    return null;
                },

                openDetail(slotDetail) {
                    if (!slotDetail) return;
                    this.selectedDetail = slotDetail;
                    this.showDetailModal = true;
                }
            };
        }
    </script>
</x-petugas-layout>
