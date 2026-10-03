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
    <div class="flex flex-col gap-6">
        {{-- Section Header & Filter Periode Terpadu --}}
        <section class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
            <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4 pb-4 border-b border-slate-100 mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-900 flex items-center justify-center shadow-xs border border-blue-100">
                        <span class="material-symbols-outlined text-[22px] text-blue-800">analytics</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-0.5">
                            <span class="text-[10px] font-semibold uppercase tracking-wider text-slate-400 font-mono">UR17 • REPORTING & ANALYTICS</span>
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">Statuter Ready</span>
                        </div>
                        <h2 class="text-xl font-bold text-slate-900 tracking-tight">Rekapitulasi Okupansi Fasilitas & Insiden Kerusakan</h2>
                    </div>
                </div>

                {{-- Action Buttons Ekspor Utama (Reservasi) --}}
                <div class="flex items-center gap-2 flex-wrap">
                    <!-- 
                      ROUTE: GET /admin/export/reservations/excel?start_date=...&end_date=...
                      FUNGSI: Mengunduh data rekapitulasi okupansi peminjaman ruang dalam format spreadsheet Excel/CSV kompatibel
                    -->
                    <a href="{{ route('admin.export.reservations.excel', ['start_date' => $startDate, 'end_date' => $endDate]) }}" 
                       class="h-9 px-3.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition-colors flex items-center gap-1.5 border border-slate-200">
                        <span class="material-symbols-outlined text-[16px] text-emerald-600">table_view</span>
                        <span>Ekspor Excel / CSV</span>
                    </a>

                    <!-- 
                      ROUTE: GET /admin/export/reservations/pdf?start_date=...&end_date=...
                      FUNGSI: Mencetak dokumen laporan statuter resmi reservasi dalam format PDF kop resmi (DomPDF)
                    -->
                    <a href="{{ route('admin.export.reservations.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}" 
                       class="h-9 px-3.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-semibold transition-colors flex items-center gap-1.5 shadow-xs">
                        <span class="material-symbols-outlined text-[16px]">picture_as_pdf</span>
                        <span>Cetak Dokumen PDF Resmi</span>
                    </a>
                </div>
            </div>

            <!-- 
              ROUTE: GET /admin/export-report
              FUNGSI: Memfilter rekapitulasi data analitik okupansi dan insiden kerusakan berdasarkan rentang tanggal
            -->
            <form action="{{ route('admin.export-report') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1" for="rep-start">Periode Mulai</label>
                    <input type="date" id="rep-start" name="start_date" value="{{ $startDate }}" class="w-full h-9 px-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-800 focus:bg-white focus:border-slate-900 focus:outline-none transition-colors">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-600 mb-1" for="rep-end">Periode Selesai</label>
                    <input type="date" id="rep-end" name="end_date" value="{{ $endDate }}" class="w-full h-9 px-3 rounded-xl bg-slate-50 border border-slate-200 text-xs text-slate-800 focus:bg-white focus:border-slate-900 focus:outline-none transition-colors">
                </div>
                <div>
                    <button type="submit" class="w-full h-9 inline-flex items-center justify-center gap-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold border border-slate-200 transition-colors cursor-pointer">
                        <span class="material-symbols-outlined text-[16px]">refresh</span>
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
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <x-cava.stat-card 
                title="Okupansi Global" 
                badgeCode="METRIK-1" 
                :value="$occupancyRate . '%'" 
                subtitle="Rata-rata Terpakai" 
                :footerBadge="$occupancyRate >= 70 ? 'Sangat Optimal' : ($occupancyRate >= 40 ? 'Optimal' : 'Cukup')" 
                :footerText="number_format($totalHoursReserved, 1) . ' Jam Terpakai'" 
                icon="pie_chart" 
                variant="secondary" 
            />
            <x-cava.stat-card 
                title="Total Sesi Disetujui" 
                badgeCode="METRIK-2" 
                :value="$approvedCount" 
                subtitle="Kegiatan Sivitas Kampus" 
                footerBadge="0 Sengketa" 
                footerText="100% Concurrency" 
                icon="event_available" 
                variant="primary" 
            />
            <x-cava.stat-card 
                title="Frekuensi Kerusakan" 
                badgeCode="METRIK-3" 
                :value="$totalDamageReports" 
                subtitle="Tiket Pengaduan" 
                :footerBadge="$resolvedDamageCount . ' Selesai'" 
                :footerText="$inProgressDamageCount . ' Dikerjakan'" 
                icon="build" 
                variant="tertiary" 
            />
            <x-cava.stat-card 
                title="Indeks Resolusi SLA" 
                badgeCode="METRIK-4" 
                :value="$slaResolutionIndex . '%'" 
                subtitle="Tuntas Sesuai SLA" 
                :footerBadge="$slaResolutionIndex >= 90 ? 'Optimal' : 'Perlu Evaluasi'" 
                footerText="Standar Statuter Rektorat" 
                icon="verified" 
                variant="primary" 
            />
        </section>

        <!-- 
          ELEMEN       : Tabel 1 - Rekapitulasi Okupansi & Pemanfaatan Fasilitas Kampus
          KEGUNAAN     : Menyajikan daftar rincian utilisasi setiap ruang pada periode berjalan.
          CARA KERJA   : Alpine.js membagi data menjadi halaman-halaman berukuran 10 baris, menghitung total halaman secara dinamis via fungsi terisolasi bebas syntax leak.
        -->
        <section x-data="facilityReportTable(@js($facilityUtilization))" class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 flex flex-col gap-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-100 gap-2">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px] text-slate-700">table_chart</span>
                    <h3 class="text-sm font-bold text-slate-900">Rekapitulasi Okupansi & Pemanfaatan Fasilitas Kampus</h3>
                </div>
                <div class="flex items-center gap-2">
                    <span class="font-mono text-xs text-slate-500">Periode: {{ $startDate }} s.d. {{ $endDate }}</span>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-100">
                            <th class="py-3 px-4">Kode & Fasilitas</th>
                            <th class="py-3 px-4">Lokasi Gedung</th>
                            <th class="py-3 px-4">Total Pengajuan</th>
                            <th class="py-3 px-4">Disetujui</th>
                            <th class="py-3 px-4">Ditolak / Batal</th>
                            <th class="py-3 px-4">Total Jam</th>
                            <th class="py-3 px-4 text-right">Tingkat Utilisasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="row in displayedRows" :key="row.id">
                            <tr class="hover:bg-slate-50/60 transition-colors">
                                <td class="py-3 px-4 font-semibold text-slate-800" x-text="row.code + ' • ' + row.name"></td>
                                <td class="py-3 px-4 text-slate-500" x-text="row.building"></td>
                                <td class="py-3 px-4 font-mono text-slate-700" x-text="row.total_applications"></td>
                                <td class="py-3 px-4 font-mono font-semibold text-emerald-600" x-text="row.approved_count"></td>
                                <td class="py-3 px-4 font-mono text-rose-600" x-text="row.rejected_count"></td>
                                <td class="py-3 px-4 font-mono text-slate-600" x-text="parseFloat(row.total_hours).toFixed(1) + ' Jam'"></td>
                                <td class="py-3 px-4 text-right">
                                    <span class="px-2 py-0.5 rounded-full font-mono font-bold text-[10px]"
                                          :class="row.utilization_rate >= 70 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-slate-100 text-slate-700'"
                                          x-text="row.utilization_rate + '%'"></span>
                                </td>
                            </tr>
                        </template>
                        <template x-if="allRows.length === 0">
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400">
                                    <div class="flex flex-col items-center justify-center gap-1.5">
                                        <span class="material-symbols-outlined text-[28px] text-slate-300">event_busy</span>
                                        <p class="font-medium text-xs text-slate-600">Belum ada rekaman permohonan reservasi fasilitas pada rentang tanggal ini.</p>
                                        <p class="text-[11px] text-slate-400">Gunakan filter periode di atas untuk memuat tanggal lainnya.</p>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            <!-- Kontrol Navigasi Paginasi Interaktif -->
            <template x-if="allRows.length > 0">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pt-3 border-t border-slate-100 text-xs text-slate-500">
                    <div>
                        <span>Menampilkan <strong class="text-slate-800 font-bold" x-text="((currentPage - 1) * perPage) + 1"></strong> s.d. <strong class="text-slate-800 font-bold" x-text="Math.min(currentPage * perPage, allRows.length)"></strong> dari <strong class="text-slate-800 font-bold" x-text="allRows.length"></strong> fasilitas</span>
                    </div>

                    <div class="flex items-center gap-1">
                        {{-- Tombol Sebelumnya --}}
                        <button 
                            type="button" 
                            @click="prevPage()" 
                            :disabled="currentPage === 1"
                            class="px-2.5 py-1 rounded-lg border border-slate-200 text-xs font-medium hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition flex items-center gap-1">
                            <span class="material-symbols-outlined text-[14px]">chevron_left</span>
                            <span>Sebelumnya</span>
                        </button>

                        {{-- Nomor Halaman Dinamis --}}
                        <template x-for="p in totalPages" :key="p">
                            <button 
                                type="button" 
                                @click="setPage(p)"
                                x-show="p === 1 || p === totalPages || Math.abs(p - currentPage) <= 1"
                                :class="currentPage === p ? 'bg-slate-900 text-white font-bold shadow-xs' : 'bg-slate-100 text-slate-700 hover:bg-slate-200'"
                                class="w-7 h-7 rounded-lg text-xs transition flex items-center justify-center font-medium"
                                x-text="p">
                            </button>
                        </template>

                        {{-- Tombol Selanjutnya --}}
                        <button 
                            type="button" 
                            @click="nextPage()" 
                            :disabled="currentPage === totalPages"
                            class="px-2.5 py-1 rounded-lg border border-slate-200 text-xs font-medium hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition flex items-center gap-1">
                            <span>Selanjutnya</span>
                            <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                        </button>
                    </div>
                </div>
            </template>
        </section>

        <!-- 
          ELEMEN       : Tabel 2 - Rekapitulasi Frekuensi Kerusakan Aset per Kategori
          KEGUNAAN     : Menyajikan data agregasi insiden kerusakan, fasilitas terdampak, rata-rata resolusi, dan kepatuhan SLA.
          CARA KERJA   : Melakukan perulangan data pada $damageByCategory serta menyediakan tombol ekspor khusus laporan kerusakan (Excel/PDF).
        -->
        <section class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-5 flex flex-col gap-4">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 border-b border-slate-100 gap-2">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-rose-600 text-[20px]">build_circle</span>
                    <h3 class="text-sm font-bold text-slate-900">Rekapitulasi Frekuensi Kerusakan Aset per Kategori</h3>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <!-- 
                      ROUTE: GET /admin/export/damage-reports/excel?start_date=...&end_date=...
                      FUNGSI: Mengunduh data rekapitulasi keluhan kerusakan format Excel/CSV
                    -->
                    <a href="{{ route('admin.export.damage-reports.excel', ['start_date' => $startDate, 'end_date' => $endDate]) }}" 
                       class="h-8 px-3 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition flex items-center gap-1 border border-slate-200">
                        <span class="material-symbols-outlined text-[14px]">table_view</span>
                        <span>Excel Kerusakan</span>
                    </a>
                    <!-- 
                      ROUTE: GET /admin/export/damage-reports/pdf?start_date=...&end_date=...
                      FUNGSI: Mencetak dokumen resmi laporan rekapitulasi kerusakan aset format PDF landscape A4
                    -->
                    <a href="{{ route('admin.export.damage-reports.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}" 
                       class="h-8 px-3 rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 text-xs font-semibold transition flex items-center gap-1 border border-rose-200">
                        <span class="material-symbols-outlined text-[14px]">picture_as_pdf</span>
                        <span>PDF Kerusakan</span>
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 text-slate-500 uppercase tracking-wider font-semibold border-b border-slate-100">
                            <th class="py-3 px-4">Kategori Kerusakan</th>
                            <th class="py-3 px-4">Jumlah Insiden</th>
                            <th class="py-3 px-4">Fasilitas Paling Sering Terdampak</th>
                            <th class="py-3 px-4">Rata-rata Waktu Resolusi</th>
                            <th class="py-3 px-4 text-right">Status Kepatuhan SLA</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($damageByCategory as $cat)
                        <tr class="hover:bg-slate-50/60 transition-colors">
                            <td class="py-3 px-4 font-semibold text-slate-800">{{ $cat['category'] }}</td>
                            <td class="py-3 px-4 font-mono font-bold text-slate-900">{{ $cat['incident_count'] }} Laporan</td>
                            <td class="py-3 px-4 text-slate-500">{{ $cat['most_affected_facility'] }}</td>
                            <td class="py-3 px-4 font-mono text-slate-600">{{ $cat['avg_resolution_time'] }}</td>
                            <td class="py-3 px-4 text-right">
                                <span class="text-emerald-700 font-semibold text-xs">{{ $cat['sla_compliance'] }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-slate-400">
                                <div class="flex flex-col items-center justify-center gap-1.5">
                                    <span class="material-symbols-outlined text-[28px] text-slate-300">task_alt</span>
                                    <p class="font-medium text-xs text-slate-600">Tidak ada catatan tiket kerusakan pada rentang periode yang dipilih.</p>
                                    <p class="text-[11px] text-slate-400">Kondisi seluruh sarana dan prasarana terpantau dalam kondisi optimal.</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    {{-- Script Pembungkus Komponen Alpine.js Terisolasi Bebas Syntax Leak (Poin 2) --}}
    <script>
        function facilityReportTable(initialData) {
            return {
                allRows: initialData || [],
                currentPage: 1,
                perPage: 10,
                get totalPages() {
                    return Math.max(1, Math.ceil(this.allRows.length / this.perPage));
                },
                get displayedRows() {
                    const start = (this.currentPage - 1) * this.perPage;
                    return this.allRows.slice(start, start + this.perPage);
                },
                setPage(p) {
                    if (p >= 1 && p <= this.totalPages) {
                        this.currentPage = p;
                    }
                },
                prevPage() {
                    if (this.currentPage > 1) {
                        this.currentPage--;
                    }
                },
                nextPage() {
                    if (this.currentPage < this.totalPages) {
                        this.currentPage++;
                    }
                }
            };
        }
    </script>
</x-admin-layout>
