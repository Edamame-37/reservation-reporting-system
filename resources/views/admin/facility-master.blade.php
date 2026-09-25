{{-- 
  NAMA FILE      : facility-master.blade.php
  FUNGSIONALITAS : Halaman Master Data Inventaris Fasilitas Kampus (Super Admin)
  DESKRIPSI      : Menampilkan seluruh inventaris ruangan kampus dinamis, fitur CRUD (Tambah, Edit, Non-aktifkan fasilitas), upload foto cover, dan penguncian status operasional fasilitas (UR16 / ADM-03).
  CARA KERJA     : Memanfaatkan layout <x-admin-layout active="facility-master"> dengan Alpine.js untuk pencarian, filter, dan dialog modal manajemen fasilitas yang terhubung ke FacilityController.
--}}

<x-admin-layout title="Master Data Fasilitas Kampus" active="facility-master">
    <!-- 
      ELEMEN       : Wadah Komponen Utama dengan State Alpine.js
      KEGUNAAN     : Mengelola reaktivitas dialog modal form tambah/edit, modal konfirmasi nonaktifkan, dan sinkronisasi filter data.
      CARA KERJA   : Menyimpan state modal (showFacilityModal, showDeleteModal, isEdit), payload formulir (facility), dan target penghapusan (deleteTarget).
    -->
    <div x-data="{
        showFacilityModal: false,
        showDeleteModal: false,
        isEdit: false,
        modalTitle: 'Tambah Fasilitas Kampus Baru (UR16)',
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
            this.modalTitle = 'Tambah Fasilitas Kampus Baru (UR16)';
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
            this.modalTitle = 'Perbarui Data Fasilitas: ' + item.name;
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
    }" class="space-y-6">

        {{-- Page Header & Top Action Button --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <nav class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                    <a href="{{ url('/admin/dashboard') }}" class="hover:text-slate-900 transition-colors">Admin Console</a>
                    <span>/</span>
                    <span class="text-slate-800 font-medium">Master Fasilitas</span>
                </nav>
                <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Master Data Fasilitas & Ruang Kampus</h1>
                <p class="text-sm text-slate-500 mt-0.5">Kelola data inventaris venue, alokasi kapasitas, sarana kelistrikan/audio, dan status operasional sistem (UR16).</p>
            </div>

            <div class="flex items-center gap-2.5">
                <button type="button" @click="openAdd()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 shadow-md hover:shadow-lg transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-[18px]">add_circle</span>
                    <span>+ Tambah Fasilitas (UR16)</span>
                </button>
            </div>
        </div>

        {{-- Session Flash Notifications --}}
        @if (session('success'))
            <!-- 
              ELEMEN   : Alert Notifikasi Sukses
              KEGUNAAN : Memberikan umpan balik visual saat penambahan, pembaruan, toggle status, atau soft delete berhasil.
            -->
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[20px] text-emerald-600">check_circle</span>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if (session('error'))
            <!-- 
              ELEMEN   : Alert Notifikasi Error
              KEGUNAAN : Memberikan peringatan visual saat operasi transaksi gagal diproses server.
            -->
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800 flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[20px] text-rose-600">error</span>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <!-- 
              ELEMEN   : Alert Notifikasi Kesalahan Validasi Input
              KEGUNAAN : Memberikan rincian pesan error penolakan validasi form kepada pengguna.
            -->
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800 flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[20px] text-rose-600">warning</span>
                    <div>
                        <p class="font-semibold mb-0.5">Terdapat kesalahan validasi formulir:</p>
                        <ul class="list-disc list-inside space-y-0.5 text-slate-700">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        {{-- Quick Stat Summary Cards (Dinamis dari Controller) --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="text-xs font-medium text-slate-500 mb-1">Total Ruangan Terdata</div>
                <div class="text-2xl font-bold text-slate-800">{{ $totalCount ?? 0 }} <span class="text-xs font-normal text-slate-400">venue</span></div>
                <div class="text-[11px] text-emerald-600 mt-1 font-medium flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">domain</span> {{ $buildingCount ?? 0 }} Gedung Utama
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="text-xs font-medium text-slate-500 mb-1">Status Operasional Aktif</div>
                <div class="text-2xl font-bold text-emerald-600">{{ $activeCount ?? 0 }} <span class="text-xs font-normal text-slate-400">venue</span></div>
                <div class="text-[11px] text-slate-500 mt-1">Dapat direservasi oleh sivitas</div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="text-xs font-medium text-slate-500 mb-1">Terkunci / Perbaikan</div>
                <div class="text-2xl font-bold text-amber-600">{{ $maintenanceCount ?? 0 }} <span class="text-xs font-normal text-slate-400">venue</span></div>
                <div class="text-[11px] text-amber-700 mt-1 font-medium">Dalam pemeliharaan teknisi</div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs">
                <div class="text-xs font-medium text-slate-500 mb-1">Total Kapasitas Kursi</div>
                <div class="text-2xl font-bold text-slate-800">{{ number_format($totalCapacity ?? 0, 0, ',', '.') }} <span class="text-xs font-normal text-slate-400">kursi</span></div>
                <div class="text-[11px] text-slate-500 mt-1">Akumulasi seluruh kampus</div>
            </div>
        </div>

        {{-- Main Table Container --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            {{-- Search & Filter Bar --}}
            <!-- 
              ROUTE: GET /admin/facility-master
              FUNGSI: Mengirim parameter filter pencarian teks bebas, kategori, dan status operasional ke Controller.
            -->
            <form method="GET" action="{{ route('admin.facility-master') }}" class="p-4 sm:p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                {{-- Category Filter Pills --}}
                <div class="flex flex-wrap items-center gap-1.5">
                    @php
                        $currentCat = request('category', 'all');
                    @endphp
                    <a href="{{ route('admin.facility-master', array_merge(request()->query(), ['category' => 'all'])) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $currentCat === 'all' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        Semua Kategori ({{ $totalCount ?? 0 }})
                    </a>
                    <a href="{{ route('admin.facility-master', array_merge(request()->query(), ['category' => 'auditorium'])) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $currentCat === 'auditorium' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        Auditorium
                    </a>
                    <a href="{{ route('admin.facility-master', array_merge(request()->query(), ['category' => 'kelas'])) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $currentCat === 'kelas' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        Ruang Kelas
                    </a>
                    <a href="{{ route('admin.facility-master', array_merge(request()->query(), ['category' => 'lab'])) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $currentCat === 'lab' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        Laboratorium
                    </a>
                    <a href="{{ route('admin.facility-master', array_merge(request()->query(), ['category' => 'olahraga'])) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $currentCat === 'olahraga' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        Olahraga
                    </a>
                    <a href="{{ route('admin.facility-master', array_merge(request()->query(), ['category' => 'rapat'])) }}"
                       class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all {{ $currentCat === 'rapat' ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}">
                        Ruang Rapat
                    </a>
                </div>

                {{-- Search & Status Filter --}}
                <div class="flex items-center gap-2.5">
                    <input type="hidden" name="category" value="{{ request('category', 'all') }}">

                    <select name="status" onchange="this.form.submit()" class="h-9 px-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-700 focus:bg-white focus:border-slate-800 focus:outline-none">
                        <option value="all" {{ request('status', 'all') === 'all' ? 'selected' : '' }}>Semua Status</option>
                        <option value="aktif" {{ request('status') === 'aktif' ? 'selected' : '' }}>Aktif</option>
                        <option value="dalam perbaikan" {{ request('status') === 'dalam perbaikan' ? 'selected' : '' }}>Dalam Perbaikan</option>
                    </select>

                    <div class="relative w-full sm:w-56">
                        <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-[18px]">search</span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama / kode ruang..." class="w-full h-9 pl-9 pr-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-slate-800 focus:outline-none transition-colors">
                    </div>

                    <button type="submit" class="px-3.5 h-9 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 shadow-xs transition-colors cursor-pointer">
                        Cari
                    </button>

                    @if(request('search') || (request('category') && request('category') !== 'all') || (request('status') && request('status') !== 'all'))
                        <a href="{{ route('admin.facility-master') }}" class="px-2 h-9 flex items-center text-xs text-slate-500 hover:text-slate-800" title="Reset Filter">
                            <span class="material-symbols-outlined text-[18px]">refresh</span>
                        </a>
                    @endif
                </div>
            </form>

            {{-- Table of Facilities (UR16 - Data Dinamis) --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-100">
                            <th class="py-3 px-5">Kode & Nama Ruangan</th>
                            <th class="py-3 px-4">Tipe & Lokasi Gedung</th>
                            <th class="py-3 px-4">Kapasitas</th>
                            <th class="py-3 px-4">Perlengkapan Standar</th>
                            <th class="py-3 px-4">Jam Operasional</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-5 text-right">Aksi Manajemen (UR16)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
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
                            <tr class="hover:bg-slate-50/60 transition-colors {{ $facility->status === 'dalam perbaikan' ? 'bg-amber-50/20' : '' }}">
                                <td class="py-3.5 px-5">
                                    <div class="flex items-center gap-3">
                                        @if ($facility->image_path)
                                            <img src="{{ asset('storage/' . $facility->image_path) }}" alt="{{ $facility->name }}" class="w-10 h-10 rounded-lg object-cover border border-slate-200 shrink-0">
                                        @else
                                            <div class="w-10 h-10 rounded-lg bg-slate-100 flex items-center justify-center text-slate-400 shrink-0">
                                                <span class="material-symbols-outlined text-[20px]">meeting_room</span>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="font-semibold text-slate-800 text-sm">{{ $facility->name }}</div>
                                            <div class="font-mono text-[11px] text-slate-500">{{ $facility->code }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-medium text-slate-800">
                                        {{ $categoryLabels[$facility->category] ?? ucfirst($facility->category) }}
                                    </div>
                                    <div class="text-[11px] text-slate-500">
                                        {{ $facility->building }}
                                        @if ($facility->floor_location)
                                            <span class="text-slate-400">• {{ $facility->floor_location }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 font-semibold text-slate-800">
                                    {{ number_format($facility->capacity, 0, ',', '.') }} Orang
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex flex-wrap gap-1 max-w-xs">
                                        @if (is_array($facility->equipment) && count($facility->equipment) > 0)
                                            @foreach (array_slice($facility->equipment, 0, 3) as $tool)
                                                <span class="px-1.5 py-0.5 rounded bg-slate-100 text-[10px] text-slate-600">{{ $tool }}</span>
                                            @endforeach
                                            @if (count($facility->equipment) > 3)
                                                <span class="px-1.5 py-0.5 rounded bg-slate-100 text-[10px] text-slate-400">+{{ count($facility->equipment) - 3 }}</span>
                                            @endif
                                        @else
                                            <span class="text-slate-400 text-[11px]">-</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-slate-600 text-[11px]">
                                    07:00 - 20:00 WIB
                                </td>
                                <td class="py-3.5 px-4">
                                    @if ($facility->status === 'aktif')
                                        <x-cava.status-badge status="active" label="Aktif" />
                                    @elseif ($facility->status === 'dalam perbaikan')
                                        <x-cava.status-badge status="locked" label="Dalam Perbaikan" />
                                    @else
                                        <x-cava.status-badge status="cancelled" label="Nonaktif" />
                                    @endif
                                </td>
                                <td class="py-3.5 px-5 text-right">
                                    <div class="inline-flex items-center gap-1.5 justify-end">
                                        <!-- Tombol Edit Fasilitas -->
                                        <button type="button" @click='openEdit(@json($facility))' class="px-2.5 py-1.5 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 font-medium transition-colors" title="Edit Fasilitas">
                                            Edit
                                        </button>

                                        <!-- 
                                          ROUTE: POST /admin/facilities/{id}/toggle
                                          FUNGSI: Mengubah status operasional fasilitas (aktif <-> dalam perbaikan) secara cepat (ADM-03)
                                        -->
                                        <form action="{{ route('admin.facilities.toggle', $facility->id) }}" method="POST" class="inline">
                                            @csrf
                                            @if ($facility->status === 'aktif')
                                                <button type="submit" class="px-2.5 py-1.5 rounded-lg border border-amber-200 text-amber-700 hover:bg-amber-50 font-medium transition-colors" title="Kunci untuk pemeliharaan/perbaikan">
                                                    Kunci
                                                </button>
                                            @else
                                                <button type="submit" class="px-2.5 py-1.5 rounded-lg border border-emerald-200 text-emerald-700 hover:bg-emerald-50 font-medium transition-colors" title="Aktifkan operasional">
                                                    Aktifkan
                                                </button>
                                            @endif
                                        </form>

                                        <!-- Tombol Nonaktifkan (Soft Delete) -->
                                        <button type="button" @click="openDelete({{ $facility->id }}, '{{ addslashes($facility->name) }}', '{{ $facility->code }}')" class="px-2.5 py-1.5 rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 font-medium transition-colors" title="Nonaktifkan Fasilitas (Soft Delete)">
                                            Nonaktifkan
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-2">
                                        <span class="material-symbols-outlined text-[40px] text-slate-300">domain_disabled</span>
                                        <p class="text-sm font-medium text-slate-600">Tidak ada data fasilitas yang ditemukan.</p>
                                        <p class="text-xs text-slate-400">Silakan sesuaikan kata kunci pencarian atau tambah fasilitas baru.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Table Footer Pagination (Dinamis dari Paginator) --}}
            <div class="p-4 bg-slate-50/50 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between text-xs text-slate-500 gap-2">
                <div>
                    Menampilkan <strong>{{ $facilities->firstItem() ?? 0 }}</strong> - <strong>{{ $facilities->lastItem() ?? 0 }}</strong> dari <strong>{{ $facilities->total() }}</strong> ruangan kampus
                </div>
                <div>
                    {{ $facilities->links() }}
                </div>
            </div>
        </div>

        {{-- MODAL: Tambah / Edit Fasilitas (UR16) --}}
        <!-- 
          ELEMEN       : Modal Formulir Tambah / Edit Fasilitas (UR16)
          KEGUNAAN     : Menyediakan formulir input spesifikasi ruangan kampus baru atau memperbarui data yang sudah ada.
          CARA KERJA   : Dikendalikan oleh state Alpine.js (showFacilityModal, isEdit). Mendukung unggah berkas foto cover via multipart/form-data.
        -->
        <div x-show="showFacilityModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="showFacilityModal = false" class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-lg w-full p-6 flex flex-col gap-4 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2 text-slate-900">
                        <span class="material-symbols-outlined text-[22px]">domain</span>
                        <h3 class="font-bold text-base" x-text="modalTitle"></h3>
                    </div>
                    <button type="button" @click="showFacilityModal = false" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                <!-- 
                  ROUTE: Mengirimkan form data via POST / PUT multipart/form-data ke endpoint /admin/facilities
                  FUNGSI: Menyimpan data fasilitas baru atau memperbarui spesifikasi dan cover image pada database.
                -->
                <form :action="formAction" method="POST" enctype="multipart/form-data" class="space-y-3.5">
                    @csrf
                    <template x-if="isEdit">
                        @method('PUT')
                    </template>
                    <input type="hidden" name="id" :value="facility.id">

                    <div class="grid grid-cols-3 gap-3">
                        <div class="col-span-2">
                            <label class="block text-xs font-semibold text-slate-700 mb-1" for="fac-name">Nama Ruangan <span class="text-rose-500">*</span></label>
                            <input type="text" id="fac-name" name="name" x-model="facility.name" required placeholder="Contoh: Auditorium B.J. Habibie" class="w-full h-9 px-3 bg-slate-50 rounded-xl text-xs border border-slate-200 focus:bg-white focus:border-slate-800 focus:outline-none">
                            @error('name') <p class="text-rose-600 text-[11px] mt-0.5">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1" for="fac-code">Kode Ruang <span class="text-rose-500">*</span></label>
                            <input type="text" id="fac-code" name="code" x-model="facility.code" required placeholder="AUD-H01" class="w-full h-9 px-3 bg-slate-50 rounded-xl text-xs font-mono uppercase border border-slate-200 focus:bg-white focus:border-slate-800 focus:outline-none">
                            @error('code') <p class="text-rose-600 text-[11px] mt-0.5">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1" for="fac-type">Kategori Fasilitas <span class="text-rose-500">*</span></label>
                            <select id="fac-type" name="category" x-model="facility.category" required class="w-full h-9 px-3 bg-slate-50 rounded-xl text-xs border border-slate-200 focus:bg-white focus:border-slate-800 focus:outline-none">
                                <option value="auditorium">Auditorium & Hall</option>
                                <option value="kelas">Ruang Kelas</option>
                                <option value="lab">Laboratorium</option>
                                <option value="olahraga">Lapangan Olahraga</option>
                                <option value="rapat">Ruang Rapat</option>
                            </select>
                            @error('category') <p class="text-rose-600 text-[11px] mt-0.5">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1" for="fac-cap">Kapasitas (Orang) <span class="text-rose-500">*</span></label>
                            <input type="number" id="fac-cap" name="capacity" x-model="facility.capacity" min="1" required class="w-full h-9 px-3 bg-slate-50 rounded-xl text-xs border border-slate-200 focus:bg-white focus:border-slate-800 focus:outline-none">
                            @error('capacity') <p class="text-rose-600 text-[11px] mt-0.5">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1" for="fac-bld">Lokasi Gedung <span class="text-rose-500">*</span></label>
                            <input type="text" id="fac-bld" name="building" x-model="facility.building" required placeholder="Contoh: Gedung Rektorat Baru" class="w-full h-9 px-3 bg-slate-50 rounded-xl text-xs border border-slate-200 focus:bg-white focus:border-slate-800 focus:outline-none">
                            @error('building') <p class="text-rose-600 text-[11px] mt-0.5">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1" for="fac-flr">Keterangan Lantai</label>
                            <input type="text" id="fac-flr" name="floor_location" x-model="facility.floor_location" placeholder="Contoh: Lantai 1 & 2" class="w-full h-9 px-3 bg-slate-50 rounded-xl text-xs border border-slate-200 focus:bg-white focus:border-slate-800 focus:outline-none">
                            @error('floor_location') <p class="text-rose-600 text-[11px] mt-0.5">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1" for="fac-eq">Perlengkapan Standar (Pisahkan dengan Koma)</label>
                        <input type="text" id="fac-eq" name="equipment" x-model="facility.equipment" placeholder="Contoh: Proyektor HD, AC Sentral, Sound System, 4 Mic" class="w-full h-9 px-3 bg-slate-50 rounded-xl text-xs border border-slate-200 focus:bg-white focus:border-slate-800 focus:outline-none">
                        <span class="text-[10px] text-slate-400">Contoh format: Proyektor HD, Sound System, AC Sentral</span>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1" for="fac-desc">Deskripsi Fasilitas</label>
                        <textarea id="fac-desc" name="description" x-model="facility.description" rows="2" placeholder="Tuliskan keterangan detail fungsi ruangan..." class="w-full p-2.5 bg-slate-50 rounded-xl text-xs border border-slate-200 focus:bg-white focus:border-slate-800 focus:outline-none"></textarea>
                        @error('description') <p class="text-rose-600 text-[11px] mt-0.5">{{ $message }}</p> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1" for="fac-img">Foto Sampul (Cover Image)</label>
                            <input type="file" id="fac-img" name="cover_image" accept="image/png,image/jpeg,image/jpg" class="w-full text-xs text-slate-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 cursor-pointer">
                            <span class="text-[10px] text-slate-400 block mt-0.5">JPG / PNG, Maksimal 2MB</span>
                            <template x-if="isEdit && facility.image_path">
                                <span class="text-[10px] text-emerald-600 block mt-0.5 font-medium">✓ Sudah ada gambar cover terpasang</span>
                            </template>
                            @error('cover_image') <p class="text-rose-600 text-[11px] mt-0.5">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1" for="fac-status">Status Operasional Awal</label>
                            <select id="fac-status" name="status" x-model="facility.status" class="w-full h-9 px-3 bg-slate-50 rounded-xl text-xs border border-slate-200 focus:bg-white focus:border-slate-800 focus:outline-none">
                                <option value="aktif">Aktif (Dapat Direservasi)</option>
                                <option value="dalam perbaikan">Dalam Perbaikan (Terkunci)</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="showFacilityModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 shadow-sm transition-colors cursor-pointer" x-text="isEdit ? 'Perbarui Fasilitas' : 'Simpan Fasilitas Baru'"></button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL: Konfirmasi Nonaktifkan Fasilitas (Soft Delete) --}}
        <!-- 
          ELEMEN       : Modal Konfirmasi Penonaktifan Fasilitas (Soft Delete)
          KEGUNAAN     : Mencegah penghapusan tak sengaja dengan konfirmasi eksplisit sebelum mencabut status operasional ruangan.
          CARA KERJA   : Terbuka saat showDeleteModal bernilai true. Form mengirimkan request DELETE ke route('facilities.destroy', id).
        -->
        <div x-show="showDeleteModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="showDeleteModal = false" class="bg-white rounded-2xl border border-slate-200 shadow-xl max-w-sm w-full p-6 flex flex-col gap-4">
                <div class="flex items-center gap-3 text-rose-600">
                    <div class="w-10 h-10 rounded-full bg-rose-50 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[24px]">warning</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm text-slate-800">Nonaktifkan Fasilitas?</h3>
                        <p class="text-xs text-slate-500 font-mono" x-text="deleteTarget.code"></p>
                    </div>
                </div>

                <p class="text-xs text-slate-600 leading-relaxed">
                    Fasilitas <strong x-text="deleteTarget.name"></strong> akan dicabut dari katalog publik. Riwayat reservasi masa lalu akan tetap tersimpan secara aman melalui mekanisme <em>Soft Delete</em>.
                </p>

                <!-- 
                  ROUTE: DELETE /admin/facilities/{id}
                  FUNGSI: Menjalankan soft delete fasilitas dan mengubah status menjadi nonaktif (ADM-03)
                -->
                <form :action="'{{ url('/admin/facilities') }}/' + deleteTarget.id" method="POST" class="flex items-center justify-end gap-2 pt-2">
                    @csrf
                    @method('DELETE')
                    <button type="button" @click="showDeleteModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition-colors">
                        Batal
                    </button>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 text-white text-xs font-semibold hover:bg-rose-700 shadow-sm transition-colors">
                        Ya, Nonaktifkan
                    </button>
                </form>
            </div>
        </div>

    </div>
</x-admin-layout>
