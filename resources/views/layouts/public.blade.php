{{-- 
  NAMA FILE      : public.blade.php
  FUNGSIONALITAS : Kerangka Layout Utama (Master) untuk Halaman Publik / Pengunjung
  DESKRIPSI      : Layout Apple-esque Minimalist (Option 1) dengan Glassmorphism
--}}

@props([
    'title' => 'CAVA - Portal Publik Fasilitas Kampus',
    'active' => 'home',
    'subIndex' => 'null'
])

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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ $title }} | CAVA</title>
    <link rel="icon" type="image/webp" href="{{ asset('assets/images/logo.webp') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        [x-cloak] { display: none !important; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        body { font-family: 'Inter', sans-serif; }
        .glass-panel { background: rgba(255, 255, 255, 0.7); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); border-bottom: 1px solid rgba(0, 0, 0, 0.05); }
        .hide-scroll::-webkit-scrollbar { display: none; }
        
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
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('smartSearch', (pageType) => ({
                query: '{{ request('search') }}',
                suggestions: [],
                facilities: [],
                loading: false,
                open: false,
                timer: null,
                pageType: pageType,

                init() {
                    this.$watch('query', value => {
                        if (value.trim().length === 0) {
                            this.suggestions = [];
                            this.facilities = [];
                            this.open = false;
                            return;
                        }
                        
                        this.loading = true;
                        this.open = true;
                        clearTimeout(this.timer);
                        
                        this.timer = setTimeout(() => {
                            this.fetchResults();
                        }, 1000);
                    });
                },

                async fetchResults() {
                    try {
                        let params = new URLSearchParams({ q: this.query });
                        
                        // Cek apakah ada allFilters di form terdekat
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

                        let res = await fetch(`/api/facilities/search?${params.toString()}`);
                        let data = await res.json();
                        this.suggestions = data.suggestions || [];
                        this.facilities = data.facilities || [];
                    } catch (e) {
                        console.error(e);
                    } finally {
                        this.loading = false;
                    }
                },

                highlight(text) {
                    if (!this.query) return text;
                    // Escape special regex chars
                    let escapedQuery = this.query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                    const regex = new RegExp(`(${escapedQuery})`, 'gi');
                    return text.replace(regex, '<span class="text-blue-600 font-semibold">$1</span>');
                }
            }));

            Alpine.data('pageTransition', (pageIndex) => ({
                animationClass: '',
                mobileMenuOpen: false,
                init() {
                    this.animationClass = 'opacity-0'; // Default hidden
                    
                    let prevIndex = sessionStorage.getItem('prevPageIndex');
                    let categoryClicked = sessionStorage.getItem('categoryClicked');
                    
                    if (!prevIndex) {
                        this.animationClass = 'animate-fade-in';
                    } else {
                        prevIndex = parseInt(prevIndex);
                        pageIndex = parseInt(pageIndex);
                        
                        if (pageIndex > prevIndex) {
                            this.animationClass = 'animate-slide-in-right';
                        } else if (pageIndex < prevIndex) {
                            this.animationClass = 'animate-slide-in-left';
                        } else {
                            // Jika berada di indeks halaman yang sama
                            if (categoryClicked === 'true') {
                                this.animationClass = 'opacity-100'; // Langsung tampil utuh tanpa kedip
                                sessionStorage.removeItem('categoryClicked');
                            } else {
                                this.animationClass = 'animate-fade-in';
                            }
                        }
                    }
                    
                    sessionStorage.setItem('prevPageIndex', pageIndex);
                }
            }))
        });
    </script>
</head>
<body x-data="pageTransition({{ $currentIndex }})" class="bg-[#FAFAFC] text-[#1D1D1F] antialiased min-h-screen flex flex-col overflow-x-hidden">

    <nav class="fixed w-full z-40 glass-panel">
        <div class="max-w-7xl mx-auto px-6 h-16 flex items-center">
            <!-- Kiri: Logo -->
            <div class="flex-1 flex justify-start">
                <a href="{{ route('home') }}" class="flex items-center gap-2 font-semibold tracking-tight text-lg hover:opacity-80 transition">
                    <img src="{{ asset('assets/images/logo.webp') }}" alt="Logo CAVA" class="w-8 h-8 object-contain">
                    <span>CAVA.</span>
                </a>
            </div>
            
            <!-- Tengah: Menu Navigasi -->
            <div class="hidden md:flex gap-8 text-sm text-[#86868B] font-medium justify-center">
                <a href="{{ route('home') }}" class="{{ $active === 'home' ? 'text-[#1D1D1F]' : 'hover:text-[#1D1D1F] transition' }}">Beranda</a>
                <a href="{{ route('public.catalog') }}" class="{{ $active === 'catalog' ? 'text-[#1D1D1F]' : 'hover:text-[#1D1D1F] transition' }}">Katalog</a>
                <a href="{{ route('public.availability') }}" class="{{ $active === 'availability' ? 'text-[#1D1D1F]' : 'hover:text-[#1D1D1F] transition' }}">Matriks Jadwal</a>
                <a href="{{ route('public.information') }}" class="{{ $active === 'information' ? 'text-[#1D1D1F]' : 'hover:text-[#1D1D1F] transition' }}">Informasi</a>
            </div>

            <!-- Kanan: Tombol Masuk -->
            <div class="flex-1 flex justify-end">
                <a href="{{ route('login') }}" class="text-sm font-medium text-blue-600 hover:text-blue-700">Masuk</a>
            </div>
        </div>
    </nav>

    <div class="flex-1 flex flex-col min-h-screen pt-16 opacity-0" :class="animationClass">
        <main class="flex-1 w-full mx-auto">
            {{ $slot ?? '' }}
            @yield('content')
        </main>
        <footer class="py-6 text-center text-xs text-[#86868B] mt-auto">
            &copy; {{ date('Y') }} CAVA - Biro Sarana & Prasarana Kampus.
        </footer>
    </div>
</body>
</html>
