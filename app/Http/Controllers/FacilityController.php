<?php
/**
 * NAMA FILE    : FacilityController.php
 * FUNGSI       : Controller pengelola master data inventaris fasilitas & ruangan kampus (Konsol Admin)
 * DESKRIPSI    : Bertanggung jawab menangani siklus lengkap CRUD fasilitas kampus (ADM-03 / US-16), manajemen berkas foto sampul, penguncian status operasional, dan penghapusan lunak (soft delete).
 * CARA KERJA   : Menerima HTTP Request terotentikasi, memvalidasi input melalui FormRequest (Store/UpdateFacilityRequest), mengelola unggahan media di disk publik, serta memperbarui tabel facilities.
 */

namespace App\Http\Controllers;

use App\Http\Requests\Admin\StoreFacilityRequest;
use App\Http\Requests\Admin\UpdateFacilityRequest;
use App\Models\Facility;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class FacilityController extends Controller
{
    /**
     * FUNCTION/PROCEDURE : index()
     * KEGUNAAN           : Menampilkan tabel daftar master fasilitas kampus beserta metrik statistik ringkasan inventaris.
     * CARA KERJA         : Mengambil data fasilitas dengan dukungan filter pencarian, kategori, dan status operasional, lalu merender view admin.facility-master.
     */
    public function index(Request $request): View|JsonResponse
    {
        $search   = $request->query('search');
        $category = $request->query('category');
        $building = $request->query('building');
        $status   = $request->query('status');

        $query = Facility::query();

        // 1. Filter Pencarian Teks Bebas (Nama Ruang, Kode, atau Gedung)
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('building', 'like', "%{$search}%")
                  ->orWhere('floor_location', 'like', "%{$search}%");
            });
        }

        // 2. Filter Kategori Ruangan
        if ($category && $category !== 'all') {
            $query->where('category', $category);
        }

        // 3. Filter Lokasi Gedung
        if ($building && $building !== 'all') {
            $query->where('building', $building);
        }

        // 4. Filter Status Operasional
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        $facilities = $query->orderBy('name', 'asc')->paginate(10)->withQueryString();

        // 5. Agregasi Statistik Ringkasan Dasbor (Top Metric Cards)
        $totalCount       = Facility::count();
        $activeCount      = Facility::where('status', 'aktif')->count();
        $maintenanceCount = Facility::where('status', 'dalam perbaikan')->count();
        $totalCapacity    = (int) Facility::sum('capacity');
        $buildingCount    = Facility::distinct('building')->count('building');

        if ($request->wantsJson()) {
            return response()->json([
                'data'       => $facilities,
                'statistics' => [
                    'total'         => $totalCount,
                    'active'        => $activeCount,
                    'maintenance'   => $maintenanceCount,
                    'capacity'      => $totalCapacity,
                    'buildingCount' => $buildingCount,
                ],
            ]);
        }

        return view('admin.facility-master', compact(
            'facilities',
            'totalCount',
            'activeCount',
            'maintenanceCount',
            'totalCapacity',
            'buildingCount'
        ));
    }

    /**
     * FUNCTION/PROCEDURE : create()
     * KEGUNAAN           : Mengarahkan pengguna ke halaman pembuatan fasilitas kampus baru.
     * CARA KERJA         : Mengalihkan request ke halaman admin.facility-master karena form input diintegrasikan melalui modal antarmuka.
     */
    public function create(): RedirectResponse
    {
        return redirect()->route('admin.facility-master');
    }

    /**
     * FUNCTION/PROCEDURE : store()
     * KEGUNAAN           : Menyimpan entitas fasilitas baru ke dalam database beserta berkas foto sampul (cover image).
     * CARA KERJA         : Menerima payload valid dari StoreFacilityRequest, mengunggah foto ke folder storage/facilities/ pada disk public, dan menyimpan record ke tabel facilities.
     */
    public function store(StoreFacilityRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();

        try {
            DB::beginTransaction();

            // 1. Penanganan Unggah Berkas Foto Cover
            if ($request->hasFile('cover_image')) {
                $validated['image_path'] = $request->file('cover_image')->store('facilities', 'public');
            }

            // 2. Normalisasi Array Perlengkapan (Equipment)
            if (isset($validated['equipment']) && is_string($validated['equipment'])) {
                $decoded = json_decode($validated['equipment'], true);
                $validated['equipment'] = is_array($decoded)
                    ? $decoded
                    : array_filter(array_map('trim', explode(',', $validated['equipment'])));
            }

            // 3. Status Default
            $validated['status'] = $validated['status'] ?? 'aktif';

            // 4. Simpan ke Basis Data
            $facility = Facility::create($validated);

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Fasilitas baru berhasil ditambahkan!',
                    'data'    => $facility,
                ], 201);
            }

            return redirect()->route('admin.facility-master')->with('success', 'Fasilitas baru berhasil ditambahkan!');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Gagal menambahkan fasilitas: {$e->getMessage()}", ['exception' => $e]);

            // Bersihkan file yang terlanjur terunggah jika transaksi database gagal
            if (isset($validated['image_path']) && Storage::disk('public')->exists($validated['image_path'])) {
                Storage::disk('public')->delete($validated['image_path']);
            }

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan sistem saat menyimpan fasilitas baru.',
                ], 500);
            }

            return back()->withInput()->with('error', 'Terjadi kesalahan sistem saat menyimpan fasilitas baru.');
        }
    }

    /**
     * FUNCTION/PROCEDURE : saveAlias()
     * KEGUNAAN           : Endpoint alias untuk menangani submit form modal gabungan (create & update).
     * CARA KERJA         : Memeriksa keberadaan parameter ID. Jika ada, teruskan ke method update. Jika tidak, ke store.
     */
    public function saveAlias(Request $request): RedirectResponse|JsonResponse
    {
        if ($request->filled('id')) {
            $updateRequest = app(UpdateFacilityRequest::class);
            return $this->update($updateRequest, $request->input('id'));
        }
        
        $storeRequest = app(StoreFacilityRequest::class);
        return $this->store($storeRequest);
    }

    /**
     * FUNCTION/PROCEDURE : edit()
     * KEGUNAAN           : Mengambil data satu fasilitas spesifik untuk kebutuhan formulir penyuntingan (modal edit).
     * CARA KERJA         : Mencari data fasilitas berdasarkan ID, mengembalikan respons JSON untuk konsumsi modal Alpine.js frontend.
     */
    public function edit(int|string $id): JsonResponse|View
    {
        $facility = Facility::findOrFail($id);

        if (request()->wantsJson()) {
            return response()->json([
                'success' => true,
                'data'    => $facility,
            ]);
        }

        return view('admin.facility-master', compact('facility'));
    }

    /**
     * FUNCTION/PROCEDURE : update()
     * KEGUNAAN           : Memperbarui spesifikasi, kapasitas, atau foto fasilitas yang sudah terdaftar.
     * CARA KERJA         : Memvalidasi data melalui UpdateFacilityRequest, mengganti foto cover fisik lama jika foto baru dikirimkan, lalu memperbarui baris data di tabel facilities.
     */
    public function update(UpdateFacilityRequest $request, int|string $id): RedirectResponse|JsonResponse
    {
        $facility = Facility::findOrFail($id);
        $validated = $request->validated();

        try {
            DB::beginTransaction();

            // 1. Penanganan Penggantian Foto Cover & Penghapusan File Lama
            if ($request->hasFile('cover_image')) {
                if ($facility->image_path && Storage::disk('public')->exists($facility->image_path)) {
                    Storage::disk('public')->delete($facility->image_path);
                }
                $validated['image_path'] = $request->file('cover_image')->store('facilities', 'public');
            }

            // 2. Normalisasi Array Perlengkapan (Equipment)
            if (isset($validated['equipment']) && is_string($validated['equipment'])) {
                $decoded = json_decode($validated['equipment'], true);
                $validated['equipment'] = is_array($decoded)
                    ? $decoded
                    : array_filter(array_map('trim', explode(',', $validated['equipment'])));
            }

            // 3. Simpan Pembaruan
            $facility->update($validated);

            DB::commit();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Data fasilitas berhasil diperbarui!',
                    'data'    => $facility,
                ]);
            }

            return redirect()->route('admin.facility-master')->with('success', 'Data fasilitas berhasil diperbarui!');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error("Gagal memperbarui fasilitas [ID: {$id}]: {$e->getMessage()}", ['exception' => $e]);

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Terjadi kesalahan sistem saat memperbarui data fasilitas.',
                ], 500);
            }

            return back()->withInput()->with('error', 'Terjadi kesalahan sistem saat memperbarui data fasilitas.');
        }
    }

    /**
     * FUNCTION/PROCEDURE : destroy()
     * KEGUNAAN           : Mencabut fasilitas dari peredaran sistem menggunakan metode Soft Delete.
     * CARA KERJA         : Mengubah nilai kolom status menjadi 'nonaktif', lalu memanggil delete() untuk mengisi deleted_at agar riwayat reservasi lama tidak rusak.
     */
    public function destroy(int|string $id): RedirectResponse|JsonResponse
    {
        $facility = Facility::findOrFail($id);

        try {
            // Ubah status operasional menjadi nonaktif terlebih dahulu
            $facility->status = 'nonaktif';
            $facility->save();

            // Eksekusi SoftDeletes (isi kolom deleted_at tanpa menghapus baris fisik)
            $facility->delete();

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Fasilitas berhasil dinonaktifkan dari sistem.',
                ]);
            }

            return redirect()->route('admin.facility-master')->with('success', 'Fasilitas berhasil dinonaktifkan dari sistem.');
        } catch (\Throwable $e) {
            Log::error("Gagal menghapus lunak fasilitas [ID: {$id}]: {$e->getMessage()}", ['exception' => $e]);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menonaktifkan fasilitas dari sistem.',
                ], 500);
            }

            return back()->with('error', 'Gagal menonaktifkan fasilitas dari sistem.');
        }
    }

    /**
     * FUNCTION/PROCEDURE : toggleStatus()
     * KEGUNAAN           : Mengubah status operasional fasilitas secara cepat antara 'aktif' dan 'dalam perbaikan'.
     * CARA KERJA         : Memeriksa status saat ini, membalik status ('aktif' <-> 'dalam perbaikan'), dan menyimpan perubahan ke basis data.
     */
    public function toggleStatus(int|string $id): RedirectResponse|JsonResponse
    {
        $facility = Facility::findOrFail($id);

        try {
            $newStatus = ($facility->status === 'aktif') ? 'dalam perbaikan' : 'aktif';
            $facility->status = $newStatus;
            $facility->save();

            $statusLabel = ($newStatus === 'aktif') ? 'diaktifkan kembali' : 'dikunci untuk perbaikan';

            if (request()->wantsJson()) {
                return response()->json([
                    'success'    => true,
                    'message'    => "Fasilitas berhasil {$statusLabel}.",
                    'status'     => $newStatus,
                ]);
            }

            return redirect()->route('admin.facility-master')->with('success', "Fasilitas {$facility->name} berhasil {$statusLabel}.");
        } catch (\Throwable $e) {
            Log::error("Gagal mengubah status fasilitas [ID: {$id}]: {$e->getMessage()}", ['exception' => $e]);

            if (request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal mengubah status operasional fasilitas.',
                ], 500);
            }

            return back()->with('error', 'Gagal mengubah status operasional fasilitas.');
        }
    }
}
