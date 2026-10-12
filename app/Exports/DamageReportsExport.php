<?php

/**
 * ====================================================================================================
 * SISTEM INFORMASI RESERVASI & PELAPORAN FASILITAS KAMPUS (CAVA)
 * MODUL 8: KONSOL ADMIN - REKAPITULASI ANALITIK, AUDIT OKUPANSI, & EKSPOR RESMI (PDF & EXCEL - ADM-04)
 * BERKAS: app/Exports/DamageReportsExport.php
 * ====================================================================================================
 * 
 * DESKRIPSI BERKAS:
 * Generator berkas spreadsheet (Excel-compatible CSV) untuk rekapitulasi data tiket pelaporan kerusakan
 * sarana dan prasarana kampus. Mengimplementasikan arsitektur streaming memori rendah berbasis chunking
 * untuk menjamin kinerja stabil saat mengekspor ribuan catatan audit insiden aset.
 * 
 * PERAN & ARSITEKTUR DALAM SISTEM CAVA:
 * 1. Pencegahan Memory Out-of-Bound (OOM Protection):
 *    Mengalirkan data langsung ke buffer `php://output` per 500 baris record via `chunk(500)` sehingga
 *    penggunaan RAM server tetap konstan (skala O(1)).
 * 2. Integritas Data & Encoding Microsoft Excel:
 *    - Menyuntikkan UTF-8 Byte Order Mark (`\xEF\xBB\xBF`) agar Microsoft Excel membaca teks tanpa masalah karakter aksen.
 *    - Menyisipkan baris instruksi `sep=,\r\n` untuk memandu parser Excel membaca tanda koma sebagai pemisah kolom otomatis.
 * 3. Eager Loading Tiga Relasi Sekaligus:
 *    Memuat relasi `user` (pelapor), `facility` (ruang rusak), dan `handledBy` (teknisi PIC sarpras) secara efisien
 *    tanpa menyebabkan N+1 database roundtrips.
 * ====================================================================================================
 */

namespace App\Exports;

use App\Models\DamageReport;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Class DamageReportsExport
 * 
 * Mengatur filter waktu/status, pembuatan kueri laporan kerusakan, tajuk kolom, dan pengaliran file spreadsheet.
 */
class DamageReportsExport
{
    /**
     * Rentang tanggal awal filter pembuatan laporan (format YYYY-MM-DD).
     *
     * @var string|null
     */
    protected ?string $startDate;

    /**
     * Rentang tanggal akhir filter pembuatan laporan (format YYYY-MM-DD).
     *
     * @var string|null
     */
    protected ?string $endDate;

    /**
     * Filter status penanganan tiket ('baru', 'diproses', 'selesai', 'ditolak', 'all').
     *
     * @var string|null
     */
    protected ?string $status;

    /**
     * Konstruktor inisialisasi kriteria penyaringan ekspor tiket kerusakan aset.
     *
     * @param string|null $startDate Tanggal awal batas pencarian tiket
     * @param string|null $endDate Tanggal akhir batas pencarian tiket
     * @param string|null $status Filter status progres investigasi sarpras
     */
    public function __construct(?string $startDate = null, ?string $endDate = null, ?string $status = null)
    {
        $this->startDate = $startDate;
        $this->endDate   = $endDate;
        $this->status    = $status;
    }

    /**
     * FUNCTION: query()
     * ------------------------------------------------------------------------------------------------
     * KEGUNAAN   : Membangun kueri Eloquent terfilter untuk menarik riwayat tiket aduan sarpras.
     * CARA KERJA :
     * 1. Menyiapkan query builder `DamageReport::query()`.
     * 2. Menerapkan eager loading ke relasi `user`, `facility`, dan `handledBy`.
     * 3. Menyaring kolom `created_at` mencakup jam 00:00:00 hari awal hingga 23:59:59 hari akhir.
     * 4. Menyaring status tiket jika parameter status disetel dan bukan 'all'.
     * 5. Mengurutkan tiket dari yang paling baru diajukan (`created_at desc`).
     * 
     * @return Builder
     */
    public function query(): Builder
    {
        // Menyiapkan kueri dengan eager loading untuk pelapor, fasilitas, dan teknisi penanggung jawab
        $query = DamageReport::query()->with(['user', 'facility', 'handledBy']);

        // Penerapan filter batas tanggal pelaporan aduan
        if (!empty($this->startDate) && !empty($this->endDate)) {
            $query->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);
        } elseif (!empty($this->startDate)) {
            $query->where('created_at', '>=', $this->startDate . ' 00:00:00');
        } elseif (!empty($this->endDate)) {
            $query->where('created_at', '<=', $this->endDate . ' 23:59:59');
        }

