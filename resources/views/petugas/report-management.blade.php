{{-- 
  NAMA FILE      : report-management.blade.php
  FUNGSIONALITAS : Halaman Manajemen Tiket Kerusakan & Status Fasilitas Sarpras
  DESKRIPSI      : Menampilkan seluruh antrean tiket kerusakan fasilitas kampus, mengubah status progres (Baru/Diproses/Selesai), menginput catatan resolusi, serta mengunci fasilitas ke status 'Dalam Perbaikan' (UR11, UR12).
  CARA KERJA     : Memanfaatkan layout <x-petugas-layout active="report-management">, mengelola modal pembaruan status dan sinkronisasi kalender maintenance via Alpine.js.
--}}

<x-petugas-layout title="Manajemen Tiket Kerusakan Fasilitas" active="report-management">
    <div x-data="{
        showStatusModal: false,
        reportId: '',
        venueName: '',
        currentStatus: 'baru',
        openStatusModal(id, venue, status) {
            this.reportId = id;
            this.venueName = venue;
            this.currentStatus = status;
            this.showStatusModal = true;
        }
    }">
        <!-- 
          ELEMEN       : Section Manajemen Tiket Kerusakan (UR11, UR12)
          KEGUNAAN     : Menindaklanjuti keluhan sarana, menginput progress kerja teknisi, dan mengunci kalender fasilitas yang sedang diperbaiki.
          CARA KERJA   : Petugas memilih tiket, memperbarui status tiket, dan opsi mencentang 'Kunci Status Fasilitas: Dalam Perbaikan'.
        -->
        <section class="bg-surface-container-lowest rounded-xl shadow-sm p-space-lg flex flex-col gap-space-md">
            {{-- Header & Search --}}
            <div class="flex flex-wrap items-center justify-between gap-space-md pb-space-sm border-b border-outline-variant">
                <div>
                    <div class="flex items-center gap-space-xs">
                        <h2 class="font-headline-lg text-headline-lg text-primary">Manajemen Laporan Kerusakan & Ticketing Sarpras</h2>
                        <span class="px-space-sm py-0.5 rounded font-data-mono text-data-mono bg-tertiary-fixed text-on-tertiary-fixed text-[11px] font-bold">UR11 • UR12</span>
                    </div>
                    <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
                        Kelola pergerakan status tiket perbaikan sarpras dan otomatisasi kunci kalender fasilitas (Mode Perbaikan).
                    </p>
                </div>
                <div class="flex items-center gap-space-sm">
                    <div class="relative">
                        <input type="text" placeholder="Cari tiket/ruang/kategori..." class="h-9 px-space-md pl-9 rounded-lg bg-surface-container-low text-on-surface font-body-sm border border-outline-variant/60 focus:border-primary focus:outline-none w-64">
                        <span class="material-symbols-outlined absolute left-2.5 top-2 text-[18px] text-on-surface-variant">search</span>
                    </div>
                </div>
            </div>

            {{-- Tabel Tiket Laporan Sarpras --}}
            <div class="overflow-x-auto w-full">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-container-low text-on-surface-variant font-label-sm text-label-sm uppercase">
                            <th class="py-space-md px-space-md font-semibold">Tiket & Pelapor</th>
                            <th class="py-space-md px-space-md font-semibold">Fasilitas & Kategori</th>
                            <th class="py-space-md px-space-md font-semibold">Deskripsi Malfungsi</th>
                            <th class="py-space-md px-space-md font-semibold">Status Tiket</th>
                            <th class="py-space-md px-space-md font-semibold">Status Fasilitas (UR12)</th>
                            <th class="py-space-md px-space-md font-semibold text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container-high/40 font-body-sm text-body-sm text-on-surface">
                        {{-- Row 1: Maintenance Locked --}}
                        <tr class="hover:bg-surface-container-low/60 transition-colors bg-error-container/5">
                            <td class="py-space-md px-space-md align-top">
                                <div class="font-data-mono text-data-mono font-bold text-primary">RPT-20240422-001</div>
                                <div class="font-label-sm text-label-sm text-on-surface">Rudi H. (Laboran)</div>
                                <span class="font-data-mono text-[11px] text-on-surface-variant">22 Apr 2024 08:30</span>
                            </td>
                            <td class="py-space-md px-space-md align-top">
                                <div class="font-label-lg text-label-lg font-semibold text-primary">Lab Hardware 2</div>
                                <span class="px-2 py-0.5 rounded bg-surface-container font-label-sm text-label-sm font-medium">Kelistrikan</span>
                            </td>
                            <td class="py-space-md px-space-md align-top max-w-xs">
                                <p class="line-clamp-2">Korsleting panel daya utama 3-phase di ruang praktikum robotika.</p>
                                <span class="text-primary text-[11px] font-semibold underline cursor-pointer">Foto_Kerusakan.jpg</span>
                            </td>
                            <td class="py-space-md px-space-md align-top">
                                <x-cava.status-badge status="Diproses" />
                            </td>
                            <td class="py-space-md px-space-md align-top">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full font-label-sm text-label-sm bg-error-container text-error font-bold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-error animate-pulse"></span>
                                    Dalam Perbaikan (Terkunci)
                                </span>
                            </td>
                            <td class="py-space-md px-space-md align-top text-right">
                                <button type="button" @click="openStatusModal('RPT-20240422-001', 'Lab Hardware 2', 'diproses')" class="px-space-md py-1.5 rounded-lg bg-primary text-on-primary font-label-md text-label-md font-semibold hover:bg-primary-container transition-colors shadow-sm">
                                    Kelola Tiket
                                </button>
                            </td>
                        </tr>

                        {{-- Row 2: Baru --}}
                        <tr class="hover:bg-surface-container-low/60 transition-colors">
                            <td class="py-space-md px-space-md align-top">
                                <div class="font-data-mono text-data-mono font-bold text-primary">RPT-20240420-009</div>
                                <div class="font-label-sm text-label-sm text-on-surface">Dimas Pratama (Mahasiswa)</div>
                                <span class="font-data-mono text-[11px] text-on-surface-variant">20 Apr 2024 10:14</span>
                            </td>
                            <td class="py-space-md px-space-md align-top">
                                <div class="font-label-lg text-label-lg font-semibold text-primary">Lab Komputasi Awan</div>
                                <span class="px-2 py-0.5 rounded bg-surface-container font-label-sm text-label-sm font-medium">Jaringan Internet</span>
                            </td>
                            <td class="py-space-md px-space-md align-top max-w-xs">
                                <p class="line-clamp-2">Port switch nomor 12 baris C tidak terhubung ke router gateway.</p>
                            </td>
                            <td class="py-space-md px-space-md align-top">
                                <x-cava.status-badge status="Baru" />
                            </td>
                            <td class="py-space-md px-space-md align-top">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full font-label-sm text-label-sm bg-secondary-container text-on-secondary-container font-semibold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span>
                                    Aktif (Dapat Dipesan)
                                </span>
                            </td>
                            <td class="py-space-md px-space-md align-top text-right">
                                <button type="button" @click="openStatusModal('RPT-20240420-009', 'Lab Komputasi Awan', 'baru')" class="px-space-md py-1.5 rounded-lg bg-primary text-on-primary font-label-md text-label-md font-semibold hover:bg-primary-container transition-colors shadow-sm">
                                    Kelola Tiket
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Modal Update Status Tiket & Kunci Fasilitas --}}
        <div x-show="showStatusModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-space-md bg-black/50 backdrop-blur-xs">
            <div @click.away="showStatusModal = false" class="bg-surface-container-lowest rounded-2xl shadow-xl max-w-lg w-full p-space-xl border border-outline-variant flex flex-col gap-space-md">
                <div class="flex items-center justify-between pb-space-sm border-b border-outline-variant">
                    <h3 class="font-headline-sm text-headline-sm text-primary flex items-center gap-2">
                        <span class="material-symbols-outlined">edit_note</span>
                        Update Status Penanganan Tiket
                    </h3>
                    <button type="button" @click="showStatusModal = false"><span class="material-symbols-outlined">close</span></button>
                </div>

                <!-- 
                  ROUTE: POST /petugas/reports/{id}/status
                  FUNGSI: Memperbarui pergerakan status laporan sarpras dan mengunci/membuka fasilitas di kalender ketersediaan
                -->
                <form action="{{ url('/petugas/reports/1/status') }}" method="POST" class="flex flex-col gap-space-md">
                    @csrf
                    <div>
                        <span class="font-body-sm text-body-sm text-on-surface-variant">Fasilitas: </span>
                        <strong class="text-on-surface" x-text="venueName + ' (' + reportId + ')'"></strong>
                    </div>

                    {{-- Pilihan Status Tiket --}}
                    <div class="flex flex-col gap-1">
                        <label class="font-label-sm text-label-sm text-on-surface font-semibold" for="rep-status-sel">Status Tiket Sarpras</label>
                        <select id="rep-status-sel" name="status" class="w-full h-10 px-space-md bg-surface-container-low rounded-lg text-body-md border border-outline-variant/60 focus:border-primary focus:outline-none">
                            <option value="baru">1. Baru Masuk (Pemeriksaan Awal)</option>
                            <option value="diproses" selected>2. Diproses (Teknisi Sedang Menangani)</option>
                            <option value="selesai">3. Selesai (Perbaikan Tuntas)</option>
                            <option value="ditolak">4. Ditolak (Bukan Kerusakan Teknis)</option>
                        </select>
                    </div>

                    {{-- Toggle Status Fasilitas: Dalam Perbaikan (UR12) --}}
                    <div class="p-space-md bg-error-container/20 rounded-xl border border-error-container flex items-start gap-space-sm">
                        <input type="checkbox" id="lock-facility" name="lock_facility" value="1" class="mt-1 w-4 h-4 rounded text-error focus:ring-error">
                        <label for="lock-facility" class="font-body-sm text-body-sm text-on-surface">
                            <strong class="text-error block">Tandai Fasilitas 'Dalam Perbaikan' (Maintenance Mode)</strong>
                            Otomatis memblokir dan mengunci slot fasilitas di kalender ketersediaan agar tidak dapat dipesan pengguna selama perbaikan.
                        </label>
                    </div>

                    {{-- Catatan Resolusi / Perbaikan --}}
                    <div class="flex flex-col gap-1">
                        <label class="font-label-sm text-label-sm text-on-surface font-semibold" for="rep-resolution">Catatan Resolusi / Log Penanganan</label>
                        <textarea id="rep-resolution" name="resolution_notes" rows="3" placeholder="Tuliskan tindakan teknisi, penggantian suku cadang, atau alasan penutupan tiket..." class="w-full p-space-md bg-surface-container-low rounded-lg text-body-sm border border-outline-variant/60 focus:border-primary focus:outline-none"></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-outline-variant/40">
                        <button type="button" @click="showStatusModal = false" class="px-space-md py-1.5 rounded-lg bg-surface-container text-on-surface font-label-md">Batal</button>
                        <button type="submit" class="px-space-md py-1.5 rounded-lg bg-primary text-on-primary font-label-md font-semibold hover:bg-primary-container transition-colors shadow-sm">Simpan Pembaruan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-petugas-layout>
