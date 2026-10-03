<?php
/**
 * NAMA FILE    : PublicInformationController.php
 * FUNGSI       : Menangani halaman informasi publik (Kebijakan, Syarat & Ketentuan, dan Bantuan).
 * DESKRIPSI    : Menyediakan endpoint untuk menampilkan panduan, tata tertib peminjaman, kebijakan privasi/operasional, serta pusat bantuan & FAQ untuk sivitas akademika dan publik.
 * CARA KERJA   : Menerima request HTTP GET dengan parameter seksi aktif, memvalidasi tab yang diminta, dan mengembalikan view 'public.information'.
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicInformationController extends Controller
{
    /**
     * Menampilkan halaman pusat informasi publik.
     *
     * @param Request $request
     * @param string|null $section
     * @return View
     */
    public function index(Request $request, ?string $section = null): View
    {
        $validSections = ['kebijakan', 'syarat-ketentuan', 'bantuan'];

        // Jika section tidak diberikan di URL, cek query parameter ?tab=... atau default ke 'kebijakan'
        $activeSection = $section ?? $request->query('tab', 'kebijakan');

        if (!in_array($activeSection, $validSections, true)) {
            $activeSection = 'kebijakan';
        }

        return view('public.information', [
            'activeSection' => $activeSection,
        ]);
    }
}
