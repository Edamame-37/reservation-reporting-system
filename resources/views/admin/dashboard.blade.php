{{-- 
  NAMA FILE      : dashboard.blade.php
  FUNGSIONALITAS : Halaman Dasbor Utama Super Admin CAVA
  DESKRIPSI      : Menampilkan metrik tata kelola kampus (Verifikasi User Pending, Total Fasilitas, Okupansi Rata-rata, Tiket Kerusakan), antrean verifikasi akun sivitas, ringkasan master fasilitas, dan utilisasi gedung.
  CARA KERJA     : Memanfaatkan layout <x-admin-layout active="dashboard">, menyajikan monitoring sentral administrasi sistem secara minimalis dan profesional dari AdminDashboardController.
--}}

<x-admin-layout title="Dasbor Super Admin" active="dashboard">
    {{-- Umpan Balik Flash Message & Notifikasi Sistem --}}
    @if(session('success'))
    <!-- 
      ELEMEN       : Banner Notifikasi Sukses
      KEGUNAAN     : Memberikan umpan balik visual saat aksi otorisasi berhasil dieksekusi.
      CARA KERJA   : Muncul secara kondisional jika session('success') memiliki pesan.
    -->
    <div class="p-4 mb-2 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-semibold flex items-center gap-2 shadow-xs">
        <span class="material-symbols-outlined text-[18px]">check_circle</span>
        <span>{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error') || (isset($errors) && $errors->any()))
    <!-- 
      ELEMEN       : Banner Peringatan Kesalahan / Validasi
      KEGUNAAN     : Memberitahukan adanya kendala pada saat eksekusi aksi pengguna.
      CARA KERJA   : Muncul jika terdapat error pada session atau pesan validasi $errors.
    -->
    <div class="p-4 mb-2 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs font-semibold flex items-center gap-2 shadow-xs">
        <span class="material-symbols-outlined text-[18px]">error</span>
        <span>{{ session('error') ?? (isset($errors) ? $errors->first() : '') }}</span>
    </div>
    @endif

    <!-- 
      ELEMEN       : 4 KPI Stat Cards Eksekutif
      KEGUNAAN     : Menyajikan gambaran umum tata kelola ruang dan pengguna kampus secara real-time.
      CARA KERJA   : Merender komponen kartu metrik CAVA berdasarkan variabel ringkasan statistik dari AdminDashboardController.
    -->
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <x-cava.stat-card 
            title="Verifikasi Akun Pending"
            :value="$pendingUsersCount"
            subtitle="Pendaftar Baru"
            :tagText="'+' . $pendingUsersToday . ' Hari Ini'"
            footerText="Target SLA < 24 Jam"
            icon="how_to_reg"
            variant="tertiary"
        />

        <x-cava.stat-card 
            title="Master Fasilitas"
            :value="$totalFacilities"
            subtitle="Ruang Terdaftar"
            :tagText="$activeFacilities . ' Aktif • ' . $maintenanceFacilities . ' Perbaikan'"
            :footerText="'Kapasitas ' . number_format($totalCapacitySeats) . ' Kursi'"
            icon="domain"
            variant="primary"
        />

        <x-cava.stat-card 
            title="Tingkat Okupansi"
            :value="$occupancyRate . '%'"
            subtitle="Rata-rata Bulan Ini"
            :tagText="$occupancyRate >= 70 ? 'Sangat Optimal' : ($occupancyRate >= 40 ? 'Cukup Optimal' : 'Perlu Peningkatan')"
            :footerText="'Status: ' . ($occupancyRate >= 70 ? 'Sangat Optimal' : 'Optimal')"
            icon="trending_up"
            variant="secondary"
        />

        <x-cava.stat-card 
            title="Tiket Kerusakan"
            :value="$activeDamageReports"
            subtitle="Perlu Pemantauan"
            :tagText="$urgentDamageCount . ' Prioritas Baru'"
            :footerText="'SLA Terselesaikan ' . $slaResolutionPercent . '%'"
            icon="build"
            variant="error"
        />
    </section>

    <!-- 
      ELEMEN       : Section Antrean Verifikasi Akun Sivitas (Batas 3 Data Preview + Tombol Lihat Semua)
      KEGUNAAN     : Memungkinkan Super Admin mengevaluasi pendaftar mandiri (UR15) tanpa membuka tabel panjang di dasbor.
      CARA KERJA   : Melakukan perulangan data pada $pendingUsersPreview dengan tombol otorisasi verifikasi cepat atau navigasi ke manajemen akun.
    -->
    <section class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 mb-4 border-b border-slate-100 gap-2">
            <div>
                <h3 class="text-base font-bold text-slate-900">Antrean Verifikasi Akun Sivitas Baru</h3>
                <p class="text-xs text-slate-500 mt-0.5">Menampilkan {{ $pendingUsersPreview->count() }} dari total {{ $pendingUsersCount }} calon pengguna yang menunggu otorisasi akun (UR15).</p>
            </div>
            <!-- 
              ROUTE: GET /admin/user-management
              FUNGSI: Mengarahkan ke halaman direktori lengkap manajemen verifikasi seluruh akun sivitas
            -->
            <a href="{{ route('admin.user-management') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-blue-950 hover:text-blue-700 transition">
                <span>Kelola Seluruh Akun Sivitas ({{ $totalUsersCount }} Pengguna)</span>
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
                    @forelse($pendingUsersPreview as $user)
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900">{{ $user->name }}</div>
                            <div class="text-[11px] text-slate-500 font-mono">ID: {{ $user->identity_number ?? '-' }} • {{ $user->email }}</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700">{{ ucfirst($user->role ?? 'Sivitas') }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-slate-700 font-medium">{{ $user->department ?? '-' }}</td>
                        <td class="py-3.5 px-4">
                            <x-cava.status-badge status="pending" label="Pending Verifikasi" />
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <!-- 
                                  ROUTE: POST /admin/users/{{ $user->id }}/verify
                                  FUNGSI: Memverifikasi dan mengaktifkan akun calon pengguna secara langsung
                                -->
                                <form action="{{ route('admin.users.verify', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Verifikasi dan aktifkan akun {{ addslashes($user->name) }}?')">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 rounded-xl bg-slate-900 text-white font-semibold hover:bg-slate-800 transition shadow-xs">
                                        Verifikasi
                                    </button>
                                </form>

                                <!-- 
                                  ROUTE: GET /admin/user-management?search={{ urlencode($user->email) }}
                                  FUNGSI: Membuka konsol manajemen pengguna untuk proses penolakan berstempel alasan resmi
                                -->
                                <a href="{{ route('admin.user-management', ['search' => $user->email]) }}" class="px-2.5 py-1 rounded-xl border border-slate-200 text-rose-700 font-semibold hover:bg-rose-50 transition inline-block">
                                    Tolak
                                </a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-slate-500">
                            <div class="flex flex-col items-center justify-center gap-1.5">
                                <span class="material-symbols-outlined text-slate-400 text-[28px]">check_circle</span>
                                <p class="font-medium text-slate-600 text-xs">Tidak ada antrean verifikasi akun baru saat ini.</p>
                                <p class="text-[11px] text-slate-400">Seluruh permohonan pendaftaran sivitas telah selesai dievaluasi.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <!-- 
      ELEMEN       : Section Master Fasilitas & Utilisasi (Batas 3 Data Preview + Tombol Lihat Semua)
      KEGUNAAN     : Memantau master data ruang dan membuka analitik okupansi per gedung.
      CARA KERJA   : Merender cuplikan inventaris fasilitas dan grafik persentase okupansi per gedung dari $buildingUtilization.
    -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        {{-- 3 Fasilitas Preview (7 Kolom) --}}
        <div class="lg:col-span-7 bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                    <div>
                        <h3 class="text-base font-bold text-slate-900">Ringkasan Master Fasilitas</h3>
                        <p class="text-xs text-slate-500 mt-0.5">{{ $facilitiesPreview->count() }} dari {{ $totalFacilities }} ruang terdaftar dalam inventaris universitas.</p>
                    </div>
                    <!-- 
                      ROUTE: GET /admin/facility-master
                      FUNGSI: Mengarahkan ke halaman pengelolaan seluruh master data fasilitas kampus
                    -->
                    <a href="{{ route('admin.facility-master') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-blue-950 hover:text-blue-700 transition">
                        <span>Kelola Master ({{ $totalFacilities }} Ruang)</span>
                        <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse($facilitiesPreview as $facility)
                    <div class="p-3 rounded-xl border border-slate-200/70 bg-slate-50/50 flex items-center justify-between">
                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-bold text-slate-900 text-sm">{{ $facility->name }}</span>
                                <span class="text-[10px] font-mono font-bold bg-slate-200 px-1.5 py-0.5 rounded text-slate-700">{{ $facility->code }}</span>
                            </div>
                            <span class="text-xs text-slate-500">{{ $facility->building }} • Kapasitas: {{ $facility->capacity }} {{ strtolower($facility->category ?? '') == 'laboratorium' ? 'PC/Meja' : 'Kursi' }}</span>
                        </div>
                        <x-cava.status-badge :status="$facility->status == 'aktif' ? 'active' : 'locked'" :label="ucfirst($facility->status)" />
                    </div>
                    @empty
                    <div class="py-6 text-center text-slate-500">
                        <p class="text-xs font-medium">Belum ada data fasilitas terdaftar dalam sistem.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 text-center">
                <!-- 
                  ROUTE: GET /admin/facility-master
                  FUNGSI: Pintasan ke formulir penambahan ruang baru pada katalog master fasilitas
                -->
                <a href="{{ route('admin.facility-master') }}" class="text-xs font-semibold text-blue-950 hover:underline">
                    Buka Master Data & Tambah Ruang Baru (UR16) →
                </a>
            </div>
        </div>

        {{-- Mini Analitik Okupansi (5 Kolom) --}}
        <div class="lg:col-span-5 bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                    <h3 class="text-base font-bold text-slate-900">Analitik Utilisasi Kampus</h3>
                    <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">Optimal {{ $occupancyRate }}%</span>
                </div>

                <div class="space-y-3.5 text-xs text-slate-600 mb-6">
                    @forelse($buildingUtilization as $b)
                    <div>
                        <div class="flex justify-between mb-1">
                            <span>{{ $b['building'] }}</span>
                            <span class="font-bold text-slate-900">{{ $b['rate'] }}% Okupansi</span>
                        </div>
                        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                            <div class="bg-slate-900 h-full rounded-full transition-all duration-500" style="width: {{ min(100, max(0, $b['rate'])) }}%"></div>
                        </div>
                    </div>
                    @empty
                    <div class="py-4 text-center text-slate-500">
                        <p class="text-xs">Belum ada rekaman peminjaman ruang pada periode berjalan.</p>
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- 
              ROUTE: GET /admin/export-report
              FUNGSI: Mengarahkan ke halaman rekapitulasi analitik dan unduh laporan resmi (ADM-04 / UR17)
            -->
            <a href="{{ route('admin.export-report') }}" class="w-full py-2.5 px-4 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition shadow-xs flex items-center justify-center gap-2">
                <span class="material-symbols-outlined text-[16px]">file_download</span>
                <span>Buka Laporan Statuter & Ekspor (UR17)</span>
            </a>
        </div>
    </div>
</x-admin-layout>
