{{-- 
  NAMA FILE      : stat-card.blade.php
  FUNGSIONALITAS : Kartu Metrik / KPI CAVA
  DESKRIPSI      : Menampilkan metrik ringkasan (jumlah antrean, laporan kerusakan, utilitas ruang) dengan sudut rounded-xl dan corner decorator.
--}}

@props([
    'title' => 'Metrik CAVA',
    'value' => '0',
    'subtitle' => '',
    'badgeCode' => 'KPI-01',
    'icon' => 'analytics',
    'tagText' => '',
    'tagType' => 'primary',
    'footerText' => '',
    'variant' => 'default'
])

@php
    $colorClasses = [
        'default' => [
            'value' => 'text-primary',
            'bgDecor' => 'bg-surface-container/60',
            'tag' => 'bg-surface-container-highest text-primary font-bold',
        ],
        'error' => [
            'value' => 'text-error',
            'bgDecor' => 'bg-error-container/40',
            'tag' => 'bg-error-container text-on-error-container font-bold',
        ],
        'secondary' => [
            'value' => 'text-secondary',
            'bgDecor' => 'bg-secondary-fixed/40',
            'tag' => 'bg-secondary-container text-on-secondary-container font-bold',
        ]
    ];

    $cfg = $colorClasses[$variant] ?? $colorClasses['default'];
@endphp

<div class="bg-surface-container-lowest rounded-xl p-space-lg shadow-sm flex flex-col justify-between relative overflow-hidden">
    <div class="absolute right-0 top-0 w-24 h-24 {{ $cfg['bgDecor'] }} rounded-bl-full pointer-events-none"></div>
    <div>
        <div class="flex items-center justify-between">
            <span class="font-label-sm text-label-sm font-semibold uppercase tracking-wider text-on-surface-variant flex items-center gap-1">
                <span class="material-symbols-outlined text-[16px] {{ $cfg['value'] }}">{{ $icon }}</span>
                {{ $title }}
            </span>
            <span class="px-space-sm py-0.5 rounded-full font-label-sm text-label-sm bg-surface-container text-on-surface font-data-mono">{{ $badgeCode }}</span>
        </div>
        <div class="mt-space-md flex items-baseline gap-space-sm">
            <span class="font-display-lg text-display-lg {{ $cfg['value'] }} tracking-tight font-bold">{{ $value }}</span>
            <span class="font-headline-sm text-headline-sm text-on-surface">{{ $subtitle }}</span>
        </div>
    </div>
    <div class="mt-space-lg pt-space-md flex items-center justify-between border-t border-outline-variant/30">
        @if($tagText)
            <span class="inline-flex items-center gap-1 px-space-md py-1 rounded font-label-md text-label-md {{ $cfg['tag'] }}">
                {{ $tagText }}
            </span>
        @endif
        @if($footerText)
            <span class="font-body-sm text-body-sm text-on-surface-variant">{{ $footerText }}</span>
        @endif
    </div>
</div>
