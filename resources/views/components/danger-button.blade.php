{{-- 
====================================================================================================
SISTEM INFORMASI RESERVASI & PELAPORAN FASILITAS KAMPUS (CAVA)
MODUL 6: KONSOL ADMIN - VERIFIKASI AKUN PENDING SIVITAS & MANAJEMEN PENGGUNA INTERNAL (ADM-01 & ADM-02)
BERKAS: resources/views/components/danger-button.blade.php
====================================================================================================

DESKRIPSI BERKAS:
Komponen Blade 'danger-button' berfungsi sebagai tombol aksi peringatan tinggi (High Alert / Destructive CTA)
yang digunakan khusus untuk operasi berisiko tinggi atau merusak data pada antarmuka CAVA.

PERAN & ARSITEKTUR DALAM SISTEM CAVA:
1. Semantik Visual Bahaya:
   Memberikan peringatan visual yang jelas bagi Administrator melalui palet warna merah semantik (bg-red-600)
   agar tidak terjadi eksekusi yang tidak disengaja. Digunakan pada aksi:
   - Penolakan Pendaftaran Sivitas Pending (Reject Verification Form).
   - Penghapusan / Pengarsipan Lunak Akun Pengguna (Soft Delete User).
   - Penonaktifan Cepat Status Akun (Suspend Account).
2. Standar Aksesibilitas:
   Dilengkapi indikator cincin fokus navigasi keyboard (`focus:ring-red-500`) yang kontras dan ramah aksesibilitas.
3. Fleksibilitas Penggabungan Atribut:
   Memanfaatkan `$attributes->merge()` sehingga pengembang dapat menentukan atau menimpa atribut HTML
   seperti `type="button"` (jika digunakan untuk trigger modal Alpine.js), `@click`, `wire:click`, atau kelas kustom.
====================================================================================================
--}}

{{--
  PENJELASAN ELEMEN BUTTON & MERGE ATRIBUT:
  1. '<button>': Elemen HTML standar tombol pemicu aksi formulir atau skrip klien.
  2. '$attributes->merge([...])': Menggabungkan atribut bawaan dengan atribut kustom dari pemanggil:
     - 'type' => 'submit': Tipe default untuk pengiriman formulir POST/DELETE.
     - 'class' => '...': Rangkaian utilitas Tailwind CSS:
       * 'inline-flex items-center': Penjajaran ikon dan teks label secara fleksibel di tengah.
       * 'px-4 py-2': Padding tombol proporsional untuk area sentuh yang ergonomis.
       * 'bg-red-600 hover:bg-red-500 active:bg-red-700': Palet merah tegas dengan feedback status hover dan active (saat ditekan).
       * 'border border-transparent rounded-md': Sudut tumpul melengkung rapi (6px) tanpa garis tepi kasar.
       * 'font-semibold text-xs text-white uppercase tracking-widest': Tipografi teks kapital dengan spasi lebar penanda tombol bahaya.
       * 'focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2': Cincin fokus aksesibilitas navigasi papan ketik.
       * 'transition ease-in-out duration-150': Efek transisi mikro yang halus selama 150ms.
--}}
<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150']) }}>
    {{--
      PENJELASAN SLOT KONTEN ($slot):
      Menampilkan label teks tombol (misal: "Tolak Akun", "Hapus Pengguna", "Nonaktifkan")
      beserta ikon SVG pendukung yang disisipkan oleh komponen pemanggil.
    --}}
    {{ $slot }}
</button>
