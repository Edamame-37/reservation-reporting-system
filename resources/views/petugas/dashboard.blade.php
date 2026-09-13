{{-- 
  NAMA FILE      : dashboard.blade.php
  FUNGSIONALITAS : Halaman Antarmuka Dasbor Utama Petugas Sarpras CAVA
  DESKRIPSI      : Menampilkan metrik KPI operasional sarpras (Antrean Menunggu, Laporan Kerusakan, Fasilitas Maintenance) dan ringkasan antrian persetujuan cepat.
  CARA KERJA     : Memanfaatkan layout <x-petugas-layout active="dashboard">, menyajikan dasbor monitoring operasional real-time.
--}}

<x-petugas-layout title="Dasbor Petugas Sarpras" active="dashboard">
    <!-- 
      ELEMEN       : Overview Stats Metrik Sarpras (UR08)
      KEGUNAAN     : Menyajikan jumlah antrean persetujuan dan laporan aktif secara instan bagi petugas piket.
      CARA KERJA   : Merender 3 komponen stat-card CAVA dengan status data dan target SLA.
    -->
    <section class="grid grid-cols-1 md:grid-cols-3 gap-space-lg">
        {{-- Card 1: Antrean Reservasi Menunggu --}}
        <x-cava.stat-card 
            title="Antrian Reservasi Menunggu"
            code="UR08-A"
            value="8"
            valueLabel="Pengajuan Butuh Verifikasi"
            footerBadge="+3 Pengajuan Hari Ini"
            footerText="SLA Respon < 2 Jam"
            icon="pending_actions"
            variant="primary"
        />

        {{-- Card 2: Antrean Laporan Kerusakan --}}
        <x-cava.stat-card 
            title="Antrian Laporan Kerusakan"
            code="UR08-B"
            value="5"
            valueLabel="Laporan Aktif"
            footerBadge="2 Darurat Prioritas"
            footerText="Prioritas Tinggi"
            icon="build_circle"
            variant="error"
        />

        {{-- Card 3: Fasilitas Dalam Perbaikan --}}
        <x-cava.stat-card 
            title="Fasilitas Dalam Perbaikan"
            code="UR08-C"
            value="2"
            valueLabel="Ruangan Non-Aktif (Locked)"
            footerBadge="Lab Hardware 2 & R. Senat"
            footerText="Maintenance Mode"
            icon="domain_disabled"
            variant="tertiary"
        />
    </section>

    {{-- Antrian Verifikasi Cepat (Quick Approval Preview) --}}
    <section class="bg-surface-container-lowest rounded-xl shadow-sm p-space-lg flex flex-col gap-space-md">
        <div class="flex flex-wrap items-center justify-between gap-space-md pb-space-sm border-b border-outline-variant">
            <div>
                <div class="flex items-center gap-space-xs">
                    <span class="font-headline-lg text-headline-lg text-primary">Antrian Prioritas Verifikasi Reservasi</span>
                    <span class="px-space-sm py-0.5 rounded font-data-mono text-data-mono bg-surface-container text-on-surface text-[11px]">UR09 • SFR06</span>
                </div>
                <p class="font-body-sm text-body-sm text-on-surface-variant mt-0.5">Permohonan masuk yang memerlukan evaluasi jadwal bebas bentrok dan surat izin.</p>
            </div>
            <a href="{{ url('/petugas/reservation-management') }}" class="px-space-md py-2 rounded-lg bg-primary text-on-primary font-label-md text-label-md hover:bg-primary-container transition-colors shadow-sm flex items-center gap-1">
                <span>Buka Seluruh Antrean</span>
                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
            </a>
        </div>

        {{-- Tabel Antrian Cepat --}}
        <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container-low text-on-surface-variant font-label-sm text-label-sm uppercase">
                        <th class="py-space-md px-space-md font-semibold">Pemohon & Lembaga</th>
                        <th class="py-space-md px-space-md font-semibold">Fasilitas Diminta</th>
                        <th class="py-space-md px-space-md font-semibold">Jadwal Slot 30m</th>
                        <th class="py-space-md px-space-md font-semibold">Validasi Konflik (SFR06)</th>
                        <th class="py-space-md px-space-md font-semibold text-right">Keputusan</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container-high/40 font-body-sm text-body-sm text-on-surface">
                    <tr class="hover:bg-surface-container-low/60 transition-colors">
                        <td class="py-space-md px-space-md">
                            <div class="font-label-lg text-label-lg font-bold text-on-surface">Dimas Pratama</div>
                            <div class="font-data-mono text-data-mono text-on-surface-variant text-[11px]">Mahasiswa TI • 2110512044</div>
                            <span class="font-label-sm text-label-sm text-primary-container mt-0.5 block">Himpunan Mahasiswa Informatika</span>
                        </td>
                        <td class="py-space-md px-space-md">
                            <div class="font-label-lg text-label-lg font-semibold text-primary">Auditorium B.J. Habibie</div>
                            <div class="font-body-sm text-body-sm text-on-surface-variant text-[12px]">Gedung Utama Lantai 3 (350 Orang)</div>
                        </td>
                        <td class="py-space-md px-space-md">
                            <div class="font-label-md text-label-md font-bold text-on-surface">24 Apr 2024</div>
                            <div class="font-data-mono text-data-mono text-secondary font-semibold">09:00 - 12:00 WIB</div>
                            <span class="font-label-sm text-label-sm text-on-surface-variant text-[11px]">(6 slot 30m)</span>
                        </td>
                        <td class="py-space-md px-space-md">
                            <span class="inline-flex items-center gap-1 px-space-sm py-1 rounded font-label-sm text-label-sm bg-secondary-fixed text-on-secondary-fixed font-bold">
                                <span class="w-1.5 h-1.5 rounded-full bg-secondary"></span>
                                100% Bebas Bentrok
                            </span>
                        </td>
                        <td class="py-space-md px-space-md text-right">
                            <div class="flex items-center justify-end gap-space-xs">
                                <!-- 
                                  ROUTE: POST /petugas/reservations/{id}/approve
                                  FUNGSI: Menyetujui reservasi secara resmi dan mengunci slot kalender
                                -->
                                <form action="{{ url('/petugas/reservations/1/approve') }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-space-md py-1.5 rounded-lg bg-secondary text-on-secondary font-label-md text-label-md font-semibold hover:bg-secondary/90 transition-colors flex items-center gap-1 shadow-sm">
                                        <span class="material-symbols-outlined text-[16px]">check_circle</span> Setujui
                                    </button>
                                </form>
                                <a href="{{ url('/petugas/reservation-management') }}" class="px-space-md py-1.5 rounded-lg bg-surface-container text-on-surface hover:bg-surface-container-high transition-colors font-label-md text-label-md font-semibold">
                                    Detail
                                </a>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</x-petugas-layout>
