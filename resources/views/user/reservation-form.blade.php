{{-- 
  NAMA FILE      : reservation-form.blade.php
  FUNGSIONALITAS : Formulir Pengajuan Reservasi Ruang & Fasilitas Kampus
  DESKRIPSI      : Form interaktif pemilihan fasilitas aktif dari basis data, tanggal pelaksanaan, pemilihan slot 30 menit (07:00 - 20:00 WIB), tujuan kegiatan, dan proteksi anti-bentrok.
  DESAIN         : Opsi 1 (Apple-esque Bento) dengan styling borderless soft gray (bg-[#F5F5F7] border-0), tipografi bersih Outfit/Inter, tanpa AI slop / tanpa border hitam pekat.
  CARA KERJA     : Mengirimkan HTTP POST ke route('user.reservations.store') dengan validasi StoreReservationRequest, menangani error session dan old values secara dinamis.
--}}

<x-app-layout title="Form Pengajuan Reservasi" active="reservation-form">
    @php
        $timeSlots = [
            '07:00', '07:30', '08:00', '08:30', '09:00', '09:30',
            '10:00', '10:30', '11:00', '11:30', '12:00', '12:30',
            '13:00', '13:30', '14:00', '14:30', '15:00', '15:30',
            '16:00', '16:30', '17:00', '17:30', '18:00', '18:30',
            '19:00', '19:30', '20:00'
        ];

        // Format data fasilitas ke JSON untuk interaktivitas ringkasan instan dan 3 dropdown bertingkat
        $facilitiesMap = [];
        $hierarchy = [];

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

        $initFacilityId = old('facility_id', $selectedFacilityId ?? ($facilities->first()->id ?? '1'));
        $initFacility = $facilities->firstWhere('id', $initFacilityId) ?? $facilities->first();
        $initBuilding = $initFacility ? $initFacility->building : (array_key_first($hierarchy) ?? '');

        $initFloor = 'Lantai 1';
        if ($initFacility && preg_match('/Lantai\s*(\d+)/i', $initFacility->floor_location ?? '', $matches)) {
            $initFloor = 'Lantai ' . $matches[1];
        } elseif ($initFacility && !empty($initFacility->floor_location)) {
            $initFloor = $initFacility->floor_location;
        }
    @endphp

    <div x-data="{
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
                name: 'Pilih Fasilitas Kampus',
                building: 'Lokasi Gedung',
                capacity: '-',
                category: '-'
            };
        }
    }" class="space-y-8">

        {{-- Top Navigation & Indikator Status (Judul halaman telah terwakili pada Universal Top Header) --}}
        <div class="flex items-center justify-between">
            <a href="{{ route('user.reservation-history') }}" class="inline-flex items-center gap-1.5 text-sm text-[#86868B] hover:text-[#1D1D1F] font-medium transition">
                <span class="material-symbols-outlined text-[18px]">arrow_back</span>
                <span>Kembali ke Riwayat Reservasi</span>
            </a>
            <span class="text-xs font-semibold tracking-wide text-emerald-800">
                Pengecekan Bentrok Jadwal Aktif
            </span>
        </div>

        {{-- Banner Error Validasi Server (Soft Apple Tone tanpa border hitam) --}}
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

        {{-- Toast / Session Flash Sukses --}}
        @if (session('success'))
            <div class="p-6 rounded-[2rem] bg-emerald-50/70 border border-emerald-100 text-emerald-900 flex items-center gap-4">
                <span class="material-symbols-outlined text-emerald-600 text-[24px] shrink-0">check_circle</span>
                <div>
                    <h4 class="text-sm font-bold">Permohonan Berhasil Dikirim!</h4>
                    <p class="text-xs text-emerald-700 mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- 
          ROUTE: POST /user/reservations
          FUNGSI: Mengirimkan permohonan reservasi baru dengan validasi slot & proteksi anti bentrok
        -->
        <form action="{{ route('user.reservations.store') }}" method="POST" @submit="submitting = true" class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            @csrf

            {{-- Kolom Kiri: Form Input Data Utama (7 Kolom Bento) --}}
            <div class="lg:col-span-7 space-y-8">
                <div class="bg-white rounded-[2rem] p-8 md:p-10 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.03)] space-y-8">
                    
                    {{-- Bagian 1: Pemilihan Ruang Bertingkat --}}
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg sm:text-xl font-bold text-[#1D1D1F]">1. Pilih Ruangan Kampus</h2>
                            <span class="text-xs text-[#86868B]">Gedung ➔ Lantai ➔ Ruangan</span>
                        </div>

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
                        @error('facility_id')
                            <p class="text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror

                        {{-- Ringkasan Ruangan Teks Bersih Bento --}}
                        <div class="p-4 rounded-xl bg-[#F9F9FB] text-sm text-[#86868B]">
                            <strong class="text-[#1D1D1F]" x-text="currentVenue.name"></strong> • <span x-text="currentVenue.building"></span> • Kapasitas <span x-text="currentVenue.capacity"></span> Orang • <span x-text="currentVenue.category"></span>
                        </div>
                    </div>

                    {{-- Bagian 2: Waktu & Rentang Penggunaan --}}
                    <div class="space-y-4 pt-6 border-t border-gray-100">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg sm:text-xl font-bold text-[#1D1D1F]">2. Waktu & Sesi Pelaksanaan</h2>
                            <span class="text-xs text-[#86868B]">Operasional 07:00 - 20:00 WIB</span>
                        </div>

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

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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

                        <div class="text-xs text-[#86868B] flex items-center justify-between pt-1">
                            <span>Durasi kegiatan: <strong class="text-[#1D1D1F]" x-text="selectedStart + ' - ' + selectedEnd + ' WIB'"></strong></span>
                            <span>Slot kelipatan 30 menit</span>
                        </div>
                    </div>

                    {{-- Bagian 3: Rincian Kegiatan & Peserta --}}
                    <div class="space-y-4 pt-6 border-t border-gray-100">
                        <h2 class="text-lg sm:text-xl font-bold text-[#1D1D1F]">3. Rincian Kegiatan</h2>

                        <div>
                            <label for="participants_count" class="block text-xs font-semibold text-[#86868B] uppercase tracking-wider mb-2">
                                Estimasi Jumlah Peserta (Orang)
                            </label>
                            <input type="number" 
                                   id="participants_count" 
                                   name="participants_count" 
                                   min="1" 
                                   max="1000" 
                                   value="{{ old('participants_count', 1) }}" 
                                   class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-4 py-3 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-black/10 focus:bg-white transition">
                        </div>

                        <div>
                            <label for="purpose" class="block text-xs font-semibold text-[#86868B] uppercase tracking-wider mb-2">
                                Tujuan Penggunaan, Nama Acara & PIC <span class="text-rose-500">*</span>
                            </label>
                            <textarea id="purpose" 
                                      name="purpose" 
                                      rows="4" 
                                      required 
                                      placeholder="Contoh: Seminar Nasional Web Development HMIF - Estimasi 75 Peserta Mahasiswa. PIC: Dimas Pratama (08123456789)" 
                                      class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] placeholder:text-[#86868B] p-4 rounded-xl text-sm @error('purpose') ring-2 ring-rose-400 bg-rose-50/50 @enderror focus:outline-none focus:ring-1 focus:ring-black/10 focus:bg-white transition leading-relaxed">{{ old('purpose') }}</textarea>
                            @error('purpose')
                                <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                </div>
            </div>

            {{-- Kolom Kanan: Preview Validasi Sistem & Aksi Submit (5 Kolom Sticky Bento) --}}
            <div class="lg:col-span-5 space-y-6 lg:sticky lg:top-24">
                <div class="bg-white rounded-[2rem] p-8 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.03)] space-y-6">
                    
                    {{-- Header Ringkasan --}}
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                        <h3 class="text-base font-bold text-[#1D1D1F]">Ringkasan Permohonan</h3>
                        <span class="text-xs font-semibold tracking-wide text-emerald-800">Bebas Bentrok</span>
                    </div>

                    {{-- Item Ringkasan --}}
                    <div class="space-y-3 text-xs">
                        <div class="bg-[#F9F9FB] p-4 rounded-2xl space-y-1">
                            <span class="text-[#86868B] block">Ruangan Terpilih</span>
                            <div class="font-bold text-[#1D1D1F] text-sm" x-text="currentVenue.name"></div>
                            <div class="text-[#86868B]" x-text="currentVenue.building + ' • Kapasitas: ' + currentVenue.capacity + ' Orang'"></div>
                        </div>

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

                    {{-- Tombol Aksi --}}
                    <div class="space-y-3 pt-2">
                        <button type="submit" 
                                :disabled="submitting" 
                                class="w-full py-3.5 px-6 rounded-xl bg-[#1D1D1F] text-white text-sm font-medium hover:bg-black transition flex items-center justify-center gap-2 shadow-sm disabled:opacity-50">
                            <span x-show="!submitting" class="material-symbols-outlined text-[18px]">send</span>
                            <span x-show="submitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            <span x-text="submitting ? 'Memproses Validasi...' : 'Kirim Permohonan Reservasi'"></span>
                        </button>

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
