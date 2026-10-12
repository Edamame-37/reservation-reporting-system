{{-- 
  ==============================================================================
  NAMA FILE      : status-badge.blade.php
  LOKASI         : resources/views/components/cava/status-badge.blade.php
  MODUL CAVA     : Modul 11 (Riwayat Reservasi & Pembatalan), Modul 12 (Pelaporan Kerusakan),
                   Modul 4 (Katalog Publik), Modul 19 (Komponen UI Bersama)
  KODE FITUR     : USR-02, USR-05, PUB-01 (Komponen Badge Visual Status Semantik Universal CAVA)
  ==============================================================================
  DESKRIPSI:
  Komponen UI seragam (Reusable Blade Component) yang merender badge status berdesain kapsul
  (pill badge) dengan warna semantik terstandar, kontras tinggi yang ramah mata (WCAG compliant),
  indikator titik status (status dot) dengan animasi denyut (pulse), serta label teks deskriptif.

  KOMPATIBILITAS STATUS:
  - Reservasi Tiket    : 'pending', 'approved', 'rejected', 'cancelled'
  - Fasilitas & Aset   : 'active', 'locked' (dalam perbaikan)
  - Tiket Kerusakan    : 'baru', 'diproses', 'selesai'

  CARA PENGGUNAAN:
  <x-cava.status-badge status="approved" />
  <x-cava.status-badge status="pending" label="Menunggu Review Sarpras" />
  <x-cava.status-badge status="locked" />
  ==============================================================================
--}}

{{-- Inisialisasi Properti Komponen Blade (@props) --}}
@props([
    'status' => 'pending', // Kunci status yang menentukan tema warna (default: 'pending')
    'label'  => null       // Teks kustom opsional yang menimpa label bawaan
])

@php
    /**
     * Konfigurasi Palet Warna Semantik & Tipografi Badge:
     * - 'class' : Kelas Tailwind CSS untuk background warna pastel, warna teks, border, dan bobot font.
     * - 'dot'   : Titik bundar indikator status dengan warna kontras dan efek animasi 'animate-pulse'.
     * - 'text'  : Label deskripsi default jika props 'label' bernilai null.
     */
    $configs = [
        // 1. STATUS RESERVASI: MENUNGGU PERSETUJUAN PETUGAS
        'pending' => [
            'class' => 'bg-amber-50 text-amber-800 border border-amber-200/80 font-medium',
            'dot'   => 'bg-amber-500 animate-pulse',
            'text'  => $label ?? 'Menunggu Konfirmasi'
        ],
        // 2. STATUS RESERVASI: DISETUJUI & TIKET TERBIT
        'approved' => [
            'class' => 'bg-emerald-50 text-emerald-800 border border-emerald-200/80 font-semibold',
            'dot'   => 'bg-emerald-600',
            'text'  => $label ?? 'Disetujui Petugas'
        ],
        // 3. STATUS RESERVASI: DITOLAK PETUGAS
        'rejected' => [
            'class' => 'bg-rose-50 text-rose-700 border border-rose-200/80 font-medium',
            'dot'   => 'bg-rose-500',
            'text'  => $label ?? 'Ditolak'
        ],
        // 4. STATUS RESERVASI: DIBATALKAN MANDIRI OLEH PEMOHON (H-1)
        'cancelled' => [
            'class' => 'bg-slate-100 text-slate-600 border border-slate-200 font-medium',
            'dot'   => 'bg-slate-400',
            'text'  => $label ?? 'Dibatalkan Pengguna'
        ],
        // 5. STATUS FASILITAS: OPERASIONAL NORMAL / AKTIF
        'active' => [
            'class' => 'bg-emerald-50 text-emerald-800 border border-emerald-200/80 font-semibold',
            'dot'   => 'bg-emerald-500 animate-pulse',
            'text'  => $label ?? 'Fasilitas Aktif'
        ],
        // 6. STATUS FASILITAS: TERKUNCI KARENA DALAM PERBAIKAN
        'locked' => [
            'class' => 'bg-rose-50 text-rose-700 border border-rose-200/80 font-semibold',
            'dot'   => 'bg-rose-600',
            'text'  => $label ?? 'Dalam Perbaikan (Terkunci)'
        ],
        // 7. STATUS TIKET KERUSAKAN: LAPORAN BARU MASUK
        'baru' => [
            'class' => 'bg-amber-50 text-amber-800 border border-amber-200/80 font-medium',
            'dot'   => 'bg-amber-500 animate-pulse',
            'text'  => $label ?? 'Laporan Baru'
        ],
        // 8. STATUS TIKET KERUSAKAN: SEDANG DITANGANI TEKNISI SARPRAS
        'diproses' => [
            'class' => 'bg-sky-50 text-sky-800 border border-sky-200/80 font-semibold',
            'dot'   => 'bg-sky-500 animate-pulse',
            'text'  => $label ?? 'Sedang Diproses'
        ],
        // 9. STATUS TIKET KERUSAKAN: PERBAIKAN SELESAI
        'selesai' => [
            'class' => 'bg-emerald-50 text-emerald-800 border border-emerald-200/80 font-semibold',
            'dot'   => 'bg-emerald-600',
            'text'  => $label ?? 'Selesai Ditangani'
        ]
    ];

    // Gunakan konfigurasi status yang cocok; fallback ke 'pending' jika status tidak dikenali
    $badge = $configs[$status] ?? $configs['pending'];
@endphp

{{-- STRUKTUR VISUAL BADGE PILL --}}
<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs {{ $badge['class'] }}">
    {{-- Titik Status Berwarna (Status Dot) --}}
    <span class="w-1.5 h-1.5 rounded-full {{ $badge['dot'] }}"></span>
    {{-- Teks Label Status --}}
    <span>{{ $badge['text'] }}</span>
</span>
