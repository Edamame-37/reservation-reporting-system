{{-- 
====================================================================================================
SISTEM INFORMASI RESERVASI & PELAPORAN FASILITAS KAMPUS (CAVA)
MODUL 8: KONSOL ADMIN - REKAPITULASI ANALITIK, AUDIT OKUPANSI, & EKSPOR RESMI (PDF & EXCEL - ADM-04)
BERKAS: resources/views/admin/exports/damage-reports-pdf.blade.php
====================================================================================================

DESKRIPSI BERKAS:
Template cetak dokumen PDF resmi (Institutional Incident & Maintenance Report) untuk Rekapitulasi
Insiden Kerusakan Sarana & Prasarana Kampus beserta riwayat resolusi teknisi. Dikompilasi oleh library
DomPDF (`Barryvdh\DomPDF\Facade\Pdf`) ke dalam format biner PDF A4 Landscape.

PERAN & ARSITEKTUR DALAM SISTEM CAVA:
1. Format Cetak Statuter Insiden Aset (Statutory Maintenance Report):
   Memuat kop surat resmi Divisi Pemeliharaan Aset & Tanggap Kerusakan, kode registrasi dokumen
   `STATUTER-INSIDEN`, stempel waktu pencetakan audit, dan tanda tangan legal Koordinator Divisi Pemeliharaan.
2. Optimasi Mesin Render DomPDF:
   - CSS inline statis bergaya crimson red (#991b1b) penanda dokumen audit keselamatan & sarpras.
   - Orientasi `@page { size: A4 landscape; margin: 15mm; }` untuk keleluasaan tabel rincian kerusakan 10 kolom.
   - Properti `page-break-inside: avoid` pada signature box agar lembar tanda tangan tidak terpotong ke halaman baru.
3. Transparansi Rekam Jejak Resolusi:
   Menyajikan kolom teknisi penanggung jawab (PIC) dan catatan teknis perbaikan (`resolution_note`)
   sebagai bahan evaluasi pemeliharaan berkala universitas.
====================================================================================================
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Rekapitulasi Kerusakan & Pemeliharaan Fasilitas</title>
    <style>
        /* Konfigurasi Ukuran Kertas & Margin Cetak DomPDF (A4 Landscape) */
        @page {
            size: A4 landscape;
            margin: 15mm 15mm 15mm 15mm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #1e293b;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        /* Tata Letak Kop Surat Institusi */
        .header-table {
            width: 100%;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header-table td {
            vertical-align: middle;
        }
        .inst-title {
            font-size: 15px;
            font-weight: bold;
            color: #002046;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .inst-sub {
            font-size: 10px;
            color: #475569;
        }
        .doc-title {
            font-size: 13px;
            font-weight: bold;
            color: #991b1b;
            margin-top: 10px;
            margin-bottom: 2px;
            text-transform: uppercase;
            text-align: center;
        }
        .doc-meta {
            font-size: 9px;
            color: #64748b;
            text-align: center;
            margin-bottom: 14px;
        }
        /* Tabel Data Insiden Kerusakan */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .data-table th {
            background-color: #991b1b;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            padding: 6px 5px;
            border: 1px solid #991b1b;
            text-align: left;
        }
        .data-table td {
            padding: 5px;
            border: 1px solid #cbd5e1;
            font-size: 9px;
            vertical-align: top;
        }
        .data-table tr:nth-child(even) {
            background-color: #fff1f2;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        /* Badge Status Penanganan Tiket */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 8px;
            font-weight: bold;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .badge-baru { background-color: #fee2e2; color: #991b1b; }
        .badge-diproses { background-color: #fef3c7; color: #92400e; }
        .badge-selesai { background-color: #dcfce7; color: #166534; }
        .badge-ditolak { background-color: #f1f5f9; color: #475569; }

        /* Kolom Tanda Tangan Pejabat Pengesah */
        .signature-table {
            width: 100%;
            margin-top: 20px;
            page-break-inside: avoid;
        }
        .signature-table td {
            vertical-align: top;
        }
        .sign-title {
            font-size: 9px;
            color: #64748b;
            margin-bottom: 45px;
        }
        .sign-name {
            font-size: 10px;
            font-weight: bold;
            color: #0f172a;
            text-decoration: underline;
        }
        .sign-nip {
            font-size: 9px;
            color: #475569;
        }
    </style>
</head>
<body>
    {{-- -------------------------------------------------------------------------------------- --}}
    {{-- 1. KOP SURAT RESMI DIVISI PEMELIHARAAN ASET & TANGGAP KERUSAKAN                        --}}
    {{-- -------------------------------------------------------------------------------------- --}}
    <table class="header-table">
        <tr>
            <td style="width: 70%;">
                <div class="inst-title">KONSOL SISTEM TATA KELOLA FASILITAS KAMPUS (CAVA)</div>
                <div class="inst-sub">Divisi Pemeliharaan Aset & Tanggap Kerusakan Sarana Prasarana</div>
                <div class="inst-sub">Gedung Pemeliharaan Terpadu • Email: maintenance@cava.ac.id</div>
            </td>
            <td style="width: 30%; text-align: right;">
                <div style="font-size: 9px; color: #64748b;">KODE DOKUMEN: STATUTER-INSIDEN</div>
                <div style="font-size: 9px; color: #64748b;">TANGGAL CETAK: {{ date('d/m/Y H:i') }} WIB</div>
            </td>
        </tr>
    </table>

    {{-- Judul Dokumen dan Informasi Rentang Periode Laporan --}}
    <div class="doc-title">Laporan Rekapitulasi Insiden Kerusakan & Tindak Lanjut Pemeliharaan</div>
    <div class="doc-meta">
        Periode Laporan: <strong>{{ $startDate ? date('d F Y', strtotime($startDate)) : 'Awal Sistem' }}</strong> s/d <strong>{{ $endDate ? date('d F Y', strtotime($endDate)) : date('d F Y') }}</strong>
        • Total Tiket: <strong>{{ count($damageReports) }} Laporan</strong>
    </div>

    {{-- -------------------------------------------------------------------------------------- --}}
    {{-- 2. TABEL DATA TIKET ADUAN KERUSAKAN FASILITAS                                          --}}
    {{-- -------------------------------------------------------------------------------------- --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">No</th>
                <th style="width: 95px;">Kode Tiket</th>
                <th style="width: 120px;">Pelapor</th>
                <th style="width: 130px;">Fasilitas & Lokasi</th>
                <th style="width: 90px;">Kategori</th>
                <th>Deskripsi Kerusakan</th>
                <th style="width: 75px;" class="text-center">Tgl Lapor</th>
                <th style="width: 70px;" class="text-center">Status</th>
                <th style="width: 110px;">Teknisi / PIC</th>
                <th style="width: 110px;">Catatan Resolusi</th>
            </tr>
        </thead>
        <tbody>
            @forelse($damageReports as $index => $item)
                @php
                    // Pemetaan gaya badge berdasarkan status tiket penanganan
                    $badgeClass = match($item->status) {
                        'baru', 'submitted'      => 'badge-baru',
                        'diproses', 'in_review',
                        'in_progress'            => 'badge-diproses',
                        'selesai', 'resolved'    => 'badge-selesai',
                        'ditolak', 'rejected'    => 'badge-ditolak',
                        default                  => 'badge-baru'
                    };
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td style="font-family: monospace; font-weight: bold;">{{ $item->report_code }}</td>
                    <td>
                        <strong>{{ $item->user?->name ?? '-' }}</strong><br>
                        <span style="color: #64748b; font-size: 8px;">{{ $item->user?->identifier ?? $item->user?->identity_number ?? '-' }}</span>
                    </td>
                    <td>
                        <strong>{{ $item->facility?->name ?? '-' }}</strong><br>
                        <span style="color: #64748b; font-size: 8px;">{{ $item->facility?->code }} • {{ $item->facility?->building }}</span>
                    </td>
                    <td><strong>{{ $item->category }}</strong></td>
                    <td>{{ $item->description }}</td>
                    <td class="text-center">{{ $item->created_at ? $item->created_at->format('d/m/Y') : '-' }}</td>
                    <td class="text-center">
                        <span class="badge {{ $badgeClass }}">{{ strtoupper($item->status) }}</span>
                    </td>
                    <td>{{ $item->handledBy?->name ?? '-' }}</td>
                    <td>{{ $item->resolution_note ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 20px; color: #64748b;">
                        Tidak ada catatan insiden kerusakan pada rentang tanggal yang dipilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- -------------------------------------------------------------------------------------- --}}
    {{-- 3. KOLOM PENGESAHAN DOKUMEN & TANDA TANGAN KOORDINATOR PEMELIHARAAN                    --}}
    {{-- -------------------------------------------------------------------------------------- --}}
    <table class="signature-table">
        <tr>
            <td style="width: 65%;">
                <div style="font-size: 8px; color: #94a3b8; line-height: 1.3;">
                    * Laporan rekapitulasi penanganan kerusakan aset sarana prasarana ini bersifat resmi.<br>
                    * Dilaporkan sebagai bahan evaluasi pemeliharaan berkala universitas.
                </div>
            </td>
            <td style="width: 35%; text-align: center;">
                <div class="sign-title">
                    Ditetapkan di Kampus Terpadu<br>
                    Koordinator Divisi Pemeliharaan & Aset
                </div>
                <div class="sign-name">Bambang Sutejo, S.T.</div>
                <div class="sign-nip">NIP. 19820311 200812 1 001</div>
            </td>
        </tr>
    </table>
</body>
</html>
