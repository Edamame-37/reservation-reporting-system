{{-- 
  NAMA FILE      : status-badge.blade.php
  FUNGSIONALITAS : Badge Status CAVA Seragam (Status Indicator Component)
  DESKRIPSI      : Menampilkan pill status permohonan reservasi atau laporan kerusakan dengan styling token CAVA yang konsisten.
--}}

@props([
    'status' => 'pending',
    'label' => null
])

@php
    $configs = [
        'pending' => [
            'class' => 'bg-surface-container-highest text-primary',
            'dot' => 'bg-primary animate-ping',
            'icon' => 'pending_actions',
            'text' => $label ?? 'Menunggu Konfirmasi'
        ],
        'approved' => [
            'class' => 'bg-secondary-container text-on-secondary-container font-semibold',
            'dot' => 'bg-secondary',
            'icon' => 'check_circle',
            'text' => $label ?? 'Disetujui Petugas'
        ],
        'rejected' => [
            'class' => 'bg-error-container text-on-error-container font-semibold',
            'dot' => 'bg-error',
            'icon' => 'cancel',
            'text' => $label ?? 'Ditolak'
        ],
        'cancelled' => [
            'class' => 'bg-surface-container-highest text-on-surface-variant',
            'dot' => 'bg-outline',
            'icon' => 'block',
            'text' => $label ?? 'Dibatalkan Pengguna'
        ],
        'active' => [
            'class' => 'bg-secondary-fixed text-on-secondary-fixed font-semibold',
            'dot' => 'bg-secondary animate-pulse',
            'icon' => 'verified',
            'text' => $label ?? 'Fasilitas Aktif'
        ],
        'locked' => [
            'class' => 'bg-error-container text-on-error-container font-semibold',
            'dot' => 'bg-error',
            'icon' => 'lock',
            'text' => $label ?? 'Fasilitas Terkunci (Perbaikan)'
        ],
    ];

    $badge = $configs[$status] ?? $configs['pending'];
@endphp

<span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full font-label-sm text-label-sm {{ $badge['class'] }}">
    <span class="w-1.5 h-1.5 rounded-full {{ $badge['dot'] }}"></span>
    <span>{{ $badge['text'] }}</span>
</span>
