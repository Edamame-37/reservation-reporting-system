{{-- 
  NAMA FILE      : reservation-form.blade.php
  FUNGSIONALITAS : Halaman Formulir Pengajuan Reservasi Fasilitas (Mahasiswa / Dosen)
  DESKRIPSI      : Form interaktif pemilihan fasilitas, tanggal, picker rentang slot 30 menit (07:00 - 20:00 WIB), tujuan kegiatan, dan live conflict check.
  CARA KERJA     : Memanfaatkan layout <x-app-layout active="reservation-form">, mengirimkan data form via POST ke route reservasi.
--}}

<x-app-layout title="Pengajuan Reservasi Fasilitas" active="reservation-form">
    <!-- 
      ELEMEN       : Form Pengajuan Reservasi Baru (UR03, SFR03, SFR04)
      KEGUNAAN     : Memungkinkan mahasiswa dan dosen memesan slot ruang kampus dengan validasi anti-bentrok secara real-time.
      CARA KERJA   : Alpine.js mengelola state pemilihan slot 30 menit, menghitung total durasi, dan memperbarui kotak validasi preview seketika.
    -->
    <section class="bg-surface-container-lowest rounded-xl shadow-md p-space-xl mb-space-xl" id="section-form">
        {{-- Card Header --}}
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between pb-space-md mb-space-lg border-b border-outline-variant gap-2">
            <div class="flex items-center gap-space-sm">
                <div class="w-10 h-10 rounded-lg bg-primary-container text-on-primary flex items-center justify-center shadow-sm">
                    <span class="material-symbols-outlined text-[24px]">edit_calendar</span>
                </div>
                <div>
                    <h1 class="font-headline-md text-headline-md text-primary">Form Pengajuan Reservasi Ruang & Fasilitas</h1>
                    <p class="font-body-sm text-body-sm text-on-surface-variant">Modul UR03 & SFR03: Pilih fasilitas, tanggal, dan rentang slot 30 menit operasional (07:00 - 20:00 WIB).</p>
                </div>
            </div>
            <div class="flex items-center gap-space-xs font-data-mono text-data-mono text-secondary text-[11px] font-bold">
                <span class="w-2.5 h-2.5 rounded-full bg-secondary"></span>
                PENGECEKAN KONFLIK OTOMATIS: AKTIF
            </div>
        </div>

        <!-- 
          ROUTE: Mengirimkan form data input pemesanan via POST ke /user/reservations
          FUNGSI: Menyimpan permohonan reservasi baru ke dalam database untuk diverifikasi oleh Petugas Sarpras
        -->
        <form action="{{ url('/user/reservations') }}" method="POST">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-space-xl">
                {{-- Kolom Kiri: Input Pilihan (7 Kolom) --}}
                <div class="lg:col-span-7 flex flex-col gap-space-lg">
                    {{-- Pilihan Fasilitas --}}
                    <div class="flex flex-col gap-1">
                        <label class="font-label-lg text-label-lg text-on-surface flex items-center justify-between" for="venue-select">
                            <span>Fasilitas & Ruang Akademik</span>
                            <span class="font-label-sm text-label-sm text-on-surface-variant">Gedung Utama / Kampus Barat</span>
                        </label>
                        <select name="facility_id" id="venue-select" class="w-full h-10 px-space-md bg-surface-container-low rounded-lg text-on-surface font-body-md border border-outline-variant/60 focus:border-primary focus:bg-surface-container-lowest focus:outline-none">
                            <option value="1">Auditorium B.J. Habibie - Gedung A (Kapasitas: 450, Sound & Videotron)</option>
                            <option value="2">Lab Jaringan & Cloud Komputasi - Gedung C Lt. 2 (Kapasitas: 45)</option>
                            <option value="3">Smart Classroom 302 - Gedung B Lt. 3 (Kapasitas: 60)</option>
                            <option value="4">Aula Kemahasiswaan & Olahraga - Gedung PKM (Kapasitas: 500)</option>
                            <option value="5">Ruang Seminar Lantai 3 - Gedung A (Kapasitas: 120)</option>
                        </select>
                    </div>

                    {{-- Tanggal Pelaksanaan --}}
                    <div class="flex flex-col gap-1">
                        <label class="font-label-lg text-label-lg text-on-surface flex items-center justify-between" for="res-date">
                            <span>Tanggal Pelaksanaan Kegiatan</span>
                            <span class="font-label-sm text-label-sm text-secondary font-semibold">Tersedia untuk reservasi H-14 s/d H-2</span>
                        </label>
                        <input type="date" id="res-date" name="reservation_date" value="{{ date('Y-m-d', strtotime('+3 days')) }}" class="w-full h-10 px-space-md bg-surface-container-low rounded-lg font-body-md text-on-surface border border-outline-variant/60 focus:border-primary focus:bg-surface-container-lowest focus:outline-none">
                    </div>

                    {{-- Slot Picker 30 Menit Interaktif --}}
                    <x-cava.slot-matrix :selectable="true" venueName="Auditorium B.J. Habibie" />

                    {{-- Tujuan Penggunaan & PIC --}}
                    <div class="flex flex-col gap-1">
                        <label class="font-label-lg text-label-lg text-on-surface flex items-center justify-between" for="purpose">
                            <span>Tujuan Penggunaan & Penanggung Jawab Kegiatan</span>
                            <span class="font-label-sm text-label-sm text-on-surface-variant font-data-mono">Wajib diisi detail</span>
                        </label>
                        <textarea id="purpose" name="purpose" rows="3" placeholder="Contoh: Seminar Nasional Cloud Architecture Himpunan Mahasiswa TI - Estimasi 75 Peserta, PIC: Dimas Pratama (08123456789)" class="w-full p-space-md bg-surface-container-low rounded-lg font-body-md text-on-surface border border-outline-variant/60 focus:border-primary focus:bg-surface-container-lowest focus:outline-none"></textarea>
                    </div>
                </div>

                {{-- Kolom Kanan: Kotak Preview Real-Time & Submit (5 Kolom) --}}
                <div class="lg:col-span-5 flex flex-col justify-between bg-surface-container-low p-space-lg rounded-xl border border-outline-variant/40">
                    <div class="flex flex-col gap-space-md">
                        <div class="flex items-center justify-between pb-space-sm border-b border-outline-variant/40">
                            <span class="font-headline-sm text-headline-sm text-primary flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-secondary text-[20px]">verified</span>
                                Status Validasi Real-time
                            </span>
                            <span class="font-data-mono text-data-mono px-2 py-0.5 rounded bg-secondary-container text-on-secondary-container font-semibold text-[11px]">
                                SFR04 VALIDATED
                            </span>
                        </div>

                        {{-- Preview Box --}}
                        <div class="bg-surface-container-lowest p-space-md rounded-lg shadow-sm flex flex-col gap-space-sm">
                            <div class="flex items-start justify-between">
                                <div>
                                    <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Venue Terpilih</span>
                                    <div class="font-label-lg text-label-lg text-primary font-bold">Auditorium B.J. Habibie</div>
                                    <div class="font-body-sm text-body-sm text-on-surface-variant">Gedung Rektorat Baru, Lantai 1 & 2</div>
                                </div>
                                <span class="px-2 py-0.5 rounded font-data-mono text-data-mono bg-surface-container-high text-on-surface text-[11px]">
                                    KAP: 450 ORANG
                                </span>
                            </div>

                            <div class="pt-space-xs border-t border-outline-variant/30">
                                <span class="font-label-sm text-label-sm text-on-surface-variant uppercase">Slot Waktu Terpilih:</span>
                                <div class="font-headline-sm text-headline-sm text-secondary font-bold">
                                    09:00 - 12:00 WIB
                                </div>
                                <div class="font-body-sm text-body-sm text-secondary flex items-center gap-1 mt-0.5">
                                    <span class="material-symbols-outlined text-[16px]">check_circle</span>
                                    <span>Status: Siap Diajukan, Bebas Konflik</span>
                                </div>
                            </div>

                            <div class="pt-space-xs text-[12px] bg-surface-container-low p-space-sm rounded">
                                <div class="font-label-sm text-label-sm text-on-surface-variant mb-1 font-semibold">Peralatan Terintegrasi:</div>
                                <div class="flex flex-wrap gap-1">
                                    <span class="px-1.5 py-0.5 rounded bg-surface-container text-on-surface font-data-mono text-[10px]">Dual Screen Projector</span>
                                    <span class="px-1.5 py-0.5 rounded bg-surface-container text-on-surface font-data-mono text-[10px]">8 Wireless Mic</span>
                                    <span class="px-1.5 py-0.5 rounded bg-surface-container text-on-surface font-data-mono text-[10px]">Central AC</span>
                                    <span class="px-1.5 py-0.5 rounded bg-surface-container text-on-surface font-data-mono text-[10px]">WiFi Eduroam 1 Gbps</span>
                                </div>
                            </div>
                        </div>

                        {{-- Checkbox Persetujuan Tata Tertib --}}
                        <div class="flex items-start gap-space-sm pt-space-xs">
                            <input type="checkbox" id="terms-check" name="terms" required checked class="mt-1 w-4 h-4 rounded text-primary focus:ring-primary">
                            <label for="terms-check" class="font-body-sm text-body-sm text-on-surface leading-tight">
                                Saya menyetujui <span class="text-primary font-semibold underline cursor-pointer">Tata Tertib Penggunaan Sarana Kampus</span> dan bertanggung jawab penuh atas kebersihan dan keutuhan fasilitas selama sesi.
                            </label>
                        </div>
                    </div>

                    {{-- Tombol Submit --}}
                    <div class="pt-space-lg">
                        <button type="submit" class="w-full py-3 px-space-lg bg-primary hover:bg-primary-container text-on-primary font-label-lg text-label-lg rounded-lg shadow-md transition-all flex items-center justify-center gap-space-sm">
                            <span class="material-symbols-outlined text-[20px]">send</span>
                            <span>Kirim Pengajuan Reservasi</span>
                        </button>
                        <p class="font-label-sm text-label-sm text-center text-on-surface-variant mt-2">
                            Notifikasi persetujuan petugas sarpras akan tercatat di menu Riwayat Saya.
                        </p>
                    </div>
                </div>
            </div>
        </form>
    </section>
</x-app-layout>
