/**
 * NAMA FILE    : tailwind.config.js
 * LOKASI       : tailwind.config.js
 * FUNGSI       : Konfigurasi Design System & Utility Styling Engine (Tailwind CSS)
 * DESKRIPSI    : Mengonfigurasi token desain kustom sistem CAVA (Campus Venue Access)
 *                berdasarkan spesifikasi Material Design 3 / Bento UI Modern.
 *                Mendefinisikan palet warna institusional, skala tipografi Inter,
 *                token jarak (spacing), mode gelap, dan plugin form.
 * CARA KERJA   : Saat proses build (`npm run build` atau dev `npm run dev`):
 *                1. Compiler Tailwind memindai seluruh file yang terdaftar di properti 'content'.
 *                2. Mengekstrak class utility yang digunakan dalam template Blade.
 *                3. Menerapkan token ekstensi tema (theme.extend) ke dalam utility class CSS akhir.
 *                4. Melakukan tree-shaking (purging) untuk menghapus class yang tidak terpakai
 *                   sehingga ukuran bundle CSS tetap minimalis dan performan.
 */

import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    // =========================================================================================
    // 1. STRATEGI MODE GELAP (DARK MODE)
    // =========================================================================================
    // SYNTAX     : darkMode: 'class'
    // CARA KERJA : Mode gelap diaktifkan secara manual melalui class CSS 'dark' pada elemen root <html>,
    //              bukan otomatis mengikuti preferensi OS (@media (prefers-color-scheme)).
    darkMode: 'class',

    // =========================================================================================
    // 2. PEMINDAIAN KONTEN (CONTENT SCANNING & PURGING)
    // =========================================================================================
    // SYNTAX     : content: [ ... ]
    // CARA KERJA : Array path file yang dipindai compiler untuk mendeteksi class Tailwind.
    //              Hanya class yang tertulis dalam file-file ini yang akan di-generate ke bundle CSS.
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php', // Template paginasi Laravel
        './storage/framework/views/*.php',                                                   // Kompilasi cache Blade
        './resources/views/**/*.blade.php',                                                  // Seluruh view & komponen Blade aplikasi
    ],

    // =========================================================================================
    // 3. EKSTENSI TEMA (THEME EXTENSION & DESIGN TOKENS)
    // =========================================================================================
    theme: {
        extend: {
            // ---------------------------------------------------------------------------------
            // A. PALET WARNA SEMANTIK (MATERIAL 3 / BENTO UI PALETTE)
            // ---------------------------------------------------------------------------------
            // Menggunakan sistem penamaan token Material Design 3 (surface, on-surface, container variants):
            colors: {
                // Lapisan Latar & Permukaan (Surface Layers)
                'surface': '#f8f9ff',
                'surface-dim': '#cbdbf5',
                'surface-bright': '#f8f9ff',
                'surface-variant': '#d3e4fe',
                'surface-tint': '#465f88',
                'surface-container-lowest': '#ffffff',
                'surface-container-low': '#eff4ff',
                'surface-container': '#e5eeff',
                'surface-container-high': '#dce9ff',
                'surface-container-highest': '#d3e4fe',

                // Konten Teks di Atas Permukaan (On-Surface Text)
                'on-surface': '#0b1c30',
                'on-surface-variant': '#44474e',
                'inverse-surface': '#213145',
                'inverse-on-surface': '#eaf1ff',
                'background': '#f8f9ff',
                'on-background': '#0b1c30',

                // Garis Batas & Border (Outlines)
                'outline': '#74777f',
                'outline-variant': '#c4c6cf',

                // Warna Primer: Biru Dongker Institusi CAVA (Trust, Formalitas Kampus)
                'primary': '#002046',
                'primary-container': '#1b365d',
                'on-primary': '#ffffff',
                'on-primary-container': '#87a0cd',
                'primary-fixed': '#d6e3ff',
                'primary-fixed-dim': '#aec7f7',
                'on-primary-fixed': '#001b3d',
                'on-primary-fixed-variant': '#2e476f',
                'inverse-primary': '#aec7f7',

                // Warna Sekunder: Hijau Zamrud Sarpras (Status Approved / Fasilitas Aktif)
                'secondary': '#006c4a',
                'secondary-container': '#82f5c1',
                'on-secondary': '#ffffff',
                'on-secondary-container': '#00714e',
                'secondary-fixed': '#85f8c4',
                'secondary-fixed-dim': '#68dba9',
                'on-secondary-fixed': '#002114',
                'on-secondary-fixed-variant': '#005137',

                // Warna Tersier: Cokelat/Kuning Amber (Status Pending / Menunggu Verifikasi)
                'tertiary': '#361900',
                'tertiary-container': '#552b00',
                'on-tertiary': '#ffffff',
                'on-tertiary-container': '#eb851c',
                'tertiary-fixed': '#ffdcc3',
                'tertiary-fixed-dim': '#ffb77d',
                'on-tertiary-fixed': '#2f1500',
                'on-tertiary-fixed-variant': '#6e3900',

                // Warna Error / Danger: Merah Rose (Status Ditolak / Tiket Kerusakan)
                'error': '#ba1a1a',
                'error-container': '#ffdad6',
                'on-error': '#ffffff',
                'on-error-container': '#93000a',
            },

            // ---------------------------------------------------------------------------------
            // B. SKALA JARAK LAYOUT (SPACING & GUTTER TOKENS)
            // ---------------------------------------------------------------------------------
            // Menjaga konsistensi ritme vertikal dan horizontal pada layout Bento:
            spacing: {
                'margin': '1rem',       // Margin standar mobile
                'margin-md': '1.5rem',  // Margin tablet
                'margin-lg': '2rem',    // Margin desktop
                'gutter': '1rem',       // Jarak antar kolom grid
                'gutter-lg': '1.5rem',  // Jarak grid lebar
                'space-xs': '0.25rem',  // Jarak mikro 4px
                'space-sm': '0.5rem',   // Jarak kecil 8px
                'space-md': '0.75rem',  // Jarak medium 12px
                'space-lg': '1rem',     // Jarak besar 16px
                'space-xl': '1.5rem',   // Jarak ekstra besar 24px
            },

            // ---------------------------------------------------------------------------------
            // C. TIPOGRAFI & FONT FAMILY
            // ---------------------------------------------------------------------------------
            // Menggabungkan font Inter modern dengan fallback sans bawaan sistem:
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
                'headline-sm': ['Inter', 'sans-serif'],
                'headline-md': ['Inter', 'sans-serif'],
                'headline-lg': ['Inter', 'sans-serif'],
                'display-lg': ['Inter', 'sans-serif'],
                'body-sm': ['Inter', 'sans-serif'],
                'body-md': ['Inter', 'sans-serif'],
                'body-lg': ['Inter', 'sans-serif'],
                'label-sm': ['Inter', 'sans-serif'],
                'label-md': ['Inter', 'sans-serif'],
                'label-lg': ['Inter', 'sans-serif'],
                'data-mono': ['Inter', 'monospace'],
            },

            // ---------------------------------------------------------------------------------
            // D. UKURAN TEKS KOMPOSIT (TYPOGRAPHY TOKENS: SIZE, LINE-HEIGHT, WEIGHT)
            // ---------------------------------------------------------------------------------
            // Sintaks tuple Tailwind: ['font-size', { lineHeight, letterSpacing, fontWeight }]
            fontSize: {
                'headline-sm': ['16px', { lineHeight: '24px', letterSpacing: '-0.005em', fontWeight: '600' }],
                'headline-md': ['20px', { lineHeight: '28px', letterSpacing: '-0.01em', fontWeight: '600' }],
                'headline-lg': ['24px', { lineHeight: '32px', letterSpacing: '-0.015em', fontWeight: '600' }],
                'display-lg': ['30px', { lineHeight: '36px', letterSpacing: '-0.02em', fontWeight: '700' }],
                'body-sm': ['13px', { lineHeight: '18px', fontWeight: '400' }],
                'body-md': ['14px', { lineHeight: '20px', fontWeight: '400' }],
                'body-lg': ['15px', { lineHeight: '22px', fontWeight: '400' }],
                'label-sm': ['11px', { lineHeight: '14px', letterSpacing: '0.04em', fontWeight: '600' }],
                'label-md': ['12px', { lineHeight: '16px', letterSpacing: '0.01em', fontWeight: '500' }],
                'label-lg': ['13px', { lineHeight: '18px', letterSpacing: '0.01em', fontWeight: '600' }],
                'data-mono': ['12px', { lineHeight: '16px', letterSpacing: '0.02em', fontWeight: '500' }],
            }
        },
    },

    // =========================================================================================
    // 4. PLUGIN TAILWIND (TAILWIND PLUGINS)
    // =========================================================================================
    // SYNTAX     : plugins: [forms]
    // CARA KERJA : Menerapkan plugin @tailwindcss/forms untuk mereset dan menstandarisasi elemen
    //              form bawaan HTML (input, select, textarea, checkbox) agar mudah di-styling.
    plugins: [forms],
};
