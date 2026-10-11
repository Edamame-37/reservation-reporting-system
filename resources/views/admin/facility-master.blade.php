{{-- 
  NAMA FILE      : facility-master.blade.php
  FUNGSIONALITAS : Halaman Master Data Inventaris Fasilitas Kampus (Super Admin)
  DESKRIPSI      : Menampilkan seluruh inventaris ruangan kampus dinamis, kontrol ketersediaan/pemeliharaan, penambahan/penyuntingan data, serta penonaktifan fasilitas (soft delete).
  DESAIN SISTEM  : Opsi 1 (Executive Apple Bento & Minimalist Precision)
                   - Layout Bento Cards lega (rounded-[2rem], border gray-100, soft shadows)
                   - Search Bar dengan aura glow halus adaptasi katalog publik
                   - Underline navigation tabs untuk filter kategori tanpa kotak warna-warni
                   - Pure typographic status indicator (Tersedia / Pemeliharaan / Nonaktif)
                   - Deskripsi perlengkapan satu kalimat rapi tanpa badge mini warna-warni berisik
  CARA KERJA     : Memanfaatkan layout <x-admin-layout active="facility-master"> dengan Alpine.js untuk formulir modal dan integrasi penuh ke FacilityController.
--}}

<x-admin-layout title="Master Fasilitas Kampus" active="facility-master">
    <div x-data="{
        showFacilityModal: false,
        showDeleteModal: false,
        isEdit: false,
        modalTitle: 'Tambah Ruang Fasilitas Baru',
        formAction: '{{ route('facilities.store') }}',
        facility: {
            id: '',
            name: '',
            code: '',
            category: 'auditorium',
            building: '',
            floor_location: '',
            capacity: 50,
            description: '',
            equipment: '',
            status: 'aktif',
            image_path: ''
        },
        deleteTarget: {
            id: '',
            name: '',
            code: ''
        },
        openAdd() {
            this.isEdit = false;
            this.modalTitle = 'Tambah Ruang Fasilitas Baru';
            this.formAction = '{{ route('facilities.store') }}';
            this.facility = {
                id: '',
                name: '',
                code: '',
                category: 'auditorium',
                building: '',
                floor_location: '',
                capacity: 50,
                description: '',
                equipment: '',
                status: 'aktif',
                image_path: ''
            };
            this.showFacilityModal = true;
        },
        openEdit(item) {
            this.isEdit = true;
            this.modalTitle = 'Sunting Data Fasilitas: ' + item.name;
            this.formAction = '{{ url('/admin/facilities') }}/' + item.id;
            
            let equipmentStr = '';
            if (Array.isArray(item.equipment)) {
                equipmentStr = item.equipment.join(', ');
            } else if (typeof item.equipment === 'string') {
                equipmentStr = item.equipment;
            }

            this.facility = {
                id: item.id,
                name: item.name,
                code: item.code,
                category: item.category,
                building: item.building,
                floor_location: item.floor_location || '',
                capacity: item.capacity,
                description: item.description || '',
                equipment: equipmentStr,
                status: item.status || 'aktif',
                image_path: item.image_path || ''
            };
            this.showFacilityModal = true;
        },
        openDelete(id, name, code) {
            this.deleteTarget = { id: id, name: name, code: code };
            this.showDeleteModal = true;
        }
    }" class="space-y-8">

        {{-- 1. Header Eksekutif & Tombol Tambah Fasilitas --}}
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <nav class="flex items-center gap-2 text-xs text-[#86868B] mb-1.5">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-[#1D1D1F] transition-colors">Admin Console</a>
                    <span>/</span>
                    <span class="text-[#1D1D1F] font-medium">Master Fasilitas</span>
                </nav>
                <h1 class="text-3xl font-extrabold tracking-tight text-[#1D1D1F] font-display">Master Fasilitas Kampus</h1>
                <p class="text-sm text-[#86868B] mt-1 leading-relaxed">
                    Inventaris ruangan, kapasitas peserta, dan kontrol ketersediaan sarana prasarana universitas.
                </p>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <button type="button" 
                        @click="openAdd()" 
                        class="px-5 py-2.5 rounded-xl bg-[#1D1D1F] text-white text-xs font-semibold hover:bg-black transition shadow-sm cursor-pointer flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    <span>+ Tambah Ruang Baru</span>
                </button>
            </div>
        </div>

        {{-- 2. Flash Session Notifications --}}
        @if (session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50/80 border border-emerald-100 text-xs text-emerald-900 flex items-center justify-between shadow-[0_4px_20px_rgb(0,0,0,0.02)]">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-[20px] text-emerald-700">check_circle</span>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if (session('error'))
            <div class="p-4 rounded-2xl bg-rose-50/80 border border-rose-100 text-xs text-rose-900 flex items-center justify-between shadow-[0_4px_20px_rgb(0,0,0,0.02)]">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-[20px] text-rose-700">error</span>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 rounded-2xl bg-rose-50/80 border border-rose-100 text-xs text-rose-900 shadow-[0_4px_20px_rgb(0,0,0,0.02)]">
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

        {{-- 3. Bento Metriks Fasilitas (4 Kolom Mewah) --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            {{-- Metrik 1: Total Ruangan --}}
            <div class="bg-white rounded-[2rem] p-6 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                <div class="text-xs font-medium text-[#86868B] mb-2">Total Ruangan Terdaftar</div>
                <div class="text-3xl font-extrabold text-[#1D1D1F] tracking-tight font-display">{{ number_format($totalCount ?? 0) }}</div>
                <div class="text-xs text-[#86868B] mt-2 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#1D1D1F]"></span>
                    <span>{{ $buildingCount ?? 0 }} Kluster Gedung Kampus</span>
                </div>
            </div>

            {{-- Metrik 2: Ruangan Siap Digunakan --}}
            <div class="bg-white rounded-[2rem] p-6 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                <div class="text-xs font-medium text-[#86868B] mb-2">Operasional Aktif</div>
                <div class="text-3xl font-extrabold text-[#1D1D1F] tracking-tight font-display">{{ number_format($activeCount ?? 0) }}</div>
                <div class="text-xs text-emerald-800 font-semibold mt-2 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>Tersedia untuk reservasi sivitas</span>
                </div>
            </div>

            {{-- Metrik 3: Pemeliharaan / Terkunci --}}
            <div class="bg-white rounded-[2rem] p-6 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                <div class="text-xs font-medium text-[#86868B] mb-2">Dalam Pemeliharaan</div>
                <div class="text-3xl font-extrabold text-[#1D1D1F] tracking-tight font-display">{{ number_format($maintenanceCount ?? 0) }}</div>
                <div class="text-xs text-amber-800 font-semibold mt-2 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    <span>Terkunci untuk perbaikan teknisi</span>
                </div>
            </div>

            {{-- Metrik 4: Akumulasi Kapasitas --}}
            <div class="bg-white rounded-[2rem] p-6 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                <div class="text-xs font-medium text-[#86868B] mb-2">Total Kapasitas Kursi</div>
                <div class="text-3xl font-extrabold text-[#1D1D1F] tracking-tight font-display">{{ number_format($totalCapacity ?? 0, 0, ',', '.') }}</div>
                <div class="text-xs text-[#86868B] mt-2 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#86868B]"></span>
                    <span>Daya tampung simultan</span>
                </div>
            </div>
        </div>

        {{-- 4. Search Bar & Category Underline Filter Tabs --}}
        <div class="space-y-4">
            {{-- Search Bar Katalog Publik (Aura Glow Halus) --}}
            <form method="GET" action="{{ route('admin.facility-master') }}" class="space-y-4">
                <input type="hidden" name="category" value="{{ request('category', 'all') }}">

                <div class="relative group">
                    <div class="absolute -inset-1 bg-gradient-to-r from-blue-100 to-indigo-100 rounded-2xl blur opacity-25 group-hover:opacity-50 transition duration-500"></div>
                    <div class="relative bg-white rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-1.5 flex items-center border border-gray-100">
                        <span class="material-symbols-outlined text-gray-400 ml-3 mr-2 text-[20px]">search</span>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Cari nama fasilitas, kode ruangan, atau nama gedung..." 
                               class="flex-1 py-2.5 px-2 border-0 focus:ring-0 focus:border-transparent outline-none text-sm bg-transparent text-[#1D1D1F] placeholder:text-[#86868B]" 
                               autocomplete="off">
                        
                        {{-- Filter Status Dropdown --}}
                        <div class="hidden sm:flex items-center border-l border-gray-100 pl-3 pr-2">
                            <select name="status" onchange="this.form.submit()" class="text-xs text-[#1D1D1F] bg-transparent border-0 focus:ring-0 outline-none cursor-pointer">
                                <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>Semua Status</option>
                                <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif (Tersedia)</option>
                                <option value="dalam perbaikan" {{ request('status') === 'dalam perbaikan' ? 'selected' : '' }}>Dalam Perbaikan</option>
                            </select>
                        </div>

                        <button type="submit" class="bg-[#1D1D1F] text-white px-5 py-2.5 rounded-xl text-xs font-semibold hover:bg-black transition cursor-pointer">
                            Cari
                        </button>
                    </div>
                </div>
            </form>

            {{-- Underline Tabs Filter Kategori (Bebas Kotak Warna-Warni) --}}
            @php
                $currentCat = request('category', 'all');
            @endphp
            <div class="flex items-center gap-8 text-sm font-medium overflow-x-auto no-scrollbar border-b border-gray-100 pt-1">
                <a href="{{ route('admin.facility-master', array_merge(request()->query(), ['category' => 'all'])) }}"
                   class="pb-3 -mb-px transition whitespace-nowrap {{ $currentCat === 'all' ? 'text-[#1D1D1F] border-b-2 border-[#1D1D1F] font-semibold' : 'text-[#86868B] hover:text-[#1D1D1F]' }}">
                    Semua Ruangan ({{ $totalCount ?? 0 }})
                </a>
                <a href="{{ route('admin.facility-master', array_merge(request()->query(), ['category' => 'auditorium'])) }}"
                   class="pb-3 -mb-px transition whitespace-nowrap {{ $currentCat === 'auditorium' ? 'text-[#1D1D1F] border-b-2 border-[#1D1D1F] font-semibold' : 'text-[#86868B] hover:text-[#1D1D1F]' }}">
                    Auditorium & Hall
                </a>
                <a href="{{ route('admin.facility-master', array_merge(request()->query(), ['category' => 'kelas'])) }}"
                   class="pb-3 -mb-px transition whitespace-nowrap {{ $currentCat === 'kelas' ? 'text-[#1D1D1F] border-b-2 border-[#1D1D1F] font-semibold' : 'text-[#86868B] hover:text-[#1D1D1F]' }}">
                    Ruang Kelas
                </a>
                <a href="{{ route('admin.facility-master', array_merge(request()->query(), ['category' => 'lab'])) }}"
                   class="pb-3 -mb-px transition whitespace-nowrap {{ $currentCat === 'lab' ? 'text-[#1D1D1F] border-b-2 border-[#1D1D1F] font-semibold' : 'text-[#86868B] hover:text-[#1D1D1F]' }}">
                    Laboratorium
                </a>
                <a href="{{ route('admin.facility-master', array_merge(request()->query(), ['category' => 'olahraga'])) }}"
                   class="pb-3 -mb-px transition whitespace-nowrap {{ $currentCat === 'olahraga' ? 'text-[#1D1D1F] border-b-2 border-[#1D1D1F] font-semibold' : 'text-[#86868B] hover:text-[#1D1D1F]' }}">
                    Lapangan Olahraga
                </a>
                <a href="{{ route('admin.facility-master', array_merge(request()->query(), ['category' => 'rapat'])) }}"
                   class="pb-3 -mb-px transition whitespace-nowrap {{ $currentCat === 'rapat' ? 'text-[#1D1D1F] border-b-2 border-[#1D1D1F] font-semibold' : 'text-[#86868B] hover:text-[#1D1D1F]' }}">
                    Ruang Rapat
                </a>
            </div>
        </div>

        {{-- 5. Bento Cards Master Fasilitas (2 Kolom Presisi Apple) --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 sm:gap-8">
            @php
                $categoryLabels = [
                    'auditorium' => 'Auditorium & Hall',
                    'kelas'      => 'Ruang Kelas',
                    'lab'        => 'Laboratorium',
                    'olahraga'   => 'Lapangan Olahraga',
                    'rapat'      => 'Ruang Rapat',
                ];
            @endphp

            @forelse ($facilities as $facility)
                @php
                    // Merapikan string perlengkapan menjadi teks terstruktur rapi
                    $equipmentText = '-';
                    if (is_array($facility->equipment) && count($facility->equipment) > 0) {
                        $equipmentText = implode(', ', $facility->equipment);
                    } elseif (is_string($facility->equipment) && !empty($facility->equipment)) {
                        $equipmentText = $facility->equipment;
                    }
                @endphp

                <div class="bg-white rounded-[2rem] p-7 sm:p-8 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)] flex flex-col justify-between hover:border-gray-200 transition">
                    <div>
                        {{-- Header Fasilitas Card --}}
                        <div class="flex items-start justify-between gap-4 pb-4 mb-4 border-b border-gray-100">
                            <div class="flex items-center gap-3.5 min-w-0">
                                @if ($facility->image_path)
                                    <img src="{{ asset('storage/' . $facility->image_path) }}" alt="{{ $facility->name }}" class="w-12 h-12 rounded-2xl object-cover border border-gray-100 shrink-0">
                                @else
                                    <div class="w-12 h-12 rounded-2xl bg-[#FBFBFD] border border-gray-100 flex items-center justify-center text-[#86868B] shrink-0">
                                        <span class="material-symbols-outlined text-[22px]">domain</span>
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <span class="text-xs font-mono text-[#86868B] block">{{ $facility->code }}</span>
                                    <h3 class="text-lg font-bold text-[#1D1D1F] tracking-tight truncate">{{ $facility->name }}</h3>
                                    <p class="text-xs text-[#86868B] mt-0.5">
                                        {{ $facility->building }}
                                        @if ($facility->floor_location)
                                            <span>• {{ $facility->floor_location }}</span>
                                        @endif
                                    </p>
                                </div>
                            </div>

                            {{-- Pure Typographic Status (Option 1) --}}
                            <span class="text-xs font-semibold shrink-0 {{ $facility->status === 'aktif' ? 'text-emerald-800' : 'text-amber-800' }}">
                                {{ $facility->status === 'aktif' ? 'Tersedia' : 'Pemeliharaan' }}
                            </span>
                        </div>

                        {{-- Spesifikasi Fasilitas --}}
                        <div class="space-y-2 text-xs text-[#86868B] my-4 leading-relaxed">
                            <div>
                                <strong class="text-[#1D1D1F]">Kapasitas:</strong> {{ number_format($facility->capacity, 0, ',', '.') }} Kursi • 
                                <strong class="text-[#1D1D1F]">Kategori:</strong> {{ $categoryLabels[$facility->category] ?? ucfirst($facility->category) }}
                            </div>
                            <div>
                                <strong class="text-[#1D1D1F]">Perlengkapan Standar:</strong> {{ $equipmentText }}
                            </div>
                            @if($facility->description)
                                <p class="text-[11px] text-[#86868B] line-clamp-2 mt-1">
                                    {{ $facility->description }}
                                </p>
                            @endif
                        </div>
                    </div>

                    {{-- Footer Tindakan Manajemen --}}
                    <div class="pt-5 mt-3 border-t border-gray-100 flex items-center justify-between gap-3">
                        {{-- Tombol Toggle Status Operasional --}}
                        <form action="{{ route('admin.facilities.toggle', $facility->id) }}" method="POST" class="inline">
                            @csrf
                            @if ($facility->status === 'aktif')
                                <button type="submit" class="text-xs font-medium text-[#86868B] hover:text-amber-800 transition cursor-pointer">
                                    Kunci untuk Pemeliharaan
                                </button>
                            @else
                                <button type="submit" class="text-xs font-medium text-[#86868B] hover:text-emerald-800 transition cursor-pointer">
                                    Buka Akses Reservasi
                                </button>
                            @endif
                        </form>

                        <div class="flex items-center gap-2">
                            <button type="button" 
                                    @click='openEdit(@json($facility))' 
                                    class="px-4 py-2 rounded-xl bg-gray-100 hover:bg-gray-200 text-[#1D1D1F] text-xs font-medium transition cursor-pointer">
                                Sunting Data
                            </button>

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
                <div class="col-span-full bg-white rounded-[2rem] p-16 text-center border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                    <span class="material-symbols-outlined text-4xl block mb-2 text-gray-300">domain_disabled</span>
                    <h3 class="text-base font-bold text-[#1D1D1F]">Tidak ada data fasilitas yang ditemukan</h3>
                    <p class="text-xs text-[#86868B] mt-1">Silakan sesuaikan kata kunci filter pencarian atau tambahkan fasilitas baru.</p>
                </div>
            @endforelse
        </div>

        {{-- 6. Footer Paginasi --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs text-[#86868B] pt-2">
            <div>
                Menampilkan <strong class="text-[#1D1D1F]">{{ $facilities->firstItem() ?? 0 }}</strong> - <strong class="text-[#1D1D1F]">{{ $facilities->lastItem() ?? 0 }}</strong> dari <strong class="text-[#1D1D1F]">{{ $facilities->total() }}</strong> ruangan terdaftar
            </div>
            <div>
                {{ $facilities->links() }}
            </div>
        </div>

        {{-- ============================================================== --}}
        {{-- MODAL: Formulir Tambah / Edit Fasilitas Kampus                 --}}
        {{-- ============================================================== --}}
        <div x-show="showFacilityModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm">
            <div @click.away="showFacilityModal = false" class="bg-white rounded-[2rem] shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-8 border border-gray-100 flex flex-col gap-6">
                
                {{-- Modal Header --}}
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <div>
                        <h3 class="font-bold text-lg text-[#1D1D1F] font-display" x-text="modalTitle"></h3>
                        <p class="text-xs text-[#86868B] mt-0.5">Lengkapi parameter spesifikasi dan sarana ruangan kampus.</p>
                    </div>
                    <button type="button" @click="showFacilityModal = false" class="text-[#86868B] hover:text-[#1D1D1F] transition cursor-pointer">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                {{-- Formulir Input Data Fasilitas --}}
                <form :action="formAction" method="POST" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <template x-if="isEdit">
                        @method('PUT')
                    </template>
                    <input type="hidden" name="id" :value="facility.id">

                    <div class="grid grid-cols-3 gap-3">
                        <div class="col-span-2">
                            <label class="block text-xs font-semibold text-[#1D1D1F] mb-1.5" for="fac-name">Nama Ruangan <span class="text-rose-500">*</span></label>
                            <input type="text" id="fac-name" name="name" x-model="facility.name" required placeholder="Contoh: Auditorium Prof. Soedarto" class="w-full h-10 px-3.5 bg-[#FBFBFD] rounded-xl text-xs border border-gray-200 text-[#1D1D1F] focus:bg-white focus:border-[#1D1D1F] focus:outline-none transition">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#1D1D1F] mb-1.5" for="fac-code">Kode Ruang <span class="text-rose-500">*</span></label>
                            <input type="text" id="fac-code" name="code" x-model="facility.code" required placeholder="AUD-AC-01" class="w-full h-10 px-3.5 bg-[#FBFBFD] rounded-xl text-xs font-mono uppercase border border-gray-200 text-[#1D1D1F] focus:bg-white focus:border-[#1D1D1F] focus:outline-none transition">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#1D1D1F] mb-1.5" for="fac-type">Kategori Fasilitas <span class="text-rose-500">*</span></label>
                            <select id="fac-type" name="category" x-model="facility.category" required class="w-full h-10 px-3 bg-[#FBFBFD] rounded-xl text-xs border border-gray-200 text-[#1D1D1F] focus:bg-white focus:border-[#1D1D1F] focus:outline-none transition">
                                <option value="auditorium">Auditorium & Hall</option>
                                <option value="kelas">Ruang Kelas</option>
                                <option value="lab">Laboratorium</option>
                                <option value="olahraga">Lapangan Olahraga</option>
                                <option value="rapat">Ruang Rapat</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#1D1D1F] mb-1.5" for="fac-cap">Kapasitas (Kursi) <span class="text-rose-500">*</span></label>
                            <input type="number" id="fac-cap" name="capacity" x-model="facility.capacity" min="1" required class="w-full h-10 px-3.5 bg-[#FBFBFD] rounded-xl text-xs border border-gray-200 text-[#1D1D1F] focus:bg-white focus:border-[#1D1D1F] focus:outline-none transition">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#1D1D1F] mb-1.5" for="fac-bld">Lokasi Gedung <span class="text-rose-500">*</span></label>
                            <input type="text" id="fac-bld" name="building" x-model="facility.building" required placeholder="Contoh: Gedung Rektorat Utama" class="w-full h-10 px-3.5 bg-[#FBFBFD] rounded-xl text-xs border border-gray-200 text-[#1D1D1F] focus:bg-white focus:border-[#1D1D1F] focus:outline-none transition">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#1D1D1F] mb-1.5" for="fac-flr">Keterangan Lantai</label>
                            <input type="text" id="fac-flr" name="floor_location" x-model="facility.floor_location" placeholder="Contoh: Lantai 2 Sayap Barat" class="w-full h-10 px-3.5 bg-[#FBFBFD] rounded-xl text-xs border border-gray-200 text-[#1D1D1F] focus:bg-white focus:border-[#1D1D1F] focus:outline-none transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#1D1D1F] mb-1.5" for="fac-eq">Perlengkapan Standar (Pisahkan dengan koma)</label>
                        <input type="text" id="fac-eq" name="equipment" x-model="facility.equipment" placeholder="Videotron 4K, Tata Suara 10kW, AC Sentral, Podium VIP" class="w-full h-10 px-3.5 bg-[#FBFBFD] rounded-xl text-xs border border-gray-200 text-[#1D1D1F] focus:bg-white focus:border-[#1D1D1F] focus:outline-none transition">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#1D1D1F] mb-1.5" for="fac-desc">Deskripsi Fasilitas</label>
                        <textarea id="fac-desc" name="description" x-model="facility.description" rows="2" placeholder="Keterangan peruntukan ruangan kampus..." class="w-full p-3 bg-[#FBFBFD] rounded-xl text-xs border border-gray-200 text-[#1D1D1F] focus:bg-white focus:border-[#1D1D1F] focus:outline-none transition resize-none"></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#1D1D1F] mb-1.5" for="fac-img">Foto Sampul (Cover Image)</label>
                            <input type="file" id="fac-img" name="cover_image" accept="image/png,image/jpeg,image/jpg" class="w-full text-xs text-[#86868B] file:mr-2 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-gray-100 file:text-[#1D1D1F] hover:file:bg-gray-200 cursor-pointer">
                            <span class="text-[11px] text-[#86868B] block mt-1">JPG / PNG, Maksimal 2MB</span>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#1D1D1F] mb-1.5" for="fac-status">Status Operasional Awal</label>
                            <select id="fac-status" name="status" x-model="facility.status" class="w-full h-10 px-3 bg-[#FBFBFD] rounded-xl text-xs border border-gray-200 text-[#1D1D1F] focus:bg-white focus:border-[#1D1D1F] focus:outline-none transition">
                                <option value="aktif">Aktif (Tersedia untuk Reservasi)</option>
                                <option value="dalam perbaikan">Dalam Pemeliharaan (Terkunci)</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="showFacilityModal = false" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-[#86868B] hover:text-[#1D1D1F] transition cursor-pointer">Batal</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#1D1D1F] text-white text-xs font-semibold hover:bg-black transition shadow-sm cursor-pointer" x-text="isEdit ? 'Perbarui Fasilitas' : 'Simpan Fasilitas Baru'"></button>
                    </div>
                </form>

            </div>
        </div>

        {{-- ============================================================== --}}
        {{-- MODAL: Konfirmasi Nonaktifkan Fasilitas (Soft Delete)          --}}
        {{-- ============================================================== --}}
        <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm">
            <div @click.away="showDeleteModal = false" class="bg-white rounded-[2rem] shadow-2xl max-w-sm w-full p-8 border border-gray-100 flex flex-col gap-5">
                
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[22px]">warning</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-[#1D1D1F] text-sm">Nonaktifkan Fasilitas?</h3>
                        <p class="text-xs text-[#86868B] font-mono mt-0.5" x-text="deleteTarget.code"></p>
                    </div>
                </div>

                <p class="text-xs text-[#86868B] leading-relaxed">
                    Fasilitas <strong class="text-[#1D1D1F]" x-text="deleteTarget.name"></strong> akan dicabut dari katalog publik. Riwayat reservasi lampau tetap tersimpan aman melalui mekanisme arsip (Soft Delete).
                </p>

                <form :action="'{{ url('/admin/facilities') }}/' + deleteTarget.id" method="POST" class="flex items-center justify-end gap-3 pt-2 border-t border-gray-100">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="showDeleteModal = false" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-[#86868B] hover:text-[#1D1D1F] transition cursor-pointer">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-white border border-rose-200 text-rose-800 text-xs font-semibold hover:bg-rose-50 transition shadow-sm cursor-pointer">
                        Ya, Nonaktifkan
                    </button>
                </form>

            </div>
        </div>

    </div>
</x-admin-layout>
