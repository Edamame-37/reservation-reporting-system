{{-- 
  NAMA FILE      : reservations-pdf.blade.php
  FUNGSIONALITAS : Dokumen Cetak PDF Resmi Rekapitulasi Peminjaman & Okupansi Ruang Kampus
  DESKRIPSI      : Menampilkan kop universitas resmi, rentang tanggal filter, tabel rekapitulasi data reservasi, dan tanda tangan otoritas Biro Sarpras & TIK.
  CARA KERJA     : Dirender oleh Barryvdh\DomPDF\Facade\Pdf dan diunduh sebagai file biner PDF landscape A4.
--}}
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Rekapitulasi Reservasi Ruang Kampus</title>
    <style>
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
            color: #0f172a;
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
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .data-table th {
            background-color: #002046;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 9px;
            padding: 6px 5px;
            border: 1px solid #002046;
            text-align: left;
        }
        .data-table td {
            padding: 5px;
            border: 1px solid #cbd5e1;
            font-size: 9px;
            vertical-align: top;
        }
        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-center {
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            font-size: 8px;
            font-weight: bold;
            border-radius: 4px;
            text-transform: uppercase;
        }
        .badge-approved { background-color: #dcfce7; color: #166534; }
        .badge-pending { background-color: #fef3c7; color: #92400e; }
        .badge-rejected { background-color: #fee2e2; color: #991b1b; }
        .badge-cancelled { background-color: #f1f5f9; color: #475569; }
        .badge-completed { background-color: #e0e7ff; color: #3730a3; }

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
    {{-- Header Kop Surat Resmi --}}
    <table class="header-table">
        <tr>
            <td style="width: 70%;">
                <div class="inst-title">KONSOL SISTEM TATA KELOLA FASILITAS KAMPUS (CAVA)</div>
                <div class="inst-sub">Biro Sarana Prasarana & Tata Kelola Sistem Informasi Terpadu Universitas</div>
                <div class="inst-sub">Gedung Rektorat Sayap Barat, Kampus Terpadu • Email: sarpras@cava.ac.id</div>
            </td>
            <td style="width: 30%; text-align: right;">
                <div style="font-size: 9px; color: #64748b;">KODE DOKUMEN: STATUTER-UR17</div>
                <div style="font-size: 9px; color: #64748b;">TANGGAL CETAK: {{ date('d/m/Y H:i') }} WIB</div>
            </td>
        </tr>
    </table>

    <div class="doc-title">Laporan Rekapitulasi Okupansi & Peminjaman Ruang Kampus</div>
    <div class="doc-meta">
        Periode Laporan: <strong>{{ $startDate ? date('d F Y', strtotime($startDate)) : 'Awal Sistem' }}</strong> s/d <strong>{{ $endDate ? date('d F Y', strtotime($endDate)) : date('d F Y') }}</strong>
        • Total Transaksi: <strong>{{ count($reservations) }} Sesi</strong>
    </div>

    {{-- Tabel Utama Reservasi --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">No</th>
                <th style="width: 95px;">Kode Tiket</th>
                <th style="width: 130px;">Nama Pemohon</th>
                <th style="width: 140px;">Ruang & Lokasi</th>
                <th style="width: 75px;" class="text-center">Tanggal</th>
                <th style="width: 85px;" class="text-center">Waktu</th>
                <th style="width: 40px;" class="text-center">Slot</th>
                <th style="width: 45px;" class="text-center">Peserta</th>
                <th>Tujuan Penggunaan</th>
                <th style="width: 75px;" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($reservations as $index => $item)
                @php
                    $badgeClass = match($item->status) {
                        'approved' => 'badge-approved',
                        'pending' => 'badge-pending',
                        'rejected' => 'badge-rejected',
                        'cancelled' => 'badge-cancelled',
                        'completed' => 'badge-completed',
                        default => 'badge-pending'
                    };
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td style="font-family: monospace; font-weight: bold;">{{ $item->ticket_code }}</td>
                    <td>
                        <strong>{{ $item->user?->name ?? '-' }}</strong><br>
                        <span style="color: #64748b; font-size: 8px;">{{ $item->user?->identifier ?? '-' }} ({{ $item->user?->study_program ?? 'Umum' }})</span>
                    </td>
                    <td>
                        <strong>{{ $item->facility?->name ?? '-' }}</strong><br>
                        <span style="color: #64748b; font-size: 8px;">{{ $item->facility?->code }} • {{ $item->facility?->building }}</span>
                    </td>
                    <td class="text-center">{{ $item->reservation_date ? $item->reservation_date->format('d/m/Y') : '-' }}</td>
                    <td class="text-center">{{ substr($item->start_time, 0, 5) }} - {{ substr($item->end_time, 0, 5) }}</td>
                    <td class="text-center">{{ $item->total_slots }}</td>
                    <td class="text-center">{{ $item->participants_count }} org</td>
                    <td>{{ $item->purpose }}</td>
                    <td class="text-center">
                        <span class="badge {{ $badgeClass }}">{{ strtoupper($item->status) }}</span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 20px; color: #64748b;">
                        Tidak ada data permohonan reservasi pada rentang tanggal yang dipilih.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Tabel Tanda Tangan Pengesahan --}}
    <table class="signature-table">
        <tr>
            <td style="width: 65%;">
                <div style="font-size: 8px; color: #94a3b8; line-height: 1.3;">
                    * Dokumen rekapitulasi ini dicetak secara otomatis melalui Modul Reporting & Analytics CAVA Konsol Super Admin.<br>
                    * Sah digunakan sebagai laporan statuter triwulan Biro Sarana Prasarana ke Senat & Rektorat Universitas.
                </div>
            </td>
            <td style="width: 35%; text-align: center;">
                <div class="sign-title">
                    Ditetapkan di Kampus Terpadu<br>
                    Kepala Biro Sarana, Prasarana & TIK
                </div>
                <div class="sign-name">Dr. Ir. Hendra Gunawan, S.T., M.T.</div>
                <div class="sign-nip">NIP. 19780514 200501 1 002</div>
            </td>
        </tr>
    </table>
</body>
</html>
