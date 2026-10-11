{{-- 
  NAMA FILE      : user-management.blade.php
  FUNGSIONALITAS : Halaman Manajemen Sivitas Kampus & Otorisasi Pengguna (100% Sesuai Opsi 1 Prototype 4_try-admin.html)
  DESKRIPSI      : Menampilkan direktori seluruh sivitas akademika, verifikasi pendaftar mandiri, underline tabs kategori (Semua Sivitas, Menunggu Otorisasi, Akun Aktif, Dinonaktifkan), tabel presisi dengan status tipografis murni, serta modal interaktif Apple Bento.
  DESAIN SISTEM  : Apple Bento & Minimalist Precision (Opsi 1)
  CARA KERJA     : Menggunakan layout <x-admin-layout active="user-management"> dengan state Alpine.js terintegrasi penuh ke endpoint Controller.
--}}

<x-admin-layout title="Manajemen Sivitas" active="user-management">
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

        {{-- Flash Session Notifications --}}
        @if (session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50/80 border border-emerald-100 text-xs text-emerald-900 flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-3">
                    <span class="material-symbols-outlined text-[20px] text-emerald-700">check_circle</span>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            </div>
        @endif

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

        {{-- 1. Header & Action Button (100% Sesuai 4_try-admin.html Opsi 1) --}}
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-[#1D1D1F] font-display">Manajemen Sivitas</h1>
                <p class="text-sm text-[#86868B] mt-1.5 leading-relaxed">Direktori seluruh civitas akademika, verifikasi pendaftar mandiri, dan penugasan peran sistem.</p>
            </div>
            <div class="flex items-center gap-3">
                <button type="button" 
                        @click="openAddUser()" 
                        class="bg-[#1D1D1F] text-white px-5 py-2.5 rounded-xl text-sm font-medium hover:bg-black transition shadow-sm cursor-pointer flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px]">add</span>
                    <span>Tambah Akun Baru</span>
                </button>
            </div>
        </div>

        {{-- 2. Search Bar Desain Katalog Publik (Aura Glow Halus) & Underline Tabs Filter --}}
        <div class="space-y-3">
            <div class="relative group">
                <div class="absolute -inset-1 bg-gradient-to-r from-blue-100 to-indigo-100 rounded-2xl blur opacity-25 group-hover:opacity-50 transition duration-500"></div>
                <div class="relative bg-white rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-1.5 flex items-center border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400 ml-3 mr-2 text-[20px]">search</span>
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

            <!-- Underline Tabs Filter Kategori (Bebas Kotak Warna-Warni - 100% Sesuai Prototype) -->
            @php
                $totalSivitasAll = $sivitasCount + $petugasCount + $pendingCount + $inactiveCount;
                $activeCountTotal = $sivitasCount + $petugasCount;
            @endphp
            <div class="flex items-center gap-8 text-sm font-medium overflow-x-auto no-scrollbar pt-2 border-b border-gray-100">
                <button type="button" 
                        @click="userTab = 'all'" 
                        :class="userTab === 'all' ? 'text-[#1D1D1F] border-b-2 border-[#1D1D1F] font-semibold' : 'text-[#86868B] hover:text-[#1D1D1F]'" 
                        class="pb-3 -mb-px transition whitespace-nowrap cursor-pointer">
                    Semua Sivitas ({{ $totalSivitasAll }})
                </button>
                <button type="button" 
                        @click="userTab = 'pending'" 
                        :class="userTab === 'pending' ? 'text-[#1D1D1F] border-b-2 border-[#1D1D1F] font-semibold' : 'text-[#86868B] hover:text-[#1D1D1F]'" 
                        class="pb-3 -mb-px transition whitespace-nowrap cursor-pointer">
                    Menunggu Otorisasi ({{ $pendingCount }})
                </button>
                <button type="button" 
                        @click="userTab = 'active'" 
                        :class="userTab === 'active' ? 'text-[#1D1D1F] border-b-2 border-[#1D1D1F] font-semibold' : 'text-[#86868B] hover:text-[#1D1D1F]'" 
                        class="pb-3 -mb-px transition whitespace-nowrap cursor-pointer">
                    Akun Aktif ({{ $activeCountTotal }})
                </button>
                <button type="button" 
                        @click="userTab = 'suspended'" 
                        :class="userTab === 'suspended' ? 'text-[#1D1D1F] border-b-2 border-[#1D1D1F] font-semibold' : 'text-[#86868B] hover:text-[#1D1D1F]'" 
                        class="pb-3 -mb-px transition whitespace-nowrap cursor-pointer">
                    Dinonaktifkan ({{ $inactiveCount }})
                </button>
            </div>
        </div>

        {{-- 3. Tabel Data Sivitas: Bersih, Lega, dan Status Tipografis Murni --}}
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
                        {{-- Data Gabungan dengan Filter Tab Alpine.js & Pencarian Real-time --}}
                        
                        {{-- 1. Pendaftar Pending --}}
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
                                    <div class="flex items-center justify-end gap-3">
                                        <form action="{{ route('admin.users.verify', $user->id) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="px-3.5 py-1.5 rounded-xl bg-[#1D1D1F] text-white text-xs font-medium hover:bg-black transition cursor-pointer">
                                                Verifikasi
                                            </button>
                                        </form>
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
                                            Lihat Detail
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach

                        {{-- 2. Sivitas Aktif --}}
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

                        {{-- 3. Petugas Sarpras Aktif --}}
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

                        {{-- 4. Akun Nonaktif / Ditangguhkan --}}
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

        {{-- ============================================================== --}}
        {{-- MODAL TAMBAH / DETAIL SIVITAS (100% Persis 4_try-admin.html)   --}}
        {{-- ============================================================== --}}
        <div x-show="showUserModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/40 backdrop-blur-sm" @click="showUserModal = false"></div>
            <div class="relative w-full max-w-md bg-white rounded-[2rem] p-7 sm:p-8 shadow-2xl border border-gray-100 z-10 max-h-[90vh] overflow-y-auto">
                <div class="flex items-center justify-between pb-4 mb-5 border-b border-gray-100">
                    <h3 class="text-base font-bold text-[#1D1D1F]" x-text="selectedUser ? 'Detail Data Sivitas' : 'Tambah Akun Baru'"></h3>
                    <button type="button" @click="showUserModal = false" class="text-gray-400 hover:text-[#1D1D1F] cursor-pointer">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>
                </div>

                {{-- FORM TAMBAH PENGGUNA BARU (Saat selectedUser null) --}}
                <template x-if="!selectedUser">
                    <form action="{{ route('admin.users.create-user') }}" method="POST" class="space-y-4 text-xs">
                        @csrf
                        <div>
                            <label class="block font-medium text-[#86868B] mb-1.5" for="m-name">Nama Lengkap</label>
                            <input type="text" id="m-name" name="name" required placeholder="Nama lengkap sivitas..." class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-4 py-2.5 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-black">
                        </div>
                        <div>
                            <label class="block font-medium text-[#86868B] mb-1.5" for="m-id">NIM / NIP</label>
                            <input type="text" id="m-id" name="identifier" required placeholder="Nomor induk mahasiswa atau pegawai..." class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-4 py-2.5 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-black font-mono">
                        </div>
                        <div>
                            <label class="block font-medium text-[#86868B] mb-1.5" for="m-role">Peran Sivitas</label>
                            <select id="m-role" name="role_type" class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-4 py-2.5 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-black">
                                <option value="mahasiswa">Mahasiswa</option>
                                <option value="dosen">Dosen</option>
                                <option value="staf">Tendik</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-medium text-[#86868B] mb-1.5" for="m-dept">Departemen / Program Studi</label>
                            <input type="text" id="m-dept" name="department" placeholder="Contoh: Teknik Informatika" class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-4 py-2.5 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-black">
                        </div>
                        <div>
                            <label class="block font-medium text-[#86868B] mb-1.5" for="m-email">Email Kampus</label>
                            <input type="email" id="m-email" name="email" required placeholder="nama@students.undip.ac.id" class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-4 py-2.5 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-black">
                        </div>
                        <div>
                            <label class="block font-medium text-[#86868B] mb-1.5" for="m-pass">Kata Sandi Awal (Opsional)</label>
                            <input type="password" id="m-pass" name="password" placeholder="Kosongkan untuk default: 'password'" class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-4 py-2.5 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-black">
                        </div>
                        <div class="pt-5 mt-6 border-t border-gray-100 flex items-center justify-end gap-3">
                            <button type="button" @click="showUserModal = false" class="px-4 py-2 rounded-xl text-xs font-medium text-[#86868B] hover:text-[#1D1D1F]">Batal</button>
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#1D1D1F] text-white text-xs font-medium hover:bg-black transition cursor-pointer">Simpan Perubahan</button>
                        </div>
                    </form>
                </template>

                {{-- TAMPILAN DETAIL SIVITAS (Saat selectedUser aktif) --}}
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
                        
                        <template x-if="selectedUser.id_card">
                            <div>
                                <label class="block font-medium text-[#86868B] mb-1.5">Berkas Bukti Identitas (KTM / SK)</label>
                                <img :src="selectedUser.id_card" alt="Bukti Identitas" class="w-full max-h-44 object-contain rounded-xl border border-gray-100 bg-[#F5F5F7]">
                            </div>
                        </template>

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

    </div>
</x-admin-layout>
