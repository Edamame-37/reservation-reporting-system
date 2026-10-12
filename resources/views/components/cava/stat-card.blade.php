{{-- 
====================================================================================================
SISTEM INFORMASI RESERVASI & PELAPORAN FASILITAS KAMPUS (CAVA)
MODUL 7: KONSOL ADMIN - DASBOR EKSEKUTIF & STATISTIK UTAMA (ADM-04 / US-17)
BERKAS: resources/views/components/cava/stat-card.blade.php
====================================================================================================

DESKRIPSI BERKAS:
Komponen Blade 'stat-card' (Bento UI Metric Card) berfungsi menyajikan kartu indikator performa utama (KPI)
secara ringkas, berkelas, dan mudah dicerna pada seluruh antarmuka dasbor sistem CAVA, khususnya
Dasbor Eksekutif Super Admin (ADM-04).

PERAN & ARSITEKTUR DALAM SISTEM CAVA:
1. Standardisasi Kartu Metrik Eksekutif:
   Menyeragamkan presentasi angka statistik penting seperti:
   - Antrean Verifikasi Sivitas Mandiri (varian: tertiary/amber).
   - Tingkat Okupansi Ruangan Bulanan (varian: secondary/emerald).
   - Inventaris Fasilitas Aktif & Kapasitas Kursi (varian: primary/blue).
   - Tiket Pengaduan Kerusakan & Kepatuhan SLA (varian: error/rose).
2. Sistem Pewarnaan Semantik (Bento Color Schemes):
   Mendukung konfigurasi palet dinamis ('default', 'primary', 'secondary', 'tertiary', 'error')
   yang memetakan warna teks angka, latar belakang ikon Material Symbols, dan pill badge kaki kartu.
3. Desain Mikro-Interaksi Elegan:
   Menerapkan sudut membulat lebar (rounded-2xl), border halus (border-slate-200/80), bayangan elevasi
   ringan (shadow-2xs ke hover:shadow-xs), dan pembagian vertikal proporsional (flex-col justify-between).
====================================================================================================
--}}

{{--
  PENJELASAN PROPS KOMPONEN (@props):
  - 'title'       : Judul label metrik di bagian atas kartu (contoh: "VERIFIKASI PENDING").
  - 'value'       : Nilai angka utama metrik KPI (contoh: "14", "82.5%", "35 Ruang").
  - 'subtitle'    : Keterangan tambahan atau satuan di samping nilai angka (opsional).
  - 'badgeCode'   : Kode identifier alternatif untuk badge.
  - 'icon'        : Nama ikon Material Symbols Outlined (contoh: 'how_to_reg', 'calendar_month', 'handyman').
  - 'tagText'     : Teks label pill badge di bagian footer kartu (contoh: "Prioritas Hari Ini", "+12% MoM").
  - 'footerBadge' : Alias cadangan jika tagText kosong.
  - 'footerText'  : Catatan kaki penjelasan konteks di sebelah kanan pill badge.
  - 'variant'     : Pilihan palet tema warna ('default', 'primary', 'secondary', 'tertiary', 'error').
--}}
@props([
    'title'       => 'Metrik CAVA',
    'value'       => '0',
    'subtitle'    => '',
    'badgeCode'   => '',
    'icon'        => 'analytics',
    'tagText'     => '',
    'footerBadge' => '',
    'footerText'  => '',
    'variant'     => 'default'
])

{{--
  PENJELASAN KONFIGURASI TEMA WARNA SEMANTIK ($colorConfig):
  Memetakan setiap varian nama ke pasangan kelas utilitas Tailwind CSS:
  - 'text'   : Warna tipografi angka nilai utama.
  - 'bgIcon' : Warna latar dan warna ikon Material Symbols.
  - 'tag'    : Gaya border, teks, dan latar pill badge tren/status.
--}}
@php
    $colorConfig = [
        'default' => [
            'text'   => 'text-slate-900',
            'bgIcon' => 'bg-slate-100 text-slate-700',
            'tag'    => 'bg-slate-100 text-slate-700 border border-slate-200/80 font-semibold',
        ],
        'primary' => [
            'text'   => 'text-slate-900',
            'bgIcon' => 'bg-blue-50 text-blue-800',
            'tag'    => 'bg-blue-50 text-blue-700 border border-blue-200/80 font-semibold',
        ],
        'error' => [
            'text'   => 'text-rose-600',
            'bgIcon' => 'bg-rose-50 text-rose-600',
            'tag'    => 'bg-rose-50 text-rose-700 border border-rose-200/80 font-semibold',
        ],
        'tertiary' => [
            'text'   => 'text-amber-700',
            'bgIcon' => 'bg-amber-50 text-amber-700',
            'tag'    => 'bg-amber-50 text-amber-800 border border-amber-200/80 font-semibold',
        ],
        'secondary' => [
            'text'   => 'text-emerald-700',
            'bgIcon' => 'bg-emerald-50 text-emerald-700',
            'tag'    => 'bg-emerald-50 text-emerald-800 border border-emerald-200/80 font-semibold',
        ]
    ];

    // Memilih konfigurasi warna sesuai varian yang dioper (fallback ke 'default')
    $cfg = $colorConfig[$variant] ?? $colorConfig['default'];
    // Menetapkan teks badge (prioritas tagText, cadangan footerBadge)
    $tag = $tagText ?: $footerBadge;
@endphp

{{--
  WADAH UTAMA KARTU METRIK BENTO UI:
  - 'bg-white rounded-2xl border border-slate-200/80 p-5': Kartu putih bersih dengan sudut melengkung 16px.
  - 'shadow-2xs hover:shadow-xs transition-shadow': Efek elevasi halus saat kursor melintas.
  - 'flex flex-col justify-between': Memisahkan area konten angka dan footer secara vertikal.
--}}
<div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-2xs hover:shadow-xs transition-shadow flex flex-col justify-between">
    {{-- AREA ATAS: LABEL JUDUL, IKON MATERIAL, DAN NILAI UTAMA --}}
    <div>
        {{-- Header Kartu: Label Judul dan Ikon --}}
        <div class="flex items-center justify-between gap-2 mb-3">
            <span class="text-xs font-semibold uppercase tracking-wider text-slate-500">{{ $title }}</span>
            <div class="w-8 h-8 rounded-lg {{ $cfg['bgIcon'] }} flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-[18px]">{{ $icon }}</span>
            </div>
        </div>

        {{-- Nilai Angka Utama dan Subtitle Satuan --}}
        <div class="flex items-baseline gap-2 flex-wrap">
            <span class="text-3xl font-bold tracking-tight {{ $cfg['text'] }}">{{ $value }}</span>
            @if($subtitle)
                <span class="text-xs text-slate-500 font-medium">{{ $subtitle }}</span>
            @endif
        </div>
    </div>

    {{-- AREA BAWAH (FOOTER): PILL BADGE TREN & CATATAN PENJELASAN KONTEKS --}}
    @if($tag || $footerText)
        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between gap-2 text-xs">
            {{-- Pill Badge Status / Indikator Tren --}}
            @if($tag)
                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[11px] whitespace-nowrap shrink-0 shadow-2xs {{ $cfg['tag'] }}">
                    {{ $tag }}
                </span>
            @endif

            {{-- Teks Catatan Kaki Penjelas --}}
            @if($footerText)
                <span class="text-[11px] text-slate-400 font-medium text-right truncate" title="{{ $footerText }}">{{ $footerText }}</span>
            @endif
        </div>
    @endif
</div>
