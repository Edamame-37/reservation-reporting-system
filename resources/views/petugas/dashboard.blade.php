{{-- 
  NAMA FILE      : dashboard.blade.php
  FUNGSIONALITAS : Halaman Dasbor Utama Petugas Sarpras
  DESKRIPSI      : Menampilkan metrik KPI sarpras (Antrean Menunggu, Laporan Aktif, Fasilitas Terkunci), 3 antrean prioritas verifikasi, dan 3 laporan kerusakan mendesak dengan tombol 'Lihat Selengkapnya'.
  CARA KERJA     : Memanfaatkan layout <x-petugas-layout active="dashboard">, menerapkan prinsip progressive disclosure untuk fokus kerja operasional.
--}}

<x-petugas-layout title="Dasbor Operasional Sarpras" active="dashboard">
    <!-- 
      ELEMEN       : 3 KPI Stat Cards Operasional
      KEGUNAAN     : Menyajikan metrik beban kerja aktif petugas piket secara instan.
    -->
    <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <x-cava.stat-card 
            title="Antrean Reservasi Menunggu"
            value="8"
            subtitle="Permohonan Masuk"
            tagText="+3 Pengajuan Hari Ini"
            footerText="SLA Respon < 2 Jam"
            icon="pending_actions"
            variant="primary"
        />

        <x-cava.stat-card 
            title="Antrean Laporan Kerusakan"
            value="5"
            subtitle="Tiket Kerusakan Aktif"
            tagText="2 Prioritas Mendesak"
            footerText="Target Selesai < 24 Jam"
            icon="build_circle"
            variant="error"
        />

        <x-cava.stat-card 
            title="Fasilitas Dalam Perbaikan"
            value="2"
            subtitle="Ruangan Terkunci (Locked)"
            tagText="Lab Hardware 2 & Senat"
            footerText="Otomatis Non-Aktif di Kalender"
            icon="domain_disabled"
            variant="tertiary"
        />
    </section>

    <!-- 
      ELEMEN       : Antrean Prioritas Verifikasi Reservasi (Batas 3 Data Preview + Tombol Lihat Semua)
      KEGUNAAN     : Memeriksa dan memproses permohonan masuk dengan validasi bentrok jadwal (SFR06).
    -->
    <section class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 mb-4 border-b border-slate-100 gap-2">
            <div>
                <h3 class="text-base font-bold text-slate-900">Antrean Prioritas Verifikasi Reservasi</h3>
                <p class="text-xs text-slate-500 mt-0.5">Menampilkan 3 permohonan paling mendesak dari total 8 antrean masuk.</p>
            </div>
            {{-- Tombol Lihat Selengkapnya menuju Antrean Lengkap --}}
            <a href="{{ url('/petugas/reservation-management') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-blue-950 hover:text-blue-700 transition">
                <span>Buka Seluruh Antrean Reservasi (8 Data)</span>
                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                        <th class="py-3 px-4">Pemohon & Lembaga</th>
                        <th class="py-3 px-4">Fasilitas Diminta</th>
                        <th class="py-3 px-4">Jadwal Sesi 30m</th>
                        <th class="py-3 px-4">Status Konflik Jadwal</th>
                        <th class="py-3 px-4 text-right">Aksi Cepat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    {{-- Antrean 1: Aman --}}
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900">Dimas Pratama</div>
                            <div class="text-[11px] text-slate-500">Mahasiswa TI • 2110512044</div>
                            <span class="text-[10px] text-blue-900 font-semibold mt-0.5 block">Himpunan Mahasiswa TI (HMIF)</span>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900">Auditorium B.J. Habibie</div>
                            <div class="text-[11px] text-slate-500">Gedung Rektorat (450 Kursi)</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900">24 Apr 2024</div>
                            <div class="text-[11px] text-slate-600 font-mono">09:00 - 12:00 WIB (6 slot)</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs bg-emerald-50 text-emerald-800 border border-emerald-200 font-semibold">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                100% Bebas Bentrok
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <a href="{{ url('/petugas/reservation-management') }}" class="px-3 py-1 rounded-lg bg-slate-900 text-white font-semibold hover:bg-slate-800 transition shadow-xs">
                                    Tinjau & Setujui
                                </a>
                            </div>
                        </td>
                    </tr>

                    {{-- Antrean 2: Terdeteksi Konflik --}}
                    <tr class="hover:bg-slate-50/70 transition bg-rose-50/20">
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900">Dr. Ir. Hendra Prasetyo</div>
                            <div class="text-[11px] text-slate-500">Dosen Tetap • NIP. 198402112009121003</div>
                            <span class="text-[10px] text-slate-600 font-medium mt-0.5 block">Prodi Sistem Informasi</span>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900">Lab Komputasi Cloud</div>
                            <div class="text-[11px] text-slate-500">Gedung C Lt. 2 (45 PC)</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900">24 Apr 2024</div>
                            <div class="text-[11px] text-rose-600 font-mono font-bold">10:00 - 13:00 WIB</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs bg-rose-50 text-rose-700 border border-rose-200 font-bold">
                                <span class="material-symbols-outlined text-[14px]">warning</span>
                                Bentrok Jadwal Kuliah
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <a href="{{ url('/petugas/reservation-management') }}" class="px-3 py-1 rounded-lg border border-rose-200 bg-rose-50 text-rose-700 font-semibold hover:bg-rose-100 transition">
                                Evaluasi Bentrok
                            </a>
                        </td>
                    </tr>

                    {{-- Antrean 3: Aman --}}
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900">Siti Nurhaliza</div>
                            <div class="text-[11px] text-slate-500">Mahasiswa Akuntansi • 2210811002</div>
                            <span class="text-[10px] text-blue-900 font-semibold mt-0.5 block">BEM Fakultas Ekonomi</span>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900">Smart Classroom 302</div>
                            <div class="text-[11px] text-slate-500">Gedung B Lt. 3 (60 Kursi)</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900">26 Apr 2024</div>
                            <div class="text-[11px] text-slate-600 font-mono">14:00 - 16:30 WIB (5 slot)</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs bg-emerald-50 text-emerald-800 border border-emerald-200 font-semibold">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                100% Bebas Bentrok
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <a href="{{ url('/petugas/reservation-management') }}" class="px-3 py-1 rounded-lg bg-slate-900 text-white font-semibold hover:bg-slate-800 transition shadow-xs">
                                Tinjau & Setujui
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- 
      ELEMEN       : Section Laporan Kerusakan Mendesak (Batas 3 Data Preview + Tombol Lihat Semua)
      KEGUNAAN     : Memantau kerusakan yang membutuhkan respon cepat atau penguncian fasilitas (UR12).
    -->
    <section class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 mb-4 border-b border-slate-100 gap-2">
            <div>
                <h3 class="text-base font-bold text-slate-900">Laporan Kerusakan Mendesak Perlu Tindakan</h3>
                <p class="text-xs text-slate-500 mt-0.5">Menampilkan 3 tiket laporan terbaru dari total 5 kerusakan aktif di kampus.</p>
            </div>
            {{-- Tombol Lihat Selengkapnya menuju Manajemen Laporan --}}
            <a href="{{ url('/petugas/report-management') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-blue-950 hover:text-blue-700 transition">
                <span>Kelola Seluruh Laporan Kerusakan (5 Data)</span>
                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            {{-- Ticket 1 --}}
            <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-mono text-xs font-bold text-slate-800">RPT-20240420-009</span>
                        <x-cava.status-badge status="baru" label="Laporan Baru" />
                    </div>
                    <h4 class="text-sm font-bold text-slate-900 leading-snug">Kabel LAN Meja 12 & 13 Putus</h4>
                    <p class="text-xs text-slate-500 flex items-center gap-1 mt-1 mb-2">
                        <span class="material-symbols-outlined text-[15px]">location_on</span>
                        Lab Komputasi Cloud (Gedung C)
                    </p>
                    <p class="text-xs text-slate-600 line-clamp-2">Kabel jaringan putus terpotong hewan liar, port switch tidak merespon.</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-200/60 flex items-center justify-between text-xs">
                    <span class="text-slate-400">20 Apr, 10:14 WIB</span>
                    <a href="{{ url('/petugas/report-management') }}" class="font-bold text-slate-900 hover:underline">Proses Tiket →</a>
                </div>
            </div>

            {{-- Ticket 2 --}}
            <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-mono text-xs font-bold text-slate-800">RPT-20240419-003</span>
                        <x-cava.status-badge status="diproses" label="Dalam Perbaikan" />
                    </div>
                    <h4 class="text-sm font-bold text-slate-900 leading-snug">Proyektor Berkedip & Mati Total</h4>
                    <p class="text-xs text-slate-500 flex items-center gap-1 mt-1 mb-2">
                        <span class="material-symbols-outlined text-[15px]">location_on</span>
                        Smart Classroom 302 (Gedung B)
                    </p>
                    <p class="text-xs text-slate-600 line-clamp-2">Lampu indikator merah menyala, ruangan sementara terkunci di kalender.</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-200/60 flex items-center justify-between text-xs">
                    <span class="text-slate-400">19 Apr, 14:30 WIB</span>
                    <a href="{{ url('/petugas/report-management') }}" class="font-bold text-slate-900 hover:underline">Proses Tiket →</a>
                </div>
            </div>

            {{-- Ticket 3 --}}
            <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-mono text-xs font-bold text-slate-800">RPT-20240417-001</span>
                        <x-cava.status-badge status="baru" label="Laporan Baru" />
                    </div>
                    <h4 class="text-sm font-bold text-slate-900 leading-snug">AC Sisi Barat Teteskan Air</h4>
                    <p class="text-xs text-slate-500 flex items-center gap-1 mt-1 mb-2">
                        <span class="material-symbols-outlined text-[15px]">location_on</span>
                        Auditorium Utama B.J. Habibie
                    </p>
                    <p class="text-xs text-slate-600 line-clamp-2">Pipa pembuangan AC tersumbat, butuh pembersihan teknisi pendingin.</p>
                </div>
                <div class="mt-4 pt-3 border-t border-slate-200/60 flex items-center justify-between text-xs">
                    <span class="text-slate-400">17 Apr, 08:20 WIB</span>
                    <a href="{{ url('/petugas/report-management') }}" class="font-bold text-slate-900 hover:underline">Proses Tiket →</a>
                </div>
            </div>
        </div>
    </section>
</x-petugas-layout>
