{{-- 
====================================================================================================
SISTEM INFORMASI RESERVASI & PELAPORAN FASILITAS KAMPUS (CAVA)
MODUL 6: KONSOL ADMIN - VERIFIKASI AKUN PENDING SIVITAS & MANAJEMEN PENGGUNA INTERNAL (ADM-01 & ADM-02)
BERKAS: resources/views/admin/user-management.blade.php
====================================================================================================

DESKRIPSI BERKAS:
Halaman Konsol Tata Kelola Sivitas & Petugas Sarpras (User Management Console) bagi Super Admin.
Menyediakan antarmuka tunggal terpadu untuk:
1. Verifikasi Antrean Pendaftaran Mandiri Sivitas (ADM-01 / UR15):
   Meninjau kelengkapan identitas calon pengguna (Mahasiswa/Dosen/Staf), memeriksa berkas kartu
   identitas resmi (KTM/NIP/Surat Tugas), menyetujui akun (status -> 'active'), atau menolak dengan alasan terstandarisasi (status -> 'rejected').
2. Pembuatan Akun Internal Langsung (ADM-02 / US-13 & US-14):
   Mendaftarkan personil Petugas Sarpras (teknisi pemeliharaan) serta akun sivitas kolektif
   langsung aktif tanpa melewati antrean verifikasi mandiri.
3. Kontrol Operasional Akun (Quick Suspension & Soft Delete):
   Mengubah status aktif/non-aktif akun secara cepat (toggle-status) serta pengarsipan aman via Soft Delete.

DESAIN & ARSITEKTUR ANTARMUKA:
- Mengadopsi prinsip desain Apple Bento UI & Minimalist Precision: tata letak kartu bersih, sudut membulat lebar (rounded-[2rem]),
  bayangan lembut, serta tipografi kontras tinggi (Inter & Outfit).
- Dikelola oleh mesin reaktivitas sisi klien Alpine.js (`x-data`):
  * Filter pencarian instan multi-kolom tanpa reload halaman (`userSearch`).
  * Tab underline navigasi kategori status (`userTab`: 'all', 'pending', 'active', 'suspended').
  * Dialog modal pratinjau data & kartu identitas sivitas (`showUserModal`).
  * Dialog modal konfirmasi penolakan pendaftaran berformat resmi (`showRejectModal`).
====================================================================================================
--}}

