<?php
/**
 * NAMA FILE    : AppLayout.php
 * FUNGSI       : Blade Component Class untuk master layout portal pengguna (Mahasiswa/Dosen)
 * DESKRIPSI    : Menyajikan view layouts.app saat tag <x-app-layout> dipanggil pada tampilan portal pengguna.
 * CARA KERJA   : Mengembalikan view 'layouts.app' untuk merender antarmuka navigasi dan dashboard pengguna.
 */

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AppLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.app');
    }
}
