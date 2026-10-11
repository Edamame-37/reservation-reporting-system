<?php
/**
 * NAMA FILE    : console.php
 * FUNGSI       : Pendaftaran rute dan perintah CLI Artisan berbasis closure
 * DESKRIPSI    : Mendefinisikan perintah-perintah konsol interaktif untuk utilitas CLI sistem CAVA.
 * CARA KERJA   : Memanggil Artisan::command() untuk mendaftarkan perintah CLI langsung.
 */

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
