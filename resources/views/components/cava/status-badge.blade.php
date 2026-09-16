{{-- 
  NAMA FILE      : status-badge.blade.php
  FUNGSIONALITAS : Badge Status CAVA Seragam (Semantic Status Pill Component)
  DESKRIPSI      : Menampilkan pill status permohonan reservasi atau laporan kerusakan dengan palet warna semantik yang elegan dan konsisten.
  CARA KERJA     : Menerima props 'status' (pending|approved|rejected|cancelled|active|locked|baru|diproses|selesai) dan 'label' opsional.
--}}

@props([
    'status' => 'pending',
    'label' => null
])

@php
    $configs = [
        'pending' => [
            'class' => 'bg-amber-50 text-amber-800 border border-amber-200/80 font-medium',
            'dot' => 'bg-amber-500 animate-pulse',
            'text' => $label ?? 'Menunggu Konfirmasi'
        ],
        'approved' => [
            'class' => 'bg-emerald-50 text-emerald-800 border border-emerald-200/80 font-semibold',
            'dot' => 'bg-emerald-600',
            'text' => $label ?? 'Disetujui Petugas'
        ],
        'rejected' => [
            'class' => 'bg-rose-50 text-rose-700 border border-rose-200/80 font-medium',
            'dot' => 'bg-rose-500',
            'text' => $label ?? 'Ditolak'
        ],
        'cancelled' => [
            'class' => 'bg-slate-100 text-slate-600 border border-slate-200 font-medium',
            'dot' => 'bg-slate-400',
            'text' => $label ?? 'Dibatalkan Pengguna'
        ],
        'active' => [
            'class' => 'bg-emerald-50 text-emerald-800 border border-emerald-200/80 font-semibold',
            'dot' => 'bg-emerald-500 animate-pulse',
            'text' => $label ?? 'Fasilitas Aktif'
        ],
        'locked' => [
            'class' => 'bg-rose-50 text-rose-700 border border-rose-200/80 font-semibold',
            'dot' => 'bg-rose-600',
            'text' => $label ?? 'Dalam Perbaikan (Terkunci)'
        ],
        'baru' => [
            'class' => 'bg-amber-50 text-amber-800 border border-amber-200/80 font-medium',
            'dot' => 'bg-amber-500 animate-pulse',
            'text' => $label ?? 'Laporan Baru'
        ],
        'diproses' => [
            'class' => 'bg-sky-50 text-sky-800 border border-sky-200/80 font-semibold',
            'dot' => 'bg-sky-500 animate-pulse',
            'text' => $label ?? 'Sedang Diproses'
        ],
        'selesai' => [
            'class' => 'bg-emerald-50 text-emerald-800 border border-emerald-200/80 font-semibold',
            'dot' => 'bg-emerald-600',
            'text' => $label ?? 'Selesai Ditangani'
        ]
    ];

    $badge = $configs[$status] ?? $configs['pending'];
@endphp

<!-- 
  ELEMEN       : Modern Semantic Status Badge
  KEGUNAAN     : Indikator status visual yang mudah dibedakan dengan kontras yang nyaman bagi mata.
-->
<span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs {{ $badge['class'] }}">
    <span class="w-1.5 h-1.5 rounded-full {{ $badge['dot'] }}"></span>
    <span>{{ $badge['text'] }}</span>
</span>
