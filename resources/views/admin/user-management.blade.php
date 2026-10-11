{{-- 
  NAMA FILE      : user-management.blade.php
  FUNGSIONALITAS : Halaman Manajemen Sivitas Kampus & Otorisasi Pengguna (Super Admin)
  DESKRIPSI      : Menampilkan metriks pengguna, antrean otorisasi pendaftar mandiri, direktori sivitas, petugas sarpras, akun arsip/nonaktif, serta modal penambahan dan detail profil komprehensif.
  DESAIN SISTEM  : Opsi 1 (Executive Apple Bento & Minimalist Precision)
                   - Layout Bento Box lega (rounded-[2rem], border gray-100, soft shadows)
                   - Search Bar dengan aura glow halus adaptasi katalog publik
                   - Underline navigation tabs minimalis bebas kotak warna-warni
                   - Pure typographic status indicator (Aktif / Menunggu Otorisasi / Dinonaktifkan)
  CARA KERJA     : Menggunakan layout <x-admin-layout active="user-management"> dengan state Alpine.js terintegrasi penuh ke endpoint Controller.
--}}

<x-admin-layout title="Manajemen Sivitas & Otorisasi Akun" active="user-management">
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
    }" class="space-y-8">

        {{-- 1. Header Eksekutif & Tombol Tindakan Cepat --}}
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <nav class="flex items-center gap-2 text-xs text-[#86868B] mb-1.5">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-[#1D1D1F] transition-colors">Admin Console</a>
                    <span>/</span>
                    <span class="text-[#1D1D1F] font-medium">Manajemen Pengguna</span>
                </nav>
                <h1 class="text-3xl font-extrabold tracking-tight text-[#1D1D1F] font-display">Manajemen Sivitas</h1>
                <p class="text-sm text-[#86868B] mt-1 leading-relaxed">
                    Direktori seluruh civitas akademika, otorisasi pendaftar mandiri, dan penugasan peran sistem kampus.
                </p>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <button type="button" 
                        @click="showAddPetugasModal = true" 
                        class="px-4 py-2.5 rounded-xl bg-white border border-gray-200 text-[#1D1D1F] text-xs font-semibold hover:bg-gray-50 transition shadow-[0_2px_8px_rgb(0,0,0,0.02)] cursor-pointer flex items-center gap-2">
                    <span class="material-symbols-outlined text-[16px] text-[#86868B]">badge</span>
                    <span>+ Akun Petugas</span>
                </button>
                <button type="button" 
                        @click="showAddPenggunaModal = true" 
                        class="px-4 py-2.5 rounded-xl bg-[#1D1D1F] text-white text-xs font-semibold hover:bg-black transition shadow-sm cursor-pointer flex items-center gap-2">
                    <span class="material-symbols-outlined text-[16px]">person_add</span>
                    <span>+ Akun Sivitas</span>
                </button>
            </div>
        </div>

        {{-- 2. Notifikasi Flash Session --}}
        @if (session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50/80 border border-emerald-100 text-xs text-emerald-900 flex items-center justify-between shadow-[0_4px_20px_rgb(0,0,0,0.02)]">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-[20px] text-emerald-700">check_circle</span>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        @if (isset($errors) && $errors->any())
            <div class="p-4 rounded-2xl bg-rose-50/80 border border-rose-100 text-xs text-rose-900 flex items-center justify-between shadow-[0_4px_20px_rgb(0,0,0,0.02)]">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-[20px] text-rose-700">error</span>
                    <div class="space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <p class="font-medium">{{ $error }}</p>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- 3. Bento Metriks Sivitas (4 Kolom Bersih & Berbobot) --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            {{-- Metrik 1: Antrean Otorisasi --}}
            <div class="bg-white rounded-[2rem] p-6 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                <div class="text-xs font-medium text-[#86868B] mb-2">Menunggu Otorisasi</div>
                <div class="text-3xl font-extrabold text-[#1D1D1F] tracking-tight font-display">{{ number_format($pendingCount) }}</div>
                <div class="text-xs text-amber-800 font-semibold mt-2 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    <span>Verifikasi berkas identitas</span>
                </div>
            </div>

            {{-- Metrik 2: Sivitas Aktif --}}
            <div class="bg-white rounded-[2rem] p-6 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                <div class="text-xs font-medium text-[#86868B] mb-2">Sivitas Terdaftar</div>
                <div class="text-3xl font-extrabold text-[#1D1D1F] tracking-tight font-display">{{ number_format($sivitasCount) }}</div>
                <div class="text-xs text-emerald-800 font-semibold mt-2 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>Hak akses aktif beroperasi</span>
                </div>
            </div>

            {{-- Metrik 3: Petugas Sarpras --}}
            <div class="bg-white rounded-[2rem] p-6 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                <div class="text-xs font-medium text-[#86868B] mb-2">Petugas Sarpras</div>
                <div class="text-3xl font-extrabold text-[#1D1D1F] tracking-tight font-display">{{ number_format($petugasCount) }}</div>
                <div class="text-xs text-[#86868B] mt-2 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#1D1D1F]"></span>
                    <span>Personel operasional kampus</span>
                </div>
            </div>

            {{-- Metrik 4: Akun Non-aktif --}}
            <div class="bg-white rounded-[2rem] p-6 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                <div class="text-xs font-medium text-[#86868B] mb-2">Akun Non-aktif / Arsip</div>
                <div class="text-3xl font-extrabold text-[#1D1D1F] tracking-tight font-display">{{ number_format($inactiveCount) }}</div>
                <div class="text-xs text-rose-800 font-semibold mt-2 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                    <span>Dibekukan atau ditolak</span>
                </div>
            </div>
        </div>

        {{-- 4. Search Filter & Underline Tabs Filter --}}
        <div class="space-y-4">
            {{-- Search Bar dengan Desain Aura Glow Halus (Katalog Publik Style) --}}
            <div class="relative group">
                <div class="absolute -inset-1 bg-gradient-to-r from-blue-100 to-indigo-100 rounded-2xl blur opacity-25 group-hover:opacity-50 transition duration-500"></div>
                <div class="relative bg-white rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-1.5 flex items-center border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400 ml-3 mr-2 text-[20px]">search</span>
                    <input type="text" 
                           x-model="searchQuery" 
                           placeholder="Cari nama sivitas, NIM / NIP, alamat email kampus, atau program studi..." 
                           class="flex-1 py-2.5 px-2 border-0 focus:ring-0 focus:border-transparent outline-none text-sm bg-transparent text-[#1D1D1F] placeholder:text-[#86868B]" 
                           autocomplete="off">
                    <button type="button" 
                            @click="searchQuery = ''" 
                            x-show="searchQuery.length > 0" 
                            class="px-3 text-xs text-[#86868B] hover:text-[#1D1D1F] transition">
                        Reset
                    </button>
                </div>
            </div>

            {{-- Underline Tabs Navigasi Minimalis (Bebas Kotak Warna-Warni) --}}
            <div class="flex items-center gap-8 text-sm font-medium overflow-x-auto no-scrollbar border-b border-gray-100 pt-1">
                <button type="button" 
                        @click="currentTab = 'sivitas'" 
                        :class="currentTab === 'sivitas' ? 'text-[#1D1D1F] border-b-2 border-[#1D1D1F] font-semibold' : 'text-[#86868B] hover:text-[#1D1D1F]'" 
                        class="pb-3 -mb-px transition whitespace-nowrap cursor-pointer">
                    Sivitas Terdaftar ({{ $sivitasCount }})
                </button>
                <button type="button" 
                        @click="currentTab = 'verification'" 
                        :class="currentTab === 'verification' ? 'text-[#1D1D1F] border-b-2 border-[#1D1D1F] font-semibold' : 'text-[#86868B] hover:text-[#1D1D1F]'" 
                        class="pb-3 -mb-px transition whitespace-nowrap cursor-pointer">
                    Antrean Otorisasi ({{ $pendingCount }})
                </button>
                <button type="button" 
                        @click="currentTab = 'petugas'" 
                        :class="currentTab === 'petugas' ? 'text-[#1D1D1F] border-b-2 border-[#1D1D1F] font-semibold' : 'text-[#86868B] hover:text-[#1D1D1F]'" 
                        class="pb-3 -mb-px transition whitespace-nowrap cursor-pointer">
                    Petugas Sarpras ({{ $petugasCount }})
                </button>
                <button type="button" 
                        @click="currentTab = 'inactive'" 
                        :class="currentTab === 'inactive' ? 'text-[#1D1D1F] border-b-2 border-[#1D1D1F] font-semibold' : 'text-[#86868B] hover:text-[#1D1D1F]'" 
                        class="pb-3 -mb-px transition whitespace-nowrap cursor-pointer">
                    Non-aktif / Dihapus ({{ $inactiveCount }})
                </button>
            </div>
        </div>

        {{-- 5. Tabel Data Eksekutif (Card Bento Mewah) --}}
        <div class="bg-white rounded-[2rem] border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)] overflow-hidden">

            {{-- TAB 1: Sivitas Terdaftar --}}
            <div x-show="currentTab === 'sivitas'" class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-xs text-[#86868B] font-medium bg-[#FBFBFD]">
                            <th class="py-4.5 px-6">Identitas Sivitas</th>
                            <th class="py-4.5 px-6">NIM / NIP</th>
                            <th class="py-4.5 px-6">Peran & Departemen</th>
                            <th class="py-4.5 px-6">Total Reservasi</th>
                            <th class="py-4.5 px-6">Status Akun</th>
                            <th class="py-4.5 px-6 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($sivitasUsers as $sivitas)
                            <tr class="hover:bg-[#FBFBFD] transition group"
                                x-show="!searchQuery || '{{ strtolower($sivitas->name . ' ' . $sivitas->identity_number . ' ' . $sivitas->email . ' ' . $sivitas->department) }}'.includes(searchQuery.toLowerCase())">
                                <td class="py-5 px-6">
                                    <div class="font-bold text-[#1D1D1F]">{{ $sivitas->name }}</div>
                                    <div class="text-xs text-[#86868B] mt-0.5">{{ $sivitas->email }}</div>
                                </td>
                                <td class="py-5 px-6 font-mono text-xs text-[#1D1D1F]">{{ $sivitas->identity_number ?? '-' }}</td>
                                <td class="py-5 px-6">
                                    <div class="font-medium text-[#1D1D1F]">{{ ucfirst($sivitas->role) }}</div>
                                    <div class="text-xs text-[#86868B] mt-0.5">{{ $sivitas->department ?? 'Sivitas Umum' }}</div>
                                </td>
                                <td class="py-5 px-6">
                                    <span class="text-xs font-semibold text-[#1D1D1F]">{{ $sivitas->reservations_count ?? $sivitas->reservations->count() }} Kali</span>
                                </td>
                                <td class="py-5 px-6">
                                    <span class="text-xs font-semibold text-emerald-800">Aktif</span>
                                </td>
                                <td class="py-5 px-6 text-right">
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
                                            class="text-xs font-semibold text-[#1D1D1F] hover:underline transition cursor-pointer">
                                        Lihat Detail &rarr;
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-16 text-center text-[#86868B] text-xs">
                                    Belum ada sivitas akademika terdaftar dalam sistem.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- TAB 2: Antrean Otorisasi (Pending Registration) --}}
            <div x-show="currentTab === 'verification'" class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-xs text-[#86868B] font-medium bg-[#FBFBFD]">
                            <th class="py-4.5 px-6">Nama & Institusi</th>
                            <th class="py-4.5 px-6">Kategori & NIM/NIP</th>
                            <th class="py-4.5 px-6">Email Resmi</th>
                            <th class="py-4.5 px-6">Waktu Pengajuan</th>
                            <th class="py-4.5 px-6">Dokumen Identitas</th>
                            <th class="py-4.5 px-6 text-right">Tindakan Otorisasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($pendingUsers as $user)
                            <tr class="hover:bg-[#FBFBFD] transition group"
                                x-show="!searchQuery || '{{ strtolower($user->name . ' ' . $user->identity_number . ' ' . $user->email . ' ' . $user->department) }}'.includes(searchQuery.toLowerCase())">
                                <td class="py-5 px-6">
                                    <div class="font-bold text-[#1D1D1F]">{{ $user->name }}</div>
                                    <div class="text-xs text-[#86868B] mt-0.5">{{ $user->department ?? 'Sivitas Baru' }}</div>
                                </td>
                                <td class="py-5 px-6">
                                    <div class="font-medium text-[#1D1D1F]">{{ ucfirst($user->role) }}</div>
                                    <div class="font-mono text-xs text-[#86868B] mt-0.5">{{ $user->identity_number ?? '-' }}</div>
                                </td>
                                <td class="py-5 px-6 font-mono text-xs text-[#1D1D1F]">{{ $user->email }}</td>
                                <td class="py-5 px-6 text-xs text-[#86868B]">
                                    {{ $user->created_at ? $user->created_at->format('d M Y, H:i') : '-' }}
                                </td>
                                <td class="py-5 px-6">
                                    @if($user->id_card_path)
                                        <a href="{{ asset('storage/' . $user->id_card_path) }}" target="_blank" class="inline-flex items-center gap-1.5 text-xs font-semibold text-[#1D1D1F] hover:underline">
                                            <span class="material-symbols-outlined text-[16px]">visibility</span>
                                            <span>Lihat Berkas</span>
                                        </a>
                                    @else
                                        <span class="text-xs text-[#86868B] italic">Tidak ada berkas</span>
                                    @endif
                                </td>
                                <td class="py-5 px-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <form action="{{ route('admin.users.verify', $user->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-[#1D1D1F] text-white text-xs font-medium hover:bg-black transition cursor-pointer">
                                                Setujui
                                            </button>
                                        </form>

                                        <button type="button" 
                                                @click="openRejectModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->identity_number ?? '-') }}', '{{ ucfirst($user->role) }}')" 
                                                class="px-3 py-1.5 rounded-xl text-xs font-medium text-[#86868B] hover:text-rose-600 transition cursor-pointer">
                                            Tolak
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-16 text-center text-[#86868B] text-xs">
                                    <span class="material-symbols-outlined text-4xl block mb-2 text-gray-300">verified_user</span>
                                    Tidak ada antrean otorisasi pendaftaran akun saat ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- TAB 3: Petugas Sarpras --}}
            <div x-show="currentTab === 'petugas'" class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-xs text-[#86868B] font-medium bg-[#FBFBFD]">
                            <th class="py-4.5 px-6">Nama Petugas</th>
                            <th class="py-4.5 px-6">NIP</th>
                            <th class="py-4.5 px-6">Zona Penugasan</th>
                            <th class="py-4.5 px-6">Email Resmi</th>
                            <th class="py-4.5 px-6">Tiket Selesai</th>
                            <th class="py-4.5 px-6 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($petugasUsers as $petugas)
                            <tr class="hover:bg-[#FBFBFD] transition group"
                                x-show="!searchQuery || '{{ strtolower($petugas->name . ' ' . $petugas->identity_number . ' ' . $petugas->email . ' ' . $petugas->department . ' ' . $petugas->assignment_zone) }}'.includes(searchQuery.toLowerCase())">
                                <td class="py-5 px-6">
                                    <div class="font-bold text-[#1D1D1F]">{{ $petugas->name }}</div>
                                    <div class="text-xs text-[#86868B] mt-0.5">{{ $petugas->department ?? 'Unit Pelaksana Teknis Sarpras' }}</div>
                                </td>
                                <td class="py-5 px-6 font-mono text-xs text-[#1D1D1F]">{{ $petugas->identity_number ?? '-' }}</td>
                                <td class="py-5 px-6">
                                    <span class="text-xs font-semibold text-[#1D1D1F]">{{ $petugas->assignment_zone ?? 'Zona Terpadu' }}</span>
                                </td>
                                <td class="py-5 px-6 font-mono text-xs text-[#86868B]">{{ $petugas->email }}</td>
                                <td class="py-5 px-6 font-mono text-xs text-[#1D1D1F] font-semibold">
                                    {{ $petugas->handledReports->count() }} Tiket
                                </td>
                                <td class="py-5 px-6 text-right">
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
                                            class="text-xs font-semibold text-[#1D1D1F] hover:underline transition cursor-pointer">
                                        Lihat Detail &rarr;
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-16 text-center text-[#86868B] text-xs">
                                    Belum ada data petugas sarpras terdaftar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- TAB 4: Akun Non-aktif & Arsip --}}
            <div x-show="currentTab === 'inactive'" class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-xs text-[#86868B] font-medium bg-[#FBFBFD]">
                            <th class="py-4.5 px-6">Nama & Identitas</th>
                            <th class="py-4.5 px-6">Peran</th>
                            <th class="py-4.5 px-6">Status Arsip</th>
                            <th class="py-4.5 px-6">Alasan Pembekuan</th>
                            <th class="py-4.5 px-6">Waktu Pembaruan</th>
                            <th class="py-4.5 px-6 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($inactiveUsers as $inactive)
                            <tr class="hover:bg-[#FBFBFD] transition group"
                                x-show="!searchQuery || '{{ strtolower($inactive->name . ' ' . $inactive->identity_number . ' ' . $inactive->email . ' ' . $inactive->department) }}'.includes(searchQuery.toLowerCase())">
                                <td class="py-5 px-6">
                                    <div class="font-bold text-[#1D1D1F]">{{ $inactive->name }}</div>
                                    <div class="text-xs text-[#86868B] mt-0.5">{{ $inactive->email }}</div>
                                </td>
                                <td class="py-5 px-6 text-xs text-[#1D1D1F]">{{ ucfirst($inactive->role) }}</td>
                                <td class="py-5 px-6">
                                    @if($inactive->trashed())
                                        <span class="text-xs font-semibold text-rose-800">Dihapus</span>
                                    @elseif($inactive->status === 'inactive')
                                        <span class="text-xs font-semibold text-amber-800">Dinonaktifkan</span>
                                    @elseif($inactive->status === 'rejected')
                                        <span class="text-xs font-semibold text-[#86868B]">Ditolak Verifikasi</span>
                                    @endif
                                </td>
                                <td class="py-5 px-6 text-xs text-[#86868B] max-w-xs truncate">
                                    {{ $inactive->rejection_reason ?? '-' }}
                                </td>
                                <td class="py-5 px-6 text-xs text-[#86868B] font-mono">
                                    {{ $inactive->deleted_at ? $inactive->deleted_at->format('d M Y') : $inactive->updated_at->format('d M Y') }}
                                </td>
                                <td class="py-5 px-6 text-right">
                                    <div class="flex items-center justify-end gap-3">
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
                                                class="text-xs font-semibold text-[#1D1D1F] hover:underline cursor-pointer">
                                            Detail
                                        </button>

                                        <form action="{{ route('admin.users.toggle-status', $inactive->id) }}" method="POST" class="inline" onsubmit="return confirm('Aktifkan kembali akun {{ addslashes($inactive->name) }}?')">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-xl bg-white border border-gray-200 text-xs font-semibold text-emerald-800 hover:bg-emerald-50 transition cursor-pointer">
                                                Pulihkan
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-16 text-center text-[#86868B] text-xs">
                                    Tidak ada rekaman akun yang dinonaktifkan atau dihapus.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Footer Info --}}
            <div class="p-5 bg-[#FBFBFD] border-t border-gray-100 flex items-center justify-between text-xs text-[#86868B]">
                <span>Sinkronisasi basis data institusi terverifikasi.</span>
                <span>CAVA Identity Directory</span>
            </div>
        </div>

        {{-- ============================================================== --}}
        {{-- MODAL 1: Detail Lengkap Profil Sivitas                         --}}
        {{-- ============================================================== --}}
        <div x-show="showDetailModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm">
            <div @click.away="showDetailModal = false" class="bg-white rounded-[2rem] shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-8 border border-gray-100 flex flex-col gap-6">
                
                {{-- Header Modal --}}
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <div>
                        <h3 class="font-bold text-lg text-[#1D1D1F] font-display">Profil Sivitas</h3>
                        <p class="text-xs text-[#86868B] mt-0.5">Detail informasi identitas dan rekaman aktivitas sistem.</p>
                    </div>
                    <button type="button" @click="showDetailModal = false" class="text-[#86868B] hover:text-[#1D1D1F] transition cursor-pointer">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                {{-- Header Pengguna --}}
                <div class="flex items-center gap-4 p-5 rounded-2xl bg-[#FBFBFD] border border-gray-100">
                    <div class="w-14 h-14 rounded-2xl bg-[#1D1D1F] text-white flex items-center justify-center text-xl font-bold font-mono shrink-0 shadow-sm">
                        <span x-text="detailUser.name ? detailUser.name.charAt(0).toUpperCase() : 'U'"></span>
                    </div>
                    <div class="min-w-0">
                        <h4 class="text-base font-bold text-[#1D1D1F] truncate" x-text="detailUser.name"></h4>
                        <div class="flex items-center gap-2 mt-1">
                            <span class="text-xs font-semibold text-[#1D1D1F]" x-text="detailUser.role"></span>
                            <span class="text-xs text-[#86868B] font-mono" x-text="'• ID: ' + detailUser.identity_number"></span>
                        </div>
                    </div>
                </div>

                {{-- Grid Informasi Detail --}}
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div class="p-3.5 rounded-2xl bg-[#FBFBFD] border border-gray-100/80">
                        <span class="text-[#86868B] block text-[11px] mb-1">Email Resmi Kampus</span>
                        <span class="font-semibold text-[#1D1D1F] break-all font-mono" x-text="detailUser.email"></span>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-[#FBFBFD] border border-gray-100/80">
                        <span class="text-[#86868B] block text-[11px] mb-1">Nomor Kontak / WhatsApp</span>
                        <span class="font-semibold text-[#1D1D1F]" x-text="detailUser.phone_number || '-'"></span>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-[#FBFBFD] border border-gray-100/80">
                        <span class="text-[#86868B] block text-[11px] mb-1">Departemen / Prodi</span>
                        <span class="font-semibold text-[#1D1D1F]" x-text="detailUser.department || '-'"></span>
                    </div>
                    <div class="p-3.5 rounded-2xl bg-[#FBFBFD] border border-gray-100/80">
                        <span class="text-[#86868B] block text-[11px] mb-1">Tanggal Registrasi</span>
                        <span class="font-semibold text-[#1D1D1F] font-mono" x-text="detailUser.created_at"></span>
                    </div>
                </div>

                {{-- Aktivitas Reservasi & Laporan --}}
                <div class="grid grid-cols-2 gap-3 text-xs">
                    <div class="p-4 rounded-2xl bg-[#FBFBFD] border border-gray-100/80 flex items-center justify-between">
                        <div>
                            <span class="text-[#86868B] block text-[11px]">Total Reservasi</span>
                            <span class="text-base font-bold text-[#1D1D1F]" x-text="(detailUser.reservations_count || 0) + ' Kali'"></span>
                        </div>
                        <span class="material-symbols-outlined text-gray-400 text-[20px]">meeting_room</span>
                    </div>
                    <div class="p-4 rounded-2xl bg-[#FBFBFD] border border-gray-100/80 flex items-center justify-between">
                        <div>
                            <span class="text-[#86868B] block text-[11px]">Laporan Fasilitas</span>
                            <span class="text-base font-bold text-[#1D1D1F]" x-text="(detailUser.reports_count || 0) + ' Tiket'"></span>
                        </div>
                        <span class="material-symbols-outlined text-gray-400 text-[20px]">build</span>
                    </div>
                </div>

                {{-- Berkas KTM / SK Identitas --}}
                <div class="p-4 rounded-2xl bg-[#FBFBFD] border border-gray-100/80">
                    <span class="text-xs font-semibold text-[#1D1D1F] block mb-2.5">Dokumen Bukti Identitas (KTM / SK Resmi):</span>
                    <template x-if="detailUser.id_card_path">
                        <div class="space-y-2">
                            <img :src="detailUser.id_card_path" alt="Dokumen Identitas" class="w-full max-h-48 object-contain rounded-xl border border-gray-200 bg-white">
                            <a :href="detailUser.id_card_path" target="_blank" class="inline-flex items-center gap-1.5 text-xs text-[#1D1D1F] font-semibold hover:underline">
                                <span class="material-symbols-outlined text-[14px]">open_in_new</span>
                                <span>Buka dokumen berkas ukuran penuh</span>
                            </a>
                        </div>
                    </template>
                    <template x-if="!detailUser.id_card_path">
                        <div class="py-4 text-center text-xs text-[#86868B]">
                            Tidak ada lampiran dokumen identitas tersimpan.
                        </div>
                    </template>
                </div>

                {{-- Footer Modal & Tombol Pembekuan Akun --}}
                <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                    <button type="button" @click="showDetailModal = false" class="px-5 py-2.5 rounded-xl text-xs font-semibold text-[#86868B] hover:text-[#1D1D1F] transition cursor-pointer">
                        Tutup
                    </button>
                    
                    <form :action="'{{ url('/admin/users') }}/' + detailUser.id + '/toggle-status'" method="POST" 
                          onsubmit="return confirm('Perbarui status otorisasi akun ini?')">
                        @csrf
                        <template x-if="detailUser.status === 'active'">
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-white border border-rose-200 text-rose-800 text-xs font-semibold hover:bg-rose-50 transition shadow-sm cursor-pointer">
                                Nonaktifkan Akun
                            </button>
                        </template>
                        <template x-if="detailUser.status !== 'active'">
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#1D1D1F] text-white text-xs font-semibold hover:bg-black transition shadow-sm cursor-pointer">
                                Aktifkan Akun
                            </button>
                        </template>
                    </form>
                </div>

            </div>
        </div>

        {{-- ============================================================== --}}
        {{-- MODAL 2: Tambah Akun Petugas Sarpras                           --}}
        {{-- ============================================================== --}}
        <div x-show="showAddPetugasModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm">
            <div @click.away="showAddPetugasModal = false" class="bg-white rounded-[2rem] shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-8 border border-gray-100 flex flex-col gap-5">
                
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <div>
                        <h3 class="font-bold text-lg text-[#1D1D1F] font-display">Tambah Petugas Sarpras</h3>
                        <p class="text-xs text-[#86868B] mt-0.5">Daftarkan personel teknis untuk penanganan sarana & prasarana kampus.</p>
                    </div>
                    <button type="button" @click="showAddPetugasModal = false" class="text-[#86868B] hover:text-[#1D1D1F] transition cursor-pointer">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                <form action="{{ route('admin.users.create-petugas') }}" method="POST" class="space-y-4">
                    @csrf
                    
                    <div>
                        <label class="block text-xs font-semibold text-[#1D1D1F] mb-1.5" for="pet-name">Nama Lengkap Petugas</label>
                        <input type="text" id="pet-name" name="name" required placeholder="Contoh: Bambang Setyawan, A.Md." class="w-full h-10 px-3.5 bg-[#FBFBFD] rounded-xl text-xs border border-gray-200 text-[#1D1D1F] focus:bg-white focus:border-[#1D1D1F] focus:outline-none transition">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#1D1D1F] mb-1.5" for="pet-nip">NIP Petugas</label>
                            <input type="text" id="pet-nip" name="nip" required placeholder="198402122010011002" class="w-full h-10 px-3.5 bg-[#FBFBFD] rounded-xl text-xs border border-gray-200 text-[#1D1D1F] focus:bg-white focus:border-[#1D1D1F] focus:outline-none transition">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#1D1D1F] mb-1.5" for="pet-zone">Zona Penugasan</label>
                            <select id="pet-zone" name="zone" class="w-full h-10 px-3 bg-[#FBFBFD] rounded-xl text-xs border border-gray-200 text-[#1D1D1F] focus:bg-white focus:border-[#1D1D1F] focus:outline-none transition">
                                <option value="Zona 1 (Rektorat & Auditorium)">Zona 1 (Rektorat & Auditorium)</option>
                                <option value="Zona 2 (Gedung Kuliah Terpadu)">Zona 2 (Gedung Kuliah Terpadu)</option>
                                <option value="Zona 3 (Laboratorium Terpadu & Jaringan)">Zona 3 (Laboratorium Terpadu & Jaringan)</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#1D1D1F] mb-1.5" for="pet-email">Email Resmi Sarpras</label>
                        <input type="email" id="pet-email" name="email" required placeholder="bambang.sarpras@univ.ac.id" class="w-full h-10 px-3.5 bg-[#FBFBFD] rounded-xl text-xs border border-gray-200 text-[#1D1D1F] focus:bg-white focus:border-[#1D1D1F] focus:outline-none transition">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#1D1D1F] mb-1.5" for="pet-password">Kata Sandi Awal (Opsional)</label>
                        <input type="password" id="pet-password" name="password" placeholder="Kosongkan untuk kata sandi default: 'password'" class="w-full h-10 px-3.5 bg-[#FBFBFD] rounded-xl text-xs border border-gray-200 text-[#1D1D1F] focus:bg-white focus:border-[#1D1D1F] focus:outline-none transition">
                        <span class="text-[11px] text-[#86868B] mt-1 block">Default: password (dapat diganti mandiri setelah login).</span>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="showAddPetugasModal = false" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-[#86868B] hover:text-[#1D1D1F] transition cursor-pointer">Batal</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#1D1D1F] text-white text-xs font-semibold hover:bg-black transition shadow-sm cursor-pointer">Simpan Akun Petugas</button>
                    </div>
                </form>

            </div>
        </div>

        {{-- ============================================================== --}}
        {{-- MODAL 3: Daftarkan Akun Sivitas Langsung                       --}}
        {{-- ============================================================== --}}
        <div x-show="showAddPenggunaModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm">
            <div @click.away="showAddPenggunaModal = false" class="bg-white rounded-[2rem] shadow-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto p-8 border border-gray-100 flex flex-col gap-5">
                
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <div>
                        <h3 class="font-bold text-lg text-[#1D1D1F] font-display">Tambah Sivitas Baru</h3>
                        <p class="text-xs text-[#86868B] mt-0.5">Pendaftaran langsung otomatis berstatus terotorisasi dan siap digunakan.</p>
                    </div>
                    <button type="button" @click="showAddPenggunaModal = false" class="text-[#86868B] hover:text-[#1D1D1F] transition cursor-pointer">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                <form action="{{ route('admin.users.create-user') }}" method="POST" class="space-y-4">
                    @csrf

                    <div>
                        <label class="block text-xs font-semibold text-[#1D1D1F] mb-1.5" for="usr-name">Nama Lengkap Sivitas</label>
                        <input type="text" id="usr-name" name="name" required placeholder="Contoh: Dimas Pratama" class="w-full h-10 px-3.5 bg-[#FBFBFD] rounded-xl text-xs border border-gray-200 text-[#1D1D1F] focus:bg-white focus:border-[#1D1D1F] focus:outline-none transition">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#1D1D1F] mb-1.5" for="usr-role">Peran Sivitas</label>
                            <select id="usr-role" name="role_type" class="w-full h-10 px-3 bg-[#FBFBFD] rounded-xl text-xs border border-gray-200 text-[#1D1D1F] focus:bg-white focus:border-[#1D1D1F] focus:outline-none transition">
                                <option value="mahasiswa">Mahasiswa</option>
                                <option value="dosen">Dosen Tetap</option>
                                <option value="staf">Staf Akademik / Tendik</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#1D1D1F] mb-1.5" for="usr-id">NIM / NIP</label>
                            <input type="text" id="usr-id" name="identifier" required placeholder="2110512044" class="w-full h-10 px-3.5 bg-[#FBFBFD] rounded-xl text-xs border border-gray-200 text-[#1D1D1F] focus:bg-white focus:border-[#1D1D1F] focus:outline-none transition">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#1D1D1F] mb-1.5" for="usr-department">Fakultas / Program Studi (Opsional)</label>
                        <input type="text" id="usr-department" name="department" placeholder="Contoh: Fakultas Sains dan Matematika" class="w-full h-10 px-3.5 bg-[#FBFBFD] rounded-xl text-xs border border-gray-200 text-[#1D1D1F] focus:bg-white focus:border-[#1D1D1F] focus:outline-none transition">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#1D1D1F] mb-1.5" for="usr-email">Email Resmi Kampus</label>
                        <input type="email" id="usr-email" name="email" required placeholder="pengguna@students.undip.ac.id" class="w-full h-10 px-3.5 bg-[#FBFBFD] rounded-xl text-xs border border-gray-200 text-[#1D1D1F] focus:bg-white focus:border-[#1D1D1F] focus:outline-none transition">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#1D1D1F] mb-1.5" for="usr-password">Kata Sandi Awal (Opsional)</label>
                        <input type="password" id="usr-password" name="password" placeholder="Kosongkan untuk kata sandi default: 'password'" class="w-full h-10 px-3.5 bg-[#FBFBFD] rounded-xl text-xs border border-gray-200 text-[#1D1D1F] focus:bg-white focus:border-[#1D1D1F] focus:outline-none transition">
                        <span class="text-[11px] text-[#86868B] mt-1 block">Default: password (dapat diganti mandiri setelah login).</span>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-100">
                        <button type="button" @click="showAddPenggunaModal = false" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-[#86868B] hover:text-[#1D1D1F] transition cursor-pointer">Batal</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#1D1D1F] text-white text-xs font-semibold hover:bg-black transition shadow-sm cursor-pointer">Simpan Akun Sivitas</button>
                    </div>
                </form>

            </div>
        </div>

        {{-- ============================================================== --}}
        {{-- MODAL 4: Konfirmasi Penolakan Otorisasi                        --}}
        {{-- ============================================================== --}}
        <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm">
            <div @click.away="showRejectModal = false" class="bg-white rounded-[2rem] shadow-2xl max-w-md w-full p-8 border border-gray-100 flex flex-col gap-5">
                
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-[22px]">warning</span>
                    </div>
                    <div>
                        <h3 class="font-bold text-[#1D1D1F] text-sm">Tolak Otorisasi Registrasi</h3>
                        <p class="text-xs text-[#86868B] mt-0.5">Pemohon tidak dapat mengakses sistem CAVA.</p>
                    </div>
                </div>

                <form :action="'{{ url('/admin/users') }}/' + selectedUser.id + '/reject'" method="POST" class="space-y-4">
                    @csrf
                    
                    <div class="p-4 bg-[#FBFBFD] rounded-2xl border border-gray-100 text-xs">
                        <div class="font-bold text-[#1D1D1F]" x-text="selectedUser.name"></div>
                        <div class="text-[#86868B] mt-0.5"><span x-text="selectedUser.role"></span> • NIM/NIP: <span class="font-mono text-[#1D1D1F]" x-text="selectedUser.nim"></span></div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#1D1D1F] mb-1.5" for="reject-reason">Alasan Penolakan</label>
                        <select id="reject-reason" name="reason" required class="w-full h-10 px-3 bg-[#FBFBFD] rounded-xl text-xs border border-gray-200 text-[#1D1D1F] focus:bg-white focus:border-[#1D1D1F] focus:outline-none transition mb-2">
                            <option value="invalid_ktm">Foto KTM / SK buram atau tidak terbaca</option>
                            <option value="mismatched_data">Data NIM/NIP tidak terdaftar dalam pangkalan data</option>
                            <option value="invalid_email">Bukan domain email resmi universitas</option>
                            <option value="other">Alasan lainnya</option>
                        </select>
                        <textarea name="notes" rows="2" placeholder="Catatan tambahan untuk pemohon (opsional)..." class="w-full p-3 bg-[#FBFBFD] rounded-xl text-xs border border-gray-200 text-[#1D1D1F] focus:bg-white focus:border-[#1D1D1F] focus:outline-none transition resize-none"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-3 border-t border-gray-100">
                        <button type="button" @click="showRejectModal = false" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-[#86868B] hover:text-[#1D1D1F] transition cursor-pointer">Batal</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-white border border-rose-200 text-rose-800 text-xs font-semibold hover:bg-rose-50 transition shadow-sm cursor-pointer">Tolak Registrasi</button>
                    </div>
                </form>

            </div>
        </div>

    </div>
</x-admin-layout>
