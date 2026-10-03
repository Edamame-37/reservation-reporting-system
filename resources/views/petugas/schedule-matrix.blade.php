{{-- 
  NAMA FILE      : schedule-matrix.blade.php
  FUNGSIONALITAS : Matriks Jadwal 30 Menit & Peninjauan Detail Reservasi Petugas Sarpras
  DESKRIPSI      : Menampilkan kalender operasional slot 30 menit (07:00 - 20:00 WIB) untuk seluruh fasilitas dengan akses peninjauan identitas pemohon, agenda kegiatan, surat izin, dan status tiket secara mendalam berdesain Bento UI.
  CARA KERJA     : Memanfaatkan <x-petugas-layout active="schedule-matrix">, fetching data asinkron via Alpine.js ke endpoint API /api/petugas/matrix/{date}, serta menyajikan Bento UI cards dan modal interaktif.
--}}

<x-petugas-layout title="Matriks Jadwal 30 Menit & Peninjauan Reservasi" active="schedule-matrix">
    <div x-data="petugasScheduleMatrix({
        initialDate: '{{ $selectedDate }}',
        initialBuilding: '{{ $selectedBuilding }}',
        rooms: {{ Js::from($facilities) }}
    })" x-init="init()" class="flex flex-col gap-6">

        {{-- 1. Breadcrumb & Header --}}
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-2 font-medium">
                <a href="{{ url('/petugas/dashboard') }}" class="hover:text-slate-900 transition">Dasbor Operasional</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-slate-900">Matriks Jadwal 30m</span>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2.5">
                        <span class="p-2 rounded-xl bg-slate-900 text-white shadow-xs">
                            <span class="material-symbols-outlined text-[22px]">calendar_month</span>
                        </span>
                        <span>Matriks Jadwal 30m & Peninjauan Detail</span>
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">
                        Tinjau penggunaan ruang kampus per interval 30 menit. Klik slot terisi untuk melihat rincian pemohon, NIM, surat izin, dan tujuan kegiatan.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('petugas.reservation-management') }}" class="px-3.5 py-2 rounded-xl bg-white border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50 transition shadow-xs flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">receipt_long</span>
                        <span>Lembar Antrean Reservasi</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- 2. Bento Statistics Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Bento 1: Slot Terisi Hari Ini --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs relative overflow-hidden flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Slot Terjadwal</span>
                    <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-800 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[18px]">event_seat</span>
                    </span>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight" x-text="summary.booked_slots_count">0</span>
                    <span class="text-xs text-slate-500 font-medium">Slot (30m)</span>
                </div>
                <div class="mt-2 text-[11px] text-slate-500 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-blue-700"></span>
                    <span>Pada tanggal terpilih</span>
                </div>
            </div>

            {{-- Bento 2: Ruang Digunakan --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs relative overflow-hidden flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Ruang Terpakai</span>
                    <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-800 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[18px]">meeting_room</span>
                    </span>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-extrabold text-emerald-700 tracking-tight" x-text="summary.active_rooms_count">0</span>
                    <span class="text-xs text-slate-500 font-medium">/ {{ $totalFacilities }} Ruang Aktif</span>
                </div>
                <div class="mt-2 text-[11px] text-emerald-700 font-medium flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">check_circle</span>
                    <span>Terdapat agenda kegiatan</span>
                </div>
            </div>

            {{-- Bento 3: Total Reservasi --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs relative overflow-hidden flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Total Reservasi</span>
                    <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-800 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[18px]">verified</span>
                    </span>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-extrabold text-indigo-700 tracking-tight" x-text="summary.total_reservations">0</span>
                    <span class="text-xs text-slate-500 font-medium">Permohonan</span>
                </div>
                <div class="mt-2 text-[11px] text-slate-500 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-indigo-600"></span>
                    <span>Approved & Pending</span>
                </div>
            </div>

            {{-- Bento 4: Mode Perbaikan --}}
            <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs relative overflow-hidden flex flex-col justify-between">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[11px] font-bold text-slate-500 uppercase tracking-wider">Pemeliharaan</span>
                    <span class="w-8 h-8 rounded-xl bg-amber-50 text-amber-800 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[18px]">build</span>
                    </span>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-extrabold text-amber-700 tracking-tight">{{ $maintenanceCount }}</span>
                    <span class="text-xs text-slate-500 font-medium">Fasilitas</span>
                </div>
                <div class="mt-2 text-[11px] text-amber-700 font-medium flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">lock</span>
                    <span>Slot terkunci sistem</span>
                </div>
            </div>
        </div>

        {{-- 3. Bento Filter & Toolbar --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs flex flex-col lg:flex-row items-center justify-between gap-4">
            <div class="flex flex-wrap items-center gap-3 w-full lg:w-auto">
                {{-- Pemilih Tanggal --}}
                <div class="flex items-center gap-2">
                    <label for="matrix-date" class="text-xs font-bold text-slate-700 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px] text-slate-500">calendar_today</span>
                        <span>Tanggal:</span>
                    </label>
                    <input type="date" id="matrix-date" x-model="selectedDate" @change="fetchMatrix()" class="text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 font-semibold focus:outline-none focus:ring-2 focus:ring-slate-900 focus:bg-white transition cursor-pointer">
                </div>

                {{-- Pintasan Tanggal --}}
                <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl text-xs font-medium">
                    <button type="button" @click="setDate('{{ now()->format('Y-m-d') }}')" :class="selectedDate === '{{ now()->format('Y-m-d') }}' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'" class="px-2.5 py-1 rounded-lg transition text-[11px]">
                        Hari Ini
                    </button>
                    <button type="button" @click="setDate('{{ now()->addDay()->format('Y-m-d') }}')" :class="selectedDate === '{{ now()->addDay()->format('Y-m-d') }}' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'" class="px-2.5 py-1 rounded-lg transition text-[11px]">
                        Besok
                    </button>
                    <button type="button" @click="setDate('{{ now()->addDays(2)->format('Y-m-d') }}')" :class="selectedDate === '{{ now()->addDays(2)->format('Y-m-d') }}' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'" class="px-2.5 py-1 rounded-lg transition text-[11px]">
                        Lusa
                    </button>
                </div>

                {{-- Filter Gedung --}}
                <div class="flex items-center gap-2">
                    <label for="matrix-building" class="text-xs font-bold text-slate-700 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px] text-slate-500">apartment</span>
                        <span>Gedung:</span>
                    </label>
                    <select id="matrix-building" x-model="selectedBuilding" class="text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-800 font-medium focus:outline-none focus:ring-2 focus:ring-slate-900 focus:bg-white transition">
                        <option value="semua">Seluruh Gedung Kampus</option>
                        @foreach($buildings as $b)
                            <option value="{{ $b }}">{{ $b }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Pencarian Ruang --}}
                <div class="relative w-full sm:w-48">
                    <span class="material-symbols-outlined absolute left-2.5 top-2 text-slate-400 text-[16px]">search</span>
                    <input type="text" x-model="searchRoom" placeholder="Cari nama ruang..." class="w-full pl-8 pr-3 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-slate-900 focus:bg-white transition">
                </div>
            </div>

            {{-- Legend Status --}}
            <div class="flex flex-wrap items-center gap-3 text-xs bg-slate-50 px-3.5 py-2 rounded-xl border border-slate-200/60">
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded bg-emerald-100 border border-emerald-400"></span>
                    <span class="text-slate-700 font-medium">Bebas</span>
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded bg-blue-100 border border-blue-500"></span>
                    <span class="text-slate-700 font-medium">Disetujui</span>
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded bg-amber-100 border border-amber-400"></span>
                    <span class="text-slate-700 font-medium">Menunggu</span>
                </span>
                <span class="flex items-center gap-1.5">
                    <span class="w-3 h-3 rounded bg-slate-300 border border-slate-400"></span>
                    <span class="text-slate-700 font-medium">Perbaikan</span>
                </span>
            </div>
        </div>

        {{-- 4. Matriks Jadwal 30m Bento Table --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="p-3 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between text-xs">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[16px] text-slate-500">info</span>
                    <span class="text-slate-600 font-medium">
                        Klik pada slot <strong class="text-blue-900">Disetujui</strong> atau <strong class="text-amber-800">Menunggu</strong> untuk membuka kartu profil pemohon, kontak, dan surat izin resmi.
                    </span>
                </div>
                <div x-show="loading" class="flex items-center gap-1.5 text-blue-900 font-semibold">
                    <span class="inline-block animate-spin material-symbols-outlined text-[16px]">sync</span>
                    <span>Memperbarui matriks...</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-200 text-slate-700">
                            <th class="py-3 px-4 font-bold uppercase tracking-wider min-w-[240px] sticky left-0 bg-slate-50 z-20 shadow-[2px_0_4px_-2px_rgba(0,0,0,0.08)]">
                                Ruang / Fasilitas
                            </th>
                            <template x-for="slot in timeSlots" :key="slot">
                                <th class="py-2.5 px-1.5 text-center font-semibold text-slate-600 border-l border-slate-200/60 min-w-[56px] text-[11px]" x-text="slot"></th>
                            </template>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="room in filteredRooms" :key="room.id">
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                {{-- Kolom Ruang Sticky --}}
                                <td class="py-3 px-4 sticky left-0 bg-white z-10 shadow-[2px_0_4px_-2px_rgba(0,0,0,0.06)] border-b border-slate-100">
                                    <div class="flex items-start justify-between gap-1">
                                        <div>
                                            <div class="font-bold text-slate-900 text-xs sm:text-sm hover:text-blue-950 transition" x-text="room.name"></div>
                                            <div class="text-[11px] text-slate-500 flex items-center gap-1 mt-0.5">
                                                <span x-text="room.building" class="font-medium text-slate-700"></span>
                                                <template x-if="room.floor_location">
                                                    <span x-text="'(Lt. ' + room.floor_location + ')'"></span>
                                                </template>
                                                <span>•</span>
                                                <span x-text="(room.capacity || 0) + ' Kursi'"></span>
                                            </div>
                                        </div>
                                        <template x-if="room.status === 'dalam perbaikan'">
                                            <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-amber-100 text-amber-800 shrink-0" title="Mode Perbaikan">
                                                Perbaikan
                                            </span>
                                        </template>
                                    </div>
                                </td>

                                {{-- Sel Slot 30 Menit --}}
                                <template x-for="slot in timeSlots" :key="slot">
                                    <td class="p-1 border-l border-slate-100 text-center align-middle">
                                        {{-- 1. Kondisi Ruang Dalam Perbaikan --}}
                                        <template x-if="room.status === 'dalam perbaikan'">
                                            <div class="w-full h-8 rounded-lg bg-slate-200 border border-slate-300 flex items-center justify-center text-[10px] text-slate-600" title="Ruang Dalam Perbaikan (Terkunci)">
                                                <span class="material-symbols-outlined text-[13px]">build</span>
                                            </div>
                                        </template>

                                        {{-- 2. Kondisi Ruang Normal --}}
                                        <template x-if="room.status !== 'dalam perbaikan'">
                                            <div>
                                                {{-- 2a. Slot Terisi Disetujui (Approved) --}}
                                                <template x-if="getSlot(room.id, slot) && getSlot(room.id, slot).status === 'approved'">
                                                    <button type="button" @click="openDetail(getSlot(room.id, slot))" class="w-full h-8 rounded-lg bg-blue-50 border border-blue-200 hover:bg-blue-100 hover:border-blue-400 transition text-blue-900 flex flex-col items-center justify-center px-0.5 cursor-pointer shadow-2xs group" :title="'Disetujui: ' + getSlot(room.id, slot).applicant_name + ' (' + getSlot(room.id, slot).purpose + ')'">
                                                        <span class="text-[9px] font-bold tracking-tight block truncate max-w-full" x-text="getSlot(room.id, slot).applicant_name.split(' ')[0]"></span>
                                                        <span class="text-[8px] text-blue-700 font-mono" x-text="getSlot(room.id, slot).ticket_code.replace('TKT-', '')"></span>
                                                    </button>
                                                </template>

                                                {{-- 2b. Slot Menunggu Persetujuan (Pending) --}}
                                                <template x-if="getSlot(room.id, slot) && getSlot(room.id, slot).status === 'pending'">
                                                    <button type="button" @click="openDetail(getSlot(room.id, slot))" class="w-full h-8 rounded-lg bg-amber-50 border border-amber-300 hover:bg-amber-100 transition text-amber-900 flex flex-col items-center justify-center px-0.5 cursor-pointer shadow-2xs group" :title="'Menunggu Persetujuan: ' + getSlot(room.id, slot).applicant_name">
                                                        <span class="text-[9px] font-bold tracking-tight block truncate max-w-full" x-text="getSlot(room.id, slot).applicant_name.split(' ')[0]"></span>
                                                        <span class="text-[8px] text-amber-700 font-mono">Antrean</span>
                                                    </button>
                                                </template>

                                                {{-- 2c. Slot Bebas/Tersedia --}}
                                                <template x-if="!getSlot(room.id, slot)">
                                                    <div class="w-full h-8 rounded-lg bg-emerald-50/70 border border-emerald-100 hover:bg-emerald-100/70 transition text-emerald-700 flex items-center justify-center text-[9px] font-medium" title="Slot Waktu Bebas / Tersedia">
                                                        <span>Bebas</span>
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
                                <td colspan="28" class="py-12 px-4 text-center">
                                    <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                        <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-500 flex items-center justify-center mb-3">
                                            <span class="material-symbols-outlined text-[26px]">search_off</span>
                                        </div>
                                        <h4 class="text-sm font-bold text-slate-900">Ruangan Tidak Ditemukan</h4>
                                        <p class="text-xs text-slate-500 mt-1">
                                            Tidak ada ruang yang sesuai dengan filter gedung atau kata kunci pencarian.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 5. Bento Detail Modal: Peninjauan Lengkap Reservasi --}}
        <div x-show="showDetailModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="showDetailModal = false" class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl border border-slate-200 relative animate-in fade-in zoom-in-95 duration-200">
                <button type="button" @click="showDetailModal = false" class="absolute top-5 right-5 p-1 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition">
                    <span class="material-symbols-outlined">close</span>
                </button>

                <template x-if="selectedDetail">
                    <div class="flex flex-col gap-5">
                        {{-- Header Modal Bento --}}
                        <div class="flex items-center justify-between pr-8">
                            <div class="flex items-center gap-3">
                                <span class="p-2.5 rounded-2xl bg-blue-50 text-blue-900">
                                    <span class="material-symbols-outlined text-[24px]">verified</span>
                                </span>
                                <div>
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-sm font-extrabold text-slate-900" x-text="selectedDetail.ticket_code"></span>
                                        <span :class="selectedDetail.status === 'approved' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'" class="text-[10px] px-2 py-0.5 rounded-full font-bold uppercase" x-text="selectedDetail.status === 'approved' ? 'Disetujui' : 'Menunggu'"></span>
                                    </div>
                                    <p class="text-xs text-slate-500 mt-0.5">Peninjauan detail operasional pemesanan fasilitas sarpras</p>
                                </div>
                            </div>
                        </div>

                        {{-- Bento Grid Modal --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                            {{-- Bento Card 1: Identitas Pemohon --}}
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 flex flex-col justify-between">
                                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[16px] text-blue-900">person</span>
                                    <span>Identitas Pemohon</span>
                                </div>
                                <div class="font-bold text-slate-900 text-sm" x-text="selectedDetail.applicant_name"></div>
                                <div class="text-xs text-slate-600 mt-1 space-y-0.5">
                                    <p><strong class="text-slate-700">NIM / NIP:</strong> <span class="font-mono" x-text="selectedDetail.applicant_identity"></span></p>
                                    <p><strong class="text-slate-700">Unit / Prodi:</strong> <span x-text="selectedDetail.applicant_department"></span></p>
                                    <p><strong class="text-slate-700">Email:</strong> <span x-text="selectedDetail.applicant_email"></span></p>
                                </div>
                                <div class="mt-2.5">
                                    <span class="inline-block px-2 py-0.5 rounded-md bg-blue-100 text-blue-900 text-[10px] font-bold uppercase" x-text="selectedDetail.applicant_role"></span>
                                </div>
                            </div>

                            {{-- Bento Card 2: Fasilitas & Waktu Pemakaian --}}
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 flex flex-col justify-between">
                                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[16px] text-emerald-800">domain</span>
                                    <span>Fasilitas & Jadwal</span>
                                </div>
                                <div class="font-bold text-slate-900 text-sm" x-text="selectedDetail.facility_name"></div>
                                <div class="text-xs text-slate-600 mt-1 space-y-0.5">
                                    <p><strong class="text-slate-700">Lokasi:</strong> <span x-text="selectedDetail.facility_building + (selectedDetail.facility_floor ? ' (Lt. ' + selectedDetail.facility_floor + ')' : '')"></span></p>
                                    <p><strong class="text-slate-700">Tanggal:</strong> <span x-text="selectedDetail.reservation_date"></span></p>
                                    <p><strong class="text-slate-700">Waktu:</strong> <span class="font-mono font-bold text-slate-900" x-text="selectedDetail.start_time + ' - ' + selectedDetail.end_time + ' WIB'"></span> (<span x-text="selectedDetail.total_slots + ' slot'"></span>)</p>
                                </div>
                                <div class="mt-2.5 text-[11px] text-slate-500">
                                    <span>Kapasitas: <strong class="text-slate-800" x-text="selectedDetail.facility_capacity + ' Kursi'"></strong></span>
                                </div>
                            </div>

                            {{-- Bento Card 3: Keperluan & Agenda Kegiatan (Full Width) --}}
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 md:col-span-2">
                                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[16px] text-amber-800">event_note</span>
                                    <span>Agenda Kegiatan & Berkas Surat Izin</span>
                                </div>
                                <p class="text-xs text-slate-800 leading-relaxed font-medium bg-white p-3 rounded-xl border border-slate-200" x-text="selectedDetail.purpose || 'Tidak ada keterangan khusus.'"></p>
                                
                                <div class="mt-3 flex flex-wrap items-center justify-between gap-3 text-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="text-slate-500">Estimasi Peserta:</span>
                                        <strong class="text-slate-900" x-text="(selectedDetail.participants_count || 1) + ' Orang'"></strong>
                                    </div>

                                    <div>
                                        <template x-if="selectedDetail.permit_letter_path">
                                            <a :href="selectedDetail.permit_letter_path" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 text-blue-900 border border-blue-200 font-semibold hover:bg-blue-100 transition shadow-2xs">
                                                <span class="material-symbols-outlined text-[15px]">attachment</span>
                                                <span>Buka Berkas Surat Izin</span>
                                            </a>
                                        </template>
                                        <template x-if="!selectedDetail.permit_letter_path">
                                            <span class="text-slate-400 text-xs italic">Tidak melampirkan berkas fisik</span>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- Bento Card 4: Audit & Peninjauan Petugas --}}
                            <template x-if="selectedDetail.reviewed_by_name || selectedDetail.reviewed_at">
                                <div class="p-3.5 rounded-2xl bg-blue-50/50 border border-blue-100 md:col-span-2 text-xs flex items-center justify-between">
                                    <div class="flex items-center gap-2 text-slate-700">
                                        <span class="material-symbols-outlined text-[18px] text-blue-900">verified_user</span>
                                        <span>Diverifikasi oleh: <strong class="text-slate-900" x-text="selectedDetail.reviewed_by_name"></strong></span>
                                    </div>
                                    <div class="text-[11px] text-slate-500" x-text="'Waktu: ' + selectedDetail.reviewed_at"></div>
                                </div>
                            </template>
                        </div>

                        {{-- Footer Aksi --}}
                        <div class="flex items-center justify-between pt-2 border-t border-slate-100">
                            <a :href="'{{ url('/petugas/reservation-management') }}?status=semua&search=' + selectedDetail.ticket_code" class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-900 hover:text-blue-950 transition">
                                <span class="material-symbols-outlined text-[16px]">open_in_new</span>
                                <span>Lihat di Antrean Reservasi</span>
                            </a>
                            <button type="button" @click="showDetailModal = false" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition shadow-xs">
                                Tutup Tinjauan
                            </button>
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
