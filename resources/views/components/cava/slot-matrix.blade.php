{{-- 
  NAMA FILE      : slot-matrix.blade.php
  FUNGSIONALITAS : Grid Matriks Slot Waktu 30 Menit (30-Minute Interval Time Matrix)
  DESKRIPSI      : Menampilkan 27 interval waktu dari 07:00 hingga 20:00 WIB untuk modul UR01, SFR03, dan SFR04.
--}}

@props([
    'slots' => [],
    'interactive' => false
])

@php
    $defaultSlots = [
        ['time' => '07:00', 'status' => 'available'],
        ['time' => '07:30', 'status' => 'available'],
        ['time' => '08:00', 'status' => 'available'],
        ['time' => '08:30', 'status' => 'available'],
        ['time' => '09:00', 'status' => 'booked'],
        ['time' => '09:30', 'status' => 'booked'],
        ['time' => '10:00', 'status' => 'booked'],
        ['time' => '10:30', 'status' => 'booked'],
        ['time' => '11:00', 'status' => 'booked'],
        ['time' => '11:30', 'status' => 'booked'],
        ['time' => '12:00', 'status' => 'booked'],
        ['time' => '12:30', 'status' => 'booked'],
        ['time' => '13:00', 'status' => 'available'],
        ['time' => '13:30', 'status' => 'available'],
        ['time' => '14:00', 'status' => 'available'],
        ['time' => '14:30', 'status' => 'available'],
        ['time' => '15:00', 'status' => 'available'],
        ['time' => '15:30', 'status' => 'booked'],
        ['time' => '16:00', 'status' => 'booked'],
        ['time' => '16:30', 'status' => 'booked'],
        ['time' => '17:00', 'status' => 'booked'],
        ['time' => '17:30', 'status' => 'booked'],
        ['time' => '18:00', 'status' => 'available'],
        ['time' => '18:30', 'status' => 'available'],
        ['time' => '19:00', 'status' => 'available'],
        ['time' => '19:30', 'status' => 'available'],
        ['time' => '20:00', 'status' => 'available'],
    ];

    $items = !empty($slots) ? $slots : $defaultSlots;
@endphp

<div>
    <div class="flex items-center justify-between mb-space-xs">
        <span class="font-label-sm text-label-sm uppercase tracking-wider text-on-surface-variant font-bold">Ketersediaan Slot Sesi (Interval 30 Menit)</span>
        <span class="font-data-mono text-[11px] text-on-surface-variant">MODE PRIVASI: DATA RESERVASI DIRAHASIAKAN</span>
    </div>
    <div class="grid grid-cols-3 sm:grid-cols-6 md:grid-cols-9 lg:grid-cols-14 xl:grid-cols-27 gap-1">
        @foreach($items as $s)
            @if($s['status'] === 'available')
                <div class="flex flex-col items-center justify-center p-1 rounded bg-secondary-fixed/30 text-on-secondary-fixed text-center">
                    <span class="font-data-mono text-[10px] font-bold">{{ $s['time'] }}</span>
                    <span class="text-[9px] font-semibold text-secondary">Tersedia</span>
                </div>
            @elseif($s['status'] === 'selected')
                <div class="flex flex-col items-center justify-center p-1 rounded bg-primary text-on-primary font-bold text-center shadow-sm">
                    <span class="font-data-mono text-[10px]">{{ $s['time'] }}</span>
                    <span class="text-[9px] font-semibold text-primary-fixed">Terpilih</span>
                </div>
            @else
                <div class="flex flex-col items-center justify-center p-1 rounded bg-error-container text-on-error-container text-center">
                    <span class="font-data-mono text-[10px] font-bold">{{ $s['time'] }}</span>
                    <span class="text-[9px] font-semibold text-error">Tidak Tersedia</span>
                </div>
            @endif
        @endforeach
    </div>
</div>
