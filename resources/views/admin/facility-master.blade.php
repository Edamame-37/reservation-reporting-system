{{-- 
  NAMA FILE      : facility-master.blade.php
  FUNGSIONALITAS : Halaman Antarmuka Master Data Fasilitas Kampus (Super Admin)
  DESKRIPSI      : Menampilkan seluruh inventaris ruangan kampus, fitur CRUD (Tambah, Edit, Non-aktifkan fasilitas), dan penguncian status operasional (UR16).
  CARA KERJA     : Memanfaatkan layout <x-admin-layout active="facility-master">, mengelola modal formulir tambah/edit fasilitas kampus via Alpine.js.
--}}

<x-admin-layout title="Master Data Fasilitas Kampus" active="facility-master">
    <div x-data="{
        showFacilityModal: false,
        modalTitle: 'Tambah Fasilitas Baru (UR16)',
        facility: { id: '', name: '', code: '', type: 'auditorium', location: '', capacity: 100, openHours: '07:00 - 20:00 WIB', status: 'aktif' },
        openAdd() {
            this.modalTitle = 'Tambah Fasilitas Baru (UR16)';
            this.facility = { id: '', name: '', code: '', type: 'auditorium', location: '', capacity: 100, openHours: '07:00 - 20:00 WIB', status: 'aktif' };
            this.showFacilityModal = true;
        },
        openEdit(item) {
            this.modalTitle = 'Edit Data Fasilitas: ' + item.name;
            this.facility = Object.assign({}, item);
            this.showFacilityModal = true;
        }
    }" class="flex flex-col gap-space-xl">
        <!-- 
          ELEMEN       : Section Master Data Fasilitas (UR16)
          KEGUNAAN     : Pengelolaan data inventaris ruangan, penambahan gedung baru, pembaruan kapasitas, dan penonaktifan fasilitas.
          CARA KERJA   : Super Admin mengontrol data fasilitas yang akan ditampilkan pada modul pencarian publik dan matriks pemesanan pengguna.
        -->
        <section class="flex flex-col rounded-xl bg-surface-container-lowest shadow-sm overflow-hidden border border-outline-variant/50">
            {{-- Header & Action Controls --}}
            <div class="p-space-lg bg-surface-container-low flex flex-col xl:flex-row items-start xl:items-center justify-between gap-space-md border-b border-outline-variant">
                <div class="flex items-center gap-space-md">
                    <div class="w-10 h-10 rounded-lg bg-primary-container text-on-primary flex items-center justify-center shadow-sm">
                        <span class="material-symbols-outlined text-[22px]">domain</span>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center gap-space-sm">
                            <span class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant font-data-mono text-[11px]">UR16 • MASTER INVENTARIS</span>
                            <span class="px-2 py-0.5 rounded font-label-sm text-label-sm bg-surface-container-high text-primary font-bold">6 Fasilitas Terdata</span>
                        </div>
                        <h2 class="font-headline-md text-headline-md text-primary">Master Data Fasilitas Kampus & Inventarisasi Ruang</h2>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-space-sm w-full xl:w-auto">
                    <div class="relative flex-1 sm:w-64">
                        <span class="material-symbols-outlined absolute left-2.5 top-2 text-outline text-[18px]">search</span>
                        <input type="text" placeholder="Cari nama ruang, gedung, kode..." class="w-full h-9 pl-9 pr-space-md rounded-lg bg-surface-container-lowest text-body-sm text-on-surface placeholder:text-on-surface-variant border border-outline-variant/60 focus:border-primary focus:outline-none shadow-sm">
                    </div>
                    <button type="button" @click="openAdd()" class="h-9 px-space-md rounded-lg bg-primary text-on-primary font-label-sm text-label-sm shadow-sm hover:bg-primary-container transition-colors flex items-center gap-1 font-semibold">
                        <span class="material-symbols-outlined text-[16px]">add_circle</span>
                        <span>+ Tambah Fasilitas (UR16)</span>
                    </button>
                </div>
            </div>

            {{-- Tabel Master Fasilitas --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-container-high text-on-surface-variant font-label-sm text-label-sm uppercase">
                            <th class="py-space-sm px-space-lg">Kode & Nama Ruangan</th>
                            <th class="py-space-sm px-space-md">Tipe & Lokasi Gedung</th>
                            <th class="py-space-sm px-space-md">Kapasitas</th>
                            <th class="py-space-sm px-space-md">Jam Operasional</th>
                            <th class="py-space-sm px-space-md">Status Operasional</th>
                            <th class="py-space-sm px-space-lg text-right">Aksi Manajemen</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container-high/40 font-body-sm text-body-sm text-on-surface">
                        {{-- Row 1 --}}
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="py-space-md px-space-lg">
                                <div class="flex flex-col">
                                    <span class="font-label-lg text-label-lg text-primary font-bold">Auditorium B.J. Habibie</span>
                                    <span class="font-data-mono text-data-mono text-on-surface-variant text-[11px]">AUD-H01</span>
                                </div>
                            </td>
                            <td class="py-space-md px-space-md">
                                <div class="font-semibold">Auditorium & Hall</div>
                                <span class="text-on-surface-variant text-[12px]">Gedung Rektorat Baru Lt. 1 & 2</span>
                            </td>
                            <td class="py-space-md px-space-md font-data-mono font-bold">450 Orang</td>
                            <td class="py-space-md px-space-md font-data-mono text-[11px] text-on-surface-variant">07:00 - 20:00 WIB</td>
                            <td class="py-space-md px-space-md">
                                <x-cava.status-badge status="Aktif" />
                            </td>
                            <td class="py-space-md px-space-lg text-right">
                                <div class="inline-flex items-center gap-1">
                                    <button type="button" @click="openEdit({
                                        id: 1, name: 'Auditorium B.J. Habibie', code: 'AUD-H01', type: 'auditorium', location: 'Gedung Rektorat Baru Lt. 1 & 2', capacity: 450, openHours: '07:00 - 20:00 WIB', status: 'aktif'
                                    })" class="px-space-sm py-1 rounded bg-surface-container text-primary font-label-sm font-semibold hover:bg-surface-container-highest transition-colors">
                                        Edit
                                    </button>
                                    <!-- 
                                      ROUTE: POST /admin/facilities/{id}/toggle
                                      FUNGSI: Menonaktifkan atau mengaktifkan status fasilitas kampus (UR16)
                                    -->
                                    <form action="{{ url('/admin/facilities/1/toggle') }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-space-sm py-1 rounded bg-surface-container text-error hover:bg-error-container hover:text-on-error-container font-label-sm font-semibold transition-colors">
                                            Non-Aktifkan
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        {{-- Row 2 --}}
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="py-space-md px-space-lg">
                                <div class="flex flex-col">
                                    <span class="font-label-lg text-label-lg text-primary font-bold">Lab Jaringan & Cloud</span>
                                    <span class="font-data-mono text-data-mono text-on-surface-variant text-[11px]">LAB-C204</span>
                                </div>
                            </td>
                            <td class="py-space-md px-space-md">
                                <div class="font-semibold">Lab Komputer</div>
                                <span class="text-on-surface-variant text-[12px]">Gedung Lab Barat Lt. 2</span>
                            </td>
                            <td class="py-space-md px-space-md font-data-mono font-bold">45 Orang</td>
                            <td class="py-space-md px-space-md font-data-mono text-[11px] text-on-surface-variant">07:00 - 20:00 WIB</td>
                            <td class="py-space-md px-space-md">
                                <x-cava.status-badge status="Aktif" />
                            </td>
                            <td class="py-space-md px-space-lg text-right">
                                <div class="inline-flex items-center gap-1">
                                    <button type="button" @click="openEdit({
                                        id: 2, name: 'Lab Jaringan & Cloud', code: 'LAB-C204', type: 'lab', location: 'Gedung Lab Barat Lt. 2', capacity: 45, openHours: '07:00 - 20:00 WIB', status: 'aktif'
                                    })" class="px-space-sm py-1 rounded bg-surface-container text-primary font-label-sm font-semibold hover:bg-surface-container-highest transition-colors">
                                        Edit
                                    </button>
                                    <form action="{{ url('/admin/facilities/2/toggle') }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-space-sm py-1 rounded bg-surface-container text-error hover:bg-error-container hover:text-on-error-container font-label-sm font-semibold transition-colors">
                                            Non-Aktifkan
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        {{-- Row 3: Maintenance --}}
                        <tr class="hover:bg-surface-container-low transition-colors bg-error-container/5">
                            <td class="py-space-md px-space-lg">
                                <div class="flex flex-col">
                                    <span class="font-label-lg text-label-lg text-primary font-bold">Lab Hardware & Robotika 2</span>
                                    <span class="font-data-mono text-data-mono text-on-surface-variant text-[11px]">LAB-HW02</span>
                                </div>
                            </td>
                            <td class="py-space-md px-space-md">
                                <div class="font-semibold">Lab Komputer / Riset</div>
                                <span class="text-on-surface-variant text-[12px]">Gedung Lab Barat Lt. 1</span>
                            </td>
                            <td class="py-space-md px-space-md font-data-mono font-bold">30 Orang</td>
                            <td class="py-space-md px-space-md font-data-mono text-[11px] text-on-surface-variant">07:00 - 20:00 WIB</td>
                            <td class="py-space-md px-space-md">
                                <x-cava.status-badge status="Dalam Perbaikan" />
                            </td>
                            <td class="py-space-md px-space-lg text-right">
                                <div class="inline-flex items-center gap-1">
                                    <button type="button" @click="openEdit({
                                        id: 3, name: 'Lab Hardware & Robotika 2', code: 'LAB-HW02', type: 'lab', location: 'Gedung Lab Barat Lt. 1', capacity: 30, openHours: '07:00 - 20:00 WIB', status: 'dalam perbaikan'
                                    })" class="px-space-sm py-1 rounded bg-surface-container text-primary font-label-sm font-semibold hover:bg-surface-container-highest transition-colors">
                                        Edit
                                    </button>
                                    <form action="{{ url('/admin/facilities/3/toggle') }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-space-md py-1 rounded bg-secondary text-on-secondary font-label-sm font-semibold hover:bg-secondary/90 transition-colors">
                                            Aktifkan
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Modal Tambah / Edit Fasilitas --}}
        <div x-show="showFacilityModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-space-md bg-black/50 backdrop-blur-xs">
            <div @click.away="showFacilityModal = false" class="bg-surface-container-lowest rounded-2xl shadow-xl max-w-lg w-full p-space-xl border border-outline-variant flex flex-col gap-space-md">
                <div class="flex items-center justify-between pb-space-sm border-b border-outline-variant">
                    <h3 class="font-headline-sm text-headline-sm text-primary flex items-center gap-2" x-text="modalTitle"></h3>
                    <button type="button" @click="showFacilityModal = false"><span class="material-symbols-outlined">close</span></button>
                </div>

                <!-- 
                  ROUTE: POST /admin/facilities/save
                  FUNGSI: Menyimpan entitas data fasilitas baru atau memperbarui data fasilitas yang sudah ada (UR16)
                -->
                <form action="{{ url('/admin/facilities/save') }}" method="POST" class="flex flex-col gap-space-md">
                    @csrf
                    <input type="hidden" name="id" :value="facility.id">

                    <div class="grid grid-cols-2 gap-space-md">
                        <div class="flex flex-col gap-1">
                            <label class="font-label-sm text-label-sm font-semibold" for="fac-name">Nama Ruangan</label>
                            <input type="text" id="fac-name" name="name" x-model="facility.name" required placeholder="Auditorium..." class="w-full h-10 px-space-md bg-surface-container-low rounded-lg text-body-sm border border-outline-variant/60 focus:border-primary focus:outline-none">
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="font-label-sm text-label-sm font-semibold" for="fac-code">Kode Ruang</label>
                            <input type="text" id="fac-code" name="code" x-model="facility.code" required placeholder="AUD-01" class="w-full h-10 px-space-md bg-surface-container-low rounded-lg text-body-sm border border-outline-variant/60 focus:border-primary focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-space-md">
                        <div class="flex flex-col gap-1">
                            <label class="font-label-sm text-label-sm font-semibold" for="fac-type">Tipe Fasilitas</label>
                            <select id="fac-type" name="type" x-model="facility.type" class="w-full h-10 px-space-md bg-surface-container-low rounded-lg text-body-sm border border-outline-variant/60 focus:border-primary focus:outline-none">
                                <option value="auditorium">Auditorium & Hall</option>
                                <option value="kelas">Ruang Kelas</option>
                                <option value="lab">Laboratorium</option>
                                <option value="olahraga">Lapangan Olahraga</option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="font-label-sm text-label-sm font-semibold" for="fac-cap">Kapasitas (Orang)</label>
                            <input type="number" id="fac-cap" name="capacity" x-model="facility.capacity" required class="w-full h-10 px-space-md bg-surface-container-low rounded-lg text-body-sm border border-outline-variant/60 focus:border-primary focus:outline-none">
                        </div>
                    </div>

                    <div class="flex flex-col gap-1">
                        <label class="font-label-sm text-label-sm font-semibold" for="fac-loc">Lokasi Gedung & Lantai</label>
                        <input type="text" id="fac-loc" name="location" x-model="facility.location" required placeholder="Gedung Rektorat Lt. 2..." class="w-full h-10 px-space-md bg-surface-container-low rounded-lg text-body-sm border border-outline-variant/60 focus:border-primary focus:outline-none">
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-outline-variant/40">
                        <button type="button" @click="showFacilityModal = false" class="px-space-md py-1.5 rounded-lg bg-surface-container text-on-surface font-label-md">Batal</button>
                        <button type="submit" class="px-space-md py-1.5 rounded-lg bg-primary text-on-primary font-label-md font-semibold hover:bg-primary-container transition-colors shadow-sm">Simpan Data Fasilitas</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
