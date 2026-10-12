<?php
/**
 * NAMA FILE    : web.php
 * FUNGSI       : Pendaftaran rute web aplikasi CAVA (Campus Venue Access)
 * DESKRIPSI    : Menyediakan pemetaan endpoint URL terpusat untuk portal publik, operasional petugas, konsol admin, dan portal pengguna.
 * CARA KERJA   : Menerima HTTP Request, menerapkan middleware autentikasi dan Spatie RBAC (role:admin, role:petugas), serta mengarahkan ke Controller terkait dengan integrasi basis data MySQL.
 */

use App\Http\Controllers\DashboardPetugasController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminUserManagementController;
use App\Http\Controllers\DashboardUserController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\FacilityController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReportManagementController;
use App\Http\Controllers\ReservationManagementController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\PublicFacilityController;
use App\Http\Controllers\PublicInformationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Portal Publik (Terbuka untuk Sivitas & Tamu Umum - Modul 4 / PUB-01 & PUB-02)
|--------------------------------------------------------------------------
| Rute-rute di bawah ini bersifat publik dan TIDAK dilindungi oleh middleware 'auth'.
| Siapa pun, termasuk pengunjung anonim tanpa akun, dapat mengakses katalog ruang,
| memeriksa matriks ketersediaan slot waktu, dan melakukan pencarian fasilitas.
*/

/**
 * --------------------------------------------------------------------------
 * RUTE: GET / (Root URL / Landing Page Beranda)
 * --------------------------------------------------------------------------
 * Syntax: Route::get('/', [PublicFacilityController::class, 'home'])->name('home');
 * - Method HTTP : GET (pengambilan data representasi halaman).
 * - URI Path   : '/' (halaman muka aplikasi).
 * - Handler    : Controller PublicFacilityController, method 'home'.
 * - Name       : 'home' (digunakan pada fungsi helper route('home') di Blade/Controller).
 * - Cara Kerja : Menampilkan hero section, statistik ringkas fasilitas kampus,
 *                pilihan venue populer, dan direktori alur pengajuan peminjaman.
 */
Route::get('/', [PublicFacilityController::class, 'home'])->name('home');

/**
 * --------------------------------------------------------------------------
 * RUTE: GET /public/catalog (Katalog Fasilitas & Filter Multi-Dimensi - PUB-01)
 * --------------------------------------------------------------------------
 * Syntax: Route::get('/public/catalog', [PublicFacilityController::class, 'index'])->name('public.catalog');
 * - Method HTTP : GET.
 * - URI Path   : '/public/catalog'.
 * - Handler    : Controller PublicFacilityController, method 'index'.
 * - Name       : 'public.catalog'.
 * - Cara Kerja : Menerima parameter kueri filter (search, category, equipment, min_capacity),
 *                menyimpan riwayat filter terakhir ke cookie browser (1 minggu), dan
 *                mengembalikan view katalog daftar kartu fasilitas interaktif.
 */
Route::get('/public/catalog', [PublicFacilityController::class, 'index'])->name('public.catalog');

/**
 * --------------------------------------------------------------------------
 * RUTE: GET /public/availability (Kalender Matriks Ketersediaan Publik - PUB-01)
 * --------------------------------------------------------------------------
 * Syntax: Route::get('/public/availability', [PublicFacilityController::class, 'availability'])->name('public.availability');
 * - Method HTTP : GET.
 * - URI Path   : '/public/availability'.
 * - Handler    : Controller PublicFacilityController, method 'availability'.
 * - Name       : 'public.availability'.
 * - Cara Kerja : Merender tampilan antarmuka visual grid ketersediaan seluruh ruangan
 *                kampus pada tanggal tertentu (default: hari ini). Menghubungkan
 *                datepicker frontend dengan pemanggilan asynchronous ke endpoint API.
 */
Route::get('/public/availability', [PublicFacilityController::class, 'availability'])->name('public.availability');

/**
 * --------------------------------------------------------------------------
 * API ENDPOINT: GET /api/availability/{date} (JSON Matriks Slot Seluruh Fasilitas)
 * --------------------------------------------------------------------------
 * Syntax: Route::get('/api/availability/{date}', [PublicFacilityController::class, 'getMatrixAvailability'])->name('api.availability.matrix');
 * - Method HTTP : GET.
 * - URI Path   : '/api/availability/{date}' di mana parameter {date} berformat YYYY-MM-DD.
 * - Handler    : Controller PublicFacilityController, method 'getMatrixAvailability'.
 * - Name       : 'api.availability.matrix'.
 * - Cara Kerja : Menghasilkan payload JSON yang memetakan slot 30 menit (07:00 - 20:00 WIB)
 *                untuk seluruh ruangan aktif. Slot yang terisi oleh reservasi berstatus
 *                'approved' ditandai sebagai 'booked' dengan privasi identitas pemohon disembunyikan.
 */
Route::get('/api/availability/{date}', [PublicFacilityController::class, 'getMatrixAvailability'])->name('api.availability.matrix');
// RUTE ALIAS: Mendukung pemanggilan fleksibel via endpoint /api/availability/matrix/{date}
Route::get('/api/availability/matrix/{date}', [PublicFacilityController::class, 'getMatrixAvailability'])->name('api.availability.matrix.alias');

/**
 * --------------------------------------------------------------------------
 * API ENDPOINT: GET /api/availability/{id}/{date} (JSON Ketersediaan Tunggal)
 * --------------------------------------------------------------------------
 * Syntax: Route::get('/api/availability/{id}/{date}', [PublicFacilityController::class, 'showAvailability'])->name('api.availability.single');
 * - Method HTTP : GET.
 * - URI Path   : '/api/availability/{id}/{date}' ({id}: ID fasilitas, {date}: format YYYY-MM-DD).
 * - Handler    : Controller PublicFacilityController, method 'showAvailability'.
 * - Name       : 'api.availability.single'.
 * - Cara Kerja : Mengembalikan rincian jam mulai & selesai permohonan yang disetujui
 *                untuk 1 fasilitas tertentu guna validasi slot pada formulir pemesanan.
 */
