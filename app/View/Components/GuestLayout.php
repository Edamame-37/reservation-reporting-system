<?php
/**
 * NAMA FILE    : GuestLayout.php
 * FUNGSI       : Blade Component Class untuk master layout halaman otentikasi pengunjung / tamu
 * DESKRIPSI    : Menyajikan view layouts.guest saat tag <x-guest-layout> dipanggil pada halaman login, registrasi, dan lupa sandi.
 * CARA KERJA   : Mengembalikan view 'layouts.guest' untuk merender bingkai kartu otentikasi terpusat.
 */

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class GuestLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.guest');
    }
}
