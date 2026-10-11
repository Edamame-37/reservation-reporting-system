<?php
/**
 * NAMA FILE    : LoginRequest.php
 * FUNGSI       : Form Request validasi kredensial dan mitigasi serangan brute-force login
 * DESKRIPSI    : Memvalidasi keberadaan email dan kata sandi, memeriksa pembatasan laju percobaan login (Rate Limiting), dan memproses percobaan otentikasi via Auth::attempt().
 * CARA KERJA   : Memeriksa rate limiter IP/email, menjalankan Auth::attempt() dengan opsi remember-me, dan melempar Lockout event serta ValidationException jika terdeteksi kegagalan berulang.
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
     * FUNCTION/PROCEDURE : authorize()
     * KEGUNAAN           : Menentukan apakah pengguna diizinkan mengeksekusi request ini.
     * CARA KERJA         : Mengembalikan true untuk mengizinkan seluruh request login publik.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        $user = Auth::user();
        if ($user && $user->status !== 'active') {
            Auth::logout();
            RateLimiter::hit($this->throttleKey());

            if ($user->status === 'pending') {
                throw ValidationException::withMessages([
                    'email' => 'Akun Anda masih berstatus pending dan menunggu verifikasi dari Admin.',
                ]);
            }

            if ($user->status === 'rejected') {
                $reason = $user->rejection_reason ? ' Alasan: ' . $user->rejection_reason : '';
                throw ValidationException::withMessages([
                    'email' => 'Pendaftaran akun Anda ditolak oleh Admin.' . $reason,
                ]);
            }

            throw ValidationException::withMessages([
                'email' => 'Akun Anda tidak aktif.',
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
