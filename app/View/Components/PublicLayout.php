<?php
/**
 * NAMA FILE    : PublicLayout.php
 * FUNGSI       : Blade Component Class untuk master layout portal publik CAVA
 * DESKRIPSI    : Menyajikan view layouts.public saat tag <x-public-layout> dipanggil pada view mockup.
 * CARA KERJA   : Mengembalikan view 'layouts.public' untuk merender antarmuka publik kampus.
 */

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class PublicLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.public');
    }
}
