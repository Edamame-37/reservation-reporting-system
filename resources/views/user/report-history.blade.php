{{-- 
  NAMA FILE      : report-history.blade.php
  FUNGSIONALITAS : Halaman Lengkap Status & Riwayat Pelaporan Kerusakan
  DESKRIPSI      : Menampilkan daftar seluruh tiket laporan kerusakan fasilitas yang pernah diajukan pengguna beserta status penanganan, foto bukti, dan catatan teknisi sarpras.
  CARA KERJA     : Menggunakan layout <x-app-layout active="report-history">, menyediakan modal detail foto bukti dan catatan penanganan berbasis Alpine.js.
--}}

<x-app-layout title="Riwayat Lengkap Laporan Kerusakan" active="report-history">
    <div x-data="{
        showPhotoModal: false,
        activeTab: 'semua',
        selectedReport: null,
        reports: [
            {
                id: 'RPT-20240420-009',
                date: '20 Apr 2024 • 10:14 WIB',
                venue: 'Lab Komputasi Cloud & Jaringan',
                building: 'Gedung C, Lt. 2',
                category: 'Jaringan & Kabel',
                desc: 'Kabel LAN pada meja praktikum 12 dan 13 putus digigit hewan pengerat/kucing liar.',
                status: 'baru',
                statusLabel: 'Laporan Baru',
                note: 'Menunggu alokasi teknisi jaringan shift siang.',
                hasPhoto: true
            },
            {
                id: 'RPT-20240419-003',
                date: '19 Apr 2024 • 14:30 WIB',
                venue: 'Smart Classroom 302',
                building: 'Gedung Kuliah Bersama B, Lt. 3',
                category: 'Proyektor & Audio',
                desc: 'Layar proyektor berkedip dan lampu indikator menyala merah berkedip.',
                status: 'diproses',
                statusLabel: 'Sedang Ditangani',
                note: 'Sedang dalam penggantian lampu optik oleh teknisi sarpras Pak Bambang.',
                hasPhoto: true
            },
            {
                id: 'RPT-20240415-021',
                date: '15 Apr 2024 • 09:00 WIB',
                venue: 'Auditorium Utama B.J. Habibie',
                building: 'Gedung Rektorat, Lt. 1 & 2',
                category: 'AC & Pendingin',
                desc: 'Blower AC sisi barat mengeluarkan tetesan air dan tidak dingin maksimal.',
                status: 'selesai',
                statusLabel: 'Selesai Ditangani',
                note: 'Pembersihan filter dan pengisian freon telah rampung pada 16 Apr 2024.',
                hasPhoto: false
            },
            {
                id: 'RPT-20240402-005',
                date: '02 Apr 2024 • 16:45 WIB',
                venue: 'Aula Kemahasiswaan PKM',
                building: 'Pusat Kegiatan Mahasiswa, Lt. 1',
                category: 'Fasilitas Umum',
                desc: 'Engsel pintu toilet pria sayap barat terlepas dan tidak dapat dikunci rapat.',
                status: 'selesai',
                statusLabel: 'Selesai Ditangani',
                note: 'Engsel telah diganti baru oleh staf pertukangan sarpras.',
                hasPhoto: true
            }
        ],
        get filteredReports() {
            return this.reports.filter(r => {
                return this.activeTab === 'semua' || r.status === this.activeTab;
            });
        },
        openPhoto(r) {
            this.selectedReport = r;
            this.showPhotoModal = true;
        }
    }">

        {{-- Breadcrumb & Header --}}
        <div class="mb-6">
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-2 font-medium">
                <a href="{{ url('/user/dashboard') }}" class="hover:text-slate-900 transition">Dasbor Saya</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-slate-900">Status Laporan Kerusakan</span>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Status & Riwayat Pelaporan Kerusakan</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Pantau status penanganan teknisi sarpras terhadap laporan kendala sarana yang Anda kirimkan.</p>
                </div>
                <a href="{{ url('/user/report-form') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-900 text-white text-xs sm:text-sm font-semibold hover:bg-slate-800 transition shadow-xs">
                    <span class="material-symbols-outlined text-[18px]">add_circle</span>
                    <span>Buat Laporan Baru</span>
                </a>
            </div>
        </div>

        {{-- Tabs Filter --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs mb-6 flex items-center justify-between">
            <div class="flex items-center gap-1 p-1 bg-slate-100 rounded-xl text-xs font-medium overflow-x-auto">
                <button type="button" @click="activeTab = 'semua'" :class="activeTab === 'semua' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                    Semua Laporan (4)
                </button>
                <button type="button" @click="activeTab = 'baru'" :class="activeTab === 'baru' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                    Baru (1)
                </button>
                <button type="button" @click="activeTab = 'diproses'" :class="activeTab === 'diproses' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                    Sedang Diproses (1)
                </button>
                <button type="button" @click="activeTab = 'selesai'" :class="activeTab === 'selesai' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                    Selesai (2)
                </button>
            </div>
            <span class="text-xs text-slate-500 hidden sm:inline">Standar SLA Penanganan: &lt; 24 Jam</span>
        </div>

        {{-- Tabel Laporan Lengkap --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden mb-8">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                            <th class="py-3 px-4">ID Tiket & Waktu</th>
                            <th class="py-3 px-4">Fasilitas / Kategori</th>
                            <th class="py-3 px-4">Deskripsi Kerusakan</th>
                            <th class="py-3 px-4">Status Penanganan</th>
                            <th class="py-3 px-4">Catatan Resolusi Teknisi</th>
                            <th class="py-3 px-4 text-right">Foto Bukti</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="r in filteredReports" :key="r.id">
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3.5 px-4">
                                    <div class="font-mono font-bold text-slate-800" x-text="r.id"></div>
                                    <div class="text-[11px] text-slate-500" x-text="r.date"></div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900" x-text="r.venue"></div>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600 inline-block mt-0.5" x-text="r.category"></span>
                                </td>
                                <td class="py-3.5 px-4 max-w-xs text-slate-700" x-text="r.desc"></td>
                                <td class="py-3.5 px-4">
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
                                <td class="py-3.5 px-4 text-slate-600 italic text-[11px]" x-text="r.note"></td>
                                <td class="py-3.5 px-4 text-right">
                                    <button type="button" x-show="r.hasPhoto" @click="openPhoto(r)" class="px-2.5 py-1 rounded-lg border border-slate-200 text-slate-700 font-semibold hover:bg-slate-50 transition inline-flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">image</span>
                                        <span>Lihat</span>
                                    </button>
                                    <span x-show="!r.hasPhoto" class="text-[11px] text-slate-400">Tidak ada</span>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Modal Foto Bukti --}}
        <div x-show="showPhotoModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="showPhotoModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-bold text-slate-900">Lampiran Foto Bukti Kerusakan</h3>
                    <button type="button" @click="showPhotoModal = false" class="text-slate-400 hover:text-slate-700">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <template x-if="selectedReport">
                    <div>
                        <div class="w-full h-56 bg-slate-100 rounded-2xl overflow-hidden border border-slate-200 mb-3 flex items-center justify-center relative">
                            <img src="https://images.unsplash.com/photo-1581092160607-ee22621dd758?auto=format&fit=crop&w=600&q=80" alt="Bukti Kerusakan" class="w-full h-full object-cover">
                        </div>
                        <div class="text-xs text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-100">
                            <span class="font-bold text-slate-800" x-text="selectedReport.id"></span>: <span x-text="selectedReport.desc"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>

    </div>
</x-app-layout>