Route::get('/api/availability/{id}/{date}', [PublicFacilityController::class, 'showAvailability'])->name('api.availability.single');

/**
 * --------------------------------------------------------------------------
 * API ENDPOINT: GET /api/facilities/search (Autocomplete & Pencarian Instan - PUB-02)
 * --------------------------------------------------------------------------
 * Syntax: Route::get('/api/facilities/search', [PublicFacilityController::class, 'searchSuggestions'])->name('api.facilities.search');
 * - Method HTTP : GET.
 * - URI Path   : '/api/facilities/search?q=keyword'.
 * - Handler    : Controller PublicFacilityController, method 'searchSuggestions'.
 * - Name       : 'api.facilities.search'.
 * - Cara Kerja : Menangani AJAX request dari search bar untuk menampilkan dropdown
 *                rekomendasi ruangan (nama, kode, gedung, kapasitas) secara instan.
 */
Route::get('/api/facilities/search', [PublicFacilityController::class, 'searchSuggestions'])->name('api.facilities.search');

/**
 * --------------------------------------------------------------------------
 * RUTE: GET /informasi/{section?} (Pusat Informasi, Kebijakan, S&K, dan Bantuan)
 * --------------------------------------------------------------------------
 * Syntax: Route::get('/informasi/{section?}', [PublicInformationController::class, 'index'])->name('public.information');
 * - Parameter opsional: {section?} dapat bernilai 'kebijakan', 'syarat-ketentuan', atau 'bantuan'.
 */
Route::get('/informasi/{section?}', [PublicInformationController::class, 'index'])->name('public.information');

/**
 * --------------------------------------------------------------------------
 * RUTE ALIAS / SHORTCUT: Redireksi Cepat ke Tab Pusat Informasi
 * --------------------------------------------------------------------------
 * Syntax: Route::get('/...', fn() => redirect()->route(...));
 * Menggunakan Arrow Function (PHP 7.4+) untuk mengembalikan respon redirect HTTP 302
 * ke rute 'public.information' dengan parameter tab yang bersangkutan.
 */
Route::get('/kebijakan', fn() => redirect()->route('public.information', ['section' => 'kebijakan']))->name('public.kebijakan');
Route::get('/syarat-ketentuan', fn() => redirect()->route('public.information', ['section' => 'syarat-ketentuan']))->name('public.syarat-ketentuan');
Route::get('/bantuan', fn() => redirect()->route('public.information', ['section' => 'bantuan']))->name('public.bantuan');

/*
|--------------------------------------------------------------------------
| Konsol Biro Sarpras (Super Admin)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->group(function () {

/**
 * ------------------------------------------------------------------------------------------
 * MODUL KONSOL ADMIN: DASBOR EKSEKUTIF & STATISTIK UTAMA (ADM-04 / US-17)
 * ------------------------------------------------------------------------------------------
 * Rute ini menjadi pintu gerbang utama bagi Super Administrator untuk meninjau metrik makro
 * operasional kampus: utilisasi ruangan, tingkat okupansi real-time, rasio penanganan tiket
 * kerusakan (SLA resolution), antrean verifikasi akun pending, dan log transaksi terkini.
 * Dilindungi oleh middleware: 'auth' (wajib terotentikasi) dan 'role:admin' (hanya Super Admin).
 */

/**
 * RUTE: GET /admin/dashboard (Dasbor Eksekutif Super Administrator - ADM-04 / US-17)
 * ----------------------------------------------------------------------------------
 * Syntax: Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
 * - Method HTTP : GET (pengambilan dan visualisasi representasi metrik).
 * - URI Path   : '/admin/dashboard'.
 * - Handler    : AdminDashboardController@index.
 * - Route Name : 'admin.dashboard' (dipanggil via helper route('admin.dashboard')).
 * - Cara Kerja : Menghubungkan permintaan browser dengan AdminDashboardController untuk mengeksekusi
 *                agregasi data real-time, lalu merender antarmuka eksekutif Bento UI (kartu KPI,
 *                grafik okupansi mingguan, dan tabel riwayat transaksi terbaru).
 */
Route::get('/admin/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');

/**
 * ------------------------------------------------------------------------------------------
 * MODUL KONSOL ADMIN: VERIFIKASI AKUN PENDING & MANAJEMEN PENGGUNA INTERNAL (ADM-01 & ADM-02)
 * ------------------------------------------------------------------------------------------
 * Rute-rute di bawah ini mengelola seluruh siklus hidup akun pengguna dalam sistem CAVA,
 * mencakup verifikasi pendaftaran mandiri sivitas (status 'pending' -> 'active' / 'rejected'),
 * pembuatan akun internal langsung (Petugas Sarpras & Pengguna Sivitas), pengalihan status,
 * serta mekanisme penghapusan lunak (soft delete) dan pemulihan (restore).
 * Dilindungi oleh middleware: 'auth' (wajib login) dan 'role:admin' (hanya Super Admin).
 */

/**
 * RUTE: GET /admin/user-management & GET /admin/users (Indeks Manajemen Pengguna & Verifikasi Pending)
 * -------------------------------------------------------------------------------------------------
 * Syntax: Route::get('/admin/user-management', [AdminUserManagementController::class, 'index'])->name('admin.user-management');
 * - Method HTTP : GET (pengambilan dan perenderan halaman).
 * - URI Path   : '/admin/user-management' (dan alias '/admin/users').
 * - Handler    : AdminUserManagementController@index.
 * - Route Name : 'admin.user-management' & 'admin.users.index'.
 * - Cara Kerja : Menampilkan dasbor pengguna dengan pembagian 4 tab utama:
 *                1. Antrean Verifikasi Pending (sivitas yang baru mendaftar mandiri).
 *                2. Sivitas Akademika Aktif (Mahasiswa, Dosen, Staf yang telah diverifikasi).
 *                3. Petugas Sarpras Internal (staf operasional pemeliharaan & validasi).
 *                4. Akun Non-aktif / Terhapus (arsip soft delete).
 */
