{{-- 
  NAMA FILE      : reservation-history.blade.php
  FUNGSIONALITAS : Halaman Terpadu Reservasi & Riwayat Peminjaman Ruang Kampus (USR-01, USR-02, USR-03)
  DESKRIPSI      : Antarmuka Apple-esque Bento modern yang mengintegrasikan riwayat peminjaman fasilitas, 
                   metrik status, pencarian dinamis, tab filter underline, modal detail tiket lengkap, 
                   modal konfirmasi pembatalan mandiri H-1, serta modal formulir pengajuan reservasi terpadu.
                   Mengadopsi Status Opsi B: Pure Typographic (bebas dari bulatan titik/dot dan ikon mikro pada status).
  CARA KERJA     : Menerima $reservations, $counts, $activeStatus, dan $facilities dari ReservationController@history. 
                   Formulir permohonan baru dan aksi pembatalan dikelola secara reaktif via Alpine.js.
--}}

<x-app-layout title="Riwayat Reservasi Saya" active="reservation-history">
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
    }" class="space-y-10 font-sans text-[#1D1D1F]">

        {{-- Toolbar Aksi Cepat (Judul halaman telah terwakili pada Universal Top Header) --}}
        <div class="flex items-center justify-end gap-3">
            <a href="{{ route('user.reservation-form') }}" 
               class="hidden md:inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border border-gray-200 text-xs font-medium text-[#1D1D1F] hover:bg-gray-100 transition">
                <span>Halaman Form Penuh</span>
            </a>
            <button type="button" 
                    @click="showCreateModal = true" 
                    class="bg-[#1D1D1F] text-white px-6 py-2.5 rounded-xl font-medium hover:bg-black transition text-sm shadow-xs flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">add</span>
                <span>Permohonan Baru</span>
            </button>
        </div>

        {{-- Flash Session Notifikasi Sukses --}}
        @if (session('success'))
            <div class="p-5 rounded-2xl bg-emerald-50 border border-emerald-100 text-emerald-900 flex items-center gap-3">
                <span class="material-symbols-outlined text-emerald-600 text-[22px] shrink-0">check_circle</span>
                <div>
                    <h4 class="text-sm font-bold">Permohonan Berhasil!</h4>
                    <p class="text-xs text-emerald-700 mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        {{-- Flash Session Error / Validation Errors --}}
        @if ($errors->any())
            <div class="p-5 rounded-2xl bg-rose-50 border border-rose-100 text-rose-900 flex items-start gap-3">
                <span class="material-symbols-outlined text-rose-600 text-[22px] shrink-0 mt-0.5">error</span>
                <div>
                    <h4 class="text-sm font-bold">Pengajuan Tidak Dapat Diproses:</h4>
                    <ul class="list-disc list-inside text-xs text-rose-700 mt-1 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- 2. REKAPITULASI METRIK BENTO (AIRY & CLEAN) --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            {{-- Metrik 1: Semua --}}
            <a href="{{ request()->fullUrlWithQuery(['status' => 'all', 'page' => 1]) }}" 
               class="p-6 rounded-[2rem] transition-all {{ $activeStatus === 'all' ? 'bg-[#1D1D1F] text-white shadow-sm' : 'bg-[#F9F9FB] text-[#1D1D1F] hover:bg-white hover:shadow-[0_8px_24px_rgba(0,0,0,0.06)]' }}">
                <span class="text-xs font-medium block opacity-70">Semua Tiket</span>
                <span class="text-2xl sm:text-3xl font-bold mt-2 block">{{ $counts['all'] ?? 0 }}</span>
            </a>

            {{-- Metrik 2: Menunggu --}}
            <a href="{{ request()->fullUrlWithQuery(['status' => 'pending', 'page' => 1]) }}" 
               class="p-6 rounded-[2rem] transition-all {{ $activeStatus === 'pending' ? 'bg-[#1D1D1F] text-white shadow-sm' : 'bg-[#F9F9FB] text-[#1D1D1F] hover:bg-white hover:shadow-[0_8px_24px_rgba(0,0,0,0.06)]' }}">
                <span class="text-xs font-medium block opacity-70">Menunggu</span>
                <span class="text-2xl sm:text-3xl font-bold mt-2 block text-amber-700 {{ $activeStatus === 'pending' ? '!text-white' : '' }}">{{ $counts['pending'] ?? 0 }}</span>
            </a>

            {{-- Metrik 3: Disetujui --}}
            <a href="{{ request()->fullUrlWithQuery(['status' => 'approved', 'page' => 1]) }}" 
               class="p-6 rounded-[2rem] transition-all {{ $activeStatus === 'approved' ? 'bg-[#1D1D1F] text-white shadow-sm' : 'bg-[#F9F9FB] text-[#1D1D1F] hover:bg-white hover:shadow-[0_8px_24px_rgba(0,0,0,0.06)]' }}">
                <span class="text-xs font-medium block opacity-70">Disetujui</span>
                <span class="text-2xl sm:text-3xl font-bold mt-2 block text-emerald-800 {{ $activeStatus === 'approved' ? '!text-white' : '' }}">{{ $counts['approved'] ?? 0 }}</span>
            </a>

            {{-- Metrik 4: Ditolak / Batal --}}
            <a href="{{ request()->fullUrlWithQuery(['status' => 'cancelled', 'page' => 1]) }}" 
               class="p-6 rounded-[2rem] transition-all {{ in_array($activeStatus, ['rejected', 'cancelled']) ? 'bg-[#1D1D1F] text-white shadow-sm' : 'bg-[#F9F9FB] text-[#1D1D1F] hover:bg-white hover:shadow-[0_8px_24px_rgba(0,0,0,0.06)]' }}">
                <span class="text-xs font-medium block opacity-70">Ditolak / Batal</span>
                <span class="text-2xl sm:text-3xl font-bold mt-2 block text-rose-700 {{ in_array($activeStatus, ['rejected', 'cancelled']) ? '!text-white' : '' }}">{{ ($counts['rejected'] ?? 0) + ($counts['cancelled'] ?? 0) }}</span>
            </a>
        </div>

        {{-- 3. TOOLBAR FILTER TAB UNDERLINE & FORM PENCARIAN --}}
        <div id="daftar-reservasi" class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-2 border-b border-gray-100 scroll-mt-6">
            <div class="flex items-center gap-8 text-sm font-medium overflow-x-auto no-scrollbar">
                <a href="{{ request()->fullUrlWithQuery(['status' => 'all', 'page' => 1]) }}" 
                   class="pb-3 -mb-px transition whitespace-nowrap {{ $activeStatus === 'all' ? 'text-[#1D1D1F] border-b-2 border-[#1D1D1F] font-semibold' : 'text-[#86868B] hover:text-[#1D1D1F]' }}">
                    Semua Reservasi ({{ $counts['all'] ?? 0 }})
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'pending', 'page' => 1]) }}" 
                   class="pb-3 -mb-px transition whitespace-nowrap {{ $activeStatus === 'pending' ? 'text-[#1D1D1F] border-b-2 border-[#1D1D1F] font-semibold' : 'text-[#86868B] hover:text-[#1D1D1F]' }}">
                    Menunggu ({{ $counts['pending'] ?? 0 }})
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'approved', 'page' => 1]) }}" 
                   class="pb-3 -mb-px transition whitespace-nowrap {{ $activeStatus === 'approved' ? 'text-[#1D1D1F] border-b-2 border-[#1D1D1F] font-semibold' : 'text-[#86868B] hover:text-[#1D1D1F]' }}">
                    Disetujui ({{ $counts['approved'] ?? 0 }})
                </a>
                <a href="{{ request()->fullUrlWithQuery(['status' => 'cancelled', 'page' => 1]) }}" 
                   class="pb-3 -mb-px transition whitespace-nowrap {{ in_array($activeStatus, ['rejected', 'cancelled']) ? 'text-[#1D1D1F] border-b-2 border-[#1D1D1F] font-semibold' : 'text-[#86868B] hover:text-[#1D1D1F]' }}">
                    Ditolak/Batal ({{ ($counts['rejected'] ?? 0) + ($counts['cancelled'] ?? 0) }})
                </a>
            </div>

            <form action="{{ url()->current() }}" method="GET" class="w-full md:w-80">
                @if (request('status') && request('status') !== 'all')
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="relative">
                    <span class="material-symbols-outlined absolute left-3.5 top-1/2 -translate-y-1/2 text-[#86868B] text-[18px]">search</span>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Cari nama ruang atau kegiatan..." 
                           class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] placeholder:text-[#86868B] pl-10 pr-8 py-2.5 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-black/10 focus:bg-white transition">
                    @if (request('search'))
                        <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#86868B] hover:text-[#1D1D1F]">
                            <span class="material-symbols-outlined text-[16px]">close</span>
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- 4. DAFTAR KARTU RESERVASI BERSIH & LUAS (STATUS OPSI B: PURE TYPOGRAPHIC) --}}
        <div class="space-y-4">
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
                        'building'    => ($res->facility->building ?? 'Gedung Kampus') . ($res->facility->floor_location ? ' • ' . $res->facility->floor_location : ''),
                        'capacity'    => ($res->facility->capacity ?? '-') . ' Kursi',
                        'date'        => \Carbon\Carbon::parse($res->reservation_date)->isoFormat('dddd, D MMMM Y'),
                        'time'        => substr($res->start_time, 0, 5) . ' - ' . substr($res->end_time, 0, 5) . ' WIB',
                        'slots'       => $res->total_slots ?? 1,
                        'purpose'     => $res->purpose,
                        'participants'=> ($res->participants_count ?? 1) . ' Orang',
                        'status'      => $res->status,
                        'reviewer'    => $res->reviewer->name ?? 'Belum Diverifikasi',
                        'reviewed_at' => $res->reviewed_at ? \Carbon\Carbon::parse($res->reviewed_at)->isoFormat('dddd, D MMMM Y • H:i') : null,
                        'officerNote' => $res->rejection_reason ?? $res->cancellation_reason ?? ($res->status === 'approved' ? 'Telah disetujui oleh Petugas Sarpras. Harap menjaga kebersihan fasilitas.' : ($res->status === 'cancelled' ? 'Reservasi telah dibatalkan.' : 'Sedang dalam antrean evaluasi dan verifikasi staf sarpras.')),
                        'canCancel'   => $isHMinus1,
                    ];
                @endphp

                <div class="bg-white p-7 sm:p-8 rounded-[2rem] border border-gray-100 hover:shadow-[0_8px_30px_rgb(0,0,0,0.06)] transition duration-300">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                        
                        <div class="space-y-2 flex-1">
                            {{-- Status Opsi B: Pure Typographic Tanpa Dot dan Tanpa Ikon --}}
                            <div>
                                @if ($res->status === 'approved')
                                    <span class="text-xs font-semibold tracking-wide text-emerald-800">Disetujui</span>
                                @elseif ($res->status === 'pending')
                                    <span class="text-xs font-semibold tracking-wide text-amber-800">Menunggu Verifikasi</span>
                                @elseif ($res->status === 'rejected')
                                    <span class="text-xs font-semibold tracking-wide text-rose-700">Ditolak</span>
                                @elseif ($res->status === 'cancelled')
                                    <span class="text-xs font-semibold tracking-wide text-slate-500">Dibatalkan</span>
                                @else
                                    <span class="text-xs font-semibold tracking-wide text-slate-600 capitalize">{{ $res->status }}</span>
                                @endif
                            </div>

                            <h3 class="text-xl font-bold text-[#1D1D1F]">
                                {{ $res->facility->name ?? 'Fasilitas Kampus' }} • {{ $res->facility->building ?? 'Gedung' }}
                            </h3>
                            <p class="text-sm text-[#86868B]">{{ $res->purpose }}</p>
                            
                            <div class="text-xs text-[#86868B] pt-1">
                                {{ \Carbon\Carbon::parse($res->reservation_date)->isoFormat('dddd, D MMMM Y') }} • {{ substr($res->start_time, 0, 5) }} - {{ substr($res->end_time, 0, 5) }} WIB • Kapasitas {{ $res->facility->capacity ?? '-' }} Kursi
                            </div>
                        </div>
                        
                        {{-- Tombol Aksi Bersih --}}
                        <div class="flex items-center gap-3 shrink-0 pt-4 lg:pt-0 border-t lg:border-t-0 border-gray-100">
                            <button type="button" 
                                    @click="openDetail({{ json_encode($ticketData) }})" 
                                    class="bg-[#1D1D1F] text-white px-5 py-2.5 rounded-xl text-xs font-medium hover:bg-gray-800 transition">
                                Lihat Detail Tiket
                            </button>

                            @if ($isHMinus1)
                                <button type="button" 
                                        @click="openCancel({{ json_encode($ticketData) }})" 
                                        class="text-xs font-medium text-red-600 hover:underline px-3 py-2">
                                    Batalkan (H-1)
                                </button>
                            @endif
                        </div>

                    </div>
                </div>
            @empty
                <div class="py-16 text-center rounded-[2rem] border border-dashed border-gray-200 bg-[#F9F9FB] space-y-3">
                    <span class="material-symbols-outlined text-[36px] text-gray-400 font-light">calendar_today</span>
                    <h4 class="text-base font-bold text-[#1D1D1F]">Belum Ada Riwayat Reservasi</h4>
                    <p class="text-sm text-[#86868B] max-w-sm mx-auto">
                        @if (request('search') || request('status', 'all') !== 'all')
                            Tidak ditemukan permohonan reservasi yang cocok dengan kriteria pencarian Anda.
                        @else
                            Anda belum pernah mengajukan permohonan reservasi peminjaman ruangan kampus.
                        @endif
                    </p>
                    <div class="pt-2">
                        <button type="button" 
                                @click="showCreateModal = true" 
                                class="bg-[#1D1D1F] text-white px-6 py-2.5 rounded-xl text-xs font-medium hover:bg-gray-800 transition">
                            Ajukan Reservasi Baru
                        </button>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Navigasi Paginasi Laravel --}}
        @if ($reservations->hasPages())
            <div class="pt-4">
                {{ $reservations->links() }}
            </div>
        @endif

        {{-- ========================================================================= --}}
        {{-- MODAL 1: DETAIL LENGKAP TIKET RESERVASI (APPLE-ESQUE PURE TYPOGRAPHY)     --}}
        {{-- ========================================================================= --}}
        <div x-show="showDetailModal" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" 
             @click.self="showDetailModal = false">
            <div class="bg-white w-full max-w-lg rounded-[32px] p-7 sm:p-8 border border-black/[0.05] shadow-[0_24px_64px_rgba(0,0,0,0.12)] space-y-6 relative max-h-[90vh] overflow-y-auto no-scrollbar font-sans">
                <template x-if="selectedTicket">
                    <div class="space-y-6">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="text-xl font-bold text-[#1D1D1F]" x-text="selectedTicket.venue"></h3>
                                <p class="text-sm text-[#86868B]" x-text="selectedTicket.building"></p>
                            </div>
                            <button @click="showDetailModal = false" class="w-8 h-8 rounded-full bg-[#F5F5F7] hover:bg-gray-200 text-[#1D1D1F] flex items-center justify-center transition">
                                <span class="material-symbols-outlined text-[18px]">close</span>
                            </button>
                        </div>

                        {{-- Status Opsi B: Pure Typographic --}}
                        <div class="p-4 rounded-xl bg-[#F9F9FB] space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-medium text-[#86868B]">Status Verifikasi:</span>
                                <div>
                                    <span class="text-xs font-semibold tracking-wide"
                                          :class="selectedTicket.status === 'approved' ? 'text-emerald-800' : (selectedTicket.status === 'pending' ? 'text-amber-800' : (selectedTicket.status === 'rejected' ? 'text-rose-700' : 'text-slate-500'))"
                                          x-text="selectedTicket.status === 'approved' ? 'Disetujui' : (selectedTicket.status === 'pending' ? 'Menunggu Verifikasi' : (selectedTicket.status === 'rejected' ? 'Ditolak' : (selectedTicket.status === 'cancelled' ? 'Dibatalkan' : selectedTicket.status)))"></span>
                                </div>
                            </div>
                            <div class="text-xs text-[#86868B]" x-text="selectedTicket.officerNote"></div>
                            <template x-if="selectedTicket.reviewed_at">
                                <div class="text-[11px] text-[#86868B] pt-1">
                                    Diverifikasi oleh: <span class="font-medium text-[#1D1D1F]" x-text="selectedTicket.reviewer"></span> • <span x-text="selectedTicket.reviewed_at"></span>
                                </div>
                            </template>
                        </div>

                        {{-- Sesi & Waktu --}}
                        <div class="grid grid-cols-2 gap-3 text-[13px]">
                            <div class="p-3.5 rounded-2xl bg-[#F5F5F7]">
                                <span class="text-[11px] text-[#86868B] block">Tanggal Kegiatan</span>
                                <span class="font-semibold text-[#1D1D1F]" x-text="selectedTicket.date"></span>
                            </div>
                            <div class="p-3.5 rounded-2xl bg-[#F5F5F7]">
                                <span class="text-[11px] text-[#86868B] block">Sesi Waktu</span>
                                <span class="font-semibold text-[#1D1D1F]" x-text="selectedTicket.time"></span>
                            </div>
                        </div>

                        {{-- Keperluan --}}
                        <div>
                            <span class="text-[12px] font-semibold text-[#86868B] block mb-1">Peruntukan Kegiatan:</span>
                            <p class="text-[13px] text-[#1D1D1F] bg-[#FAFAFC] p-3.5 rounded-2xl border border-black/[0.04]" x-text="selectedTicket.purpose"></p>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
                            <template x-if="selectedTicket.canCancel">
                                <button type="button" 
                                        @click="showDetailModal = false; openCancel(selectedTicket)" 
                                        class="px-4 py-2.5 rounded-xl text-xs font-medium text-red-600 hover:underline">
                                    Batalkan Reservasi
                                </button>
                            </template>
                            <button @click="showDetailModal = false" class="px-5 py-2.5 rounded-xl bg-[#1D1D1F] text-white text-[13px] font-medium hover:bg-black transition">
                                Tutup
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- MODAL 2: KONFIRMASI PEMBATALAN MANDIRI (BATAS MINIMAL H-1)                 --}}
        {{-- ========================================================================= --}}
        <div x-show="showCancelModal" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" 
             @click.self="showCancelModal = false">
            <div class="bg-white w-full max-w-md rounded-[32px] p-7 border border-black/[0.05] shadow-[0_24px_64px_rgba(0,0,0,0.12)] space-y-5 font-sans">
                <template x-if="selectedTicket">
                    <div class="space-y-4">
                        <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center">
                            <span class="material-symbols-outlined text-[26px]">event_busy</span>
                        </div>
                        
                        <div>
                            <h3 class="text-[18px] font-bold text-[#1D1D1F]">Batalkan Reservasi Ruangan?</h3>
                            <p class="text-[13px] text-[#86868B] mt-1">
                                Sesuai ketentuan kampus, pembatalan mandiri hanya dapat dilakukan minimal <strong>1 hari sebelum (H-1)</strong> tanggal pelaksanaan kegiatan.
                            </p>
                            <div class="p-3 rounded-xl bg-[#F9F9FB] border border-gray-100 mt-3 text-[12px] text-[#1D1D1F] font-semibold" x-text="selectedTicket.venue"></div>
                        </div>

                        <form :action="'{{ url('/user/reservations') }}/' + selectedTicket.db_id + '/cancel'" method="POST" class="space-y-3">
                            @csrf
                            @method('DELETE')

                            <div>
                                <label for="cancellation_reason" class="block text-xs font-semibold text-[#86868B] uppercase tracking-wider mb-1.5">
                                    Alasan Pembatalan (Opsional)
                                </label>
                                <textarea name="cancellation_reason" 
                                          id="cancellation_reason" 
                                          rows="2" 
                                          placeholder="Tuliskan alasan pembatalan kegiatan Anda..." 
                                          class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] p-3 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-black/10 focus:bg-white transition"></textarea>
                            </div>

                            <div class="flex items-center justify-end gap-3 pt-2">
                                <button type="button" 
                                        @click="showCancelModal = false" 
                                        class="px-4 py-2.5 rounded-xl text-xs font-medium text-[#86868B] hover:text-[#1D1D1F] transition">
                                    Kembali
                                </button>
                                <button type="submit" 
                                        class="px-5 py-2.5 rounded-xl bg-rose-600 text-white text-xs font-semibold hover:bg-rose-700 transition shadow-xs">
                                    Ya, Batalkan Reservasi
                                </button>
                            </div>
                        </form>
                    </div>
                </template>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- MODAL 3: FORM PENGAJUAN RESERVASI RUANGAN BARU                            --}}
        {{-- ========================================================================= --}}
        <div x-show="showCreateModal" 
             x-cloak 
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" 
             @click.self="showCreateModal = false">
            <div class="bg-white w-full max-w-2xl rounded-[32px] p-7 sm:p-9 border border-black/[0.05] shadow-[0_24px_64px_rgba(0,0,0,0.12)] space-y-6 relative max-h-[90vh] overflow-y-auto no-scrollbar font-sans">
                
                <div class="flex items-start justify-between">
                    <div>
                        <h2 class="text-2xl font-bold text-[#1D1D1F]">Pengajuan Reservasi Ruang</h2>
                        <p class="text-sm text-[#86868B] mt-0.5">Pilih ruangan akademik, tanggal pelaksanaan, dan slot waktu.</p>
                    </div>
                    <button type="button" 
                            @click="showCreateModal = false" 
                            class="w-8 h-8 rounded-full bg-[#F5F5F7] hover:bg-gray-200 text-[#1D1D1F] flex items-center justify-center transition">
                        <span class="material-symbols-outlined text-[18px]">close</span>
                    </button>
                </div>

                <form action="{{ route('user.reservations.store') }}" 
                      method="POST" 
                      @submit="submitting = true" 
                      class="space-y-6">
                    @csrf

                    {{-- 1. Pemilihan Fasilitas Bertingkat --}}
                    <div class="space-y-3">
                        <label class="block text-xs font-semibold text-[#86868B] uppercase tracking-wider">
                            Pilih Ruangan Kampus <span class="text-rose-500">*</span>
                        </label>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label for="building-select" class="block text-[11px] font-medium text-[#86868B] mb-1">Gedung</label>
                                <select id="building-select" 
                                        x-model="selectedBuilding" 
                                        @change="onBuildingChange()" 
                                        class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-3.5 py-2.5 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-black/10 focus:bg-white transition">
                                    <template x-for="b in availableBuildings" :key="b">
                                        <option :value="b" x-text="b" :selected="b === selectedBuilding"></option>
                                    </template>
                                </select>
                            </div>

                            <div>
                                <label for="floor-select" class="block text-[11px] font-medium text-[#86868B] mb-1">Lantai</label>
                                <select id="floor-select" 
                                        x-model="selectedFloor" 
                                        @change="onFloorChange()" 
                                        class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-3.5 py-2.5 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-black/10 focus:bg-white transition">
                                    <template x-for="f in availableFloors" :key="f">
                                        <option :value="f" x-text="f" :selected="f === selectedFloor"></option>
                                    </template>
                                </select>
                            </div>

                            <div>
                                <label for="room-select" class="block text-[11px] font-medium text-[#86868B] mb-1">Ruangan</label>
                                <select id="room-select" 
                                        name="facility_id" 
                                        x-model="selectedFacilityId" 
                                        required 
                                        class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-3.5 py-2.5 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-black/10 focus:bg-white transition">
                                    <template x-for="r in availableRooms" :key="r.id">
                                        <option :value="String(r.id)" x-text="r.name" :selected="String(r.id) === selectedFacilityId"></option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        {{-- Ringkasan Ruangan --}}
                        <div class="p-3.5 rounded-xl bg-[#F9F9FB] text-xs text-[#86868B]">
                            <strong class="text-[#1D1D1F]" x-text="currentVenue.name"></strong> • <span x-text="currentVenue.building"></span> • Kapasitas <span x-text="currentVenue.capacity"></span> Kursi
                        </div>
                    </div>

                    {{-- 2. Tanggal & Waktu --}}
                    <div class="space-y-3 pt-4 border-t border-gray-100">
                        <label class="block text-xs font-semibold text-[#86868B] uppercase tracking-wider">
                            Waktu & Tanggal Kegiatan <span class="text-rose-500">*</span>
                        </label>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            <div>
                                <label for="modal-date" class="block text-[11px] font-medium text-[#86868B] mb-1">Tanggal</label>
                                <input type="date" 
                                       id="modal-date" 
                                       name="reservation_date" 
                                       x-model="selectedDate" 
                                       min="{{ date('Y-m-d', strtotime('+1 day')) }}" 
                                       required 
                                       class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-3.5 py-2.5 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-black/10 focus:bg-white transition">
                            </div>

                            <div>
                                <label for="modal-start" class="block text-[11px] font-medium text-[#86868B] mb-1">Mulai</label>
                                <select id="modal-start" 
                                        name="start_time" 
                                        x-model="selectedStart" 
                                        required 
                                        class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-3.5 py-2.5 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-black/10 focus:bg-white transition">
                                    @foreach ($timeSlots as $slot)
                                        <option value="{{ $slot }}">{{ $slot }} WIB</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label for="modal-end" class="block text-[11px] font-medium text-[#86868B] mb-1">Selesai</label>
                                <select id="modal-end" 
                                        name="end_time" 
                                        x-model="selectedEnd" 
                                        required 
                                        class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-3.5 py-2.5 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-black/10 focus:bg-white transition">
                                    @foreach ($timeSlots as $slot)
                                        <option value="{{ $slot }}">{{ $slot }} WIB</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- 3. Detail Kegiatan --}}
                    <div class="space-y-3 pt-4 border-t border-gray-100">
                        <div>
                            <label for="modal-purpose" class="block text-xs font-semibold text-[#86868B] uppercase tracking-wider mb-1.5">
                                Tujuan / Nama Acara <span class="text-rose-500">*</span>
                            </label>
                            <textarea id="modal-purpose" 
                                      name="purpose" 
                                      rows="3" 
                                      required 
                                      placeholder="Jelaskan nama agenda, organisasi penyelenggara, atau keperluan perkuliahan..." 
                                      class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] placeholder:text-[#86868B] p-3.5 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-black/10 focus:bg-white transition"></textarea>
                        </div>

                        <div>
                            <label for="modal-participants" class="block text-xs font-semibold text-[#86868B] uppercase tracking-wider mb-1.5">
                                Estimasi Peserta <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" 
                                   id="modal-participants" 
                                   name="participants_count" 
                                   value="{{ old('participants_count', 10) }}" 
                                   min="1" 
                                   max="500" 
                                   required 
                                   class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-3.5 py-2.5 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-black/10 focus:bg-white transition">
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" 
                                @click="showCreateModal = false" 
                                class="px-5 py-2.5 rounded-xl text-xs font-medium text-[#86868B] hover:text-[#1D1D1F] transition">
                            Batal
                        </button>
                        <button type="submit" 
                                :disabled="submitting" 
                                class="bg-[#1D1D1F] text-white px-7 py-2.5 rounded-xl text-xs font-medium hover:bg-gray-800 transition">
                            <span x-show="!submitting">Kirim Permohonan</span>
                            <span x-show="submitting">Mengirimkan...</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-app-layout>
