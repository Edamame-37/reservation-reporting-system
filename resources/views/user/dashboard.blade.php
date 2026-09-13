{{-- 
  NAMA FILE      : dashboard.blade.php
  FUNGSIONALITAS : Halaman Antarmuka Dasbor Utama Mahasiswa / Dosen (Pengguna)
  DESKRIPSI      : Menampilkan ringkasan pinjaman fasilitas aktif, navigasi cepat pemesanan & pelaporan, serta ikhtisar kuota bulanan.
  CARA KERJA     : Menggunakan layout <x-app-layout active="dashboard">, menyajikan dasbor sentral untuk akses modul reservasi dan pelaporan mandiri.
--}}

<x-app-layout title="Dasbor Pengguna" active="dashboard">
    {{-- Banner Pemberitahuan Kebijakan Reservasi SK Rektor --}}
    <div class="bg-surface-container-high rounded-xl p-space-md mb-space-xl flex flex-col md:flex-row items-start md:items-center justify-between gap-space-md shadow-sm">
        <div class="flex items-center gap-space-md">
            <div class="w-10 h-10 rounded-lg bg-primary text-on-primary flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[24px]">info</span>
            </div>
            <div class="flex flex-col">
                <span class="font-label-lg text-label-lg text-primary font-semibold">Prosedur Pengajuan Sesi Kuliah & Acara Kemahasiswaan (SK-Rektor No. 428/2024)</span>
                <span class="font-body-sm text-body-sm text-on-surface-variant">Reservasi wajib diajukan minimal 2x24 jam sebelum kegiatan. Pembatalan mandiri dibatasi maksimal H-1 sebelum jadwal.</span>
            </div>
        </div>
        <span class="font-data-mono text-data-mono bg-surface-container-lowest px-space-md py-1 rounded-lg text-primary font-bold shadow-sm whitespace-nowrap">
            KUOTA BULAN INI: 4/5 TERPAKAI
        </span>
    </div>

    {{-- Quick Action Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-space-md mb-space-xl">
        <a href="{{ url('/user/reservation-form') }}" class="bg-surface-container-lowest p-space-lg rounded-xl shadow-sm hover:shadow-md hover:border-primary border border-outline-variant/40 transition-all flex items-center gap-space-md group">
            <div class="w-12 h-12 rounded-lg bg-primary-container text-on-primary flex items-center justify-center group-hover:scale-105 transition-transform">
                <span class="material-symbols-outlined text-[24px]">add_circle</span>
            </div>
            <div>
                <div class="font-label-lg text-label-lg text-primary font-bold">Ajukan Reservasi</div>
                <div class="font-body-sm text-body-sm text-on-surface-variant text-[12px]">Pilih slot 30 menit</div>
            </div>
        </a>

        <a href="{{ url('/user/reservation-history') }}" class="bg-surface-container-lowest p-space-lg rounded-xl shadow-sm hover:shadow-md hover:border-primary border border-outline-variant/40 transition-all flex items-center gap-space-md group">
            <div class="w-12 h-12 rounded-lg bg-surface-container text-primary flex items-center justify-center group-hover:scale-105 transition-transform">
                <span class="material-symbols-outlined text-[24px]">history</span>
            </div>
            <div>
                <div class="font-label-lg text-label-lg text-primary font-bold">Riwayat Reservasi</div>
                <div class="font-body-sm text-body-sm text-on-surface-variant text-[12px]">Status tiket & batal H-1</div>
            </div>
        </a>

        <a href="{{ url('/user/report-form') }}" class="bg-surface-container-lowest p-space-lg rounded-xl shadow-sm hover:shadow-md hover:border-primary border border-outline-variant/40 transition-all flex items-center gap-space-md group">
            <div class="w-12 h-12 rounded-lg bg-error-container text-on-error-container flex items-center justify-center group-hover:scale-105 transition-transform">
                <span class="material-symbols-outlined text-[24px]">build</span>
            </div>
            <div>
                <div class="font-label-lg text-label-lg text-primary font-bold">Lapor Kerusakan</div>
                <div class="font-body-sm text-body-sm text-on-surface-variant text-[12px]">Unggah foto bukti</div>
            </div>
        </a>

        <a href="{{ url('/user/report-history') }}" class="bg-surface-container-lowest p-space-lg rounded-xl shadow-sm hover:shadow-md hover:border-primary border border-outline-variant/40 transition-all flex items-center gap-space-md group">
            <div class="w-12 h-12 rounded-lg bg-tertiary-fixed text-on-tertiary-fixed flex items-center justify-center group-hover:scale-105 transition-transform">
                <span class="material-symbols-outlined text-[24px]">checklist</span>
            </div>
            <div>
                <div class="font-label-lg text-label-lg text-primary font-bold">Status Laporan</div>
                <div class="font-body-sm text-body-sm text-on-surface-variant text-[12px]">Pantau tindak lanjut</div>
            </div>
        </a>
    </div>

    {{-- Ringkasan Pinjaman Fasilitas Aktif & Permohonan Terkini --}}
    <section class="bg-surface-container-lowest rounded-xl shadow-sm p-space-lg mb-space-xl">
        <div class="flex items-center justify-between pb-space-sm mb-space-md border-b border-outline-variant">
            <div class="flex items-center gap-space-xs">
                <span class="material-symbols-outlined text-[20px] text-primary">calendar_month</span>
                <h2 class="font-headline-sm text-headline-sm text-primary">Reservasi Terkini Saya</h2>
            </div>
            <a href="{{ url('/user/reservation-history') }}" class="font-label-sm text-label-sm text-primary hover:underline flex items-center gap-1">
                Lihat Semua <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container-high text-on-surface-variant font-label-sm text-label-sm uppercase">
                        <th class="py-space-sm px-space-md">Tiket</th>
                        <th class="py-space-sm px-space-md">Fasilitas</th>
                        <th class="py-space-sm px-space-md">Jadwal & Waktu</th>
                        <th class="py-space-sm px-space-md">Tujuan</th>
                        <th class="py-space-sm px-space-md">Status</th>
                        <th class="py-space-sm px-space-md text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container-high">
                    <tr class="hover:bg-surface-container-low transition-colors">
                        <td class="py-space-md px-space-md font-data-mono text-data-mono font-bold text-primary">TKT-20240424-001</td>
                        <td class="py-space-md px-space-md font-label-md font-semibold text-on-surface">Auditorium B.J. Habibie (Gedung A)</td>
                        <td class="py-space-md px-space-md">
                            <div class="font-label-md text-on-surface">24 Apr 2024</div>
                            <div class="font-data-mono text-data-mono text-secondary font-semibold">09:00 - 12:00 WIB</div>
                        </td>
                        <td class="py-space-md px-space-md font-body-sm text-on-surface-variant truncate max-w-xs">Seminar Cloud Computing HMIF</td>
                        <td class="py-space-md px-space-md">
                            <x-cava.status-badge status="Menunggu Konfirmasi" />
                        </td>
                        <td class="py-space-md px-space-md text-right">
                            <a href="{{ url('/user/reservation-history') }}" class="px-space-md py-1 rounded bg-surface-container text-primary font-label-sm hover:bg-surface-container-highest transition-colors">
                                Detail
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</x-app-layout>
