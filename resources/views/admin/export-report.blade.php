{{-- 
  NAMA FILE      : export-report.blade.php
  FUNGSIONALITAS : Halaman Antarmuka Rekapitulasi & Ekspor Laporan (100% Sesuai Opsi 1 Prototype 4_try-admin.html)
  DESKRIPSI      : Menampilkan rentang tanggal periode laporan statuter, tombol unduh Excel/CSV dan PDF resmi, serta data rekapitulasi okupansi fasilitas dan insiden pemeliharaan.
  DESAIN SISTEM  : Apple Bento & Minimalist Precision (Opsi 1)
  CARA KERJA     : Menggunakan layout <x-admin-layout active="export-report">, memicu download file CSV/Excel atau PDF dari ExportController berdasarkan rentang tanggal aktif.
--}}

<x-admin-layout title="Rekapitulasi & Ekspor Laporan" active="export-report">
    <div class="space-y-8">
        
        {{-- 1. Header Halaman (100% Sesuai 4_try-admin.html Opsi 1) --}}
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <h1 class="text-3xl font-extrabold tracking-tight text-[#1D1D1F] font-display">Rekapitulasi & Ekspor Laporan</h1>
                <p class="text-sm text-[#86868B] mt-1.5 leading-relaxed">Unduh data rekapitulasi okupansi fasilitas dan insiden pemeliharaan dalam format spreadsheet resmi.</p>
            </div>
        </div>

        {{-- 2. Rentang Tanggal Filter Box Bergaya Bento Apple (100% Sesuai Prototype) --}}
        <div class="bg-white rounded-[2rem] p-7 sm:p-8 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
            <h3 class="text-base font-bold text-[#1D1D1F] mb-1">Periode Waktu Laporan</h3>
            <p class="text-xs text-[#86868B] mb-6">Tentukan rentang tanggal untuk kompilasi data peminjaman ruangan dan catatan kerusakan.</p>

            <form action="{{ route('admin.export-report') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 items-end">
                <div>
                    <label class="block text-xs font-medium text-[#86868B] mb-1.5" for="exp-start">Tanggal Mulai</label>
                    <input type="date" 
                           id="exp-start" 
                           name="start_date" 
                           value="{{ $startDate }}" 
                           onchange="this.form.submit()" 
                           class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-4 py-2.5 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-black">
                </div>
                <div>
                    <label class="block text-xs font-medium text-[#86868B] mb-1.5" for="exp-end">Tanggal Selesai</label>
                    <input type="date" 
                           id="exp-end" 
                           name="end_date" 
                           value="{{ $endDate }}" 
                           onchange="this.form.submit()" 
                           class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-4 py-2.5 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-black">
                </div>
                <div>
                    <a href="{{ route('admin.export.reservations.excel', ['start_date' => $startDate, 'end_date' => $endDate]) }}" 
                       class="w-full bg-white border border-gray-200 text-[#1D1D1F] hover:bg-gray-50 py-2.5 px-4 rounded-xl text-sm font-medium transition cursor-pointer flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">table_view</span>
                        <span>Unduh Excel / CSV</span>
                    </a>
                </div>
                <div>
                    <a href="{{ route('admin.export.reservations.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}" 
                       class="w-full bg-[#1D1D1F] text-white hover:bg-black py-2.5 px-4 rounded-xl text-sm font-medium transition cursor-pointer flex items-center justify-center gap-2 shadow-sm">
                        <span class="material-symbols-outlined text-[18px]">picture_as_pdf</span>
                        <span>Cetak PDF Resmi</span>
                    </a>
                </div>
            </form>
        </div>

        {{-- 3. Tabel Rekapitulasi Okupansi Fasilitas --}}
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

        {{-- 4. Tabel Frekuensi Kerusakan Aset --}}
        <section class="bg-white rounded-[2rem] border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)] overflow-hidden">
            <div class="p-6 sm:p-7 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h3 class="text-base font-bold text-[#1D1D1F] font-display">Frekuensi Kerusakan Aset per Kategori</h3>
                    <p class="text-xs text-[#86868B] mt-0.5">Ringkasan penanganan gangguan dan kepatuhan SLA sarana prasarana.</p>
                </div>
                
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.export.damage-reports.excel', ['start_date' => $startDate, 'end_date' => $endDate]) }}" 
                       class="px-3.5 py-2 rounded-xl bg-white border border-gray-200 text-[#1D1D1F] text-xs font-semibold hover:bg-gray-50 transition shadow-xs flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px] text-emerald-700">table_view</span>
                        <span>Excel Kerusakan</span>
                    </a>
                    <a href="{{ route('admin.export.damage-reports.pdf', ['start_date' => $startDate, 'end_date' => $endDate]) }}" 
                       class="px-3.5 py-2 rounded-xl bg-white border border-rose-200 text-rose-800 text-xs font-semibold hover:bg-rose-50 transition shadow-xs flex items-center gap-1.5">
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
