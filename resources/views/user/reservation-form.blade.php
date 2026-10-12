{{-- 
  ====================================================================================================
  NAMA FILE      : reservation-form.blade.php
  LOKASI         : resources/views/user/reservation-form.blade.php
  FUNGSIONALITAS : Antarmuka Formulir Pengajuan Reservasi Ruang & Fasilitas Kampus Mandiri (USR-01)
  MODUL CAVA     : 10. Portal Pengguna: Formulir Pengajuan Reservasi Ruangan Mandiri (USR-01)
  ====================================================================================================

  DESKRIPSI ARSITEKTUR & DESAIN SISTEM:
  1. Integrasi Master Layout:
     - Menggunakan komponen Blade `<x-app-layout title="Form Pengajuan Reservasi" active="reservation-form">`
       sehingga navigasi header dan sidebar pengguna tetap sinkron dan konsisten.
  
  2. Pendekatan Desain Apple-esque Bento UI:
     - Menggunakan kontainer kartu modular bersudut membulat (`rounded-[2rem]`), border halus (`border-gray-100`),
       dan bayangan elevasi lembut (`shadow-[0_8px_30px_rgb(0,0,0,0.03)]`).
     - Input field menggunakan gaya borderless soft gray (`bg-[#F5F5F7] border-0 text-[#1D1D1F]`) dengan
       fokus ring minimalis hitam halus (`focus:ring-black/10 focus:bg-white`).
  
  3. Reaktivitas Klien Bertingkat 3-Level (Gedung -> Lantai -> Ruangan):
     - Membangun hierarki fasilitas langsung di server PHP (`@php`) dan mengekspornya ke JSON untuk
       dikonsumsi oleh Alpine.js (`x-data`).
     - Ketika pengguna memilih Gedung, daftar Lantai diperbarui otomatis; ketika Lantai dipilih,
       daftar Ruangan diperbarui seketika tanpa memerlukan AJAX request tambahan (Zero Network Latency).
  
  4. Panel Ringkasan Sticky Bento (Live Preview):
     - Kolom kanan (5 kolom grid) diposisikan secara melayang tetap (`lg:sticky lg:top-24`)
       untuk menyajikan ringkasan instan secara real-time mengenai ruangan yang dipilih, estimasi tanggal,
       durasi sesi waktu, serta kebijakan operasional pembatalan H-1 kampus.
  ====================================================================================================
--}}

