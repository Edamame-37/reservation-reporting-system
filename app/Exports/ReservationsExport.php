<?php

/**
 * ====================================================================================================
 * SISTEM INFORMASI RESERVASI & PELAPORAN FASILITAS KAMPUS (CAVA)
 * MODUL 8: KONSOL ADMIN - REKAPITULASI ANALITIK, AUDIT OKUPANSI, & EKSPOR RESMI (PDF & EXCEL - ADM-04)
 * BERKAS: app/Exports/ReservationsExport.php
 * ====================================================================================================
 * 
 * DESKRIPSI BERKAS:
 * Generator berkas spreadsheet (Excel-compatible CSV) untuk rekapitulasi data permohonan reservasi
 * fasilitas kampus. Dirancang khusus untuk efisiensi performa tinggi (High-Performance Data Export)
 * dengan arsitektur streaming berbasis chunking memori rendah.
 * 
 * PERAN & ARSITEKTUR DALAM SISTEM CAVA:
 * 1. Pencegahan Kehabisan Memori (Memory Exhaustion / OOM Protection):
 *    Mengalirkan ribuan baris data transaksi sewa secara bertahap (chunking per 500 baris) langsung ke
 *    stream output browser (`php://output`) tanpa memuat seluruh baris database ke RAM sekaligus.
 * 2. Kompatibilitas Sempurna dengan Microsoft Excel:
 *    - Menyisipkan Byte Order Mark UTF-8 (`\xEF\xBB\xBF`) agar Microsoft Excel membuka karakter khusus/aksen tanpa korupsi encoding.
 *    - Menyertakan direktif pemisah kolom eksplisit (`sep=,\r\n`) agar Excel langsung membagi data ke dalam kolom A, B, C, dst.
 * 3. Eager Loading Anti N+1 Query:
 *    Mengoptimasi penarikan entitas terkait via `with(['user', 'facility'])` guna menjaga kecepatan kueri MySQL.
 * ====================================================================================================
 */

namespace App\Exports;

use App\Models\Reservation;
use Illuminate\Database\Eloquent\Builder;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Class ReservationsExport
 * 
 * Mengatur filter, penyusunan kueri, header kolom, dan pengaliran file spreadsheet data reservasi.
 */
class ReservationsExport
{
    /**
     * Rentang tanggal awal pelaksanaan reservasi (format YYYY-MM-DD).
     *
     * @var string|null
     */
    protected ?string $startDate;

    /**
     * Rentang tanggal akhir pelaksanaan reservasi (format YYYY-MM-DD).
     *
     * @var string|null
     */
    protected ?string $endDate;

    /**
     * Filter status persetujuan ('approved', 'pending', 'rejected', 'cancelled', 'all').
     *
     * @var string|null
     */
    protected ?string $status;

    /**
     * Konstruktor inisialisasi parameter kriteria ekspor data.
     *
     * @param string|null $startDate Tanggal awal batas pencarian
     * @param string|null $endDate Tanggal akhir batas pencarian
     * @param string|null $status Filter status persetujuan peminjaman
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
     * KEGUNAAN   : Membangun kueri Eloquent untuk menyaring transaksi reservasi yang akan diekspor.
     * CARA KERJA :
     * 1. Menyiapkan query builder `Reservation::query()`.
     * 2. Menerapkan Eager Loading relasi `user` (pemohon) dan `facility` (ruangan) untuk mencegah N+1 problem.
     * 3. Menyaring rentang tanggal `reservation_date` sesuai kombinasi `$this->startDate` dan `$this->endDate`.
     * 4. Menyaring status persetujuan jika parameter status disetel dan bukan bernilai 'all'.
     * 5. Mengurutkan hasil dari tanggal kegiatan terbaru (`reservation_date desc`) lalu jam mulai (`start_time asc`).
     * 
     * @return Builder
     */
    public function query(): Builder
    {
        // Menyiapkan kueri dasar dengan eager loading relasi pengguna pemohon dan fasilitas ruang
        $query = Reservation::query()->with(['user', 'facility']);

        // Penerapan filter rentang tanggal kegiatan
        if (!empty($this->startDate) && !empty($this->endDate)) {
            $query->whereBetween('reservation_date', [$this->startDate, $this->endDate]);
        } elseif (!empty($this->startDate)) {
            $query->where('reservation_date', '>=', $this->startDate);
        } elseif (!empty($this->endDate)) {
            $query->where('reservation_date', '<=', $this->endDate);
        }

        // Penerapan filter status spesifik jika dipilih oleh Admin
        if (!empty($this->status) && $this->status !== 'all') {
            $query->where('status', $this->status);
        }

        // Mengurutkan kronologis data: tanggal descending dan jam mulai ascending
        return $query->orderBy('reservation_date', 'desc')->orderBy('start_time', 'asc');
    }

