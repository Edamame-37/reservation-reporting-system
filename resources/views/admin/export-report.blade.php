{{-- 
  NAMA FILE      : export-report.blade.php
  FUNGSIONALITAS : Halaman Antarmuka Rekapitulasi, Analitik, dan Ekspor Laporan (Super Admin)
  DESKRIPSI      : Menampilkan visualisasi data rekap okupansi fasilitas kampus, frekuensi kerusakan aset, dan tombol pencetakan dokumen resmi ke format CSV/Excel dan PDF (UR17).
  CARA KERJA     : Menggunakan layout <x-admin-layout active="export-report">, memicu download file CSV/Excel atau PDF dari ExportController berdasarkan rentang tanggal aktif.
--}}

<x-admin-layout title="Rekapitulasi & Ekspor Laporan" active="export-report">
    <!-- 
      ELEMEN       : Section Rekapitulasi & Analitik Pelaporan Sistem (UR17)
      KEGUNAAN     : Menyediakan data agregasi berkala bagi pimpinan universitas dan tombol cetak laporan resmi.
      CARA KERJA   : Merender tabel rekap okupansi fasilitas, distribusi jenis kerusakan, serta tombol trigger unduh dokumen (Excel/CSV dan PDF).
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

                {{-- Action Buttons Ekspor Utama (Reservasi) --}}
                <div class="flex items-center gap-space-sm flex-wrap">
                    <!-- 
                      ROUTE: GET /admin/export/reservations/excel?start_date=...&end_date=...
                      FUNGSI: Mengunduh data rekapitulasi okupansi peminjaman ruang dalam format spreadsheet Excel/CSV kompatibel
                    -->
                    <a href="{{ route('admin.export.reservations.excel', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="h-10 px-space-lg rounded-lg bg-surface-container text-primary font-label-md text-label-md font-semibold hover:bg-surface-container-highest transition-colors flex items-center gap-1 shadow-sm border border-outline-variant/50">
                        <span class="material-symbols-outlined text-[18px]">table_view</span>
                        <span>Ekspor Excel / CSV</span>
                    </a>

                    <!-- 
                      ROUTE: GET /admin/export/reservations/pdf?start_date=...&end_date=...
                      FUNGSI: Mencetak dokumen laporan statuter resmi reservasi dalam format PDF kop resmi (DomPDF)
                    -->
                    <a href="{{ route('admin.export.reservations.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="h-10 px-space-lg rounded-lg bg-primary text-on-primary font-label-md text-label-md font-semibold hover:bg-primary-container transition-colors flex items-center gap-1 shadow-sm">
                        <span class="material-symbols-outlined text-[18px]">picture_as_pdf</span>
                        <span>Cetak Dokumen PDF Resmi</span>
                    </a>
                </div>
            </div>

            <!-- 
              ROUTE: GET /admin/export-report
              FUNGSI: Memfilter rekapitulasi data analitik okupansi dan insiden kerusakan berdasarkan rentang tanggal
            -->
            <form action="{{ route('admin.export-report') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-space-md items-end">
                <div>
                    <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant" for="rep-start">Periode Mulai</label>
                    <input type="date" id="rep-start" name="start_date" value="{{ $startDate }}" class="w-full h-10 px-space-md rounded-lg bg-surface-container-low text-on-surface font-body-sm border border-outline-variant/60 focus:border-primary focus:outline-none">
                </div>
                <div>
                    <label class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant" for="rep-end">Periode Selesai</label>
                    <input type="date" id="rep-end" name="end_date" value="{{ $endDate }}" class="w-full h-10 px-space-md rounded-lg bg-surface-container-low text-on-surface font-body-sm border border-outline-variant/60 focus:border-primary focus:outline-none">
                </div>
                <div>
                    <button type="submit" class="w-full h-10 inline-flex items-center justify-center gap-space-xs rounded-lg bg-primary text-on-primary font-label-md text-label-md hover:bg-primary-container transition-colors shadow-sm">
                        <span class="material-symbols-outlined text-[18px]">refresh</span>
                        <span>Segarkan Data Analitik</span>
                    </button>
                </div>
            </form>
        </section>

        <!-- 
          ELEMEN       : 4 Kartu KPI Analitik Sistem
          KEGUNAAN     : Menyajikan metrik ringkasan performa fasilitas dan penanganan aduan pada rentang tanggal aktif.
          CARA KERJA   : Menerima kalkulasi dari ExportController (okupansi global, sesi selesai, tiket aduan, indeks SLA).
        -->
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-space-lg">
            <x-cava.stat-card 
                title="Okupansi Global" 
                badgeCode="METRIK-1" 
                :value="$occupancyRate . '%'" 
                subtitle="Rata-rata Terpakai" 
                :footerBadge="$occupancyRate >= 70 ? 'Sangat Optimal' : ($occupancyRate >= 40 ? 'Cukup Optimal' : 'Perlu Peningkatan')" 
                :footerText="number_format($totalHoursReserved, 1) . ' Jam Peminjaman'" 
                icon="pie_chart" 
                variant="secondary" 
            />
            <x-cava.stat-card 
                title="Total Sesi Disetujui" 
                badgeCode="METRIK-2" 
                :value="$approvedCount" 
                subtitle="Kegiatan Sivitas Kampus" 
                footerBadge="0 Sengketa Waktu" 
                footerText="100% Concurrency Pass" 
                icon="event_available" 
                variant="primary" 
            />
            <x-cava.stat-card 
                title="Frekuensi Kerusakan" 
                badgeCode="METRIK-3" 
                :value="$totalDamageReports" 
                subtitle="Tiket Pengaduan" 
                :footerBadge="$resolvedDamageCount . ' Selesai Diperbaiki'" 
                :footerText="$inProgressDamageCount . ' Sedang Dikerjakan'" 
                icon="build" 
                variant="tertiary" 
            />
            <x-cava.stat-card 
                title="Indeks Resolusi SLA" 
                badgeCode="METRIK-4" 
                :value="$slaResolutionIndex . '%'" 
                subtitle="Tuntas Sesuai SLA" 
                :footerBadge="$slaResolutionIndex >= 90 ? 'Optimal Performance' : 'Perlu Evaluasi'" 
                footerText="Standar Statuter Rektorat" 
                icon="verified" 
                variant="primary" 
            />
        </section>

        <!-- 
          ELEMEN       : Tabel 1 - Rekapitulasi Okupansi & Pemanfaatan Fasilitas Kampus
          KEGUNAAN     : Menyajikan daftar rincian utilisasi setiap ruang pada periode berjalan.
          CARA KERJA   : Melakukan perulangan data pada $facilityUtilization dengan data permohonan, persetujuan, dan rasio jam.
        -->
        <section class="bg-surface-container-lowest rounded-xl shadow-sm p-space-lg border border-outline-variant/50 flex flex-col gap-space-md">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-space-sm border-b border-outline-variant gap-2">
                <div class="flex items-center gap-space-xs">
                    <span class="material-symbols-outlined text-primary text-[20px]">table_chart</span>
                    <h3 class="font-headline-sm text-headline-sm text-primary">Rekapitulasi Okupansi & Pemanfaatan Fasilitas Kampus</h3>
                </div>
                <div class="flex items-center gap-2">
                    <span class="font-data-mono text-data-mono text-on-surface-variant text-[11px]">Periode: {{ $startDate }} s.d. {{ $endDate }}</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse font-body-sm">
                    <thead>
                        <tr class="bg-surface-container-high text-on-surface-variant font-label-sm uppercase">
                            <th class="py-space-sm px-space-md">Kode & Fasilitas</th>
                            <th class="py-space-sm px-space-md">Lokasi Gedung</th>
                            <th class="py-space-sm px-space-md">Total Pengajuan</th>
                            <th class="py-space-sm px-space-md">Disetujui</th>
                            <th class="py-space-sm px-space-md">Ditolak / Batal</th>
                            <th class="py-space-sm px-space-md">Total Jam</th>
                            <th class="py-space-sm px-space-md text-right">Tingkat Utilisasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container-high/40">
                        @forelse($facilityUtilization as $row)
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="py-space-md px-space-md font-bold text-primary">{{ $row['code'] }} • {{ $row['name'] }}</td>
                            <td class="py-space-md px-space-md text-on-surface-variant">{{ $row['building'] }}</td>
                            <td class="py-space-md px-space-md font-data-mono">{{ $row['total_applications'] }}</td>
                            <td class="py-space-md px-space-md font-data-mono text-secondary font-bold">{{ $row['approved_count'] }}</td>
                            <td class="py-space-md px-space-md font-data-mono text-error">{{ $row['rejected_count'] }}</td>
                            <td class="py-space-md px-space-md font-data-mono">{{ number_format($row['total_hours'], 1) }} Jam</td>
                            <td class="py-space-md px-space-md text-right">
                                <span class="px-2 py-0.5 rounded-full {{ $row['utilization_rate'] >= 70 ? 'bg-secondary-container text-on-secondary-container' : 'bg-surface-container-highest text-primary' }} font-data-mono font-bold text-[11px]">{{ $row['utilization_rate'] }}%</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="py-8 text-center text-on-surface-variant">
                                <div class="flex flex-col items-center justify-center gap-1.5">
                                    <span class="material-symbols-outlined text-outline-variant text-[28px]">event_busy</span>
                                    <p class="font-medium text-xs">Belum ada rekaman permohonan reservasi fasilitas pada rentang tanggal ini.</p>
                                    <p class="text-[11px] text-outline">Gunakan filter periode di atas untuk memuat tanggal lainnya.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <!-- 
          ELEMEN       : Tabel 2 - Rekapitulasi Frekuensi Kerusakan Aset per Kategori
          KEGUNAAN     : Menyajikan data agregasi insiden kerusakan, fasilitas terdampak, rata-rata resolusi, dan kepatuhan SLA.
          CARA KERJA   : Melakukan perulangan data pada $damageByCategory serta menyediakan tombol ekspor khusus laporan kerusakan (Excel/PDF).
        -->
        <section class="bg-surface-container-lowest rounded-xl shadow-sm p-space-lg border border-outline-variant/50 flex flex-col gap-space-md">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-space-sm border-b border-outline-variant gap-2">
                <div class="flex items-center gap-space-xs">
                    <span class="material-symbols-outlined text-error text-[20px]">build_circle</span>
                    <h3 class="font-headline-sm text-headline-sm text-primary">Rekapitulasi Frekuensi Kerusakan Aset per Kategori</h3>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <!-- 
                      ROUTE: GET /admin/export/damage-reports/excel?start_date=...&end_date=...
                      FUNGSI: Mengunduh data rekapitulasi keluhan kerusakan format Excel/CSV
                    -->
                    <a href="{{ route('admin.export.damage-reports.excel', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="px-2.5 py-1 rounded bg-surface-container text-primary font-label-sm text-[11px] font-semibold hover:bg-surface-container-highest transition flex items-center gap-1 border border-outline-variant/50">
                        <span class="material-symbols-outlined text-[14px]">table_view</span>
                        <span>Excel Kerusakan</span>
                    </a>
                    <!-- 
                      ROUTE: GET /admin/export/damage-reports/pdf?start_date=...&end_date=...
                      FUNGSI: Mencetak dokumen resmi laporan rekapitulasi kerusakan aset format PDF landscape A4
                    -->
                    <a href="{{ route('admin.export.damage-reports.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="px-2.5 py-1 rounded bg-rose-50 text-rose-700 font-label-sm text-[11px] font-semibold hover:bg-rose-100 transition flex items-center gap-1 border border-rose-200">
                        <span class="material-symbols-outlined text-[14px]">picture_as_pdf</span>
                        <span>PDF Kerusakan</span>
                    </a>
                </div>
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
                        @forelse($damageByCategory as $cat)
                        <tr class="hover:bg-surface-container-low transition-colors">
                            <td class="py-space-md px-space-md font-bold">{{ $cat['category'] }}</td>
                            <td class="py-space-md px-space-md font-data-mono font-bold text-primary">{{ $cat['incident_count'] }} Laporan</td>
                            <td class="py-space-md px-space-md text-on-surface-variant">{{ $cat['most_affected_facility'] }}</td>
                            <td class="py-space-md px-space-md font-data-mono">{{ $cat['avg_resolution_time'] }}</td>
                            <td class="py-space-md px-space-md text-right">
                                <span class="text-secondary font-semibold">{{ $cat['sla_compliance'] }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-on-surface-variant">
                                <div class="flex flex-col items-center justify-center gap-1.5">
                                    <span class="material-symbols-outlined text-outline-variant text-[28px]">task_alt</span>
                                    <p class="font-medium text-xs">Tidak ada catatan tiket kerusakan pada rentang periode yang dipilih.</p>
                                    <p class="text-[11px] text-outline">Kondisi seluruh sarana dan prasarana terpantau dalam kondisi optimal.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-admin-layout>
