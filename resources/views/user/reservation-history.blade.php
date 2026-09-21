{{-- 
  NAMA FILE      : reservation-history.blade.php
  FUNGSIONALITAS : Halaman Lengkap Riwayat & Detail Reservasi Pengguna
  DESKRIPSI      : Menampilkan daftar seluruh tiket permohonan reservasi pengguna dengan filter status, pencarian tiket, modal detail lengkap, dan alur pembatalan mandiri H-1.
  CARA KERJA     : Menggunakan layout <x-app-layout active="reservation-history">, menyediakan modal dialog detail dan konfirmasi pembatalan mandiri via Alpine.js.
--}}

<x-app-layout title="Riwayat Lengkap Reservasi Saya" active="reservation-history">
    <div x-data="{
        showDetailModal: false,
        showCancelModal: false,
        search: '',
        activeTab: 'semua',
        selectedTicket: null,
        tickets: [
            {
                id: 'TKT-20240428-009',
                venue: 'Lab Komputasi Cloud & Jaringan',
                building: 'Gedung Lab Terpadu C, Lt. 2',
                date: '28 Apr 2024',
                time: '13:00 - 15:30 WIB',
                purpose: 'Praktikum Mandiri Pemrograman Web Lanjut (35 Mahasiswa)',
                status: 'pending',
                statusLabel: 'Menunggu Konfirmasi',
                officerNote: 'Sedang dalam antrean evaluasi staf sarpras.',
                canCancel: true
            },
            {
                id: 'TKT-20240424-001',
                venue: 'Auditorium Utama B.J. Habibie',
                building: 'Gedung Rektorat, Lt. 1 & 2',
                date: '24 Apr 2024',
                time: '09:00 - 12:00 WIB',
                purpose: 'Seminar Nasional Cloud Architecture Himpunan TI (75 Peserta)',
                status: 'approved',
                statusLabel: 'Disetujui Petugas',
                officerNote: 'Disetujui oleh Bambang S. Surat izin kegiatan telah diverifikasi valid.',
                canCancel: true
            },
            {
                id: 'TKT-20240418-034',
                venue: 'Smart Classroom 302',
                building: 'Gedung Kuliah Bersama B, Lt. 3',
                date: '18 Apr 2024',
                time: '10:00 - 12:00 WIB',
                purpose: 'Kuliah Tamu Industri AI & Machine Learning (50 Peserta)',
                status: 'approved',
                statusLabel: 'Selesai Digunakan',
                officerNote: 'Kegiatan selesai terlaksana tanpa insiden.',
                canCancel: false
            },
            {
                id: 'TKT-20240410-012',
                venue: 'Aula Serbaguna & Olahraga PKM',
                building: 'Pusat Kegiatan Mahasiswa, Lt. 1',
                date: '10 Apr 2024',
                time: '08:00 - 17:00 WIB',
                purpose: 'Festival Musik Dies Natalis BEM Universitas',
                status: 'rejected',
                statusLabel: 'Ditolak',
                officerNote: 'Jadwal bentrok dengan agenda resmi Wisuda Sarjana di aula utama.',
                canCancel: false
            },
            {
                id: 'TKT-20240329-005',
                venue: 'Ruang Seminar Lt. 3',
                building: 'Gedung Kuliah Terpadu A, Lt. 3',
                date: '29 Mar 2024',
                time: '14:00 - 16:00 WIB',
                purpose: 'Rapat Koordinasi Pengurus Ormawa BEM',
                status: 'cancelled',
                statusLabel: 'Dibatalkan Pengguna',
                officerNote: 'Dibatalkan oleh pemohon pada H-2 jadwal kegiatan.',
                canCancel: false
            }
        ],
        get filteredTickets() {
            return this.tickets.filter(t => {
                const matchSearch = t.id.toLowerCase().includes(this.search.toLowerCase()) || t.venue.toLowerCase().includes(this.search.toLowerCase()) || t.purpose.toLowerCase().includes(this.search.toLowerCase());
                const matchTab = this.activeTab === 'semua' || t.status === this.activeTab;
                return matchSearch && matchTab;
            });
        },
        openDetail(t) {
            this.selectedTicket = t;
            this.showDetailModal = true;
        },
        openCancel(t) {
            this.selectedTicket = t;
            this.showCancelModal = true;
        }
    }">

        {{-- Breadcrumb & Header --}}
        <div class="mb-6">
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-2 font-medium">
                <a href="{{ url('/user/dashboard') }}" class="hover:text-slate-900 transition">Dasbor Saya</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-slate-900">Riwayat Lengkap Reservasi</span>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Riwayat Lengkap Permohonan Reservasi</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Daftar seluruh reservasi aktif dan arsip peminjaman ruang Anda di universitas.</p>
                </div>
                <a href="{{ url('/user/reservation-form') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-900 text-white text-xs sm:text-sm font-semibold hover:bg-slate-800 transition shadow-xs">
                    <span class="material-symbols-outlined text-[18px]">add_circle</span>
                    <span>Ajukan Reservasi Baru</span>
                </a>
            </div>
        </div>

        {{-- Filter Tabs & Pencarian --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
            {{-- Tabs Status --}}
            <div class="flex items-center gap-1 overflow-x-auto w-full md:w-auto p-1 bg-slate-100 rounded-xl text-xs font-medium">
                <button type="button" @click="activeTab = 'semua'" :class="activeTab === 'semua' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                    Semua (5)
                </button>
                <button type="button" @click="activeTab = 'pending'" :class="activeTab === 'pending' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                    Menunggu (1)
                </button>
                <button type="button" @click="activeTab = 'approved'" :class="activeTab === 'approved' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                    Disetujui (2)
                </button>
                <button type="button" @click="activeTab = 'rejected'" :class="activeTab === 'rejected' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                    Ditolak (1)
                </button>
                <button type="button" @click="activeTab = 'cancelled'" :class="activeTab === 'cancelled' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                    Dibatalkan (1)
                </button>
            </div>

            {{-- Input Pencarian --}}
            <div class="relative w-full md:w-64">
                <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-[18px]">search</span>
                <input type="text" x-model="search" placeholder="Cari kode tiket / ruang..." class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-slate-900 focus:bg-white transition">
            </div>
        </div>

        {{-- Tabel Riwayat Lengkap --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden mb-8">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                            <th class="py-3 px-4">Kode Tiket</th>
                            <th class="py-3 px-4">Fasilitas & Lokasi</th>
                            <th class="py-3 px-4">Jadwal & Waktu</th>
                            <th class="py-3 px-4">Tujuan Penggunaan</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <template x-for="ticket in filteredTickets" :key="ticket.id">
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-800" x-text="ticket.id"></td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900" x-text="ticket.venue"></div>
                                    <div class="text-[11px] text-slate-500" x-text="ticket.building"></div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="text-slate-800 font-medium" x-text="ticket.date"></div>
                                    <div class="text-[11px] text-slate-500 font-mono" x-text="ticket.time"></div>
                                </td>
                                <td class="py-3.5 px-4 max-w-xs truncate text-slate-600" x-text="ticket.purpose"></td>
                                <td class="py-3.5 px-4">
                                    <span x-show="ticket.status === 'pending'" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs bg-amber-50 text-amber-800 border border-amber-200/60 font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        Menunggu Konfirmasi
                                    </span>
                                    <span x-show="ticket.status === 'approved'" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs bg-emerald-50 text-emerald-800 border border-emerald-200/60 font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                        Disetujui Petugas
                                    </span>
                                    <span x-show="ticket.status === 'rejected'" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs bg-rose-50 text-rose-700 border border-rose-200/60 font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        Ditolak
                                    </span>
                                    <span x-show="ticket.status === 'cancelled'" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs bg-slate-100 text-slate-600 border border-slate-200 font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                        Dibatalkan
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" @click="openDetail(ticket)" class="px-2.5 py-1 rounded-lg border border-slate-200 text-slate-700 font-semibold hover:bg-slate-50 transition">
                                            Detail
                                        </button>
                                        <button type="button" x-show="ticket.canCancel" @click="openCancel(ticket)" class="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 font-semibold hover:bg-rose-100 transition" title="Batalkan Reservasi (Maksimal H-1)">
                                            Batal
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </template>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Modal Detail Reservasi --}}
        <div x-show="showDetailModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="showDetailModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 relative">
                <button type="button" @click="showDetailModal = false" class="absolute top-4 right-4 p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100">
                    <span class="material-symbols-outlined">close</span>
                </button>

                <template x-if="selectedTicket">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="px-2.5 py-0.5 rounded-md bg-slate-100 font-mono text-xs font-bold text-slate-800" x-text="selectedTicket.id"></span>
                            <span class="text-xs font-semibold text-slate-500">Detail Permohonan</span>
                        </div>
                        <h2 class="text-lg font-bold text-slate-900" x-text="selectedTicket.venue"></h2>
                        <p class="text-xs text-slate-500 mb-4" x-text="selectedTicket.building"></p>

                        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 mb-4 space-y-2 text-xs">
                            <div class="flex justify-between">
                                <span class="text-slate-500">Tanggal Kegiatan:</span>
                                <span class="font-bold text-slate-900" x-text="selectedTicket.date"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Rentang Waktu:</span>
                                <span class="font-mono font-bold text-slate-900" x-text="selectedTicket.time"></span>
                            </div>
                            <div class="pt-2 border-t border-slate-200/60">
                                <span class="text-slate-500 block mb-0.5">Tujuan Penggunaan:</span>
                                <span class="text-slate-800" x-text="selectedTicket.purpose"></span>
                            </div>
                        </div>

                        <div class="mb-6">
                            <span class="text-xs font-bold text-slate-700 block mb-1">Catatan Verifikasi Petugas:</span>
                            <p class="text-xs text-slate-600 bg-blue-50/60 p-3 rounded-xl border border-blue-100" x-text="selectedTicket.officerNote"></p>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                            <button type="button" @click="showDetailModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50">
                                Tutup
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Modal Konfirmasi Pembatalan Mandiri H-1 --}}
        <div x-show="showCancelModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="showCancelModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 text-center">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mx-auto mb-3">
                    <span class="material-symbols-outlined text-[28px]">warning</span>
                </div>
                <h3 class="text-base font-bold text-slate-900 mb-1">Konfirmasi Pembatalan Reservasi</h3>
                <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                    Sesuai ketentuan, pembatalan mandiri hanya diizinkan maksimal <strong>H-1 sebelum jadwal</strong>. Slot waktu yang dilepas akan langsung terbuka kembali di kalender ketersediaan umum.
                </p>

                <div class="flex items-center justify-center gap-2">
                    <button type="button" @click="showCancelModal = false" class="flex-1 px-4 py-2 rounded-xl border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50">
                        Kembali
                    </button>
                    <!-- 
                      ROUTE: POST /user/reservations/{id}/cancel
                      FUNGSI: Membatalkan reservasi mandiri dengan validasi H-1 di server
                    -->
                    <form action="{{ url('/user/reservation-history') }}" method="GET" class="flex-1">
                        <button type="submit" @click="alert('Reservasi berhasil dibatalkan. Slot waktu telah dilepas kembali ke matriks umum.'); showCancelModal = false;" class="w-full px-4 py-2 rounded-xl bg-rose-600 text-white text-xs font-semibold hover:bg-rose-700 shadow-xs">
                            Ya, Batalkan
                        </button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
