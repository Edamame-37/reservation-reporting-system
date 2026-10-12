{{-- 
  ==============================================================================
  NAMA FILE      : slot-matrix.blade.php
  MODUL          : Modul 4 - Portal Publik: Beranda, Katalog Fasilitas, Filter & Matriks Ketersediaan
  KODE FITUR     : PUB-01 (Komponen Visual Matriks Slot Waktu 30 Menit & Pemilih Okupansi)
  ==============================================================================
  DESKRIPSI:
  Komponen Blade modular (reusable UI component) yang menyajikan visualisasi okupansi ketersediaan
  fasilitas dalam 2 mode operasional:
  1. Mode Ringkasan (interactive=false):
     Ditampilkan pada kartu katalog publik. Menghilangkan kerumitan puluhan kotak dengan menyajikan
     progress bar proporsional keterisian ruang dan ringkasan sesi waktu (Pagi, Siang, Sore).
  2. Mode Interaktif (interactive=true):
     Ditampilkan pada formulir pengajuan reservasi. Menggunakan Alpine.js untuk membagi 26 slot 30-menit
     ke dalam 3 tab sesi (Pagi 07:00-12:00, Siang 12:00-16:00, Sore 16:00-20:00) dengan proteksi bentrok
     jadwal (booked slots otomatis disabled) dan penanda visual slot terpilih.

  CARA PENGGUNAAN:
  <x-cava.slot-matrix :availableCount="$f['availableSlots']" :totalCount="$f['totalSlots']" />
  <x-cava.slot-matrix :interactive="true" />
  ==============================================================================
--}}

{{-- Inisialisasi Properti Komponen Blade (@props) --}}
@props([
    'slots' => [],           // Array data slot waktu (opsional)
    'interactive' => false,  // Boolean: false = mode visual bar ringkas; true = mode tombol interaktif
    'availableCount' => 14,  // Jumlah slot yang masih bebas (default 14)
    'totalCount' => 26       // Total slot operasional kampus (13 jam * 2 slot/jam = 26 slot)
])

@if(!$interactive)
    {{-- 
      ==========================================================================
      MODE 1: MINIMALIST AVAILABILITY BAR & SESSION BADGES (Mode Ringkasan Katalog)
      ==========================================================================
      Menyajikan visual rasio keterisian ruang secara bersih tanpa membuat layar sesak.
    --}}
    <div class="pt-2">
        {{-- Header Teks Okupansi --}}
        <div class="flex items-center justify-between text-xs text-slate-600 mb-1.5">
            <span class="font-medium flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Okupansi Hari Ini: <strong>{{ $availableCount }} dari {{ $totalCount }}</strong> Slot Bebas</span>
            </span>
            <span class="text-[11px] font-mono text-slate-400">07:00 - 20:00 WIB</span>
        </div>

        {{-- Progress Bar Dua Warna (Hijau = Tersedia, Merah Muda = Terpakai) --}}
        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden flex mb-2.5">
            <div class="bg-emerald-500 h-full transition-all" style="width: {{ $totalCount > 0 ? ($availableCount / $totalCount) * 100 : 0 }}%"></div>
            <div class="bg-rose-400 h-full transition-all" style="width: {{ $totalCount > 0 ? (($totalCount - $availableCount) / $totalCount) * 100 : 0 }}%"></div>
        </div>

        {{-- Badge Ringkasan 3 Sesi Jam Kerja Kampus --}}
        <div class="flex items-center gap-2 flex-wrap">
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-medium bg-slate-50 text-slate-700 border border-slate-200/80">
                <span class="text-slate-400">Pagi (07-12):</span>
                <span class="font-semibold text-emerald-700">Tersedia</span>
            </span>
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-medium bg-slate-50 text-slate-700 border border-slate-200/80">
                <span class="text-slate-400">Siang (12-16):</span>
                <span class="font-semibold text-rose-700">Sebagian Terisi</span>
            </span>
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-medium bg-slate-50 text-slate-700 border border-slate-200/80">
                <span class="text-slate-400">Sore (16-20):</span>
                <span class="font-semibold text-emerald-700">Tersedia</span>
            </span>
        </div>
    </div>
