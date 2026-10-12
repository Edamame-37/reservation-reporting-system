<?php

/**
 * ==============================================================================================
 * CONTROLLER: MANAJEMEN MASTER DATA FASILITAS & RUANGAN (KONSOL ADMIN - ADM-03)
 * ==============================================================================================
 * Berkas: app/Http/Controllers/FacilityController.php
 * 
 * PERAN & FUNGSI UTAMA:
 * 1. Mengendalikan siklus hidup penuh (Full CRUD) inventaris ruangan dan sarana prasarana kampus.
 * 2. Mengelola pencarian multi-dimensi, filter kategori, filter gedung, dan paginasi data
 *    dengan pemeliharaan parameter URL (`withQueryString()`).
 * 3. Mengkalkulasi metrik agregat dasbor fasilitas secara real-time (total venue, venue aktif,
 *    pemeliharaan, kapasitas total, dan sebaran gedung).
 * 4. Mengelola unggahan berkas gambar cover ke disk publik (`Storage::disk('public')`),
 *    termasuk pembersihan otomatis berkas lama saat foto diperbarui atau saat transaksi gagal.
 * 5. Menjamin konsistensi data basis data menggunakan Database Transactions (`DB::beginTransaction()`).
 * 6. Mendukung negosiasi konten ganda: merender Blade View untuk navigasi web standar dan
 *    mengembalikan JSON API untuk interaksi modal dinamis Alpine.js.
 * ==============================================================================================
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
     * Menampilkan daftar master fasilitas kampus beserta metrik statistik ringkasan inventaris.
     * 
     * CARA KERJA & ALUR:
     * 1. Menangkap parameter query string: `search`, `category`, `building`, dan `status`.
     * 2. Membangun kueri Eloquent secara dinamis menggunakan nested closure WHERE untuk pencarian teks.
     * 3. Menerapkan paginasi 10 data per halaman dengan `withQueryString()`.
     * 4. Menghitung ringkasan KPI (agregasi SQL: count, sum, distinct).
     * 5. Merender view `admin.facility-master` atau mengembalikan JSON jika diminta via AJAX.
     * 
     * @param  Request  $request
     * @return View|JsonResponse
     */
    public function index(Request $request): View|JsonResponse
    {
        // Pengambilan parameter filter dari URL (contoh: ?search=auditorium&category=lab):
        $search   = $request->query('search');
        $category = $request->query('category');
        $building = $request->query('building');
        $status   = $request->query('status');

        $query = Facility::query();

        // ------------------------------------------------------------------------------------------
        // 1. FILTER PENCARIAN TEKS BEBAS (FULL-TEXT LIKE QUERY)
        // ------------------------------------------------------------------------------------------
        // Menggunakan closure bersarang agar klausul OR diapit tanda kurung dalam SQL:
        // "WHERE ... AND (name LIKE ? OR code LIKE ? OR building LIKE ? OR floor_location LIKE ?)"
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('building', 'like', "%{$search}%")
                  ->orWhere('floor_location', 'like', "%{$search}%");
            });
        }

        // ------------------------------------------------------------------------------------------
        // 2. FILTER KATEGORI RUANGAN
        // ------------------------------------------------------------------------------------------
        if ($category && $category !== 'all') {
            $query->where('category', $category);
        }

        // ------------------------------------------------------------------------------------------
        // 3. FILTER LOKASI GEDUNG
        // ------------------------------------------------------------------------------------------
        if ($building && $building !== 'all') {
            $query->where('building', $building);
        }

        // ------------------------------------------------------------------------------------------
        // 4. FILTER STATUS OPERASIONAL
        // ------------------------------------------------------------------------------------------
        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        // Paginasi: 10 data per halaman terurut abjad.
        // ->withQueryString() menjaga filter tetap aktif saat admin mengklik tombol halaman 2, 3, dst.
        $facilities = $query->orderBy('name', 'asc')->paginate(10)->withQueryString();

        // ------------------------------------------------------------------------------------------
        // 5. AGREGASI STATISTIK RINGKASAN INVENTARIS (TOP KPI CARDS)
        // ------------------------------------------------------------------------------------------
        $totalCount       = Facility::count();                                       // Total seluruh ruangan
        $activeCount      = Facility::where('status', 'aktif')->count();              // Ruangan siap pakai
        $maintenanceCount = Facility::where('status', 'dalam perbaikan')->count();    // Ruangan terkunci perbaikan
        $totalCapacity    = (int) Facility::sum('capacity');                          // Total daya tampung kampus
        $buildingCount    = Facility::distinct('building')->count('building');        // Jumlah gedung unik

        // Negosiasi Konten (Content Negotiation): Mengembalikan JSON jika diminta via AJAX / fetch API
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

        // Merender tampilan Blade admin lengkap dengan data fasilitas dan metrik KPI:
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
     * Mengarahkan pengguna ke halaman pembuatan fasilitas kampus baru.
     * 
     * CARA KERJA & ALUR:
     * Karena antarmuka penambahan ruangan menggunakan modal interaktif Alpine.js pada halaman utama,
     * pemanggilan GET rute ini langsung dialihkan (redirect) ke `admin.facility-master`.
     * 
     * @return RedirectResponse
     */
    public function create(): RedirectResponse
    {
        return redirect()->route('admin.facility-master');
    }

    /**
     * Menyimpan entitas fasilitas baru ke dalam database beserta berkas foto sampul (cover image).
     * 
     * CARA KERJA & ALUR:
     * 1. Memvalidasi payload request melalui `StoreFacilityRequest`.
     * 2. Membuka Database Transaction (`DB::beginTransaction()`).
     * 3. Menyimpan berkas gambar ke direktori `storage/app/public/facilities/`.
     * 4. Menormalkan input daftar perlengkapan (`equipment`) menjadi array PHP.
     * 5. Mengeksekusi `Facility::create()` dan melakukan `DB::commit()`.
     * 6. Jika terjadi error, melakukan `DB::rollBack()` dan menghapus file gambar yatim (orphaned file).
     * 
     * @param  StoreFacilityRequest  $request
     * @return RedirectResponse|JsonResponse
     */
    public function store(StoreFacilityRequest $request): RedirectResponse|JsonResponse
    {
        $validated = $request->validated();

        try {
            DB::beginTransaction();

            // 1. Penanganan Unggah Berkas Foto Cover:
            // $request->file('cover_image')->store('facilities', 'public') menyimpan gambar
            // dengan nama acak hash unik di folder 'storage/app/public/facilities'.
            if ($request->hasFile('cover_image')) {
                $validated['image_path'] = $request->file('cover_image')->store('facilities', 'public');
            }

            // 2. Normalisasi Array Perlengkapan (Equipment):
            // Memastikan data string JSON atau string koma terpisah diubah menjadi PHP Array murni.
            if (isset($validated['equipment']) && is_string($validated['equipment'])) {
                $decoded = json_decode($validated['equipment'], true);
                $validated['equipment'] = is_array($decoded)
                    ? $decoded
                    : array_filter(array_map('trim', explode(',', $validated['equipment'])));
            }

            // 3. Status Operasional Default:
            $validated['status'] = $validated['status'] ?? 'aktif';

            // 4. Simpan ke Basis Data:
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

            // Pembersihan Defensif: Hapus file fisik yang terlanjur terupload jika transaksi database gagal
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
     * Endpoint alias untuk menangani submit form modal gabungan (create & update).
     * 
     * CARA KERJA & ALUR:
     * - Memeriksa apakah payload berisi field `id`.
     * - Jika ada ID: Menyelesaikan request sebagai pembaruan (`update`).
     * - Jika tanpa ID: Menyelesaikan request sebagai penambahan baru (`store`).
     * 
     * @param  Request  $request
     * @return RedirectResponse|JsonResponse
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
     * Mengambil data satu fasilitas spesifik untuk kebutuhan formulir penyuntingan (modal edit).
     * 
     * CARA KERJA & ALUR:
     * - Mencari record fasilitas berdasarkan primary key atau melempar ModelNotFoundException (HTTP 404).
     * - Mengembalikan respons JSON agar modal Alpine.js dapat mengisi nilai field form secara instan.
     * 
     * @param  int|string  $id
     * @return JsonResponse|View
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
     * Memperbarui spesifikasi, kapasitas, atau foto fasilitas yang sudah terdaftar.
     * 
     * CARA KERJA & ALUR:
     * 1. Mengambil instance model via `findOrFail($id)`.
     * 2. Memvalidasi payload melalui `UpdateFacilityRequest` (dengan Rule::unique()->ignore).
     * 3. Jika ada upload foto baru: menghapus file foto fisik lama dari disk storage lalu menyimpan foto baru.
     * 4. Memperbarui atribut model via `$facility->update($validated)`.
     * 5. Menerapkan commit transaksi database.
     * 
     * @param  UpdateFacilityRequest  $request
     * @param  int|string  $id
     * @return RedirectResponse|JsonResponse
     */
    public function update(UpdateFacilityRequest $request, int|string $id): RedirectResponse|JsonResponse
    {
        $facility = Facility::findOrFail($id);
        $validated = $request->validated();

        try {
            DB::beginTransaction();

            // 1. Penanganan Penggantian Foto Cover & Penghapusan File Lama:
            // Mencegah penumpukan file sampah (garbage accumulation) di server dengan menghapus foto lama terlebih dahulu.
            if ($request->hasFile('cover_image')) {
                if ($facility->image_path && Storage::disk('public')->exists($facility->image_path)) {
                    Storage::disk('public')->delete($facility->image_path);
                }
                $validated['image_path'] = $request->file('cover_image')->store('facilities', 'public');
            }

            // 2. Normalisasi Array Perlengkapan (Equipment):
            if (isset($validated['equipment']) && is_string($validated['equipment'])) {
                $decoded = json_decode($validated['equipment'], true);
                $validated['equipment'] = is_array($decoded)
                    ? $decoded
                    : array_filter(array_map('trim', explode(',', $validated['equipment'])));
            }

            // 3. Simpan Pembaruan Atribut Fasilitas:
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
     * Mencabut fasilitas dari peredaran sistem menggunakan metode Soft Delete.
     * 
     * CARA KERJA & ALUR:
     * 1. Mengubah nilai kolom status menjadi 'nonaktif' terlebih dahulu.
     * 2. Memanggil method `$facility->delete()` yang akan mengisi kolom `deleted_at`.
     * 3. Ruangan tidak akan muncul lagi di daftar maupun katalog publik, tetapi seluruh data
     *    historis permohonan reservasi masa lalu tetap utuh di basis data.
     * 
     * @param  int|string  $id
     * @return RedirectResponse|JsonResponse
     */
    public function destroy(int|string $id): RedirectResponse|JsonResponse
    {
        $facility = Facility::findOrFail($id);

        try {
            // Ubah status operasional menjadi nonaktif terlebih dahulu:
            $facility->status = 'nonaktif';
            $facility->save();

            // Eksekusi SoftDeletes (isi kolom deleted_at tanpa menghapus baris fisik dari SQL):
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
     * Mengubah status operasional fasilitas secara cepat antara 'aktif' dan 'dalam perbaikan'.
     * 
     * CARA KERJA & ALUR:
     * 1. Mengambil model fasilitas via `findOrFail($id)`.
     * 2. Membalik status: jika 'aktif' diubah menjadi 'dalam perbaikan'; jika lainnya diubah menjadi 'aktif'.
     * 3. Menyimpan status baru. Jika ruangan dikunci ('dalam perbaikan'), modul reservasi otomatis
     *    menolak permohonan baru pada ruangan ini.
     * 
     * @param  int|string  $id
     * @return RedirectResponse|JsonResponse
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

