{{-- 
  NAMA FILE      : dashboard.blade.php
  FUNGSIONALITAS : Halaman Dasbor Utama Super Admin CAVA
  DESKRIPSI      : Menampilkan metrik tata kelola kampus (Verifikasi User Pending, Total Fasilitas, Okupansi Rata-rata, Tiket Kerusakan), 3 antrean verifikasi akun preview, 3 fasilitas preview, dan ringkasan okupansi dengan tombol 'Lihat Selengkapnya'.
  CARA KERJA     : Memanfaatkan layout <x-admin-layout active="dashboard">, menyajikan monitoring sentral administrasi sistem secara minimalis dan profesional.
--}}

<x-admin-layout title="Dasbor Super Admin" active="dashboard">
    <!-- 
      ELEMEN       : 4 KPI Stat Cards Eksekutif
      KEGUNAAN     : Menyajikan gambaran umum tata kelola ruang dan pengguna kampus.
    -->
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <x-cava.stat-card 
            title="Verifikasi Akun Pending"
            value="5"
            subtitle="Pendaftar Baru"
            tagText="+2 Hari Ini"
            footerText="Target SLA < 24 Jam"
            icon="how_to_reg"
            variant="tertiary"
        />

        <x-cava.stat-card 
            title="Master Fasilitas"
            value="24"
            subtitle="Ruang Terdaftar"
            tagText="22 Aktif • 2 Perbaikan"
            footerText="Kapasitas 1,850 Kursi"
            icon="domain"
            variant="primary"
        />

        <x-cava.stat-card 
            title="Tingkat Okupansi"
            value="78.4%"
            subtitle="Rata-rata Semester"
            tagText="+12.5% vs Semester Lalu"
            footerText="Status: Sangat Optimal"
            icon="trending_up"
            variant="secondary"
        />

        <x-cava.stat-card 
            title="Tiket Kerusakan"
            value="5"
            subtitle="Perlu Pemantauan"
            tagText="2 Prioritas Mendesak"
            footerText="SLA Terselesaikan 94%"
            icon="build"
            variant="error"
        />
    </section>

    <!-- 
      ELEMEN       : Section Verifikasi Akun Sivitas (Batas 3 Data Preview + Tombol Lihat Semua)
      KEGUNAAN     : Memungkinkan Super Admin mengevaluasi pendaftar mandiri (UR15) tanpa membuka tabel panjang di dasbor.
    -->
    <section class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 mb-4 border-b border-slate-100 gap-2">
            <div>
                <h3 class="text-base font-bold text-slate-900">Antrean Verifikasi Akun Sivitas Baru</h3>
                <p class="text-xs text-slate-500 mt-0.5">Menampilkan 3 dari total 5 calon pengguna yang menunggu otorisasi akun (UR15).</p>
            </div>
            <a href="{{ url('/admin/user-management') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-blue-950 hover:text-blue-700 transition">
                <span>Kelola Seluruh Akun Sivitas (48 Pengguna)</span>
                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                        <th class="py-3 px-4">Nama Lengkap & Identitas</th>
                        <th class="py-3 px-4">Peran Dimohon</th>
                        <th class="py-3 px-4">Program Studi / Lembaga</th>
                        <th class="py-3 px-4">Status Akun</th>
                        <th class="py-3 px-4 text-right">Otorisasi Cepat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900">Muhammad Rizky Pratama</div>
                            <div class="text-[11px] text-slate-500 font-mono">NIM: 2310512011 • m.rizky@student.ac.id</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">Mahasiswa</span>
                        </td>
                        <td class="py-3.5 px-4 text-slate-700 font-medium">Informatika (Fakultas Ilmu Komputer)</td>
                        <td class="py-3.5 px-4">
                            <x-cava.status-badge status="pending" label="Pending Verifikasi" />
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <button type="button" @click="alert('Akun Muhammad Rizky berhasil diverifikasi dan diaktifkan.')" class="px-3 py-1 rounded-xl bg-slate-900 text-white font-semibold hover:bg-slate-800 transition shadow-xs">
                                    Verifikasi
                                </button>
                                <button type="button" @click="alert('Akun ditolak.')" class="px-2.5 py-1 rounded-xl border border-slate-200 text-rose-700 font-semibold hover:bg-rose-50 transition">
                                    Tolak
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900">Dr. Wahyu Hidayat, M.Kom.</div>
                            <div class="text-[11px] text-slate-500 font-mono">NIDN: 0412088501 • w.hidayat@univ.ac.id</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-900 border border-blue-200/60">Dosen Tetap</span>
                        </td>
                        <td class="py-3.5 px-4 text-slate-700 font-medium">Sistem Informasi (Fakultas Sains & TIK)</td>
                        <td class="py-3.5 px-4">
                            <x-cava.status-badge status="pending" label="Pending Verifikasi" />
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <button type="button" @click="alert('Akun Dr. Wahyu berhasil diverifikasi.')" class="px-3 py-1 rounded-xl bg-slate-900 text-white font-semibold hover:bg-slate-800 transition shadow-xs">
                                    Verifikasi
                                </button>
                                <button type="button" @click="alert('Akun ditolak.')" class="px-2.5 py-1 rounded-xl border border-slate-200 text-rose-700 font-semibold hover:bg-rose-50 transition">
                                    Tolak
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900">Nabila Putri Kirana</div>
                            <div class="text-[11px] text-slate-500 font-mono">NIM: 2210811099 • nabila.p@student.ac.id</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">Staf Ormawa</span>
                        </td>
                        <td class="py-3.5 px-4 text-slate-700 font-medium">Sekretaris BEM Universitas</td>
                        <td class="py-3.5 px-4">
                            <x-cava.status-badge status="pending" label="Pending Verifikasi" />
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <button type="button" @click="alert('Akun Nabila berhasil diverifikasi.')" class="px-3 py-1 rounded-xl bg-slate-900 text-white font-semibold hover:bg-slate-800 transition shadow-xs">
                                    Verifikasi
                                </button>
                                <button type="button" @click="alert('Akun ditolak.')" class="px-2.5 py-1 rounded-xl border border-slate-200 text-rose-700 font-semibold hover:bg-rose-50 transition">
                                    Tolak
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- 
      ELEMEN       : Section Master Fasilitas & Utilisasi (Batas 3 Data Preview + Tombol Lihat Semua)
      KEGUNAAN     : Memantau master data ruang dan membuka analitik okupansi.
    -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- 3 Fasilitas Preview (7 Kolom) --}}
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Ringkasan Master Fasilitas</h3>
                        <p class="text-xs text-slate-500 mt-0.5">3 dari 24 ruang terdaftar dalam inventaris universitas.</p>
                    </div>
                    <a href="{{ url('/admin/facility-master') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-blue-950 hover:text-blue-700 transition">
                        <span>Kelola Master (24 Ruang)</span>
                        <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                    </a>
                </div>

                <div class="space-y-3">
                    <div class="p-3 rounded-xl border border-slate-200/70 bg-slate-50/50 flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-900 text-sm">Auditorium B.J. Habibie</span>
                                <span class="text-[10px] font-mono font-bold bg-slate-200 px-1.5 py-0.5 rounded text-slate-700">AUD-H01</span>
                            </div>
                            <span class="text-xs text-slate-500">Gedung Rektorat • Kapasitas: 450 Kursi</span>
                        </div>
                        <x-cava.status-badge status="active" label="Aktif" />
                    </div>

                    <div class="p-3 rounded-xl border border-slate-200/70 bg-slate-50/50 flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-900 text-sm">Lab Komputasi Cloud & Jaringan</span>
                                <span class="text-[10px] font-mono font-bold bg-slate-200 px-1.5 py-0.5 rounded text-slate-700">LAB-C204</span>
                            </div>
                            <span class="text-xs text-slate-500">Gedung Lab Terpadu C • Kapasitas: 45 PC</span>
                        </div>
                        <x-cava.status-badge status="active" label="Aktif" />
                    </div>

                    <div class="p-3 rounded-xl border border-slate-200/70 bg-slate-50/50 flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-900 text-sm">Ruang Rapat Senat Akademik</span>
                                <span class="text-[10px] font-mono font-bold bg-slate-200 px-1.5 py-0.5 rounded text-slate-700">RPT-SENAT</span>
                            </div>
                            <span class="text-xs text-slate-500">Gedung Rektorat • Kapasitas: 35 Kursi</span>
                        </div>
                        <x-cava.status-badge status="locked" label="Perbaikan" />
                    </div>
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 text-center">
                <a href="{{ url('/admin/facility-master') }}" class="text-xs font-semibold text-blue-950 hover:underline">
                    Buka Master Data & Tambah Ruang Baru (UR16) →
                </a>
            </div>
        </div>

        {{-- Mini Analitik Okupansi (5 Kolom) --}}
        <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900">Analitik Utilisasi Kampus</h3>
                    <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">Optimal 78%</span>
                </div>

                <div class="space-y-3.5 text-xs text-slate-600 mb-6">
                    <div>
                        <div class="flex justify-between mb-1">
                            <span>Gedung Kuliah Terpadu A</span>
                            <span class="font-bold text-slate-900">88% Okupansi</span>
                        </div>
                        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                            <div class="bg-slate-900 h-full rounded-full" style="width: 88%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between mb-1">
                            <span>Gedung Lab Terpadu C</span>
                            <span class="font-bold text-slate-900">82% Okupansi</span>
                        </div>
                        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                            <div class="bg-slate-900 h-full rounded-full" style="width: 82%"></div>
                        </div>
                    </div>

                    <div>
                        <div class="flex justify-between mb-1">
                            <span>Auditorium & Gedung Rektorat</span>
                            <span class="font-bold text-slate-900">65% Okupansi</span>
                        </div>
                        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                            <div class="bg-slate-900 h-full rounded-full" style="width: 65%"></div>
                        </div>
                    </div>
                </div>
            </div>

            <a href="{{ url('/admin/export-report') }}" class="w-full py-2.5 px-4 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition shadow-xs flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-[16px]">file_download</span>
                <span>Buka Laporan Statuter & Ekspor (UR17)</span>
            </a>
        </div>
    </div>
</x-admin-layout>
