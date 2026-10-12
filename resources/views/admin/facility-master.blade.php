{{-- 
  ==============================================================================================
  TAMPILAN KONSOL ADMIN: MANAJEMEN MASTER DATA FASILITAS KAMPUS (ADM-03 / US-16)
  ==============================================================================================
  Berkas: resources/views/admin/facility-master.blade.php
  
  PERAN & FUNGSI UTAMA:
  1. Merupakan antarmuka grafis sentral bagi Administrator Biro Sarpras untuk mengelola inventaris
     seluruh venue, auditorium, laboratorium, ruang kelas, dan arena olahraga kampus.
  2. Menerapkan desain Apple-inspired Bento UI (Grid 2 Kolom lega, rounded-3xl, shadow lembut).
  3. Menggunakan Alpine.js untuk reaktivitas sisi klien:
     - Pencarian instan (instant search filter) tanpa perlu reload halaman.
     - Modal pop-up formulir terpadu (Tambah Ruang Baru & Sunting Data).
     - Modal konfirmasi penghapusan lunak (Soft Delete Modal).
  4. Menyediakan tombol toggle instan untuk mengunci/membuka akses peminjaman ruangan
     ('aktif' <-> 'dalam perbaikan') secara satu kali klik.
  ==============================================================================================
--}}

