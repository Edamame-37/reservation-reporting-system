<?php
/**
 * ==============================================================================
 * NAMA FILE    : LoginRequest.php
 * MODUL        : Modul 5 - Alur Autentikasi Pengguna & Registrasi Mandiri Sivitas (Laravel Breeze Core)
 * KODE FITUR   : AUTH-01 (Autentikasi Login & Penjaga Status Akun Sivitas)
 * ==============================================================================
 * DESKRIPSI:
 * Form Request khusus yang mengontrol validasi masukan kredensial login (email dan sandi),
 * mekanisme pertahanan anti brute-force attack melalui RateLimiter (maksimal 5 percobaan),
 * pemrosesan verifikasi hash sandi via `Auth::attempt()`, serta validasi status siklus
 * hidup akun pengguna (status gatekeeper):
 * - Jika status 'pending'  : Akses diblokir, muncul pesan edukasi antrean verifikasi admin.
 * - Jika status 'rejected' : Akses diblokir, menampilkan alasan penolakan dari admin.
 * - Jika status 'active'   : Autentikasi diloloskan dan sesi sesi aktif dibuat.
 *
 * ALUR SISTEM:
 * POST /login ➔ LoginRequest
 *   ├── authorize() (Izinkan publik/guest)
 *   ├── rules() (Validasi format email & string sandi)
 *   └── authenticate()
 *        ├── ensureIsNotRateLimited() (Cek limit 5 percobaan via IP|email)
 *        ├── Auth::attempt() (Pencocokan hash Bcrypt ke tabel users)
 *        ├── Gatekeeper Status Akun ($user->status !== 'active' ➔ Logout & Exception)
 *        └── RateLimiter::clear() (Reset counter percobaan jika berhasil)
 * ==============================================================================
 */

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Menentukan apakah pemohon (klien) memiliki otorisasi untuk menjalankan request ini.
     *
     * @return bool True karena endpoint login terbuka untuk seluruh pengunjung (guest)
     *
     * CARA KERJA & SYNTAX:
     * Mengembalikan nilai boolean `true` tanpa pengecekan token sesi,
     * karena formulir login harus dapat diakses oleh siapa saja sebelum login.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Mendefinisikan aturan validasi (validation rules) yang berlaku untuk formulir login.
     *
     * @return array<string, ValidationRule|array<mixed>|string> Aturan validasi input form
     *
     * CARA KERJA & SYNTAX:
     * 1. 'email' => ['required', 'string', 'email']:
     *    - 'required': Input email tidak boleh kosong.
     *    - 'string'  : Input harus berupa tipe string.
     *    - 'email'   : Memvalidasi sintaks format email standar (RFC 5322).
     * 2. 'password' => ['required', 'string']:
     *    - Memastikan kata sandi diisi dan berupa string sebelum diverifikasi ke hash basis data.
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Memproses verifikasi kredensial, pembatasan laju (throttling), dan verifikasi status akun.
     *
     * @return void
     * @throws \Illuminate\Validation\ValidationException Jika kredensial salah, dibatasi, atau akun non-aktif
     *
     * CARA KERJA & SYNTAX:
     * 1. $this->ensureIsNotRateLimited(): Memastikan IP dan email belum melebihi 5 kali percobaan gagal.
     * 2. Auth::attempt($credentials, $remember):
     *    - Menarik baris pengguna dari tabel `users` berdasarkan email.
     *    - Menggunakan Hash::check() untuk membandingkan plain text password dengan hash bcrypt di kolom `password`.
     *    - Jika $this->boolean('remember') bernilai true, Laravel membuat cookie 'remember_web' berumur panjang.
     * 3. RateLimiter::hit($this->throttleKey()): Menambah counter percobaan gagal jika kredensial tidak cocok.
     * 4. Gatekeeper Status Siklus Hidup ($user->status !== 'active'):
     *    - Walaupun email dan password benar, CAVA mewajibkan verifikasi manual oleh Super Admin (ADM-01).
     *    - Auth::logout(): Sesi pengguna yang sempat terbuat langsung dibatalkan seketika.
     *    - Melempar ValidationException dengan pesan edukatif spesifik ('pending', 'rejected' + alasan).
     * 5. RateLimiter::clear($this->throttleKey()): Menghapus histori kegagalan setelah login sukses.
     */
    public function authenticate(): void
    {
        // 1. Periksa batas laju percobaan gagal (Anti Brute-Force)
        $this->ensureIsNotRateLimited();

        // 2. Eksekusi pencocokan kredensial email dan password
        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            // Catat 1 kegagalan ke RateLimiter
            RateLimiter::hit($this->throttleKey());

            // Lempar pesan kesalahan standar autentikasi
            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        // 3. Pengecekan Status Pengguna (Gatekeeper Akun Sivitas Kampus CAVA)
        $user = Auth::user();
        if ($user && $user->status !== 'active') {
            // Batalkan sesi yang sempat terbentuk
            Auth::logout();
            // Catat percobaan login terblokir ke RateLimiter
            RateLimiter::hit($this->throttleKey());

            // Skenario A: Pendaftaran mandiri masih menunggu peninjauan Biro Sarpras
            if ($user->status === 'pending') {
                throw ValidationException::withMessages([
                    'email' => 'Akun Anda masih berstatus pending dan menunggu verifikasi dari Admin.',
                ]);
            }

            // Skenario B: Pendaftaran ditolak (tampilkan catatan alasan penolakan jika ada)
            if ($user->status === 'rejected') {
                $reason = $user->rejection_reason ? ' Alasan: ' . $user->rejection_reason : '';
                throw ValidationException::withMessages([
                    'email' => 'Pendaftaran akun Anda ditolak oleh Admin.' . $reason,
                ]);
            }

            // Skenario C: Akun dibekukan atau dinonaktifkan oleh administrator
            throw ValidationException::withMessages([
                'email' => 'Akun Anda tidak aktif.',
            ]);
        }

        // 4. Bersihkan riwayat throttle limiter karena pengguna berhasil masuk secara sah
        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Memastikan request saat ini belum terkunci akibat terlalu banyak percobaan login gagal.
     *
     * @return void
     * @throws \Illuminate\Validation\ValidationException Jika melebihi batas 5 percobaan
     *
     * CARA KERJA & SYNTAX:
     * 1. RateLimiter::tooManyAttempts($key, 5): Mengecek apakah counter melebihi batas maksimal 5 kali.
     * 2. event(new Lockout($this)): Memicu event sistem Laravel untuk pencatatan audit log keamanan.
     * 3. RateLimiter::availableIn($key): Mengambil sisa durasi detik penalti sebelum pengguna boleh mencoba lagi.
     * 4. ValidationException::withMessages(...): Menampilkan notifikasi waktu tunggu ke antarmuka pengguna.
     */
    public function ensureIsNotRateLimited(): void
    {
        // Periksa apakah sudah mencapai 5 kali percobaan gagal
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        // Picu Event Lockout sistem Laravel
        event(new Lockout($this));

        // Ambil sisa waktu penalti dalam satuan detik
        $seconds = RateLimiter::availableIn($this->throttleKey());

        // Lempar exception dengan pesan multibahasa auth.throttle
        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Membangun kunci identifikasi throttle unik berbasis kombinasi email pengguna dan alamat IP klien.
     *
     * @return string Kunci string unik (misal: 'john@undip.ac.id|192.168.1.10')
     *
     * CARA KERJA & SYNTAX:
     * 1. $this->string('email'): Mengambil nilai input email dalam bentuk Stringable instance.
     * 2. Str::lower(...): Menormalkan huruf menjadi huruf kecil agar 'User@domain' dan 'user@domain' dihitung sama.
     * 3. Str::transliterate(...): Mengonversi karakter aksen atau Unicode non-standar menjadi ASCII biasa.
     * 4. $this->ip(): Mengambil IP address klien untuk membatasi serangan yang berasal dari jaringan yang sama.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
