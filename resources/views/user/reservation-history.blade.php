{{-- 
  ====================================================================================================
  NAMA FILE      : reservation-history.blade.php
  LOKASI         : resources/views/user/reservation-history.blade.php
  FUNGSIONALITAS : Antarmuka Terpadu Riwayat Reservasi Ruang & Pembatalan Mandiri H-1 (USR-02 & USR-03)
  MODUL CAVA     : 11. Portal Pengguna: Riwayat Reservasi & Pembatalan Mandiri H-1 (USR-02 & USR-03)
  ====================================================================================================

  DESKRIPSI ARSITEKTUR & DESAIN SISTEM:
  1. Halaman Terpadu Swalayan Sivitas (Self-Service Dashboard):
     - Berkas ini menyajikan seluruh rekam jejak pengajuan peminjaman ruangan yang pernah diajukan
       oleh mahasiswa atau dosen yang sedang terotentikasi.
     - Menyediakan metrik status agregat interaktif (Semua, Menunggu, Disetujui, Ditolak/Batal)
       yang berfungsi sebagai tab filter instan.
  
  2. Implementasi Kebijakan Pembatalan Mandiri H-1 (USR-03):
     - Sistem menghitung selisih waktu pelaksanaan kegiatan dengan waktu saat ini (`now()`):
       `$isHMinus1 = now()->diffInSeconds($resDateTime, false) >= 86400`.
     - Jika selisih waktu masih $\ge 24$ jam (H-1) dan status bernilai 'pending' atau 'approved',
       tombol "Batalkan (H-1)" aktif dan memicu modal dialog konfirmasi.
     - Jika waktu tersisa kurang dari 24 jam, tombol pembatalan disembunyikan untuk menjaga
       kestabilan operasional jadwal ruangan kampus.
  
  3. Desain Apple-esque Bento & Status Opsi B (Pure Typographic):
     - Menerapkan sudut lengkung membulat halus (`rounded-[2rem]`), border lembut (`border-gray-100`),
       dan bayangan elegan (`shadow-[0_8px_30px_rgb(0,0,0,0.06)]`).
     - Seluruh status dirender menggunakan tipografi murni berbobot semibold dengan warna semantik
       harmonis (Emerald = Disetujui, Amber = Menunggu, Rose = Ditolak, Slate = Dibatalkan)
       tanpa menggunakan bulatan titik (dot indicator) maupun mikro-ikon.
  
  4. Pengelolaan Modal Berbasis `x-teleport="body"` (Alpine.js):
     - Tiga jendela modal (Detail Tiket, Konfirmasi Pembatalan, dan Formulir Buat Baru) diteleportasi
       langsung ke elemen `<body>` peramban guna mencegah masalah pemotongan overflow (*clipping*)
       dan konflik konteks penumpukan z-index (*stacking context*).
  ====================================================================================================
--}}