Route::get('/admin/user-management', [AdminUserManagementController::class, 'index'])->name('admin.user-management');
Route::get('/admin/users', [AdminUserManagementController::class, 'index'])->name('admin.users.index');

/**
 * RUTE: POST/PATCH /admin/users/{id}/verify (Persetujuan & Verifikasi Akun Sivitas - ADM-01)
 * ------------------------------------------------------------------------------------------
 * Syntax: Route::match(['post', 'patch'], '/admin/users/{id}/verify', [AdminUserManagementController::class, 'verifyUser'])->name('admin.users.verify');
 * - Method HTTP : POST atau PATCH (mendukung form submit standar Blade dan request RESTful AJAX).
 * - Parameter  : {id} ID pengguna yang statusnya masih 'pending'.
 * - Handler    : AdminUserManagementController@verifyUser.
 * - Route Name : 'admin.users.verify' & alias 'admin.users.approve'.
 * - Cara Kerja : Mengubah nilai kolom 'status' pengguna dari 'pending' menjadi 'active',
 *                membersihkan alasan penolakan (jika ada sebelumnya), dan mencatat timestamp
 *                verifikasi sehingga akun tersebut dapat langsung login ke portal CAVA.
 */
Route::match(['post', 'patch'], '/admin/users/{id}/verify', [AdminUserManagementController::class, 'verifyUser'])->name('admin.users.verify');
Route::match(['post', 'patch'], '/admin/users/{id}/approve', [AdminUserManagementController::class, 'verifyUser'])->name('admin.users.approve');

/**
 * RUTE: POST/PATCH /admin/users/{id}/reject (Penolakan Akun Pending Sivitas - ADM-01)
 * ----------------------------------------------------------------------------------
 * Syntax: Route::match(['post', 'patch'], '/admin/users/{id}/reject', [AdminUserManagementController::class, 'rejectUser'])->name('admin.users.reject');
 * - Method HTTP : POST atau PATCH.
 * - Parameter  : {id} ID pengguna yang pendaftarannya ditolak.
 * - Payload    : Divalidasi oleh RejectUserRequest (wajib menyertakan 'reason' dan opsional 'notes').
 * - Handler    : AdminUserManagementController@rejectUser.
 * - Route Name : 'admin.users.reject'.
 * - Cara Kerja : Mengubah kolom 'status' menjadi 'rejected' dan menyimpan deskripsi penolakan
 *                ke kolom 'rejection_reason'. Saat pengguna mencoba login, sistem menampilkan
 *                alasan penolakan tersebut secara transparan.
 */
Route::match(['post', 'patch'], '/admin/users/{id}/reject', [AdminUserManagementController::class, 'rejectUser'])->name('admin.users.reject');

/**
 * RUTE: POST /admin/users/petugas (Pembuatan Akun Internal Petugas Sarpras - ADM-02)
 * ----------------------------------------------------------------------------------
 * Syntax: Route::post('/admin/users/petugas', [AdminUserManagementController::class, 'storePetugas'])->name('admin.users.create-petugas');
 * - Method HTTP : POST.
 * - Payload    : Divalidasi oleh StoreInternalUserRequest (nama, email unik, password opsional, NIP, zona penugasan).
 * - Handler    : AdminUserManagementController@storePetugas.
 * - Route Name : 'admin.users.create-petugas'.
 * - Cara Kerja : Membuat akun baru dengan status langsung 'active', menetapkan role 'petugas' via Spatie RBAC,
 *                dan menyimpan data spesifik penugasan sarpras.
 */
Route::post('/admin/users/petugas', [AdminUserManagementController::class, 'storePetugas'])->name('admin.users.create-petugas');

/**
 * RUTE: POST /admin/users/pengguna (Pembuatan Akun Sivitas Langsung oleh Admin - ADM-02)
 * ------------------------------------------------------------------------------------
 * Syntax: Route::post('/admin/users/pengguna', [AdminUserManagementController::class, 'storePengguna'])->name('admin.users.create-user');
 * - Method HTTP : POST.
 * - Payload    : Divalidasi oleh StoreInternalUserRequest (nama, email unik, NIM/NIP, fakultas/departemen).
 * - Handler    : AdminUserManagementController@storePengguna.
 * - Route Name : 'admin.users.create-user'.
 * - Cara Kerja : Membuat akun sivitas langsung aktif tanpa melalui antrean verifikasi mandiri.
 */
Route::post('/admin/users/pengguna', [AdminUserManagementController::class, 'storePengguna'])->name('admin.users.create-user');

/**
 * RUTE: POST /admin/users (Endpoint Alias Penambahan Pengguna Universal)
 * ----------------------------------------------------------------------
 * Syntax: Route::post('/admin/users', [AdminUserManagementController::class, 'storeUser'])->name('admin.users.store');
 * - Handler: Mengarahkan secara cerdas ke storePetugas() atau storePengguna() berdasarkan nilai atribut 'role'.
 */
Route::post('/admin/users', [AdminUserManagementController::class, 'storeUser'])->name('admin.users.store');

/**
 * RUTE: POST /admin/users/{id}/toggle-status (Pengubahan Status Akun Aktif <-> Nonaktif)
 * --------------------------------------------------------------------------------------
 * Syntax: Route::post('/admin/users/{id}/toggle-status', [AdminUserManagementController::class, 'toggleStatus'])->name('admin.users.toggle-status');
 * - Parameter  : {id} ID akun pengguna yang akan diubah statusnya.
 * - Handler    : AdminUserManagementController@toggleStatus.
 * - Route Name : 'admin.users.toggle-status'.
 * - Cara Kerja : Membalik status pengguna antara 'active' dan 'inactive'. Akun berstatus 'inactive'
 *                akan diblokir saat mencoba login ke sistem.
 */
