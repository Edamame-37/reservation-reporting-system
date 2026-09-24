{{-- 
  NAMA FILE      : reservation-form.blade.php
  FUNGSIONALITAS : Formulir Pengajuan Reservasi Ruang & Fasilitas Kampus
  DESKRIPSI      : Form interaktif pemilihan fasilitas aktif dari basis data, tanggal pelaksanaan, pemilihan slot 30 menit (07:00 - 20:00 WIB), tujuan kegiatan, dan proteksi anti-bentrok.
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
    }">

        {{-- Breadcrumb & Header --}}
        <div class="mb-6">
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-2 font-medium">
                <a href="{{ url('/user/dashboard') }}" class="hover:text-slate-900 transition">Dasbor Saya</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-slate-900">Form Pengajuan Reservasi</span>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Form Permohonan Reservasi Fasilitas</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Pilih fasilitas, tanggal pelaksanaan, dan rentang slot waktu operasional (07:00 - 20:00 WIB).</p>
                </div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-emerald-50 text-emerald-800 border border-emerald-200 text-xs font-semibold">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Pengecekan Bentrok Jadwal: Aktif</span>
                </div>
            </div>
        </div>

        {{-- Banner Error Validasi Server --}}
        @if ($errors->any())
            <div class="mb-6 p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 flex items-start gap-3 shadow-xs">
                <span class="material-symbols-outlined text-rose-600 text-[24px] shrink-0 mt-0.5">error</span>
                <div>
                    <h4 class="text-sm font-bold">Pengajuan Reservasi Tidak Dapat Diproses</h4>
                    <ul class="mt-1 list-disc list-inside text-xs text-rose-700 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- Toast / Session Flash Sukses --}}
        @if (session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-center gap-3 shadow-xs">
                <span class="material-symbols-outlined text-emerald-600 text-[24px]">check_circle</span>
                <div>
                    <h4 class="text-sm font-bold">Permohonan Berhasil Dikirim!</h4>
                    <p class="text-xs text-emerald-700">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        <!-- 
          ROUTE: POST /user/reservations
          FUNGSI: Mengirimkan permohonan reservasi baru dengan proteksi interval 30 menit
        -->
        <form action="{{ route('user.reservations.store') }}" method="POST" @submit="submitting = true" class="grid grid-cols-1 lg:grid-cols-12 gap-8">
            @csrf

            {{-- Kolom Kiri: Form Input Data (7 Kolom) --}}
            <div class="lg:col-span-7 flex flex-col gap-6">
                {{-- Card Input Utama --}}
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs flex flex-col gap-5">
                    {{-- Pemilihan Fasilitas Bertingkat (Gedung -> Lantai -> Ruangan) --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                Pilih Fasilitas & Ruang Akademik <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-[11px] text-slate-500 font-medium">Hierarki: Gedung ➔ Lantai ➔ Ruangan</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                            {{-- Dropdown 1: Pilih Gedung --}}
                            <div>
                                <label for="building-select" class="block text-[11px] font-bold text-slate-600 mb-1">
                                    1. Gedung
                                </label>
                                <select id="building-select" 
                                        x-model="selectedBuilding" 
                                        @change="onBuildingChange()" 
                                        class="w-full h-11 px-3 bg-slate-50 rounded-xl text-xs sm:text-sm font-medium text-slate-800 border border-slate-200 focus:border-slate-900 focus:bg-white focus:outline-none transition">
                                    <template x-for="b in availableBuildings" :key="b">
                                        <option :value="b" x-text="b" :selected="b === selectedBuilding"></option>
                                    </template>
                                </select>
                            </div>

                            {{-- Dropdown 2: Pilih Lantai --}}
                            <div>
                                <label for="floor-select" class="block text-[11px] font-bold text-slate-600 mb-1">
                                    2. Lantai
                                </label>
                                <select id="floor-select" 
                                        x-model="selectedFloor" 
                                        @change="onFloorChange()" 
                                        class="w-full h-11 px-3 bg-slate-50 rounded-xl text-xs sm:text-sm font-medium text-slate-800 border border-slate-200 focus:border-slate-900 focus:bg-white focus:outline-none transition">
                                    <template x-for="fl in availableFloors" :key="fl">
                                        <option :value="fl" x-text="fl" :selected="fl === selectedFloor"></option>
                                    </template>
                                </select>
                            </div>

                            {{-- Dropdown 3: Pilih Ruangan (Mengikat facility_id ke Form Request) --}}
                            <div>
                                <label for="venue-select" class="block text-[11px] font-bold text-slate-600 mb-1">
                                    3. Ruangan
                                </label>
                                <select name="facility_id" 
                                        id="venue-select" 
                                        x-model="selectedFacilityId" 
                                        required 
                                        class="w-full h-11 px-3 bg-slate-50 rounded-xl text-xs sm:text-sm font-bold text-slate-900 border @error('facility_id') border-rose-400 bg-rose-50/50 @else border-slate-200 @enderror focus:border-slate-900 focus:bg-white focus:outline-none transition">
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
                            <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tanggal Pelaksanaan --}}
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <label for="res-date" class="text-xs font-bold uppercase tracking-wider text-slate-700">
                                Tanggal Pelaksanaan Kegiatan <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-[11px] text-emerald-700 font-semibold">Tersedia Mulai Hari Ini</span>
                        </div>
                        <input type="date" id="res-date" name="reservation_date" x-model="selectedDate" min="{{ date('Y-m-d') }}" required class="w-full h-11 px-3.5 bg-slate-50 rounded-xl text-sm font-medium text-slate-800 border @error('reservation_date') border-rose-400 bg-rose-50/50 @else border-slate-200 @enderror focus:border-slate-900 focus:bg-white focus:outline-none transition">
                        @error('reservation_date')
                            <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Pemilih Rentang Waktu (Jam Mulai & Jam Selesai Slot 30 Menit) --}}
                    <div class="p-4 rounded-xl bg-slate-50/80 border border-slate-200/80">
                        <div class="flex items-center justify-between mb-3">
                            <label class="text-xs font-bold uppercase tracking-wider text-slate-700">
                                Rentang Waktu Operasional (07:00 - 20:00 WIB) <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-[11px] text-slate-500 font-medium">Slot Kelipatan 30 Menit</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label for="start_time" class="block text-xs font-semibold text-slate-600 mb-1.5">Jam Mulai</label>
                                <select name="start_time" id="start_time" x-model="selectedStart" required class="w-full h-10 px-3 bg-white rounded-lg text-sm font-semibold text-slate-800 border @error('start_time') border-rose-400 @else border-slate-200 @enderror focus:border-slate-900 focus:outline-none transition">
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
                                <label for="end_time" class="block text-xs font-semibold text-slate-600 mb-1.5">Jam Selesai</label>
                                <select name="end_time" id="end_time" x-model="selectedEnd" required class="w-full h-10 px-3 bg-white rounded-lg text-sm font-semibold text-slate-800 border @error('end_time') border-rose-400 @else border-slate-200 @enderror focus:border-slate-900 focus:outline-none transition">
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

                        <div class="mt-3 pt-2.5 border-t border-slate-200 flex items-center justify-between text-[11px] text-slate-500">
                            <span class="flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                                <span>Durasi: <strong class="text-slate-800" x-text="selectedStart + ' - ' + selectedEnd + ' WIB'"></strong></span>
                            </span>
                            <span>Min. 30 menit (1 slot)</span>
                        </div>
                    </div>

                    {{-- Estimasi Peserta (Pendukung) --}}
                    <div>
                        <label for="participants_count" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                            Estimasi Jumlah Peserta (Orang)
                        </label>
                        <input type="number" id="participants_count" name="participants_count" min="1" max="1000" value="{{ old('participants_count', 1) }}" class="w-full h-11 px-3.5 bg-slate-50 rounded-xl text-sm font-medium text-slate-800 border border-slate-200 focus:border-slate-900 focus:bg-white focus:outline-none transition">
                    </div>

                    {{-- Tujuan Penggunaan & Nama Acara --}}
                    <div>
                        <label for="purpose" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-2">
                            Tujuan Penggunaan, Nama Acara & PIC <span class="text-rose-500">*</span>
                        </label>
                        <textarea id="purpose" name="purpose" rows="3" required placeholder="Contoh: Seminar Nasional Web Development HMIF - Estimasi 75 Peserta Mahasiswa. PIC: Dimas Pratama (08123456789)" class="w-full p-3.5 bg-slate-50 rounded-xl text-sm text-slate-800 border @error('purpose') border-rose-400 bg-rose-50/50 @else border-slate-200 @enderror focus:border-slate-900 focus:bg-white focus:outline-none transition">{{ old('purpose') }}</textarea>
                        @error('purpose')
                            <p class="mt-1.5 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Kolom Kanan: Preview Validasi & Submit (5 Kolom) --}}
            <div class="lg:col-span-5 flex flex-col gap-6">
                {{-- Box Ringkasan & Validasi --}}
                <div class="bg-white rounded-2xl border border-slate-200/80 p-6 shadow-xs flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                            <h3 class="text-sm font-bold text-slate-900 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-emerald-600 text-[20px]">verified</span>
                                <span>Ringkasan Validasi Sistem</span>
                            </h3>
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-emerald-50 text-emerald-800 border border-emerald-200">BEBAS BENTROK</span>
                        </div>

                        <div class="space-y-3 text-xs mb-6">
                            <div class="bg-slate-50 p-3.5 rounded-xl border border-slate-100">
                                <span class="text-slate-400 block mb-0.5">Ruang Terpilih:</span>
                                <span class="font-bold text-slate-900 text-sm" x-text="currentVenue.name"></span>
                                <span class="text-slate-500 block text-[11px]" x-text="currentVenue.building + ' • Kapasitas: ' + currentVenue.capacity + ' Orang'"></span>
                            </div>

                            <div class="grid grid-cols-2 gap-2 text-slate-600">
                                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                                    <span class="text-slate-400 block mb-0.5">Tanggal Kegiatan:</span>
                                    <span class="font-bold text-slate-900" x-text="selectedDate"></span>
                                </div>
                                <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                                    <span class="text-slate-400 block mb-0.5">Jam Operasional:</span>
                                    <span class="font-bold text-slate-900" x-text="selectedStart + ' - ' + selectedEnd + ' WIB'"></span>
                                </div>
                            </div>
                        </div>

                        {{-- Panduan Kebijakan Kampus --}}
                        <div class="p-3.5 rounded-xl bg-blue-50/70 border border-blue-100 text-xs text-slate-600 mb-6 space-y-1.5">
                            <div class="font-bold text-blue-950 flex items-center gap-1">
                                <span class="material-symbols-outlined text-[16px]">rule</span>
                                Aturan Penggunaan Fasilitas:
                            </div>
                            <p class="text-[11px] leading-relaxed">
                                1. Pengajuan diverifikasi oleh petugas sarpras maksimal 1x24 jam.<br>
                                2. Pembatalan mandiri hanya diizinkan maksimal <strong>H-1</strong> sebelum kegiatan.<br>
                                3. Wajib menjaga kebersihan dan mengembalikan tata letak fasilitas.
                            </p>
                        </div>
                    </div>

                    {{-- Tombol Submit --}}
                    <div>
                        <button type="submit" :disabled="submitting" class="w-full py-3 px-4 rounded-xl bg-slate-900 text-white text-sm font-semibold hover:bg-slate-800 transition shadow-sm flex items-center justify-center gap-2 disabled:opacity-50">
                            <span x-show="!submitting" class="material-symbols-outlined text-[18px]">send</span>
                            <span x-show="submitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            <span x-text="submitting ? 'Memproses Validasi...' : 'Kirim Permohonan Reservasi'"></span>
                        </button>
                    </div>
                </div>
            </div>
        </form>

    </div>
</x-app-layout>