{{-- Memanggil komponen master layout khusus portal pengguna dengan prop active="reservation-form" --}}
<x-app-layout title="Form Pengajuan Reservasi" active="reservation-form">
    
    {{-- 
      Blok @php: Transformasi Data Fasilitas Menjadi Struktur Hierarki & Peta Objek JSON
      - Menyiapkan array slot waktu operasional kampus 30 menitan (07:00 - 20:00 WIB).
      - Mengonversi koleksi Eloquent $facilities menjadi array hierarki bersarang [Gedung][Lantai][Ruangan].
    --}}
    @php
        // Array titik slot waktu baku per 30 menit dalam rentang operasional 07:00 hingga 20:00 WIB
        $timeSlots = [
            '07:00', '07:30', '08:00', '08:30', '09:00', '09:30',
            '10:00', '10:30', '11:00', '11:30', '12:00', '12:30',
            '13:00', '13:30', '14:00', '14:30', '15:00', '15:30',
            '16:00', '16:30', '17:00', '17:30', '18:00', '18:30',
            '19:00', '19:30', '20:00'
        ];

        // Inisialisasi peta asosiatif fasilitas untuk pencarian instan (O(1) lookup) pada live summary preview
        $facilitiesMap = [];
        // Inisialisasi struktur hierarki 3 level: [Nama Gedung] => [Nama Lantai] => [Daftar Ruangan]
        $hierarchy = [];

        // Melakukan iterasi seluruh koleksi master fasilitas aktif
        foreach ($facilities as $f) {
            // Memetakan atribut fasilitas berdasarkan ID untuk akses cepat di klien
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
            
            // Ekstraksi nomor lantai menggunakan regex jika string mengandung pola "Lantai X"
            if (preg_match('/Lantai\s*(\d+)/i', $f->floor_location ?? '', $matches)) {
                $floorName = 'Lantai ' . $matches[1];
            } elseif (!empty($f->floor_location)) {
                $floorName = $f->floor_location;
            }

            // Memastikan kunci gedung terdaftar dalam array hierarki
            if (!isset($hierarchy[$bName])) {
                $hierarchy[$bName] = [];
            }
            // Memastikan kunci lantai terdaftar di dalam gedung terkait
            if (!isset($hierarchy[$bName][$floorName])) {
                $hierarchy[$bName][$floorName] = [];
            }

            // Memasukkan entitas ruangan ke dalam sub-kunci lantai
            $hierarchy[$bName][$floorName][] = [
                'id'       => $f->id,
                'code'     => $f->code,
                'name'     => $f->name,
                'capacity' => $f->capacity,
            ];
        }

        // Menentukan nilai awal (fallback) untuk inisialisasi state Alpine.js
        $draft          = $draft ?? [];
        $initFacilityId = old('facility_id', $draft['facility_id'] ?? ($selectedFacilityId ?? ($facilities->first()->id ?? '1')));
        $initFacility   = $facilities->firstWhere('id', $initFacilityId) ?? $facilities->first();
        $initBuilding   = $initFacility ? $initFacility->building : (array_key_first($hierarchy) ?? '');

        $initFloor = 'Lantai 1';
        if ($initFacility && preg_match('/Lantai\s*(\d+)/i', $initFacility->floor_location ?? '', $matches)) {
            $initFloor = 'Lantai ' . $matches[1];
        } elseif ($initFacility && !empty($initFacility->floor_location)) {
            $initFloor = $initFacility->floor_location;
        }
    @endphp

    {{-- 
      Pembungkus Utama Form dengan Reactive State Management Alpine.js:
      - submitting         : Mencegah double-submission saat tombol kirim diklik.
      - hierarchy          : Menyimpan pohon hierarki Gedung -> Lantai -> Ruangan dalam format JSON.
      - facilitiesData     : Peta data detail seluruh fasilitas untuk preview instan.
      - selectedBuilding   : Gedung aktif yang dipilih pada dropdown 1.
      - selectedFloor      : Lantai aktif yang dipilih pada dropdown 2.
      - selectedFacilityId : Ruangan terpilih yang nilainya dikirim ke server via input 'facility_id'.
      - selectedDate       : Tanggal pelaksanaan kegiatan (default: H+1 besok).
      - selectedStart      : Jam mulai pelaksanaan (default: 09:00).
      - selectedEnd        : Jam selesai pelaksanaan (default: 11:00).
    --}}
    <div x-data="{
        submitting: false,
        hierarchy: {{ json_encode($hierarchy) }},
        facilitiesData: {{ json_encode($facilitiesMap) }},
        selectedBuilding: '{{ $initBuilding }}',
        selectedFloor: '{{ $initFloor }}',
        selectedFacilityId: '{{ $initFacilityId }}',
        selectedDate: '{{ old('reservation_date', $draft['reservation_date'] ?? date('Y-m-d', strtotime('+1 day'))) }}',
        selectedStart: '{{ old('start_time', $draft['start_time'] ?? '09:00') }}',
        selectedEnd: '{{ old('end_time', $draft['end_time'] ?? '11:00') }}',

        // Getter untuk mengambil seluruh daftar gedung yang tersedia
        get availableBuildings() {
            return Object.keys(this.hierarchy);
        },

        // Getter untuk mengambil daftar lantai berdasarkan gedung yang sedang dipilih
        get availableFloors() {
            if (!this.selectedBuilding || !this.hierarchy[this.selectedBuilding]) {
                return [];
            }
            return Object.keys(this.hierarchy[this.selectedBuilding]);
        },

        // Getter untuk mengambil daftar ruangan berdasarkan kombinasi gedung & lantai yang dipilih
        get availableRooms() {
            if (!this.selectedBuilding || !this.selectedFloor || 
                !this.hierarchy[this.selectedBuilding] || 
                !this.hierarchy[this.selectedBuilding][this.selectedFloor]) {
                return [];
            }
            return this.hierarchy[this.selectedBuilding][this.selectedFloor];
        },

        // Event handler saat pengguna mengganti gedung
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

        // Event handler saat pengguna mengganti lantai
        onFloorChange() {
            const rooms = this.availableRooms;
            if (rooms.length > 0) {
                this.selectedFacilityId = String(rooms[0].id);
            } else {
                this.selectedFacilityId = '';
            }
        },

        // Getter untuk mengambil objek detail ruangan terpilih guna ditampilkan pada ringkasan Bento
        get currentVenue() {
            return this.facilitiesData[this.selectedFacilityId] || {
                name: 'Pilih Fasilitas Kampus',
                building: 'Lokasi Gedung',
                capacity: '-',
                category: '-'
            };
        }
    }" class="space-y-8">

        {{-- Top Navigation & Indikator Status Opsi B: Pure Typographic --}}
        <div class="flex items-center justify-between">
            <a href="{{ route('user.reservation-history') }}" class="inline-flex items-center gap-1.5 text-sm text-[#86868B] hover:text-[#1D1D1F] font-medium transition">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                <span>Kembali ke Riwayat Reservasi</span>
            </a>
            <span class="text-xs font-semibold tracking-wide text-emerald-800">
                Pengecekan Bentrok Jadwal Aktif
            </span>
        </div>

        {{-- Banner Error Validasi Server (Soft Apple Tone dengan latar rose-50 lembut) --}}
        @if ($errors->any())
            <div class="p-6 rounded-[2rem] bg-rose-50/70 border border-rose-100 text-rose-900 flex items-start gap-4">
                <span class="material-symbols-outlined text-rose-600 text-[24px] shrink-0 mt-0.5">error</span>
                <div class="space-y-1">
                    <h4 class="text-sm font-bold">Pengajuan Reservasi Tidak Dapat Diproses</h4>
                    <ul class="list-disc list-inside text-xs text-rose-700 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- Toast / Session Flash Sukses (Latar emerald-50 lembut) --}}
        @if (session('success'))
            <div class="p-6 rounded-[2rem] bg-emerald-50/70 border border-emerald-100 text-emerald-900 flex items-center gap-4">
                <span class="material-symbols-outlined text-emerald-600 text-[24px] shrink-0">check_circle</span>
                <div>
                    <h4 class="text-sm font-bold">Permohonan Berhasil Dikirim!</h4>
                    <p class="text-xs text-emerald-700 mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        {{-- 
          Formulir Utama Pengajuan Reservasi:
          - action: route('user.reservations.store') -> POST /user/reservations
          - @submit: Mengubah state submitting = true untuk mengaktifkan animasi loading dan mencegah double post
          - Grid 12 Kolom: 7 kolom sisi kiri untuk isian form, 5 kolom sisi kanan untuk preview sticky
        --}}
        <form action="{{ route('user.reservations.store') }}" method="POST" @submit="submitting = true" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            {{-- Token Proteksi CSRF Laravel Wajib untuk Seluruh Request HTTP POST --}}
            @csrf

            {{-- ====================================================================================== --}}
            {{-- KOLOM KIRI: FORM INPUT DATA UTAMA (7 KOLOM BENTO)                                      --}}
            {{-- ====================================================================================== --}}
            <div class="lg:col-span-7 space-y-8">
                <div class="bg-white rounded-[2rem] p-8 md:p-10 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.03)] space-y-8">
                    
                    {{-- ---------------------------------------------------------------------------------- --}}
                    {{-- BAGIAN 1: PEMILIHAN RUANG BERTINGKAT (GEDUNG -> LANTAI -> RUANGAN)                 --}}
                    {{-- ---------------------------------------------------------------------------------- --}}
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg sm:text-xl font-bold text-[#1D1D1F]">1. Pilih Ruangan Kampus</h2>
                            <span class="text-xs text-[#86868B]">Gedung ➔ Lantai ➔ Ruangan</span>
                        </div>

                        {{-- Grid 3 Dropdown Pemilihan Bertingkat --}}
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            
                            {{-- Dropdown 1: Pilih Gedung --}}
                            <div>
                                <label for="building-select" class="block text-xs font-semibold text-[#86868B] uppercase tracking-wider mb-2">
                                    Gedung
                                </label>
                                <select id="building-select" 
                                        x-model="selectedBuilding" 
                                        @change="onBuildingChange()" 
                                        class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-4 py-3 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-black/10 focus:bg-white transition cursor-pointer">
                                    <template x-for="b in availableBuildings" :key="b">
                                        <option :value="b" x-text="b" :selected="b === selectedBuilding"></option>
                                    </template>
                                </select>
                            </div>

                            {{-- Dropdown 2: Pilih Lantai --}}
                            <div>
                                <label for="floor-select" class="block text-xs font-semibold text-[#86868B] uppercase tracking-wider mb-2">
                                    Lantai
                                </label>
                                <select id="floor-select" 
                                        x-model="selectedFloor" 
                                        @change="onFloorChange()" 
                                        class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-4 py-3 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-black/10 focus:bg-white transition cursor-pointer">
                                    <template x-for="fl in availableFloors" :key="fl">
                                        <option :value="fl" x-text="fl" :selected="fl === selectedFloor"></option>
                                    </template>
                                </select>
                            </div>

                            {{-- Dropdown 3: Pilih Ruangan (Mengikat facility_id ke Form Request) --}}
                            <div>
                                <label for="venue-select" class="block text-xs font-semibold text-[#86868B] uppercase tracking-wider mb-2">
                                    Ruangan <span class="text-rose-500">*</span>
                                </label>
                                <select name="facility_id" 
                                        id="venue-select" 
                                        x-model="selectedFacilityId" 
                                        required 
                                        class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] font-medium px-4 py-3 rounded-xl text-sm @error('facility_id') ring-2 ring-rose-400 bg-rose-50/50 @enderror focus:outline-none focus:ring-1 focus:ring-black/10 focus:bg-white transition cursor-pointer">
                                    <template x-for="room in availableRooms" :key="room.id">
                                        <option :value="String(room.id)" 
                                                x-text="room.code + ' - ' + room.name + ' (' + room.capacity + ' org)'" 
                                                :selected="String(room.id) === String(selectedFacilityId)">
                                        </option>
                                    </template>
                                </select>
                            </div>
                        </div>

                        {{-- Pesan Kesalahan Validasi Field facility_id --}}
                        @error('facility_id')
                            <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror

                        {{-- Ringkasan Ruangan Teks Bersih Bento (Kapasitas, Kategori, Gedung) --}}
                        <div class="p-4 rounded-xl bg-[#F9F9FB] text-sm text-[#86868B]">
                            <strong class="text-[#1D1D1F]" x-text="currentVenue.name"></strong> • <span x-text="currentVenue.building"></span> • Kapasitas <span x-text="currentVenue.capacity"></span> Orang • <span x-text="currentVenue.category"></span>
                        </div>
                    </div>

                    {{-- ---------------------------------------------------------------------------------- --}}
                    {{-- BAGIAN 2: WAKTU & RENTANG PENGGUNAAN (TANGGAL & SLOT 30 MENIT)                     --}}
                    {{-- ---------------------------------------------------------------------------------- --}}
                    <div class="space-y-4 pt-6 border-t border-gray-100">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg sm:text-xl font-bold text-[#1D1D1F]">2. Waktu & Sesi Pelaksanaan</h2>
                            <span class="text-xs text-[#86868B]">Operasional 07:00 - 20:00 WIB</span>
                        </div>

                        {{-- Input Tanggal Pelaksanaan (Minimal Hari Ini) --}}
                        <div>
                            <label for="res-date" class="block text-xs font-semibold text-[#86868B] uppercase tracking-wider mb-2">
                                Tanggal Pelaksanaan <span class="text-rose-500">*</span>
                            </label>
                            <input type="date" 
                                   id="res-date" 
                                   name="reservation_date" 
                                   x-model="selectedDate" 
                                   min="{{ date('Y-m-d') }}" 
                                   required 
                                   class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-4 py-3 rounded-xl text-sm @error('reservation_date') ring-2 ring-rose-400 bg-rose-50/50 @enderror focus:outline-none focus:ring-1 focus:ring-black/10 focus:bg-white transition cursor-pointer">
                            @error('reservation_date')
                                <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Pemilihan Jam Mulai dan Jam Selesai --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            {{-- Dropdown Jam Mulai --}}
                            <div>
                                <label for="start_time" class="block text-xs font-semibold text-[#86868B] uppercase tracking-wider mb-2">
                                    Jam Mulai <span class="text-rose-500">*</span>
                                </label>
                                <select name="start_time" 
                                        id="start_time" 
                                        x-model="selectedStart" 
                                        required 
                                        class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-4 py-3 rounded-xl text-sm @error('start_time') ring-2 ring-rose-400 @enderror focus:outline-none focus:ring-1 focus:ring-black/10 focus:bg-white transition cursor-pointer">
                                    @foreach (array_slice($timeSlots, 0, -1) as $slot)
                                        <option value="{{ $slot }}" {{ old('start_time', '09:00') === $slot ? 'selected' : '' }}>
                                            {{ $slot }} WIB
                                        </option>
                                    @endforeach
                                </select>
                                @error('start_time')
                                    <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                                @enderror
                            </div>

                            {{-- Dropdown Jam Selesai --}}
                            <div>
                                <label for="end_time" class="block text-xs font-semibold text-[#86868B] uppercase tracking-wider mb-2">
                                    Jam Selesai <span class="text-rose-500">*</span>
                                </label>
                                <select name="end_time" 
                                        id="end_time" 
                                        x-model="selectedEnd" 
                                        required 
                                        class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-4 py-3 rounded-xl text-sm @error('end_time') ring-2 ring-rose-400 @enderror focus:outline-none focus:ring-1 focus:ring-black/10 focus:bg-white transition cursor-pointer">
                                    @foreach (array_slice($timeSlots, 1) as $slot)
                                        <option value="{{ $slot }}" {{ old('end_time', '11:00') === $slot ? 'selected' : '' }}>
                                            {{ $slot }} WIB
                                        </option>
                                    @endforeach
                                </select>
                                @error('end_time')
                                    <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Informasi Durasi Kegiatan Terhitung --}}
                        <div class="text-xs text-[#86868B] flex items-center justify-between pt-1">
                            <span>Durasi kegiatan: <strong class="text-[#1D1D1F]" x-text="selectedStart + ' - ' + selectedEnd + ' WIB'"></strong></span>
                            <span>Slot kelipatan 30 menit</span>
                        </div>
                    </div>

                    {{-- ---------------------------------------------------------------------------------- --}}
                    {{-- BAGIAN 3: RINCIAN KEGIATAN & JUMLAH PESERTA                                        --}}
                    {{-- ---------------------------------------------------------------------------------- --}}
                    <div class="space-y-4 pt-6 border-t border-gray-100">
                        <h2 class="text-lg sm:text-xl font-bold text-[#1D1D1F]">3. Rincian Kegiatan</h2>

                        {{-- Input Estimasi Jumlah Peserta --}}
                        <div>
                            <label for="participants_count" class="block text-xs font-semibold text-[#86868B] uppercase tracking-wider mb-2">
                                Estimasi Jumlah Peserta (Orang)
                            </label>
                            <input type="number" 
                                   id="participants_count" 
                                   name="participants_count" 
                                   min="1" 
                                   max="2000" 
                                   value="{{ old('participants_count', 1) }}" 
                                   class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-4 py-3 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-black/10 focus:bg-white transition @error('participants_count') ring-2 ring-rose-400 bg-rose-50/50 @enderror">
                            @error('participants_count')
                                <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Textarea Tujuan Penggunaan, Nama Acara, dan Kontak PIC --}}
                        <div>
                            <label for="purpose" class="block text-xs font-semibold text-[#86868B] uppercase tracking-wider mb-2">
                                Tujuan Penggunaan, Nama Acara & PIC <span class="text-rose-500">*</span>
                            </label>
                            <textarea id="purpose" 
                                      name="purpose" 
                                      rows="4" 
                                      required 
                                      minlength="10" 
                                      maxlength="500" 
                                      placeholder="Contoh: Seminar Nasional Web Development HMIF - Estimasi 75 Peserta Mahasiswa. PIC: Dimas Pratama (08123456789)" 
                                      class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] placeholder:text-[#86868B] p-4 rounded-xl text-sm @error('purpose') ring-2 ring-rose-400 bg-rose-50/50 @enderror focus:outline-none focus:ring-1 focus:ring-black/10 focus:bg-white transition leading-relaxed">{{ old('purpose') }}</textarea>
                            @error('purpose')
                                <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                </div>
            </div>

            {{-- ====================================================================================== --}}
            {{-- KOLOM KANAN: PREVIEW RINGKASAN BENTO & AKSI SUBMIT (5 KOLOM STICKY)                    --}}
            {{-- ====================================================================================== --}}
            <div class="lg:col-span-5 space-y-6 lg:sticky lg:top-24">
                <div class="bg-white rounded-[2rem] p-8 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.03)] space-y-6">
                    
                    {{-- Header Ringkasan Permohonan --}}
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                        <h3 class="text-base font-bold text-[#1D1D1F]">Ringkasan Permohonan</h3>
                        <span class="text-xs font-semibold tracking-wide text-emerald-800">Bebas Bentrok</span>
                    </div>

                    {{-- Kotak Preview Reaktif Ruangan, Tanggal, dan Jam --}}
                    <div class="space-y-3 text-xs">
                        {{-- Preview Ruangan --}}
                        <div class="bg-[#F9F9FB] p-4 rounded-2xl space-y-1">
                            <span class="text-[#86868B] block">Ruangan Terpilih</span>
                            <div class="font-bold text-[#1D1D1F] text-sm" x-text="currentVenue.name"></div>
                            <div class="text-[#86868B]" x-text="currentVenue.building + ' • Kapasitas: ' + currentVenue.capacity + ' Orang'"></div>
                        </div>

                        {{-- Preview Tanggal dan Jam Sesi --}}
                        <div class="grid grid-cols-2 gap-3 text-[#86868B]">
                            <div class="bg-[#F9F9FB] p-4 rounded-2xl space-y-1">
                                <span class="block">Tanggal</span>
                                <div class="font-bold text-[#1D1D1F] text-sm" x-text="selectedDate"></div>
                            </div>
                            <div class="bg-[#F9F9FB] p-4 rounded-2xl space-y-1">
                                <span class="block">Sesi Waktu</span>
                                <div class="font-bold text-[#1D1D1F] text-sm" x-text="selectedStart + ' - ' + selectedEnd"></div>
                            </div>
                        </div>
                    </div>

                    {{-- Panduan Kebijakan Kampus Teks Bersih --}}
                    <div class="p-4 rounded-2xl bg-[#F9F9FB] text-xs text-[#86868B] space-y-1.5 leading-relaxed">
                        <div class="font-semibold text-[#1D1D1F]">Kebijakan Penggunaan Fasilitas:</div>
                        <ul class="list-disc list-inside space-y-1">
                            <li>Pengajuan diverifikasi oleh petugas sarpras maksimal 1x24 jam kerja.</li>
                            <li>Pembatalan mandiri hanya diizinkan maksimal <strong>H-1</strong> sebelum kegiatan.</li>
                            <li>Wajib menjaga kebersihan dan mengembalikan tata letak fasilitas seperti semula.</li>
                        </ul>
                    </div>

                    {{-- Tombol Aksi Submit Form dan Batal --}}
                    <div class="space-y-3 pt-2">
                        <button type="submit" 
                                :disabled="submitting" 
                                class="w-full py-3.5 px-6 rounded-xl bg-[#1D1D1F] text-white text-sm font-medium hover:bg-black transition flex items-center justify-center gap-2 shadow-sm disabled:opacity-50">
                            {{-- Ikon Kirim Normal --}}
                            <span x-show="!submitting" class="material-symbols-outlined text-[18px]">send</span>
                            {{-- Spinner Loading Saat Sedang Memproses Request --}}
                            <span x-show="submitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            <span x-text="submitting ? 'Memproses Validasi...' : 'Kirim Permohonan Reservasi'"></span>
                        </button>

                        {{-- Tombol Pembatalan Kembali ke Halaman Riwayat --}}
                        <a href="{{ route('user.reservation-history') }}" 
                           class="block text-center w-full py-2.5 text-xs font-medium text-[#86868B] hover:text-[#1D1D1F] transition">
                            Batal dan Kembali
                        </a>
                    </div>

                </div>
            </div>

        </form>

    </div>
</x-app-layout>