{{-- Memanggil komponen master layout khusus portal pengguna dengan prop active="reservation-history" --}}
<x-app-layout title="Riwayat Reservasi Saya" active="reservation-history">
    
    {{-- 
      Blok @php: Transformasi Data Fasilitas untuk Modal Form Pengajuan Terpadu
      - Menyiapkan array slot waktu operasional kampus 30 menitan (07:00 - 20:00 WIB).
      - Menyiapkan struktur hierarki 3 level (Gedung -> Lantai -> Ruangan) jika pengguna
        memilih mengajukan permohonan baru langsung dari jendela modal di halaman ini.
    --}}
    @php
        // Array slot waktu operasional baku per 30 menit
        $timeSlots = [
            '07:00', '07:30', '08:00', '08:30', '09:00', '09:30',
            '10:00', '10:30', '11:00', '11:30', '12:00', '12:30',
            '13:00', '13:30', '14:00', '14:30', '15:00', '15:30',
            '16:00', '16:30', '17:00', '17:30', '18:00', '18:30',
            '19:00', '19:30', '20:00'
        ];

        // Format data fasilitas ke JSON untuk hierarki 3 dropdown bertingkat di formulir modal
        $facilitiesMap = [];
        $hierarchy     = [];

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

                $bName     = $f->building;
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

        // Penentuan nilai awal (fallback) untuk form modal pengajuan baru
        $initFacility   = isset($facilities) ? $facilities->first() : null;
        $initBuilding   = $initFacility ? $initFacility->building : (array_key_first($hierarchy) ?? '');
        $initFloor      = 'Lantai 1';
        if ($initFacility && preg_match('/Lantai\s*(\d+)/i', $initFacility->floor_location ?? '', $matches)) {
            $initFloor = 'Lantai ' . $matches[1];
        } elseif ($initFacility && !empty($initFacility->floor_location)) {
            $initFloor = $initFacility->floor_location;
        }
        $initFacilityId = $initFacility ? (string) $initFacility->id : '';
    @endphp

    {{-- 
      Pembungkus Konten Utama dengan State Reaktif Alpine.js:
      - showCreateModal : Menentukan visibilitas modal form reservasi baru.
      - showDetailModal : Menentukan visibilitas modal rincian informasi tiket.
      - showCancelModal : Menentukan visibilitas modal dialog konfirmasi pembatalan mandiri H-1.
      - selectedTicket  : Objek data tiket yang sedang dipilih untuk ditinjau atau dibatalkan.
      - submitting      : State pencegah double-post saat pengiriman form.
    --}}
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

        // Getter untuk daftar gedung yang tersedia
        get availableBuildings() {
            return Object.keys(this.hierarchy);
        },

        // Getter untuk daftar lantai sesuai gedung terpilih
        get availableFloors() {
            if (!this.selectedBuilding || !this.hierarchy[this.selectedBuilding]) {
                return [];
            }
            return Object.keys(this.hierarchy[this.selectedBuilding]);
        },

        // Getter untuk daftar ruangan berdasarkan kombinasi gedung & lantai
        get availableRooms() {
            if (!this.selectedBuilding || !this.selectedFloor || 
                !this.hierarchy[this.selectedBuilding] || 
                !this.hierarchy[this.selectedBuilding][this.selectedFloor]) {
                return [];
            }
            return this.hierarchy[this.selectedBuilding][this.selectedFloor];
        },

        // Handler perubahan dropdown gedung
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

        // Handler perubahan dropdown lantai
        onFloorChange() {
            const rooms = this.availableRooms;
            if (rooms.length > 0) {
                this.selectedFacilityId = String(rooms[0].id);
            } else {
                this.selectedFacilityId = '';
            }
        },

        // Getter data detail ruangan untuk ringkasan instan
        get currentVenue() {
            return this.facilitiesData[this.selectedFacilityId] || {
                name: 'Pilih Ruangan Kampus',
                building: 'Lokasi Gedung',
                capacity: '-',
                category: '-'
            };
        },

        // Membuka modal detail tiket dengan data spesifik
        openDetail(t) {
            this.selectedTicket = t;
            this.showDetailModal = true;
        },

        // Membuka modal konfirmasi pembatalan mandiri H-1
        openCancel(t) {
            this.selectedTicket = t;
            this.showCancelModal = true;
        }
    }" class="space-y-10 font-sans text-[#1D1D1F]">

        {{-- Toolbar Aksi Cepat: Tombol Pengajuan Permohonan Baru --}}
        <div class="flex items-center justify-end">
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

        {{-- Flash Session Error Validasi --}}
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

        {{-- ========================================================================================== --}}
        {{-- SEKSI 1: REKAPITULASI METRIK BENTO (4 TAB FILTER STATUS INTERAKTIF)                        --}}
        {{-- ========================================================================================== --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            {{-- Metrik 1: Semua Tiket Milik Pengguna --}}
            <a href="{{ request()->fullUrlWithQuery(['status' => 'all', 'page' => 1]) }}" 
               class="p-6 rounded-[2rem] transition-all {{ $activeStatus === 'all' ? 'bg-[#1D1D1F] text-white shadow-sm' : 'bg-[#F9F9FB] text-[#1D1D1F] hover:bg-white hover:shadow-[0_8px_24px_rgba(0,0,0,0.06)]' }}">
                <span class="text-xs font-medium block opacity-70">Semua Tiket</span>
                <span class="text-2xl sm:text-3xl font-bold mt-2 block">{{ $counts['all'] ?? 0 }}</span>
            </a>

            {{-- Metrik 2: Menunggu Verifikasi Petugas (Status: Pending) --}}
            <a href="{{ request()->fullUrlWithQuery(['status' => 'pending', 'page' => 1]) }}" 
               class="p-6 rounded-[2rem] transition-all {{ $activeStatus === 'pending' ? 'bg-[#1D1D1F] text-white shadow-sm' : 'bg-[#F9F9FB] text-[#1D1D1F] hover:bg-white hover:shadow-[0_8px_24px_rgba(0,0,0,0.06)]' }}">
                <span class="text-xs font-medium block opacity-70">Menunggu</span>
                <span class="text-2xl sm:text-3xl font-bold mt-2 block text-amber-700 {{ $activeStatus === 'pending' ? '!text-white' : '' }}">{{ $counts['pending'] ?? 0 }}</span>
            </a>

            {{-- Metrik 3: Disetujui Petugas (Status: Approved) --}}
            <a href="{{ request()->fullUrlWithQuery(['status' => 'approved', 'page' => 1]) }}" 
               class="p-6 rounded-[2rem] transition-all {{ $activeStatus === 'approved' ? 'bg-[#1D1D1F] text-white shadow-sm' : 'bg-[#F9F9FB] text-[#1D1D1F] hover:bg-white hover:shadow-[0_8px_24px_rgba(0,0,0,0.06)]' }}">
                <span class="text-xs font-medium block opacity-70">Disetujui</span>
                <span class="text-2xl sm:text-3xl font-bold mt-2 block text-emerald-800 {{ $activeStatus === 'approved' ? '!text-white' : '' }}">{{ $counts['approved'] ?? 0 }}</span>
            </a>

            {{-- Metrik 4: Ditolak atau Dibatalkan Mandiri (Status: Rejected / Cancelled) --}}
            <a href="{{ request()->fullUrlWithQuery(['status' => 'cancelled', 'page' => 1]) }}" 
               class="p-6 rounded-[2rem] transition-all {{ in_array($activeStatus, ['rejected', 'cancelled']) ? 'bg-[#1D1D1F] text-white shadow-sm' : 'bg-[#F9F9FB] text-[#1D1D1F] hover:bg-white hover:shadow-[0_8px_24px_rgba(0,0,0,0.06)]' }}">
                <span class="text-xs font-medium block opacity-70">Ditolak / Batal</span>
                <span class="text-2xl sm:text-3xl font-bold mt-2 block text-rose-700 {{ in_array($activeStatus, ['rejected', 'cancelled']) ? '!text-white' : '' }}">{{ ($counts['rejected'] ?? 0) + ($counts['cancelled'] ?? 0) }}</span>
            </a>
        </div>

        {{-- ========================================================================================== --}}
        {{-- SEKSI 2: FORM PENCARIAN TIKET & INDIKATOR FILTER AKTIF (SEARCH & FILTER PILLS)            --}}
        {{-- ========================================================================================== --}}
        <div id="daftar-reservasi" class="space-y-3 scroll-mt-6">
            <form action="{{ url()->current() }}" method="GET" class="w-full">
                {{-- Menyimpan parameter status yang sedang aktif saat pencarian kata kunci dieksekusi --}}
                @if (request('status') && request('status') !== 'all')
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="relative group">
                    <div class="absolute -inset-1 bg-gradient-to-r from-blue-100 to-indigo-100 rounded-2xl blur opacity-25 group-hover:opacity-50 transition duration-1000 group-hover:duration-200"></div>
                    <div class="relative bg-white rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-1.5 flex items-center border border-gray-100">
                        <span class="material-symbols-outlined text-gray-400 ml-3 mr-2 text-[20px]">search</span>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Cari berdasarkan nama ruang, gedung, atau kegiatan..." 
                               class="flex-1 py-2.5 px-2 border-0 focus:ring-0 focus:border-transparent outline-none text-sm md:text-base bg-transparent text-[#1D1D1F] placeholder:text-[#86868B]" 
                               autocomplete="off">
                        @if (request('search'))
                            <a href="{{ request()->fullUrlWithQuery(['search' => null, 'page' => 1]) }}" 
                               class="text-gray-400 hover:text-[#1D1D1F] p-1 mr-1 transition rounded-lg hover:bg-gray-50 flex items-center justify-center" 
                               title="Hapus kata kunci pencarian">
                                <span class="material-symbols-outlined text-[18px]">close</span>
                            </a>
                        @endif
                        <button type="submit" 
                                class="bg-[#1D1D1F] text-white px-5 py-2.5 rounded-xl text-sm font-medium hover:bg-gray-800 transition shrink-0 cursor-pointer shadow-sm">
                            Cari
                        </button>
                    </div>
                </div>
            </form>

            {{-- Lencana (Pills) Filter Aktif yang Sedang Diterapkan --}}
            @if ((request('status') && request('status') !== 'all') || request()->filled('search'))
                <div class="flex flex-wrap items-center gap-2 pt-1 pb-1">
                    <span class="text-xs font-medium text-gray-500 py-1 mr-1">Filter Aktif:</span>

                    {{-- Pill Status --}}
                    @if (request('status') && request('status') !== 'all')
                        @php
                            $statusLabels = [
                                'pending'   => 'Menunggu',
                                'approved'  => 'Disetujui',
                                'rejected'  => 'Ditolak',
                                'cancelled' => 'Ditolak / Batal',
                            ];
                            $statusLabel = $statusLabels[request('status')] ?? ucfirst(request('status'));
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-gray-100 text-[#1D1D1F] border border-gray-200 shadow-sm">
                            <span>Status: {{ $statusLabel }}</span>
                            <a href="{{ request()->fullUrlWithQuery(['status' => 'all', 'page' => 1]) }}" 
                               class="text-gray-400 hover:text-rose-600 transition" 
                               title="Hapus filter status">
                                <span class="material-symbols-outlined text-[14px] font-bold">close</span>
                            </a>
                        </span>
                    @endif

                    {{-- Pill Kata Kunci Pencarian --}}
                    @if (request()->filled('search'))
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-blue-50 text-blue-800 border border-blue-200 shadow-sm">
                            <span>Pencarian: "{{ request('search') }}"</span>
                            <a href="{{ request()->fullUrlWithQuery(['search' => null, 'page' => 1]) }}" 
                               class="text-blue-500 hover:text-blue-900 transition" 
                               title="Hapus kata kunci pencarian">
                                <span class="material-symbols-outlined text-[14px] font-bold">close</span>
                            </a>
                        </span>
                    @endif

                    {{-- Reset Seluruh Filter ke Tampilan Awal --}}
                    <a href="{{ route('user.reservation-history') }}" 
                       class="text-xs font-medium text-gray-500 hover:text-rose-600 underline px-2 py-1 transition ml-1">
                        Hapus Semua
                    </a>
                </div>
            @endif
        </div>

        {{-- ========================================================================================== --}}
        {{-- SEKSI 3: DAFTAR KARTU RIWAYAT RESERVASI (STATUS OPSI B: PURE TYPOGRAPHIC)                  --}}
        {{-- ========================================================================================== --}}
        <div class="space-y-4">
            @forelse ($reservations as $res)
                {{-- 
                  Evaluasi Kelayakan Pembatalan Mandiri H-1 (BR-USR03-02):
                  - Menghitung apakah waktu mulai kegiatan masih berjarak >= 24 jam (86.400 detik) dari saat ini
                  - Hanya tiket berstatus 'pending' atau 'approved' yang boleh dibatalkan mandiri
                --}}
                @php
                    $isHMinus1 = false;
                    try {
                        $resDateTime = \Carbon\Carbon::parse($res->reservation_date)->setTimeFromTimeString($res->start_time);
                        $isHMinus1   = in_array($res->status, ['pending', 'approved']) && now()->diffInSeconds($resDateTime, false) >= 86400;
                    } catch (\Exception $e) {
                        $isHMinus1 = false;
                    }

                    // Menyiapkan array representasi tiket untuk diparsing ke modal Alpine.js
                    $ticketData = [
                        'db_id'        => $res->id,
                        'id'           => $res->ticket_code,
                        'venue'        => $res->facility->name ?? 'Fasilitas Kampus',
                        'building'     => ($res->facility->building ?? 'Gedung Kampus') . ($res->facility->floor_location ? ' • ' . $res->facility->floor_location : ''),
                        'capacity'     => ($res->facility->capacity ?? '-') . ' Kursi',
                        'date'         => \Carbon\Carbon::parse($res->reservation_date)->isoFormat('dddd, D MMMM Y'),
                        'time'         => substr($res->start_time, 0, 5) . ' - ' . substr($res->end_time, 0, 5) . ' WIB',
                        'slots'        => $res->total_slots ?? 1,
                        'purpose'      => $res->purpose,
                        'participants' => ($res->participants_count ?? 1) . ' Orang',
                        'status'       => $res->status,
                        'reviewer'     => $res->reviewer->name ?? 'Belum Diverifikasi',
                        'reviewed_at'  => $res->reviewed_at ? \Carbon\Carbon::parse($res->reviewed_at)->isoFormat('dddd, D MMMM Y • H:i') : null,
                        'officerNote'  => $res->rejection_reason ?? $res->cancellation_reason ?? ($res->status === 'approved' ? 'Telah disetujui oleh Petugas Sarpras. Harap menjaga kebersihan fasilitas.' : ($res->status === 'cancelled' ? 'Reservasi telah dibatalkan.' : 'Sedang dalam antrean evaluasi dan verifikasi staf sarpras.')),
                        'canCancel'    => $isHMinus1,
                    ];
                @endphp

                {{-- Kartu Riwayat Reservasi Perorangan --}}
                <div class="bg-white p-7 sm:p-8 rounded-[2rem] border border-gray-100 hover:shadow-[0_8px_30px_rgb(0,0,0,0.06)] transition duration-300">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                        
                        <div class="space-y-2 flex-1">
                            {{-- Label Status Opsi B: Pure Typographic Tanpa Bullet/Dot --}}
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

                            {{-- Nama Fasilitas dan Lokasi Gedung --}}
                            <h3 class="text-xl font-bold text-[#1D1D1F]">
                                {{ $res->facility->name ?? 'Fasilitas Kampus' }} • {{ $res->facility->building ?? 'Gedung' }}
                            </h3>
                            <p class="text-sm text-[#86868B]">{{ $res->purpose }}</p>
                            
                            {{-- Rincian Tanggal, Jam, dan Kapasitas Ruangan --}}
                            <div class="text-xs text-[#86868B] pt-1">
                                {{ \Carbon\Carbon::parse($res->reservation_date)->isoFormat('dddd, D MMMM Y') }} • {{ substr($res->start_time, 0, 5) }} - {{ substr($res->end_time, 0, 5) }} WIB • Kapasitas {{ $res->facility->capacity ?? '-' }} Kursi
                            </div>
                        </div>
                        
                        {{-- Tombol Aksi: Lihat Detail Tiket & Batalkan Mandiri H-1 --}}
                        <div class="flex items-center gap-3 shrink-0 pt-4 lg:pt-0 border-t lg:border-t-0 border-gray-100">
                            {{-- Tombol Buka Modal Detail Tiket --}}
                            <button type="button" 
                                    @click="openDetail({{ json_encode($ticketData) }})" 
                                    class="bg-[#1D1D1F] text-white px-5 py-2.5 rounded-xl text-xs font-medium hover:bg-gray-800 transition cursor-pointer">
                                Lihat Detail Tiket
                            </button>

                            {{-- Tombol Pembatalan Mandiri H-1 (Hanya Aktif Jika Sisa Waktu >= 24 Jam) --}}
                            @if ($isHMinus1)
                                <button type="button" 
                                        @click="openCancel({{ json_encode($ticketData) }})" 
                                        class="text-xs font-medium text-red-600 hover:underline px-3 py-2 cursor-pointer">
                                    Batalkan (H-1)
                                </button>
                            @endif
                        </div>

                    </div>
                </div>
            @empty
                {{-- Keadaan Kosong (Empty State) Jika Tidak Ada Data Reservasi --}}
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

        {{-- Navigasi Paginasi Laravel (Pagination Links) --}}
        @if ($reservations->hasPages())
            <div class="pt-4">
                {{ $reservations->links() }}
            </div>
        @endif

        {{-- ========================================================================================== --}}
        {{-- MODAL 1: DETAIL LENGKAP TIKET RESERVASI (DITELEPORTASI KE <body>)                          --}}
        {{-- ========================================================================================== --}}
        <template x-teleport="body">
            <div x-show="showDetailModal" 
                 x-cloak 
                 class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6 bg-black/60 backdrop-blur-sm overflow-y-auto" 
                 @click.self="showDetailModal = false">
                <div class="bg-white w-full max-w-lg rounded-[32px] p-7 sm:p-8 border border-black/[0.05] shadow-[0_24px_64px_rgba(0,0,0,0.12)] space-y-6 relative max-h-[85vh] overflow-y-auto no-scrollbar font-sans my-auto">
                    <template x-if="selectedTicket">
                        <div class="space-y-6">
                            {{-- Header Modal Detail --}}
                            <div class="flex items-start justify-between">
                                <div>
                                    <h3 class="text-xl font-bold text-[#1D1D1F]" x-text="selectedTicket.venue"></h3>
                                    <p class="text-sm text-[#86868B]" x-text="selectedTicket.building"></p>
                                </div>
                                <button @click="showDetailModal = false" class="w-8 h-8 rounded-full bg-[#F5F5F7] hover:bg-gray-200 text-[#1D1D1F] flex items-center justify-center transition cursor-pointer">
                                    <span class="material-symbols-outlined text-[18px]">close</span>
                                </button>
                            </div>

                            {{-- Rincian Status Verifikasi & Catatan Petugas --}}
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

                            {{-- Tanggal dan Jam Kegiatan --}}
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

                            {{-- Deskripsi Peruntukan Kegiatan --}}
                            <div>
                                <span class="text-[12px] font-semibold text-[#86868B] block mb-1">Peruntukan Kegiatan:</span>
                                <p class="text-[13px] text-[#1D1D1F] bg-[#FAFAFC] p-3.5 rounded-2xl border border-black/[0.04]" x-text="selectedTicket.purpose"></p>
                            </div>

                            {{-- Tombol Aksi Bawah Modal Detail --}}
                            <div class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
                                <template x-if="selectedTicket.canCancel">
                                    <button type="button" 
                                            @click="showDetailModal = false; openCancel(selectedTicket)" 
                                            class="px-4 py-2.5 rounded-xl text-xs font-medium text-red-600 hover:underline cursor-pointer">
                                        Batalkan Reservasi
                                    </button>
                                </template>
                                <button @click="showDetailModal = false" class="px-5 py-2.5 rounded-xl bg-[#1D1D1F] text-white text-[13px] font-medium hover:bg-black transition cursor-pointer">
                                    Tutup
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        {{-- ========================================================================================== --}}
        {{-- MODAL 2: KONFIRMASI PEMBATALAN MANDIRI H-1 (DITELEPORTASI KE <body>)                       --}}
        {{-- ========================================================================================== --}}
        <template x-teleport="body">
            <div x-show="showCancelModal" 
                 x-cloak 
                 class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6 bg-black/60 backdrop-blur-sm overflow-y-auto" 
                 @click.self="showCancelModal = false">
                <div class="bg-white w-full max-w-md rounded-[32px] p-7 border border-black/[0.05] shadow-[0_24px_64px_rgba(0,0,0,0.12)] space-y-5 font-sans my-auto">
                    <template x-if="selectedTicket">
                        <div class="space-y-4">
                            {{-- Ikon Peringatan Pembatalan --}}
                            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center">
                                <span class="material-symbols-outlined text-[26px]">event_busy</span>
                            </div>
                            
                            {{-- Keterangan Aturan Pembatalan H-1 --}}
                            <div>
                                <h3 class="text-[18px] font-bold text-[#1D1D1F]">Batalkan Reservasi Ruangan?</h3>
                                <p class="text-[13px] text-[#86868B] mt-1">
                                    Sesuai ketentuan kampus, pembatalan mandiri hanya dapat dilakukan minimal <strong>1 hari sebelum (H-1)</strong> tanggal pelaksanaan kegiatan.
                                </p>
                                <div class="p-3 rounded-xl bg-[#F9F9FB] border border-gray-100 mt-3 text-[12px] text-[#1D1D1F] font-semibold" x-text="selectedTicket.venue"></div>
                            </div>

                            {{-- Form Eksekusi Pembatalan via Method DELETE ke user.reservations.cancel --}}
                            <form :action="'{{ url('/user/reservations') }}/' + selectedTicket.db_id + '/cancel'" method="POST" class="space-y-3">
                                @csrf
                                @method('DELETE')

                                {{-- Textarea Alasan Pembatalan Opsional --}}
                                <div>
                                    <label for="cancellation_reason" class="block text-xs font-semibold text-[#86868B] uppercase tracking-wider mb-1.5">
                                        Alasan Pembatalan (Opsional)
                                    </label>
                                    <textarea name="cancellation_reason" 
                                              id="cancellation_reason" 
                                              rows="2" 
                                              maxlength="500" 
                                              placeholder="Tuliskan alasan pembatalan kegiatan Anda (maks. 500 karakter)..." 
                                              class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] p-3 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-black/10 focus:bg-white transition"></textarea>
                                </div>

                                {{-- Tombol Aksi Modal Pembatalan --}}
                                <div class="flex items-center justify-end gap-3 pt-2">
                                    <button type="button" 
                                            @click="showCancelModal = false" 
                                            class="px-4 py-2.5 rounded-xl text-xs font-medium text-[#86868B] hover:text-[#1D1D1F] transition cursor-pointer">
                                        Kembali
                                    </button>
                                    <button type="submit" 
                                            class="px-5 py-2.5 rounded-xl bg-rose-600 text-white text-xs font-semibold hover:bg-rose-700 transition shadow-xs cursor-pointer">
                                        Ya, Batalkan Reservasi
                                    </button>
                                </div>
                            </form>
                        </div>
                    </template>
                </div>
            </div>
        </template>

        {{-- ========================================================================================== --}}
        {{-- MODAL 3: FORM PENGAJUAN RESERVASI RUANGAN BARU (DITELEPORTASI KE <body>)                   --}}
        {{-- ========================================================================================== --}}
        <template x-teleport="body">
            <div x-show="showCreateModal" 
                 x-cloak 
                 class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-6 bg-black/60 backdrop-blur-sm overflow-y-auto" 
                 @click.self="showCreateModal = false">
                <div class="bg-white w-full max-w-2xl rounded-[32px] p-7 sm:p-9 border border-black/[0.05] shadow-[0_24px_64px_rgba(0,0,0,0.12)] space-y-6 relative max-h-[85vh] overflow-y-auto no-scrollbar font-sans my-auto">
                    
                    {{-- Header Modal Buat Reservasi --}}
                    <div class="flex items-start justify-between">
                        <div>
                            <h2 class="text-2xl font-bold text-[#1D1D1F]">Pengajuan Reservasi Ruang</h2>
                            <p class="text-sm text-[#86868B] mt-0.5">Pilih ruangan akademik, tanggal pelaksanaan, dan slot waktu.</p>
                        </div>
                        <button type="button" 
                                @click="showCreateModal = false" 
                                class="w-8 h-8 rounded-full bg-[#F5F5F7] hover:bg-gray-200 text-[#1D1D1F] flex items-center justify-center transition cursor-pointer">
                            <span class="material-symbols-outlined text-[18px]">close</span>
                        </button>
                    </div>

                    {{-- Formulir Input Pengajuan Reservasi --}}
                    <form action="{{ route('user.reservations.store') }}" 
                          method="POST" 
                          @submit="submitting = true" 
                          class="space-y-6">
                        @csrf

                        {{-- 1. Pemilihan Fasilitas Bertingkat 3 Level --}}
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
                                            class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-3.5 py-2.5 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-black/10 focus:bg-white transition cursor-pointer">
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
                                            class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-3.5 py-2.5 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-black/10 focus:bg-white transition cursor-pointer">
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
                                            class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-3.5 py-2.5 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-black/10 focus:bg-white transition cursor-pointer">
                                        <template x-for="r in availableRooms" :key="r.id">
                                            <option :value="String(r.id)" x-text="r.name" :selected="String(r.id) === selectedFacilityId"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>

                            {{-- Ringkasan Ruangan Terpilih --}}
                            <div class="p-3.5 rounded-xl bg-[#F9F9FB] text-xs text-[#86868B]">
                                <strong class="text-[#1D1D1F]" x-text="currentVenue.name"></strong> • <span x-text="currentVenue.building"></span> • Kapasitas <span x-text="currentVenue.capacity"></span> Kursi
                            </div>
                        </div>

                        {{-- 2. Tanggal & Waktu Pelaksanaan --}}
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
                                           class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-3.5 py-2.5 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-black/10 focus:bg-white transition cursor-pointer">
                                </div>

                                <div>
                                    <label for="modal-start" class="block text-[11px] font-medium text-[#86868B] mb-1">Mulai</label>
                                    <select id="modal-start" 
                                            name="start_time" 
                                            x-model="selectedStart" 
                                            required 
                                            class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-3.5 py-2.5 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-black/10 focus:bg-white transition cursor-pointer">
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
                                            class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-3.5 py-2.5 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-black/10 focus:bg-white transition cursor-pointer">
                                        @foreach ($timeSlots as $slot)
                                            <option value="{{ $slot }}">{{ $slot }} WIB</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- 3. Detail Tujuan & Estimasi Peserta --}}
                        <div class="space-y-3 pt-4 border-t border-gray-100">
                            <div>
                                <label for="modal-purpose" class="block text-xs font-semibold text-[#86868B] uppercase tracking-wider mb-1.5">
                                    Tujuan / Nama Acara <span class="text-rose-500">*</span>
                                </label>
                                <textarea id="modal-purpose" 
                                          name="purpose" 
                                          rows="3" 
                                          required 
                                          minlength="10" 
                                          maxlength="500" 
                                          placeholder="Jelaskan nama agenda, organisasi penyelenggara, atau keperluan perkuliahan (10 - 500 karakter)..." 
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
                                       max="2000" 
                                       required 
                                       class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-3.5 py-2.5 rounded-xl text-xs focus:outline-none focus:ring-1 focus:ring-black/10 focus:bg-white transition">
                            </div>
                        </div>

                        {{-- Tombol Kirim Form Modal --}}
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                            <button type="button" 
                                    @click="showCreateModal = false" 
                                    class="px-5 py-2.5 rounded-xl text-xs font-medium text-[#86868B] hover:text-[#1D1D1F] transition cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" 
                                    :disabled="submitting" 
                                    class="bg-[#1D1D1F] text-white px-7 py-2.5 rounded-xl text-xs font-medium hover:bg-gray-800 transition cursor-pointer">
                                <span x-show="!submitting">Kirim Permohonan</span>
                                <span x-show="submitting">Mengirimkan...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

    </div>
</x-app-layout>
