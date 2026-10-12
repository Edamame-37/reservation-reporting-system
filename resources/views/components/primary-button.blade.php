{{-- 
====================================================================================================
SISTEM INFORMASI RESERVASI & PELAPORAN FASILITAS KAMPUS (CAVA)
MODUL 5: ALUR AUTENTIKASI PENGGUNA & REGISTRASI MANDIRI SIVITAS (AUTH-01 & AUTH-02)
BERKAS: resources/views/components/primary-button.blade.php
====================================================================================================

DESKRIPSI BERKAS:
Komponen Blade 'primary-button' berfungsi sebagai elemen tombol aksi utama (Call to Action / CTA)
yang seragam di seluruh modul autentikasi CAVA (Login, Register, Forgot Password, Reset Password, 
Confirm Password, dan Update Password).

PERAN & ARSITEKTUR:
1. Standardisasi UI: Menjamin konsistensi visual tombol utama (warna slate-900 elegan, elevasi bayangan,
   tipografi bold uppercase berjarak, dan sudut membulat rounded-xl).
2. Aksesibilitas & Interaktivitas: Menyertakan ring fokus navigasi papan ketik (WCAG AA compliant)
   serta efek umpan balik taktil saat tombol ditekan (active:scale-95).
3. Fleksibilitas Atribut: Memanfaatkan '$attributes->merge()' sehingga pengembang dapat menambahkan
   atau menimpa atribut HTML standar (seperti 'id', 'name', 'wire:click', 'disabled', atau class kustom)
   tanpa merusak konfigurasi bawaan.
====================================================================================================
--}}

{{--
  PENJELASAN ELEMEN BUTTON & MERGE ATRIBUT:
  1. '<button>': Elemen HTML standar untuk memicu pengiriman formulir atau aksi JavaScript.
  2. '$attributes->merge([...])': Fitur Blade Component Attributes Bag yang menggabungkan atribut
     default dengan atribut tambahan yang dioper dari pemanggil komponen:
     - 'type' => 'submit': Nilai bawaan tombol formulir (dapat ditimpa menjadi type="button" jika diperlukan).
     - 'class' => '...': Rangkaian utilitas Tailwind CSS untuk visual dan interaksi:
       * 'inline-flex justify-center items-center': Penjajaran konten teks/ikon tepat di tengah secara fleksibel.
       * 'px-6 py-3': Area sentuh yang ergonomis (padding horizontal 24px, padding vertikal 12px).
       * 'bg-slate-900 hover:bg-slate-800': Palet warna dasar slate gelap dengan efek transisi saat kursor melayang (hover).
       * 'border border-transparent': Menghindari pergeseran tata letak saat dipadukan dengan fokus/garis batas.
       * 'rounded-xl': Sudut tumpul melengkung modern (12px) yang serasi dengan desain form CAVA.
       * 'font-bold text-sm text-white uppercase tracking-wider': Format tipografi tegas, berhuruf kapital, dan spasi antar-huruf longgar.
       * 'focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-2 dark:focus:ring-offset-gray-800':
         Indikator visual fokus navigasi keyboard yang aman bagi aksesibilitas tuna netra / keyboard-only user.
       * 'transform active:scale-95': Efek mikro-animasi taktil di mana tombol sedikit mengecil (95%) saat diklik/ditekan.
       * 'transition-all ease-in-out duration-200': Transisi halus berdurasi 200ms untuk semua perubahan status interaksi.
       * 'shadow-md hover:shadow-lg': Bayangan elevasi yang meninggi saat kursor diarahkan ke tombol.
--}}
<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex justify-center items-center px-6 py-3 bg-slate-900 hover:bg-slate-800 border border-transparent rounded-xl font-bold text-sm text-white uppercase tracking-wider focus:outline-none focus:ring-2 focus:ring-slate-900 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transform active:scale-95 transition-all ease-in-out duration-200 shadow-md hover:shadow-lg']) }}>
    {{--
      PENJELASAN SLOT KONTEN ($slot):
      Menampilkan teks label tombol, elemen ikon (SVG/FontAwesome), atau indikator loading spinner
      yang disisipkan di antara tag pembuka <x-primary-button> dan penutup </x-primary-button>.
    --}}
    {{ $slot }}
</button>
