{{-- 
  ========================================================================================
  SISTEM INFORMASI MANAJEMEN FASILITAS & SARANA PRASARANA KAMPUS (CAVA)
  ========================================================================================
  NAMA FILE       : application-logo.blade.php
  LOKASI FILE     : resources/views/components/application-logo.blade.php
  DESKRIPSI       : Komponen Identitas Visual & Logo Vektor Brand CAVA (Application Logo)
  ARSITEKTUR      : Blade View Component (UI Shared Component Laravel Breeze)
  AUTHOR          : Tim Pengembang CAVA & Sivitas Akademika
  
  FITUR UTAMA:
  1. Grafis Vektor Terukur (Scalable Vector Graphics): Menggunakan elemen SVG murni dengan 
     `viewBox="0 0 400 100"` sehingga logo tetap tajam sempurna pada resolusi retina/4K.
  2. Pewarnaan Gradien Halus (Linear Gradient): Menggabungkan warna Indigo (#4F46E5) ke Violet (#7C3AED) 
     yang mencerminkan estetika modern dan profesional.
  3. Aksen Visual Dinamis: Dilengkapi lingkaran aksen amber (#F59E0B) sebagai titik fokus brand CAVA.
  4. Kompatibilitas Atribut Bebas ($attributes): Mengizinkan penyematan kelas ukuran Tailwind 
     (seperti `h-9 w-auto`) atau atribut ARIA langsung dari tag pemanggil `<x-application-logo>`.
  ========================================================================================
--}}

{{-- 
  Elemen Root SVG:
  - `viewBox="0 0 400 100"`: Menentukan sistem koordinat internal vektor (lebar 400 unit, tinggi 100 unit).
  - `xmlns="http://www.w3.org/2000/svg"`: Namespace XML standar W3C untuk format dokumen SVG.
  - `{{ $attributes }}`: Menggabungkan kelas CSS pembungkus (misal: h-9, w-auto) yang dikirim pemanggil.
--}}
<svg viewBox="0 0 400 100" xmlns="http://www.w3.org/2000/svg" {{ $attributes }}>
    {{-- Definisi Sumber Daya Grafis (Gradients, ClipPaths, Filters) --}}
    <defs>
        {{-- Gradien Warna Linier Horizontal dari Kiri ke Kanan --}}
        <linearGradient id="cavaGrad" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" stop-color="#4F46E5" />   <!-- Indigo 600 -->
            <stop offset="100%" stop-color="#7C3AED" /> <!-- Violet 600 -->
        </linearGradient>
    </defs>

    {{-- Teks Brand "CAVA" dengan Tipografi Inter Tebal & Warna Gradien --}}
    <text x="0" y="75" font-family="Inter, sans-serif" font-size="80" font-weight="800" fill="url(#cavaGrad)" letter-spacing="-2">CAVA</text>

    {{-- Lingkaran Aksen Estetis Berwarna Kuning Amber (#F59E0B) di Samping Huruf Terakhir --}}
    <circle cx="215" cy="70" r="12" fill="#F59E0B" />
</svg>