{{--
  PENJELASAN WRAPPER LAYOUT:
  Menggunakan komponen Blade <x-admin-layout> dari app/View/Components/AdminLayout.php:
  - 'title'  : Menyetel judul halaman dinamis "Manajemen Sivitas".
  - 'active' : Mengaktifkan indikator visual rute "user-management" pada sidebar.
--}}
<x-admin-layout title="Manajemen Sivitas" active="user-management">

    {{--
      PENJELASAN STATE MACHINE ALPINE.JS (x-data):
      - 'userTab'         : Menyimpan tab filter aktif ('all', 'pending', 'active', 'suspended').
      - 'userSearch'      : Menyimpan string kata kunci pencarian real-time pengguna.
      - 'showUserModal'   : Boolean visibilitas modal form tambah / detail data sivitas.
      - 'selectedUser'    : Object data pengguna yang dipilih untuk ditinjau (null jika membuat akun baru).
      - 'showRejectModal' : Boolean visibilitas dialog modal penolakan pendaftaran akun pending.
      - 'rejectTarget'    : Object penampung data pengguna target penolakan { id, name, nim, role }.
      - 'openAddUser()'   : Mereset selectedUser ke null lalu membuka modal form pembuatan akun baru.
      - 'openDetailUser()': Mengisi data selectedUser dengan payload pengguna lalu membuka modal detail.
      - 'openReject()'    : Mengisi rejectTarget lalu menampilkan dialog modal penolakan resmi.
    --}}
    <div x-data="{
        userTab: 'all',
        userSearch: '',
        showUserModal: false,
        selectedUser: null,
        showRejectModal: false,
        rejectTarget: { id: null, name: '', nim: '', role: '' },
        openAddUser() {
            this.selectedUser = null;
            this.showUserModal = true;
        },
        openDetailUser(u) {
            this.selectedUser = u;
            this.showUserModal = true;
        },
        openReject(id, name, nim, role) {
            this.rejectTarget = { id, name, nim, role };
            this.showRejectModal = true;
        }
    }" class="space-y-8">

        {{-- ---------------------------------------------------------------------------------- --}}
        {{-- FLASH SESSION NOTIFICATIONS (Umpan Balik Sukses & Galat Controller)                --}}
        {{-- ---------------------------------------------------------------------------------- --}}
        {{-- Menampilkan alert hijau saat operasi verifikasi, aktivasi, atau pembuatan akun sukses: --}}
        @if (session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50/80 border border-emerald-100 text-xs text-emerald-900 flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-[20px] text-emerald-700">check_circle</span>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif

        {{-- Menampilkan alert merah jika terjadi kegagalan validasi atau pelanggaran keamanan: --}}
        @if (isset($errors) && $errors->any())
            <div class="p-4 rounded-2xl bg-rose-50/80 border border-rose-100 text-xs text-rose-900 shadow-xs">
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

        {{-- ---------------------------------------------------------------------------------- --}}
        {{-- 1. HEADER HALAMAN & TOMBOL TAMBAH AKUN INTERNAL (ADM-02)                           --}}
        {{-- ---------------------------------------------------------------------------------- --}}
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-[#1D1D1F] font-display">Manajemen Sivitas</h1>
                <p class="text-sm text-[#86868B] mt-1.5 leading-relaxed">Direktori seluruh civitas akademika, verifikasi pendaftar mandiri, dan penugasan peran sistem.</p>
            </div>
            <div class="flex items-center gap-3">
                {{-- Tombol pemicu modal pembuatan akun baru secara langsung oleh Super Admin --}}
                <button type="button" 
                        @click="openAddUser()" 
                        class="bg-[#1D1D1F] text-white px-5 py-2.5 rounded-xl text-sm font-medium hover:bg-black transition shadow-sm cursor-pointer flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    <span>Tambah Akun Baru</span>
                </button>
            </div>
        </div>

        {{-- ---------------------------------------------------------------------------------- --}}
        {{-- 2. SEARCH BAR PENCARIAN INSTAN & UNDERLINE TABS FILTER                              --}}
        {{-- ---------------------------------------------------------------------------------- --}}
        <div class="space-y-3">
            {{-- Input pencarian real-time dengan efek aura glow halus --}}
            <div class="relative group">
                <div class="absolute -inset-1 bg-gradient-to-r from-blue-100 to-indigo-100 rounded-2xl blur opacity-25 group-hover:opacity-50 transition duration-500"></div>
                <div class="relative bg-white rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-1.5 flex items-center border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400 ml-3 mr-2 text-[20px]">search</span>
                    {{-- Binding dua arah dengan properti Alpine.js userSearch --}}
                    <input type="text" 
                           x-model="userSearch" 
                           placeholder="Cari nama sivitas, NIM/NIP, alamat email, atau jurusan..." 
                           class="flex-1 py-2.5 px-2 border-0 focus:ring-0 focus:border-transparent outline-none text-sm md:text-base bg-transparent text-[#1D1D1F] placeholder:text-[#86868B]" 
                           autocomplete="off">
                    <button type="button" class="bg-[#1D1D1F] text-white px-5 py-2.5 rounded-xl text-sm font-medium hover:bg-black transition cursor-pointer">
                        Cari
                    </button>
                </div>
            </div>

            {{-- 
              Kalkulasi Agregat Jumlah Pengguna untuk Label Tab Underline:
              - $totalSivitasAll : Menjumlahkan seluruh data pada 4 kategori.
              - $activeCountTotal: Total akun yang berstatus aktif (Sivitas + Petugas).
            --}}
            @php
                $totalSivitasAll = $sivitasCount + $petugasCount + $pendingCount + $inactiveCount;
                $activeCountTotal = $sivitasCount + $petugasCount;
            @endphp

            {{-- Underline Tabs Navigasi Kategori Akun --}}
            <div class="flex items-center gap-8 text-sm font-medium overflow-x-auto no-scrollbar pt-2 border-b border-gray-100">
                {{-- Tab 1: Semua Sivitas --}}
                <button type="button" 
                        @click="userTab = 'all'" 
                        :class="userTab === 'all' ? 'text-[#1D1D1F] border-b-2 border-[#1D1D1F] font-semibold' : 'text-[#86868B] hover:text-[#1D1D1F]'" 
                        class="pb-3 -mb-px transition whitespace-nowrap cursor-pointer">
                    Semua Sivitas ({{ $totalSivitasAll }})
                </button>
                {{-- Tab 2: Antrean Verifikasi Pending (ADM-01) --}}
                <button type="button" 
                        @click="userTab = 'pending'" 
                        :class="userTab === 'pending' ? 'text-[#1D1D1F] border-b-2 border-[#1D1D1F] font-semibold' : 'text-[#86868B] hover:text-[#1D1D1F]'" 
                        class="pb-3 -mb-px transition whitespace-nowrap cursor-pointer">
                    Menunggu Otorisasi ({{ $pendingCount }})
                </button>
                {{-- Tab 3: Akun Aktif Sivitas & Petugas --}}
                <button type="button" 
                        @click="userTab = 'active'" 
                        :class="userTab === 'active' ? 'text-[#1D1D1F] border-b-2 border-[#1D1D1F] font-semibold' : 'text-[#86868B] hover:text-[#1D1D1F]'" 
                        class="pb-3 -mb-px transition whitespace-nowrap cursor-pointer">
                    Akun Aktif ({{ $activeCountTotal }})
                </button>
                {{-- Tab 4: Akun Dinonaktifkan / Soft Deleted --}}
                <button type="button" 
                        @click="userTab = 'suspended'" 
                        :class="userTab === 'suspended' ? 'text-[#1D1D1F] border-b-2 border-[#1D1D1F] font-semibold' : 'text-[#86868B] hover:text-[#1D1D1F]'" 
                        class="pb-3 -mb-px transition whitespace-nowrap cursor-pointer">
                    Dinonaktifkan ({{ $inactiveCount }})
                </button>
            </div>
        </div>

        {{-- ---------------------------------------------------------------------------------- --}}
        {{-- 3. TABEL DATA SIVITAS & PETUGAS (DENGAN REAKTIVITAS ALPINE.JS)                     --}}
        {{-- ---------------------------------------------------------------------------------- --}}
        <div class="bg-white rounded-[2rem] border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-xs text-[#86868B] font-medium bg-[#FBFBFD]">
                            <th class="py-4.5 px-6">Identitas Sivitas</th>
                            <th class="py-4.5 px-6">NIM / NIP</th>
                            <th class="py-4.5 px-6">Peran & Departemen</th>
                            <th class="py-4.5 px-6">Status Akun</th>
                            <th class="py-4.5 px-6 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        {{-- 
                          PENGGABUNGAN DATA & FILTER DINAMIS:
                          Baris tabel di bawah ini disaring secara reaktif menggunakan direktif Alpine.js `x-show`:
                          1. Memeriksa kecocokan tab aktif (`userTab`).
                          2. Memeriksa substring teks pencarian (`userSearch`) pada nama, email, identitas, atau departemen.
                        --}}

                        {{-- ==================================================================== --}}
                        {{-- KELOMPOK 1: PENDAFTAR MANDIRI BERSTATUS PENDING (ADM-01)            --}}
                        {{-- ==================================================================== --}}
                        @foreach($pendingUsers as $user)
                            <tr class="hover:bg-[#FBFBFD] transition group"
                                x-show="(userTab === 'all' || userTab === 'pending') && (!userSearch || '{{ strtolower($user->name . ' ' . $user->identity_number . ' ' . $user->email . ' ' . $user->department . ' ' . $user->role) }}'.includes(userSearch.toLowerCase()))">
                                <td class="py-5 px-6">
                                    <div class="font-bold text-[#1D1D1F]">{{ $user->name }}</div>
                                    <div class="text-xs text-[#86868B] mt-0.5">{{ $user->email }}</div>
                                </td>
                                <td class="py-5 px-6 font-mono text-xs text-[#1D1D1F]">{{ $user->identity_number ?? '-' }}</td>
                                <td class="py-5 px-6">
                                    <div class="font-medium text-[#1D1D1F]">{{ ucfirst($user->role) }}</div>
                                    <div class="text-xs text-[#86868B] mt-0.5">{{ $user->department ?? 'Sivitas Mandiri' }}</div>
                                </td>
                                <td class="py-5 px-6">
                                    <span class="text-xs font-semibold block text-amber-800">
                                        Menunggu Otorisasi
                                    </span>
                                </td>
                                <td class="py-5 px-6 text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        {{-- Formulir Verifikasi Cepat (POST ke admin.users.verify) --}}
                                        <form action="{{ route('admin.users.verify', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Verifikasi dan aktifkan akun sivitas ini?')">
                                            @csrf
                                            <button type="submit" class="px-3 py-1.5 rounded-xl bg-[#1D1D1F] text-white text-xs font-medium hover:bg-black transition cursor-pointer">
                                                Verifikasi
                                            </button>
                                        </form>

                                        {{-- Tombol Buka Dialog Penolakan Berformat Resmi --}}
                                        <button type="button" 
                                                @click="openReject({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->identity_number ?? '-') }}', '{{ ucfirst($user->role) }}')" 
                                                class="px-3 py-1.5 rounded-xl bg-rose-50 text-rose-700 hover:bg-rose-100 text-xs font-medium transition cursor-pointer">
                                            Tolak
                                        </button>

                                        {{-- Tombol Buka Modal Pratinjau Detail & Berkas Kartu Identitas --}}
                                        <button type="button" 
                                                @click="openDetailUser({
                                                    id: {{ $user->id }},
                                                    name: '{{ addslashes($user->name) }}',
                                                    email: '{{ addslashes($user->email) }}',
                                                    nim: '{{ addslashes($user->identity_number ?? '-') }}',
                                                    role: '{{ ucfirst($user->role) }}',
                                                    dept: '{{ addslashes($user->department ?? '-') }}',
                                                    phone: '{{ addslashes($user->phone_number ?? '-') }}',
                                                    id_card: '{{ $user->id_card_path ? asset('storage/' . $user->id_card_path) : '' }}',
                                                    status: 'pending',
                                                    created_at: '{{ $user->created_at ? $user->created_at->format('d M Y, H:i') : '-' }}'
                                                })" 
                                                class="text-xs font-medium text-[#86868B] hover:text-[#1D1D1F] transition cursor-pointer">
                                            Detail
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach

                        {{-- ==================================================================== --}}
                        {{-- KELOMPOK 2: SIVITAS AKADEMIKA AKTIF (MAHASISWA, DOSEN, STAF)        --}}
                        {{-- ==================================================================== --}}
                        @foreach($sivitasUsers as $user)
                            <tr class="hover:bg-[#FBFBFD] transition group"
                                x-show="(userTab === 'all' || userTab === 'active') && (!userSearch || '{{ strtolower($user->name . ' ' . $user->identity_number . ' ' . $user->email . ' ' . $user->department . ' ' . $user->role) }}'.includes(userSearch.toLowerCase()))">
                                <td class="py-5 px-6">
                                    <div class="font-bold text-[#1D1D1F]">{{ $user->name }}</div>
                                    <div class="text-xs text-[#86868B] mt-0.5">{{ $user->email }}</div>
                                </td>
                                <td class="py-5 px-6 font-mono text-xs text-[#1D1D1F]">{{ $user->identity_number ?? '-' }}</td>
                                <td class="py-5 px-6">
                                    <div class="font-medium text-[#1D1D1F]">{{ ucfirst($user->role) }}</div>
                                    <div class="text-xs text-[#86868B] mt-0.5">{{ $user->department ?? 'Sivitas Aktif' }}</div>
                                </td>
                                <td class="py-5 px-6">
                                    <span class="text-xs font-semibold block text-emerald-800">
                                        Aktif
                                    </span>
                                </td>
                                <td class="py-5 px-6 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <button type="button" 
                                                @click="openDetailUser({
                                                    id: {{ $user->id }},
                                                    name: '{{ addslashes($user->name) }}',
                                                    email: '{{ addslashes($user->email) }}',
                                                    nim: '{{ addslashes($user->identity_number ?? '-') }}',
                                                    role: '{{ ucfirst($user->role) }}',
                                                    dept: '{{ addslashes($user->department ?? '-') }}',
                                                    phone: '{{ addslashes($user->phone_number ?? '-') }}',
                                                    id_card: '{{ $user->id_card_path ? asset('storage/' . $user->id_card_path) : '' }}',
                                                    status: 'active',
                                                    created_at: '{{ $user->created_at ? $user->created_at->format('d M Y, H:i') : '-' }}'
                                                })" 
                                                class="text-xs font-medium text-[#86868B] hover:text-[#1D1D1F] transition cursor-pointer">
                                            Lihat Detail
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach

                        {{-- ==================================================================== --}}
                        {{-- KELOMPOK 3: PETUGAS SARPRAS AKTIF (OPERASIONAL FASILITAS)           --}}
                        {{-- ==================================================================== --}}
                        @foreach($petugasUsers as $user)
                            <tr class="hover:bg-[#FBFBFD] transition group"
                                x-show="(userTab === 'all' || userTab === 'active') && (!userSearch || '{{ strtolower($user->name . ' ' . $user->identity_number . ' ' . $user->email . ' ' . $user->department . ' ' . $user->assignment_zone) }}'.includes(userSearch.toLowerCase()))">
                                <td class="py-5 px-6">
                                    <div class="font-bold text-[#1D1D1F]">{{ $user->name }}</div>
                                    <div class="text-xs text-[#86868B] mt-0.5">{{ $user->email }}</div>
                                </td>
                                <td class="py-5 px-6 font-mono text-xs text-[#1D1D1F]">{{ $user->identity_number ?? '-' }}</td>
                                <td class="py-5 px-6">
                                    <div class="font-medium text-[#1D1D1F]">Petugas Sarpras</div>
                                    <div class="text-xs text-[#86868B] mt-0.5">{{ $user->assignment_zone ?? 'Zona Terpadu' }}</div>
                                </td>
                                <td class="py-5 px-6">
                                    <span class="text-xs font-semibold block text-emerald-800">
                                        Aktif
                                    </span>
                                </td>
                                <td class="py-5 px-6 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <button type="button" 
                                                @click="openDetailUser({
                                                    id: {{ $user->id }},
                                                    name: '{{ addslashes($user->name) }}',
                                                    email: '{{ addslashes($user->email) }}',
                                                    nim: '{{ addslashes($user->identity_number ?? '-') }}',
                                                    role: 'Petugas Sarpras',
                                                    dept: '{{ addslashes($user->assignment_zone ?? 'UPT Sarpras') }}',
                                                    phone: '{{ addslashes($user->phone_number ?? '-') }}',
                                                    id_card: '',
                                                    status: 'active',
                                                    created_at: '{{ $user->created_at ? $user->created_at->format('d M Y, H:i') : '-' }}'
                                                })" 
                                                class="text-xs font-medium text-[#86868B] hover:text-[#1D1D1F] transition cursor-pointer">
                                            Lihat Detail
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach

                        {{-- ==================================================================== --}}
                        {{-- KELOMPOK 4: AKUN DINONAKTIFKAN / DITANGGUHKAN / SOFT DELETED        --}}
                        {{-- ==================================================================== --}}
                        @foreach($inactiveUsers as $user)
                            <tr class="hover:bg-[#FBFBFD] transition group"
                                x-show="(userTab === 'all' || userTab === 'suspended') && (!userSearch || '{{ strtolower($user->name . ' ' . $user->identity_number . ' ' . $user->email . ' ' . $user->department . ' ' . $user->role) }}'.includes(userSearch.toLowerCase()))">
                                <td class="py-5 px-6">
                                    <div class="font-bold text-[#1D1D1F]">{{ $user->name }}</div>
                                    <div class="text-xs text-[#86868B] mt-0.5">{{ $user->email }}</div>
                                </td>
                                <td class="py-5 px-6 font-mono text-xs text-[#1D1D1F]">{{ $user->identity_number ?? '-' }}</td>
                                <td class="py-5 px-6">
                                    <div class="font-medium text-[#1D1D1F]">{{ ucfirst($user->role) }}</div>
                                    <div class="text-xs text-[#86868B] mt-0.5">{{ $user->department ?? '-' }}</div>
                                </td>
                                <td class="py-5 px-6">
                                    <span class="text-xs font-semibold block text-rose-800">
                                        Dinonaktifkan
                                    </span>
                                </td>
                                <td class="py-5 px-6 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <button type="button" 
                                                @click="openDetailUser({
                                                    id: {{ $user->id }},
                                                    name: '{{ addslashes($user->name) }}',
                                                    email: '{{ addslashes($user->email) }}',
                                                    nim: '{{ addslashes($user->identity_number ?? '-') }}',
                                                    role: '{{ ucfirst($user->role) }}',
                                                    dept: '{{ addslashes($user->department ?? '-') }}',
                                                    phone: '{{ addslashes($user->phone_number ?? '-') }}',
                                                    id_card: '{{ $user->id_card_path ? asset('storage/' . $user->id_card_path) : '' }}',
                                                    status: 'inactive',
                                                    created_at: '{{ $user->created_at ? $user->created_at->format('d M Y, H:i') : '-' }}'
                                                })" 
                                                class="text-xs font-medium text-[#86868B] hover:text-[#1D1D1F] transition cursor-pointer">
                                            Lihat Detail
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- ================================================================================== --}}
        {{-- MODAL TAMBAH / DETAIL SIVITAS (BENTO UI CARD WITH ALPINE.JS)                       --}}
        {{-- ================================================================================== --}}
        <div x-show="showUserModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            {{-- Backdrop Gelap dengan Efek Blur --}}
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="showUserModal = false"></div>
            
            {{-- Wadah Kartu Dialog Modal --}}
            <div class="relative w-full max-w-md bg-white rounded-[2rem] p-7 sm:p-8 shadow-2xl border border-gray-100 z-10 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-4 mb-5 border-b border-gray-100">
                    <h3 class="text-base font-bold text-[#1D1D1F]" x-text="selectedUser ? 'Detail Data Sivitas' : 'Tambah Akun Baru'"></h3>
                    <button type="button" @click="showUserModal = false" class="text-gray-400 hover:text-[#1D1D1F] cursor-pointer">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                {{-- -------------------------------------------------------------------------- --}}
                {{-- 1. FORMULIR TAMBAH AKUN BARU (Dirender saat selectedUser === null)          --}}
                {{-- -------------------------------------------------------------------------- --}}
                <template x-if="!selectedUser">
                    <form action="{{ route('admin.users.create-user') }}" method="POST" class="space-y-4 text-xs">
                        @csrf
                        <div>
                            <label class="block font-medium text-[#86868B] mb-1.5" for="m-name">Nama Lengkap</label>
                            <input type="text" id="m-name" name="name" required maxlength="150" placeholder="Nama lengkap sivitas..." class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-4 py-2.5 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-black">
                        </div>
                        <div>
                            <label class="block font-medium text-[#86868B] mb-1.5" for="m-id">NIM / NIP</label>
                            <input type="text" id="m-id" name="identifier" required maxlength="50" placeholder="Nomor induk mahasiswa atau pegawai..." class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-4 py-2.5 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-black font-mono">
                        </div>
                        <div>
                            <label class="block font-medium text-[#86868B] mb-1.5" for="m-role">Peran Sivitas</label>
                            <select id="m-role" name="role_type" class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-4 py-2.5 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-black">
                                <option value="mahasiswa">Mahasiswa</option>
                                <option value="dosen">Dosen</option>
                                <option value="staf">Tendik / Staf</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-medium text-[#86868B] mb-1.5" for="m-dept">Departemen / Program Studi</label>
                            <input type="text" id="m-dept" name="department" maxlength="100" placeholder="Contoh: Teknik Informatika" class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-4 py-2.5 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-black">
                        </div>
                        <div>
                            <label class="block font-medium text-[#86868B] mb-1.5" for="m-email">Email Kampus</label>
                            <input type="email" id="m-email" name="email" required maxlength="150" placeholder="nama@students.undip.ac.id" class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-4 py-2.5 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-black">
                        </div>
                        <div>
                            <label class="block font-medium text-[#86868B] mb-1.5" for="m-pass">Kata Sandi Awal (Opsional)</label>
                            <input type="password" id="m-pass" name="password" minlength="8" placeholder="Kosongkan untuk default: 'password' (min. 8 karakter)" class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-4 py-2.5 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-black">
                        </div>
                        <div class="pt-5 mt-6 border-t border-gray-100 flex items-center justify-end gap-3">
                            <button type="button" @click="showUserModal = false" class="px-4 py-2 rounded-xl text-xs font-medium text-[#86868B] hover:text-[#1D1D1F]">Batal</button>
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#1D1D1F] text-white text-xs font-medium hover:bg-black transition cursor-pointer">Simpan Perubahan</button>
                        </div>
                    </form>
                </template>

                {{-- -------------------------------------------------------------------------- --}}
                {{-- 2. TAMPILAN DETAIL SIVITAS & KARTU IDENTITAS (Saat selectedUser !== null)   --}}
                {{-- -------------------------------------------------------------------------- --}}
                <template x-if="selectedUser">
                    <div class="space-y-4 text-xs">
                        <div>
                            <label class="block font-medium text-[#86868B] mb-1">Nama Lengkap</label>
                            <div class="font-bold text-[#1D1D1F] text-sm" x-text="selectedUser.name"></div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block font-medium text-[#86868B] mb-1">NIM / NIP</label>
                                <div class="font-mono text-sm text-[#1D1D1F]" x-text="selectedUser.nim"></div>
                            </div>
                            <div>
                                <label class="block font-medium text-[#86868B] mb-1">Peran Sivitas</label>
                                <div class="font-semibold text-sm text-[#1D1D1F]" x-text="selectedUser.role"></div>
                            </div>
                        </div>
                        <div>
                            <label class="block font-medium text-[#86868B] mb-1">Email Resmi</label>
                            <div class="font-mono text-xs text-[#1D1D1F]" x-text="selectedUser.email"></div>
                        </div>
                        <div>
                            <label class="block font-medium text-[#86868B] mb-1">Departemen / Jurusan</label>
                            <div class="text-xs text-[#1D1D1F]" x-text="selectedUser.dept"></div>
                        </div>
                        
                        {{-- Pratinjau Gambar Berkas Bukti Kartu Identitas (KTM / NIP) --}}
                        <template x-if="selectedUser.id_card">
                            <div>
                                <label class="block font-medium text-[#86868B] mb-1.5">Berkas Bukti Identitas (KTM / SK)</label>
                                <a :href="selectedUser.id_card" target="_blank" title="Klik untuk memperbesar gambar">
                                    <img :src="selectedUser.id_card" alt="Bukti Identitas" class="w-full max-h-44 object-contain rounded-xl border border-gray-100 bg-[#F5F5F7] hover:opacity-90 transition">
                                </a>
                                <p class="text-[10px] text-gray-400 mt-1">Klik gambar untuk membuka ukuran penuh di tab baru.</p>
                            </div>
                        </template>

                        {{-- Tindakan Pengubahan Status Cepat (Aktif <-> Nonaktif) --}}
                        <div class="pt-5 mt-6 border-t border-gray-100 flex items-center justify-between gap-3">
                            <form :action="'{{ url('/admin/users') }}/' + selectedUser.id + '/toggle-status'" method="POST" onsubmit="return confirm('Perbarui status akun ini?')">
                                @csrf
                                <button type="submit" 
                                        :class="selectedUser.status === 'active' ? 'text-rose-600 hover:underline' : 'text-emerald-700 hover:underline'" 
                                        class="text-xs font-semibold cursor-pointer" 
                                        x-text="selectedUser.status === 'active' ? 'Nonaktifkan Akun' : 'Aktifkan Akun'"></button>
                            </form>
                            <button type="button" @click="showUserModal = false" class="px-5 py-2.5 rounded-xl bg-[#1D1D1F] text-white text-xs font-medium hover:bg-black transition cursor-pointer">
                                Tutup
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- ================================================================================== --}}
        {{-- MODAL KONFIRMASI PENOLAKAN PENDAFTARAN SIVITAS PENDING (ADM-01 / RejectUserRequest)--}}
        {{-- ================================================================================== --}}
        <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="showRejectModal = false"></div>
            
            <div class="relative w-full max-w-md bg-white rounded-[2rem] p-7 sm:p-8 shadow-2xl border border-gray-100 z-10">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-gray-100">
                    <div class="flex items-center gap-2 text-rose-700">
                        <span class="material-symbols-outlined text-[22px]">cancel</span>
                        <h3 class="text-base font-bold text-[#1D1D1F]">Tolak Pendaftaran Akun</h3>
                    </div>
                    <button type="button" @click="showRejectModal = false" class="text-gray-400 hover:text-[#1D1D1F] cursor-pointer">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                {{-- Formulir Penolakan Akun yang mengirim payload reason & notes ke admin.users.reject --}}
                <form :action="'{{ url('/admin/users') }}/' + rejectTarget.id + '/reject'" method="POST" class="space-y-4 text-xs">
                    @csrf
                    <div>
                        <p class="text-slate-600 mb-3">
                            Anda akan menolak pendaftaran akun sivitas <strong class="text-slate-900" x-text="rejectTarget.name"></strong> (<span x-text="rejectTarget.nim"></span>).
                        </p>
                    </div>

                    {{-- Pilihan Alasan Standar Terverifikasi Sesuai RejectUserRequest --}}
                    <div>
                        <label class="block font-medium text-[#86868B] mb-1.5" for="rej-reason">Kategori Alasan Penolakan <span class="text-rose-500">*</span></label>
                        <select id="rej-reason" name="reason" required class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-4 py-2.5 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-rose-500">
                            <option value="invalid_ktm">Foto KTM / SK buram atau tidak terbaca</option>
                            <option value="mismatched_data">Data NIM/NIP tidak cocok dengan pangkalan data</option>
                            <option value="invalid_email">Bukan domain email resmi universitas</option>
                            <option value="invalid_data">Data identitas palsu atau mencurigakan</option>
                            <option value="other">Alasan lainnya</option>
                        </select>
                    </div>

                    {{-- Catatan Tambahan bagi Sivitas (Opsional) --}}
                    <div>
                        <label class="block font-medium text-[#86868B] mb-1.5" for="rej-notes">Catatan Tambahan untuk Pendaftar (Opsional)</label>
                        <textarea id="rej-notes" name="notes" rows="3" maxlength="500" placeholder="Berikan arahan perbaikan bagi sivitas untuk pendaftaran ulang..." class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-4 py-2.5 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-rose-500 resize-none"></textarea>
                    </div>

                    <div class="pt-5 mt-5 border-t border-gray-100 flex items-center justify-end gap-3">
                        <button type="button" @click="showRejectModal = false" class="px-4 py-2 rounded-xl text-xs font-medium text-[#86868B] hover:text-[#1D1D1F]">Batal</button>
                        <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-600 text-white text-xs font-medium hover:bg-rose-700 transition cursor-pointer shadow-sm">
                            Konfirmasi Tolak
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-admin-layout>
