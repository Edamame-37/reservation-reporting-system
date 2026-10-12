{{-- 
====================================================================================================
SISTEM INFORMASI RESERVASI & PELAPORAN FASILITAS KAMPUS (CAVA)
MODUL 6: KONSOL ADMIN - VERIFIKASI AKUN PENDING SIVITAS & MANAJEMEN PENGGUNA INTERNAL (ADM-01 & ADM-02)
BERKAS: resources/views/components/modal.blade.php
====================================================================================================

DESKRIPSI BERKAS:
Komponen Blade 'modal' ini menyediakan dialog pop-up modal interaktif dan aksesibel (WAI-ARIA compliant)
berbasis Alpine.js dan Tailwind CSS. Digunakan secara intensif pada Konsol Manajemen Pengguna (ADM-01 & ADM-02)
untuk menampilkan:
1. Modal Pratinjau Dokumen Identitas (KTM/NIP/Surat Tugas) ukuran penuh sebelum verifikasi.
2. Dialog Formulir Konfirmasi Penolakan Akun beserta input alasan dan catatan.
3. Modal Formulir Pendaftaran Akun Internal Petugas Sarpras & Pengguna Baru.

PERAN & ARSITEKTUR DALAM SISTEM CAVA:
1. Komunikasi Event-Driven (Decoupled Architecture):
   Modal dapat dibuka atau ditutup dari tombol mana pun menggunakan event JavaScript standar:
   `$dispatch('open-modal', 'nama-modal')` atau `$dispatch('close-modal', 'nama-modal')`.
2. Aksesibilitas Penuh (Focus Trapping & Keyboard Navigasi):
   - Mengurung fokus tombol keyboard (Tab / Shift+Tab) agar tidak bocor ke luar jendela modal saat terbuka.
   - Tombol Escape (`keydown.escape.window`) otomatis menutup modal.
   - Opsi `focusable` otomatis mengarahkan kursor ke input/tombol pertama saat modal dibuka.
3. Kunci Scroll Latar Belakang (Scroll Lock):
   Secara otomatis menambahkan kelas `overflow-y-hidden` pada elemen `document.body` saat modal aktif,
   mencegah pergeseran halaman latar belakang yang mengganggu kenyamanan pengguna.
4. Transisi Halus (Micro-Interactions):
   Menerapkan animasi fading pada backdrop overlay dan animasi zoom-scaling (scale-95 ke scale-100)
   pada kotak konten modal.
====================================================================================================
--}}

{{--
  PENJELASAN DIREKTIF @props:
  - 'name'     : Identifier unik string modal (wajib disetel, contoh: 'preview-ktm-12' atau 'reject-user-modal').
  - 'show'     : Status awal tampilan modal (default: false / tersembunyi).
  - 'maxWidth' : Batas lebar kartu modal (pilihan: 'sm', 'md', 'lg', 'xl', '2xl' - default: '2xl').
--}}
@props([
    'name',
    'show' => false,
    'maxWidth' => '2xl'
])

{{--
  PENJELASAN PEMETAAN KELAS LEBAR TAILWIND ($maxWidth):
  Mengonversi kode string ukuran menjadi utility class Tailwind CSS yang sesuai.
--}}
@php
$maxWidth = [
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
][$maxWidth];
@endphp

