{{-- 
  NAMA FILE      : export-report.blade.php
  FUNGSIONALITAS : Halaman Antarmuka Rekapitulasi, Analitik, dan Ekspor Laporan Statuter (Super Admin)
  DESKRIPSI      : Menampilkan visualisasi data rekap okupansi fasilitas kampus, frekuensi kerusakan aset, dan pencetakan dokumen resmi ke format CSV/Excel dan PDF.
  DESAIN SISTEM  : Opsi 1 (Executive Apple Bento & Minimalist Precision)
                   - Layout Bento Box lega (rounded-[2rem], border gray-100, soft shadows)
                   - Filter rentang waktu terintegrasi tombol aksi unduh dokumen resmi
                   - 4 Bento KPI Metric Cards informatif dan berbobot
                   - Tabel analitik luas dengan navigasi paginasi interaktif Alpine.js
                   - Pure typographic status indicator bebas badge mini berisik
  CARA KERJA     : Menggunakan layout <x-admin-layout active="export-report">, memicu download file CSV/Excel atau PDF dari ExportController berdasarkan rentang tanggal aktif.
--}}

<x-admin-layout title="Rekapitulasi & Ekspor Laporan" active="export-report">
    <div class="space-y-8">
        
        {{-- 1. Header Eksekutif & Pengantar Laporan --}}
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <nav class="flex items-center gap-2 text-xs text-[#86868B] mb-1.5">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-[#1D1D1F] transition-colors">Admin Console</a>
                    <span>/</span>
                    <span class="text-[#1D1D1F] font-medium">Laporan Statuter</span>
                </nav>
                <h1 class="text-3xl font-extrabold tracking-tight text-[#1D1D1F] font-display">Rekapitulasi & Ekspor Laporan</h1>
                <p class="text-sm text-[#86868B] mt-1 leading-relaxed">
                    Unduh data rekapitulasi pemanfaatan fasilitas dan insiden pemeliharaan dalam format spreadsheet dan PDF resmi.
                </p>
            </div>
        </div>

        {{-- 2. Bento Box Filter Rentang Waktu & Aksi Cepat Ekspor --}}
        <div class="bg-white rounded-[2rem] p-7 sm:p-8 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
            <div class="pb-5 mb-5 border-b border-gray-100">
                <h3 class="text-base font-bold text-[#1D1D1F]">Periode Waktu Laporan Statuter</h3>
                <p class="text-xs text-[#86868B] mt-0.5">Tentukan rentang tanggal untuk kompilasi data peminjaman ruangan dan catatan keluhan sarpras.</p>
            </div>

            <form action="{{ route('admin.export-report') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-xs font-semibold text-[#1D1D1F] mb-1.5" for="rep-start">Tanggal Mulai</label>
                    <input type="date" 
                           id="rep-start" 
                           name="start_date" 
                           value="{{ $startDate }}" 
                           class="w-full h-11 px-3.5 bg-[#FBFBFD] rounded-xl text-xs border border-gray-200 text-[#1D1D1F] focus:bg-white focus:border-[#1D1D1F] focus:outline-none transition">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-[#1D1D1F] mb-1.5" for="rep-end">Tanggal Selesai</label>
                    <input type="date" 
                           id="rep-end" 
                           name="end_date" 
                           value="{{ $endDate }}" 
                           class="w-full h-11 px-3.5 bg-[#FBFBFD] rounded-xl text-xs border border-gray-200 text-[#1D1D1F] focus:bg-white focus:border-[#1D1D1F] focus:outline-none transition">
                </div>

                <div>
                    <button type="submit" 
                            class="w-full h-11 px-4 rounded-xl bg-white border border-gray-200 text-[#1D1D1F] text-xs font-semibold hover:bg-gray-50 transition shadow-[0_2px_8px_rgb(0,0,0,0.02)] cursor-pointer flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-[18px] text-[#86868B]">sync</span>
                        <span>Segarkan Analitik</span>
                    </button>
                </div>

                <div class="flex items-center gap-2">
                    {{-- Tombol Ekspor Excel Reservasi --}}
                    <a href="{{ route('admin.export.reservations.excel', ['start_date' => $startDate, 'end_date' => $endDate]) }}" 
                       class="flex-1 h-11 px-3 rounded-xl bg-white border border-gray-200 text-[#1D1D1F] text-xs font-semibold hover:bg-gray-50 transition shadow-[0_2px_8px_rgb(0,0,0,0.02)] flex items-center justify-center gap-1.5 text-center">
                        <span class="material-symbols-outlined text-[16px] text-emerald-700">table_view</span>
                        <span>Excel</span>
                    </a>

                    {{-- Tombol Ekspor PDF Reservasi --}}
                    <a href="{{ route('admin.export.reservations.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}" 
                       class="flex-1 h-11 px-3 rounded-xl bg-[#1D1D1F] text-white text-xs font-semibold hover:bg-black transition shadow-sm flex items-center justify-center gap-1.5 text-center">
                        <span class="material-symbols-outlined text-[16px]">picture_as_pdf</span>
                        <span>PDF</span>
                    </a>
                </div>
            </form>
        </div>

        {{-- 3. Bento KPI Cards (4 Metrik Performa Statuter) --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            {{-- Metrik 1: Okupansi Global --}}
            <div class="bg-white rounded-[2rem] p-6 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                <div class="text-xs font-medium text-[#86868B] mb-2">Okupansi Fasilitas</div>
                <div class="text-3xl font-extrabold text-[#1D1D1F] tracking-tight font-display">{{ $occupancyRate }}%</div>
                <div class="text-xs text-emerald-800 font-semibold mt-2 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    <span>{{ number_format($totalHoursReserved, 1) }} Jam Terpakai</span>
                </div>
            </div>

            {{-- Metrik 2: Total Sesi Terpakai --}}
            <div class="bg-white rounded-[2rem] p-6 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                <div class="text-xs font-medium text-[#86868B] mb-2">Sesi Disetujui</div>
                <div class="text-3xl font-extrabold text-[#1D1D1F] tracking-tight font-display">{{ number_format($approvedCount) }}</div>
                <div class="text-xs text-[#86868B] mt-2 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#1D1D1F]"></span>
                    <span>Kegiatan sivitas terakomodasi</span>
                </div>
            </div>

            {{-- Metrik 3: Pengaduan Kerusakan --}}
            <div class="bg-white rounded-[2rem] p-6 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                <div class="text-xs font-medium text-[#86868B] mb-2">Tiket Kerusakan</div>
                <div class="text-3xl font-extrabold text-[#1D1D1F] tracking-tight font-display">{{ number_format($totalDamageReports) }}</div>
                <div class="text-xs text-amber-800 font-semibold mt-2 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                    <span>{{ $resolvedDamageCount }} Selesai • {{ $inProgressDamageCount }} Dikerjakan</span>
                </div>
            </div>

            {{-- Metrik 4: Indeks Resolusi SLA --}}
            <div class="bg-white rounded-[2rem] p-6 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                <div class="text-xs font-medium text-[#86868B] mb-2">Indeks Resolusi SLA</div>
                <div class="text-3xl font-extrabold text-[#1D1D1F] tracking-tight font-display">{{ $slaResolutionIndex }}%</div>
                <div class="text-xs {{ $slaResolutionIndex >= 85 ? 'text-emerald-800' : 'text-amber-800' }} font-semibold mt-2 flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full {{ $slaResolutionIndex >= 85 ? 'bg-emerald-500' : 'bg-amber-500' }}"></span>
                    <span>Standar kepatuhan penanganan</span>
                </div>
            </div>
        </div>

        {{-- 4. Tabel 1: Rekapitulasi Utilisasi Fasilitas Kampus --}}
        <section x-data="facilityReportTable(@js($facilityUtilization))" class="bg-white rounded-[2rem] border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)] overflow-hidden">
            <div class="p-6 sm:p-7 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold text-[#1D1D1F] font-display">Rekapitulasi Okupansi Fasilitas</h3>
                    <p class="text-xs text-[#86868B] mt-0.5">Rincian utilisasi dan jam pemakaian setiap ruangan pada periode berjalan.</p>
                </div>
                <span class="text-xs font-mono text-[#86868B]">{{ $startDate }} s.d. {{ $endDate }}</span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-xs text-[#86868B] font-medium bg-[#FBFBFD]">
                            <th class="py-4.5 px-6">Kode & Nama Ruangan</th>
                            <th class="py-4.5 px-6">Lokasi Gedung</th>
                            <th class="py-4.5 px-6">Total Pengajuan</th>
                            <th class="py-4.5 px-6">Disetujui</th>
                            <th class="py-4.5 px-6">Ditolak / Batal</th>
                            <th class="py-4.5 px-6">Total Jam</th>
                            <th class="py-4.5 px-6 text-right">Tingkat Okupansi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <template x-for="row in displayedRows" :key="row.id">
                            <tr class="hover:bg-[#FBFBFD] transition group">
                                <td class="py-5 px-6">
                                    <div class="font-bold text-[#1D1D1F]" x-text="row.name"></div>
                                    <div class="text-xs font-mono text-[#86868B] mt-0.5" x-text="row.code"></div>
                                </td>
                                <td class="py-5 px-6 text-xs text-[#86868B]" x-text="row.building"></td>
                                <td class="py-5 px-6 font-mono text-xs text-[#1D1D1F]" x-text="row.total_applications"></td>
                                <td class="py-5 px-6 font-mono text-xs font-semibold text-emerald-800" x-text="row.approved_count"></td>
                                <td class="py-5 px-6 font-mono text-xs text-[#86868B]" x-text="row.rejected_count"></td>
                                <td class="py-5 px-6 font-mono text-xs text-[#1D1D1F]" x-text="parseFloat(row.total_hours).toFixed(1) + ' Jam'"></td>
                                <td class="py-5 px-6 text-right">
                                    <span class="text-xs font-semibold"
                                          :class="row.utilization_rate >= 70 ? 'text-emerald-800' : 'text-[#1D1D1F]'"
                                          x-text="row.utilization_rate + '%'"></span>
                                </td>
                            </tr>
                        </template>
                        <template x-if="allRows.length === 0">
                            <tr>
                                <td colspan="7" class="py-16 text-center text-[#86868B] text-xs">
                                    Belum ada catatan peminjaman fasilitas pada rentang tanggal ini.
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>

            {{-- Paginasi Interaktif --}}
            <template x-if="allRows.length > 0">
                <div class="p-6 bg-[#FBFBFD] border-t border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs text-[#86868B]">
                    <div>
                        Menampilkan <strong class="text-[#1D1D1F]" x-text="((currentPage - 1) * perPage) + 1"></strong> - <strong class="text-[#1D1D1F]" x-text="Math.min(currentPage * perPage, allRows.length)"></strong> dari <strong class="text-[#1D1D1F]" x-text="allRows.length"></strong> fasilitas
                    </div>

                    <div class="flex items-center gap-1.5">
                        <button type="button" 
                                @click="prevPage()" 
                                :disabled="currentPage === 1"
                                class="px-3 py-1.5 rounded-xl border border-gray-200 text-xs font-medium hover:bg-white disabled:opacity-40 disabled:cursor-not-allowed transition cursor-pointer">
                            Sebelumnya
                        </button>

                        <template x-for="p in totalPages" :key="p">
                            <button type="button" 
                                    @click="setPage(p)"
                                    x-show="p === 1 || p === totalPages || Math.abs(p - currentPage) <= 1"
                                    :class="currentPage === p ? 'bg-[#1D1D1F] text-white font-bold' : 'bg-white border border-gray-200 text-[#1D1D1F] hover:bg-gray-50'"
                                    class="w-8 h-8 rounded-xl text-xs transition flex items-center justify-center font-medium cursor-pointer"
                                    x-text="p">
                            </button>
                        </template>

                        <button type="button" 
                                @click="nextPage()" 
                                :disabled="currentPage === totalPages"
                                class="px-3 py-1.5 rounded-xl border border-gray-200 text-xs font-medium hover:bg-white disabled:opacity-40 disabled:cursor-not-allowed transition cursor-pointer">
                            Selanjutnya
                        </button>
                    </div>
                </div>
            </template>
        </section>

        {{-- 5. Tabel 2: Rekapitulasi Insiden Kerusakan Aset --}}
        <section class="bg-white rounded-[2rem] border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)] overflow-hidden">
            <div class="p-6 sm:p-7 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold text-[#1D1D1F] font-display">Frekuensi Kerusakan Aset per Kategori</h3>
                    <p class="text-xs text-[#86868B] mt-0.5">Ringkasan penanganan gangguan dan kepatuhan SLA sarana prasarana.</p>
                </div>
                
                {{-- Aksi Unduh Laporan Kerusakan --}}
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.export.damage-reports.excel', ['start_date' => $startDate, 'end_date' => $endDate]) }}" 
                       class="px-3.5 py-2 rounded-xl bg-white border border-gray-200 text-[#1D1D1F] text-xs font-semibold hover:bg-gray-50 transition shadow-[0_2px_8px_rgb(0,0,0,0.02)] flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px] text-emerald-700">table_view</span>
                        <span>Excel Kerusakan</span>
                    </a>
                    <a href="{{ route('admin.export.damage-reports.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}" 
                       class="px-3.5 py-2 rounded-xl bg-white border border-rose-200 text-rose-800 text-xs font-semibold hover:bg-rose-50 transition shadow-[0_2px_8px_rgb(0,0,0,0.02)] flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">picture_as_pdf</span>
                        <span>PDF Kerusakan</span>
                    </a>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead>
                        <tr class="border-b border-gray-100 text-xs text-[#86868B] font-medium bg-[#FBFBFD]">
                            <th class="py-4.5 px-6">Kategori Kerusakan</th>
                            <th class="py-4.5 px-6">Jumlah Insiden</th>
                            <th class="py-4.5 px-6">Fasilitas Paling Sering Terdampak</th>
                            <th class="py-4.5 px-6">Rata-rata Waktu Resolusi</th>
                            <th class="py-4.5 px-6 text-right">Status Kepatuhan SLA</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($damageByCategory as $cat)
                            <tr class="hover:bg-[#FBFBFD] transition group">
                                <td class="py-5 px-6 font-bold text-[#1D1D1F]">{{ $cat['category'] }}</td>
                                <td class="py-5 px-6 font-mono text-xs font-bold text-[#1D1D1F]">{{ $cat['incident_count'] }} Laporan</td>
                                <td class="py-5 px-6 text-xs text-[#86868B]">{{ $cat['most_affected_facility'] }}</td>
                                <td class="py-5 px-6 font-mono text-xs text-[#86868B]">{{ $cat['avg_resolution_time'] }}</td>
                                <td class="py-5 px-6 text-right">
                                    <span class="text-xs font-semibold text-emerald-800">{{ $cat['sla_compliance'] }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="py-16 text-center text-[#86868B] text-xs">
                                    Tidak ada catatan insiden kerusakan pada rentang periode yang dipilih.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="p-5 bg-[#FBFBFD] border-t border-gray-100 flex items-center justify-between text-xs text-[#86868B]">
                <span>Seluruh laporan diekspor sesuai format audit statuter universitas.</span>
                <span>CAVA Institutional Analytics</span>
            </div>
        </section>

    </div>

    {{-- Script Pembungkus Komponen Alpine.js Terisolasi Bebas Syntax Leak --}}
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
