{{-- 
  ==============================================================================
  NAMA FILE      : public.blade.php
  MODUL          : Modul 4 - Portal Publik: Beranda, Katalog Fasilitas, Filter & Matriks Ketersediaan
  KODE FITUR     : PUB-01 (Katalog & Navigasi Publik) & PUB-02 (Pencarian Cerdas & Autocomplete)
  ==============================================================================
  DESKRIPSI:
  Kerangka tata letak master (Master Layout Shell) untuk seluruh halaman pengunjung publik.
  Mengusung estetika Apple-esque Minimalist dengan aksen Glassmorphism, tipografi Inter,
  animasi transisi halaman terarah (directional slide), komponen pencarian cerdas terpadu
  (Alpine.js Smart Search), serta banner persetujuan kebijakan cookie mengambang (floating consent).

  STRUKTUR HIERARKI:
  <x-public-layout>
    ├── <head> (SEO Meta, Google Fonts, Vite Bundle, Alpine Plugins, Custom CSS Animations)
    ├── Alpine Component: smartSearch (Engine Autocomplete Debounce 1000ms & Filter Aggregator)
    ├── Alpine Component: pageTransition (Deteksi Arah Transisi via sessionStorage)
    ├── <nav> (Fixed Header Glassmorphism dengan Navigasi Utama & Tombol Masuk)
    ├── <main> (Injeksi Konten Anak via $slot atau @yield('content'))
    ├── <footer> (Hak Cipta Biro Sarana & Prasarana Kampus)
    └── Cookie Floating Pill (Pemberitahuan Persetujuan Cookie Berbasis Native document.cookie)
  ==============================================================================
--}}

{{-- Inisialisasi Properti Komponen Blade (@props) dengan Nilai Bawaan (Default Values) --}}
@props([
    'title' => 'CAVA - Portal Publik Fasilitas Kampus', // Judul halaman di bilah browser tab
    'active' => 'home',                                  // Penanda tab navigasi aktif (home, catalog, availability, information)
    'subIndex' => 'null'                                // Indeks sub-halaman opsional
])

