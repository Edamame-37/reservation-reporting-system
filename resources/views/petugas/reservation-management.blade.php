{{-- 
  NAMA FILE      : reservation-management.blade.php
  FUNGSIONALITAS : Halaman Manajemen & Approval Reservasi Petugas Sarpras
  DESKRIPSI      : Menampilkan antrean permohonan reservasi dengan deteksi bentrok otomatis (SFR06), aksi persetujuan/penolakan, serta fitur pembatalan darurat (UR10) ber-alasan.
  CARA KERJA     : Memanfaatkan layout <x-petugas-layout active="reservation-management">, mengelola modal interaktif penolakan dan pembatalan darurat via Alpine.js.
--}}

<x-petugas-layout title="Manajemen & Approval Reservasi" active="reservation-management">
    <div x-data="{
        showRejectModal: false,
        showOverrideModal: false,
        applicantName: '',
        ticketCode: '',
        openReject(name, code) {
            this.applicantName = name;
            this.ticketCode = code;
            this.showRejectModal = true;
        },
        openOverride(code, venue) {
            this.ticketCode = code;
            this.applicantName = venue;
            this.showOverrideModal = true;
        }
    }" class="flex flex-col gap-space-xl">
        <!-- 
          ELEMEN       : Section A: Antrian Persetujuan Reservasi (UR09, SFR06)
          KEGUNAAN     : Memeriksa dan memutuskan permohonan reservasi yang masuk dengan validasi bentrok jadwal otomatis.
          CARA KERJA   : Sistem menampilkan status bentrok secara otomatis. Petugas dapat menekan 'Setujui' atau membuka modal 'Tolak'.
        -->
        <section class="bg-surface-container-lowest rounded-xl shadow-sm p-space-lg flex flex-col gap-space-md">
            {{-- Header --}}
            <div class="flex flex-wrap items-center justify-between gap-space-md pb-space-sm border-b border-outline-variant">
                <div class="flex flex-col">
                    <div class="flex items-center gap-space-sm">
                        <h2 class="font-headline-lg text-headline-lg text-primary">Antrian Persetujuan Reservasi Fasilitas</h2>
                        <span class="px-space-sm py-0.5 rounded font-data-mono text-data-mono bg-surface-container text-on-surface text-[11px]">UR09 • SFR06</span>
                    </div>
                    <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
                        Evaluasi berkas pengajuan, tujuan kegiatan, dan verifikasi validitas bentrok sistem sebelum memberikan persetujuan resmi.
                    </p>
                </div>
                <div class="flex items-center gap-space-sm">
                    <div class="relative">
                        <input type="text" placeholder="Cari Mahasiswa/NIP/Ruang..." class="h-9 px-space-md pl-9 rounded-lg bg-surface-container-low text-on-surface font-body-sm placeholder:text-on-surface-variant border border-outline-variant/60 focus:outline-none focus:bg-surface-container-lowest w-64">
                        <span class="material-symbols-outlined absolute left-2.5 top-2 text-[18px] text-on-surface-variant">search</span>
                    </div>
                </div>
            </div>

            {{-- Tabel Antrian Approval --}}
            <div class="overflow-x-auto w-full">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-container-low text-on-surface-variant font-label-sm text-label-sm uppercase">
                            <th class="py-space-md px-space-md font-semibold">ID & Pemohon</th>
                            <th class="py-space-md px-space-md font-semibold">Fasilitas Diminta</th>
                            <th class="py-space-md px-space-md font-semibold">Tanggal & Slot Waktu</th>
                            <th class="py-space-md px-space-md font-semibold">Tujuan & Surat Izin</th>
                            <th class="py-space-md px-space-md font-semibold">Validasi Konflik (SFR06)</th>
                            <th class="py-space-md px-space-md font-semibold text-right">Keputusan Operasional</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container-high/40 font-body-sm text-body-sm text-on-surface">
                        {{-- Row 1: SAFE / HMIF --}}
                        <tr class="hover:bg-surface-container-low/60 transition-colors">
                            <td class="py-space-md px-space-md align-top">
                                <div class="flex flex-col">
                                    <span class="font-label-lg text-label-lg font-bold text-on-surface">Dimas Pratama</span>
                                    <span class="font-data-mono text-data-mono text-on-surface-variant text-[11px]">Mahasiswa TI • 2110512044</span>
                                    <span class="font-label-sm text-label-sm text-primary-container mt-1 font-semibold">Himpunan Mahasiswa TI</span>
                                </div>
                            </td>
                            <td class="py-space-md px-space-md align-top">
                                <div class="flex flex-col">
                                    <span class="font-label-lg text-label-lg font-semibold text-primary">Auditorium B.J. Habibie</span>
                                    <span class="font-body-sm text-body-sm text-on-surface-variant text-[12px]">Gedung Utama Lt. 3 • 350 Orang</span>
                                    <div class="flex items-center gap-1 mt-1">
                                        <span class="px-space-xs py-0.5 rounded font-data-mono text-[10px] bg-surface-container text-on-surface">TIER-1 AV</span>
                                        <span class="px-space-xs py-0.5 rounded font-data-mono text-[10px] bg-surface-container text-on-surface">MIC-WIRELESS (4)</span>
                                    </div>
                                </div>
                            </td>
                            <td class="py-space-md px-space-md align-top">
                                <div class="flex flex-col">
                                    <span class="font-label-md text-label-md font-bold text-on-surface">24 Apr 2024</span>
                                    <span class="font-data-mono text-data-mono text-secondary font-semibold">09:00 - 12:00 WIB</span>
                                    <span class="font-label-sm text-label-sm text-on-surface-variant text-[11px]">(6 slot 30m beruntun)</span>
                                </div>
                            </td>
                            <td class="py-space-md px-space-md align-top">
                                <div class="flex flex-col gap-1 max-w-xs">
                                    <span class="font-label-md text-label-md font-medium text-on-surface">Seminar Cloud Computing HMIF</span>
                                    <span class="inline-flex items-center gap-1 font-label-sm text-label-sm text-primary hover:underline cursor-pointer">
                                        <span class="material-symbols-outlined text-[14px]">description</span>
                                        Lampiran SK Terverifikasi.pdf
                                    </span>
                                </div>
                            </td>
                            <td class="py-space-md px-space-md align-top">
                                <span class="inline-flex items-center gap-1 px-space-sm py-1 rounded font-label-sm text-label-sm bg-secondary-fixed text-on-secondary-fixed font-bold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span>
                                    Jadwal Aman - Bebas Bentrok
                                </span>
                                <p class="font-body-sm text-body-sm text-on-surface-variant mt-1 text-[11px]">Tidak ada reservasi reguler pada slot ini</p>
                            </td>
                            <td class="py-space-md px-space-md align-top text-right">
                                <div class="flex items-center justify-end gap-space-xs">
                                    <!-- 
                                      ROUTE: POST /petugas/reservations/{id}/approve
                                      FUNGSI: Menyetujui pengajuan reservasi dan mengunci slot waktu secara permanen
                                    -->
                                    <form action="{{ url('/petugas/reservations/1/approve') }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-space-md py-1.5 rounded-lg bg-secondary text-on-secondary font-label-md text-label-md font-semibold hover:bg-secondary/90 transition-colors flex items-center gap-1 shadow-sm">
                                            <span class="material-symbols-outlined text-[16px]">check_circle</span> Setujui
                                        </button>
                                    </form>
                                    <button type="button" @click="openReject('Dimas Pratama', 'TKT-20240424-001')" class="px-space-md py-1.5 rounded-lg bg-surface-container text-error hover:bg-error-container hover:text-on-error-container transition-colors font-label-md text-label-md font-semibold flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[16px]">cancel</span> Tolak
                                    </button>
                                </div>
                            </td>
                        </tr>

                        {{-- Row 2: CONFLICT WARNING / DOSEN ELEKTRO --}}
                        <tr class="hover:bg-surface-container-low/60 transition-colors bg-error-container/10">
                            <td class="py-space-md px-space-md align-top">
                                <div class="flex flex-col">
                                    <span class="font-label-lg text-label-lg font-bold text-on-surface">Dr. Ir. Hendra Prasetyo</span>
                                    <span class="font-data-mono text-data-mono text-on-surface-variant text-[11px]">Dosen Elektro • 198203112008011003</span>
                                    <span class="font-label-sm text-label-sm text-primary-container mt-1 font-semibold">Fakultas Teknik Industri</span>
                                </div>
                            </td>
                            <td class="py-space-md px-space-md align-top">
                                <div class="flex flex-col">
                                    <span class="font-label-lg text-label-lg font-semibold text-primary">Smart Classroom 302</span>
                                    <span class="font-body-sm text-body-sm text-on-surface-variant text-[12px]">Gedung B Lt. 3 • 60 Orang</span>
                                </div>
                            </td>
                            <td class="py-space-md px-space-md align-top">
                                <div class="flex flex-col">
                                    <span class="font-label-md text-label-md font-bold text-on-surface">25 Apr 2024</span>
                                    <span class="font-data-mono text-data-mono text-error font-semibold">13:00 - 15:30 WIB</span>
                                    <span class="font-label-sm text-label-sm text-on-surface-variant text-[11px]">(5 slot 30m)</span>
                                </div>
                            </td>
                            <td class="py-space-md px-space-md align-top">
                                <div class="flex flex-col gap-1 max-w-xs">
                                    <span class="font-label-md text-label-md font-medium text-on-surface">Kuliah Umum Tamu IoT</span>
                                    <span class="inline-flex items-center gap-1 font-label-sm text-label-sm text-primary hover:underline cursor-pointer">
                                        <span class="material-symbols-outlined text-[14px]">description</span>
                                        Nota-Dinas-Dekan-FT.pdf
                                    </span>
                                </div>
                            </td>
                            <td class="py-space-md px-space-md align-top">
                                <span class="inline-flex items-center gap-1 px-space-sm py-1 rounded font-label-sm text-label-sm bg-error-container text-on-error-container font-bold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-error animate-pulse"></span>
                                    Bentrok Terdeteksi (2 Slot)
                                </span>
                                <p class="font-body-sm text-body-sm text-error mt-1 text-[11px]">Bentrok dengan: Praktikum Jaringan Sesi 3 (14:00 - 15:30 WIB)</p>
                            </td>
                            <td class="py-space-md px-space-md align-top text-right">
                                <div class="flex items-center justify-end gap-space-xs">
                                    <button type="button" disabled title="Persetujuan diblokir karena bentrok jadwal (SFR06)" class="px-space-md py-1.5 rounded-lg bg-surface-container-highest text-outline font-label-md text-label-md font-semibold cursor-not-allowed flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[16px]">block</span> Terkunci
                                    </button>
                                    <button type="button" @click="openReject('Dr. Hendra Prasetyo', 'TKT-20240425-088')" class="px-space-md py-1.5 rounded-lg bg-error-container text-on-error-container font-label-md text-label-md font-semibold flex items-center gap-1 hover:bg-error hover:text-on-error transition-colors">
                                        <span class="material-symbols-outlined text-[16px]">cancel</span> Tolak Bentrok
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- 
          ELEMEN       : Section B: Pembatalan Darurat / Override oleh Petugas (UR10)
          KEGUNAAN     : Memungkinkan petugas membatalkan paksa pemesanan yang sudah disetujui jika terjadi kondisi darurat (dengan alasan tertulis).
          CARA KERJA   : Membuka modal konfirmasi pembatalan darurat yang mewajibkan input alasan pembatalan resmi.
        -->
        <section class="bg-surface-container-lowest rounded-xl shadow-sm p-space-lg flex flex-col gap-space-md">
            <div class="flex flex-wrap items-center justify-between gap-space-md pb-space-sm border-b border-outline-variant">
                <div>
                    <div class="flex items-center gap-space-xs">
                        <h2 class="font-headline-lg text-headline-lg text-primary">Daftar Reservasi Disetujui & Hak Override Darurat</h2>
                        <span class="px-space-sm py-0.5 rounded font-data-mono text-data-mono bg-error-container text-on-error-container text-[11px] font-bold">UR10 OVERRIDE</span>
                    </div>
                    <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">
                        Kewenangan petugas sarpras untuk membatalkan reservasi aktif secara darurat (misal atap bocor atau bencana) wajib menyertakan alasan resmi.
                    </p>
                </div>
            </div>

            <div class="overflow-x-auto w-full">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-container-low text-on-surface-variant font-label-sm text-label-sm uppercase">
                            <th class="py-space-md px-space-md">ID Reservasi</th>
                            <th class="py-space-md px-space-md">Fasilitas & Lokasi</th>
                            <th class="py-space-md px-space-md">Jadwal Penggunaan</th>
                            <th class="py-space-md px-space-md">Pemohon</th>
                            <th class="py-space-md px-space-md">Status</th>
                            <th class="py-space-md px-space-md text-right">Aksi Hak Override</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container-high/40 font-body-sm text-body-sm text-on-surface">
                        <tr class="hover:bg-surface-container-low/60 transition-colors">
                            <td class="py-space-md px-space-md font-data-mono text-data-mono font-bold text-primary">TKT-20240428-099</td>
                            <td class="py-space-md px-space-md font-semibold">Aula Kemahasiswaan PKM</td>
                            <td class="py-space-md px-space-md font-data-mono">28 Apr 2024 • 08:00 - 16:00 WIB</td>
                            <td class="py-space-md px-space-md">BEM Universitas (Ketua: Fahri)</td>
                            <td class="py-space-md px-space-md">
                                <x-cava.status-badge status="Disetujui" />
                            </td>
                            <td class="py-space-md px-space-md text-right">
                                <button type="button" @click="openOverride('TKT-20240428-099', 'Aula Kemahasiswaan PKM')" class="px-space-md py-1.5 rounded-lg bg-error-container text-on-error-container hover:bg-error hover:text-on-error font-label-md text-label-md font-semibold transition-colors inline-flex items-center gap-1 shadow-sm">
                                    <span class="material-symbols-outlined text-[16px]">warning</span>
                                    <span>Batalkan Paksa (Override)</span>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Modal Tolak Reservasi --}}
        <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-space-md bg-black/50 backdrop-blur-xs">
            <div @click.away="showRejectModal = false" class="bg-surface-container-lowest rounded-2xl shadow-xl max-w-md w-full p-space-xl border border-outline-variant flex flex-col gap-space-md">
                <div class="flex items-center justify-between pb-space-sm border-b border-outline-variant">
                    <h3 class="font-headline-sm text-headline-sm text-error flex items-center gap-2">
                        <span class="material-symbols-outlined">cancel</span>
                        Tolak Pengajuan Reservasi
                    </h3>
                    <button type="button" @click="showRejectModal = false"><span class="material-symbols-outlined">close</span></button>
                </div>

                <!-- 
                  ROUTE: POST /petugas/reservations/{id}/reject
                  FUNGSI: Menolak permohonan reservasi dengan catatan alasan penolakan
                -->
                <form action="{{ url('/petugas/reservations/1/reject') }}" method="POST" class="flex flex-col gap-space-md">
                    @csrf
                    <div>
                        <span class="font-body-sm text-body-sm text-on-surface-variant">Pemohon: </span>
                        <span class="font-semibold text-on-surface" x-text="applicantName + ' (' + ticketCode + ')'"></span>
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-label-sm text-label-sm text-on-surface font-semibold" for="rej-reason">Alasan Resmi Penolakan</label>
                        <textarea id="rej-reason" name="rejection_reason" rows="3" required placeholder="Contoh: Bertabrakan dengan agenda wisuda universitas gelombang II..." class="w-full p-space-md bg-surface-container-low rounded-lg text-body-sm border border-outline-variant/60 focus:border-primary focus:outline-none"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="showRejectModal = false" class="px-space-md py-1.5 rounded-lg bg-surface-container text-on-surface font-label-md">Batal</button>
                        <button type="submit" class="px-space-md py-1.5 rounded-lg bg-error text-on-error font-label-md font-semibold hover:bg-red-800 transition-colors">Konfirmasi Tolak</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Modal Override Darurat (UR10) --}}
        <div x-show="showOverrideModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-space-md bg-black/50 backdrop-blur-xs">
            <div @click.away="showOverrideModal = false" class="bg-surface-container-lowest rounded-2xl shadow-xl max-w-md w-full p-space-xl border border-outline-variant flex flex-col gap-space-md">
                <div class="flex items-center justify-between pb-space-sm border-b border-outline-variant">
                    <h3 class="font-headline-sm text-headline-sm text-error flex items-center gap-2">
                        <span class="material-symbols-outlined">warning</span>
                        Override Pembatalan Darurat
                    </h3>
                    <button type="button" @click="showOverrideModal = false"><span class="material-symbols-outlined">close</span></button>
                </div>

                <!-- 
                  ROUTE: POST /petugas/reservations/{id}/override-cancel
                  FUNGSI: Pembatalan darurat sepihak oleh petugas sarpras disertai input alasan resmi
                -->
                <form action="{{ url('/petugas/reservations/1/override-cancel') }}" method="POST" class="flex flex-col gap-space-md">
                    @csrf
                    <div class="p-space-sm bg-error-container text-on-error-container rounded-lg text-[12px]">
                        Tindakan ini akan membatalkan reservasi aktif <strong x-text="ticketCode"></strong> dan mengirimkan notifikasi alasan pembatalan langsung kepada pemohon.
                    </div>
                    <div class="flex flex-col gap-1">
                        <label class="font-label-sm text-label-sm text-on-surface font-semibold" for="over-reason">Alasan Pembatalan Darurat (Wajib)</label>
                        <textarea id="over-reason" name="override_reason" rows="3" required placeholder="Contoh: Kebocoran instalasi pipa air di atas panggung aula mendadak membutuhkan perbaikan darurat..." class="w-full p-space-md bg-surface-container-low rounded-lg text-body-sm border border-outline-variant/60 focus:border-primary focus:outline-none"></textarea>
                    </div>
                    <div class="flex justify-end gap-2 pt-2">
                        <button type="button" @click="showOverrideModal = false" class="px-space-md py-1.5 rounded-lg bg-surface-container text-on-surface font-label-md">Batal</button>
                        <button type="submit" class="px-space-md py-1.5 rounded-lg bg-error text-on-error font-label-md font-semibold hover:bg-red-800 transition-colors">Eksekusi Override</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-petugas-layout>