@else
    {{-- 
      ==========================================================================
      MODE 2: INTERACTIVE 30-MINUTE SESSION SELECTOR (Mode Formulir Reservasi)
      ==========================================================================
      Mengizinkan pemohon memilih slot waktu mulai secara visual.
      - State Alpine.js:
        * activeSession : Tab sesi yang sedang aktif ('pagi', 'siang', 'sore').
        * selectedStart : Jam mulai terpilih.
        * slotsPagi, slotsSiang, slotsSore : Array slot waktu 30 menitan.
        * bookedSlots : Slot yang sudah dipesan (otomatis dinonaktifkan / disabled).
    --}}
    <div x-data="{
        activeSession: 'pagi',
        selectedStart: '09:00',
        selectedEnd: '12:00',
        slotsPagi: ['07:00','07:30','08:00','08:30','09:00','09:30','10:00','10:30','11:00','11:30'],
        slotsSiang: ['12:00','12:30','13:00','13:30','14:00','14:30','15:00','15:30'],
        slotsSore: ['16:00','16:30','17:00','17:30','18:00','18:30','19:00','19:30','20:00'],
        bookedSlots: ['09:00','09:30','10:00','10:30','11:00','11:30','15:30','16:00']
    }" class="bg-slate-50 p-4 rounded-xl border border-slate-200">
        {{-- Header Tab Sesi --}}
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Pemilih Sesi & Slot Waktu (30 Menit)</span>
            <div class="flex items-center gap-1 bg-white p-1 rounded-lg border border-slate-200 text-xs">
                <button type="button" @click="activeSession = 'pagi'" :class="activeSession === 'pagi' ? 'bg-slate-900 text-white font-semibold' : 'text-slate-600 hover:text-slate-900'" class="px-2.5 py-1 rounded-md transition cursor-pointer">
                    Pagi (07-12)
                </button>
                <button type="button" @click="activeSession = 'siang'" :class="activeSession === 'siang' ? 'bg-slate-900 text-white font-semibold' : 'text-slate-600 hover:text-slate-900'" class="px-2.5 py-1 rounded-md transition cursor-pointer">
                    Siang (12-16)
                </button>
                <button type="button" @click="activeSession = 'sore'" :class="activeSession === 'sore' ? 'bg-slate-900 text-white font-semibold' : 'text-slate-600 hover:text-slate-900'" class="px-2.5 py-1 rounded-md transition cursor-pointer">
                    Sore (16-20)
                </button>
            </div>
        </div>

        {{-- Sesi Pagi (07:00 - 11:30 WIB) --}}
        <div x-show="activeSession === 'pagi'" class="grid grid-cols-5 gap-2">
            <template x-for="slot in slotsPagi" :key="slot">
                <button type="button" 
                        @click="selectedStart = slot" 
                        :disabled="bookedSlots.includes(slot)"
                        :class="bookedSlots.includes(slot) ? 'bg-slate-200 text-slate-400 cursor-not-allowed border-slate-200' : (selectedStart === slot ? 'bg-slate-900 text-white font-bold border-slate-900 shadow-xs' : 'bg-white text-slate-800 border-slate-200 hover:border-slate-400 cursor-pointer')"
                        class="py-2 rounded-lg text-xs font-medium border text-center transition">
                    <span x-text="slot"></span>
                </button>
            </template>
        </div>

        {{-- Sesi Siang (12:00 - 15:30 WIB) --}}
        <div x-show="activeSession === 'siang'" class="grid grid-cols-4 gap-2">
            <template x-for="slot in slotsSiang" :key="slot">
                <button type="button" 
                        @click="selectedStart = slot" 
                        :disabled="bookedSlots.includes(slot)"
                        :class="bookedSlots.includes(slot) ? 'bg-slate-200 text-slate-400 cursor-not-allowed border-slate-200' : (selectedStart === slot ? 'bg-slate-900 text-white font-bold border-slate-900 shadow-xs' : 'bg-white text-slate-800 border-slate-200 hover:border-slate-400 cursor-pointer')"
                        class="py-2 rounded-lg text-xs font-medium border text-center transition">
                    <span x-text="slot"></span>
                </button>
            </template>
        </div>

        {{-- Sesi Sore (16:00 - 20:00 WIB) --}}
        <div x-show="activeSession === 'sore'" class="grid grid-cols-5 gap-2">
            <template x-for="slot in slotsSore" :key="slot">
                <button type="button" 
                        @click="selectedStart = slot" 
                        :disabled="bookedSlots.includes(slot)"
                        :class="bookedSlots.includes(slot) ? 'bg-slate-200 text-slate-400 cursor-not-allowed border-slate-200' : (selectedStart === slot ? 'bg-slate-900 text-white font-bold border-slate-900 shadow-xs' : 'bg-white text-slate-800 border-slate-200 hover:border-slate-400 cursor-pointer')"
                        class="py-2 rounded-lg text-xs font-medium border text-center transition">
                    <span x-text="slot"></span>
                </button>
            </template>
        </div>

        {{-- Visual Feedback Waktu Terpilih --}}
        <div class="mt-3 pt-3 border-t border-slate-200/80 flex items-center justify-between text-xs text-slate-600">
            <span class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Waktu Mulai: <strong class="text-slate-900" x-text="selectedStart"></strong> WIB</span>
            </span>
            <span class="text-[11px] text-slate-500">Rentang minimal 30 menit sesuai aturan operasional</span>
        </div>
    </div>
@endif