Route::post('/admin/users/{id}/toggle-status', [AdminUserManagementController::class, 'toggleStatus'])->name('admin.users.toggle-status');

/**
 * RUTE: DELETE /admin/users/{id} (Penghapusan Lunak Akun Pengguna / Soft Delete)
 * ------------------------------------------------------------------------------
 * Syntax: Route::delete('/admin/users/{id}', [AdminUserManagementController::class, 'destroy'])->name('admin.users.destroy');
 * - Method HTTP : DELETE (mengikuti konvensi RESTful untuk operasi penghapusan).
 * - Parameter  : {id} ID pengguna yang dihapus.
 * - Handler    : AdminUserManagementController@destroy.
 * - Route Name : 'admin.users.destroy'.
 * - Cara Kerja : Mengisi kolom 'deleted_at' (Eloquent SoftDeletes) atau mengatur status ke 'inactive'
 *                sehingga akun tidak dapat login dan dipindahkan ke tab arsip tanpa menghapus riwayat audit transaksi.
 */
Route::delete('/admin/users/{id}', [AdminUserManagementController::class, 'destroy'])->name('admin.users.destroy');

/**
 * RUTE: POST /admin/users/{id}/restore (Pemulihan Akun Terhapus / Restorasi)
 * --------------------------------------------------------------------------
 * Syntax: Route::post('/admin/users/{id}/restore', [AdminUserManagementController::class, 'restore'])->name('admin.users.restore');
 * - Parameter  : {id} ID pengguna yang sebelumnya di-soft delete atau dinonaktifkan.
 * - Handler    : AdminUserManagementController@restore.
 * - Route Name : 'admin.users.restore'.
 * - Cara Kerja : Mengembalikan 'deleted_at' menjadi NULL dan status kembali 'active' sehingga
 *                pengguna dapat kembali mengakses sistem.
 */
Route::post('/admin/users/{id}/restore', [AdminUserManagementController::class, 'restore'])->name('admin.users.restore');


// ------------------------------------------------------------------------------------------
// MODUL MASTER FASILITAS & RUANGAN KAMPUS (ADM-03 / US-16)
// ------------------------------------------------------------------------------------------
// Memfasilitasi Administrator untuk mengelola seluruh siklus hidup venue kampus
// (Katalogisasi, Unggah Foto, Pengaturan Kapasitas & Alat, serta Toggle Status Operasional).

// ROUTE: Menerima GET request ke '/admin/facility-master' atau '/admin/facilities'
// FUNGSI: Menampilkan antarmuka Bento UI manajemen fasilitas kampus dengan filter pencarian & kategori
Route::get('/admin/facility-master', [App\Http\Controllers\FacilityController::class, 'index'])->name('admin.facility-master');
Route::get('/admin/facilities', [App\Http\Controllers\FacilityController::class, 'index'])->name('facilities.index');

// ROUTE: Menerima POST request ke '/admin/facilities/save'
// FUNGSI: Endpoint alias serbaguna untuk menyimpan data baru atau memperbarui data via modal pop-up
Route::post('/admin/facilities/save', [App\Http\Controllers\FacilityController::class, 'saveAlias'])->name('facilities.save');

// ROUTE: Menerima POST request ke '/admin/facilities'
// FUNGSI: Menyimpan entitas ruangan baru ke basis data dengan validasi StoreFacilityRequest & upload cover image
Route::post('/admin/facilities', [App\Http\Controllers\FacilityController::class, 'store'])->name('facilities.store');

// ROUTE: Menerima PUT request ke '/admin/facilities/{facility}'
// FUNGSI: Memperbarui spesifikasi ruangan yang sudah ada menggunakan Implicit Route Model Binding {facility}
Route::put('/admin/facilities/{facility}', [App\Http\Controllers\FacilityController::class, 'update'])->name('facilities.update');

// ROUTE: Menerima DELETE request ke '/admin/facilities/{facility}'
// FUNGSI: Melakukan penghapusan lunak (Soft Delete) pada ruangan sehingga tidak muncul di katalog tapi tetap tersimpan di arsip
Route::delete('/admin/facilities/{facility}', [App\Http\Controllers\FacilityController::class, 'destroy'])->name('facilities.destroy');

// ROUTE: Menerima POST request ke '/admin/facilities/{facility}/toggle'
// FUNGSI: Mengubah status operasional ruangan ('aktif' <-> 'dalam perbaikan' <-> 'nonaktif') secara cepat
Route::post('/admin/facilities/{facility}/toggle', [App\Http\Controllers\FacilityController::class, 'toggleStatus'])->name('admin.facilities.toggle');


/**
 * ------------------------------------------------------------------------------------------
 * MODUL KONSOL ADMIN: REKAPITULASI ANALITIK & EKSPOR RESMI (PDF & EXCEL - ADM-04 / UR17)
 * ------------------------------------------------------------------------------------------
 * Rute-rute di bawah ini memfasilitasi Super Administrator dalam menghasilkan dokumen laporan
 * statuter resmi universitas terkait riwayat peminjaman ruangan dan audit kerusakan sarana prasarana.
 * Menyediakan pengunduhan dokumen biner PDF Landscape A4 (berkop surat, bertanda tangan digital,
 * dan stempel biro) serta berkas spreadsheet CSV/Excel streaming memori rendah.
 * Dilindungi oleh middleware: 'auth' (wajib login) dan 'role:admin' (hanya Super Admin).
 */

/**
 * RUTE: GET /admin/export-report (Panel Filter & Pusat Unduh Rekapitulasi)
 * ------------------------------------------------------------------------------------------
 * Syntax: Route::get('/admin/export-report', [ExportController::class, 'index'])->name('admin.export-report');
 * - Method HTTP : GET (pengambilan formulir filter laporan).
 * - URI Path   : '/admin/export-report'.
 * - Handler    : ExportController@index.
 * - Route Name : 'admin.export-report'.
 * - Cara Kerja : Menampilkan antarmuka Bento UI untuk memilih tipe laporan (Reservasi vs Kerusakan),
 *                menentukan rentang tanggal (start_date & end_date), memilih format unduhan (PDF vs Excel),
 *                dan menampilkan ringkasan metrik statistik sebelum dokumen diunduh.
 */
