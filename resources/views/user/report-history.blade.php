{{-- 
  NAMA FILE      : report-history.blade.php
  FUNGSIONALITAS : Halaman Antarmuka Status & Riwayat Pelaporan Kerusakan (Mahasiswa / Dosen)
  DESKRIPSI      : Menampilkan daftar seluruh tiket laporan malfungsi yang pernah dikirimkan oleh pengguna beserta status penanganan dan catatan resolusi sarpras.
  CARA KERJA     : Menggunakan layout <x-app-layout active="report-history">, menyajikan tabel pelacakan tiket laporan secara real-time.
--}}

<x-app-layout title="Status Laporan Kerusakan Saya" active="report-history">
    <!-- 
      ELEMEN       : Section Status Pelaporan Kerusakan Saya (UR07)
      KEGUNAAN     : Memungkinkan pengguna memantau pergerakan tiket penanganan malfungsi sarana kampus.
      CARA KERJA   : Merender tabel tiket dengan status badge dinamis (Baru / Diproses / Selesai) dan catatan resolusi teknisi.
    -->
    <section class="bg-surface-container-lowest rounded-xl shadow-md p-space-xl mb-space-xl">
        {{-- Header & Search --}}
        <div class="flex flex-wrap items-center justify-between pb-space-md mb-space-md gap-space-md border-b border-outline-variant">
            <div class="flex items-center gap-space-sm">
                <div class="w-10 h-10 rounded-lg bg-tertiary-fixed text-on-tertiary-fixed flex items-center justify-center shadow-sm">
                    <span class="material-symbols-outlined text-[24px]">checklist</span>
                </div>
                <div>
                    <h1 class="font-headline-md text-headline-md text-primary">Status Pelaporan Kerusakan Saya</h1>
                    <p class="font-body-sm text-body-sm text-on-surface-variant">Modul UR07: Pantau respon petugas sarpras dan riwayat resolusi perbaikan fasilitas.</p>
                </div>
            </div>
            <a href="{{ url('/user/report-form') }}" class="inline-flex items-center gap-space-xs px-space-md py-2 rounded-lg bg-primary text-on-primary font-label-md text-label-md hover:bg-primary-container transition-colors shadow-sm">
                <span class="material-symbols-outlined text-[16px]">add</span>
                <span>Buat Laporan Baru</span>
            </a>
        </div>

        {{-- Tabel Tiket Laporan --}}
        <div class="overflow-x-auto rounded-lg">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface-container-high text-on-surface-variant font-label-sm text-label-sm uppercase tracking-wider">
                        <th class="py-space-md px-space-md">Tiket & Tanggal</th>
                        <th class="py-space-md px-space-md">Fasilitas / Kategori</th>
                        <th class="py-space-md px-space-md">Deskripsi Masalah</th>
                        <th class="py-space-md px-space-md">Status Sarpras</th>
                        <th class="py-space-md px-space-md">Catatan Tindak Lanjut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-surface-container-high font-body-sm text-body-sm text-on-surface">
                    {{-- Record 1: Baru --}}
                    <tr class="hover:bg-surface-container-low transition-colors">
                        <td class="py-space-md px-space-md">
                            <div class="font-data-mono text-data-mono font-bold text-primary">RPT-20240420-009</div>
                            <div class="font-label-sm text-label-sm text-on-surface-variant">20 Apr 2024 • 10:14 WIB</div>
                        </td>
                        <td class="py-space-md px-space-md">
                            <div class="font-label-md text-label-md text-on-surface font-semibold">Lab Komputasi Awan</div>
                            <span class="px-2 py-0.5 rounded bg-surface-container font-label-sm text-label-sm text-on-surface-variant font-medium">Jaringan Internet</span>
                        </td>
                        <td class="py-space-md px-space-md max-w-xs">
                            <p class="truncate">Port switch nomor 12 di baris C tidak terhubung ke router gateway.</p>
                        </td>
                        <td class="py-space-md px-space-md">
                            <x-cava.status-badge status="Baru" />
                        </td>
                        <td class="py-space-md px-space-md text-on-surface-variant italic">
                            Menunggu inspeksi shift teknisi jaringan.
                        </td>
                    </tr>

                    {{-- Record 2: Diproses Tim Sarpras --}}
                    <tr class="hover:bg-surface-container-low transition-colors">
                        <td class="py-space-md px-space-md">
                            <div class="font-data-mono text-data-mono font-bold text-primary">RPT-20240415-021</div>
                            <div class="font-label-sm text-label-sm text-on-surface-variant">15 Apr 2024 • 14:30 WIB</div>
                        </td>
                        <td class="py-space-md px-space-md">
                            <div class="font-label-md text-label-md text-on-surface font-semibold">Auditorium B.J. Habibie</div>
                            <span class="px-2 py-0.5 rounded bg-surface-container font-label-sm text-label-sm text-on-surface-variant font-medium">AC & Pendingin</span>
                        </td>
                        <td class="py-space-md px-space-md max-w-xs">
                            <p class="truncate">AC Central blower sisi barat mengeluarkan suara bising dan tetesan air.</p>
                        </td>
                        <td class="py-space-md px-space-md">
                            <x-cava.status-badge status="Diproses" />
                        </td>
                        <td class="py-space-md px-space-md text-on-surface-variant">
                            Penggantian freon kompresor outdoor unit 03 sedang dikerjakan teknisi.
                        </td>
                    </tr>

                    {{-- Record 3: Selesai --}}
                    <tr class="hover:bg-surface-container-low transition-colors">
                        <td class="py-space-md px-space-md">
                            <div class="font-data-mono text-data-mono font-bold text-primary">RPT-20240402-004</div>
                            <div class="font-label-sm text-label-sm text-on-surface-variant">02 Apr 2024 • 08:20 WIB</div>
                        </td>
                        <td class="py-space-md px-space-md">
                            <div class="font-label-md text-label-md text-on-surface font-semibold">Smart Classroom 302</div>
                            <span class="px-2 py-0.5 rounded bg-surface-container font-label-sm text-label-sm text-on-surface-variant font-medium">Proyektor & Audio</span>
                        </td>
                        <td class="py-space-md px-space-md max-w-xs">
                            <p class="truncate">Kabel HDMI proyektor layar utama putus sambungan warna merah.</p>
                        </td>
                        <td class="py-space-md px-space-md">
                            <x-cava.status-badge status="Selesai" />
                        </td>
                        <td class="py-space-md px-space-md text-secondary font-medium">
                            Kabel HDMI 4K diganti baru dan telah ditest normal.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>
</x-app-layout>
