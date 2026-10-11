{{-- 
  NAMA FILE      : stat-card.blade.php
  FUNGSIONALITAS : Kartu Metrik / KPI CAVA (Modern Campus Minimalist Metric Card)
  DESKRIPSI      : Menampilkan kartu ringkasan metrik statistik dengan tipografi presisi, ikon berlatar lembut, dan indikator tren.
  CARA KERJA     : Menerima props title, value, subtitle, badgeCode, icon, tagText, footerBadge, footerText, dan variant.
--}}

@props([
    'title' => 'Metrik CAVA',
    'value' => '0',
    'subtitle' => '',
    'badgeCode' => '',
    'icon' => 'analytics',
    'tagText' => '',
    'footerBadge' => '',
    'footerText' => '',
    'variant' => 'default'
])

@php
    $colorConfig = [
        'default' => [
            'text' => 'text-slate-900',
            'bgIcon' => 'bg-slate-100 text-slate-700',
            'tag' => 'bg-slate-100 text-slate-700 border border-slate-200/80 font-semibold',
        ],
        'primary' => [
            'text' => 'text-slate-900',
            'bgIcon' => 'bg-blue-50 text-blue-800',
            'tag' => 'bg-blue-50 text-blue-700 border border-blue-200/80 font-semibold',
        ],
        'error' => [
            'text' => 'text-rose-600',
            'bgIcon' => 'bg-rose-50 text-rose-600',
            'tag' => 'bg-rose-50 text-rose-700 border border-rose-200/80 font-semibold',
        ],
        'tertiary' => [
            'text' => 'text-amber-700',
            'bgIcon' => 'bg-amber-50 text-amber-700',
            'tag' => 'bg-amber-50 text-amber-800 border border-amber-200/80 font-semibold',
        ],
        'secondary' => [
            'text' => 'text-emerald-700',
            'bgIcon' => 'bg-emerald-50 text-emerald-700',
            'tag' => 'bg-emerald-50 text-emerald-800 border border-emerald-200/80 font-semibold',
        ]
    ];

    $cfg = $colorConfig[$variant] ?? $colorConfig['default'];
    $tag = $tagText ?: $footerBadge;
@endphp

<!-- 
  ELEMEN       : Modern Campus Minimalist Stat Card
  KEGUNAAN     : Menampilkan ringkasan metrik KPI pada dasbor dengan hierarki visual yang jelas.
-->
<div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs hover:shadow-xs transition-shadow flex flex-col justify-between">
    <div>
        <div class="flex items-center justify-between gap-2 mb-3">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $title }}</span>
            <div class="w-8 h-8 rounded-lg {{ $cfg['bgIcon'] }} flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[18px]">{{ $icon }}</span>
            </div>
        </div>
        <div class="flex items-baseline gap-2 flex-wrap">
            <span class="text-3xl font-bold tracking-tight {{ $cfg['text'] }}">{{ $value }}</span>
            @if($subtitle)
                <span class="text-xs text-slate-500 font-medium">{{ $subtitle }}</span>
            @endif
        </div>
    </div>

    @if($tag || $footerText)
        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between gap-2 text-xs">
            @if($tag)
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] whitespace-nowrap shrink-0 shadow-2xs {{ $cfg['tag'] }}">
                    {{ $tag }}
                </span>
            @endif
            @if($footerText)
                <span class="text-[11px] text-slate-400 font-medium text-right truncate" title="{{ $footerText }}">{{ $footerText }}</span>
            @endif
        </div>
    @endif
</div>