Route::get('/admin/export-report', [ExportController::class, 'index'])->name('admin.export-report');

/**
 * RUTE: GET /admin/export/reservations/pdf (Dokumen Cetak PDF Resmi Audit Reservasi)
 * ------------------------------------------------------------------------------------------
 * Syntax: Route::get('/admin/export/reservations/pdf', [ExportController::class, 'exportReservationsPdf'])->name('admin.export.reservations.pdf');
 * - Method HTTP : GET (menerima parameter kueri start_date, end_date, dan status).
 * - Handler    : ExportController@exportReservationsPdf.
 * - Route Name : 'admin.export.reservations.pdf'.
 * - Cara Kerja : Menarik data permohonan tervalidasi, mengompilasi view Blade reservations-pdf,
 *                merender dokumen PDF format Landscape A4 via pustaka DomPDF, dan mengalirkan file biner
 *                ke browser dengan nama dinamis berstempel tanggal.
 */
Route::get('/admin/export/reservations/pdf', [ExportController::class, 'exportReservationsPdf'])->name('admin.export.reservations.pdf');

/**
 * RUTE: GET /admin/export/reservations/excel (Spreadsheet Audit Transaksi Reservasi Ruang)
 * ------------------------------------------------------------------------------------------
 * Syntax: Route::get('/admin/export/reservations/excel', [ExportController::class, 'exportReservationsExcel'])->name('admin.export.reservations.excel');
 * - Method HTTP : GET (menerima parameter filter temporal).
 * - Handler    : ExportController@exportReservationsExcel.
 * - Route Name : 'admin.export.reservations.excel'.
 * - Cara Kerja : Menginstansiasi ReservationsExport class dan mengalirkan berkas CSV bertaraf Excel
 *                (disertai UTF-8 BOM dan chunking per 500 baris) langsung ke browser tanpa lonjakan memori RAM.
 */
Route::get('/admin/export/reservations/excel', [ExportController::class, 'exportReservationsExcel'])->name('admin.export.reservations.excel');

/**
 * RUTE: GET /admin/export/damage-reports/pdf (Dokumen Cetak PDF Resmi Tiket Insiden Sarpras)
 * ------------------------------------------------------------------------------------------
 * Syntax: Route::get('/admin/export/damage-reports/pdf', [ExportController::class, 'exportDamageReportsPdf'])->name('admin.export.damage-reports.pdf');
 * - Method HTTP : GET.
 * - Handler    : ExportController@exportDamageReportsPdf.
 * - Route Name : 'admin.export.damage-reports.pdf'.
 * - Cara Kerja : Mengagregasikan tiket kerusakan sarana kampus, menghitung SLA resolusi perbaikan,
 *                dan menghasilkan dokumen PDF resmi bertanda tangan digital UPT Sarpras via DomPDF.
 */
Route::get('/admin/export/damage-reports/pdf', [ExportController::class, 'exportDamageReportsPdf'])->name('admin.export.damage-reports.pdf');

/**
 * RUTE: GET /admin/export/damage-reports/excel (Spreadsheet Audit Tiket Kerusakan Fasilitas)
 * ------------------------------------------------------------------------------------------
 * Syntax: Route::get('/admin/export/damage-reports/excel', [ExportController::class, 'exportDamageReportsExcel'])->name('admin.export.damage-reports.excel');
 * - Method HTTP : GET.
 * - Handler    : ExportController@exportDamageReportsExcel.
 * - Route Name : 'admin.export.damage-reports.excel'.
 * - Cara Kerja : Mengalirkan rekapitulasi tiket malfungsi aset fisik kampus via DamageReportsExport
 *                dengan efisiensi pemrosesan berbasis streaming chunking.
 */
Route::get('/admin/export/damage-reports/excel', [ExportController::class, 'exportDamageReportsExcel'])->name('admin.export.damage-reports.excel');

/**
 * RUTE ALIAS: Kompatibilitas Tautan UI Ekspor Laporan
 * ------------------------------------------------------------------------------------------
 * Menjaga kompatibilitas navigasi pada antarmuka admin lama atau mock-up template.
 */
Route::get('/admin/reports/export-excel', [ExportController::class, 'exportReservationsExcel'])->name('admin.reports.export-excel');
Route::get('/admin/reports/export-pdf', [ExportController::class, 'exportReservationsPdf'])->name('admin.reports.export-pdf');
});

/**
 * ------------------------------------------------------------------------------------------
 * MODUL 13: OPERASIONAL PETUGAS SARPRAS: DASBOR MONITORING ANTREAN CERDAS (PTG-01 / US-8)
 * ------------------------------------------------------------------------------------------
 * Rute-rute di dalam grup ini dikhususkan bagi staf operasional sarana dan prasarana kampus
 * (Role: 'petugas') untuk memonitor beban kerja secara real-time, mengaudit antrean reservasi,
 * meninjau matriks jadwal 30 menit, memverifikasi permohonan, dan menindaklanjuti tiket kerusakan.
 * Dilindungi oleh middleware: 'auth' (wajib login) dan 'role:petugas' (hak akses petugas sarpras).
 */
