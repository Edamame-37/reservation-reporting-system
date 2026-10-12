{{-- 
  ==============================================================================
  NAMA FILE      : text-input.blade.php
  MODUL          : Modul 5 - Alur Autentikasi Pengguna & Registrasi Mandiri Sivitas (Laravel Breeze Core)
  KODE FITUR     : AUTH-01 & AUTH-02 (Komponen Blade Input Teks Terstandar)
  ==============================================================================
  DESKRIPSI:
  Komponen UI Blade reusable untuk elemen formulir input teks `<input>`.
  Menerapkan penggabungan atribut cerdas (`$attributes->merge()`) dengan kelas Tailwind CSS
  bawaan (border rounded-xl, efek focus ring, transisi halus, dan dukungan dark mode),
  serta direktif kondisional `@disabled($disabled)` untuk menonaktifkan interaksi input saat diperlukan.

  CARA PENGGUNAAN:
  <x-text-input type="text" name="name" :value="old('name')" required />
  <x-text-input type="email" name="email" :disabled="$isReadonly" />
  ==============================================================================
--}}

{{-- Inisialisasi Properti Komponen: 'disabled' bernilai false secara default --}}
@props(['disabled' => false])

{{-- 
  ELEMEN INPUT:
  - @disabled($disabled): Direktif Blade yang menambahkan atribut HTML 'disabled' jika $disabled bernilai true.
  - $attributes->merge([...]): Menggabungkan atribut eksternal (seperti type, name, id, value, placeholder)
    dengan kelas CSS default tanpa menimpa atribut tambahan yang diberikan dari pemanggil komponen.
--}}
<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full px-4 py-3 border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-900 dark:text-gray-100 placeholder-gray-400 focus:border-[#7C3AED] focus:bg-white dark:focus:bg-gray-800 focus:ring-2 focus:ring-[#7C3AED]/20 rounded-xl shadow-sm transition duration-200 ease-in-out']) }}>