        // Penerapan filter status penanganan tiket jika ditentukan
        if (!empty($this->status) && $this->status !== 'all') {
            $query->where('status', $this->status);
        }

        // Pengurutan kronologis mundur dari aduan paling mutakhir
        return $query->orderBy('created_at', 'desc');
    }

    /**
     * FUNCTION: headings()
     * ------------------------------------------------------------------------------------------------
     * KEGUNAAN   : Mendefinisikan baris tajuk kolom dokumen spreadsheet hasil ekspor.
     * CARA KERJA : Mengembalikan array label kolom dalam Bahasa Indonesia baku sesuai standar audit sarpras.
     * 
     * @return array<int, string>
     */
    public function headings(): array
    {
        return [
            'No.',
            'Kode Tiket Laporan',
            'Nama Pelapor',
            'Identitas Pelapor',
            'Nama Fasilitas',
            'Kode Fasilitas',
            'Lokasi Gedung',
            'Kategori Kerusakan',
            'Deskripsi Keluhan',
            'Status Fasilitas Terkunci',
            'Tanggal Pelaporan',
            'Status Penanganan',
            'Teknisi / Petugas PIC',
            'Waktu Penyelesaian',
            'Catatan Resolusi',
        ];
    }

    /**
     * FUNCTION: download()
     * ------------------------------------------------------------------------------------------------
     * KEGUNAAN   : Mengalirkan data tiket kerusakan dalam format berkas CSV yang dioptimalkan untuk Microsoft Excel.
     * CARA KERJA :
     * 1. Menetapkan header respon HTTP stream untuk unduhan file attachment.
     * 2. Membuka stream output ke buffer `php://output`.
     * 3. Menulis UTF-8 BOM (`\xEF\xBB\xBF`) dan penanda pembagi kolom (`sep=,\r\n`).
     * 4. Menulis baris judul kolom melalui `fputcsv()`.
     * 5. Mengeksekusi database chunking per 500 baris untuk efisiensi alokasi RAM server.
     * 6. Memetakan nilai setiap kolom (termasuk status penguncian ruangan dan tanggal resolusi).
     * 7. Mengalirkan ke klien dan menutup stream handle.
     * 
     * @param string $filename Nama berkas lampiran (misal: 'rekap-kerusakan-aset-2026.csv')
     * @return StreamedResponse
     */
    public function download(string $filename): StreamedResponse
    {
        // Header HTTP untuk menginstruksikan browser mengunduh lampiran CSV secara langsung
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        // Mengembalikan instance StreamedResponse dengan callback penulisan bertahap
        return response()->stream(function () {
            $handle = fopen('php://output', 'w');

            // 1. Menulis UTF-8 BOM agar Excel mengenali format karakter internasional
            fputs($handle, "\xEF\xBB\xBF");

            // 2. Menulis instruksi penentu separator kolom koma
            fputs($handle, "sep=,\r\n");

            // 3. Menulis baris header kolom
            fputcsv($handle, $this->headings());

            // 4. Menulis baris data menggunakan chunking memori rendah
            $rowNumber = 0;
            $this->query()->chunk(500, function ($damageReports) use ($handle, &$rowNumber) {
                foreach ($damageReports as $row) {
                    $rowNumber++;
                    // Menulis baris data laporan kerusakan yang telah dinormalisasi
                    fputcsv($handle, [
                        $rowNumber,
                        $row->report_code,
                        $row->user?->name ?? 'Pelapor Anonim',
                        $row->user?->identifier ?? $row->user?->identity_number ?? '-',
                        $row->facility?->name ?? 'Fasilitas Tidak Dikenal',
                        $row->facility?->code ?? '-',
                        $row->facility?->building ?? '-',
                        $row->category ?? '-',
                        $row->description ?? '-',
                        $row->is_facility_locked ? 'YA (Terkunci)' : 'TIDAK',
                        $row->created_at ? $row->created_at->format('d/m/Y H:i') . ' WIB' : '-',
                        strtoupper($row->status ?? 'BARU'),
                        $row->handledBy?->name ?? '-',
                        $row->resolved_at ? $row->resolved_at->format('d/m/Y H:i') . ' WIB' : '-',
                        $row->resolution_note ?? '-',
                    ]);
                }
            });

            // Menutup stream output
            fclose($handle);
        }, 200, $headers);
    }
}
