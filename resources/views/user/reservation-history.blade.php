{{-- 
  NAMA FILE      : reservation-history.blade.php
  FUNGSIONALITAS : Halaman Terpadu Reservasi & Riwayat Peminjaman Ruang Kampus (USR-01, USR-02, USR-03)
  DESKRIPSI      : Antarmuka Bento UI modern yang mengintegrasikan riwayat peminjaman fasilitas, metrik status, pencarian dinamis, filter dropdown, modal detail lengkap, modal konfirmasi pembatalan mandiri H-1, serta formulir pengajuan reservasi baru terpadu.
  CARA KERJA     : Menerima $reservations, $counts, $activeStatus, dan $facilities dari ReservationController@history. Formulir permohonan baru dan aksi pembatalan dikelola secara reaktif via Alpine.js.
--}}

<x-app-layout title="Riwayat Lengkap Reservasi Saya" active="reservation-history">
    @php
        $timeSlots = [
            '07:00', '07:30', '08:00', '08:30', '09:00', '09:30',
            '10:00', '10:30', '11:00', '11:30', '12:00', '12:30',
            '13:00', '13:30', '14:00', '14:30', '15:00', '15:30',
            '16:00', '16:30', '17:00', '17:30', '18:00', '18:30',
            '19:00', '19:30', '20:00'
        ];

        // Format data fasilitas ke JSON untuk hierarki 3 dropdown bertingkat di formulir modal
        $facilitiesMap = [];
        $hierarchy = [];

        if (isset($facilities)) {
            foreach ($facilities as $f) {
                $facilitiesMap[$f->id] = [
                    'id'       => $f->id,
                    'code'     => $f->code,
                    'name'     => $f->name,
                    'building' => $f->building,
                    'floor'    => $f->floor_location ?? 'Lantai 1',
                    'capacity' => $f->capacity,
                    'category' => ucfirst($f->category),
                ];

                $bName = $f->building;
                $floorName = 'Lantai 1';
                if (preg_match('/Lantai\s*(\d+)/i', $f->floor_location ?? '', $matches)) {
                    $floorName = 'Lantai ' . $matches[1];
                } elseif (!empty($f->floor_location)) {
                    $floorName = $f->floor_location;
                }

                if (!isset($hierarchy[$bName])) {
                    $hierarchy[$bName] = [];
                }
                if (!isset($hierarchy[$bName][$floorName])) {
                    $hierarchy[$bName][$floorName] = [];
                }

                $hierarchy[$bName][$floorName][] = [
                    'id'       => $f->id,
                    'code'     => $f->code,
                    'name'     => $f->name,
                    'capacity' => $f->capacity,
                ];
            }
        }

        $initFacility = isset($facilities) ? $facilities->first() : null;
        $initBuilding = $initFacility ? $initFacility->building : (array_key_first($hierarchy) ?? '');
        $initFloor = 'Lantai 1';
        if ($initFacility && preg_match('/Lantai\s*(\d+)/i', $initFacility->floor_location ?? '', $matches)) {
            $initFloor = 'Lantai ' . $matches[1];
        } elseif ($initFacility && !empty($initFacility->floor_location)) {
            $initFloor = $initFacility->floor_location;
        }
        $initFacilityId = $initFacility ? (string) $initFacility->id : '';
    @endphp

    <div x-data="{
        showCreateModal: {{ $errors->any() || request('action') === 'create' ? 'true' : 'false' }},
        showDetailModal: false,
        showCancelModal: false,
        selectedTicket: null,
        submitting: false,

        hierarchy: {{ json_encode($hierarchy) }},
        facilitiesData: {{ json_encode($facilitiesMap) }},
        selectedBuilding: '{{ $initBuilding }}',
        selectedFloor: '{{ $initFloor }}',
        selectedFacilityId: '{{ $initFacilityId }}',
        selectedDate: '{{ old('reservation_date', date('Y-m-d', strtotime('+1 day'))) }}',
        selectedStart: '{{ old('start_time', '09:00') }}',
        selectedEnd: '{{ old('end_time', '11:00') }}',

        get availableBuildings() {
            return Object.keys(this.hierarchy);
        },

        get availableFloors() {
            if (!this.selectedBuilding || !this.hierarchy[this.selectedBuilding]) {
                return [];
            }
            return Object.keys(this.hierarchy[this.selectedBuilding]);
        },

        get availableRooms() {
            if (!this.selectedBuilding || !this.selectedFloor || 
                !this.hierarchy[this.selectedBuilding] || 
                !this.hierarchy[this.selectedBuilding][this.selectedFloor]) {
                return [];
            }
            return this.hierarchy[this.selectedBuilding][this.selectedFloor];
        },

        onBuildingChange() {
            const floors = this.availableFloors;
            if (floors.length > 0) {
                this.selectedFloor = floors[0];
                this.onFloorChange();
            } else {
                this.selectedFloor = '';
                this.selectedFacilityId = '';
            }
        },

        onFloorChange() {
            const rooms = this.availableRooms;
            if (rooms.length > 0) {
                this.selectedFacilityId = String(rooms[0].id);
            } else {
                this.selectedFacilityId = '';
            }
        },

        get currentVenue() {
            return this.facilitiesData[this.selectedFacilityId] || {
                name: 'Pilih Ruangan Kampus',
                building: 'Lokasi Gedung',
                capacity: '-',
                category: '-'
            };
        },

        openDetail(t) {
            this.selectedTicket = t;
            this.showDetailModal = true;
        },

        openCancel(t) {
            this.selectedTicket = t;
            this.showCancelModal = true;
        }
    }" class="space-y-6">

        {{-- Header Judul --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Reservasi & Riwayat Peminjaman Ruang</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Kelola permohonan peminjaman ruang kuliah, lab, auditorium, dan fasilitas kampus secara transparan.</p>
            </div>
            <button type="button" 
                    @click="showCreateModal = true" 
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-2xl bg-slate-900 text-white text-xs sm:text-sm font-semibold hover:bg-slate-800 transition shadow-sm hover:shadow active:scale-[0.98] shrink-0">
                <span class="material-symbols-outlined text-[18px]">add_circle</span>
                <span>Ajukan Reservasi Baru</span>
            </button>
        </div>

        {{-- Flash Session Sukses --}}
        @if (session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-center gap-3 shadow-xs">
                <span class="material-symbols-outlined text-emerald-600 text-[24px] shrink-0">check_circle</span>
                <div>
                    <h4 class="text-sm font-bold">Permohonan Berhasil!</h4>
                    <p class="text-xs text-emerald-700 mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        {{-- Flash Session Error / Validation Errors --}}
        @if ($errors->any())
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 flex items-start gap-3 shadow-xs">
                <span class="material-symbols-outlined text-rose-600 text-[24px] shrink-0 mt-0.5">error</span>
                <div>
                    <h4 class="text-sm font-bold">Pengajuan Reservasi Tidak Dapat Diproses!</h4>
                    <ul class="list-disc list-inside text-xs text-rose-700 mt-1 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- Bento Grid Baris Atas: Metrik Ringkasan & Kartu Aksi Cepat --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
            {{-- Bento Card 1: Ringkasan Metrik Status (7 Kolom di Desktop) --}}
            <div class="lg:col-span-7 bg-white rounded-3xl border border-slate-200/80 p-5 sm:p-6 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                    <span class="text-[11px] font-bold tracking-wider uppercase text-slate-400 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px] text-slate-500">event_available</span>
                        Rekapitulasi Tiket Reservasi
                    </span>
                    <span class="text-xs font-semibold text-slate-500">Total: <strong class="text-slate-900">{{ $counts['all'] ?? 0 }}</strong> Pengajuan</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    {{-- Metrik 1: Semua Tiket --}}
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'all', 'page' => 1]) }}" 
                       class="p-3.5 rounded-2xl transition border {{ $activeStatus === 'all' ? 'bg-slate-900 text-white border-slate-900 shadow-xs' : 'bg-slate-50/80 text-slate-700 border-slate-200/60 hover:bg-slate-100' }}">
                        <span class="text-[11px] font-medium block opacity-75">Semua</span>
                        <span class="text-xl sm:text-2xl font-black mt-1 block">{{ $counts['all'] ?? 0 }}</span>
                    </a>

                    {{-- Metrik 2: Menunggu Persetujuan --}}
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'pending', 'page' => 1]) }}" 
                       class="p-3.5 rounded-2xl transition border {{ $activeStatus === 'pending' ? 'bg-amber-500 text-white border-amber-500 shadow-xs' : 'bg-amber-50/60 text-amber-900 border-amber-200/60 hover:bg-amber-100/60' }}">
                        <span class="text-[11px] font-medium block opacity-85">Menunggu</span>
                        <span class="text-xl sm:text-2xl font-black mt-1 block">{{ $counts['pending'] ?? 0 }}</span>
                    </a>

                    {{-- Metrik 3: Disetujui Petugas --}}
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'approved', 'page' => 1]) }}" 
                       class="p-3.5 rounded-2xl transition border {{ $activeStatus === 'approved' ? 'bg-emerald-600 text-white border-emerald-600 shadow-xs' : 'bg-emerald-50/60 text-emerald-900 border-emerald-200/60 hover:bg-emerald-100/60' }}">
                        <span class="text-[11px] font-medium block opacity-85">Disetujui</span>
                        <span class="text-xl sm:text-2xl font-black mt-1 block">{{ $counts['approved'] ?? 0 }}</span>
                    </a>

                    {{-- Metrik 4: Ditolak / Dibatalkan --}}
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'cancelled', 'page' => 1]) }}" 
                       class="p-3.5 rounded-2xl transition border {{ in_array($activeStatus, ['rejected', 'cancelled']) ? 'bg-rose-600 text-white border-rose-600 shadow-xs' : 'bg-rose-50/60 text-rose-900 border-rose-200/60 hover:bg-rose-100/60' }}">
                        <span class="text-[11px] font-medium block opacity-85">Ditolak/Batal</span>
                        <span class="text-xl sm:text-2xl font-black mt-1 block">{{ ($counts['rejected'] ?? 0) + ($counts['cancelled'] ?? 0) }}</span>
                    </a>
                </div>
            </div>

            {{-- Bento Card 2: Quick Action & Aturan Operasional (5 Kolom di Desktop) --}}
            <div class="lg:col-span-5 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white rounded-3xl p-5 sm:p-6 shadow-xs flex flex-col justify-between relative overflow-hidden">
                <div class="relative z-10">
                    <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 text-white/90 text-[11px] font-semibold mb-3 backdrop-blur-xs">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span>Sistem Proteksi Anti Bentrok Aktif</span>
                    </div>
                    <h3 class="text-base sm:text-lg font-bold">Butuh Ruang Kegiatan?</h3>
                    <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                        Pesan ruangan kelas atau aula dengan slot 30 menit. Pembatalan mandiri diperbolehkan maksimal <strong>H-1 (24 jam)</strong> sebelum acara dimulai.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-white/10 relative z-10 flex items-center justify-between">
                    <span class="text-[11px] text-slate-400">Jam Operasional: 07:00 - 20:00 WIB</span>
                    <button type="button" 
                            @click="showCreateModal = true" 
                            class="px-4 py-2 rounded-xl bg-white text-slate-900 text-xs font-bold hover:bg-slate-100 transition shadow-xs flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">add</span>
                        <span>Pesan Ruang</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Bento Grid Bagian Utama: Daftar Riwayat Reservasi (Bento List View) --}}
        <div id="daftar-reservasi" class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-5 sm:p-7 scroll-mt-6">
            
            {{-- Toolbar Pencarian & Filter Dropdown (Penyederhanaan Komponen Tanpa Tombol Berdempetan) --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 mb-5 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Daftar Reservasi Ruangan</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Daftar permohonan peminjaman sarana akademik dan status verifikasi petugas.</p>
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    {{-- Dropdown Pemilihan Status (Menghilangkan Tombol Pill Berdempetan) --}}
                    <div class="relative min-w-[180px]">
                        <label for="reservation-status-filter" class="sr-only">Filter Status</label>
                        <select id="reservation-status-filter" 
                                onchange="window.location.href = this.value" 
                                class="w-full h-10 pl-3 pr-8 rounded-xl border border-slate-200 bg-slate-50 text-xs font-semibold text-slate-800 focus:bg-white focus:border-slate-900 focus:ring-slate-900 transition">
                            <option value="{{ request()->fullUrlWithQuery(['status' => 'all', 'page' => 1]) }}" {{ $activeStatus === 'all' ? 'selected' : '' }}>
                                Semua Status ({{ $counts['all'] ?? 0 }})
                            </option>
                            <option value="{{ request()->fullUrlWithQuery(['status' => 'pending', 'page' => 1]) }}" {{ $activeStatus === 'pending' ? 'selected' : '' }}>
                                Menunggu Persetujuan ({{ $counts['pending'] ?? 0 }})
                            </option>
                            <option value="{{ request()->fullUrlWithQuery(['status' => 'approved', 'page' => 1]) }}" {{ $activeStatus === 'approved' ? 'selected' : '' }}>
                                Disetujui Petugas ({{ $counts['approved'] ?? 0 }})
                            </option>
                            <option value="{{ request()->fullUrlWithQuery(['status' => 'rejected', 'page' => 1]) }}" {{ $activeStatus === 'rejected' ? 'selected' : '' }}>
                                Ditolak ({{ $counts['rejected'] ?? 0 }})
                            </option>
                            <option value="{{ request()->fullUrlWithQuery(['status' => 'cancelled', 'page' => 1]) }}" {{ $activeStatus === 'cancelled' ? 'selected' : '' }}>
                                Dibatalkan ({{ $counts['cancelled'] ?? 0 }})
                            </option>
                        </select>
                    </div>

                    {{-- Form Pencarian Kata Kunci --}}
                    <form action="{{ url()->current() }}" method="GET" class="relative w-full sm:w-64">
                        @if (request('status') && request('status') !== 'all')
                            <input type="hidden" name="status" value="{{ request('status') }}">
                        @endif
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">search</span>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Cari tiket, ruangan, tujuan..." 
                               class="w-full h-10 pl-9 pr-8 rounded-xl border border-slate-200 bg-slate-50 text-xs focus:bg-white focus:border-slate-900 focus:ring-slate-900 placeholder-slate-400 transition">
                        @if (request('search'))
                            <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                <span class="material-symbols-outlined text-[16px]">close</span>
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            {{-- Bento Card List View (Menggantikan Format Tabel HTML Ramai) --}}
            <div class="space-y-3">
                @forelse ($reservations as $res)
                    @php
                        $isHMinus1 = false;
                        try {
                            $resDateTime = \Carbon\Carbon::parse($res->reservation_date)->setTimeFromTimeString($res->start_time);
                            $isHMinus1 = in_array($res->status, ['pending', 'approved']) && now()->diffInSeconds($resDateTime, false) >= 86400;
                        } catch (\Exception $e) {
                            $isHMinus1 = false;
                        }

                        $ticketData = [
                            'db_id'       => $res->id,
                            'id'          => $res->ticket_code,
                            'venue'       => $res->facility->name ?? 'Fasilitas Kampus',
                            'building'    => ($res->facility->building ?? 'Gedung Kampus') . ($res->facility->floor_location ? ' - ' . $res->facility->floor_location : ''),
                            'date'        => \Carbon\Carbon::parse($res->reservation_date)->translatedFormat('d M Y'),
                            'time'        => substr($res->start_time, 0, 5) . ' - ' . substr($res->end_time, 0, 5) . ' WIB',
                            'slots'       => $res->total_slots ?? 1,
                            'purpose'     => $res->purpose,
                            'participants'=> $res->participants_count ?? 1,
                            'status'      => $res->status,
                            'reviewer'    => $res->reviewer->name ?? 'Belum Diverifikasi',
                            'reviewed_at' => $res->reviewed_at ? \Carbon\Carbon::parse($res->reviewed_at)->translatedFormat('d M Y • H:i') : null,
                            'officerNote' => $res->rejection_reason ?? $res->cancellation_reason ?? ($res->status === 'approved' ? 'Telah disetujui oleh Petugas Sarpras. Harap menjaga kebersihan fasilitas.' : ($res->status === 'cancelled' ? 'Reservasi telah dibatalkan.' : 'Sedang dalam antrean evaluasi dan verifikasi staf sarpras.')),
                            'canCancel'   => $isHMinus1,
                        ];
                    @endphp

                    <div class="group bg-white rounded-2xl border border-slate-200/80 hover:border-slate-300 p-4 sm:p-5 transition hover:shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                        
                        {{-- Kolom Kiri: Kode Tiket, Tanggal & Jam Kegiatan --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                <span class="text-xs font-semibold text-slate-700 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[15px] text-slate-400">calendar_today</span>
                                    <span>{{ \Carbon\Carbon::parse($res->reservation_date)->translatedFormat('d M Y') }}</span>
                                </span>
                                <span class="text-slate-300">•</span>
                                <span class="text-xs font-semibold text-slate-700">
                                    {{ substr($res->start_time, 0, 5) }} - {{ substr($res->end_time, 0, 5) }} WIB ({{ $res->total_slots ?? 1 }} Slot)
                                </span>
                            </div>

                            <h3 class="text-sm sm:text-base font-bold text-slate-900 group-hover:text-blue-950 transition truncate">
                                {{ $res->facility->name ?? 'Fasilitas Kampus' }}
                            </h3>

                            <div class="text-xs text-slate-500 mt-0.5 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[14px]">location_on</span>
                                <span>{{ $res->facility->building ?? 'Gedung Kampus' }} {{ $res->facility->floor_location ? '• ' . $res->facility->floor_location : '' }}</span>
                            </div>

                            <p class="text-xs text-slate-600 mt-2 line-clamp-1 italic bg-slate-50/70 rounded-lg px-2.5 py-1 border border-slate-100/80">
                                "{{ $res->purpose }}"
                            </p>
                        </div>

                        {{-- Kolom Kanan: Status & Tombol Aksi --}}
                        <div class="flex items-center justify-between md:justify-end gap-3 pt-3 md:pt-0 border-t md:border-t-0 border-slate-100 shrink-0">
                            {{-- Status Chip --}}
                            <div>
                                @if ($res->status === 'pending')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs bg-amber-50 text-amber-800 border border-amber-200 font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        <span>Menunggu Persetujuan</span>
                                    </span>
                                @elseif ($res->status === 'approved')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs bg-emerald-50 text-emerald-800 border border-emerald-200 font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                        <span>Disetujui Petugas</span>
                                    </span>
                                @elseif ($res->status === 'rejected')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs bg-rose-50 text-rose-700 border border-rose-200 font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        <span>Ditolak</span>
                                    </span>
                                @elseif ($res->status === 'cancelled')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs bg-slate-100 text-slate-600 border border-slate-200 font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        <span>Dibatalkan</span>
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs bg-blue-50 text-blue-700 border border-blue-200 font-medium capitalize">
                                        {{ $res->status }}
                                    </span>
                                @endif
                            </div>

                            {{-- Tombol Detail & Pembatalan --}}
                            <div class="flex items-center gap-1.5">
                                <button type="button" 
                                        @click="openDetail({{ json_encode($ticketData) }})" 
                                        class="px-3 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition flex items-center gap-1 shadow-2xs">
                                    <span class="material-symbols-outlined text-[15px] text-slate-500">visibility</span>
                                    <span>Lihat Detail</span>
                                </button>

                                @if ($isHMinus1)
                                    <button type="button" 
                                            @click="openCancel({{ json_encode($ticketData) }})" 
                                            class="px-2.5 py-1.5 rounded-xl bg-rose-50 text-rose-700 border border-rose-200 text-xs font-semibold hover:bg-rose-100 transition" 
                                            title="Batalkan Reservasi Mandiri (Maksimal H-1)">
                                        Batalkan
                                    </button>
                                @endif
                            </div>
                        </div>

                    </div>
                @empty
                    <div class="py-12 px-4 text-center rounded-2xl border border-dashed border-slate-200 bg-slate-50/50">
                        <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                            <span class="material-symbols-outlined text-[28px]">calendar_month</span>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900 mb-1">Belum Ada Riwayat Reservasi</h4>
                        <p class="text-xs text-slate-500 max-w-sm mx-auto mb-4">
                            @if (request('search') || request('status', 'all') !== 'all')
                                Tidak ditemukan tiket reservasi yang sesuai dengan kriteria filter atau pencarian Anda.
                            @else
                                Anda belum pernah mengajukan permohonan reservasi peminjaman ruangan kampus.
                            @endif
                        </p>
                        <button type="button" 
                                @click="showCreateModal = true" 
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition">
                            <span class="material-symbols-outlined text-[16px]">add</span>
                            <span>Ajukan Reservasi Sekarang</span>
                        </button>
                    </div>
                @endforelse
            </div>

            {{-- Navigasi Paginasi Laravel --}}
            @if ($reservations->hasPages())
                <div class="mt-6 pt-4 border-t border-slate-100">
                    {{ $reservations->links() }}
                </div>
            @endif
        </div>

        {{-- ========================================================================= --}}
        {{-- MODAL POP-UP TERPADU: FORM PENGAJUAN RESERVASI RUANGAN BARU               --}}
        {{-- ========================================================================= --}}
        <!-- 
          ELEMEN       : Modal Pop-up Form Pengajuan Reservasi Baru
          KEGUNAAN     : Memungkinkan mahasiswa/dosen mengajukan permohonan peminjaman ruang langsung dari halaman riwayat.
          CARA KERJA   : Dikendalikan oleh Alpine.js showCreateModal, otomatis terbuka jika terdapat error bentrok atau parameter action=create.
        -->
        <div x-show="showCreateModal" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            
            <div @click.away="showCreateModal = false" 
                 class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl border border-slate-200 relative my-8">
                
                {{-- Tombol Tutup --}}
                <button type="button" 
                        @click="showCreateModal = false" 
                        class="absolute top-5 right-5 p-1.5 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>

                <div class="mb-5">
                    <div class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md bg-slate-100 text-[11px] font-bold text-slate-700 mb-1.5">
                        <span class="material-symbols-outlined text-[14px] text-emerald-600">domain_add</span>
                        <span>Formulir Peminjaman Sarana</span>
                    </div>
                    <h2 class="text-xl font-bold text-slate-900">Pengajuan Reservasi Ruang Kampus</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Pilih ruangan akademik, tanggal pelaksanaan, dan rentang jam operasional (07:00 - 20:00 WIB).</p>
                </div>

                <!-- 
                  ROUTE: POST /user/reservations
                  FUNGSI: Mengirimkan permohonan reservasi baru dengan proteksi interval 30 menit
                -->
                <form action="{{ route('user.reservations.store') }}" 
                      method="POST" 
                      @submit="submitting = true" 
                      class="space-y-4">
                    @csrf

                    {{-- 1. Pemilihan Fasilitas Bertingkat (Gedung -> Lantai -> Ruangan) --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Pilih Fasilitas & Ruang Akademik <span class="text-rose-500">*</span>
                        </label>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5">
                            {{-- Dropdown 1: Pilih Gedung --}}
                            <div>
                                <label for="building-select" class="block text-[10px] font-bold uppercase text-slate-500 mb-1">
                                    1. Gedung
                                </label>
                                <select id="building-select" 
                                        x-model="selectedBuilding" 
                                        @change="onBuildingChange()" 
                                        class="w-full h-10 px-2.5 bg-slate-50 rounded-xl text-xs font-medium text-slate-800 border border-slate-200 focus:border-slate-900 focus:bg-white focus:outline-none transition">
                                    <template x-for="b in availableBuildings" :key="b">
                                        <option :value="b" x-text="b" :selected="b === selectedBuilding"></option>
                                    </template>
                                </select>
                            </div>

                            {{-- Dropdown 2: Pilih Lantai --}}
                            <div>
                                <label for="floor-select" class="block text-[10px] font-bold uppercase text-slate-500 mb-1">
                                    2. Lantai
                                </label>
                                <select id="floor-select" 
                                        x-model="selectedFloor" 
                                        @change="onFloorChange()" 
                                        class="w-full h-10 px-2.5 bg-slate-50 rounded-xl text-xs font-medium text-slate-800 border border-slate-200 focus:border-slate-900 focus:bg-white focus:outline-none transition">
                                    <template x-for="fl in availableFloors" :key="fl">
                                        <option :value="fl" x-text="fl" :selected="fl === selectedFloor"></option>
                                    </template>
                                </select>
                            </div>

                            {{-- Dropdown 3: Pilih Ruangan --}}
                            <div>
                                <label for="venue-select" class="block text-[10px] font-bold uppercase text-slate-500 mb-1">
                                    3. Ruangan
                                </label>
                                <select name="facility_id" 
                                        id="venue-select" 
                                        x-model="selectedFacilityId" 
                                        required 
                                        class="w-full h-10 px-2.5 bg-slate-50 rounded-xl text-xs font-bold text-slate-900 border @error('facility_id') border-rose-400 bg-rose-50/50 @else border-slate-200 @enderror focus:border-slate-900 focus:bg-white focus:outline-none transition">
                                    <template x-for="room in availableRooms" :key="room.id">
                                        <option :value="String(room.id)" 
                                                x-text="room.code + ' (' + room.capacity + ' org)'" 
                                                :selected="String(room.id) === String(selectedFacilityId)">
                                        </option>
                                    </template>
                                </select>
                            </div>
                        </div>
                        @error('facility_id')
                            <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- 2. Tanggal Pelaksanaan Kegiatan --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="res-date" class="text-xs font-bold uppercase tracking-wider text-slate-700">
                                Tanggal Kegiatan <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-[11px] text-emerald-700 font-semibold">Tersedia Mulai Hari Ini</span>
                        </div>
                        <input type="date" 
                               id="res-date" 
                               name="reservation_date" 
                               x-model="selectedDate" 
                               min="{{ date('Y-m-d') }}" 
                               required 
                               class="w-full h-10 px-3.5 bg-slate-50 rounded-xl text-xs sm:text-sm font-medium text-slate-800 border @error('reservation_date') border-rose-400 bg-rose-50/50 @else border-slate-200 @enderror focus:border-slate-900 focus:bg-white focus:outline-none transition">
                        @error('reservation_date')
                            <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- 3. Pemilihan Rentang Jam Operasional (07:00 - 20:00 WIB) --}}
                    <div class="p-3.5 rounded-2xl bg-slate-50/80 border border-slate-200/80">
                        <div class="flex items-center justify-between mb-2">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-700">
                                Rentang Jam Operasional <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-[11px] text-slate-500 font-mono" x-text="selectedStart + ' - ' + selectedEnd + ' WIB'"></span>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="start_time" class="block text-[11px] font-semibold text-slate-600 mb-1">Jam Mulai</label>
                                <select name="start_time" 
                                        id="start_time" 
                                        x-model="selectedStart" 
                                        required 
                                        class="w-full h-9 px-2.5 bg-white rounded-lg text-xs font-semibold text-slate-800 border @error('start_time') border-rose-400 @else border-slate-200 @enderror focus:border-slate-900 focus:outline-none transition">
                                    @foreach (array_slice($timeSlots, 0, -1) as $slot)
                                        <option value="{{ $slot }}" {{ old('start_time', '09:00') === $slot ? 'selected' : '' }}>
                                            {{ $slot }} WIB
                                        </option>
                                    @endforeach
                                </select>
                                @error('start_time')
                                    <p class="mt-1 text-[11px] text-rose-600 font-medium">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="end_time" class="block text-[11px] font-semibold text-slate-600 mb-1">Jam Selesai</label>
                                <select name="end_time" 
                                        id="end_time" 
                                        x-model="selectedEnd" 
                                        required 
                                        class="w-full h-9 px-2.5 bg-white rounded-lg text-xs font-semibold text-slate-800 border @error('end_time') border-rose-400 @else border-slate-200 @enderror focus:border-slate-900 focus:outline-none transition">
                                    @foreach (array_slice($timeSlots, 1) as $slot)
                                        <option value="{{ $slot }}" {{ old('end_time', '11:00') === $slot ? 'selected' : '' }}>
                                            {{ $slot }} WIB
                                        </option>
                                    @endforeach
                                </select>
                                @error('end_time')
                                    <p class="mt-1 text-[11px] text-rose-600 font-medium">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- 4. Estimasi Jumlah Peserta --}}
                    <div>
                        <label for="participants_count" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1">
                            Estimasi Jumlah Peserta (Orang)
                        </label>
                        <input type="number" 
                               id="participants_count" 
                               name="participants_count" 
                               min="1" 
                               max="1000" 
                               value="{{ old('participants_count', 1) }}" 
                               class="w-full h-10 px-3.5 bg-slate-50 rounded-xl text-xs sm:text-sm font-medium text-slate-800 border border-slate-200 focus:border-slate-900 focus:bg-white focus:outline-none transition">
                    </div>

                    {{-- 5. Tujuan Kegiatan & PIC --}}
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <label for="purpose" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                Tujuan Penggunaan / Nama Acara & PIC <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-[11px] text-rose-600 font-semibold">Min. 10 karakter</span>
                        </div>
                        <textarea id="purpose" 
                                  name="purpose" 
                                  rows="2" 
                                  required 
                                  minlength="10"
                                  placeholder="Contoh: Kuliah Pengganti Algoritma Kelas B. PIC: Dimas Pratama (08123456789)" 
                                  class="w-full p-3 bg-slate-50 rounded-xl text-xs sm:text-sm text-slate-800 border @error('purpose') border-rose-400 bg-rose-50/50 @else border-slate-200 @enderror focus:border-slate-900 focus:bg-white focus:outline-none transition">{{ old('purpose') }}</textarea>
                        @error('purpose')
                            <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tombol Aksi Formulir --}}
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" 
                                @click="showCreateModal = false" 
                                class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                            Batal
                        </button>
                        <button type="submit" 
                                :disabled="submitting" 
                                class="px-6 py-2.5 rounded-xl bg-slate-900 text-white text-xs sm:text-sm font-semibold hover:bg-slate-800 disabled:opacity-50 transition shadow-xs flex items-center gap-2">
                            <span x-show="!submitting" class="material-symbols-outlined text-[18px]">send</span>
                            <span x-show="submitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            <span x-text="submitting ? 'Memvalidasi...' : 'Kirim Permohonan Reservasi'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- MODAL POP-UP: DETAIL LENGKAP TIKET PERMOHONAN RESERVASI                   --}}
        {{-- ========================================================================= --}}
        <div x-show="showDetailModal" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            
            <div @click.away="showDetailModal = false" 
                 class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-slate-200 relative my-8">
                
                <button type="button" 
                        @click="showDetailModal = false" 
                        class="absolute top-5 right-5 p-1.5 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>

                <template x-if="selectedTicket">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="text-xs font-semibold text-slate-500">Rincian Reservasi Ruangan</span>
                        </div>

                        <h2 class="text-lg font-bold text-slate-900" x-text="selectedTicket.venue"></h2>
                        <p class="text-xs text-slate-500 mb-4" x-text="selectedTicket.building"></p>

                        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 mb-4 space-y-2.5 text-xs">
                            <div class="flex justify-between">
                                <span class="text-slate-500">Tanggal Kegiatan:</span>
                                <span class="font-bold text-slate-900" x-text="selectedTicket.date"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Rentang Waktu:</span>
                                <span class="font-semibold text-slate-800" x-text="selectedTicket.time + ' (' + selectedTicket.slots + ' Slot)'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Jumlah Peserta:</span>
                                <span class="font-semibold text-slate-800" x-text="selectedTicket.participants + ' Orang'"></span>
                            </div>
                            <div class="pt-2 border-t border-slate-200/80">
                                <span class="text-slate-500 block mb-1">Tujuan / Keperluan Acara:</span>
                                <p class="text-slate-700 leading-relaxed font-medium" x-text="selectedTicket.purpose"></p>
                            </div>
                        </div>

                        {{-- Catatan Petugas Sarpras --}}
                        <div class="p-3.5 rounded-2xl border text-xs mb-5"
                             :class="selectedTicket.status === 'approved' ? 'bg-emerald-50/70 border-emerald-200 text-emerald-900' : 'bg-slate-50 border-slate-200 text-slate-700'">
                            <div class="flex items-center justify-between mb-1">
                                <span class="font-bold flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[16px]">verified</span>
                                    <span>Evaluasi & Catatan Petugas</span>
                                </span>
                                <span class="font-semibold text-[11px] capitalize" x-text="selectedTicket.status"></span>
                            </div>
                            <p class="leading-relaxed" x-text="selectedTicket.officerNote"></p>
                            <template x-if="selectedTicket.reviewed_at">
                                <div class="mt-2 text-[10px] opacity-75">
                                    Diverifikasi oleh: <span class="font-semibold" x-text="selectedTicket.reviewer"></span> • <span x-text="selectedTicket.reviewed_at"></span>
                                </div>
                            </template>
                        </div>

                        <div class="flex items-center justify-end gap-2.5">
                            <template x-if="selectedTicket.canCancel">
                                <button type="button" 
                                        @click="showDetailModal = false; openCancel(selectedTicket)" 
                                        class="px-4 py-2 rounded-xl bg-rose-50 text-rose-700 border border-rose-200 text-xs font-semibold hover:bg-rose-100 transition">
                                    Batalkan Reservasi
                                </button>
                            </template>
                            <button type="button" 
                                    @click="showDetailModal = false" 
                                    class="px-5 py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition">
                                Tutup
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- MODAL POP-UP: KONFIRMASI PEMBATALAN MANDIRI (BATAS MINIMAL H-1)            --}}
        {{-- ========================================================================= --}}
        <div x-show="showCancelModal" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            
            <div @click.away="showCancelModal = false" 
                 class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 relative my-8">
                
                <template x-if="selectedTicket">
                    <div>
                        <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mx-auto mb-4">
                            <span class="material-symbols-outlined text-[28px]">event_busy</span>
                        </div>

                        <h3 class="text-base font-bold text-slate-900 text-center">Konfirmasi Pembatalan Reservasi</h3>
                        <p class="text-xs text-slate-500 text-center mt-1 mb-4">
                            Apakah Anda yakin ingin membatalkan peminjaman ruangan <strong class="text-slate-800" x-text="selectedTicket.venue"></strong>?
                        </p>

                        <div class="bg-rose-50/60 border border-rose-100 rounded-xl p-3 text-xs text-rose-800 mb-5 leading-relaxed">
                            <span class="font-bold block mb-0.5">Ketentuan Pembatalan Mandiri:</span>
                            Tindakan ini permanen. Slot ruangan yang telah dibatalkan akan otomatis dibuka kembali untuk pengguna lain.
                        </div>

                        <!-- 
                          ROUTE: DELETE /user/reservations/{id}/cancel
                          FUNGSI: Membatalkan pengajuan reservasi secara mandiri dengan verifikasi batas minimal H-1
                        -->
                        <form :action="'{{ url('/user/reservations') }}/' + selectedTicket.db_id + '/cancel'" method="POST" class="space-y-3">
                            @csrf
                            @method('DELETE')

                            <div>
                                <label for="cancellation_reason" class="block text-xs font-bold text-slate-700 mb-1">
                                    Alasan Pembatalan (Opsional)
                                </label>
                                <textarea name="cancellation_reason" 
                                          id="cancellation_reason" 
                                          rows="2" 
                                          placeholder="Tuliskan alasan pembatalan kegiatan Anda..." 
                                          class="w-full p-2.5 bg-slate-50 rounded-xl text-xs text-slate-800 border border-slate-200 focus:border-slate-900 focus:bg-white focus:outline-none transition"></textarea>
                            </div>

                            <div class="flex items-center justify-end gap-2 pt-2">
                                <button type="button" 
                                        @click="showCancelModal = false" 
                                        class="px-4 py-2 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 transition">
                                    Kembali
                                </button>
                                <button type="submit" 
                                        class="px-5 py-2 rounded-xl bg-rose-600 text-white text-xs font-semibold hover:bg-rose-700 transition shadow-xs">
                                    Ya, Batalkan Tiket
                                </button>
                            </div>
                        </form>
                    </div>
                </template>
            </div>
        </div>

    </div>
</x-app-layout>
