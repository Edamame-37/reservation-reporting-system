{{-- 
  NAMA FILE      : dashboard.blade.php
  FUNGSIONALITAS : Halaman Dasbor Utama Pengguna Mahasiswa & Dosen
  DESKRIPSI      : Menampilkan kartu sorotan jadwal terdekat, 4 pintasan cepat, 3 data riwayat reservasi preview dengan tombol 'Lihat Semua', serta 2 status laporan kerusakan aktif.
  CARA KERJA     : Menggunakan layout <x-app-layout active="dashboard">, menyajikan dasbor sentral dengan prinsip progressive disclosure.
--}}

<x-app-layout title="Dasbor Mahasiswa & Dosen" active="dashboard">
    <!-- 
      ELEMEN       : Banner Informasi Kebijakan Reservasi & Kuota
      KEGUNAAN     : Mengingatkan batas waktu reservasi minimal 2x24 jam dan batas pembatalan mandiri H-1.
    -->
    <section class="bg-white rounded-2xl border border-slate-200/80 p-4 sm:p-5 shadow-xs mb-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-900 flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[22px]">info</span>
            </div>
            <div>
                <h2 class="text-sm font-bold text-slate-900 leading-snug">Kebijakan Pemesanan Fasilitas Kampus (SK Rektor No. 428/2024)</h2>
                <p class="text-xs text-slate-500 mt-0.5">Reservasi diajukan minimal H-2 sebelum kegiatan. Pembatalan mandiri hanya diizinkan maksimal H-1 sebelum jadwal.</p>
            </div>
        </div>
        <div class="px-3.5 py-1.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-semibold text-slate-700 whitespace-nowrap self-stretch md:self-auto text-center">
            Kuota Bulanan: <span class="text-slate-900 font-bold">4 / 5 Terpakai</span>
        </div>
    </section>

    <!-- 
      ELEMEN       : Spotlight Jadwal Terdekat (Upcoming Reservation Highlight)
      KEGUNAAN     : Memanjakan pengguna dengan menyajikan jadwal kegiatan paling mendesak dalam kartu hero fokus.
    -->
    <section class="bg-gradient-to-r from-slate-900 to-slate-800 rounded-3xl p-6 text-white shadow-md mb-8">
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
            <div>
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-xs font-semibold text-emerald-300 mb-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-400"></span>
                    <span>Reservasi Aktif Disetujui</span>
                </div>
                <h3 class="text-xl font-bold tracking-tight text-white">Seminar Nasional Cloud Architecture Himpunan TI</h3>
                <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-xs text-slate-300 mt-2">
                    <span class="flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">location_on</span>
                        Auditorium B.J. Habibie (Gedung Rektorat Lt. 1)
                    </span>
                    <span>•</span>
                    <span class="flex items-center gap-1 text-emerald-300 font-medium">
                        <span class="material-symbols-outlined text-[16px]">schedule</span>
                        Rabu, 24 April 2024 • 09:00 - 12:00 WIB
                    </span>
                    <span>•</span>
                    <span class="font-mono text-slate-400">TKT-20240424-001</span>
                </div>
            </div>
            <div class="flex items-center gap-2 w-full md:w-auto">
                <a href="{{ url('/user/reservation-history') }}" class="w-full md:w-auto px-4 py-2 rounded-xl bg-white text-slate-900 text-xs font-semibold hover:bg-slate-100 transition shadow-xs text-center">
                    Buka Detail Tiket
                </a>
            </div>
        </div>
    </section>

    <!-- 
      ELEMEN       : 4 Quick Action Cards
      KEGUNAAN     : Pintasan cepat menuju aksi utama permohonan dan pemantauan pengguna.
    -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <a href="{{ url('/user/reservation-form') }}" class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md hover:border-slate-400 transition-all flex items-center gap-3.5 group">
            <div class="w-11 h-11 rounded-xl bg-slate-900 text-white flex items-center justify-center group-hover:scale-105 transition-transform">
                <span class="material-symbols-outlined text-[22px]">add_circle</span>
            </div>
            <div>
                <div class="text-sm font-bold text-slate-900">Ajukan Reservasi</div>
                <div class="text-[11px] text-slate-500">Pilih slot waktu 30 menit</div>
            </div>
        </a>

        <a href="{{ url('/user/reservation-history') }}" class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md hover:border-slate-400 transition-all flex items-center gap-3.5 group">
            <div class="w-11 h-11 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center group-hover:scale-105 transition-transform">
                <span class="material-symbols-outlined text-[22px]">history</span>
            </div>
            <div>
                <div class="text-sm font-bold text-slate-900">Riwayat Reservasi</div>
                <div class="text-[11px] text-slate-500">Pantau tiket & pembatalan H-1</div>
            </div>
        </a>

        <a href="{{ url('/user/report-form') }}" class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md hover:border-slate-400 transition-all flex items-center gap-3.5 group">
            <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center group-hover:scale-105 transition-transform">
                <span class="material-symbols-outlined text-[22px]">report_problem</span>
            </div>
            <div>
                <div class="text-sm font-bold text-slate-900">Lapor Kerusakan</div>
                <div class="text-[11px] text-slate-500">Foto bukti sarpras rusak</div>
            </div>
        </a>

        <a href="{{ url('/user/report-history') }}" class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-xs hover:shadow-md hover:border-slate-400 transition-all flex items-center gap-3.5 group">
            <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center group-hover:scale-105 transition-transform">
                <span class="material-symbols-outlined text-[22px]">checklist</span>
            </div>
            <div>
                <div class="text-sm font-bold text-slate-900">Status Laporan</div>
                <div class="text-[11px] text-slate-500">Pantau tindak lanjut teknisi</div>
            </div>
        </a>
    </div>

    <!-- 
      ELEMEN       : Section Riwayat Reservasi Terkini (Hanya 3 Data Preview + Tombol Lihat Semua)
      KEGUNAAN     : Menampilkan ringkasan ringkas tanpa tabel raksasa yang memenuhi layar.
    -->
    <section class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs mb-8">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
            <div>
                <h3 class="text-base font-bold text-slate-900">Riwayat Reservasi Terkini</h3>
                <p class="text-xs text-slate-500 mt-0.5">Menampilkan 3 pengajuan terakhir dari total 12 reservasi Anda.</p>
            </div>
            {{-- Tombol Lihat Selengkapnya --}}
            <a href="{{ url('/user/reservation-history') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-blue-950 hover:text-blue-700 transition">
                <span>Lihat Semua Riwayat Reservasi (12 Data)</span>
                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                        <th class="py-3 px-4">Kode Tiket</th>
                        <th class="py-3 px-4">Fasilitas</th>
                        <th class="py-3 px-4">Jadwal Sesi</th>
                        <th class="py-3 px-4">Tujuan Kegiatan</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    {{-- Row 1: Menunggu Konfirmasi --}}
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3.5 px-4 font-mono font-bold text-slate-800">TKT-20240428-009</td>
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900">Lab Komputasi Cloud</div>
                            <div class="text-[11px] text-slate-500">Gedung C, Lt. 2</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="text-slate-800 font-medium">28 Apr 2024</div>
                            <div class="text-[11px] text-slate-500 font-mono">13:00 - 15:30 WIB</div>
                        </td>
                        <td class="py-3.5 px-4 max-w-xs truncate text-slate-600">Praktikum Mandiri Pemrograman Web Lanjut</td>
                        <td class="py-3.5 px-4">
                            <x-cava.status-badge status="pending" label="Menunggu Konfirmasi" />
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <a href="{{ url('/user/reservation-history') }}" class="px-3 py-1 rounded-lg border border-slate-200 text-slate-700 font-semibold hover:bg-slate-50 transition">
                                Detail
                            </a>
                        </td>
                    </tr>

                    {{-- Row 2: Disetujui Petugas --}}
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3.5 px-4 font-mono font-bold text-slate-800">TKT-20240424-001</td>
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900">Auditorium B.J. Habibie</div>
                            <div class="text-[11px] text-slate-500">Gedung Rektorat, Lt. 1</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="text-slate-800 font-medium">24 Apr 2024</div>
                            <div class="text-[11px] text-slate-500 font-mono">09:00 - 12:00 WIB</div>
                        </td>
                        <td class="py-3.5 px-4 max-w-xs truncate text-slate-600">Seminar Cloud Computing Himpunan Mahasiswa TI</td>
                        <td class="py-3.5 px-4">
                            <x-cava.status-badge status="approved" label="Disetujui Petugas" />
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <a href="{{ url('/user/reservation-history') }}" class="px-3 py-1 rounded-lg border border-slate-200 text-slate-700 font-semibold hover:bg-slate-50 transition">
                                Detail
                            </a>
                        </td>
                    </tr>

                    {{-- Row 3: Ditolak Petugas --}}
                    <tr class="hover:bg-slate-50/70 transition">
                        <td class="py-3.5 px-4 font-mono font-bold text-slate-800">TKT-20240410-012</td>
                        <td class="py-3.5 px-4">
                            <div class="font-bold text-slate-900">Aula Serbaguna PKM</div>
                            <div class="text-[11px] text-slate-500">Gedung PKM, Lt. 1</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="text-slate-800 font-medium">10 Apr 2024</div>
                            <div class="text-[11px] text-slate-500 font-mono">08:00 - 17:00 WIB</div>
                        </td>
                        <td class="py-3.5 px-4 max-w-xs truncate text-slate-600">Festival Musik Dies Natalis BEM Universitas</td>
                        <td class="py-3.5 px-4">
                            <x-cava.status-badge status="rejected" label="Ditolak" />
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <a href="{{ url('/user/reservation-history') }}" class="px-3 py-1 rounded-lg border border-slate-200 text-slate-700 font-semibold hover:bg-slate-50 transition">
                                Detail
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <!-- 
      ELEMEN       : Section Laporan Kerusakan Terkini (2 Data Preview + Tombol Lihat Semua)
      KEGUNAAN     : Menyajikan perkembangan perbaikan fasilitas yang dilaporkan mahasiswa.
    -->
    <section class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
        <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
            <div>
                <h3 class="text-base font-bold text-slate-900">Status Laporan Kerusakan Terkini</h3>
                <p class="text-xs text-slate-500 mt-0.5">Pantau tindak lanjut perbaikan sarana yang Anda laporkan di kampus.</p>
            </div>
            <a href="{{ url('/user/report-history') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-blue-950 hover:text-blue-700 transition">
                <span>Lihat Semua Laporan Kerusakan</span>
                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            {{-- Laporan 1 --}}
            <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="font-mono text-xs font-bold text-slate-800">TKT-RPT-20240419-03</span>
                        <x-cava.status-badge status="diproses" label="Sedang Ditangani Teknisi" />
                    </div>
                    <h4 class="text-sm font-bold text-slate-900">Proyektor Berkedip & Tidak Muncul Gambar</h4>
                    <p class="text-xs text-slate-500 flex items-center gap-1 mt-0.5">
                        <span class="material-symbols-outlined text-[15px]">location_on</span>
                        Smart Classroom 302 • Gedung B Lt. 3
                    </p>
                </div>
                <div class="mt-3 pt-2.5 border-t border-slate-200/60 flex items-center justify-between text-xs text-slate-500">
                    <span>Dilaporkan 19 Apr 2024</span>
                    <a href="{{ url('/user/report-history') }}" class="font-semibold text-slate-800 hover:underline">Pantau Log →</a>
                </div>
            </div>

            {{-- Laporan 2 --}}
            <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="font-mono text-xs font-bold text-slate-800">TKT-RPT-20240415-01</span>
                        <x-cava.status-badge status="selesai" label="Selesai Diperbaiki" />
                    </div>
                    <h4 class="text-sm font-bold text-slate-900">Kabel LAN Meja 12 & 13 Putus Digigit Hewan</h4>
                    <p class="text-xs text-slate-500 flex items-center gap-1 mt-0.5">
                        <span class="material-symbols-outlined text-[15px]">location_on</span>
                        Lab Komputasi Cloud • Gedung C Lt. 2
                    </p>
                </div>
                <div class="mt-3 pt-2.5 border-t border-slate-200/60 flex items-center justify-between text-xs text-slate-500">
                    <span>Selesai 16 Apr 2024</span>
                    <a href="{{ url('/user/report-history') }}" class="font-semibold text-slate-800 hover:underline">Pantau Log →</a>
                </div>
            </div>
        </div>
    </section>
</x-app-layout>
