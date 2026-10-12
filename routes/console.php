<?php
/**
 * NAMA FILE    : console.php
 * LOKASI       : routes/console.php
 * FUNGSI       : Pendaftaran Rute dan Perintah Artisan CLI Berbasis Closure (Laravel 11 Core)
 * DESKRIPSI    : Menyediakan tempat pendaftaran perintah command-line interaktif secara
 *                ringkas berbasis closure tanpa harus selalu membuat dedicated Command class
 *                tersendiri di app/Console/Commands.
 * CARA KERJA   : Saat pengguna mengetikkan perintah `php artisan <nama-command>` di terminal:
 *                1. Kernel Artisan memuat berkas routes/console.php ini.
 *                2. Mencocokkan nama perintah yang didaftarkan pada Artisan::command().
 *                3. Mengeksekusi closure callback terkait dengan konteks instance Command saat ini.
 */

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

// =========================================================================================
// CONTOH PERINTAH ARTISAN CLOSURE ('php artisan inspire')
// =========================================================================================
// SYNTAX     : Artisan::command(string $signature, Closure $callback)
// CARA KERJA : Mendaftarkan signature perintah 'inspire' ke daftar perintah terminal Artisan.
// FITUR PHP  : Di dalam closure callback anonim ini, variabel '$this' secara otomatis
//              diikat (bound) ke instance kelas Illuminate\Console\Command.
// METODE     : - $this->comment(...): Mencetak output string berwarna kuning pudar ke terminal konsol.
//              - Inspiring::quote(): Menghasilkan kutipan motivasi acak bawaan Laravel.
//              - ->purpose(...): Menetapkan teks deskripsi penjelasan yang akan muncul ketika
//                pengembang menjalankan perintah 'php artisan list' di terminal.
Artisan::command('inspire', function () {
    // Menampilkan kutipan inspiratif di konsol terminal pengembang
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
