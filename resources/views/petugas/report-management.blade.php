{{-- 
  NAMA FILE      : report-management.blade.php
  FUNGSIONALITAS : Lembar Kerja Manajemen Tiket Kerusakan Fasilitas Petugas Sarpras
  DESKRIPSI      : Menampilkan seluruh antrean tiket kerusakan fasilitas kampus, mengubah status progres (Baru/Diproses/Selesai), menginput catatan resolusi, serta mengunci fasilitas ke status 'Dalam Perbaikan' (UR11, UR12).
  CARA KERJA     : Memanfaatkan layout <x-petugas-layout active="report-management">, mengelola modal pembaruan status dan sinkronisasi kalender maintenance via Alpine.js.
--}}

<x-petugas-layout title="Manajemen Tiket Kerusakan Fasilitas" active="report-management">
    <div x-data="{
        showStatusModal: false,
        activeTab: 'semua',
        search: '',
        selectedReport: null,
        reports: [
            {
                id: 'RPT-20240422-001',
                reporter: 'Rudi H. (Laboran)',
                date: '22 Apr 2024 • 08:30 WIB',
                venue: 'Lab Hardware 2',
                building: 'Gedung C, Lt. 1',
                category: 'Kelistrikan',
                desc: 'Korsleting panel daya utama 3-phase di ruang praktikum robotika.',
                status: 'diproses',
                facilityLocked: true,
                resolutionNote: 'Teknisi PLN kampus sedang mengganti MCB utama.'
            },
            {
                id: 'RPT-20240420-009',
                reporter: 'Dimas Pratama (Mahasiswa)',
                date: '20 Apr 2024 • 10:14 WIB',
                venue: 'Lab Komputasi Cloud',
                building: 'Gedung C, Lt. 2',
                category: 'Jaringan & Kabel',
                desc: 'Kabel LAN meja praktikum 12 & 13 putus terpotong hewan liar.',
                status: 'baru',
                facilityLocked: false,
                resolutionNote: 'Menunggu alokasi teknisi jaringan shift siang.'
            },
            {
                id: 'RPT-20240419-003',
                reporter: 'Siti Nurhaliza (Mahasiswa)',
                date: '19 Apr 2024 • 14:30 WIB',
                venue: 'Smart Classroom 302',
                building: 'Gedung B, Lt. 3',
                category: 'Proyektor & Audio',
                desc: 'Lampu proyektor berkedip merah dan mati total saat perkuliahan.',
                status: 'diproses',
                facilityLocked: true,
                resolutionNote: 'Penggantian lampu optik dan pengujian display.'
            },
            {
                id: 'RPT-20240417-001',
                reporter: 'Ahmad Faisal (Staf)',
                date: '17 Apr 2024 • 08:20 WIB',
                venue: 'Auditorium B.J. Habibie',
                building: 'Gedung Rektorat, Lt. 1',
                category: 'AC & Pendingin',
                desc: 'AC central blower sisi barat mengeluarkan tetesan air.',
                status: 'baru',
                facilityLocked: false,
                resolutionNote: 'Jadwal servis teknisi pendingin pukul 13.00 WIB.'
            },
            {
                id: 'RPT-20240415-021',
                reporter: 'Dr. Ir. Hendra (Dosen)',
                date: '15 Apr 2024 • 09:00 WIB',
                venue: 'Ruang Rapat Senat',
                building: 'Gedung Rektorat, Lt. 3',
                category: 'Tata Suara',
                desc: 'Mic wireless mimbar podium feedback dan mendengung keras.',
                status: 'selesai',
                facilityLocked: false,
                resolutionNote: 'Kabel receiver audio telah diperbaiki dan frekuensi disesuaikan.'
            }
        ],
        get filteredReports() {
            return this.reports.filter(r => {
                const matchSearch = r.id.toLowerCase().includes(this.search.toLowerCase()) || r.venue.toLowerCase().includes(this.search.toLowerCase()) || r.desc.toLowerCase().includes(this.search.toLowerCase());
                const matchTab = this.activeTab === 'semua' || r.status === this.activeTab;
                return matchSearch && matchTab;
            });
        },
        openUpdate(r) {
            this.selectedReport = JSON.parse(JSON.stringify(r));
            this.showStatusModal = true;
        },
        saveStatus() {
            const idx = this.reports.findIndex(x => x.id === this.selectedReport.id);
            if (idx !== -1) {
                this.reports[idx].status = this.selectedReport.status;
                this.reports[idx].facilityLocked = this.selectedReport.facilityLocked;
                this.reports[idx].resolutionNote = this.selectedReport.resolutionNote;
            }
            this.showStatusModal = false;
            alert('Status tiket ' + this.selectedReport.id + ' berhasil diperbarui! Sinkronisasi status ruang ke kalender telah tersimpan.');
        }
    }" class="flex flex-col gap-6">

        {{-- Breadcrumb & Header --}}
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-2 font-medium">
                <a href="{{ url('/petugas/dashboard') }}" class="hover:text-slate-900 transition">Dasbor Operasional</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-slate-900">Manajemen Tiket Kerusakan</span>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Manajemen Laporan Kerusakan & Fasilitas</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Pantau laporan kerusakan sivitas, kelola penugasan teknisi, dan kunci jadwal ruang yang memerlukan pemeliharaan (UR11, UR12).</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-700 shadow-xs">
                        Total Tiket: <strong class="text-slate-900">5 Laporan</strong>
                    </span>
                </div>
            </div>
        </div>

        {{-- Filter & Search Bar --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-1 p-1 bg-slate-100 rounded-xl text-xs font-medium w-full md:w-auto overflow-x-auto">
                <button type="button" @click="activeTab = 'semua'" :class="activeTab === 'semua' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                    Semua (5)
                </button>
                <button type="button" @click="activeTab = 'baru'" :class="activeTab === 'baru' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                    Baru (2)
                </button>
                <button type="button" @click="activeTab = 'diproses'" :class="activeTab === 'diproses' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                    Sedang Diproses (2)
                </button>
                <button type="button" @click="activeTab = 'selesai'" :class="activeTab === 'selesai' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                    Selesai (1)
                </button>
            </div>

            <div class="relative w-full md:w-64">
                <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-[18px]">search</span>
                <input type="text" x-model="search" placeholder="Cari tiket / ruang / pelapor..." class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-slate-900 focus:bg-white transition">
            </div>
        </div>

        {{-- Tabel Tiket Kerusakan Lengkap --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                            <th class="py-3 px-4">Tiket & Pelapor</th>
                            <th class="py-3 px-4">Fasilitas & Kategori</th>
                            <th class="py-3 px-4">Deskripsi Kerusakan</th>
                            <th class="py-3 px-4">Status Tiket</th>
                            <th class="py-3 px-4">Status Kalender (UR12)</th>
                            <th class="py-3 px-4 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="r in filteredReports" :key="r.id">
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3.5 px-4 align-top">
                                    <div class="font-mono font-bold text-slate-800" x-text="r.id"></div>
                                    <div class="text-[11px] text-slate-700 font-medium" x-text="r.reporter"></div>
                                    <span class="text-[10px] text-slate-400" x-text="r.date"></span>
                                </td>
                                <td class="py-3.5 px-4 align-top">
                                    <div class="font-bold text-slate-900" x-text="r.venue"></div>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600 inline-block mt-0.5" x-text="r.category"></span>
                                </td>
                                <td class="py-3.5 px-4 align-top max-w-xs text-slate-700" x-text="r.desc"></td>
                                <td class="py-3.5 px-4 align-top">
                                    <span x-show="r.status === 'baru'" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs bg-amber-50 text-amber-800 border border-amber-200/60 font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        Laporan Baru
                                    </span>
                                    <span x-show="r.status === 'diproses'" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs bg-sky-50 text-sky-800 border border-sky-200/60 font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-pulse"></span>
                                        Sedang Diproses
                                    </span>
                                    <span x-show="r.status === 'selesai'" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs bg-emerald-50 text-emerald-800 border border-emerald-200/60 font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                        Selesai Ditangani
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 align-top">
                                    <span x-show="r.facilityLocked" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs bg-rose-50 text-rose-700 border border-rose-200 font-semibold">
                                        <span class="material-symbols-outlined text-[13px]">lock</span>
                                        Terkunci (Perbaikan)
                                    </span>
                                    <span x-show="!r.facilityLocked" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs bg-emerald-50 text-emerald-800 border border-emerald-200 font-medium">
                                        <span class="material-symbols-outlined text-[13px]">check</span>
                                        Kalender Terbuka
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 align-top text-right">
                                    <button type="button" @click="openUpdate(r)" class="px-3 py-1 rounded-xl bg-slate-900 text-white font-semibold hover:bg-slate-800 transition shadow-xs">
                                        Perbarui Status
                                    </button>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Modal Perbarui Status Tiket & Kunci Fasilitas --}}
        <div x-show="showStatusModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="showStatusModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-base font-bold text-slate-900">Perbarui Status Penanganan & Fasilitas</h3>
                    <button type="button" @click="showStatusModal = false" class="text-slate-400 hover:text-slate-700">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <template x-if="selectedReport">
                    <div class="space-y-4">
                        <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100 text-xs">
                            <span class="text-slate-400 block mb-0.5" x-text="selectedReport.id"></span>
                            <span class="font-bold text-slate-900 text-sm block" x-text="selectedReport.venue"></span>
                            <p class="text-slate-600 mt-1" x-text="selectedReport.desc"></p>
                        </div>

                        {{-- Pilihan Status Tiket --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Status Progres Tiket (UR11):</label>
                            <select x-model="selectedReport.status" class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-slate-900">
                                <option value="baru">Laporan Baru</option>
                                <option value="diproses">Sedang Diproses Teknisi</option>
                                <option value="selesai">Selesai Ditangani</option>
                            </select>
                        </div>

                        {{-- Toggle Fasilitas Dalam Perbaikan (UR12) --}}
                        <div class="p-3.5 rounded-2xl bg-amber-50/70 border border-amber-200/80">
                            <label class="flex items-start gap-2.5 cursor-pointer">
                                <input type="checkbox" x-model="selectedReport.facilityLocked" class="mt-0.5 rounded text-slate-900 focus:ring-slate-900 w-4 h-4">
                                <div>
                                    <span class="text-xs font-bold text-amber-950 block">Tandai Fasilitas 'Dalam Perbaikan' (Kunci Jadwal UR12)</span>
                                    <span class="text-[11px] text-amber-800 leading-snug block mt-0.5">
                                        Fasilitas akan otomatis tidak dapat dipesan di kalender umum agar tidak terjadi bentrok atau pemesanan ruang yang sedang rusak.
                                    </span>
                                </div>
                            </label>
                        </div>

                        {{-- Catatan Resolusi --}}
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1.5">Catatan Resolusi / Tindakan Teknisi:</label>
                            <textarea x-model="selectedReport.resolutionNote" rows="3" placeholder="Contoh: Komponen telah diganti baru, tata suara diuji dan normal..." class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-slate-900"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                            <button type="button" @click="showStatusModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50">
                                Batal
                            </button>
                            <button type="button" @click="saveStatus" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 shadow-xs">
                                Simpan Perubahan
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

    </div>
</x-petugas-layout>
