{{-- 
  ====================================================================================================
  NAMA FILE      : slot-matrix.blade.php
  LOKASI         : resources/views/components/cava/slot-matrix.blade.php
  FUNGSIONALITAS : Komponen Reusable Blade untuk Visualisasi Matriks Slot Waktu 30 Menit & Pemilih Okupansi
  MODUL CAVA     : 10. Portal Pengguna: Formulir Pengajuan Reservasi Ruangan Mandiri (USR-01)
                   (Serta Digunakan Bersama di Modul 4 Portal Publik PUB-01)
  ====================================================================================================

  DESKRIPSI ARSITEKTUR & PERAN SISTEM:
  1. Komponen Dua Mode (Dual-Mode Component):
     - Mode Ringkasan (interactive = false):
       * Ditampilkan pada kartu katalog fasilitas publik (PUB-01).
       * Menggantikan puluhan kotak jadwal yang padat dengan sebuah progress bar proporsional keterisian ruang
         dan tiga lencana (badge) sesi kerja kampus (Pagi 07-12, Siang 12-16, Sore 16-20).
     - Mode Interaktif (interactive = true):
       * Ditampilkan pada formulir pengajuan reservasi ruangan mandiri sivitas (USR-01).
       * Menggunakan Alpine.js untuk membagi 26 slot 30-menitan ke dalam 3 tab sesi yang terorganisir rapi.
       * Memiliki proteksi bentrok: slot yang telah terisi (booked) otomatis dinonaktifkan (`disabled`)
         dan diberikan styling visual abu-abu redup (not-allowed).
  
  2. Standar Slot Waktu Kampus:
     - Jam operasional: 07:00 hingga 20:00 WIB (total 13 jam operasional).
     - Tiap jam dibagi menjadi 2 slot @ 30 menit, menghasilkan total 26 slot waktu baku per hari.
  
  3. Desain & Keterbacaan:
     - Mengikuti pedoman UI Modern Campus Minimalist dengan sudut membulat (rounded-xl),
       border halus (border-slate-200), dan animasi transisi responsif saat interaksi klik pengguna.
  ====================================================================================================
--}}

{{-- Inisialisasi Properti Komponen Blade (@props) dengan Nilai Bawaan --}}
@props([
    // Array kustom data slot waktu dari database (opsional)
    'slots'          => [],
    // Penentu mode operasional: false = mode bar ringkasan katalog; true = mode tombol interaktif form
    'interactive'    => false,
    // Jumlah slot yang masih bebas pada tanggal dan fasilitas yang dipilih (default: 14)
    'availableCount' => 14,
    // Total slot operasional standar kampus (13 jam * 2 slot/jam = 26 slot)
    'totalCount'     => 26
])

{{-- Percabangan Kondisional Berdasarkan Mode yang Dipilih --}}
@if(!$interactive)
    {{-- 
      ==================================================================================================
      MODE 1: MINIMALIST AVAILABILITY BAR & SESSION BADGES (Mode Ringkasan Katalog Publik)
      ==================================================================================================
      Menyajikan visual rasio okupansi ruang secara bersih dan ringkas tanpa membuat layar pengguna sesak.
    --}}
    <div class="pt-2">
        {{-- Header Keterangan Okupansi Hari Ini --}}
        <div class="flex items-center justify-between text-xs text-slate-600 mb-1.5">
            <span class="font-medium flex items-center gap-1.5">
                {{-- Indikator titik hijau melambangkan status ketersediaan aktif --}}
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Okupansi Hari Ini: <strong>{{ $availableCount }} dari {{ $totalCount }}</strong> Slot Bebas</span>
            </span>
            {{-- Label jam operasional kampus --}}
            <span class="text-[11px] font-mono text-slate-400">07:00 - 20:00 WIB</span>
        </div>

        {{-- 
          Progress Bar Dua Warna (Two-Tone Progress Bar):
          - Emerald-500 : Merepresentasikan persentase slot yang masih tersedia
          - Rose-400    : Merepresentasikan persentase slot yang telah terisi (booked)
          - Formula CSS : (available / total) * 100% untuk hijau, sisanya untuk merah muda
        --}}
        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden flex mb-2.5">
            <div class="bg-emerald-500 h-full transition-all" style="width: {{ $totalCount > 0 ? ($availableCount / $totalCount) * 100 : 0 }}%"></div>
            <div class="bg-rose-400 h-full transition-all" style="width: {{ $totalCount > 0 ? (($totalCount - $availableCount) / $totalCount) * 100 : 0 }}%"></div>
        </div>

        {{-- Lencana (Badge) Status Ringkasan 3 Sesi Jam Kerja Kampus --}}
        <div class="flex items-center gap-2 flex-wrap">
            {{-- Sesi Pagi --}}
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-medium bg-slate-50 text-slate-700 border border-slate-200/80">
                <span class="text-slate-400">Pagi (07-12):</span>
                <span class="font-semibold text-emerald-700">Tersedia</span>
            </span>
            {{-- Sesi Siang --}}
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-medium bg-slate-50 text-slate-700 border border-slate-200/80">
                <span class="text-slate-400">Siang (12-16):</span>
                <span class="font-semibold text-rose-700">Sebagian Terisi</span>
            </span>
            {{-- Sesi Sore --}}
            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md text-[11px] font-medium bg-slate-50 text-slate-700 border border-slate-200/80">
                <span class="text-slate-400">Sore (16-20):</span>
                <span class="font-semibold text-emerald-700">Tersedia</span>
            </span>
        </div>
    </div>