{{-- 
  Blok PHP: Pemetaan Indeks Halaman untuk Animasi Geser Relatif
  Jika pengguna berpindah dari halaman 1 (home) ke 2 (catalog), konten bergeser dari kanan ke kiri (slide-in-right).
  Jika dari 3 (availability) kembali ke 1 (home), konten bergeser dari kiri ke kanan (slide-in-left).
--}}
@php
    $pageIndexes = [
        'home' => 1,
        'catalog' => 2,
        'availability' => 3,
        'information' => 4
    ];
    $currentIndex = $pageIndexes[$active] ?? 1;
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full scroll-smooth">
<head>
    <meta charset="utf-8">
    {{-- Meta Viewport: Menjamin skalabilitas tampilan responsif di perangkat ponsel dan desktop --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    {{-- CSRF Token: Diperlukan untuk panggilan API POST/DELETE berbasis JavaScript --}}
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} | CAVA</title>
    {{-- Favicon Aplikasi Institusi Format WebP Ringan --}}
    <link rel="icon" type="image/webp" href="{{ asset('assets/images/logo.webp') }}">

    {{-- Optimasi Koneksi Font: Melakukan DNS prefetch dan handshake TLS lebih awal --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    {{-- Tipografi Modern: Google Fonts 'Inter' dan Ikon Google Material Symbols Outlined --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">

    {{-- Asset Bundler Vite: Mengompilasi Tailwind CSS dan skrip JavaScript aplikasi --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Alpine.js Plugins: Collapse (animasi akordeon filter) & Alpine Core Engine --}}
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    {{-- Kustomisasi CSS Spesifik: Glassmorphism, Sembunyikan Scrollbar, & Keyframe Animasi --}}
    <style>
        /* Mencegah Flash of Unstyled Content (FOUC) sebelum runtime Alpine.js selesai diinisialisasi */
        [x-cloak] { display: none !important; }
        
        /* Pengaturan ketebalan dan gaya visual Google Material Icons */
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        
        body { font-family: 'Inter', sans-serif; }
        
        /* Efek Kaca Buram (Glassmorphism) khas desain iOS/macOS */
        .glass-panel { 
            background: rgba(255, 255, 255, 0.7); 
            backdrop-filter: blur(12px); 
            -webkit-backdrop-filter: blur(12px); 
            border-bottom: 1px solid rgba(0, 0, 0, 0.05); 
        }
        
        /* Utilitas menyembunyikan bilah scrollbar horizontal namun tetap dapat digeser */
        .hide-scroll::-webkit-scrollbar { display: none; }
        
        /* Keyframe Animasi Transisi Halaman */
        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(60px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-60px); }
            to { opacity: 1; transform: translateX(0); }
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .animate-slide-in-right { animation: slideInRight 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .animate-slide-in-left { animation: slideInLeft 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
        .animate-fade-in { animation: fadeIn 0.6s ease-out forwards; }
    </style>

    {{-- Skrip Global Alpine.js: Mesin Autocomplete & Transisi Halaman --}}
    <script>
        document.addEventListener('alpine:init', () => {
            /**
             * Komponen Alpine: 'smartSearch' (Pencarian Cerdas & Autocomplete - PUB-02)
             * Mengelola input pencarian, debounce waktu pengetikan, pengambilan data AJAX ke server,
             * agregasi parameter filter aktif dari form induk, dan penyorotan teks hasil (highlighting).
             */
            Alpine.data('smartSearch', (pageType) => ({
                query: '{{ request('search') }}', // Inisialisasi dengan nilai parameter URL saat ini
                suggestions: [],                   // Menyimpan teks rekomendasi kategori / nama gedung
                facilities: [],                    // Menyimpan daftar objek ruangan yang cocok
                loading: false,                    // Indikator spinner pemuatan data
                open: false,                       // Status visibilitas popover dropdown hasil
                emptyState: false,                 // Status ketika tidak ada data yang ditemukan
                timer: null,                       // Timer referensi untuk fungsi Debounce
                pageType: pageType,

                init() {
                    let formEl = this.$el.closest('form');
                    if (formEl) {
                        // Cegah submit form kosong jika pengguna belum memilih filter apapun
                        formEl.addEventListener('submit', (e) => {
                            let hasFilter = false;
                            if (formEl.querySelectorAll('input[type="checkbox"]:checked').length > 0) hasFilter = true;
                            let cap = formEl.querySelector('input[name="capacity"]');
                            if (cap && cap.value.trim() !== '') hasFilter = true;

                            if (this.query.trim().length === 0 && !hasFilter) {
                                e.preventDefault();
                                this.open = true;
                                this.emptyState = true;
                                this.suggestions = [];
                                this.facilities = [];
                            }
                        });
                    }

                    // Dengarkan event kustom untuk menutup dropdown saat area lain diklik
                    window.addEventListener('close-smart-search', () => {
                        this.open = false;
                    });

                    // Pantau setiap ketukan tombol keyboard pada variabel $watch
                    this.$watch('query', value => {
                        this.emptyState = false;
                        if (value.trim().length === 0) {
                            this.suggestions = [];
                            this.facilities = [];
                            this.open = false;
                            return;
                        }
                        
                        this.loading = true;
                        this.open = true;
                        this.$dispatch('close-other-popups');
                        clearTimeout(this.timer);
                        
                        // DEBOUNCE 1000ms: Tunda eksekusi fetch API hingga pengguna berhenti mengetik selama 1 detik
                        this.timer = setTimeout(() => {
                            this.fetchResults();
                        }, 1000);
                    });
                },

                /**
                 * Mengambil data saran pencarian dari API publik Laravel secara Asynchronous
                 */
                async fetchResults() {
                    try {
                        let params = new URLSearchParams({ q: this.query });
                        
                        // Periksa apakah formulir induk memiliki filter kategori/alat yang sedang aktif
                        let formEl = this.$el.closest('form');
                        if (formEl && formEl.__x) {
                            let formComponent = Alpine.$data(formEl);
                            if (formComponent.selectedCategories && formComponent.selectedCategories.length > 0) {
                                formComponent.selectedCategories.forEach(c => params.append('categories[]', c));
                            }
                            if (formComponent.selectedFacilities && formComponent.selectedFacilities.length > 0) {
                                formComponent.selectedFacilities.forEach(f => params.append('equipments[]', f));
                            }
                            if (formComponent.minCapacity) {
                                params.append('capacity', formComponent.minCapacity);
                            }
                        }

                        // Eksekusi HTTP Request ke API Endpoint CAVA
                        let res = await fetch(`/api/facilities/search?${params.toString()}`);
                        let data = await res.json();
                        this.suggestions = data.suggestions || [];
                        this.facilities = data.facilities || [];
                        this.emptyState = data.empty_state || false;
                    } catch (e) {
                        console.error('Gagal mengambil saran pencarian:', e);
                    } finally {
                        this.loading = false;
                    }
                },

                /**
                 * Menyorot kata kunci yang dicari dengan warna biru bold pada teks hasil pencarian
                 */
                highlight(text) {
                    if (!this.query) return text;
                    // Escape karakter khusus regex agar tidak menimbulkan runtime exception
                    let escapedQuery = this.query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                    const regex = new RegExp(`(${escapedQuery})`, 'gi');
                    return text.replace(regex, '<span class="text-blue-600 font-semibold">$1</span>');
                }
            }));

            /**
             * Komponen Alpine: 'pageTransition'
             * Menghitung arah perpindahan antar halaman menggunakan sessionStorage
             * untuk menentukan kelas animasi CSS yang tepat (Slide Kanan / Slide Kiri / Fade In).
             */
            Alpine.data('pageTransition', (pageIndex) => ({
                animationClass: '',
                mobileMenuOpen: false,
                init() {
                    this.animationClass = 'opacity-0'; // Status awal tersembunyi
                    
                    let prevIndex = sessionStorage.getItem('prevPageIndex');
                    let categoryClicked = sessionStorage.getItem('categoryClicked');
                    
                    if (!prevIndex) {
                        this.animationClass = 'animate-fade-in';
                    } else {
                        prevIndex = parseInt(prevIndex);
                        pageIndex = parseInt(pageIndex);
                        
                        if (pageIndex > prevIndex) {
                            // Berpindah ke menu di sebelah kanan ➔ geser dari kanan
                            this.animationClass = 'animate-slide-in-right';
                        } else if (pageIndex < prevIndex) {
                            // Berpindah ke menu di sebelah kiri ➔ geser dari kiri
                            this.animationClass = 'animate-slide-in-left';
                        } else {
                            // Berada pada menu yang sama (misal memfilter kategori di katalog)
                            if (categoryClicked === 'true') {
                                this.animationClass = 'opacity-100'; // Hindari animasi berulang yang mengganggu
                                sessionStorage.removeItem('categoryClicked');
                            } else {
                                this.animationClass = 'animate-fade-in';
                            }
                        }
                    }
                    
                    // Simpan indeks halaman saat ini ke sesi browser
                    sessionStorage.setItem('prevPageIndex', pageIndex);
                }
            }))
        });
    </script>
</head>
<body x-data="pageTransition({{ $currentIndex }})" class="bg-[#FAFAFC] text-[#1D1D1F] antialiased min-h-screen flex flex-col overflow-x-hidden">

    {{-- NAVIGASI HEADER UNIVERSAL DENGAN GLASSMORPHISM --}}
    <nav class="fixed w-full z-40 glass-panel">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center">
            <!-- Sisi Kiri: Identitas Logo Kampus CAVA -->
            <div class="flex-1 flex justify-start">
                <a href="{{ route('home') }}" class="flex items-center gap-2 font-semibold tracking-tight text-lg hover:opacity-80 transition">
                    <img src="{{ asset('assets/images/logo.webp') }}" alt="Logo CAVA" class="w-8 h-8 object-contain">
                    <span>CAVA.</span>
                </a>
            </div>
            
            <!-- Sisi Tengah: Tautan Menu Utama Portal Publik -->
            <div class="hidden md:flex gap-8 text-sm text-[#86868B] font-medium justify-center">
                <a href="{{ route('home') }}" class="{{ $active === 'home' ? 'text-[#1D1D1F]' : 'hover:text-[#1D1D1F] transition' }}">Beranda</a>
                <a href="{{ route('public.catalog') }}" class="{{ $active === 'catalog' ? 'text-[#1D1D1F]' : 'hover:text-[#1D1D1F] transition' }}">Katalog</a>
                <a href="{{ route('public.availability') }}" class="{{ $active === 'availability' ? 'text-[#1D1D1F]' : 'hover:text-[#1D1D1F] transition' }}">Matriks Jadwal</a>
                <a href="{{ route('public.information') }}" class="{{ $active === 'information' ? 'text-[#1D1D1F]' : 'hover:text-[#1D1D1F] transition' }}">Informasi</a>
            </div>

            <!-- Sisi Kanan: Call to Action (CTA) Autentikasi Sivitas -->
            <div class="flex-1 flex justify-end">
                <a href="{{ route('login') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">Masuk</a>
            </div>
        </div>
    </nav>

    {{-- CONTAINER UTAMA: Tempat Konten Halaman Disuntikkan ($slot / @yield) --}}
    <div class="flex-1 flex flex-col min-h-screen pt-16 opacity-0" :class="animationClass">
        <main class="flex-1 w-full mx-auto">
            {{-- Mendukung pemanggilan berbasis Blade Component ($slot) maupun Blade Layout (@section) --}}
            {{ $slot ?? '' }}
            @yield('content')
        </main>

        {{-- FOOTER MINIMALIS --}}
        <footer class="py-6 text-center text-xs text-[#86868B] mt-auto">
            &copy; {{ date('Y') }} CAVA - Biro Sarana & Prasarana Kampus.
        </footer>
    </div>

    {{-- 
      BANNER PERSETUJUAN COOKIE & KEBIJAKAN (Floating Pill Notification)
      Menggunakan objek reaktif x-data untuk mendeteksi apakah cookie 'cava_policy_accepted=1' telah tersimpan.
      Jika belum disetujui, banner muncul secara elegan di pojok kanan bawah layar.
    --}}
    <div x-data="{
            accepted: document.cookie.split('; ').some(row => row.startsWith('cava_policy_accepted=1')),
            acceptPolicy() {
                // Simpan cookie persetujuan selama 1 tahun (365 hari)
                document.cookie = 'cava_policy_accepted=1;path=/;max-age=' + (60 * 60 * 24 * 365);
                this.accepted = true;
            }
         }"
         x-show="!accepted"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         class="fixed bottom-6 inset-x-6 sm:inset-x-auto sm:right-6 sm:max-w-md z-50 p-5 rounded-2xl bg-white/95 backdrop-blur-md border border-gray-200/80 shadow-[0_12px_40px_rgb(0,0,0,0.08)] flex flex-col gap-3 text-xs text-[#1D1D1F]">
        <div class="flex items-start gap-3">
            <span class="material-symbols-outlined text-blue-600 text-[20px] shrink-0 mt-0.5">cookie</span>
            <div class="space-y-1">
                <p class="font-bold text-[#1D1D1F]">Kebijakan & Privasi Layanan CAVA</p>
                <p class="text-[#86868B] leading-relaxed">
                    Sistem ini menggunakan cookies dan sesi lokal untuk menyimpan preferensi filter fasilitas dan kelancaran pengajuan reservasi Anda.
                </p>
            </div>
        </div>
        <div class="flex items-center justify-end gap-2 pt-1 border-t border-gray-100">
            <a href="{{ route('public.information', ['section' => 'kebijakan']) }}" class="px-3 py-1.5 text-[#86868B] hover:text-[#1D1D1F] font-medium transition">
                Pelajari Kebijakan
            </a>
            <button type="button" @click="acceptPolicy()" class="px-4 py-1.5 rounded-xl bg-[#1D1D1F] hover:bg-black text-white font-medium transition shadow-2xs cursor-pointer">
                Setujui & Lanjutkan
            </button>
        </div>
    </div>
</body>
</html>
