<?php
/**
 * NAMA FILE    : AdminLayout.php
 * FUNGSI       : Blade Component Class untuk master layout konsol biro sarpras (Super Admin)
 * DESKRIPSI    : Menyajikan view layouts.admin saat tag <x-admin-layout> dipanggil pada view mockup.
 * CARA KERJA   : Mengembalikan view 'layouts.admin' untuk merender antarmuka konsol manajemen kampus.
 */

namespace App\View\Components;

use Illuminate\View\Component;
use Illuminate\View\View;

class AdminLayout extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.admin');
    }
}
