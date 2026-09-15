{{-- 
  NAMA FILE      : reservation-history.blade.php
  FUNGSIONALITAS : Halaman Antarmuka Riwayat & Detail Reservasi Pengguna
  DESKRIPSI      : Menampilkan daftar seluruh tiket permohonan reservasi pengguna, status verifikasi petugas, modal detail, dan aksi pembatalan mandiri (H-1).
  CARA KERJA     : Menggunakan layout <x-app-layout active="reservation-history">, menyediakan popover konfirmasi pembatalan dan dialog detail interaktif via Alpine.js.
--}}

<x-app-layout title="Riwayat Reservasi Saya" active="reservation-history">
    <div x-data="{
        showDetailModal: false,
        showCancelPopover: false,
        selectedTicket: {
            code: 'TKT-20240424-001',
            venue: 'Auditorium B.J. Habibie',
            location: 'Gedung A, Lt. 3',
            date: '24 Apr 2024',
            time: '09:00 - 12:00 WIB',
            purpose: 'Seminar Nasional Cloud Architecture Himpunan TI (Estimasi 75 Peserta)',
            status: 'Menunggu Konfirmasi',
            officerNote: 'Dalam antrean verifikasi staf sarpras.'
        },
        openDetail(ticket) {
            this.selectedTicket = ticket;
            this.showDetailModal = true;
        }
    }">
        <!-- 
          ELEMEN       : Section Riwayat & Detail Reservasi (UR04, UR05)
          KEGUNAAN     : Memantau proses approval petugas dan membatalkan pesanan sebelum batas waktu H-1.
          CARA KERJA   : Merender tabel riwayat dengan filter status dan modal detail berbasis Alpine.js.
        -->
        <section class="bg-surface-container-lowest rounded-xl shadow-md p-space-xl mb-space-xl">
            {{-- Header & Search --}}
            <div class="flex flex-wrap items-center justify-between pb-space-md mb-space-md gap-space-md border-b border-outline-variant">
                <div class="flex items-center gap-space-sm">
                    <div class="w-10 h-10 rounded-lg bg-primary-container text-on-primary flex items-center justify-center shadow-sm">
                        <span class="material-symbols-outlined text-[24px]">history</span>
                    </div>
                    <div>
                        <h1 class="font-headline-md text-headline-md text-primary">Riwayat & Detail Reservasi Saya</h1>
                        <p class="font-body-sm text-body-sm text-on-surface-variant">Modul UR04 & UR05: Pantau status verifikasi petugas sarpras atau batalkan pesanan sebelum batas H-1.</p>
                    </div>
                </div>
                <div class="flex items-center gap-space-sm">
                    <div class="relative">
                        <input type="text" placeholder="Cari kode tiket / ruang..." class="h-9 px-space-md pl-8 bg-surface-container-low rounded-lg text-body-sm text-on-surface border border-outline-variant/60 focus:border-primary focus:bg-surface-container-lowest focus:outline-none">
                        <span class="material-symbols-outlined absolute left-2 top-2 text-[18px] text-on-surface-variant">search</span>
                    </div>
                </div>
            </div>

            {{-- Cancellation Warning Popover --}}
            <div x-show="showCancelPopover" x-cloak class="mb-space-md p-space-md bg-error-container text-on-error-container rounded-lg shadow-sm flex flex-wrap items-center justify-between gap-space-md">
                <div class="flex items-center gap-space-md">
                    <span class="material-symbols-outlined text-error text-[28px]">warning</span>
                    <div>
                        <div class="font-label-lg text-label-lg font-bold">Konfirmasi Pembatalan Reservasi TKT-20240424-001 (Batas Maksimal H-1)</div>
                        <div class="font-body-sm text-body-sm">Slot 09:00 - 12:00 WIB pada Auditorium B.J. Habibie akan segera dilepas kembali ke matriks ketersediaan umum. Tindakan ini tidak dapat dibatalkan.</div>
                    </div>
                </div>
                <div class="flex items-center gap-space-sm">
                    <button type="button" @click="showCancelPopover = false" class="px-space-md py-1.5 rounded-lg bg-surface-container-lowest text-on-surface font-label-md text-label-md hover:bg-surface-container transition-colors">
                        Batal
                    </button>
                    <!-- 
                      ROUTE: POST /user/reservations/{id}/cancel
                      FUNGSI: Membatalkan permohonan reservasi dengan validasi batas waktu H-1
                    -->
                    <form action="{{ url('/user/reservations/1/cancel') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="px-space-md py-1.5 rounded-lg bg-error text-on-error font-label-md text-label-md hover:bg-red-800 transition-colors shadow-sm font-semibold">
                            Ya, Batalkan Reservasi
                        </button>
                    </form>
                </div>
            </div>

            {{-- Tabel Daftar Reservasi --}}
            <div class="overflow-x-auto rounded-lg">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-surface-container-high text-on-surface-variant font-label-sm text-label-sm uppercase tracking-wider">
                            <th class="py-space-md px-space-md">Kode Tiket</th>
                            <th class="py-space-md px-space-md">Fasilitas & Lokasi</th>
                            <th class="py-space-md px-space-md">Tanggal & Waktu</th>
                            <th class="py-space-md px-space-md">Tujuan Penggunaan</th>
                            <th class="py-space-md px-space-md">Status Validasi</th>
                            <th class="py-space-md px-space-md text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-surface-container-high font-body-sm text-body-sm text-on-surface">
                        {{-- Row 1: Menunggu Konfirmasi --}}
                        <tr class="hover:bg-surface-container-low/60 transition-colors">
                            <td class="py-space-md px-space-md font-data-mono text-data-mono font-bold text-primary">TKT-20240424-001</td>
                            <td class="py-space-md px-space-md">
                                <div class="font-label-lg text-label-lg text-on-surface font-semibold">Auditorium B.J. Habibie</div>
                                <div class="font-body-sm text-body-sm text-on-surface-variant">Gedung A, Lt. 3</div>
                            </td>
                            <td class="py-space-md px-space-md">
                                <div class="font-label-md text-label-md text-on-surface font-medium">24 Apr 2024</div>
                                <div class="font-data-mono text-data-mono text-on-surface-variant">09:00 - 12:00 WIB</div>
                            </td>
                            <td class="py-space-md px-space-md max-w-xs">
                                <p class="font-body-sm text-body-sm text-on-surface truncate">Seminar Nasional Cloud Architecture Himpunan TI</p>
                                <span class="font-data-mono text-[11px] text-on-surface-variant">Estimasi 75 Peserta</span>
                            </td>
                            <td class="py-space-md px-space-md">
                                <x-cava.status-badge status="Menunggu Konfirmasi" />
                            </td>
                            <td class="py-space-md px-space-md text-right">
                                <div class="flex items-center justify-end gap-space-xs">
                                    <button type="button" @click="openDetail({
                                        code: 'TKT-20240424-001',
                                        venue: 'Auditorium B.J. Habibie',
                                        location: 'Gedung A, Lt. 3',
                                        date: '24 Apr 2024',
                                        time: '09:00 - 12:00 WIB (6 Slot 30m)',
                                        purpose: 'Seminar Nasional Cloud Architecture Himpunan TI (Estimasi 75 Peserta, PIC: Dimas Pratama)',
                                        status: 'Menunggu Konfirmasi',
                                        officerNote: 'Berkas SK sedang dievaluasi oleh petugas sarpras Zona Gedung A.'
                                    })" class="px-space-sm py-1 rounded bg-surface-container text-on-surface font-label-md text-label-md hover:bg-surface-container-high transition-colors flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[16px]">visibility</span> Detail
                                    </button>
                                    <button type="button" @click="showCancelPopover = !showCancelPopover" class="px-space-sm py-1 rounded bg-error-container text-on-error-container font-label-md text-label-md hover:bg-error hover:text-on-error transition-colors flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[16px]">cancel</span> Batalkan
                                    </button>
                                </div>
                            </td>
                        </tr>

                        {{-- Row 2: Disetujui Petugas --}}
                        <tr class="hover:bg-surface-container-low/60 transition-colors">
                            <td class="py-space-md px-space-md font-data-mono text-data-mono font-bold text-primary">TKT-20240418-034</td>
                            <td class="py-space-md px-space-md">
                                <div class="font-label-lg text-label-lg text-on-surface font-semibold">Lab Komputasi Awan</div>
                                <div class="font-body-sm text-body-sm text-on-surface-variant">Gedung C, Lt. 2</div>
                            </td>
                            <td class="py-space-md px-space-md">
                                <div class="font-label-md text-label-md text-on-surface font-medium">18 Apr 2024</div>
                                <div class="font-data-mono text-data-mono text-on-surface-variant">13:00 - 15:30 WIB</div>
                            </td>
                            <td class="py-space-md px-space-md max-w-xs">
                                <p class="font-body-sm text-body-sm text-on-surface truncate">Praktikum Mandiri Final Project Web</p>
                                <span class="font-data-mono text-[11px] text-on-surface-variant">35 Mahasiswa</span>
                            </td>
                            <td class="py-space-md px-space-md">
                                <x-cava.status-badge status="Disetujui" />
                            </td>
                            <td class="py-space-md px-space-md text-right">
                                <div class="flex items-center justify-end gap-space-xs">
                                    <button type="button" @click="openDetail({
                                        code: 'TKT-20240418-034',
                                        venue: 'Lab Komputasi Awan',
                                        location: 'Gedung C, Lt. 2',
                                        date: '18 Apr 2024',
                                        time: '13:00 - 15:30 WIB',
                                        purpose: 'Praktikum Mandiri Final Project Pemrograman Web',
                                        status: 'Disetujui',
                                        officerNote: 'Disetujui resmi oleh Pak Bambang S. (Petugas Sarpras Zona Gedung C).'
                                    })" class="px-space-sm py-1 rounded bg-surface-container text-on-surface font-label-md text-label-md hover:bg-surface-container-high transition-colors flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[16px]">visibility</span> Detail
                                    </button>
                                    <span class="px-space-sm py-1 rounded text-[11px] text-outline cursor-not-allowed">Selesai / Terkunci</span>
                                </div>
                            </td>
                        </tr>

                        {{-- Row 3: Ditolak --}}
                        <tr class="hover:bg-surface-container-low/60 transition-colors">
                            <td class="py-space-md px-space-md font-data-mono text-data-mono font-bold text-primary">TKT-20240410-012</td>
                            <td class="py-space-md px-space-md">
                                <div class="font-label-lg text-label-lg text-on-surface font-semibold">Aula Kemahasiswaan PKM</div>
                                <div class="font-body-sm text-body-sm text-on-surface-variant">Gedung PKM, Lt. 1</div>
                            </td>
                            <td class="py-space-md px-space-md">
                                <div class="font-label-md text-label-md text-on-surface font-medium">10 Apr 2024</div>
                                <div class="font-data-mono text-data-mono text-on-surface-variant">08:00 - 17:00 WIB</div>
                            </td>
                            <td class="py-space-md px-space-md max-w-xs">
                                <p class="font-body-sm text-body-sm text-on-surface truncate">Festival Musik Dies Natalis BEM</p>
                                <span class="font-data-mono text-[11px] text-error">Alasan: Bertabrakan dengan Agenda Wisuda</span>
                            </td>
                            <td class="py-space-md px-space-md">
                                <x-cava.status-badge status="Ditolak" />
                            </td>
                            <td class="py-space-md px-space-md text-right">
                                <button type="button" @click="openDetail({
                                    code: 'TKT-20240410-012',
                                    venue: 'Aula Kemahasiswaan PKM',
                                    location: 'Gedung PKM, Lt. 1',
                                    date: '10 Apr 2024',
                                    time: '08:00 - 17:00 WIB',
                                    purpose: 'Festival Musik Dies Natalis BEM Universitas',
                                    status: 'Ditolak',
                                    officerNote: 'Ditolak: Bertabrakan dengan agenda wisuda universitas gelombang II.'
                                })" class="px-space-sm py-1 rounded bg-surface-container text-on-surface font-label-md text-label-md hover:bg-surface-container-high transition-colors flex items-center gap-1 ml-auto">
                                    <span class="material-symbols-outlined text-[16px]">visibility</span> Detail
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        {{-- Popover Konfirmasi Pembatalan Mandiri (UR04) --}}
        <div x-show="showCancelPopover" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-space-md bg-black/50 backdrop-blur-xs">
            <div @click.away="showCancelPopover = false" class="bg-surface-container-lowest rounded-2xl shadow-xl max-w-md w-full p-space-xl border border-outline-variant flex flex-col gap-space-md">
                <div class="flex items-center justify-between pb-space-sm border-b border-outline-variant">
                    <h3 class="font-headline-sm text-headline-sm text-error flex items-center gap-2">
                        <span class="material-symbols-outlined">warning</span>
                        Konfirmasi Pembatalan
                    </h3>
                    <button type="button" @click="showCancelPopover = false"><span class="material-symbols-outlined">close</span></button>
                </div>

                <form action="#" method="POST" class="flex flex-col gap-space-md">
                    @csrf
                    <div class="p-space-sm bg-error-container text-on-error-container rounded-lg text-[12px]">
                        <strong>Perhatian:</strong> Pembatalan mandiri hanya diizinkan maksimal <strong>H-1</strong> sebelum jadwal penggunaan fasilitas (Sesuai UR04). Tindakan ini tidak dapat dibatalkan.
                    </div>
                    <p class="font-body-sm text-body-sm text-on-surface">
                        Apakah Anda yakin ingin membatalkan permohonan reservasi Anda untuk fasilitas ini?
                    </p>
                    
                    <div class="flex flex-col gap-1">
                        <label class="font-label-sm text-label-sm font-semibold text-on-surface" for="usr-cancel-reason">Alasan Pembatalan (Opsional)</label>
                        <textarea id="usr-cancel-reason" name="cancel_reason" rows="2" placeholder="Contoh: Perubahan jadwal kegiatan..." class="w-full p-space-md bg-surface-container-low rounded-lg text-body-sm border border-outline-variant/60 focus:border-primary focus:outline-none"></textarea>
                    </div>

                    <div class="flex justify-end gap-2 pt-2 border-t border-outline-variant/40">
                        <button type="button" @click="showCancelPopover = false" class="px-space-md py-1.5 rounded-lg bg-surface-container text-on-surface font-label-md">Batal</button>
                        <button type="button" @click="showCancelPopover = false" class="px-space-md py-1.5 rounded-lg bg-error text-on-error font-label-md font-semibold hover:bg-red-800 transition-colors">Eksekusi Pembatalan</button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Detail Modal Dialog --}}
        <div x-show="showDetailModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-space-md bg-black/50 backdrop-blur-xs">
            <div @click.away="showDetailModal = false" class="bg-surface-container-lowest rounded-2xl shadow-xl max-w-lg w-full p-space-xl border border-outline-variant flex flex-col gap-space-md">
                <div class="flex items-center justify-between pb-space-sm border-b border-outline-variant">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-primary text-[24px]">confirmation_number</span>
                        <h3 class="font-headline-sm text-headline-sm text-primary">Detail Reservasi</h3>
                    </div>
                    <button type="button" @click="showDetailModal = false" class="text-on-surface-variant hover:text-on-surface">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="flex flex-col gap-space-sm text-body-sm">
                    <div class="flex justify-between">
                        <span class="text-on-surface-variant">Kode Tiket:</span>
                        <span class="font-data-mono font-bold text-primary" x-text="selectedTicket.code"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-on-surface-variant">Fasilitas:</span>
                        <span class="font-semibold text-on-surface" x-text="selectedTicket.venue"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-on-surface-variant">Jadwal & Waktu:</span>
                        <span class="font-data-mono" x-text="selectedTicket.date + ' • ' + selectedTicket.time"></span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-on-surface-variant">Status Saat Ini:</span>
                        <span class="font-semibold" x-text="selectedTicket.status"></span>
                    </div>
                    <div class="p-space-sm bg-surface-container-low rounded-lg mt-1">
                        <div class="text-[11px] font-bold text-on-surface-variant uppercase">Tujuan Kegiatan:</div>
                        <div class="text-on-surface mt-0.5" x-text="selectedTicket.purpose"></div>
                    </div>
                    <div class="p-space-sm bg-surface-container-high/60 rounded-lg">
                        <div class="text-[11px] font-bold text-on-surface-variant uppercase">Catatan Petugas Sarpras:</div>
                        <div class="text-on-surface mt-0.5 italic" x-text="selectedTicket.officerNote"></div>
                    </div>
                </div>

                <div class="pt-space-sm flex justify-end">
                    <button type="button" @click="showDetailModal = false" class="px-space-lg py-2 rounded-lg bg-primary text-on-primary font-label-md text-label-md hover:bg-primary-container transition-colors">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
