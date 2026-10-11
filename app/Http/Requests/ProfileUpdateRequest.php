<?php
/**
 * NAMA FILE    : ProfileUpdateRequest.php
 * FUNGSI       : Form Request validasi pembaruan profil pengguna
 * DESKRIPSI    : Memvalidasi integritas perubahan nama lengkap dan alamat email pengguna aktif dengan pengecualian ID pengguna sendiri pada keunikan email.
 * CARA KERJA   : Menerima payload HTTP PATCH dari formulir profil, memastikan format string dan batasan panjang, serta memvalidasi keunikan email di tabel users.
 */

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * FUNCTION/PROCEDURE : rules()
     * KEGUNAAN           : Mendefinisikan aturan validasi integritas payload profil.
     * CARA KERJA         : Memeriksa nama wajib diisi dan email unik kecuali ID pengguna saat ini.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($this->user()->id),
            ],
        ];
    }
}
