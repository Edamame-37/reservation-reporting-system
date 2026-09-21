{{-- 
  NAMA FILE      : slot-matrix.blade.php
  FUNGSIONALITAS : Visualisasi Okupansi & Pemilih Slot Waktu 30 Menit (07:00 - 20:00 WIB)
  DESKRIPSI      : Menghilangkan kisi-kisi 27 kotak mentah yang padat. Menyajikan visual progress keterisian yang elegan dan pemilih sesi waktu (Pagi/Siang/Sore) yang interaktif.
  CARA KERJA     : Mendukung mode ringkas (interactive=false) untuk kartu fasilitas dan mode interaktif (interactive=true) untuk form pemesanan dengan Alpine.js.
--}}

@props([
    'slots' => [],
    'interactive' => false,
    'availableCount' => 14,
    'totalCount' => 27
])

@if(!$interactive)
    <!-- 
      ELEMEN       : Minimalist Availability Bar & Session Pills (Mode Ringkasan)
      KEGUNAAN     : Menyajikan visual keterisian ruang secara elegan tanpa membanjiri layar dengan puluhan tombol.
    -->
    <div class="pt-2">
        <div class="flex items-center justify-between text-xs text-slate-600 mb-1.5">
            <span class="font-medium flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Okupansi Hari Ini: <strong>{{ $availableCount }} dari {{ $totalCount }}</strong> Slot Bebas</span>
            </span>
            <span class="text-[11px] font-mono text-slate-400">07:00 - 20:00 WIB</span>
        </div>

        {{-- Visual Progress Bar --}}
        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden flex mb-2.5">
            <div class="bg-emerald-500 h-full transition-all" style="width: {{ ($availableCount / $totalCount) * 100 }}%"></div>
            <div class="bg-rose-400 h-full transition-all" style="width: {{ (($totalCount - $availableCount) / $totalCount) * 100 }}%"></div>
        </div>

        {{-- Session Badges --}}
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
    <!-- 
      ELEMEN       : Interactive 30-Minute Session Selector (Mode Form Reservasi)
      KEGUNAAN     : Memungkinkan pengguna memilih rentang waktu reservasi dengan visual interaktif dan proteksi bentrok.
      CARA KERJA   : Memanfaatkan tab sesi Alpine.js (Pagi, Siang, Sore) untuk pemilihan waktu yang bersih.
    -->
    <div x-data="{
        activeSession: 'pagi',
        selectedStart: '09:00',
        selectedEnd: '12:00',
        slotsPagi: ['07:00','07:30','08:00','08:30','09:00','09:30','10:00','10:30','11:00','11:30'],
        slotsSiang: ['12:00','12:30','13:00','13:30','14:00','14:30','15:00','15:30'],
        slotsSore: ['16:00','16:30','17:00','17:30','18:00','18:30','19:00','19:30','20:00'],
        bookedSlots: ['09:00','09:30','10:00','10:30','11:00','11:30','15:30','16:00']
    }" class="bg-slate-50 p-4 rounded-xl border border-slate-200">
        <div class="flex items-center justify-between mb-3">
            <span class="text-xs font-bold text-slate-800 uppercase tracking-wider">Pemilih Sesi & Slot Waktu (30 Menit)</span>
            <div class="flex items-center gap-1 bg-white p-1 rounded-lg border border-slate-200 text-xs">
                <button type="button" @click="activeSession = 'pagi'" :class="activeSession === 'pagi' ? 'bg-slate-900 text-white font-semibold' : 'text-slate-600 hover:text-slate-900'" class="px-2.5 py-1 rounded-md transition">
                    Pagi (07-12)
                </button>
                <button type="button" @click="activeSession = 'siang'" :class="activeSession === 'siang' ? 'bg-slate-900 text-white font-semibold' : 'text-slate-600 hover:text-slate-900'" class="px-2.5 py-1 rounded-md transition">
                    Siang (12-16)
                </button>
                <button type="button" @click="activeSession = 'sore'" :class="activeSession === 'sore' ? 'bg-slate-900 text-white font-semibold' : 'text-slate-600 hover:text-slate-900'" class="px-2.5 py-1 rounded-md transition">
                    Sore (16-20)
                </button>
            </div>
        </div>

        {{-- Sesi Pagi --}}
        <div x-show="activeSession === 'pagi'" class="grid grid-cols-5 gap-2">
            <template x-for="slot in slotsPagi" :key="slot">
                <button type="button" 
                        @click="selectedStart = slot" 
                        :disabled="bookedSlots.includes(slot)"
                        :class="bookedSlots.includes(slot) ? 'bg-slate-200 text-slate-400 cursor-not-allowed border-slate-200' : (selectedStart === slot ? 'bg-slate-900 text-white font-bold border-slate-900 shadow-xs' : 'bg-white text-slate-800 border-slate-200 hover:border-slate-400')"
                        class="py-2 rounded-lg text-xs font-medium border text-center transition">
                    <span x-text="slot"></span>
                </button>
            </template>
        </div>

        {{-- Sesi Siang --}}
        <div x-show="activeSession === 'siang'" class="grid grid-cols-4 gap-2">
            <template x-for="slot in slotsSiang" :key="slot">
                <button type="button" 
                        @click="selectedStart = slot" 
                        :disabled="bookedSlots.includes(slot)"
                        :class="bookedSlots.includes(slot) ? 'bg-slate-200 text-slate-400 cursor-not-allowed border-slate-200' : (selectedStart === slot ? 'bg-slate-900 text-white font-bold border-slate-900 shadow-xs' : 'bg-white text-slate-800 border-slate-200 hover:border-slate-400')"
                        class="py-2 rounded-lg text-xs font-medium border text-center transition">
                    <span x-text="slot"></span>
                </button>
            </template>
        </div>

        {{-- Sesi Sore --}}
        <div x-show="activeSession === 'sore'" class="grid grid-cols-5 gap-2">
            <template x-for="slot in slotsSore" :key="slot">
                <button type="button" 
                        @click="selectedStart = slot" 
                        :disabled="bookedSlots.includes(slot)"
                        :class="bookedSlots.includes(slot) ? 'bg-slate-200 text-slate-400 cursor-not-allowed border-slate-200' : (selectedStart === slot ? 'bg-slate-900 text-white font-bold border-slate-900 shadow-xs' : 'bg-white text-slate-800 border-slate-200 hover:border-slate-400')"
                        class="py-2 rounded-lg text-xs font-medium border text-center transition">
                    <span x-text="slot"></span>
                </button>
            </template>
        </div>

        {{-- Visual Feedback Rentang Terpilih --}}
        <div class="mt-3 pt-3 border-t border-slate-200/80 flex items-center justify-between text-xs text-slate-600">
            <span class="flex items-center gap-1.5">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span>Waktu Mulai: <strong class="text-slate-900" x-text="selectedStart"></strong> WIB</span>
            </span>
            <span class="text-[11px] text-slate-500">Rentang minimal 30 menit sesuai aturan operasional</span>
        </div>
    </div>
@endif
