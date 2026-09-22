{{-- 
  NAMA FILE      : reservation-management.blade.php
  FUNGSIONALITAS : Lembar Kerja Verifikasi & Approval Permohonan Reservasi Petugas Sarpras
  DESKRIPSI      : Menampilkan seluruh antrean reservasi masuk dengan deteksi bentrok otomatis (SFR06), modal persetujuan cepat, penolakan dengan alasan, serta pembatalan darurat (UR10).
  CARA KERJA     : Memanfaatkan layout <x-petugas-layout active="reservation-management">, mengelola modal interaktif penolakan dan pembatalan darurat via Alpine.js.
--}}

<x-petugas-layout title="Manajemen & Approval Reservasi" active="reservation-management">
    <div x-data="{
        showRejectModal: false,
        showCancelModal: false,
        applicantName: '',
        ticketCode: '',
        venueName: '',
        activeFilter: 'semua',
        search: '',
        openReject(name, code) {
            this.applicantName = name;
            this.ticketCode = code;
            this.showRejectModal = true;
        },
        openCancel(code, venue) {
            this.ticketCode = code;
            this.venueName = venue;
            this.showCancelModal = true;
        }
    }" class="flex flex-col gap-6">

        {{-- Breadcrumb & Header --}}
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-2 font-medium">
                <a href="{{ url('/petugas/dashboard') }}" class="hover:text-slate-900 transition">Dasbor Operasional</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-slate-900">Manajemen & Approval Reservasi</span>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Antrean Lengkap Persetujuan Reservasi</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Evaluasi berkas pengajuan, tujuan kegiatan, dan verifikasi validitas bentrok sistem sebelum memberikan persetujuan resmi.</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-700 shadow-xs">
                        Total Antrean: <strong class="text-slate-900">8 Pengajuan</strong>
                    </span>
                </div>
            </div>
        </div>

        {{-- Filter & Search Bar --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-1 p-1 bg-slate-100 rounded-xl text-xs font-medium w-full md:w-auto overflow-x-auto">
                <button type="button" @click="activeFilter = 'semua'" :class="activeFilter === 'semua' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                    Semua (8)
                </button>
                <button type="button" @click="activeFilter = 'pending'" :class="activeFilter === 'pending' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                    Menunggu (4)
                </button>
                <button type="button" @click="activeFilter = 'approved'" :class="activeFilter === 'approved' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                    Disetujui (3)
                </button>
                <button type="button" @click="activeFilter = 'conflict'" :class="activeFilter === 'conflict' ? 'bg-white text-rose-700 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                    Terdeteksi Bentrok (1)
                </button>
            </div>

            <div class="relative w-full md:w-64">
                <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-[18px]">search</span>
                <input type="text" x-model="search" placeholder="Cari pemohon / ruang / tiket..." class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-slate-900 focus:bg-white transition">
            </div>
        </div>

        {{-- Tabel Antrean Lengkap --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                            <th class="py-3 px-4">ID & Pemohon</th>
                            <th class="py-3 px-4">Fasilitas Diminta</th>
                            <th class="py-3 px-4">Tanggal & Slot Waktu</th>
                            <th class="py-3 px-4">Tujuan & Surat Izin</th>
                            <th class="py-3 px-4">Validasi Bentrok (SFR06)</th>
                            <th class="py-3 px-4 text-right">Keputusan Operasional</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        {{-- Row 1: SAFE HMIF --}}
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-4 align-top">
                                <div class="font-bold text-slate-900">Dimas Pratama</div>
                                <div class="text-[11px] text-slate-500">2110512044 • Mahasiswa TI</div>
                                <span class="text-[10px] text-blue-900 font-semibold mt-0.5 block">Himpunan Mahasiswa TI (HMIF)</span>
                            </td>
                            <td class="py-3.5 px-4 align-top">
                                <div class="font-bold text-slate-900">Auditorium B.J. Habibie</div>
                                <div class="text-[11px] text-slate-500">Gedung Rektorat (450 Kursi)</div>
                            </td>
                            <td class="py-3.5 px-4 align-top">
                                <div class="font-bold text-slate-900">24 Apr 2024</div>
                                <div class="text-[11px] text-slate-600 font-mono">09:00 - 12:00 WIB (6 slot)</div>
                            </td>
                            <td class="py-3.5 px-4 align-top max-w-xs">
                                <p class="text-slate-800">Seminar Nasional Cloud Computing HMIF</p>
                                <span class="inline-flex items-center gap-1 text-[11px] text-emerald-700 font-semibold mt-1">
                                    <span class="material-symbols-outlined text-[14px]">attachment</span>
                                    Surat Izin Dekanat Valid
                                </span>
                            </td>
                            <td class="py-3.5 px-4 align-top">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs bg-emerald-50 text-emerald-800 border border-emerald-200 font-semibold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                    100% Bebas Bentrok
                                </span>
                            </td>
                            <td class="py-3.5 px-4 align-top text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- 
                                      ROUTE: POST /petugas/reservations/{id}/approve
                                      FUNGSI: Menyetujui reservasi secara resmi dan mengunci slot
                                    -->
                                    <form action="{{ url('/petugas/reservation-management') }}" method="GET" class="inline">
                                        <button type="submit" @click="alert('Reservasi TKT-20240424-001 berhasil disetujui. Slot jadwal telah dikunci secara otomatis.')" class="px-3 py-1.5 rounded-xl bg-slate-900 text-white font-semibold hover:bg-slate-800 transition shadow-xs flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[15px]">check_circle</span>
                                            <span>Setujui</span>
                                        </button>
                                    </form>
                                    <button type="button" @click="openReject('Dimas Pratama (HMIF)', 'TKT-20240424-001')" class="px-2.5 py-1.5 rounded-xl border border-slate-200 text-rose-700 hover:bg-rose-50 transition font-semibold">
                                        Tolak
                                    </button>
                                </div>
                            </td>
                        </tr>

                        {{-- Row 2: BENTROK JADWAL --}}
                        <tr class="hover:bg-slate-50/70 transition bg-rose-50/20">
                            <td class="py-3.5 px-4 align-top">
                                <div class="font-bold text-slate-900">Dr. Ir. Hendra Prasetyo</div>
                                <div class="text-[11px] text-slate-500">NIP 198402112009121003</div>
                                <span class="text-[10px] text-slate-600 font-semibold mt-0.5 block">Prodi Sistem Informasi</span>
                            </td>
                            <td class="py-3.5 px-4 align-top">
                                <div class="font-bold text-slate-900">Lab Komputasi Cloud</div>
                                <div class="text-[11px] text-slate-500">Gedung C Lt. 2 (45 PC)</div>
                            </td>
                            <td class="py-3.5 px-4 align-top">
                                <div class="font-bold text-slate-900">24 Apr 2024</div>
                                <div class="text-[11px] text-rose-600 font-mono font-bold">10:00 - 13:00 WIB</div>
                            </td>
                            <td class="py-3.5 px-4 align-top max-w-xs">
                                <p class="text-slate-800">Workshop Sertifikasi Cloud Practitioner</p>
                                <span class="text-[11px] text-slate-500">Estimasi 40 Dosen & Asisten</span>
                            </td>
                            <td class="py-3.5 px-4 align-top">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs bg-rose-50 text-rose-700 border border-rose-200 font-bold">
                                    <span class="material-symbols-outlined text-[14px]">block</span>
                                    Bentrok: Praktikum Reguler
                                </span>
                            </td>
                            <td class="py-3.5 px-4 align-top text-right">
                                <button type="button" @click="openReject('Dr. Ir. Hendra Prasetyo', 'TKT-20240424-002')" class="px-3 py-1.5 rounded-xl bg-rose-600 text-white font-semibold hover:bg-rose-700 transition shadow-xs">
                                    Tolak Bentrok
                                </button>
                            </td>
                        </tr>

                        {{-- Row 3: DISETUJUI DENGAN OPSI BATAL DARURAT --}}
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-4 align-top">
                                <div class="font-bold text-slate-900">Fadhil Rahman</div>
                                <div class="text-[11px] text-slate-500">2010511032 • Mahasiswa Elektro</div>
                                <span class="text-[10px] text-blue-900 font-semibold mt-0.5 block">Himpunan Mahasiswa Elektro</span>
                            </td>
                            <td class="py-3.5 px-4 align-top">
                                <div class="font-bold text-slate-900">Smart Classroom 302</div>
                                <div class="text-[11px] text-slate-500">Gedung B Lt. 3 (60 Kursi)</div>
                            </td>
                            <td class="py-3.5 px-4 align-top">
                                <div class="font-bold text-slate-900">25 Apr 2024</div>
                                <div class="text-[11px] text-slate-600 font-mono">13:00 - 15:30 WIB</div>
                            </td>
                            <td class="py-3.5 px-4 align-top max-w-xs">
                                <p class="text-slate-800">Pelatihan Simulasi Mikrokontroler IoT</p>
                            </td>
                            <td class="py-3.5 px-4 align-top">
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs bg-emerald-50 text-emerald-800 border border-emerald-200 font-semibold">
                                    <span class="material-symbols-outlined text-[14px]">check</span>
                                    Sudah Disetujui
                                </span>
                            </td>
                            <td class="py-3.5 px-4 align-top text-right">
                                <button type="button" @click="openCancel('TKT-20240425-015', 'Smart Classroom 302')" class="px-3 py-1.5 rounded-xl border border-rose-200 text-rose-700 bg-rose-50 hover:bg-rose-100 transition font-semibold" title="Batal Darurat Petugas (UR10)">
                                    Batal Darurat (UR10)
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Modal Tolak Permohonan --}}
        <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="showRejectModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-base font-bold text-slate-900">Tolak Permohonan Reservasi</h3>
                    <button type="button" @click="showRejectModal = false" class="text-slate-400 hover:text-slate-700">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                    Menolak tiket <strong class="text-slate-900" x-text="ticketCode"></strong> untuk pemohon <strong class="text-slate-900" x-text="applicantName"></strong>. Masukkan alasan penolakan secara resmi:
                </p>
                <div class="mb-4">
                    <label for="reject-reason" class="block text-xs font-bold text-slate-700 mb-1">Alasan Penolakan (Wajib):</label>
                    <textarea id="reject-reason" rows="3" required placeholder="Contoh: Jadwal bertabrakan dengan agenda resmi universitas atau fasilitas sedang dalam perbaikan..." class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-slate-900"></textarea>
                </div>
                <div class="flex items-center justify-end gap-2">
                    <button type="button" @click="showRejectModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50">
                        Batal
                    </button>
                    <button type="button" @click="alert('Permohonan reservasi berhasil ditolak beserta alasan.'); showRejectModal = false;" class="px-4 py-2 rounded-xl bg-rose-600 text-white text-xs font-semibold hover:bg-rose-700 shadow-xs">
                        Kirim Penolakan
                    </button>
                </div>
            </div>
        </div>

        {{-- Modal Pembatalan Darurat oleh Petugas (UR10) --}}
        <div x-show="showCancelModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="showCancelModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-1.5 text-rose-600">
                        <span class="material-symbols-outlined">warning</span>
                        <span>Pembatalan Darurat (UR10)</span>
                    </h3>
                    <button type="button" @click="showCancelModal = false" class="text-slate-400 hover:text-slate-700">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                    Petugas berwenang membatalkan reservasi yang <strong>telah disetujui</strong> jika terjadi kondisi darurat (misal: pipa bocor, kabel terbakar, hewan liar). Alasan pembatalan wajib dicantumkan.
                </p>
                <div class="mb-4">
                    <label for="override-reason" class="block text-xs font-bold text-slate-700 mb-1">Alasan Pembatalan Darurat:</label>
                    <textarea id="override-reason" rows="3" required placeholder="Contoh: Terjadi kebocoran atap mendadak di ruangan, fasilitas harus segera diperbaiki..." class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-slate-900"></textarea>
                </div>
                <div class="flex items-center justify-end gap-2">
                    <button type="button" @click="showCancelModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50">
                        Kembali
                    </button>
                    <button type="button" @click="alert('Reservasi berhasil dibatalkan darurat. Notifikasi dan slot kalender telah disesuaikan.'); showCancelModal = false;" class="px-4 py-2 rounded-xl bg-rose-600 text-white text-xs font-semibold hover:bg-rose-700 shadow-xs">
                        Eksekusi Batal Darurat
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-petugas-layout>
