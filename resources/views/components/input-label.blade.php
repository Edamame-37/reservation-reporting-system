{{-- 
  ==============================================================================
  NAMA FILE      : input-label.blade.php
  MODUL          : Modul 5 - Alur Autentikasi Pengguna & Registrasi Mandiri Sivitas (Laravel Breeze Core)
  KODE FITUR     : AUTH-01 & AUTH-02 (Komponen Blade Label Input Formulir)
  ==============================================================================
  DESKRIPSI:
  Komponen UI Blade reusable untuk label teks formulir (`<label>`).
  Mendukung fleksibilitas pemberian teks label melalui prop `:value` atau melalui konten slot anak (`$slot`).
  Menyediakan gaya tipografi terstandar (text-sm, font-medium, dan warna netral adaptif dark mode).

  CARA PENGGUNAAN:
  <x-input-label for="email" :value="__('Email')" />
  <x-input-label for="password">Kata Sandi Baru</x-input-label>
  ==============================================================================
--}}

{{-- Inisialisasi Properti Komponen: 'value' teks label opsional --}}
@props(['value'])

{{-- 
  ELEMEN LABEL:
  - $attributes->merge([...]): Menggabungkan atribut HTML seperti 'for' dengan kelas default tipografi.
  - {{ $value ?? $slot }}: Mengutamakan prop 'value' jika disediakan; jika tidak, merender konten slot di antara tag pembuka & penutup.
--}}
<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-gray-700 dark:text-gray-300']) }}>
    {{ $value ?? $slot }}
</label>
