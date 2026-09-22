<?php
/**
 * NAMA FILE    : PetugasLayout.php
 * FUNGSI       : Blade Component Class untuk master layout operasional petugas sarpras CAVA
 * DESKRIPSI    : Menyajikan view layouts.petugas saat tag <x-petugas-layout> dipanggil pada view mockup.
 * CARA KERJA   : Mengembalikan view 'layouts.petugas' untuk merender antarmuka verifikasi dan operasional petugas sarpras.
 */

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class PetugasLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.petugas');
    }
}
