{{-- 
  ==============================================================================
  NAMA FILE      : input-error.blade.php
  MODUL          : Modul 5 - Alur Autentikasi Pengguna & Registrasi Mandiri Sivitas (Laravel Breeze Core)
  KODE FITUR     : AUTH-01 & AUTH-02 (Komponen Blade Tampilan Pesan Kesalahan Validasi)
  ==============================================================================
  DESKRIPSI:
  Komponen UI Blade reusable untuk menampilkan daftar pesan kesalahan (validation error messages)
  yang dihasilkan oleh FormRequest atau validator Laravel.
  Otomatis merender elemen list (`<ul>` & `<li>`) berwarna merah semantik hanya jika variabel
  `messages` memiliki isi, serta mendukung casting array aman `(array) $messages`.

  CARA PENGGUNAAN:
  <x-input-error :messages="$errors->get('email')" class="mt-2" />
  <x-input-error :messages="$errors->updatePassword->get('password')" />
  ==============================================================================
--}}

{{-- Inisialisasi Properti Komponen: 'messages' dapat berupa string tunggal atau array pesan --}}
@props(['messages'])

{{-- 
  KONDISIONAL PENAMPIL ERROR:
  - @if ($messages): Hanya merender markup HTML jika terdapat setidaknya 1 pesan error.
  - $attributes->merge([...]): Menggabungkan margin eksternal (misal 'mt-2') dengan kelas warna teks merah (text-red-600).
  - @foreach ((array) $messages as $message): Mengonversi pesan ke array dan menampilkan tiap butir error dalam tag <li>.
--}}
@if ($messages)
    <ul {{ $attributes->merge(['class' => 'text-sm text-red-600 dark:text-red-400 space-y-1']) }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
