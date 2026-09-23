<?php
/**
 * NAMA FILE    : ReservationsExport.php
 * FUNGSI       : Generator Berkas Spreadsheet (.csv / Excel) Rekapitulasi Data Reservasi
 * DESKRIPSI    : Menyusun data permohonan reservasi ruang kampus ke dalam format baris tabel spreadsheet Excel dengan streaming chunking anti memory limit.
 * CARA KERJA   : Menerapkan UTF-8 BOM untuk kompatibilitas langsung Microsoft Excel dan chunking kueri per 500 baris.
 */

namespace App\Exports;

use App\Models\Reservation;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReservationsExport
{
    /**
     * Rentang tanggal awal filter reservasi
     *
     * @var string|null
     */
    protected ?string $startDate;

    /**
     * Rentang tanggal akhir filter reservasi
     *
     * @var string|null
     */
    protected ?string $endDate;

    /**
     * Status filter reservasi (opsional)
     *
     * @var string|null
     */
    protected ?string $status;

    /**
     * Inisialisasi parameter filter ekspor
     *
     * @param string|null $startDate Tanggal awal (YYYY-MM-DD)
     * @param string|null $endDate Tanggal akhir (YYYY-MM-DD)
     * @param string|null $status Filter status approval
     */
    public function __construct(?string $startDate = null, ?string $endDate = null, ?string $status = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->status = $status;
    }

    /**
     * FUNCTION/PROCEDURE : query()
     * KEGUNAAN           : Membangun kueri Eloquent untuk mengambil data reservasi sesuai filter.
     * CARA KERJA         : Menggunakan eager loading relasi 'user' dan 'facility' serta menyaring rentang tanggal.
     * PARAMETER          : Tidak ada.
     * RETURN             : Builder
     */
    public function query(): Builder
    {
        $query = Reservation::query()->with(['user', 'facility']);

        if (!empty($this->startDate) && !empty($this->endDate)) {
            $query->whereBetween('reservation_date', [$this->startDate, $this->endDate]);
        } elseif (!empty($this->startDate)) {
            $query->where('reservation_date', '>=', $this->startDate);
        } elseif (!empty($this->endDate)) {
            $query->where('reservation_date', '<=', $this->endDate);
        }

        if (!empty($this->status) && $this->status !== 'all') {
            $query->where('status', $this->status);
        }

        return $query->orderBy('reservation_date', 'desc')->orderBy('start_time', 'asc');
    }

    /**
     * FUNCTION/PROCEDURE : headings()
     * KEGUNAAN           : Menentukan judul baris teratas (header kolom) pada spreadsheet.
     * CARA KERJA         : Mengembalikan senarai string teks header dalam Bahasa Indonesia baku.
     * PARAMETER          : Tidak ada.
     * RETURN             : array<int, string>
     */
    public function headings(): array
    {
        return [
            'No.',
            'Kode Tiket',
            'Nama Pemohon',
            'NIM / NIDN',
            'Program Studi / Unit',
            'Nama Ruang / Fasilitas',
            'Kode Ruang',
            'Lokasi Gedung',
            'Tanggal Kegiatan',
            'Jam Mulai',
            'Jam Selesai',
            'Total Slot (30m)',
            'Jumlah Peserta',
            'Tujuan / Nama Acara',
            'Status Persetujuan',
        ];
    }

    /**
     * FUNCTION/PROCEDURE : download()
     * KEGUNAAN           : Mengalirkan data reservasi dalam bentuk berkas CSV yang kompatibel penuh dengan Microsoft Excel.
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

            // Menyisipkan instruksi pemisah kolom agar Microsoft Excel otomatis memetakan kolom A, B, C, dst.
            fputs($handle, "sep=,\r\n");

            // Tulis Header Kolom
            fputcsv($handle, $this->headings());

            // Tulis baris data menggunakan chunking untuk efisiensi memori (Poin 5 ADM-04)
            $rowNumber = 0;
            $this->query()->chunk(500, function ($reservations) use ($handle, &$rowNumber) {
                foreach ($reservations as $row) {
                    $rowNumber++;
                    fputcsv($handle, [
                        $rowNumber,
                        $row->ticket_code,
                        $row->user?->name ?? 'Pengguna Tidak Dikenal',
                        $row->user?->identifier ?? '-',
                        $row->user?->study_program ?? '-',
                        $row->facility?->name ?? 'Fasilitas Tidak Dikenal',
                        $row->facility?->code ?? '-',
                        $row->facility?->building ?? '-',
                        $row->reservation_date ? $row->reservation_date->format('d/m/Y') : '-',
                        substr($row->start_time ?? '00:00', 0, 5) . ' WIB',
                        substr($row->end_time ?? '00:00', 0, 5) . ' WIB',
                        $row->total_slots ?? 0,
                        $row->participants_count ?? 1,
                        $row->purpose ?? '-',
                        strtoupper($row->status ?? 'PENDING'),
                    ]);
                }
            });

            fclose($handle);
        }, 200, $headers);
    }
}
