{{-- 
  NAMA FILE      : export-report.blade.php
  FUNGSIONALITAS : Halaman Antarmuka Rekapitulasi, Analitik, dan Ekspor Laporan (Super Admin)
  DESKRIPSI      : Menampilkan visualisasi data rekap okupansi fasilitas kampus, frekuensi kerusakan aset, dan tombol pencetakan dokumen resmi ke format CSV/Excel dan PDF (UR17).
  CARA KERJA     : Menggunakan layout <x-admin-layout active="export-report">, memicu download file CSV/Excel atau PDF dari Backend controller.
--}}

<x-admin-layout title="Rekapitulasi & Ekspor Laporan" active="export-report">
    <!-- 
      ELEMEN       : Section Rekapitulasi & Analitik Pelaporan Sistem (UR17)
      KEGUNAAN     : Menyediakan data agregasi berkala bagi pimpinan universitas dan tombol cetak laporan resmi.
      CARA KERJA   : Merender tabel rekap okupansi fasilitas, distribusi jenis kerusakan, serta tombol trigger unduh dokumen (CSV/PDF).
    -->
    <div class="flex flex-col gap-space-xl">
        {{-- Section Header & Filter Periode --}}
        <section class="bg-surface-container-lowest rounded-xl shadow-sm p-space-lg border border-outline-variant/50">
            <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-space-md pb-space-md border-b border-outline-variant mb-space-md">
                <div class="flex items-center gap-space-md">
                    <div class="w-10 h-10 rounded-lg bg-secondary-container text-on-secondary-container flex items-center justify-center shadow-sm">
                        <span class="material-symbols-outlined text-[22px]">analytics</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-space-xs">
                            <span class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant font-data-mono text-[11px]">UR17 • REPORTING & ANALYTICS</span>
                            <span class="px-2 py-0.5 rounded font-label-sm text-label-sm bg-secondary-container text-on-secondary-container font-semibold">Statuter Ready</span>
                        </div>
                        <h2 class="font-headline-md text-headline-md text-primary">Rekapitulasi Okupansi Fasilitas & Insiden Kerusakan</h2>
                    </div>
                </div>

                {{-- Action Buttons Ekspor --}}
                <div class="flex items-center gap-space-sm flex-wrap">
                    <!-- 
                      ROUTE: GET /admin/reports/export-excel
                      FUNGSI: Mengunduh data rekapitulasi okupansi dan perbaikan dalam format CSV/Excel (Laravel Excel)
                    -->
                    <a href="{{ url('/admin/reports/export-excel') }}" class="h-10 px-space-lg rounded-lg bg-surface-container text-primary font-label-md text-label-md font-semibold hover:bg-surface-container-highest transition-colors flex items-center gap-1 shadow-sm border border-outline-variant/50">
                        <span class="material-symbols-outlined text-[18px]">table_view</span>
                        <span>Ekspor Excel / CSV</span>
                    </a>

                    <!-- 
                      ROUTE: GET /admin/reports/export-pdf
                      FUNGSI: Mencetak dokumen laporan statuter resmi dalam format PDF (DomPDF)
                    -->
                    <a href="{{ url('/admin/reports/export-pdf') }}" class="h-10 px-space-lg rounded-lg bg-primary text-on-primary font-label-md text-label-md font-semibold hover:bg-primary-container transition-colors flex items-center gap-1 shadow-sm">
                        <span class="material-symbols-outlined text-[18px]">picture_as_pdf</span>
                        <span>Cetak Dokumen PDF Resmi</span>
                    </a>
                </div>
            </div>

            {{-- Filter Rentang Periode --}}
            <form action="{{ url('/admin/export-report') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-space-md items-end">
                <div>
                    <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant" for="rep-start">Periode Mulai</label>
                    <input type="date" id="rep-start" name="start_date" value="{{ date('Y-m-01') }}" class="w-full h-10 px-space-md rounded-lg bg-surface-container-low text-on-surface font-body-sm border border-outline-variant/60 focus:border-primary focus:outline-none">
                </div>
                <div>
                    <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant" for="rep-end">Periode Selesai</label>
                    <input type="date" id="rep-end" name="end_date" value="{{ date('Y-m-d') }}" class="w-full h-10 px-space-md rounded-lg bg-surface-container-low text-on-surface font-body-sm border border-outline-variant/60 focus:border-primary focus:outline-none">
                </div>
                <div>
                    <button type="submit" class="w-full h-10 inline-flex items-center justify-center gap-space-xs rounded-lg bg-primary text-on-primary font-label-md text-label-md hover:bg-primary-container transition-colors shadow-sm">
                        <span class="material-symbols-outlined text-[18px]">refresh</span>
                        <span>Segarkan Data Analitik</span>
                    </button>
                </div>
            </form>
        </section>

        {{-- 4 Kartu KPI Analitik --}}
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-space-lg">
            <x-cava.stat-card 
                title="Okupansi Global" 
                code="METRIK-1" 
                value="78.4%" 
                valueLabel="Rata-rata Terpakai" 
                footerBadge="+5.2% vs Bulan Lalu" 
                footerText="1,420 Jam Peminjaman" 
                icon="pie_chart" 
                variant="secondary" 
            />
            <x-cava.stat-card 
                title="Total Sesi Selesai" 
                code="METRIK-2" 
                value="142" 
                valueLabel="Kegiatan Mahasiswa/Dosen" 
                footerBadge="0 Sengketa Waktu" 
                footerText="100% Concurrency Pass" 
                icon="event_available" 
                variant="primary" 
            />
            <x-cava.stat-card 
                title="Frekuensi Kerusakan" 
                code="METRIK-3" 
                value="14" 
                valueLabel="Tiket Pengaduan" 
                footerBadge="11 Selesai Diperbaiki" 
                footerText="3 Sedang Dikerjakan" 
                icon="build" 
                variant="tertiary" 
            />
            <x-cava.stat-card 
                title="Indeks Resolusi SLA" 
                code="METRIK-4" 
                value="94.2%" 
                valueLabel="Tuntas < 24 Jam" 
                footerBadge="Optimal Performance" 
                footerText="Standar Statuter Rektorat" 
                icon="verified" 
                variant="primary" 
            />
        </section>

        {{-- Tabel 1: Rekapitulasi Okupansi per Fasilitas --}}
        <section class="bg-surface-container-lowest rounded-xl shadow-sm p-space-lg border border-outline-variant/50 flex flex-col gap-space-md">
            <div class="flex items-center justify-between pb-space-sm border-b border-outline-variant">
                <div class="flex items-center gap-space-xs">
                    <span class="material-symbols-outlined text-primary text-[20px]">table_chart</span>
                    <h3 class="font-headline-sm text-headline-sm text-primary">Rekapitulasi Okupansi & Pemanfaatan Fasilitas Kampus</h3>
                </div>
                <span class="font-data-mono text-data-mono text-on-surface-variant text-[11px]">TA 2024/2025</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse font-body-sm">
                    <thead>
                        <tr class="bg-surface-container-high text-on-surface-variant font-label-sm uppercase">
                            <th class="py-space-sm px-space-md">Kode & Fasilitas</th>
                            <th class="py-space-sm px-space-md">Lokasi Gedung</th>
                            <th class="py-space-sm px-space-md">Total Pengajuan</th>
                            <th class="py-space-sm px-space-md">Disetujui</th>
                            <th class="py-space-md px-space-md">Ditolak / Batal</th>
                            <th class="py-space-md px-space-md">Total Jam</th>
                            <th class="py-space-md px-space-md text-right">Tingkat Utilisasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container-high/40">
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="py-space-md px-space-md font-bold text-primary">AUD-H01 • Auditorium B.J. Habibie</td>
                            <td class="py-space-md px-space-md text-on-surface-variant">Gedung Rektorat Lt. 1 & 2</td>
                            <td class="py-space-md px-space-md font-data-mono">48</td>
                            <td class="py-space-md px-space-md font-data-mono text-secondary font-bold">42</td>
                            <td class="py-space-md px-space-md font-data-mono text-error">6</td>
                            <td class="py-space-md px-space-md font-data-mono">315 Jam</td>
                            <td class="py-space-md px-space-md text-right">
                                <span class="px-2 py-0.5 rounded-full bg-secondary-container text-on-secondary-container font-data-mono font-bold text-[11px]">87.5%</span>
                            </td>
                        </tr>
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="py-space-md px-space-md font-bold text-primary">LAB-C204 • Lab Komputasi Awan</td>
                            <td class="py-space-md px-space-md text-on-surface-variant">Gedung Lab Barat Lt. 2</td>
                            <td class="py-space-md px-space-md font-data-mono">62</td>
                            <td class="py-space-md px-space-md font-data-mono text-secondary font-bold">58</td>
                            <td class="py-space-md px-space-md font-data-mono text-error">4</td>
                            <td class="py-space-md px-space-md font-data-mono">420 Jam</td>
                            <td class="py-space-md px-space-md text-right">
                                <span class="px-2 py-0.5 rounded-full bg-secondary-container text-on-secondary-container font-data-mono font-bold text-[11px]">93.5%</span>
                            </td>
                        </tr>
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="py-space-md px-space-md font-bold text-primary">CLS-B302 • Smart Classroom 302</td>
                            <td class="py-space-md px-space-md text-on-surface-variant">Gedung B Lt. 3</td>
                            <td class="py-space-md px-space-md font-data-mono">35</td>
                            <td class="py-space-md px-space-md font-data-mono text-secondary font-bold">30</td>
                            <td class="py-space-md px-space-md font-data-mono text-error">5</td>
                            <td class="py-space-md px-space-md font-data-mono">180 Jam</td>
                            <td class="py-space-md px-space-md text-right">
                                <span class="px-2 py-0.5 rounded-full bg-surface-container-highest text-primary font-data-mono font-bold text-[11px]">71.4%</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Tabel 2: Rekapitulasi Frekuensi Kerusakan Aset --}}
        <section class="bg-surface-container-lowest rounded-xl shadow-sm p-space-lg border border-outline-variant/50 flex flex-col gap-space-md">
            <div class="flex items-center justify-between pb-space-sm border-b border-outline-variant">
                <div class="flex items-center gap-space-xs">
                    <span class="material-symbols-outlined text-error text-[20px]">build_circle</span>
                    <h3 class="font-headline-sm text-headline-sm text-primary">Rekapitulasi Frekuensi Kerusakan Aset per Kategori</h3>
                </div>
                <span class="font-data-mono text-data-mono text-on-surface-variant text-[11px]">SLA METRICS</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse font-body-sm">
                    <thead>
                        <tr class="bg-surface-container-high text-on-surface-variant font-label-sm uppercase">
                            <th class="py-space-sm px-space-md">Kategori Kerusakan</th>
                            <th class="py-space-sm px-space-md">Jumlah Insiden</th>
                            <th class="py-space-sm px-space-md">Fasilitas Paling Sering Terdampak</th>
                            <th class="py-space-sm px-space-md">Rata-rata Waktu Resolusi</th>
                            <th class="py-space-sm px-space-md text-right">Status Kepatuhan SLA</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container-high/40">
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="py-space-md px-space-md font-bold">AC & Pendingin Ruang</td>
                            <td class="py-space-md px-space-md font-data-mono font-bold text-primary">5 Laporan</td>
                            <td class="py-space-md px-space-md text-on-surface-variant">Auditorium B.J. Habibie</td>
                            <td class="py-space-md px-space-md font-data-mono">18 Jam</td>
                            <td class="py-space-md px-space-md text-right">
                                <span class="text-secondary font-semibold">100% Sesuai SLA</span>
                            </td>
                        </tr>
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="py-space-md px-space-md font-bold">Kelistrikan / Stop Kontak</td>
                            <td class="py-space-md px-space-md font-data-mono font-bold text-primary">4 Laporan</td>
                            <td class="py-space-md px-space-md text-on-surface-variant">Lab Hardware & Robotika 2</td>
                            <td class="py-space-md px-space-md font-data-mono">22 Jam</td>
                            <td class="py-space-md px-space-md text-right">
                                <span class="text-secondary font-semibold">100% Sesuai SLA</span>
                            </td>
                        </tr>
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="py-space-md px-space-md font-bold">Proyektor, Audio & AV</td>
                            <td class="py-space-md px-space-md font-data-mono font-bold text-primary">3 Laporan</td>
                            <td class="py-space-md px-space-md text-on-surface-variant">Smart Classroom 302</td>
                            <td class="py-space-md px-space-md font-data-mono">8 Jam</td>
                            <td class="py-space-md px-space-md text-right">
                                <span class="text-secondary font-semibold">100% Sesuai SLA</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-admin-layout>