Route::middleware(['auth', 'role:petugas'])->group(function () {
    /**
     * RUTE: GET /petugas/dashboard (Dasbor Operasional Pemantauan Petugas Sarpras - PTG-01 / US-8)
     * --------------------------------------------------------------------------------------------
     * Syntax: Route::get('/petugas/dashboard', [DashboardPetugasController::class, 'index'])->name('petugas.dashboard');
     * - Method HTTP : GET (pengambilan halaman representasi dasbor pemantauan operasional).
     * - URI Path   : '/petugas/dashboard'.
     * - Handler    : DashboardPetugasController@index.
     * - Route Name : 'petugas.dashboard' (dipanggil via helper route('petugas.dashboard')).
     * - Cara Kerja : Menghubungkan browser dengan controller untuk menghitung metrik kuantitas antrean
     *                (tiket reservasi pending, tiket laporan aktif baru/diproses, fasilitas terkunci maintenance),
     *                menarik 5 data antrean teratas secara FIFO dengan Eager Loading, dan merender view
     *                Bento UI modern 'petugas.dashboard'.
     */
    Route::get('/petugas/dashboard', [DashboardPetugasController::class, 'index'])->name('petugas.dashboard');

    // ROUTE: Menerima GET request ke '/petugas/reservation-management'
    // FUNGSI: Menampilkan lembar kerja manajemen dan seluruh antrean persetujuan reservasi (PTG-02 / US-9)
    Route::get('/petugas/reservation-management', [ReservationManagementController::class, 'index'])->name('petugas.reservation-management');
    Route::get('/petugas/reservations', [ReservationManagementController::class, 'index'])->name('petugas.reservations.index');

    /**
     * ------------------------------------------------------------------------------------------
     * MODUL 14: MATRIKS JADWAL 30 MENIT OPERASIONAL & DETAIL PEMOHON (SCHEDULE MATRIX)
     * ------------------------------------------------------------------------------------------
     * Rute-rute di bawah ini memfasilitasi visualisasi tabular matriks ketersediaan seluruh ruangan
     * kampus per interval 30 menit (pukul 07:00 hingga 20:00 WIB) untuk staf UPT Sarpras:
     * - Menampilkan antarmuka Bento UI matriks jadwal harian terintegrasi.
     * - Menyediakan endpoint asinkron JSON untuk data slot terisi, identitas lengkap sivitas pemohon,
     *   surat izin, status persetujuan, dan log verifikator.
     */

    /**
     * RUTE: GET /petugas/schedule-matrix (Antarmuka Matriks Jadwal 30 Menit Petugas)
     * -----------------------------------------------------------------------------
     * Syntax: Route::get('/petugas/schedule-matrix', [PetugasScheduleMatrixController::class, 'index'])->name('petugas.schedule-matrix');
     * - Method HTTP : GET (pengambilan halaman web matriks operasional).
     * - URI Path   : '/petugas/schedule-matrix'.
     * - Handler    : PetugasScheduleMatrixController@index.
     * - Route Name : 'petugas.schedule-matrix'.
     * - Cara Kerja : Menarik daftar gedung kampus aktif, fasilitas terkait, preferensi filter cookie,
     *                serta ringkasan statistik harian (disetujui, pending, maintenance) lalu merender
     *                view Bento 'petugas.schedule-matrix'.
     */
    Route::get('/petugas/schedule-matrix', [App\Http\Controllers\PetugasScheduleMatrixController::class, 'index'])->name('petugas.schedule-matrix');

    /**
     * RUTE: GET /api/petugas/matrix/{date} (Endpoint JSON Detail Slot Matriks Asinkron)
     * ---------------------------------------------------------------------------------
     * Syntax: Route::get('/api/petugas/matrix/{date}', [PetugasScheduleMatrixController::class, 'getMatrixData'])->name('api.petugas.matrix');
     * - Method HTTP : GET (pengambilan payload data berekstensi JSON melalui AJAX/Fetch API).
     * - URI Path   : '/api/petugas/matrix/{date}' ({date} berformat ISO 'YYYY-MM-DD').
     * - Handler    : PetugasScheduleMatrixController@getMatrixData.
     * - Route Name : 'api.petugas.matrix'.
     * - Cara Kerja : Menarik seluruh reservasi ('approved' & 'pending') pada tanggal yang dipilih,
     *                memetakan setiap permohonan ke dalam slot waktu 30 menit (`matrix[facility_id][H:i]`),
     *                menyematkan detail pemohon (nama, NIM, prodi, kontak, surat izin, reviewer),
     *                dan mengembalikan respons JsonResponse untuk dirender reaktif oleh Alpine.js.
     */
    Route::get('/api/petugas/matrix/{date}', [App\Http\Controllers\PetugasScheduleMatrixController::class, 'getMatrixData'])->name('api.petugas.matrix');

    // ROUTE: Menerima PATCH request ke '/petugas/reservations/{id}/approve'
    // FUNGSI: Menyetujui permohonan reservasi dengan validasi anti-bentrok jadwal (PTG-02 / US-9)
    Route::patch('/petugas/reservations/{id}/approve', [ReservationManagementController::class, 'approve'])->name('petugas.reservations.approve');

    // ROUTE: Menerima PATCH request ke '/petugas/reservations/{id}/reject'
    // FUNGSI: Menolak permohonan reservasi dengan alasan penolakan resmi (PTG-02 / US-9)
    Route::patch('/petugas/reservations/{id}/reject', [ReservationManagementController::class, 'reject'])->name('petugas.reservations.reject');

    // ROUTE: Menerima DELETE request ke '/petugas/reservations/{id}/force-cancel'
    // FUNGSI: Pembatalan darurat sepihak (override privilege) oleh petugas untuk reservasi yang telah disetujui (PTG-03 / US-10)
    Route::delete('/petugas/reservations/{id}/force-cancel', [ReservationManagementController::class, 'forceCancel'])->name('petugas.reservations.force-cancel');

    // ROUTE: Menerima GET request ke '/petugas/report-management'
    // FUNGSI: Menampilkan lembar kerja manajemen tiket kerusakan fasilitas sarpras (PTG-04 / US-11)
    Route::get('/petugas/report-management', [ReportManagementController::class, 'index'])->name('petugas.report-management');
    Route::get('/petugas/reports', [ReportManagementController::class, 'index'])->name('petugas.reports.index');

    // ROUTE: Menerima PATCH request ke '/petugas/reports/{id}'
    // FUNGSI: Memperbarui status penanganan tiket keluhan kerusakan dan mencatat resolusi teknisi (PTG-04 / US-11)
    Route::patch('/petugas/reports/{id}', [ReportManagementController::class, 'updateStatus'])->name('petugas.reports.update');

    // ROUTE: Menerima PATCH request ke '/petugas/facilities/{id}/toggle-maintenance'
    // FUNGSI: Mengunci/membuka kunci fasilitas ke status pemeliharaan / Mode Perbaikan (PTG-05 / US-12)
    Route::patch('/petugas/facilities/{id}/toggle-maintenance', [ReportManagementController::class, 'toggleMaintenance'])->name('petugas.facilities.toggle-maintenance');
});

