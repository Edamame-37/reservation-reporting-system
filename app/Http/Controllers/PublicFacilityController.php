<?php
/**
 * NAMA FILE    : PublicFacilityController.php
 * FUNGSI       : Menangani katalog dan matriks ketersediaan ruang untuk publik.
 * DESKRIPSI    : Menyediakan endpoint untuk merender halaman katalog, halaman kalender matriks, serta API JSON yang memberikan daftar slot waktu terpakai berdasar reservasi yang disetujui.
 * CARA KERJA   : Menerima request HTTP GET, melakukan kueri ke tabel facilities dan reservations (menyembunyikan privasi pemesan), lalu mengembalikan view atau JSON ke Frontend.
 */

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Carbon\Carbon;

class PublicFacilityController extends Controller
{
    /**
     * Menampilkan katalog daftar fasilitas aktif.
     */
    public function index(Request $request)
    {
        $query = Facility::where('status', '!=', 'nonaktif');

        // Filter berdasarkan kata kunci multi-kolom
        if ($request->filled('search')) {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('code', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('building', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('equipment', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Filter tipe ruangan
        if ($request->filled('category') && $request->category !== 'semua') {
            $query->where('category', $request->category);
        }

        // Filter gedung
        if ($request->filled('building') && $request->building !== 'semua') {
            $query->where('building', 'LIKE', '%' . $request->building . '%');
        }

        // Ambil data yang lolos filter
        $facilities = $query->orderBy('name', 'asc')
            ->get()
            ->map(function ($f) {
                return [
                    'id' => $f->id,
                    'code' => $f->code,
                    'name' => $f->name,
                    'category' => strtolower($f->category ?? 'umum'),
                    'building' => $f->building . ($f->floor_location ? ' (Lt. ' . $f->floor_location . ')' : ''),
                    'capacity' => $f->capacity,
                    'status' => $f->status === 'dalam perbaikan' ? 'locked' : 'approved',
                    'equipment' => is_array($f->equipment) ? $f->equipment : (json_decode($f->equipment, true) ?? []),
                    'desc' => $f->description,
                    'image' => $f->image_path
                ];
            });

        return view('public.catalog', compact('facilities'));
    }

    /**
     * Menampilkan matriks ketersediaan (frontend view).
     */
    public function availability()
    {
        $facilities = Facility::where('status', '!=', 'nonaktif')
            ->orderBy('name', 'asc')
            ->get()
            ->map(function ($f) {
                return [
                    'id' => $f->id,
                    'name' => $f->name,
                    'building' => $f->building . ($f->floor_location ? ' (Lt. ' . $f->floor_location . ')' : ''),
                    'capacity' => $f->capacity,
                    'occupied' => [], // Akan diisi dinamis via Fetch API Alpine.js
                    'locked' => $f->status === 'dalam perbaikan',
                ];
            });

        return view('public.availability', compact('facilities'));
    }

    /**
     * Endpoint API: Menarik matriks slot terpakai semua ruang untuk tanggal tertentu.
     */
    public function getMatrixAvailability($date)
    {
        // 1. Validasi format tanggal
        try {
            $parsedDate = Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Format tanggal tidak valid'], 400);
        }

        // 2. Ambil seluruh reservasi berstatus approved pada tanggal tersebut
        $reservations = Reservation::where('status', 'approved')
            ->whereDate('start_time', $parsedDate)
            ->get(['facility_id', 'start_time', 'end_time']);

        $matrix = [];

        // 3. Konversi rentang waktu ke slot 30 menit
        foreach ($reservations as $r) {
            $fid = $r->facility_id;
            if (!isset($matrix[$fid])) {
                $matrix[$fid] = [];
            }

            $start = Carbon::parse($r->start_time);
            $end = Carbon::parse($r->end_time);

            // Kita buat blok per 30 menit
            while ($start < $end) {
                // Format jam:menit
                $timeString = $start->format('H:i');
                // Hindari duplikasi jika ada overlapping (meski seharusnya tak ada di status approved)
                if (!in_array($timeString, $matrix[$fid])) {
                    $matrix[$fid][] = $timeString;
                }
                $start->addMinutes(30);
            }
        }

        return response()->json($matrix);
    }

    /**
     * Endpoint API: Menarik ketersediaan 1 fasilitas secara spesifik.
     */
    public function showAvailability($id, $date)
    {
        $facility = Facility::find($id);
        if (!$facility) {
            return response()->json(['error' => 'Fasilitas tidak ditemukan'], 404);
        }

        if ($facility->status === 'dalam perbaikan' || $facility->status === 'nonaktif') {
            return response()->json([
                'status' => 'maintenance',
                'message' => 'Fasilitas sedang ditutup atau dalam perbaikan.'
            ]);
        }

        try {
            $parsedDate = Carbon::parse($date)->format('Y-m-d');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Format tanggal tidak valid'], 400);
        }

        $bookedSlots = Reservation::where('facility_id', $id)
            ->where('status', 'approved')
            ->whereDate('start_time', $parsedDate)
            ->get(['start_time', 'end_time']);

        return response()->json([
            'status' => 'success',
            'data' => $bookedSlots
        ]);
    }

    /**
     * Endpoint API: Mencari fasilitas secara spesifik untuk fitur autocomplete
     */
    public function autocomplete(Request $request)
    {
        $query = Facility::where('status', '!=', 'nonaktif');

        if ($request->filled('q')) {
            $searchTerm = $request->q;
            $query->where(function($q) use ($searchTerm) {
                $q->where('name', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('code', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('description', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('building', 'LIKE', "%{$searchTerm}%")
                  ->orWhere('equipment', 'LIKE', "%{$searchTerm}%");
            });
        }

        // Limit data to prevent huge payload on live search
        $results = $query->take(5)->get()->map(function($f) {
            return [
                'id' => $f->id,
                'code' => $f->code,
                'name' => $f->name,
                'category' => strtolower($f->category ?? 'umum'),
                'building' => $f->building . ($f->floor_location ? ' (Lt. ' . $f->floor_location . ')' : ''),
                'capacity' => $f->capacity,
                'status' => $f->status === 'dalam perbaikan' ? 'locked' : 'approved',
                'equipment' => is_array($f->equipment) ? $f->equipment : (json_decode($f->equipment, true) ?? []),
                'desc' => $f->description,
                'image' => $f->image_path
            ];
        });

        return response()->json($results);
    }
}