{{--
  PENJELASAN KONTINER UTAMA DENGAN REAKTIVITAS ALPINE.JS:
  - x-data: Mendefinisikan status dan fungsi jebakan fokus keyboard (Focus Trap):
    * show: Mengikat nilai awal visibilitas dengan JSON boolean @js($show).
    * focusables(): Mengumpulkan seluruh elemen interaktif yang bisa menerima fokus (a, button, input, dll).
    * firstFocusable() / lastFocusable(): Mengambil elemen pertama/terakhir untuk looping siklus fokus.
    * nextFocusable() / prevFocusable(): Menghitung elemen berikutnya saat tombol Tab ditekan.
  - x-init="$watch(...)": Memantau perubahan state 'show' untuk mengunci/membuka scroll body dan mengatur fokus.
  - x-on:open-modal.window: Mendengarkan custom event 'open-modal' dari jendela window global.
  - x-on:close-modal.window: Mendengarkan custom event 'close-modal' global.
  - x-on:close.stop: Menutup modal dan menghentikan propagasi event.
  - x-on:keydown.escape.window: Menutup modal seketika saat tombol ESC ditekan.
  - x-on:keydown.tab.prevent: Menjebak fokus keyboard agar tetap berputar di dalam modal.
--}}
<div
    x-data="{
        show: @js($show),
        focusables() {
            // Mengambil semua elemen interaktif yang dapat difokuskan di dalam modal
            let selector = 'a, button, input:not([type=\'hidden\']), textarea, select, details, [tabindex]:not([tabindex=\'-1\'])'
            return [...$el.querySelectorAll(selector)]
                // Menyaring hanya elemen yang tidak berstatus disabled
                .filter(el => ! el.hasAttribute('disabled'))
        },
        firstFocusable() { return this.focusables()[0] },
        lastFocusable() { return this.focusables().slice(-1)[0] },
        nextFocusable() { return this.focusables()[this.nextFocusableIndex()] || this.firstFocusable() },
        prevFocusable() { return this.focusables()[this.prevFocusableIndex()] || this.lastFocusable() },
        nextFocusableIndex() { return (this.focusables().indexOf(document.activeElement) + 1) % (this.focusables().length + 1) },
        prevFocusableIndex() { return Math.max(0, this.focusables().indexOf(document.activeElement)) -1 },
    }"
    x-init="$watch('show', value => {
        if (value) {
            // Mengunci scrollbar halaman latar belakang saat modal aktif
            document.body.classList.add('overflow-y-hidden');
            {{ $attributes->has('focusable') ? 'setTimeout(() => firstFocusable().focus(), 100)' : '' }}
        } else {
            // Memulihkan kembali scrollbar halaman saat modal ditutup
            document.body.classList.remove('overflow-y-hidden');
        }
    })"
    x-on:open-modal.window="$event.detail == '{{ $name }}' ? show = true : null"
    x-on:close-modal.window="$event.detail == '{{ $name }}' ? show = false : null"
    x-on:close.stop="show = false"
    x-on:keydown.escape.window="show = false"
    x-on:keydown.tab.prevent="$event.shiftKey || nextFocusable().focus()"
    x-on:keydown.shift.tab.prevent="prevFocusable().focus()"
    x-show="show"
    class="fixed inset-0 overflow-y-auto px-4 py-6 sm:px-0 z-50"
    style="display: {{ $show ? 'block' : 'none' }};"
>
    {{--
      PENJELASAN BACKDROP OVERLAY (Latar Belakang Gelap Semi-Transparan):
      - x-on:click="show = false": Menutup modal saat pengguna mengeklik area luar (backdrop).
      - x-transition: Animasi fade-in halus saat muncul (duration-300) dan fade-out saat keluar (duration-200).
    --}}
    <div
        x-show="show"
        class="fixed inset-0 transform transition-all"
        x-on:click="show = false"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
    >
        <div class="absolute inset-0 bg-gray-500 dark:bg-gray-900 opacity-75"></div>
    </div>

    {{--
      PENJELASAN KOTAK KONTEN MODAL:
      - x-transition: Menggabungkan animasi opacity dan zoom (scale-95 -> scale-100) bergaya dialog sistem modern.
      - {{ $slot }}: Tempat disuntikkannya formulir, tombol aksi, pratinjau gambar, atau pesan konfirmasi.
    --}}
    <div
        x-show="show"
        class="mb-6 bg-white dark:bg-gray-800 rounded-lg overflow-hidden shadow-xl transform transition-all sm:w-full {{ $maxWidth }} sm:mx-auto"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
        x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
    >
        {{ $slot }}
    </div>
</div>
