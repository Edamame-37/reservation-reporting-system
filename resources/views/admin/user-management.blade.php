{{-- 
  NAMA FILE      : user-management.blade.php
  FUNGSIONALITAS : Halaman Manajemen Pengguna, Otorisasi Akun Sivitas & Pembuatan Akun Petugas (Super Admin)
  DESKRIPSI      : Menampilkan antrean verifikasi akun registrasi mandiri (UR15), daftar sivitas terdaftar, direktori petugas sarpras, direktori akun nonaktif/dihapus, serta modal detail profil komprehensif.
  CARA KERJA     : Menggunakan layout <x-admin-layout active="user-management"> dengan Alpine.js state untuk 4 tab navigasi, filter pencarian dinamis, modal penolakan, dan modal profil + deaktivasi akun.
--}}

<x-admin-layout title="Manajemen Pengguna & Otorisasi Akun" active="user-management">
    <div x-data="{
        currentTab: 'sivitas',
        searchQuery: '',
        showDetailModal: false,
        showAddPetugasModal: false,
        showAddPenggunaModal: false,
        showRejectModal: false,
        selectedUser: { id: null, name: '', nim: '', role: '' },
        detailUser: {},
        openDetailModal(u) {
            this.detailUser = u;
            this.showDetailModal = true;
        },
        openRejectModal(id, name, nim, role) {
            this.selectedUser = { id, name, nim, role };
            this.showRejectModal = true;
        }
    }" class="space-y-6">

        {{-- Page Header & Action Buttons --}}
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <nav class="flex items-center gap-2 text-xs text-slate-500 mb-1">
                    <a href="{{ url('/admin/dashboard') }}" class="hover:text-navy transition-colors">Admin Console</a>
                    <span>/</span>
                    <span class="text-slate-800 font-medium">Manajemen Pengguna</span>
                </nav>
                <h1 class="text-2xl font-bold text-navy tracking-tight">Otorisasi & Manajemen Sivitas Kampus</h1>
                <p class="text-sm text-slate-500 mt-0.5">Kelola verifikasi registrasi mandiri, direktori sivitas, penugasan petugas, dan pembekuan akun.</p>
            </div>

            <div class="flex items-center gap-2.5">
                <button type="button" @click="showAddPetugasModal = true" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold border border-slate-200 shadow-2xs transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-[16px] text-emerald-600">badge</span>
                    <span>+ Akun Petugas (UR13)</span>
                </button>
                <button type="button" @click="showAddPenggunaModal = true" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 shadow-sm transition-all cursor-pointer">
                    <span class="material-symbols-outlined text-[16px]">person_add</span>
                    <span>+ Akun Pengguna (UR14)</span>
                </button>
            </div>
        </div>

        {{-- Session Flash Notifications --}}
        @if (session('success'))
            <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800 flex items-center justify-between shadow-2xs">
                <div class="flex items-center gap-2.5">
                    <span class="material-symbols-outlined text-[20px] text-emerald-600">check_circle</span>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if (isset($errors) && $errors->any())
            <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-xs text-rose-800 flex items-center justify-between shadow-2xs">
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

        {{-- Stat Summary Cards (Clean & Balanced, Poin 3) --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                <div class="text-xs font-medium text-slate-500 mb-1">Antrean Verifikasi</div>
                <div class="text-2xl font-bold text-amber-600">{{ $pendingCount }} <span class="text-xs font-normal text-slate-400">pemohon</span></div>
                <div class="text-[11px] text-amber-700 mt-1 font-medium flex items-center gap-1">
                    <span class="material-symbols-outlined text-[13px]">pending</span> Menunggu evaluasi
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                <div class="text-xs font-medium text-slate-500 mb-1">Sivitas Aktif</div>
                <div class="text-2xl font-bold text-slate-800">{{ $sivitasCount }} <span class="text-xs font-normal text-slate-400">pengguna</span></div>
                <div class="text-[11px] text-emerald-600 mt-1 font-medium flex items-center gap-1">
                    <span class="material-symbols-outlined text-[13px]">check_circle</span> Terotorisasi sistem
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                <div class="text-xs font-medium text-slate-500 mb-1">Petugas Sarpras</div>
                <div class="text-2xl font-bold text-slate-800">{{ $petugasCount }} <span class="text-xs font-normal text-slate-400">personel</span></div>
                <div class="text-[11px] text-slate-500 mt-1 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[13px]">badge</span> Bertugas aktif
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                <div class="text-xs font-medium text-slate-500 mb-1">Non-aktif / Dihapus</div>
                <div class="text-2xl font-bold text-rose-600">{{ $inactiveCount }} <span class="text-xs font-normal text-slate-400">akun</span></div>
                <div class="text-[11px] text-rose-700 mt-1 font-medium flex items-center gap-1">
                    <span class="material-symbols-outlined text-[13px]">block</span> Arsip pembekuan
                </div>
            </div>
        </div>

        {{-- Main Content Container with 4 Segmented Tabs & Search Bar --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-2xs overflow-hidden">
            {{-- Tabs Navigation & Filter Bar --}}
            <div class="p-4 sm:p-5 border-b border-slate-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                {{-- 4 Segmented Tabs (Termasuk Tab Non-aktif/Dihapus, Poin 7) --}}
                <div class="inline-flex p-1 rounded-xl bg-slate-100 text-xs font-semibold flex-wrap">
                    <button type="button" @click="currentTab = 'verification'" class="px-3.5 py-1.5 rounded-lg transition-all flex items-center gap-1.5 cursor-pointer" :class="currentTab === 'verification' ? 'bg-white text-navy shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900'">
                        <span class="material-symbols-outlined text-[16px]">how_to_reg</span>
                        <span>Verifikasi Akun ({{ $pendingCount }})</span>
                    </button>
                    <button type="button" @click="currentTab = 'sivitas'" class="px-3.5 py-1.5 rounded-lg transition-all flex items-center gap-1.5 cursor-pointer" :class="currentTab === 'sivitas' ? 'bg-white text-navy shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900'">
                        <span class="material-symbols-outlined text-[16px]">group</span>
                        <span>Sivitas Terdaftar ({{ $sivitasCount }})</span>
                    </button>
                    <button type="button" @click="currentTab = 'petugas'" class="px-3.5 py-1.5 rounded-lg transition-all flex items-center gap-1.5 cursor-pointer" :class="currentTab === 'petugas' ? 'bg-white text-navy shadow-2xs font-bold' : 'text-slate-600 hover:text-slate-900'">
                        <span class="material-symbols-outlined text-[16px]">badge</span>
                        <span>Petugas Sarpras ({{ $petugasCount }})</span>
                    </button>
                    <button type="button" @click="currentTab = 'inactive'" class="px-3.5 py-1.5 rounded-lg transition-all flex items-center gap-1.5 cursor-pointer text-rose-700" :class="currentTab === 'inactive' ? 'bg-white shadow-2xs font-bold' : 'text-slate-600 hover:text-rose-700'">
                        <span class="material-symbols-outlined text-[16px]">person_off</span>
                        <span>Non-aktif / Dihapus ({{ $inactiveCount }})</span>
                    </button>
                </div>

                {{-- Search Filter --}}
                <div class="flex items-center gap-2.5">
                    <div class="relative w-full sm:w-64">
                        <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-[18px]">search</span>
                        <input type="text" x-model="searchQuery" placeholder="Cari nama, NIM/NIP, email..." class="w-full h-9 pl-9 pr-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-800 placeholder:text-slate-400 focus:bg-white focus:border-slate-900 focus:outline-none transition-colors">
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
                                        <form action="{{ route('admin.users.verify', $user->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-600 text-white font-medium hover:bg-emerald-700 shadow-2xs transition-colors cursor-pointer">
                                                <span class="material-symbols-outlined text-[14px]">check</span>
                                                <span>Setujui</span>
                                            </button>
                                        </form>

                                        <button type="button" @click="openRejectModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->identity_number ?? '-') }}', '{{ ucfirst($user->role) }}')" class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-white border border-rose-200 text-rose-600 font-medium hover:bg-rose-50 transition-colors cursor-pointer">
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

            {{-- TAB 2: Sivitas Terdaftar (Status Akun Dihapus [Poin 6], Tombol Lihat Detail Ditambahkan [Poin 4]) --}}
            <div x-show="currentTab === 'sivitas'" class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-100">
                            <th class="py-3 px-5">Nama Lengkap</th>
                            <th class="py-3 px-4">Kategori Sivitas</th>
                            <th class="py-3 px-4">NIM / NIP</th>
                            <th class="py-3 px-4">Email Kampus</th>
                            <th class="py-3 px-4">Program Studi / Unit</th>
                            <th class="py-3 px-4">Total Reservasi</th>
                            <th class="py-3 px-5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($sivitasUsers as $sivitas)
                            <tr class="hover:bg-slate-50/60 transition-colors"
                                x-show="!searchQuery || '{{ strtolower($sivitas->name . ' ' . $sivitas->identity_number . ' ' . $sivitas->email . ' ' . $sivitas->department) }}'.includes(searchQuery.toLowerCase())">
                                <td class="py-3.5 px-5">
                                    <div class="font-semibold text-slate-800 text-sm">{{ $sivitas->name }}</div>
                                    <div class="text-[11px] text-slate-400">Terdaftar {{ $sivitas->created_at ? $sivitas->created_at->format('d M Y') : '-' }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                        {{ ucfirst($sivitas->role) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 font-mono text-slate-700">{{ $sivitas->identity_number ?? '-' }}</td>
                                <td class="py-3.5 px-4 font-mono text-slate-600">{{ $sivitas->email }}</td>
                                <td class="py-3.5 px-4 text-slate-700 font-medium">{{ $sivitas->department ?? '-' }}</td>
                                <td class="py-3.5 px-4 font-mono text-slate-700 font-semibold">
                                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700">{{ $sivitas->reservations_count ?? $sivitas->reservations->count() }} kali</span>
                                </td>
                                <td class="py-3.5 px-5 text-right">
                                    {{-- Tombol Lihat Detail (Poin 4) --}}
                                    <button type="button" 
                                            @click="openDetailModal({
                                                id: {{ $sivitas->id }},
                                                name: '{{ addslashes($sivitas->name) }}',
                                                role: '{{ ucfirst($sivitas->role) }}',
                                                identity_number: '{{ addslashes($sivitas->identity_number ?? '-') }}',
                                                email: '{{ addslashes($sivitas->email) }}',
                                                department: '{{ addslashes($sivitas->department ?? '-') }}',
                                                phone_number: '{{ addslashes($sivitas->phone_number ?? '-') }}',
                                                id_card_path: '{{ $sivitas->id_card_path ? asset('storage/' . $sivitas->id_card_path) : '' }}',
                                                status: '{{ $sivitas->status }}',
                                                created_at: '{{ $sivitas->created_at ? $sivitas->created_at->format('d M Y, H:i') : '-' }}',
                                                reservations_count: {{ $sivitas->reservations_count ?? $sivitas->reservations->count() }},
                                                reports_count: {{ $sivitas->damage_reports_count ?? $sivitas->damageReports->count() }}
                                            })" 
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors cursor-pointer border border-slate-200/60">
                                        <span class="material-symbols-outlined text-[16px] text-slate-600">visibility</span>
                                        <span>Lihat Detail</span>
                                    </button>
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
                            <th class="py-3 px-4">Tiket Ditangani</th>
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
                                <td class="py-3.5 px-4 font-mono text-slate-700 font-semibold">
                                    <span class="px-2 py-0.5 rounded bg-slate-100 text-slate-700">{{ $petugas->handledReports->count() }} tiket</span>
                                </td>
                                <td class="py-3.5 px-5 text-right">
                                    <button type="button" 
                                            @click="openDetailModal({
                                                id: {{ $petugas->id }},
                                                name: '{{ addslashes($petugas->name) }}',
                                                role: 'Petugas Sarpras',
                                                identity_number: '{{ addslashes($petugas->identity_number ?? '-') }}',
                                                email: '{{ addslashes($petugas->email) }}',
                                                department: '{{ addslashes($petugas->department ?? 'UPT Sarpras') }}',
                                                phone_number: '{{ addslashes($petugas->phone_number ?? '-') }}',
                                                id_card_path: '',
                                                status: '{{ $petugas->status }}',
                                                created_at: '{{ $petugas->created_at ? $petugas->created_at->format('d M Y, H:i') : '-' }}',
                                                reservations_count: {{ $petugas->reviewedReservations->count() }},
                                                reports_count: {{ $petugas->handledReports->count() }}
                                            })" 
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors cursor-pointer border border-slate-200/60">
                                        <span class="material-symbols-outlined text-[16px] text-slate-600">visibility</span>
                                        <span>Lihat Detail</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 px-5 text-center text-slate-400 text-xs">
                                    Belum ada data petugas sarpras terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- TAB 4: Akun Non-aktif & Dihapus (Poin 7) --}}
            <div x-show="currentTab === 'inactive'" class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="bg-rose-50/50 text-slate-500 uppercase tracking-wider font-semibold border-b border-rose-100">
                            <th class="py-3 px-5">Nama & Identitas</th>
                            <th class="py-3 px-4">Kategori Akun</th>
                            <th class="py-3 px-4">Status Arsip</th>
                            <th class="py-3 px-4">Catatan / Alasan</th>
                            <th class="py-3 px-4">Waktu Pembaruan</th>
                            <th class="py-3 px-5 text-right">Otorisasi Pemulihan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($inactiveUsers as $inactive)
                            <tr class="hover:bg-slate-50/60 transition-colors"
                                x-show="!searchQuery || '{{ strtolower($inactive->name . ' ' . $inactive->identity_number . ' ' . $inactive->email . ' ' . $inactive->department) }}'.includes(searchQuery.toLowerCase())">
                                <td class="py-3.5 px-5">
                                    <div class="font-semibold text-slate-800 text-sm">{{ $inactive->name }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $inactive->email }} • ID: {{ $inactive->identity_number ?? '-' }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-100 text-slate-700">
                                        {{ ucfirst($inactive->role) }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4">
                                    @if($inactive->trashed())
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-rose-100 text-rose-800 border border-rose-200">
                                            Dihapus (Soft Delete)
                                        </span>
                                    @elseif($inactive->status === 'inactive')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                                            Dinonaktifkan
                                        </span>
                                    @elseif($inactive->status === 'rejected')
                                        <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-slate-200 text-slate-800 border border-slate-300">
                                            Ditolak Verifikasi
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-slate-500 max-w-xs truncate">
                                    {{ $inactive->rejection_reason ?? '-' }}
                                </td>
                                <td class="py-3.5 px-4 text-slate-500 font-mono">
                                    {{ $inactive->deleted_at ? $inactive->deleted_at->format('d M Y') : $inactive->updated_at->format('d M Y') }}
                                </td>
                                <td class="py-3.5 px-5 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <button type="button" 
                                                @click="openDetailModal({
                                                    id: {{ $inactive->id }},
                                                    name: '{{ addslashes($inactive->name) }}',
                                                    role: '{{ ucfirst($inactive->role) }}',
                                                    identity_number: '{{ addslashes($inactive->identity_number ?? '-') }}',
                                                    email: '{{ addslashes($inactive->email) }}',
                                                    department: '{{ addslashes($inactive->department ?? '-') }}',
                                                    phone_number: '{{ addslashes($inactive->phone_number ?? '-') }}',
                                                    id_card_path: '{{ $inactive->id_card_path ? asset('storage/' . $inactive->id_card_path) : '' }}',
                                                    status: '{{ $inactive->status }}',
                                                    created_at: '{{ $inactive->created_at ? $inactive->created_at->format('d M Y, H:i') : '-' }}',
                                                    reservations_count: {{ $inactive->reservations ? $inactive->reservations->count() : 0 }},
                                                    reports_count: {{ $inactive->damageReports ? $inactive->damageReports->count() : 0 }}
                                                })" 
                                                class="px-2.5 py-1.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-medium transition cursor-pointer">
                                            Detail
                                        </button>

                                        <form action="{{ route('admin.users.toggle-status', $inactive->id) }}" method="POST" class="inline" onsubmit="return confirm('Aktifkan kembali akun {{ addslashes($inactive->name) }}?')">
                                            @csrf
                                            <button type="submit" class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-2xs transition-colors cursor-pointer">
                                                <span class="material-symbols-outlined text-[15px]">restart_alt</span>
                                                <span>Aktifkan Kembali</span>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 px-5 text-center text-slate-400 text-xs">
                                    Tidak ada rekaman akun yang dinonaktifkan atau dihapus.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Footer Info --}}
            <div class="p-4 bg-slate-50/50 border-t border-slate-100 flex flex-col sm:flex-row sm:items-center sm:justify-between text-xs text-slate-500 gap-2">
                <div>Menampilkan data dinamis terverifikasi dari basis data CAVA.</div>
            </div>
        </div>

        {{-- MODAL DETAIL PROFIL + TOMBOL NONAKTIFKAN AKUN (Poin 4 & 5) --}}
        <div x-show="showDetailModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="showDetailModal = false" class="bg-white rounded-2xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-6 border border-slate-200 flex flex-col gap-5">
                {{-- Header Modal --}}
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[22px] text-slate-700">account_circle</span>
                        <h3 class="font-bold text-base text-slate-900">Detail Lengkap Profil Sivitas</h3>
                    </div>
                    <button type="button" @click="showDetailModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                {{-- Profil Header Card --}}
                <div class="flex items-center gap-4 p-4 rounded-xl bg-slate-50 border border-slate-200/80">
                    <div class="w-14 h-14 rounded-2xl bg-slate-900 text-white flex items-center justify-center text-xl font-bold font-mono shrink-0 shadow-2xs">
                        <span x-text="detailUser.name ? detailUser.name.charAt(0).toUpperCase() : 'U'"></span>
                    </div>
                    <div>
                        <h4 class="text-base font-bold text-slate-900" x-text="detailUser.name"></h4>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-100 text-blue-800" x-text="detailUser.role"></span>
                            <span class="text-xs text-slate-500 font-mono" x-text="'ID: ' + detailUser.identity_number"></span>
                        </div>
                    </div>
                </div>

                {{-- Detail Atribut Grid --}}
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div class="p-3 rounded-xl border border-slate-100 bg-slate-50/50">
                        <span class="text-slate-400 block text-[11px] mb-0.5">Email Resmi Kampus</span>
                        <span class="font-semibold text-slate-800 break-all font-mono" x-text="detailUser.email"></span>
                    </div>
                    <div class="p-3 rounded-xl border border-slate-100 bg-slate-50/50">
                        <span class="text-slate-400 block text-[11px] mb-0.5">Nomor Telepon / WhatsApp</span>
                        <span class="font-semibold text-slate-800" x-text="detailUser.phone_number || '-'"></span>
                    </div>
                    <div class="p-3 rounded-xl border border-slate-100 bg-slate-50/50">
                        <span class="text-slate-400 block text-[11px] mb-0.5">Departemen / Program Studi</span>
                        <span class="font-semibold text-slate-800" x-text="detailUser.department || '-'"></span>
                    </div>
                    <div class="p-3 rounded-xl border border-slate-100 bg-slate-50/50">
                        <span class="text-slate-400 block text-[11px] mb-0.5">Waktu Registrasi</span>
                        <span class="font-semibold text-slate-800 font-mono" x-text="detailUser.created_at"></span>
                    </div>
                </div>

                {{-- Aktivitas Peminjaman & Pelaporan --}}
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div class="p-3 rounded-xl border border-emerald-100 bg-emerald-50/50 flex items-center justify-between">
                        <span class="text-emerald-800 font-medium">Reservasi Fasilitas</span>
                        <span class="text-sm font-bold text-emerald-700 font-mono" x-text="(detailUser.reservations_count || 0) + ' Kali'"></span>
                    </div>
                    <div class="p-3 rounded-xl border border-amber-100 bg-amber-50/50 flex items-center justify-between">
                        <span class="text-amber-800 font-medium">Laporan Kerusakan</span>
                        <span class="text-sm font-bold text-amber-700 font-mono" x-text="(detailUser.reports_count || 0) + ' Tiket'"></span>
                    </div>
                </div>

                {{-- Foto Kartu Identitas / KTM (Poin 4) --}}
                <div class="p-3 rounded-xl border border-slate-200">
                    <span class="text-xs font-semibold text-slate-700 block mb-2">Dokumen Berkas Identitas (KTM / SK):</span>
                    <template x-if="detailUser.id_card_path">
                        <div class="space-y-2">
                            <img :src="detailUser.id_card_path" alt="Dokumen Identitas" class="w-full max-h-48 object-contain rounded-lg border border-slate-100 bg-slate-50">
                            <a :href="detailUser.id_card_path" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-blue-600 hover:underline">
                                <span class="material-symbols-outlined text-[14px]">open_in_new</span>
                                <span>Buka gambar di tab baru</span>
                            </a>
                        </div>
                    </template>
                    <template x-if="!detailUser.id_card_path">
                        <div class="p-4 text-center text-xs text-slate-400 bg-slate-50 rounded-lg">
                            Tidak ada lampiran berkas identitas tersimpan.
                        </div>
                    </template>
                </div>

                {{-- Footer Modal & Tombol Aksi Nonaktifkan (Poin 5) --}}
                <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                    <button type="button" @click="showDetailModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer">
                        Tutup
                    </button>
                    
                    {{-- Form Nonaktifkan / Aktifkan Kembali --}}
                    <form :action="'{{ url('/admin/users') }}/' + detailUser.id + '/toggle-status'" method="POST" 
                          onsubmit="return confirm('Apakah Anda yakin ingin memperbarui status akun ini?')">
                        @csrf
                        <template x-if="detailUser.status === 'active'">
                            <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-xs font-semibold shadow-2xs transition-colors cursor-pointer">
                                <span class="material-symbols-outlined text-[16px]">block</span>
                                <span>Nonaktifkan Akun Ini</span>
                            </button>
                        </template>
                        <template x-if="detailUser.status !== 'active'">
                            <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-semibold shadow-2xs transition-colors cursor-pointer">
                                <span class="material-symbols-outlined text-[16px]">check_circle</span>
                                <span>Aktifkan Kembali Akun</span>
                            </button>
                        </template>
                    </form>
                </div>
            </div>
        </div>

        {{-- MODAL 1: Daftarkan Akun Petugas Sarpras (UR13, Bersih Tanpa Duplikasi Field) --}}
        <div x-show="showAddPetugasModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="showAddPetugasModal = false" class="bg-white rounded-2xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-6 border border-slate-200 flex flex-col gap-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2 text-slate-900">
                        <span class="material-symbols-outlined text-[22px]">badge</span>
                        <h3 class="font-bold text-base">Daftarkan Petugas Sarpras Baru (UR13)</h3>
                    </div>
                    <button type="button" @click="showAddPetugasModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                <form action="{{ route('admin.users.create-petugas') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="p-3 bg-amber-50 rounded-xl border border-amber-200 text-xs text-amber-800">
                        <div class="font-semibold mb-0.5">Ketentuan Sistem UR13:</div>
                        Petugas sarpras didaftarkan langsung oleh Super Admin tanpa jalur pendaftaran publik demi integritas keamanan fasilitas kampus.
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1" for="pet-name">Nama Lengkap Petugas</label>
                        <input type="text" id="pet-name" name="name" required placeholder="Contoh: Bambang Setyawan, A.Md." class="w-full h-9 px-3 bg-slate-50 rounded-xl text-xs border border-slate-200 focus:bg-white focus:border-slate-900 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1" for="pet-nip">NIP Petugas</label>
                            <input type="text" id="pet-nip" name="nip" required placeholder="198402122010011002" class="w-full h-9 px-3 bg-slate-50 rounded-xl text-xs border border-slate-200 focus:bg-white focus:border-slate-900 focus:outline-none">
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
                        <input type="email" id="pet-email" name="email" required placeholder="bambang.sarpras@univ.ac.id" class="w-full h-9 px-3 bg-slate-50 rounded-xl text-xs border border-slate-200 focus:bg-white focus:border-slate-900 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1" for="pet-password">Password Sementara (Opsional)</label>
                        <input type="password" id="pet-password" name="password" placeholder="Kosongkan untuk kata sandi default: 'password'" class="w-full h-9 px-3 bg-slate-50 rounded-xl text-xs border border-slate-200 focus:bg-white focus:border-slate-900 focus:outline-none">
                        <span class="text-[10px] text-slate-400 mt-1 block">Minimal 8 karakter jika diisi. Default: password</span>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="showAddPetugasModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer">Batal</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 shadow-md transition-all cursor-pointer">Simpan Akun Petugas</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL 2: Daftarkan Akun Pengguna Langsung (UR14, Bersih Tanpa Duplikasi Field) --}}
        <div x-show="showAddPenggunaModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs">
            <div @click.away="showAddPenggunaModal = false" class="bg-white rounded-2xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-6 border border-slate-200 flex flex-col gap-4">
                <div class="flex items-center justify-between pb-3 border-b border-slate-100">
                    <div class="flex items-center gap-2 text-slate-900">
                        <span class="material-symbols-outlined text-[22px]">person_add</span>
                        <h3 class="font-bold text-base">Daftarkan Akun Pengguna Langsung (UR14)</h3>
                    </div>
                    <button type="button" @click="showAddPenggunaModal = false" class="text-slate-400 hover:text-slate-600 cursor-pointer">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                <form action="{{ route('admin.users.create-user') }}" method="POST" class="space-y-4">
                    @csrf
                    <div class="p-3 bg-blue-50 rounded-xl border border-blue-200 text-xs text-blue-800">
                        <div class="font-semibold mb-0.5">Bypass Verifikasi (UR14):</div>
                        Akun yang didaftarkan langsung oleh Super Admin akan otomatis berstatus <strong>Terverifikasi & Aktif</strong> tanpa perlu proses validasi berkas identitas.
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1" for="usr-name">Nama Lengkap Sivitas</label>
                        <input type="text" id="usr-name" name="name" required placeholder="Contoh: Dimas Pratama" class="w-full h-9 px-3 bg-slate-50 rounded-xl text-xs border border-slate-200 focus:bg-white focus:border-slate-900 focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1" for="usr-role">Peran Sivitas</label>
                            <select id="usr-role" name="role_type" class="w-full h-9 px-3 bg-slate-50 rounded-xl text-xs border border-slate-200 focus:bg-white focus:border-slate-900 focus:outline-none">
                                <option value="mahasiswa">Mahasiswa</option>
                                <option value="dosen">Dosen Tetap</option>
                                <option value="staf">Staf Akademik / Tendik</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-slate-700 mb-1" for="usr-id">NIM / NIP</label>
                            <input type="text" id="usr-id" name="identifier" required placeholder="2110512044" class="w-full h-9 px-3 bg-slate-50 rounded-xl text-xs border border-slate-200 focus:bg-white focus:border-slate-900 focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1" for="usr-department">Fakultas / Program Studi (Opsional)</label>
                        <input type="text" id="usr-department" name="department" placeholder="Contoh: Fakultas Teknik / Informatika" class="w-full h-9 px-3 bg-slate-50 rounded-xl text-xs border border-slate-200 focus:bg-white focus:border-navy focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1" for="usr-email">Email Kampus (@univ.ac.id)</label>
                        <input type="email" id="usr-email" name="email" required placeholder="pengguna@univ.ac.id" class="w-full h-9 px-3 bg-slate-50 rounded-xl text-xs border border-slate-200 focus:bg-white focus:border-slate-900 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1" for="usr-password">Password Sementara (Opsional)</label>
                        <input type="password" id="usr-password" name="password" placeholder="Kosongkan untuk kata sandi default: 'password'" class="w-full h-9 px-3 bg-slate-50 rounded-xl text-xs border border-slate-200 focus:bg-white focus:border-slate-900 focus:outline-none">
                        <span class="text-[10px] text-slate-400 mt-1 block">Minimal 8 karakter jika diisi. Default: password</span>
                    </div>

                    <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                        <button type="button" @click="showAddPenggunaModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer">Batal</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-900 text-white text-xs font-bold hover:bg-slate-800 shadow-md transition-all cursor-pointer">Simpan Akun Langsung</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL 3: Konfirmasi Penolakan Verifikasi (UR15 / ADM-01) --}}
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
                        <button type="button" @click="showRejectModal = false" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 cursor-pointer">Batal</button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 text-white text-xs font-semibold hover:bg-rose-700 shadow-sm transition-colors cursor-pointer">Tolak Pendaftaran</button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-admin-layout>
