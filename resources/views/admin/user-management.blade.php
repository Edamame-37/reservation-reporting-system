{{-- 
  NAMA FILE      : user-management.blade.php
  FUNGSIONALITAS : Halaman Manajemen Pengguna, Otorisasi Akun Sivitas & Pembuatan Akun Petugas (Super Admin)
  DESKRIPSI      : Menampilkan antrean verifikasi akun registrasi mandiri mahasiswa/dosen (UR15), daftar seluruh sivitas terdaftar, direktori petugas sarpras, serta modul pendaftaran akun petugas langsung (UR13) dan akun sivitas langsung (UR14).
  CARA KERJA     : Menggunakan layout <x-admin-layout active="user-management"> dengan Alpine.js state untuk tab switching (verifikasi, sivitas, petugas), search filter, dan pop-up dialog modal pendaftaran.
--}}

<x-admin-layout title="Manajemen Pengguna & Otorisasi Akun" active="user-management">
    <div x-data="{
        currentTab: 'verification',
        searchQuery: '',
        roleFilter: 'all',
        showAddPetugasModal: false,
        showAddPenggunaModal: false,
        showRejectModal: false,
        selectedUser: { id: null, name: '', nim: '', role: '' },
        openRejectModal(id, name, nim, role) {
            this.selectedUser = { id, name, nim, role };
            this.showRejectModal = true;
        }
    }" class="space-y-6">

        {{-- Page Header & Top Stats Summary --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <nav class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                    <a href="{{ url('/admin/dashboard') }}" class="hover:text-navy transition-colors">Admin Console</a>
                    <span>/</span>
                    <span class="text-slate-800 font-medium">Manajemen Pengguna</span>
                </nav>
                <h1 class="text-2xl font-bold text-navy tracking-tight">Otorisasi & Manajemen Sivitas Kampus</h1>
                <p class="text-sm text-slate-500 mt-0.5">Kelola verifikasi registrasi mandiri (UR15), hak akses sivitas, dan pembagian zona tugas petugas sarpras (UR13).</p>
            </div>

            <div class="flex items-center gap-2.5">
                <button type="button" @click="showAddPetugasModal = true" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-emerald-600 text-white text-xs font-semibold hover:bg-emerald-700 shadow-sm transition-all">
                    <span class="material-symbols-outlined text-[16px]">badge</span>
                    <span>+ Akun Petugas (UR13)</span>
                </button>
                <button type="button" @click="showAddPenggunaModal = true" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-navy text-white text-xs font-semibold hover:bg-navy-light shadow-sm transition-all">
                    <span class="material-symbols-outlined text-[16px]">person_add</span>
                    <span>+ Akun Pengguna (UR14)</span>
                </button>
            </div>
        </div>

        {{-- Stat Summary Pills --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <button @click="currentTab = 'verification'" class="p-4 rounded-2xl border text-left transition-all" :class="currentTab === 'verification' ? 'bg-amber-50/70 border-amber-300 ring-2 ring-amber-400/20' : 'bg-white border-slate-200/80 hover:bg-slate-50'">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-amber-800">Antrean Verifikasi (UR15)</span>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-200 text-amber-900">Perlu Tindakan</span>
                </div>
                <div class="text-2xl font-bold text-slate-800">5 <span class="text-xs font-normal text-slate-500">pemohon pending</span></div>
                <p class="text-xs text-amber-700 mt-1">Registrasi mandiri mahasiswa & dosen menunggu validasi KTM/SK</p>
            </button>

            <button @click="currentTab = 'sivitas'" class="p-4 rounded-2xl border text-left transition-all" :class="currentTab === 'sivitas' ? 'bg-blue-50/70 border-blue-300 ring-2 ring-blue-400/20' : 'bg-white border-slate-200/80 hover:bg-slate-50'">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-blue-800">Total Sivitas Terdaftar</span>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-medium bg-blue-100 text-blue-800">Aktif</span>
                </div>
                <div class="text-2xl font-bold text-slate-800">42 <span class="text-xs font-normal text-slate-500">akun pengguna</span></div>
                <p class="text-xs text-slate-500 mt-1">34 Mahasiswa aktif, 8 Dosen tetap terotorisasi</p>
            </button>

            <button @click="currentTab = 'petugas'" class="p-4 rounded-2xl border text-left transition-all" :class="currentTab === 'petugas' ? 'bg-emerald-50/70 border-emerald-300 ring-2 ring-emerald-400/20' : 'bg-white border-slate-200/80 hover:bg-slate-50'">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-emerald-800">Petugas Sarpras (UR13)</span>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-100 text-emerald-800">3 Zona</span>
                </div>
                <div class="text-2xl font-bold text-slate-800">8 <span class="text-xs font-normal text-slate-500">petugas zona</span></div>
                <p class="text-xs text-slate-500 mt-1">Didaftarkan otoritas Super Admin untuk verifikasi venue</p>
            </button>
        </div>

        {{-- Main Content Container with Tabs & Search --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            {{-- Tabs Navigation & Filter Bar --}}
            <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                {{-- Segmented Tabs --}}
                <div class="inline-flex p-1 rounded-xl bg-slate-100 text-xs font-semibold">
                    <button @click="currentTab = 'verification'" class="px-3.5 py-1.5 rounded-lg transition-all flex items-center gap-1.5" :class="currentTab === 'verification' ? 'bg-white text-navy shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                        <span class="material-symbols-outlined text-[16px]">how_to_reg</span>
                        <span>Verifikasi Akun (5)</span>
                    </button>
                    <button @click="currentTab = 'sivitas'" class="px-3.5 py-1.5 rounded-lg transition-all flex items-center gap-1.5" :class="currentTab === 'sivitas' ? 'bg-white text-navy shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                        <span class="material-symbols-outlined text-[16px]">group</span>
                        <span>Sivitas Terdaftar (42)</span>
                    </button>
                    <button @click="currentTab = 'petugas'" class="px-3.5 py-1.5 rounded-lg transition-all flex items-center gap-1.5" :class="currentTab === 'petugas' ? 'bg-white text-navy shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                        <span class="material-symbols-outlined text-[16px]">badge</span>
                        <span>Petugas Sarpras (8)</span>
                    </button>
                </div>

                {{-- Search & Role Filters --}}
                <div class="flex items-center gap-2.5">
                    <div class="relative w-full sm:w-64">
                        <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-[18px]">search</span>
                        <input type="text" x-model="searchQuery" placeholder="Cari nama, NIM, email..." class="w-full h-9 pl-9 pr-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-navy focus:outline-none transition-colors">
                    </div>
                </div>
            </div>

            {{-- TAB 1: Antrean Verifikasi (UR15) --}}
            <div x-show="currentTab === 'verification'" class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-100">
                            <th class="py-3 px-5">Nama & Institusi</th>
                            <th class="py-3 px-4">Kategori Sivitas</th>
                            <th class="py-3 px-4">NIM / NIP</th>
                            <th class="py-3 px-4">Email Resmi</th>
                            <th class="py-3 px-4">Waktu Pengajuan</th>
                            <th class="py-3 px-4">Dokumen Bukti</th>
                            <th class="py-3 px-5 text-right">Otorisasi (UR15)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        {{-- Row 1: Mahasiswa --}}
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-5">
                                <div class="font-semibold text-slate-800 text-sm">Anindya Putri Kirana</div>
                                <div class="text-[11px] text-slate-500">Fakultas Ilmu Komputer & TI</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">Mahasiswa S1</span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-700 font-medium">2108561044</td>
                            <td class="py-3.5 px-4 font-mono text-slate-600">anindya.pk@ti.univ.ac.id</td>
                            <td class="py-3.5 px-4 text-slate-500">24 Apr 2024, 09:15</td>
                            <td class="py-3.5 px-4">
                                <a href="#" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-navy text-[11px] font-medium transition-colors">
                                    <span class="material-symbols-outlined text-[14px]">picture_as_pdf</span>
                                    <span>KTM_2108561044.pdf</span>
                                </a>
                            </td>
                            <td class="py-3.5 px-5 text-right">
                                <div class="inline-flex items-center gap-1.5 justify-end">
                                    {{-- ROUTE: POST /admin/users/{id}/approve (UR15) --}}
                                    <form action="{{ url('/admin/users/1/approve') }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-600 text-white font-medium hover:bg-emerald-700 shadow-xs transition-colors">
                                            <span class="material-symbols-outlined text-[14px]">check</span>
                                            <span>Setujui</span>
                                        </button>
                                    </form>

                                    <button type="button" @click="openRejectModal(1, 'Anindya Putri Kirana', '2108561044', 'Mahasiswa S1')" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-white border border-rose-200 text-rose-600 font-medium hover:bg-rose-50 transition-colors">
                                        <span class="material-symbols-outlined text-[14px]">close</span>
                                        <span>Tolak</span>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        {{-- Row 2: Dosen --}}
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-5">
                                <div class="font-semibold text-slate-800 text-sm">Dr. Bambang Sudrajat, M.T.</div>
                                <div class="text-[11px] text-slate-500">Fakultas Teknik Elektro</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Dosen Tetap</span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-700 font-medium">197502142003121001</td>
                            <td class="py-3.5 px-4 font-mono text-slate-600">bambang.sudrajat@ee.univ.ac.id</td>
                            <td class="py-3.5 px-4 text-slate-500">24 Apr 2024, 08:30</td>
                            <td class="py-3.5 px-4">
                                <a href="#" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-navy text-[11px] font-medium transition-colors">
                                    <span class="material-symbols-outlined text-[14px]">picture_as_pdf</span>
                                    <span>SK_Dosen_Tetap.pdf</span>
                                </a>
                            </td>
                            <td class="py-3.5 px-5 text-right">
                                <div class="inline-flex items-center gap-1.5 justify-end">
                                    {{-- ROUTE: POST /admin/users/{id}/approve (UR15) --}}
                                    <form action="{{ url('/admin/users/2/approve') }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-600 text-white font-medium hover:bg-emerald-700 shadow-xs transition-colors">
                                            <span class="material-symbols-outlined text-[14px]">check</span>
                                            <span>Setujui</span>
                                        </button>
                                    </form>

                                    <button type="button" @click="openRejectModal(2, 'Dr. Bambang Sudrajat, M.T.', '197502142003121001', 'Dosen Tetap')" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-white border border-rose-200 text-rose-600 font-medium hover:bg-rose-50 transition-colors">
                                        <span class="material-symbols-outlined text-[14px]">close</span>
                                        <span>Tolak</span>
                                    </button>
                                </div>
                            </td>
                        </tr>

                        {{-- Row 3: Mahasiswa Organisasi --}}
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-5">
                                <div class="font-semibold text-slate-800 text-sm">Fikri Haikal Rahman</div>
                                <div class="text-[11px] text-slate-500">BEM Fakultas Kedokteran</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">Mahasiswa / BEM</span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-700 font-medium">2202111009</td>
                            <td class="py-3.5 px-4 font-mono text-slate-600">fikri.rahman@fk.univ.ac.id</td>
                            <td class="py-3.5 px-4 text-slate-500">23 Apr 2024, 16:45</td>
                            <td class="py-3.5 px-4">
                                <a href="#" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-navy text-[11px] font-medium transition-colors">
                                    <span class="material-symbols-outlined text-[14px]">picture_as_pdf</span>
                                    <span>KTM_2202111009.pdf</span>
                                </a>
                            </td>
                            <td class="py-3.5 px-5 text-right">
                                <div class="inline-flex items-center gap-1.5 justify-end">
                                    <form action="{{ url('/admin/users/3/approve') }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-600 text-white font-medium hover:bg-emerald-700 shadow-xs transition-colors">
                                            <span class="material-symbols-outlined text-[14px]">check</span>
                                            <span>Setujui</span>
                                        </button>
                                    </form>

                                    <button type="button" @click="openRejectModal(3, 'Fikri Haikal Rahman', '2202111009', 'Mahasiswa / BEM')" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-white border border-rose-200 text-rose-600 font-medium hover:bg-rose-50 transition-colors">
                                        <span class="material-symbols-outlined text-[14px]">close</span>
                                        <span>Tolak</span>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- TAB 2: Sivitas Terdaftar (42 Data) --}}
            <div x-show="currentTab === 'sivitas'" class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-100">
                            <th class="py-3 px-5">Nama Lengkap</th>
                            <th class="py-3 px-4">Role Sivitas</th>
                            <th class="py-3 px-4">NIM / NIP</th>
                            <th class="py-3 px-4">Email Kampus</th>
                            <th class="py-3 px-4">Reservasi Dibuat</th>
                            <th class="py-3 px-4">Status Akun</th>
                            <th class="py-3 px-5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-5">
                                <div class="font-semibold text-slate-800 text-sm">Reyhan Maulana</div>
                                <div class="text-[11px] text-slate-500">Teknologi Informasi - Angkatan 2021</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">Mahasiswa</span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-700">2108561021</td>
                            <td class="py-3.5 px-4 font-mono text-slate-600">reyhan.m@student.univ.ac.id</td>
                            <td class="py-3.5 px-4 text-slate-700 font-medium">12 kali</td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                </span>
                            </td>
                            <td class="py-3.5 px-5 text-right">
                                <button class="px-2.5 py-1 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 font-medium transition-colors">Detail Akun</button>
                            </td>
                        </tr>

                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-5">
                                <div class="font-semibold text-slate-800 text-sm">Prof. Dr. Ir. I Made Wardana, M.Sc.</div>
                                <div class="text-[11px] text-slate-500">Guru Besar Fakultas Teknik</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Dosen Tetap</span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-700">196503121990031002</td>
                            <td class="py-3.5 px-4 font-mono text-slate-600">made.wardana@eng.univ.ac.id</td>
                            <td class="py-3.5 px-4 text-slate-700 font-medium">24 kali</td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                </span>
                            </td>
                            <td class="py-3.5 px-5 text-right">
                                <button class="px-2.5 py-1 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 font-medium transition-colors">Detail Akun</button>
                            </td>
                        </tr>

                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-5">
                                <div class="font-semibold text-slate-800 text-sm">Dewi Kusuma Putri</div>
                                <div class="text-[11px] text-slate-500">Himpunan Mahasiswa Arsitektur</div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">Mahasiswa</span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-700">2208561089</td>
                            <td class="py-3.5 px-4 font-mono text-slate-600">dewi.kusuma@student.univ.ac.id</td>
                            <td class="py-3.5 px-4 text-slate-700 font-medium">7 kali</td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                </span>
                            </td>
                            <td class="py-3.5 px-5 text-right">
                                <button class="px-2.5 py-1 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 font-medium transition-colors">Detail Akun</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- TAB 3: Petugas Sarpras (UR13) (8 Data) --}}
            <div x-show="currentTab === 'petugas'" class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-100">
                            <th class="py-3 px-5">Nama Petugas</th>
                            <th class="py-3 px-4">NIP</th>
                            <th class="py-3 px-4">Zona Penugasan</th>
                            <th class="py-3 px-4">Email Resmi</th>
                            <th class="py-3 px-4">Beban Tiket Kerusakan</th>
                            <th class="py-3 px-4">Status Akun</th>
                            <th class="py-3 px-5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-5">
                                <div class="font-semibold text-slate-800 text-sm">Agus Salim, A.Md.</div>
                                <div class="text-[11px] text-slate-500">Unit Pelaksana Teknis Sarpras</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-700">198205102008011003</td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">Zona Gedung B</span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-600">agus.sarpras@univ.ac.id</td>
                            <td class="py-3.5 px-4 text-slate-700 font-medium">3 aktif / 19 tuntas</td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Bertugas
                                </span>
                            </td>
                            <td class="py-3.5 px-5 text-right">
                                <button class="px-2.5 py-1 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 font-medium transition-colors">Edit Zona</button>
                            </td>
                        </tr>

                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-5">
                                <div class="font-semibold text-slate-800 text-sm">Hendra Pratama</div>
                                <div class="text-[11px] text-slate-500">Teknisi Kelistrikan & Audio</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-700">198811202014021001</td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-purple-50 text-purple-700 border border-purple-200">Zona Gedung A (Auditorium)</span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-600">hendra.sarpras@univ.ac.id</td>
                            <td class="py-3.5 px-4 text-slate-700 font-medium">1 aktif / 28 tuntas</td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Bertugas
                                </span>
                            </td>
                            <td class="py-3.5 px-5 text-right">
                                <button class="px-2.5 py-1 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 font-medium transition-colors">Edit Zona</button>
                            </td>
                        </tr>

                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3.5 px-5">
                                <div class="font-semibold text-slate-800 text-sm">Siti Rahmawati, S.T.</div>
                                <div class="text-[11px] text-slate-500">Koordinator Laboratorium Terpadu</div>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-700">199004152018012002</td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">Zona Gedung C (Lab Komputer)</span>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-slate-600">siti.lab@univ.ac.id</td>
                            <td class="py-3.5 px-4 text-slate-700 font-medium">2 aktif / 14 tuntas</td>
                            <td class="py-3.5 px-4">
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Bertugas
                                </span>
                            </td>
                            <td class="py-3.5 px-5 text-right">
                                <button class="px-2.5 py-1 rounded-lg border border-slate-200 text-slate-600 hover:bg-slate-100 font-medium transition-colors">Edit Zona</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            {{-- Footer Pagination / Info --}}
            <div class="p-4 bg-slate-50/50 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between text-xs text-slate-500 gap-2">
                <div>Menampilkan data berdasarkan tab aktif</div>
                <div class="flex items-center gap-1">
                    <button class="px-2 py-1 rounded border border-slate-200 bg-white text-slate-400 cursor-not-allowed">Sebelumnya</button>
                    <button class="px-2.5 py-1 rounded bg-navy text-white font-semibold">1</button>
                    <button class="px-2.5 py-1 rounded border border-slate-200 bg-white text-slate-600 hover:bg-slate-100">2</button>
                    <button class="px-2 py-1 rounded border border-slate-200 bg-white text-slate-600 hover:bg-slate-100">Berikutnya</button>
                </div>
            </div>
        </div>

        {{-- MODAL 1: Daftarkan Akun Petugas Sarpras (UR13) --}}
        <div x-show="showAddPetugasModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="showAddPetugasModal = false" class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-6 border border-slate-200 flex flex-col gap-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2 text-navy">
                        <span class="material-symbols-outlined text-[22px]">badge</span>
                        <h3 class="font-bold text-base">Daftarkan Petugas Sarpras Baru (UR13)</h3>
                    </div>
                    <button type="button" @click="showAddPetugasModal = false" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                {{-- ROUTE: POST /admin/users/create-petugas (UR13) --}}
                <form action="{{ url('/admin/users/create-petugas') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-800">
                        <div class="font-semibold mb-0.5">Ketentuan Sistem UR13:</div>
                        Petugas sarpras mutlak didaftarkan langsung oleh Super Admin dan tidak membuka pendaftaran mandiri publik demi integritas validasi gedung.
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1" for="pet-name">Nama Lengkap Petugas</label>
                        <input type="text" id="pet-name" name="name" required placeholder="Contoh: Bambang Setyawan, A.Md." class="w-full h-9 px-3 bg-slate-50 rounded-xl text-xs border border-slate-200 focus:bg-white focus:border-navy focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1" for="pet-nip">NIP Petugas</label>
                            <input type="text" id="pet-nip" name="nip" required placeholder="198402122010011002" class="w-full h-9 px-3 bg-slate-50 rounded-xl text-xs border border-slate-200 focus:bg-white focus:border-navy focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1" for="pet-zone">Zona Penugasan</label>
                            <select id="pet-zone" name="zone" class="w-full h-9 px-3 bg-slate-50 rounded-xl text-xs border border-slate-200 focus:bg-white focus:border-navy focus:outline-none">
                                <option value="A">Zona Gedung A (Auditorium & Hall)</option>
                                <option value="B">Zona Gedung B (Kuliah Terpadu)</option>
                                <option value="C">Zona Gedung C (Lab Komputer)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1" for="pet-email">Email Resmi Sarpras</label>
                        <input type="email" id="pet-email" name="email" required placeholder="bambang.sarpras@univ.ac.id" class="w-full h-9 px-3 bg-slate-50 rounded-xl text-xs border border-slate-200 focus:bg-white focus:border-navy focus:outline-none">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="showAddPetugasModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-navy text-white text-xs font-semibold hover:bg-navy-light shadow-sm transition-colors">Simpan Akun Petugas</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL 2: Daftarkan Akun Pengguna Langsung (UR14) --}}
        <div x-show="showAddPenggunaModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="showAddPenggunaModal = false" class="bg-white rounded-2xl shadow-xl max-w-lg w-full p-6 border border-slate-200 flex flex-col gap-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2 text-navy">
                        <span class="material-symbols-outlined text-[22px]">person_add</span>
                        <h3 class="font-bold text-base">Daftarkan Akun Pengguna Langsung (UR14)</h3>
                    </div>
                    <button type="button" @click="showAddPenggunaModal = false" class="text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                {{-- ROUTE: POST /admin/users/create-user (UR14) --}}
                <form action="{{ url('/admin/users/create-user') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="p-3 bg-blue-50 rounded-xl border border-blue-200 text-xs text-blue-800">
                        <div class="font-semibold mb-0.5">Bypass Verifikasi (UR14):</div>
                        Akun yang didaftarkan langsung oleh Super Admin akan otomatis berstatus <strong>Terverifikasi & Aktif</strong> tanpa perlu proses validasi berkas identitas.
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1" for="usr-name">Nama Lengkap Sivitas</label>
                        <input type="text" id="usr-name" name="name" required placeholder="Contoh: Dimas Pratama" class="w-full h-9 px-3 bg-slate-50 rounded-xl text-xs border border-slate-200 focus:bg-white focus:border-navy focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1" for="usr-role">Peran Sivitas</label>
                            <select id="usr-role" name="role_type" class="w-full h-9 px-3 bg-slate-50 rounded-xl text-xs border border-slate-200 focus:bg-white focus:border-navy focus:outline-none">
                                <option value="mahasiswa">Mahasiswa</option>
                                <option value="dosen">Dosen Tetap</option>
                                <option value="staf">Staf Akademik / BEM</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1" for="usr-id">NIM / NIP</label>
                            <input type="text" id="usr-id" name="identifier" required placeholder="2110512044" class="w-full h-9 px-3 bg-slate-50 rounded-xl text-xs border border-slate-200 focus:bg-white focus:border-navy focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1" for="usr-email">Email Kampus (@univ.ac.id)</label>
                        <input type="email" id="usr-email" name="email" required placeholder="pengguna@univ.ac.id" class="w-full h-9 px-3 bg-slate-50 rounded-xl text-xs border border-slate-200 focus:bg-white focus:border-navy focus:outline-none">
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="showAddPenggunaModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-navy text-white text-xs font-semibold hover:bg-navy-light shadow-sm transition-colors">Simpan Akun Langsung</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL 3: Konfirmasi Penolakan Verifikasi (UR15) --}}
        <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="showRejectModal = false" class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 border border-slate-200 flex flex-col gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[22px]">warning</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm">Tolak Verifikasi Pendaftaran</h3>
                        <p class="text-xs text-slate-500">Akun pendaftar akan ditolak dan tidak dapat login</p>
                    </div>
                </div>

                {{-- ROUTE: POST /admin/users/{id}/reject --}}
                <form :action="'{{ url('/admin/users') }}/' + selectedUser.id + '/reject'" method="POST" class="space-y-3">
                    @csrf
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs">
                        <div class="font-semibold text-slate-800" x-text="selectedUser.name"></div>
                        <div class="text-slate-500 mt-0.5"><span x-text="selectedUser.role"></span> • NIM/NIP: <span class="font-mono" x-text="selectedUser.nim"></span></div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1" for="reject-reason">Alasan Penolakan</label>
                        <select id="reject-reason" name="reason" class="w-full h-9 px-3 bg-slate-50 rounded-xl text-xs border border-slate-200 focus:bg-white focus:border-navy focus:outline-none mb-2">
                            <option value="invalid_ktm">Foto KTM / SK buram atau tidak terbaca</option>
                            <option value="mismatched_data">Data NIM/NIP tidak cocok dengan pangkalan data PD-DIKTI</option>
                            <option value="invalid_email">Bukan domain email resmi universitas</option>
                            <option value="other">Alasan lainnya</option>
                        </select>
                        <textarea name="notes" rows="2" placeholder="Catatan tambahan untuk pemohon (opsional)..." class="w-full p-2.5 bg-slate-50 rounded-xl text-xs border border-slate-200 focus:bg-white focus:border-navy focus:outline-none resize-none"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                        <button type="button" @click="showRejectModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 text-white text-xs font-semibold hover:bg-rose-700 shadow-sm transition-colors">Tolak Pendaftaran</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-admin-layout>
