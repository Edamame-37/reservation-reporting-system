{{-- 
  NAMA FILE      : user-management.blade.php
  FUNGSIONALITAS : Halaman Antarmuka Manajemen Pengguna & Verifikasi Akun (Super Admin)
  DESKRIPSI      : Menampilkan antrean verifikasi akun registrasi mandiri mahasiswa/dosen (UR15), serta modul pembuatan akun petugas (UR13) dan akun pengguna langsung (UR14).
  CARA KERJA     : Memanfaatkan layout <x-admin-layout active="user-management">, mengelola dialog registrasi langsung dan otorisasi verifikasi akun via Alpine.js.
--}}

<x-admin-layout title="Manajemen User & Verifikasi Akun" active="user-management">
    <div x-data="{
        showAddPetugasModal: false,
        showAddPenggunaModal: false
    }" class="flex flex-col gap-space-xl">
        <!-- 
          ELEMEN       : Section Manajemen User & Verifikasi Akun Sivitas (UR13, UR14, UR15)
          KEGUNAAN     : Memeriksa berkas identitas sivitas (KTM/SK) untuk mengaktifkan akun pendaftaran mandiri atau menambahkan akun secara langsung.
          CARA KERJA   : Super Admin dapat menyetujui/menolak antrean akun pending atau mendaftarkan akun petugas/pengguna baru via modal.
        -->
        <section class="flex flex-col rounded-xl bg-surface-container-lowest shadow-sm overflow-hidden border border-outline-variant/50">
            {{-- Section Header & Controls --}}
            <div class="p-space-lg bg-surface-container-low flex flex-col xl:flex-row items-start xl:items-center justify-between gap-space-md border-b border-outline-variant">
                <div class="flex items-center gap-space-md">
                    <div class="w-10 h-10 rounded-lg bg-primary text-on-primary flex items-center justify-center shadow-sm">
                        <span class="material-symbols-outlined text-[22px]">how_to_reg</span>
                    </div>
                    <div class="flex flex-col">
                        <div class="flex items-center gap-space-sm">
                            <span class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant font-data-mono text-[11px]">UR13 | UR14 | UR15</span>
                            <span class="px-2 py-0.5 rounded font-label-sm text-label-sm bg-error-container text-on-error-container font-bold">5 Antrean Verifikasi</span>
                        </div>
                        <h2 class="font-headline-md text-headline-md text-primary">Manajemen User & Verifikasi Akun Sivitas</h2>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-space-sm w-full xl:w-auto">
                    <div class="relative flex-1 sm:w-64">
                        <span class="material-symbols-outlined absolute left-2.5 top-2 text-outline text-[18px]">search</span>
                        <input type="text" placeholder="Cari nama, NIM, NIP, email..." class="w-full h-9 pl-9 pr-space-md rounded-lg bg-surface-container-lowest text-body-sm text-on-surface placeholder:text-on-surface-variant border border-outline-variant/60 focus:border-primary focus:outline-none shadow-sm">
                    </div>
                    <button type="button" @click="showAddPetugasModal = true" class="h-9 px-space-md rounded-lg bg-primary text-on-primary font-label-sm text-label-sm shadow-sm hover:bg-primary-container transition-colors flex items-center gap-1 font-semibold">
                        <span class="material-symbols-outlined text-[16px]">badge</span>
                        <span>+ Akun Petugas (UR13)</span>
                    </button>
                    <button type="button" @click="showAddPenggunaModal = true" class="h-9 px-space-md rounded-lg bg-surface-container-highest text-primary font-label-sm text-label-sm shadow-sm hover:bg-surface-container transition-colors flex items-center gap-1 font-bold">
                        <span class="material-symbols-outlined text-[16px]">person_add</span>
                        <span>+ Akun Pengguna (UR14)</span>
                    </button>
                </div>
            </div>

            {{-- Tabel Verifikasi Akun Pending (UR15) --}}
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-container-high text-on-surface-variant font-label-sm text-label-sm uppercase">
                            <th class="py-space-sm px-space-lg">Nama Lengkap & Institusi</th>
                            <th class="py-space-sm px-space-md">Email Kampus</th>
                            <th class="py-space-sm px-space-md">Kategori Sivitas</th>
                            <th class="py-space-sm px-space-md">NIM / NIP / NIDN</th>
                            <th class="py-space-sm px-space-md">Tanggal Pengajuan</th>
                            <th class="py-space-sm px-space-md">Lampiran Bukti</th>
                            <th class="py-space-sm px-space-lg text-right">Tindakan Otorisasi (UR15)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container-high/40 font-body-sm text-body-sm text-on-surface">
                        {{-- Row 1: Mahasiswa --}}
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="py-space-md px-space-lg">
                                <div class="flex flex-col">
                                    <span class="font-label-lg text-label-lg text-primary font-bold">Anindya Putri Kirana</span>
                                    <span class="font-label-sm text-label-sm text-on-surface-variant">Fakultas Ilmu Komputer & TI</span>
                                </div>
                            </td>
                            <td class="py-space-md px-space-md font-data-mono text-data-mono text-on-surface">anindya.pk@ti.univ.ac.id</td>
                            <td class="py-space-md px-space-md">
                                <span class="px-2 py-0.5 rounded font-label-sm text-label-sm bg-surface-container-high text-primary font-semibold">Mahasiswa</span>
                            </td>
                            <td class="py-space-md px-space-md font-data-mono text-data-mono text-on-surface font-semibold">2108561044</td>
                            <td class="py-space-md px-space-md text-on-surface-variant font-data-mono text-data-mono text-[11px]">24/04/2024 09:15</td>
                            <td class="py-space-md px-space-md">
                                <span class="inline-flex items-center gap-1 px-space-sm py-0.5 rounded bg-surface-container text-primary font-label-sm text-label-sm hover:bg-surface-container-highest transition-colors cursor-pointer">
                                    <span class="material-symbols-outlined text-[14px]">attachment</span> KTM_2108561044.pdf
                                </span>
                            </td>
                            <td class="py-space-md px-space-lg text-right">
                                <div class="inline-flex items-center gap-space-xs">
                                    <!-- 
                                      ROUTE: POST /admin/users/{id}/approve
                                      FUNGSI: Mengaktifkan status akun pending hasil registrasi mandiri (UR15)
                                    -->
                                    <form action="{{ url('/admin/users/1/approve') }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-space-md py-1 rounded bg-secondary text-on-secondary font-label-sm text-label-sm font-semibold hover:bg-secondary/90 transition-colors shadow-sm flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[14px]">check</span> Verifikasi
                                        </button>
                                    </form>

                                    <!-- 
                                      ROUTE: POST /admin/users/{id}/reject
                                      FUNGSI: Menolak permohonan registrasi akun yang tidak valid
                                    -->
                                    <form action="{{ url('/admin/users/1/reject') }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-space-md py-1 rounded bg-surface-container text-error hover:bg-error-container hover:text-on-error-container font-label-sm text-label-sm font-semibold transition-colors flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[14px]">close</span> Tolak
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>

                        {{-- Row 2: Dosen --}}
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="py-space-md px-space-lg">
                                <div class="flex flex-col">
                                    <span class="font-label-lg text-label-lg text-primary font-bold">Dr. Bambang Sudrajat, M.T.</span>
                                    <span class="font-label-sm text-label-sm text-on-surface-variant">Fakultas Teknik Elektro</span>
                                </div>
                            </td>
                            <td class="py-space-md px-space-md font-data-mono text-data-mono text-on-surface">bambang.sudrajat@ee.univ.ac.id</td>
                            <td class="py-space-md px-space-md">
                                <span class="px-2 py-0.5 rounded font-label-sm text-label-sm bg-primary-container text-on-primary font-semibold">Dosen Tetap</span>
                            </td>
                            <td class="py-space-md px-space-md font-data-mono text-data-mono text-on-surface font-semibold">197502142003121001</td>
                            <td class="py-space-md px-space-md text-on-surface-variant font-data-mono text-data-mono text-[11px]">24/04/2024 08:30</td>
                            <td class="py-space-md px-space-md">
                                <span class="inline-flex items-center gap-1 px-space-sm py-0.5 rounded bg-surface-container text-primary font-label-sm text-label-sm hover:bg-surface-container-highest transition-colors cursor-pointer">
                                    <span class="material-symbols-outlined text-[14px]">attachment</span> SK_Dosen_Tetap.pdf
                                </span>
                            </td>
                            <td class="py-space-md px-space-lg text-right">
                                <div class="inline-flex items-center gap-space-xs">
                                    <form action="{{ url('/admin/users/2/approve') }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-space-md py-1 rounded bg-secondary text-on-secondary font-label-sm text-label-sm font-semibold hover:bg-secondary/90 transition-colors shadow-sm flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[14px]">check</span> Verifikasi
                                        </button>
                                    </form>
                                    <form action="{{ url('/admin/users/2/reject') }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-space-md py-1 rounded bg-surface-container text-error hover:bg-error-container hover:text-on-error-container font-label-sm text-label-sm font-semibold transition-colors flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[14px]">close</span> Tolak
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Modal Daftarkan Akun Petugas Langsung (UR13) --}}
        <div x-show="showAddPetugasModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-space-md bg-black/50 backdrop-blur-xs">
            <div @click.away="showAddPetugasModal = false" class="bg-surface-container-lowest rounded-2xl shadow-xl max-w-lg w-full p-space-xl border border-outline-variant flex flex-col gap-space-md">
                <div class="flex items-center justify-between pb-space-sm border-b border-outline-variant">
                    <h3 class="font-headline-sm text-headline-sm text-primary flex items-center gap-2">
                        <span class="material-symbols-outlined">badge</span>
                        Daftarkan Akun Petugas Sarpras (UR13)
                    </h3>
                    <button type="button" @click="showAddPetugasModal = false"><span class="material-symbols-outlined">close</span></button>
                </div>

                <!-- 
                  ROUTE: POST /admin/users/create-petugas
                  FUNGSI: Mendaftarkan akun petugas secara langsung oleh Admin (Petugas tidak melakukan registrasi mandiri)
                -->
                <form action="{{ url('/admin/users/create-petugas') }}" method="POST" class="flex flex-col gap-space-md">
                    @csrf
                    <div class="p-space-sm bg-surface-container-low rounded-lg text-[12px] text-on-surface-variant">
                        Sesuai spesifikasi UR13: Petugas sarpras mutlak didaftarkan langsung oleh Super Admin dan tidak membuka registrasi mandiri.
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-label-sm text-label-sm font-semibold" for="pet-name">Nama Lengkap Petugas</label>
                        <input type="text" id="pet-name" name="name" required placeholder="Contoh: Bambang Setyawan" class="w-full h-10 px-space-md bg-surface-container-low rounded-lg text-body-sm border border-outline-variant/60 focus:border-primary focus:outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-space-md">
                        <div class="flex flex-col gap-1">
                            <label class="font-label-sm text-label-sm font-semibold" for="pet-nip">NIP Petugas</label>
                            <input type="text" id="pet-nip" name="nip" required placeholder="197804122005011002" class="w-full h-10 px-space-md bg-surface-container-low rounded-lg text-body-sm border border-outline-variant/60 focus:border-primary focus:outline-none">
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="font-label-sm text-label-sm font-semibold" for="pet-zone">Zona Penugasan</label>
                            <select id="pet-zone" name="zone" class="w-full h-10 px-space-md bg-surface-container-low rounded-lg text-body-sm border border-outline-variant/60 focus:border-primary focus:outline-none">
                                <option value="A">Zona Gedung A (Rektorat & Hall)</option>
                                <option value="B">Zona Gedung B (Kuliah Terpadu)</option>
                                <option value="C">Zona Gedung C (Laboratorium)</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-label-sm text-label-sm font-semibold" for="pet-email">Email Resmi Sarpras</label>
                        <input type="email" id="pet-email" name="email" required placeholder="petugas.zona@univ.ac.id" class="w-full h-10 px-space-md bg-surface-container-low rounded-lg text-body-sm border border-outline-variant/60 focus:border-primary focus:outline-none">
                    </div>
                    <div class="flex justify-end gap-2 pt-2 border-t border-outline-variant/40">
                        <button type="button" @click="showAddPetugasModal = false" class="px-space-md py-1.5 rounded-lg bg-surface-container text-on-surface font-label-md">Batal</button>
                        <button type="submit" class="px-space-md py-1.5 rounded-lg bg-primary text-on-primary font-label-md font-semibold hover:bg-primary-container transition-colors shadow-sm">Simpan Akun Petugas</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal Daftarkan Akun Pengguna Langsung (UR14) --}}
        <div x-show="showAddPenggunaModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-space-md bg-black/50 backdrop-blur-xs">
            <div @click.away="showAddPenggunaModal = false" class="bg-surface-container-lowest rounded-2xl shadow-xl max-w-lg w-full p-space-xl border border-outline-variant flex flex-col gap-space-md">
                <div class="flex items-center justify-between pb-space-sm border-b border-outline-variant">
                    <h3 class="font-headline-sm text-headline-sm text-primary flex items-center gap-2">
                        <span class="material-symbols-outlined">person_add</span>
                        Daftarkan Akun Pengguna Langsung (UR14)
                    </h3>
                    <button type="button" @click="showAddPenggunaModal = false"><span class="material-symbols-outlined">close</span></button>
                </div>

                <!-- 
                  ROUTE: POST /admin/users/create-user
                  FUNGSI: Mendaftarkan akun mahasiswa/dosen secara langsung tanpa melalui alur verifikasi pending
                -->
                <form action="{{ url('/admin/users/create-user') }}" method="POST" class="flex flex-col gap-space-md">
                    @csrf
                    <div class="flex flex-col gap-1">
                        <label class="font-label-sm text-label-sm font-semibold" for="usr-name">Nama Lengkap Sivitas</label>
                        <input type="text" id="usr-name" name="name" required placeholder="Contoh: Dimas Pratama" class="w-full h-10 px-space-md bg-surface-container-low rounded-lg text-body-sm border border-outline-variant/60 focus:border-primary focus:outline-none">
                    </div>
                    <div class="grid grid-cols-2 gap-space-md">
                        <div class="flex flex-col gap-1">
                            <label class="font-label-sm text-label-sm font-semibold" for="usr-role">Tipe Pengguna</label>
                            <select id="usr-role" name="role_type" class="w-full h-10 px-space-md bg-surface-container-low rounded-lg text-body-sm border border-outline-variant/60 focus:border-primary focus:outline-none">
                                <option value="mahasiswa">Mahasiswa</option>
                                <option value="dosen">Dosen Tetap</option>
                                <option value="staf">Staf Akademik / BEM</option>
                            </select>
                        </div>
                        <div class="flex flex-col gap-1">
                            <label class="font-label-sm text-label-sm font-semibold" for="usr-id">NIM / NIP</label>
                            <input type="text" id="usr-id" name="identifier" required placeholder="2110512044" class="w-full h-10 px-space-md bg-surface-container-low rounded-lg text-body-sm border border-outline-variant/60 focus:border-primary focus:outline-none">
                        </div>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-label-sm text-label-sm font-semibold" for="usr-email">Email Kampus</label>
                        <input type="email" id="usr-email" name="email" required placeholder="pengguna@univ.ac.id" class="w-full h-10 px-space-md bg-surface-container-low rounded-lg text-body-sm border border-outline-variant/60 focus:border-primary focus:outline-none">
                    </div>
                    <div class="flex justify-end gap-2 pt-2 border-t border-outline-variant/40">
                        <button type="button" @click="showAddPenggunaModal = false" class="px-space-md py-1.5 rounded-lg bg-surface-container text-on-surface font-label-md">Batal</button>
                        <button type="submit" class="px-space-md py-1.5 rounded-lg bg-primary text-on-primary font-label-md font-semibold hover:bg-primary-container transition-colors shadow-sm">Simpan Akun Langsung</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
