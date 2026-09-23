{{-- 
  NAMA FILE      : user-management.blade.php
  FUNGSIONALITAS : Halaman Manajemen Pengguna, Otorisasi Akun Sivitas & Pembuatan Akun Petugas (Super Admin)
  DESKRIPSI      : Menampilkan antrean verifikasi akun registrasi mandiri mahasiswa/dosen (UR15 / ADM-01), daftar seluruh sivitas terdaftar, direktori petugas sarpras, serta modal penolakan pendaftaran.
  CARA KERJA     : Menggunakan layout <x-admin-layout active="user-management"> dengan Alpine.js state untuk tab switching (verifikasi, sivitas, petugas), filter pencarian data dinamis, dan dialog modal penolakan.
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
                <p class="text-sm text-slate-500 mt-0.5">Kelola verifikasi registrasi mandiri (UR15 / ADM-01), hak akses sivitas, dan pembagian zona tugas petugas sarpras (UR13).</p>
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

        {{-- Session Flash Notifications --}}
        @if (session('success'))
            <!-- 
              ELEMEN   : Alert Notifikasi Sukses
              KEGUNAAN : Memberikan umpan balik visual saat verifikasi atau penolakan akun berhasil diproses.
            -->
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[20px] text-emerald-600">check_circle</span>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if ($errors->any())
            <!-- 
              ELEMEN   : Alert Notifikasi Kesalahan Validasi
              KEGUNAAN : Memberikan informasi penolakan sistem jika aturan bisnis dilanggar.
            -->
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800 flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[20px] text-rose-600">error</span>
                    <div>
                        @foreach ($errors->all() as $error)
                            <p class="font-medium">{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- Stat Summary Pills --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <button @click="currentTab = 'verification'" class="p-4 rounded-2xl border text-left transition-all" :class="currentTab === 'verification' ? 'bg-amber-50/70 border-amber-300 ring-2 ring-amber-400/20' : 'bg-white border-slate-200/80 hover:bg-slate-50'">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-amber-800">Antrean Verifikasi (UR15)</span>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-bold bg-amber-200 text-amber-900">Perlu Tindakan</span>
                </div>
                <div class="text-2xl font-bold text-slate-800">{{ $pendingCount }} <span class="text-xs font-normal text-slate-500">pemohon pending</span></div>
                <p class="text-xs text-amber-700 mt-1">Registrasi mandiri mahasiswa & dosen menunggu validasi berkas identitas</p>
            </button>

            <button @click="currentTab = 'sivitas'" class="p-4 rounded-2xl border text-left transition-all" :class="currentTab === 'sivitas' ? 'bg-blue-50/70 border-blue-300 ring-2 ring-blue-400/20' : 'bg-white border-slate-200/80 hover:bg-slate-50'">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-blue-800">Total Sivitas Terdaftar</span>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-medium bg-blue-100 text-blue-800">Aktif</span>
                </div>
                <div class="text-2xl font-bold text-slate-800">{{ $sivitasCount }} <span class="text-xs font-normal text-slate-500">akun pengguna</span></div>
                <p class="text-xs text-slate-500 mt-1">Akun mahasiswa, dosen, dan staf aktif yang terotorisasi di sistem</p>
            </button>

            <button @click="currentTab = 'petugas'" class="p-4 rounded-2xl border text-left transition-all" :class="currentTab === 'petugas' ? 'bg-emerald-50/70 border-emerald-300 ring-2 ring-emerald-400/20' : 'bg-white border-slate-200/80 hover:bg-slate-50'">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-semibold uppercase tracking-wider text-emerald-800">Petugas Sarpras (UR13)</span>
                    <span class="px-2 py-0.5 rounded-full text-[11px] font-medium bg-emerald-100 text-emerald-800">Zona Aktif</span>
                </div>
                <div class="text-2xl font-bold text-slate-800">{{ $petugasCount }} <span class="text-xs font-normal text-slate-500">petugas zona</span></div>
                <p class="text-xs text-slate-500 mt-1">Didaftarkan otoritas Super Admin untuk verifikasi venue dan laporan</p>
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
                        <span>Verifikasi Akun ({{ $pendingCount }})</span>
                    </button>
                    <button @click="currentTab = 'sivitas'" class="px-3.5 py-1.5 rounded-lg transition-all flex items-center gap-1.5" :class="currentTab === 'sivitas' ? 'bg-white text-navy shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                        <span class="material-symbols-outlined text-[16px]">group</span>
                        <span>Sivitas Terdaftar ({{ $sivitasCount }})</span>
                    </button>
                    <button @click="currentTab = 'petugas'" class="px-3.5 py-1.5 rounded-lg transition-all flex items-center gap-1.5" :class="currentTab === 'petugas' ? 'bg-white text-navy shadow-xs' : 'text-slate-600 hover:text-slate-900'">
                        <span class="material-symbols-outlined text-[16px]">badge</span>
                        <span>Petugas Sarpras ({{ $petugasCount }})</span>
                    </button>
                </div>

                {{-- Search Filters --}}
                <div class="flex items-center gap-2.5">
                    <div class="relative w-full sm:w-64">
                        <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-[18px]">search</span>
                        <input type="text" x-model="searchQuery" placeholder="Cari nama, NIM, email..." class="w-full h-9 pl-9 pr-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-navy focus:outline-none transition-colors">
                    </div>
                </div>
            </div>

            {{-- TAB 1: Antrean Verifikasi (UR15 / ADM-01) --}}
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
                        @forelse($pendingUsers as $user)
                            <tr class="hover:bg-slate-50/60 transition-colors"
                                x-show="!searchQuery || '{{ strtolower($user->name . ' ' . $user->identity_number . ' ' . $user->email . ' ' . $user->department) }}'.includes(searchQuery.toLowerCase())">
                                <td class="py-3.5 px-5">
                                    <div class="font-semibold text-slate-800 text-sm">{{ $user->name }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $user->department ?? 'Sivitas Kampus' }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                        {{ ucfirst($user->role) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-slate-700 font-medium">{{ $user->identity_number ?? '-' }}</td>
                                <td class="py-3.5 px-4 font-mono text-slate-600">{{ $user->email }}</td>
                                <td class="py-3.5 px-4 text-slate-500">{{ $user->created_at ? $user->created_at->format('d M Y, H:i') : '-' }}</td>
                                <td class="py-3.5 px-4">
                                    @if($user->id_card_path)
                                        <a href="{{ asset('storage/' . $user->id_card_path) }}" target="_blank" class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-navy text-[11px] font-medium transition-colors">
                                            <span class="material-symbols-outlined text-[14px]">picture_as_pdf</span>
                                            <span>Lihat Berkas</span>
                                        </a>
                                    @else
                                        <span class="text-slate-400 text-[11px] italic">Tidak ada berkas</span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-5 text-right">
                                    <div class="inline-flex items-center gap-1.5 justify-end">
                                        <!-- 
                                          ROUTE: Mengirimkan form verifikasi via POST ke /admin/users/{id}/verify (UR15 / ADM-01)
                                          FUNGSI: Mengesahkan pendaftaran akun dan mengubah status menjadi 'active'
                                        -->
                                        <form action="{{ route('admin.users.verify', $user->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-600 text-white font-medium hover:bg-emerald-700 shadow-xs transition-colors">
                                                <span class="material-symbols-outlined text-[14px]">check</span>
                                                <span>Setujui</span>
                                            </button>
                                        </form>

                                        <button type="button" @click="openRejectModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->identity_number ?? '-') }}', '{{ ucfirst($user->role) }}')" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-white border border-rose-200 text-rose-600 font-medium hover:bg-rose-50 transition-colors">
                                            <span class="material-symbols-outlined text-[14px]">close</span>
                                            <span>Tolak</span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 px-5 text-center text-slate-400 text-xs">
                                    <span class="material-symbols-outlined text-4xl block mb-2 text-slate-300">verified_user</span>
                                    Tidak ada antrean verifikasi akun pending saat ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- TAB 2: Sivitas Terdaftar --}}
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
                        @forelse($sivitasUsers as $sivitas)
                            <tr class="hover:bg-slate-50/60 transition-colors"
                                x-show="!searchQuery || '{{ strtolower($sivitas->name . ' ' . $sivitas->identity_number . ' ' . $sivitas->email . ' ' . $sivitas->department) }}'.includes(searchQuery.toLowerCase())">
                                <td class="py-3.5 px-5">
                                    <div class="font-semibold text-slate-800 text-sm">{{ $sivitas->name }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $sivitas->department ?? 'Sivitas Terdaftar' }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                        {{ ucfirst($sivitas->role) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-slate-700">{{ $sivitas->identity_number ?? '-' }}</td>
                                <td class="py-3.5 px-4 font-mono text-slate-600">{{ $sivitas->email }}</td>
                                <td class="py-3.5 px-4 text-slate-700 font-medium">{{ $sivitas->reservations->count() }} kali</td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif
                                    </span>
                                </td>
                                <td class="py-3.5 px-5 text-right">
                                    <span class="px-2.5 py-1 rounded-lg border border-slate-200 text-slate-600 text-xs font-medium">Terverifikasi</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 px-5 text-center text-slate-400 text-xs">
                                    Belum ada sivitas akademika terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- TAB 3: Petugas Sarpras (UR13) --}}
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
                        @forelse($petugasUsers as $petugas)
                            <tr class="hover:bg-slate-50/60 transition-colors"
                                x-show="!searchQuery || '{{ strtolower($petugas->name . ' ' . $petugas->identity_number . ' ' . $petugas->email . ' ' . $petugas->department . ' ' . $petugas->assignment_zone) }}'.includes(searchQuery.toLowerCase())">
                                <td class="py-3.5 px-5">
                                    <div class="font-semibold text-slate-800 text-sm">{{ $petugas->name }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $petugas->department ?? 'Unit Pelaksana Teknis Sarpras' }}</div>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-slate-700">{{ $petugas->identity_number ?? '-' }}</td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        {{ $petugas->assignment_zone ?? 'Zona Terpadu' }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-slate-600">{{ $petugas->email }}</td>
                                <td class="py-3.5 px-4 text-slate-700 font-medium">{{ $petugas->handledReports->count() }} tiket</td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Bertugas
                                    </span>
                                </td>
                                <td class="py-3.5 px-5 text-right">
                                    <span class="px-2.5 py-1 rounded-lg border border-slate-200 text-slate-600 text-xs font-medium">Aktif</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 px-5 text-center text-slate-400 text-xs">
                                    Belum ada data petugas sarpras terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Footer Pagination / Info --}}
            <div class="p-4 bg-slate-50/50 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between text-xs text-slate-500 gap-2">
                <div>Menampilkan data dinamis terverifikasi dari basis data</div>
            </div>
        </div>

        {{-- MODAL 1: Daftarkan Akun Petugas Sarpras (UR13) --}}
        <!-- 
          ELEMEN       : Modal Registrasi Petugas Sarpras Langsung (UR13)
          KEGUNAAN     : Menyediakan formulir pembuatan akun petugas oleh Super Admin.
        -->
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
                <form action="{{ route('admin.users.create-petugas') }}" method="POST" class="space-y-4">
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
                                <option value="Zona 1 (Rektorat & Auditorium)">Zona 1 (Rektorat & Auditorium)</option>
                                <option value="Zona 2 (Gedung Kuliah Terpadu)">Zona 2 (Gedung Kuliah Terpadu)</option>
                                <option value="Zona 3 (Laboratorium Terpadu & Jaringan)">Zona 3 (Laboratorium Terpadu & Jaringan)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1" for="pet-email">Email Resmi Sarpras</label>
                        <input type="email" id="pet-email" name="email" required placeholder="bambang.sarpras@univ.ac.id" class="w-full h-9 px-3 bg-slate-50 rounded-xl text-xs border border-slate-200 focus:bg-white focus:border-navy focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1" for="pet-password">Password Sementara (Opsional)</label>
                        <input type="password" id="pet-password" name="password" placeholder="Kosongkan untuk kata sandi default: 'password'" class="w-full h-9 px-3 bg-slate-50 rounded-xl text-xs border border-slate-200 focus:bg-white focus:border-navy focus:outline-none">
                        <span class="text-[10px] text-slate-400 mt-1 block">Minimal 8 karakter jika diisi. Default: password</span>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="showAddPetugasModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-navy text-white text-xs font-semibold hover:bg-navy-light shadow-sm transition-colors">Simpan Akun Petugas</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL 2: Daftarkan Akun Pengguna Langsung (UR14) --}}
        <!-- 
          ELEMEN       : Modal Registrasi Pengguna Langsung (UR14)
          KEGUNAAN     : Menyediakan formulir pembuatan akun mahasiswa/dosen langsung oleh Admin tanpa antrean pending.
        -->
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
                <form action="{{ route('admin.users.create-user') }}" method="POST" class="space-y-4">
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
                        <label class="block text-xs font-semibold text-slate-700 mb-1" for="usr-department">Fakultas / Program Studi (Opsional)</label>
                        <input type="text" id="usr-department" name="department" placeholder="Contoh: Fakultas Teknik / Informatika" class="w-full h-9 px-3 bg-slate-50 rounded-xl text-xs border border-slate-200 focus:bg-white focus:border-navy focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1" for="usr-email">Email Kampus (@univ.ac.id)</label>
                        <input type="email" id="usr-email" name="email" required placeholder="pengguna@univ.ac.id" class="w-full h-9 px-3 bg-slate-50 rounded-xl text-xs border border-slate-200 focus:bg-white focus:border-navy focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1" for="usr-password">Password Sementara (Opsional)</label>
                        <input type="password" id="usr-password" name="password" placeholder="Kosongkan untuk kata sandi default: 'password'" class="w-full h-9 px-3 bg-slate-50 rounded-xl text-xs border border-slate-200 focus:bg-white focus:border-navy focus:outline-none">
                        <span class="text-[10px] text-slate-400 mt-1 block">Minimal 8 karakter jika diisi. Default: password</span>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="showAddPenggunaModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-navy text-white text-xs font-semibold hover:bg-navy-light shadow-sm transition-colors">Simpan Akun Langsung</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL 3: Konfirmasi Penolakan Verifikasi (UR15 / ADM-01) --}}
        <!-- 
          ELEMEN       : Modal Konfirmasi Penolakan Verifikasi Akun (UR15 / ADM-01)
          KEGUNAAN     : Memberikan layar dialog konfirmasi penolakan pendaftaran beserta pemilihan alasan resmi.
          CARA KERJA   : Tersembunyi secara default (x-show="showRejectModal"). Ketika dibuka via openRejectModal(), mengisi data target dan mengirim form ke endpoint penolakan.
        -->
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

                <!-- 
                  ROUTE: Mengirimkan form penolakan via POST ke /admin/users/{id}/reject (UR15 / ADM-01)
                  FUNGSI: Membatalkan permohonan pendaftaran akun pengguna dan menyimpan alasan penolakan ke basis data.
                -->
                <form :action="'{{ url('/admin/users') }}/' + selectedUser.id + '/reject'" method="POST" class="space-y-3">
                    @csrf
                    <div class="p-3 bg-slate-50 rounded-xl border border-slate-200 text-xs">
                        <div class="font-semibold text-slate-800" x-text="selectedUser.name"></div>
                        <div class="text-slate-500 mt-0.5"><span x-text="selectedUser.role"></span> • NIM/NIP: <span class="font-mono" x-text="selectedUser.nim"></span></div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1" for="reject-reason">Alasan Penolakan</label>
                        <select id="reject-reason" name="reason" required class="w-full h-9 px-3 bg-slate-50 rounded-xl text-xs border border-slate-200 focus:bg-white focus:border-navy focus:outline-none mb-2">
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
