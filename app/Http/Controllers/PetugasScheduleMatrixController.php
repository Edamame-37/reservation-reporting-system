<?php
/**
 * NAMA FILE    : PetugasScheduleMatrixController.php
 * FUNGSI       : Controller visualisasi matriks jadwal 30 menit & peninjauan detail pemohon untuk Petugas Sarpras
 * DESKRIPSI    : Menyajikan matriks jadwal ketersediaan seluruh fasilitas per slot 30 menit (07:00 - 20:00 WIB)
 *                dengan akses informasi lengkap bagi petugas (nama pemohon, NIM/NIP, kontak, surat izin, dan status).
 * CARA KERJA   : Merender tampilan Bento UI petugas, memuat data fasilitas dan endpoint JSON data slot terisi secara mendalam.
 */

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PetugasScheduleMatrixController extends Controller
{
    /**
     * Menampilkan lembar matriks jadwal 30 menit operasional petugas dengan Bento UI.
     */
    public function index(Request $request): View
    {
        $selectedDate = $request->query('date', now()->format('Y-m-d'));
        $selectedBuilding = $request->query('building', 'semua');

        // 1. Ambil daftar seluruh gedung unik untuk filter dropdown
        $buildings = Facility::where('status', '!=', 'nonaktif')
            ->select('building')
            ->distinct()
            ->orderBy('building')
            ->pluck('building');

        // 2. Query fasilitas dengan filter gedung
        $facilityQuery = Facility::where('status', '!=', 'nonaktif');
        if ($selectedBuilding && $selectedBuilding !== 'semua') {
            $facilityQuery->where('building', $selectedBuilding);
        }
        $facilities = $facilityQuery->orderBy('building')->orderBy('name')->get();

        // 3. Hitung ringkasan metrik Bento untuk tanggal terpilih
        $dayReservations = Reservation::whereDate('reservation_date', $selectedDate)->get();
        $approvedToday = $dayReservations->where('status', 'approved')->count();
        $pendingToday = $dayReservations->where('status', 'pending')->count();
        $totalFacilities = Facility::where('status', '!=', 'nonaktif')->count();
        $maintenanceCount = Facility::where('status', 'dalam perbaikan')->count();

        return view('petugas.schedule-matrix', compact(
            'facilities',
            'buildings',
            'selectedDate',
            'selectedBuilding',
            'approvedToday',
            'pendingToday',
            'totalFacilities',
            'maintenanceCount'
        ));
    }

    /**
     * Endpoint API: Menarik detail mendalam seluruh slot terpakai untuk petugas pada tanggal tertentu.
     */
    public function getMatrixData(string $date): JsonResponse
    {
        try {
            $parsedDate = Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Format tanggal tidak valid'], 400);
        }

        // Ambil reservasi berstatus approved & pending pada tanggal tersebut beserta relasi user & reviewer
        $reservations = Reservation::with(['user', 'facility', 'reviewer'])
            ->whereDate('reservation_date', $parsedDate)
            ->whereIn('status', ['approved', 'pending'])
            ->get();

        $matrix = [];
        $bookedSlotsCount = 0;
        $activeRooms = [];

        foreach ($reservations as $res) {
            $fid = $res->facility_id;
            if (!isset($matrix[$fid])) {
                $matrix[$fid] = [];
            }

            $activeRooms[$fid] = true;

            $start = Carbon::parse($res->reservation_date->format('Y-m-d') . ' ' . $res->start_time);
            $end = Carbon::parse($res->reservation_date->format('Y-m-d') . ' ' . $res->end_time);

            $detail = [
                'id' => $res->id,
                'ticket_code' => $res->ticket_code,
                'status' => $res->status,
                'applicant_name' => $res->user->name ?? 'Pemohon Sivitas',
                'applicant_identity' => $res->user->identity_number ?? '-',
                'applicant_email' => $res->user->email ?? '-',
                'applicant_department' => $res->user->department ?? '-',
                'applicant_role' => $res->user->role ?? 'mahasiswa',
                'facility_id' => $res->facility_id,
                'facility_name' => $res->facility->name ?? 'Fasilitas',
                'facility_building' => $res->facility->building ?? '-',
                'facility_floor' => $res->facility->floor_location ?? '-',
                'facility_capacity' => $res->facility->capacity ?? '-',
                'reservation_date' => $res->reservation_date->translatedFormat('d F Y'),
                'start_time' => substr($res->start_time, 0, 5),
                'end_time' => substr($res->end_time, 0, 5),
                'total_slots' => $res->total_slots,
                'purpose' => $res->purpose,
                'participants_count' => $res->participants_count,
                'permit_letter_path' => $res->permit_letter_path ? asset('storage/' . $res->permit_letter_path) : null,
                'reviewed_by_name' => $res->reviewer->name ?? null,
                'reviewed_at' => $res->reviewed_at ? $res->reviewed_at->translatedFormat('d M Y H:i') : null,
            ];

            while ($start < $end) {
                $timeString = $start->format('H:i');
                $matrix[$fid][$timeString] = $detail;
                $bookedSlotsCount++;
                $start->addMinutes(30);
            }
        }

        return response()->json([
            'matrix' => $matrix,
            'summary' => [
                'booked_slots_count' => $bookedSlotsCount,
                'active_rooms_count' => count($activeRooms),
                'total_reservations' => $reservations->count(),
            ]
        ]);
    }
}