    /**
     * FUNCTION: headings()
     * ------------------------------------------------------------------------------------------------
     * KEGUNAAN   : Menentukan label baris kepala (Header Kolom) pada dokumen spreadsheet.
     * CARA KERJA : Mengembalikan larik string judul kolom dalam format Bahasa Indonesia institusional.
     * 
     * @return array<int, string>
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
     * FUNCTION: download()
     * ------------------------------------------------------------------------------------------------
     * KEGUNAAN   : Mengalirkan data reservasi dalam format CSV berkode UTF-8 langsung ke browser pengguna.
     * CARA KERJA :
     * 1. Menetapkan header HTTP respon (`Content-Type: text/csv; charset=UTF-8` dan `Content-Disposition: attachment`).
     * 2. Membuka stream output PHP (`fopen('php://output', 'w')`).
     * 3. Menulis UTF-8 Byte Order Mark (`\xEF\xBB\xBF`) agar Excel otomatis mendeteksi encoding UTF-8.
     * 4. Menulis baris instruksi `sep=,\r\n` untuk memandu Excel menggunakan koma sebagai pemisah kolom.
     * 5. Menulis baris header kolom via `fputcsv()`.
     * 6. Mengambil data database secara bertahap per 500 baris menggunakan `$this->query()->chunk(500, ...)`.
     * 7. Mengalirkan setiap baris transaksi yang telah diformat langsung ke buffer keluaran.
     * 8. Menutup stream handler dan mengembalikan `StreamedResponse`.
     * 
     * @param string $filename Nama berkas lampiran saat diunduh (misal: 'rekap-reservasi-2026.csv')
     * @return StreamedResponse
     */
    public function download(string $filename): StreamedResponse
    {
        // Header HTTP untuk memaksa dialog unduhan berkas attachment pada browser
        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        // Mengembalikan instance StreamedResponse dengan callback generator baris
        return response()->stream(function () {
            // Membuka pointer stream langsung ke buffer output HTTP
            $handle = fopen('php://output', 'w');

            // 1. Menulis UTF-8 BOM untuk mencegah karakter rusak saat dibuka di Microsoft Excel
            fputs($handle, "\xEF\xBB\xBF");

            // 2. Memberitahukan parser Excel untuk menggunakan tanda koma sebagai pemisah kolom
            fputs($handle, "sep=,\r\n");

            // 3. Menulis baris header tabel spreadsheet
            fputcsv($handle, $this->headings());

            // 4. Mengambil data dengan chunking memori rendah (500 baris per iterasi kueri)
            $rowNumber = 0;
            $this->query()->chunk(500, function ($reservations) use ($handle, &$rowNumber) {
                foreach ($reservations as $row) {
                    $rowNumber++;
                    // Menulis baris data individual yang telah disanitasi
                    fputcsv($handle, [
                        $rowNumber,
                        $row->ticket_code,
                        $row->user?->name ?? 'Pengguna Tidak Dikenal',
                        $row->user?->identifier ?? $row->user?->identity_number ?? '-',
                        $row->user?->study_program ?? $row->user?->department ?? '-',
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

            // Menutup stream output
            fclose($handle);
        }, 200, $headers);
    }
}