@else
    {{-- 
      ==================================================================================================
      MODE 2: INTERACTIVE 30-MINUTE SESSION SELECTOR (Mode Formulir Pengajuan Reservasi)
      ==================================================================================================
      Mengizinkan pemohon sivitas memilih slot waktu mulai secara visual dengan pembagian sesi.
      
      Struktur Reactive State Alpine.js (x-data):
      - activeSession : Menampung tab sesi yang sedang aktif ('pagi', 'siang', atau 'sore').
      - selectedStart : Jam mulai peminjaman yang dipilih pengguna (default: '09:00').
      - selectedEnd   : Jam selesai peminjaman (default: '12:00').
      - slotsPagi     : Array slot waktu 30 menitan sesi pagi (07:00 - 11:30).
      - slotsSiang    : Array slot waktu 30 menitan sesi siang (12:00 - 15:30).
      - slotsSore     : Array slot waktu 30 menitan sesi sore (16:00 - 20:00).
      - bookedSlots   : Array slot yang telah terisi reservasi disetujui (otomatis di-disable).
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
        
        {{-- Header Tab Navigasi Sesi --}}
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Pemilih Sesi & Slot Waktu (30 Menit)</span>
            
            {{-- Tombol Pilihan Tab Sesi (Pagi, Siang, Sore) --}}
            <div class="flex items-center gap-1 bg-white p-1 rounded-lg border border-slate-200 text-xs">
                {{-- Tab Pagi --}}
                <button type="button" @click="activeSession = 'pagi'" :class="activeSession === 'pagi' ? 'bg-slate-900 text-white font-semibold' : 'text-slate-600 hover:text-slate-900'" class="px-2.5 py-1 rounded-md transition cursor-pointer">
                    Pagi (07-12)
                </button>
                {{-- Tab Siang --}}
                <button type="button" @click="activeSession = 'siang'" :class="activeSession === 'siang' ? 'bg-slate-900 text-white font-semibold' : 'text-slate-600 hover:text-slate-900'" class="px-2.5 py-1 rounded-md transition cursor-pointer">
                    Siang (12-16)
                </button>
                {{-- Tab Sore --}}
                <button type="button" @click="activeSession = 'sore'" :class="activeSession === 'sore' ? 'bg-slate-900 text-white font-semibold' : 'text-slate-600 hover:text-slate-900'" class="px-2.5 py-1 rounded-md transition cursor-pointer">
                    Sore (16-20)
                </button>
            </div>
        </div>

        {{-- 
          Grid Slot Sesi Pagi (07:00 - 11:30 WIB):
          - x-show: Hanya tampil saat activeSession bernilai 'pagi'.
          - template x-for: Melakukan iterasi tombol slot waktu secara reaktif.
          - :disabled: Menonaktifkan tombol jika slot terdapat dalam array bookedSlots.
          - :class: Memberikan efek aktif bg-slate-900 jika dipilih, atau latar abu-abu jika disabled.
        --}}
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

        {{-- Grid Slot Sesi Siang (12:00 - 15:30 WIB) --}}
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

        {{-- Grid Slot Sesi Sore (16:00 - 20:00 WIB) --}}
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

        {{-- Visual Feedback Waktu Terpilih di Bagian Bawah --}}
        <div class="mt-3 pt-3 border-t border-slate-200/80 flex items-center justify-between text-xs text-slate-600">
            <span class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Waktu Mulai: <strong class="text-slate-900" x-text="selectedStart"></strong> WIB</span>
            </span>
            <span class="text-[11px] text-slate-500">Rentang minimal 30 menit sesuai aturan operasional</span>
        </div>
    </div>
@endif