/**
 * ------------------------------------------------------------------------------------------
 * MODUL PORTAL PENGGUNA (SIVITAS AKADEMIKA / MAHASISWA & DOSEN - MODUL 9)
 * ------------------------------------------------------------------------------------------
 * Kelompok rute di bawah ini melayani portal swalayan (Self-Service Portal) untuk sivitas kampus:
 * - Pengajuan dan pelacakan reservasi peminjaman ruangan mandiri (USR-01).
 * - Pembatalan mandiri sebelum batas waktu H-1 (USR-02).
 * - Pelaporan tiket kerusakan sarana prasarana beserta bukti foto (USR-03).
 * Dilindungi oleh middleware: 'auth' (wajib login) dan 'role:pengguna' (hak akses sivitas aktif).
 */
Route::middleware(['auth', 'role:pengguna'])->group(function () {
    /**
     * RUTE: GET /user/dashboard (Dasbor Personal Sivitas & Ringkasan Tiket Aktif - Modul 9)
     * --------------------------------------------------------------------------------------
     * Syntax: Route::get('/user/dashboard', [DashboardUserController::class, 'index'])->name('user.dashboard');
     * - Method HTTP : GET (pengambilan halaman representasi dasbor pengguna).
     * - URI Path   : '/user/dashboard'.
     * - Handler    : DashboardUserController@index.
     * - Route Name : 'user.dashboard'.
     * - Cara Kerja : Mengidentifikasi akun pengguna yang sedang login (`Auth::id()`), mengambil
     *                jadwal terdekat yang disetujui (`upcomingReservation`), 3 riwayat pengajuan terkini,
     *                dan tiket pengaduan aktif, lalu merender view Bento UI `user.dashboard`.
     */
    Route::get('/user/dashboard', [DashboardUserController::class, 'index'])->name('user.dashboard');

    /**
     * ------------------------------------------------------------------------------------------
     * MODUL 10: FORMULIR PENGAJUAN RESERVASI RUANGAN MANDIRI (USR-01)
     * ------------------------------------------------------------------------------------------
     * Rute-rute di bawah ini mengelola antarmuka pengajuan sewa ruangan mandiri bagi sivitas:
     * - Menampilkan antarmuka pemilih fasilitas bertingkat 3 level (Gedung -> Lantai -> Ruangan).
     * - Memvalidasi ketersediaan jadwal 30 menitan (07:00-20:00 WIB) dan memproses penyimpanan
     *   permohonan secara atomik dengan algoritma anti-bentrok (anti double-booking).
     */

    /**
     * RUTE: GET /user/reservation-form (Formulir Pengajuan Reservasi Ruangan Mandiri - USR-01)
     * ----------------------------------------------------------------------------------------
     * Syntax: Route::get('/user/reservation-form', [ReservationController::class, 'create'])->name('user.reservation-form');
     * - Method HTTP : GET (pengambilan halaman representasi formulir reservasi).
     * - URI Path   : '/user/reservation-form'.
     * - Handler    : ReservationController@create.
     * - Route Name : 'user.reservation-form'.
     * - Cara Kerja : Menarik seluruh master data fasilitas berstatus aktif (`status = 'aktif'`),
     *                menyiapkan parameter query `facility_id` jika pengguna datang dari katalog,
     *                dan merender view Apple-esque Bento `user.reservation-form`.
     */
    Route::get('/user/reservation-form', [ReservationController::class, 'create'])->name('user.reservation-form');

    /**
     * RUTE: POST /user/reservations (Simpan Transaksi Pengajuan Reservasi Baru - USR-01)
     * ----------------------------------------------------------------------------------
     * Syntax: Route::post('/user/reservations', [ReservationController::class, 'store'])->name('user.reservations.store');
     * - Method HTTP : POST (pengiriman payload data baru secara aman via HTTP Body).
     * - URI Path   : '/user/reservations'.
     * - Handler    : ReservationController@store.
     * - FormRequest: StoreReservationRequest (validasi jam operasional 07-20 WIB, kelipatan 30m, dll).
     * - Route Name : 'user.reservations.store'.
     * - Cara Kerja : Memeriksa kelayakan fasilitas (wajib aktif), menghitung total slot 30 menit,
     *                mengecek overlap bentrok jadwal dengan reservasi yang telah disetujui (approved),
     *                membangkitkan kode tiket unik `TKT-YYYYMMDD-XXXX`, dan menyimpan data ke dalam
     *                blok transaksi database `DB::transaction()` berstatus 'pending'.
     */
    Route::post('/user/reservations', [ReservationController::class, 'store'])->name('user.reservations.store');

    /**
     * ------------------------------------------------------------------------------------------
     * MODUL 11: RIWAYAT RESERVASI & PEMBATALAN MANDIRI H-1 (USR-02 & USR-03)
     * ------------------------------------------------------------------------------------------
     * Rute-rute di bawah ini melayani pemantauan tiket permohonan reservasi milik sivitas
     * serta fasilitas pembatalan mandiri sebelum batas mutlak H-1 (24 jam sebelum kegiatan):
     */

    /**
     * RUTE: GET /user/reservation-history (Riwayat Permohonan Reservasi Pengguna - USR-02)
     * ------------------------------------------------------------------------------------
     * Syntax: Route::get('/user/reservation-history', [ReservationController::class, 'history'])->name('user.reservation-history');
     * - Method HTTP : GET (pengambilan halaman riwayat reservasi personal).
     * - URI Path   : '/user/reservation-history'.
     * - Handler    : ReservationController@history.
     * - Route Name : 'user.reservation-history'.
     * - Cara Kerja : Menarik seluruh riwayat reservasi yang diajukan oleh pengguna login (`Auth::id()`)
     *                menggunakan teknik Eager Loading (`with(['facility', 'reviewer'])`) untuk mencegah
     *                masalah performa N+1, mendukung tab filter status, pencarian multi-atribut,
     *                serta paginasi data 10 baris per halaman.
     */
    Route::get('/user/reservation-history', [ReservationController::class, 'history'])->name('user.reservation-history');

    /**
     * RUTE: DELETE /user/reservations/{id}/cancel (Pembatalan Reservasi Mandiri H-1 - USR-03)
     * --------------------------------------------------------------------------------------
     * Syntax: Route::delete('/user/reservations/{id}/cancel', [ReservationController::class, 'cancel'])->name('user.reservations.cancel');
     * - Method HTTP : DELETE (penghapusan logis / transisi status pembatalan entitas tiket).
     * - URI Path   : '/user/reservations/{id}/cancel'.
     * - Parameter  : {id} mewakili Primary Key tiket reservasi yang ingin dibatalkan.
     * - Handler    : ReservationController@cancel.
     * - Route Name : 'user.reservations.cancel'.
     * - Cara Kerja : Memverifikasi kepemilikan mutlak tiket (`user_id === Auth::id()`), memastikan
     *                status layak batal ('pending' / 'approved'), dan memvalidasi selisih waktu
     *                pelaksanaan kegiatan minimal 24 jam (H-1). Jika valid, status diubah menjadi
     *                'cancelled', mencatat keterangan pembatalan, dan melepas slot ruangan kembali.
     */
    Route::delete('/user/reservations/{id}/cancel', [ReservationController::class, 'cancel'])->name('user.reservations.cancel');

    /**
     * ------------------------------------------------------------------------------------------
     * MODUL 12: PELAPORAN KERUSAKAN FASILITAS & PELACAKAN TIKET (USR-04 & USR-05)
     * ------------------------------------------------------------------------------------------
     * Rute-rute di bawah ini melayani siklus hidup pengaduan kerusakan sarana prasarana kampus:
     * - Menampilkan formulir pelaporan mandiri sivitas dengan upload bukti foto.
     * - Menyimpan pengaduan baru dan menerbitkan kode tiket resmi `REP-YYYYMMDD-XXXX`.
     * - Menampilkan log riwayat penanganan tiket perbaikan oleh tim teknisi sarpras.
     */

    /**
     * RUTE: GET /user/report-form (Formulir Pelaporan Kerusakan Fasilitas - USR-04)
     * -----------------------------------------------------------------------------
     * Syntax: Route::get('/user/report-form', [ReportController::class, 'create'])->name('user.report-form');
     * - Method HTTP : GET (pengambilan representasi formulir pelaporan).
     * - URI Path   : '/user/report-form'.
     * - Handler    : ReportController@create.
     * - Route Name : 'user.report-form'.
     * - Cara Kerja : Menarik seluruh fasilitas aktif dari database untuk mengisi dropdown pilihan
     *                ruangan dan merender view responsif `user.report-form`.
     */
    Route::get('/user/report-form', [ReportController::class, 'create'])->name('user.report-form');

    /**
     * RUTE: POST /user/reports (Simpan Pengaduan Kerusakan Fasilitas Baru - USR-04)
     * ----------------------------------------------------------------------------
     * Syntax: Route::post('/user/reports', [ReportController::class, 'store'])->name('user.reports.store');
     * - Method HTTP : POST (pengiriman multipart form-data berisi deskripsi dan berkas foto).
     * - URI Path   : '/user/reports'.
     * - Handler    : ReportController@store.
     * - FormRequest: StoreDamageReportRequest (validasi tipe berkas gambar maks 2MB, panjang teks deskripsi).
     * - Route Name : 'user.reports.store'.
     * - Cara Kerja : Mengunggah berkas foto ke disk publik (`storage/app/public/damage-reports/`),
     *                membangkitkan kode tiket unik `REP-YYYYMMDD-XXXX`, menyimpan data berstatus 'baru',
     *                dan mengalihkan pengguna ke riwayat pelaporan dengan notifikasi sukses.
     */
    Route::post('/user/reports', [ReportController::class, 'store'])->name('user.reports.store');

    /**
     * RUTE: GET /user/report-history (Riwayat Pelacakan Tiket Kerusakan Sivitas - USR-05)
     * ----------------------------------------------------------------------------------
     * Syntax: Route::get('/user/report-history', [ReportController::class, 'history'])->name('user.report-history');
     * - Method HTTP : GET (pengambilan halaman rekapitulasi status tiket pengaduan).
     * - URI Path   : '/user/report-history'.
     * - Handler    : ReportController@history.
     * - Route Name : 'user.report-history'.
     * - Cara Kerja : Menarik seluruh tiket kerusakan milik pengguna login (`user_id === Auth::id()`)
     *                dengan Eager Loading relasi fasilitas (`with('facility')`), filter tab status,
     *                dan paginasi data untuk menampilkan progres perbaikan (Baru -> Diproses -> Selesai).
     */
    Route::get('/user/report-history', [ReportController::class, 'history'])->name('user.report-history');
});

/*
|--------------------------------------------------------------------------
| Rute Navigasi Dashboard Default & Profil Pengguna
|--------------------------------------------------------------------------
*/

// ROUTE: Menerima GET request ke '/dashboard'
// FUNGSI: Redirect dinamis berdasarkan role
Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user->hasRole('admin')) {
        return redirect()->route('admin.dashboard');
    } elseif ($user->hasRole('petugas')) {
        return redirect()->route('petugas.dashboard');
    }
    return redirect()->route('user.dashboard');
})->middleware('auth')->name('dashboard');

// Rute Profil Pengguna
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
