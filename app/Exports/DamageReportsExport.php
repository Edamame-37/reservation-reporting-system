<?php
/**
 * NAMA FILE    : DamageReportsExport.php
 * FUNGSI       : Generator Berkas Spreadsheet (.csv / Excel) Rekapitulasi Data Kerusakan Aset
 * DESKRIPSI    : Menyusun data tiket pengaduan kerusakan fisik dan malfungsi fasilitas kampus ke dalam format spreadsheet resmi dengan streaming chunking.
 * CARA KERJA   : Menerapkan UTF-8 BOM untuk kompatibilitas langsung Microsoft Excel dan chunking kueri per 500 baris.
 */

namespace App\Exports;

use App\Models\DamageReport;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DamageReportsExport
{
    /**
     * Rentang tanggal awal filter laporan
     *
     * @var string|null
     */
    protected ?string $startDate;

    /**
     * Rentang tanggal akhir filter laporan
     *
     * @var string|null
     */
    protected ?string $endDate;

    /**
     * Status filter laporan kerusakan (opsional)
     *
     * @var string|null
     */
    protected ?string $status;

    /**
     * Inisialisasi parameter filter ekspor
     *
     * @param string|null $startDate Tanggal awal (YYYY-MM-DD)
     * @param string|null $endDate Tanggal akhir (YYYY-MM-DD)
     * @param string|null $status Filter status penanganan
     */
    public function __construct(?string $startDate = null, ?string $endDate = null, ?string $status = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->status = $status;
    }

    /**
     * FUNCTION/PROCEDURE : query()
     * KEGUNAAN           : Membangun kueri Eloquent untuk mengambil data kerusakan aset sesuai filter.
     * CARA KERJA         : Menggunakan eager loading relasi 'user', 'facility', dan 'handledBy' serta menyaring tanggal pembuatan.
     * PARAMETER          : Tidak ada.
     * RETURN             : Builder
     */
    public function query(): Builder
    {
        $query = DamageReport::query()->with(['user', 'facility', 'handledBy']);

        if (!empty($this->startDate) && !empty($this->endDate)) {
            $query->whereBetween('created_at', [$this->startDate . ' 00:00:00', $this->endDate . ' 23:59:59']);
        } elseif (!empty($this->startDate)) {
            $query->where('created_at', '>=', $this->startDate . ' 00:00:00');
        } elseif (!empty($this->endDate)) {
            $query->where('created_at', '<=', $this->endDate . ' 23:59:59');
        }

        if (!empty($this->status) && $this->status !== 'all') {
            $query->where('status', $this->status);
        }

        return $query->orderBy('created_at', 'desc');
    }

    /**
     * FUNCTION/PROCEDURE : headings()
     * KEGUNAAN           : Menentukan baris header kolom pada lembar kerja spreadsheet.
     * CARA KERJA         : Mengembalikan senarai string teks header dalam Bahasa Indonesia baku.
     * PARAMETER          : Tidak ada.
     * RETURN             : array<int, string>
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
     * FUNCTION/PROCEDURE : download()
     * KEGUNAAN           : Mengalirkan data insiden kerusakan dalam bentuk berkas CSV yang kompatibel penuh dengan Microsoft Excel.
     * CARA KERJA         : Menggunakan StreamedResponse dengan UTF-8 BOM dan chunking memori per 500 baris.
     * PARAMETER          : string $filename
     * RETURN             : StreamedResponse
     */
    public function download(string $filename): StreamedResponse
    {
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        return response()->stream(function () {
            $handle = fopen('php://output', 'w');

            // Menulis UTF-8 BOM agar Microsoft Excel langsung mengenali format dan encoding tanpa error
            fputs($handle, "\xEF\xBB\xBF");

            // Tulis Header Kolom
            fputcsv($handle, $this->headings());

            // Tulis baris data menggunakan chunking untuk efisiensi memori (Poin 5 ADM-04)
            $rowNumber = 0;
            $this->query()->chunk(500, function ($damageReports) use ($handle, &$rowNumber) {
                foreach ($damageReports as $row) {
                    $rowNumber++;
                    fputcsv($handle, [
                        $rowNumber,
                        $row->report_code,
                        $row->user?->name ?? 'Pelapor Anonim',
                        $row->user?->identifier ?? '-',
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

            fclose($handle);
        }, 200, $headers);
    }
}