<x-admin-layout title="Master Fasilitas Kampus" active="facility-master">
    {{-- 
        Inisialisasi State Reaktif Alpine.js:
        - showFacilityModal : Menampilkan atau menyembunyikan modal form tambah/edit.
        - showDeleteModal   : Menampilkan atau menyembunyikan modal konfirmasi hapus.
        - selectedFacility  : Menyimpan objek data fasilitas yang sedang disunting (null jika tambah baru).
        - facilitySearch    : Nilai kata kunci pencarian real-time untuk filter kartu.
        - deleteTarget      : Menyimpan data ID, nama, dan kode ruangan yang akan dinonaktifkan.
    --}}
    <div x-data="{
        showFacilityModal: false,
        showDeleteModal: false,
        selectedFacility: null,
        facilitySearch: '',
        openAddFacility() {
            this.selectedFacility = null;
            this.showFacilityModal = true;
        },
        openEditFacility(item) {
            this.selectedFacility = item;
            this.showFacilityModal = true;
        },
        openDelete(id, name, code) {
            this.deleteTarget = { id: id, name: name, code: code };
            this.showDeleteModal = true;
        },
        deleteTarget: { id: '', name: '', code: '' }
    }" class="space-y-8">

        {{-- ---------------------------------------------------------------------------------- --}}
        {{-- NOTIFIKASI FLASH SESSION (PESAN SUKSES / GALAT / VALIDASI)                         --}}
        {{-- ---------------------------------------------------------------------------------- --}}
        @if (session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50/80 border border-emerald-100 text-xs text-emerald-900 flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-[20px] text-emerald-700">check_circle</span>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 rounded-2xl bg-rose-50/80 border border-rose-100 text-xs text-rose-900 flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-[20px] text-rose-700">error</span>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-2xl bg-rose-50/80 border border-rose-100 text-xs text-rose-900 shadow-xs">
                <div class="flex items-start gap-3">
                    <span class="material-symbols-outlined text-[20px] text-rose-700 shrink-0 mt-0.5">warning</span>
                    <div class="space-y-0.5">
                        <p class="font-semibold mb-1">Terdapat kesalahan validasi formulir:</p>
                        @foreach ($errors->all() as $error)
                            <p class="font-medium">• {{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- ---------------------------------------------------------------------------------- --}}
        {{-- 1. HEADER JUDUL & TOMBOL TAMBAH RUANG                                              --}}
        {{-- ---------------------------------------------------------------------------------- --}}
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-[#1D1D1F] font-display">Master Fasilitas Kampus</h1>
                <p class="text-sm text-[#86868B] mt-1.5 leading-relaxed">Inventaris ruangan, daya tampung peserta, dan kontrol ketersediaan sarana prasarana.</p>
            </div>
            {{-- Tombol pemicu modal tambah ruangan (@click="openAddFacility()"): --}}
            <button type="button" 
                    @click="openAddFacility()" 
                    class="bg-[#1D1D1F] text-white px-5 py-2.5 rounded-xl text-sm font-medium hover:bg-black transition shadow-sm cursor-pointer flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px]">add</span>
                <span>Tambah Ruang Baru</span>
            </button>
        </div>

        {{-- ---------------------------------------------------------------------------------- --}}
        {{-- 2. SEARCH BAR PENCARIAN REAKTIF                                                    --}}
        {{-- ---------------------------------------------------------------------------------- --}}
        {{-- Menggunakan x-model="facilitySearch" untuk penyaringan instan tanpa request ke server --}}
        <div class="relative group">
            <div class="absolute -inset-1 bg-gradient-to-r from-blue-100 to-indigo-100 rounded-2xl blur opacity-25 group-hover:opacity-50 transition duration-500"></div>
            <div class="relative bg-white rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-1.5 flex items-center border border-gray-100">
                <span class="material-symbols-outlined text-gray-400 ml-3 mr-2 text-[20px]">search</span>
                <input type="text" 
                       x-model="facilitySearch" 
                       placeholder="Cari nama fasilitas, kode ruangan, kategori, atau gedung..." 
                       class="flex-1 py-2.5 px-2 border-0 focus:ring-0 focus:border-transparent outline-none text-sm md:text-base bg-transparent text-[#1D1D1F] placeholder:text-[#86868B]" 
                       autocomplete="off">
                <button type="button" class="bg-[#1D1D1F] text-white px-5 py-2.5 rounded-xl text-sm font-medium hover:bg-black transition cursor-pointer">
                    Cari
                </button>
            </div>
        </div>

        {{-- ---------------------------------------------------------------------------------- --}}
        {{-- 3. GRID BENTO CARDS: DAFTAR RUANGAN KAMPUS (GRID 2 KOLOM)                          --}}
        {{-- ---------------------------------------------------------------------------------- --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-8">
            @php
                // Kamus label representatif untuk kategori ruangan
                $categoryLabels = [
                    'auditorium' => 'Auditorium',
                    'kelas'      => 'Ruang Kelas',
                    'lab'        => 'Laboratorium',
                    'olahraga'   => 'Olahraga',
                    'rapat'      => 'Ruang Rapat',
                ];
            @endphp

            @forelse ($facilities as $facility)
                @php
                    // Normalisasi tampilan daftar alat pendukung
                    $equipmentText = '-';
                    if (is_array($facility->equipment) && count($facility->equipment) > 0) {
                        $equipmentText = implode(', ', $facility->equipment);
                    } elseif (is_string($facility->equipment) && !empty($facility->equipment)) {
                        $equipmentText = $facility->equipment;
                    }
                    $catName = $categoryLabels[$facility->category] ?? ucfirst($facility->category);
                @endphp

                {{-- Kartu Bento Fasilitas Tunggal dengan filter x-show Alpine.js --}}
                <div class="bg-white rounded-[2rem] p-7 sm:p-8 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)] flex flex-col justify-between hover:border-gray-300 transition"
                     x-show="!facilitySearch || '{{ strtolower($facility->name . ' ' . $facility->code . ' ' . $facility->building . ' ' . $catName) }}'.includes(facilitySearch.toLowerCase())">
                    <div>
                        {{-- Baris Header Kartu: Kode, Nama Ruangan, dan Badge Status --}}
                        <div class="flex items-start justify-between gap-4 pb-4 mb-4 border-b border-gray-100">
                            <div>
                                <span class="text-xs font-mono text-[#86868B]">{{ $facility->code }}</span>
                                <h3 class="text-xl font-bold text-[#1D1D1F] mt-1">{{ $facility->name }}</h3>
                                <p class="text-xs text-[#86868B] mt-1">{{ $facility->building }}</p>
                            </div>
                            <span class="text-xs font-semibold shrink-0 {{ $facility->status === 'aktif' ? 'text-emerald-800' : 'text-amber-800' }}">
                                {{ $facility->status === 'aktif' ? 'Tersedia' : 'Pemeliharaan' }}
                            </span>
                        </div>

                        {{-- Rincian Spesifikasi Kapasitas & Perlengkapan --}}
                        <div class="space-y-2 text-xs text-[#86868B] my-4 leading-relaxed">
                            <div>
                                <strong class="text-[#1D1D1F]">Kapasitas:</strong> {{ $facility->capacity }} Kursi • <strong class="text-[#1D1D1F]">Kategori:</strong> {{ $catName }}
                            </div>
                            <div>
                                <strong class="text-[#1D1D1F]">Kelengkapan:</strong> {{ $equipmentText }}
                            </div>
                        </div>
                    </div>

                    {{-- Baris Tindakan Cepat (Action Buttons) --}}
                    <div class="pt-5 mt-3 border-t border-gray-100 flex items-center justify-between gap-4">
                        {{-- Form toggle status operasional instan (Aktif <-> Dalam Perbaikan) --}}
                        <form action="{{ route('admin.facilities.toggle', $facility->id) }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="text-xs font-medium text-[#86868B] hover:text-[#1D1D1F] transition cursor-pointer">
                                <span>{{ $facility->status === 'aktif' ? 'Kunci untuk Pemeliharaan' : 'Buka Akses Reservasi' }}</span>
                            </button>
                        </form>

                        <div class="flex items-center gap-2">
                            {{-- Tombol sunting data: Mengirim payload JSON ke method openEditFacility() --}}
                            <button type="button" 
                                    @click='openEditFacility({
                                        id: {{ $facility->id }},
                                        name: "{{ addslashes($facility->name) }}",
                                        code: "{{ addslashes($facility->code) }}",
                                        category: "{{ $facility->category }}",
                                        capacity: {{ $facility->capacity }},
                                        building: "{{ addslashes($facility->building) }}",
                                        equipmentText: "{{ addslashes($equipmentText) }}",
                                        status: "{{ $facility->status }}"
                                    })' 
                                    class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-[#1D1D1F] text-xs font-medium transition cursor-pointer">
                                Sunting Data
                            </button>

                            {{-- Tombol nonaktifkan / hapus lunak --}}
                            <button type="button" 
                                    @click="openDelete({{ $facility->id }}, '{{ addslashes($facility->name) }}', '{{ $facility->code }}')" 
                                    class="p-2 text-[#86868B] hover:text-rose-600 transition cursor-pointer" 
                                    title="Nonaktifkan Fasilitas">
                                <span class="material-symbols-outlined text-[18px]">delete</span>
                            </button>
                        </div>
                    </div>
                </div>
            @empty
                {{-- Tampilan Placeholder Kosong --}}
                <div class="col-span-full bg-white rounded-[2rem] p-16 text-center border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                    <span class="material-symbols-outlined text-4xl block mb-2 text-gray-300">domain_disabled</span>
                    <h3 class="text-base font-bold text-[#1D1D1F]">Tidak ada data fasilitas terdaftar</h3>
                    <p class="text-xs text-[#86868B] mt-1">Silakan tambahkan data ruangan baru.</p>
                </div>
            @endforelse
        </div>

        {{-- ---------------------------------------------------------------------------------- --}}
        {{-- 4. PAGINASI DATA FASILITAS                                                         --}}
        {{-- ---------------------------------------------------------------------------------- --}}
        <div class="pt-4 flex items-center justify-between text-xs text-[#86868B]">
            <div>Menampilkan {{ $facilities->firstItem() ?? 0 }} - {{ $facilities->lastItem() ?? 0 }} dari {{ $facilities->total() }} fasilitas</div>
            <div>{{ $facilities->links() }}</div>
        </div>

        {{-- ================================================================================== --}}
        {{-- 5. MODAL POP-UP: FORM TAMBAH / SUNTIng DATA FASILITAS (ALPINE.JS)                  --}}
        {{-- ================================================================================== --}}
        <div x-show="showFacilityModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            {{-- Backdrop Gelap dengan Efek Blur --}}
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="showFacilityModal = false"></div>
            
            {{-- Panel Dialog Modal --}}
            <div class="relative w-full max-w-lg bg-white rounded-[2rem] p-7 sm:p-8 shadow-2xl border border-gray-100 z-10 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-4 mb-5 border-b border-gray-100">
                    <h3 class="text-base font-bold text-[#1D1D1F]" x-text="selectedFacility ? 'Perbarui Fasilitas: ' + selectedFacility.name : 'Tambah Fasilitas Kampus Baru'"></h3>
                    <button type="button" @click="showFacilityModal = false" class="text-gray-400 hover:text-[#1D1D1F] cursor-pointer">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                {{-- 
                    Formulir Dinamis:
                    Jika selectedFacility ada: action mengarah ke '/admin/facilities/{id}' dengan @method('PUT').
                    Jika selectedFacility null: action mengarah ke 'route(facilities.store)' dengan method POST.
                --}}
                <form :action="selectedFacility ? '{{ url('/admin/facilities') }}/' + selectedFacility.id : '{{ route('facilities.store') }}'" 
                      method="POST" 
                      enctype="multipart/form-data" 
                      class="space-y-4 text-xs">
                    @csrf
                    <template x-if="selectedFacility">
                        @method('PUT')
                    </template>
                    <input type="hidden" name="id" :value="selectedFacility ? selectedFacility.id : ''">

                    {{-- Nama Fasilitas --}}
                    <div>
                        <label class="block font-medium text-[#86868B] mb-1.5" for="fac-name">Nama Fasilitas</label>
                        <input type="text" id="fac-name" name="name" :value="selectedFacility ? selectedFacility.name : ''" required maxlength="150" placeholder="Nama ruangan / gedung..." class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-4 py-2.5 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-black">
                    </div>

                    {{-- Kode Ruang & Kapasitas Kursi --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-medium text-[#86868B] mb-1.5" for="fac-code">Kode Ruang</label>
                            <input type="text" id="fac-code" name="code" :value="selectedFacility ? selectedFacility.code : ''" required maxlength="30" placeholder="AUD-01..." class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-4 py-2.5 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-black font-mono uppercase">
                        </div>
                        <div>
                            <label class="block font-medium text-[#86868B] mb-1.5" for="fac-cap">Kapasitas (Kursi)</label>
                            <input type="number" id="fac-cap" name="capacity" :value="selectedFacility ? selectedFacility.capacity : 50" min="1" max="10000" required class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-4 py-2.5 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-black">
                        </div>
                    </div>

                    {{-- Kategori & Lokasi Gedung --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block font-medium text-[#86868B] mb-1.5" for="fac-cat">Kategori</label>
                            <select id="fac-cat" name="category" class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-4 py-2.5 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-black">
                                <option value="auditorium" :selected="selectedFacility && selectedFacility.category === 'auditorium'">Auditorium</option>
                                <option value="kelas" :selected="selectedFacility && selectedFacility.category === 'kelas'">Ruang Kelas</option>
                                <option value="lab" :selected="selectedFacility && selectedFacility.category === 'lab'">Laboratorium</option>
                                <option value="olahraga" :selected="selectedFacility && selectedFacility.category === 'olahraga'">Olahraga</option>
                                <option value="rapat" :selected="selectedFacility && selectedFacility.category === 'rapat'">Ruang Rapat</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-medium text-[#86868B] mb-1.5" for="fac-bld">Gedung & Lokasi Lantai</label>
                            <input type="text" id="fac-bld" name="building" :value="selectedFacility ? selectedFacility.building : ''" required maxlength="100" placeholder="Gedung FSM Lantai 2..." class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-4 py-2.5 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-black">
                        </div>
                    </div>

                    {{-- Deskripsi Perlengkapan (Equipment) --}}
                    <div>
                        <label class="block font-medium text-[#86868B] mb-1.5" for="fac-eq">Deskripsi Kelengkapan Fasilitas</label>
                        <input type="text" id="fac-eq" name="equipment" :value="selectedFacility ? selectedFacility.equipmentText : ''" maxlength="1000" placeholder="Contoh: Videotron 4K, Tata Suara Sentral, AC (maks. 1000 karakter)..." class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-4 py-2.5 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-black">
                    </div>

                    {{-- Tombol Aksi Batal & Simpan --}}
                    <div class="pt-5 mt-6 border-t border-gray-100 flex items-center justify-end gap-3">
                        <button type="button" @click="showFacilityModal = false" class="px-4 py-2 rounded-xl text-xs font-medium text-[#86868B] hover:text-[#1D1D1F] cursor-pointer">Batal</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#1D1D1F] text-white text-xs font-medium hover:bg-black transition cursor-pointer">Simpan Fasilitas</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ================================================================================== --}}
        {{-- 6. MODAL KONFIRMASI: HAPUS / NONAKTIFKAN FASILITAS (SOFT DELETE)                  --}}
        {{-- ================================================================================== --}}
        <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="showDeleteModal = false"></div>
            <div class="relative w-full max-w-sm bg-white rounded-[2rem] p-7 shadow-2xl border border-gray-100 z-10 space-y-4">
                <div class="flex items-center gap-3 text-rose-600">
                    <span class="material-symbols-outlined text-[24px]">warning</span>
                    <h3 class="font-bold text-sm text-[#1D1D1F]">Nonaktifkan Fasilitas?</h3>
                </div>
                <p class="text-xs text-[#86868B] leading-relaxed">
                    Fasilitas <strong class="text-[#1D1D1F]" x-text="deleteTarget.name"></strong> akan dinonaktifkan dari katalog peminjaman kampus.
                </p>
                {{-- Form submission DELETE ke '/admin/facilities/{id}' --}}
                <form :action="'{{ url('/admin/facilities') }}/' + deleteTarget.id" method="POST" class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="showDeleteModal = false" class="px-4 py-2 rounded-xl text-xs font-medium text-[#86868B]">Batal</button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 text-white text-xs font-semibold hover:bg-rose-700">Nonaktifkan</button>
                </form>
            </div>
        </div>

    </div>
</x-admin-layout>

