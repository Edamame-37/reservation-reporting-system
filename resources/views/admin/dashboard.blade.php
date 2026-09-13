{{-- 
  NAMA FILE      : dashboard.blade.php
  FUNGSIONALITAS : Halaman Antarmuka Dasbor Utama Super Admin CAVA
  DESKRIPSI      : Menampilkan metrik tata kelola kampus (Verifikasi User Pending, Total Fasilitas, Okupansi Rata-rata, Tiket Kerusakan) dan jalan pintas modul admin.
  CARA KERJA     : Memanfaatkan layout <x-admin-layout active="dashboard">, menyajikan monitoring sentral administrasi sistem.
--}}

<x-admin-layout title="Dasbor Super Admin" active="dashboard">
    <!-- 
      ELEMEN       : Overview Statistik Tata Kelola Admin
      KEGUNAAN     : Menyajikan indikator operasional kampus tingkat rektorat & biro sarpras secara menyeluruh.
      CARA KERJA   : Merender 4 komponen stat-card CAVA dengan indikator metrik real-time.
    -->
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-space-lg">
        {{-- Card 1: Verifikasi User Pending --}}
        <x-cava.stat-card 
            title="Antrean Verifikasi User"
            code="UR15"
            value="5"
            valueLabel="Akun Pending"
            footerBadge="+2 Pengajuan Baru"
            footerText="SLA Validasi < 24 Jam"
            icon="how_to_reg"
            variant="error"
        />

        {{-- Card 2: Total Fasilitas Aktif --}}
        <x-cava.stat-card 
            title="Master Fasilitas"
            code="UR16"
            value="6"
            valueLabel="Ruangan Terdaftar"
            footerBadge="5 Aktif • 1 Maintenance"
            footerText="Kapasitas 1,475 Orang"
            icon="domain"
            variant="primary"
        />

        {{-- Card 3: Rata-rata Okupansi --}}
        <x-cava.stat-card 
            title="Tingkat Okupansi"
            code="UR17"
            value="78.4%"
            valueLabel="Penggunaan Mingguan"
            footerBadge="+12.5% vs Bulan Lalu"
            footerText="Optimal Utilized"
            icon="trending_up"
            variant="secondary"
        />

        {{-- Card 4: Insiden Kerusakan --}}
        <x-cava.stat-card 
            title="Tiket Kerusakan"
            code="UR11"
            value="3"
            valueLabel="Tiket Terbuka"
            footerBadge="1 Resolusi Selesai"
            footerText="SLA Perbaikan 94%"
            icon="build"
            variant="tertiary"
        />
    </section>

    {{-- Quick Action Admin Links --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-space-md">
        <a href="{{ url('/admin/user-management') }}" class="bg-surface-container-lowest p-space-lg rounded-xl shadow-sm border border-outline-variant/40 hover:border-primary hover:shadow-md transition-all flex items-center gap-space-md group">
            <div class="w-12 h-12 rounded-lg bg-primary text-on-primary flex items-center justify-center group-hover:scale-105 transition-transform">
                <span class="material-symbols-outlined text-[24px]">manage_accounts</span>
            </div>
            <div>
                <div class="font-headline-sm text-headline-sm text-primary">Manajemen User (UR15)</div>
                <div class="font-body-sm text-body-sm text-on-surface-variant">5 akun menunggu otorisasi</div>
            </div>
        </a>

        <a href="{{ url('/admin/facility-master') }}" class="bg-surface-container-lowest p-space-lg rounded-xl shadow-sm border border-outline-variant/40 hover:border-primary hover:shadow-md transition-all flex items-center gap-space-md group">
            <div class="w-12 h-12 rounded-lg bg-primary-container text-on-primary flex items-center justify-center group-hover:scale-105 transition-transform">
                <span class="material-symbols-outlined text-[24px]">add_business</span>
            </div>
            <div>
                <div class="font-headline-sm text-headline-sm text-primary">Master Fasilitas (UR16)</div>
                <div class="font-body-sm text-body-sm text-on-surface-variant">CRUD data inventaris ruang</div>
            </div>
        </a>

        <a href="{{ url('/admin/export-report') }}" class="bg-surface-container-lowest p-space-lg rounded-xl shadow-sm border border-outline-variant/40 hover:border-primary hover:shadow-md transition-all flex items-center gap-space-md group">
            <div class="w-12 h-12 rounded-lg bg-secondary-container text-on-secondary-container flex items-center justify-center group-hover:scale-105 transition-transform">
                <span class="material-symbols-outlined text-[24px]">file_download</span>
            </div>
            <div>
                <div class="font-headline-sm text-headline-sm text-primary">Ekspor Laporan (UR17)</div>
                <div class="font-body-sm text-body-sm text-on-surface-variant">Cetak CSV, Excel, dan PDF</div>
            </div>
        </a>
    </div>
</x-admin-layout>
