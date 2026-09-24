{{-- 
  NAMA FILE      : catalog.blade.php
  FUNGSIONALITAS : Halaman Katalog Lengkap Fasilitas & Ruang Kampus (Visitor & Sivitas)
  DESKRIPSI      : Menampilkan seluruh daftar fasilitas kampus secara komprehensif dengan filter tipe, gedung, kapasitas, modal spesifikasi alat, dan tombol aksi pemesanan.
  CARA KERJA     : Menggunakan layout <x-public-layout active="catalog">, menyediakan pencarian instan dan modal detail interaktif dengan Alpine.js.
--}}

<x-public-layout title="Katalog Lengkap Fasilitas Kampus" active="catalog">
    <div x-data="catalogData()">

        {{-- Header Breadcrumb & Judul Halaman --}}
        <div class="mb-6">
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-2 font-medium">
                <a href="{{ url('/') }}" class="hover:text-slate-900 transition">Beranda</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-slate-900">Katalog Lengkap Fasilitas Kampus</span>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Katalog Seluruh Fasilitas & Ruang Kampus</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Daftar lengkap inventaris ruang universitas dengan spesifikasi peralatan, kapasitas, dan status terkini.</p>
                </div>
                <a href="{{ url('/public/availability') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-white border border-slate-200 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50 transition shadow-xs">
                    <span class="material-symbols-outlined text-[18px]">calendar_month</span>
                    <span>Buka Matriks Jadwal 30m</span>
                </a>
            </div>
        </div>

        {{-- Parameter Filter & Pencarian --}}
        <form method="GET" action="{{ route('public.catalog') }}" class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="relative w-full md:w-80" @click.away="showSuggestions = false">
                <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-[20px]">search</span>
                <input type="text" name="search" x-model="search" @input.debounce.300ms="fetchSuggestions" @focus="search.length > 0 ? fetchSuggestions() : null" placeholder="Cari nama ruang, kode, gedung..." class="w-full pl-10 pr-4 py-2 text-sm bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-slate-900 focus:bg-white transition" autocomplete="off">
                
                {{-- Dropdown Autocomplete --}}
                <div x-show="showSuggestions" style="display: none;" class="absolute z-50 left-0 right-0 mt-2 bg-white border border-slate-200 rounded-xl shadow-lg max-h-60 overflow-y-auto">
                    <template x-if="isSearching">
                        <div class="p-3 text-xs text-slate-500 text-center">Mencari...</div>
                    </template>
                    <template x-if="!isSearching && suggestions.length === 0 && search.trim() !== ''">
                        <div class="p-3 text-xs text-slate-500 text-center">Tidak ditemukan.</div>
                    </template>
                    <template x-for="item in suggestions" :key="item.id">
                        <div @click="openDetail(item)" class="p-3 border-b border-slate-50 hover:bg-slate-50 cursor-pointer flex items-center justify-between transition">
                            <div class="overflow-hidden">
                                <div class="text-sm font-bold text-slate-800 truncate" x-text="item.name"></div>
                                <div class="text-xs text-slate-500 truncate" x-text="item.building"></div>
                            </div>
                            <span class="px-2 py-0.5 rounded bg-slate-100 text-[10px] font-semibold text-slate-600 uppercase ml-2 whitespace-nowrap" x-text="item.category"></span>
                        </div>
                    </template>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                <div class="flex items-center gap-2">
                    <label for="filter-cat" class="text-xs font-semibold text-slate-500">Tipe:</label>
                    <select id="filter-cat" name="category" x-model="selectedCategory" class="text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-900">
                        <option value="semua">Semua Tipe</option>
                        <option value="auditorium">Auditorium & Hall</option>
                        <option value="lab">Laboratorium</option>
                        <option value="kelas">Ruang Kelas / Teater</option>
                        <option value="olahraga">Aula & Olahraga</option>
                        <option value="rapat">Ruang Rapat</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <label for="filter-bld" class="text-xs font-semibold text-slate-500">Gedung:</label>
                    <select id="filter-bld" name="building" x-model="selectedBuilding" class="text-xs bg-slate-50 border border-slate-200 rounded-xl px-3 py-2 text-slate-700 focus:outline-none focus:ring-2 focus:ring-slate-900">
                        <option value="semua">Semua Gedung</option>
                        <option value="Rektorat">Gedung Rektorat</option>
                        <option value="Lab Terpadu C">Gedung Lab Terpadu C</option>
                        <option value="Kuliah Bersama B">Gedung Kuliah Bersama B</option>
                        <option value="PKM">Student Center (PKM)</option>
                    </select>
                </div>

                <button type="submit" class="px-4 py-2 bg-slate-900 text-white text-xs font-semibold rounded-xl hover:bg-slate-800 transition">
                    Cari
                </button>
                <a href="{{ route('public.catalog') }}" class="text-xs font-medium text-slate-500 hover:text-slate-900 underline px-1">
                    Reset
                </a>
            </div>
        </form>

        {{-- Indikator Jumlah Data --}}
        <div class="flex items-center justify-between text-xs text-slate-500 mb-4 px-1">
            <span>Menampilkan <strong class="text-slate-900" x-text="venues.length"></strong> fasilitas kampus</span>
            <span>Jam Operasional: 07:00 - 20:00 WIB</span>
        </div>

        <template x-if="venues.length === 0">
            <div class="bg-white rounded-2xl border border-slate-200/80 p-12 shadow-xs text-center">
                <span class="material-symbols-outlined text-[48px] text-slate-300 mb-3">search_off</span>
                <h3 class="text-lg font-bold text-slate-800 mb-1">Fasilitas tidak ditemukan</h3>
                <p class="text-sm text-slate-500">Tidak ada fasilitas yang cocok dengan kriteria pencarian Anda.</p>
            </div>
        </template>

        {{-- Grid Seluruh Fasilitas Kampus --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <template x-for="venue in venues" :key="venue.id">
                <div class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs hover:shadow-md transition-all flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-2 mb-2">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-slate-100 text-slate-700" x-text="venue.id"></span>
                            <span x-show="venue.status === 'approved'" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs bg-emerald-50 text-emerald-800 border border-emerald-200/60 font-semibold">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                Tersedia
                            </span>
                            <span x-show="venue.status === 'locked'" class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs bg-rose-50 text-rose-700 border border-rose-200/60 font-semibold">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                Dalam Perbaikan
                            </span>
                        </div>

                        <h3 class="text-base font-bold text-slate-900 leading-snug" x-text="venue.name"></h3>
                        <p class="text-xs text-slate-500 flex items-center gap-1 mt-1 mb-3">
                            <span class="material-symbols-outlined text-[15px]">location_on</span>
                            <span x-text="venue.building"></span>
                        </p>

                        <div class="flex items-center gap-2 text-xs text-slate-600 pb-3 mb-3 border-b border-slate-100">
                            <span class="font-semibold text-slate-800" x-text="venue.capacity + ' Kursi'"></span>
                            <span>•</span>
                            <span class="truncate" x-text="venue.equipment.slice(0, 2).join(', ') + '...'"></span>
                        </div>
                    </div>

                    <div>
                        <div class="flex items-center justify-between text-xs text-slate-600 mb-1">
                            <span>Okupansi Hari Ini:</span>
                            <span class="font-semibold" x-text="venue.status === 'approved' ? (venue.availableSlots + ' / ' + venue.totalSlots + ' Slot Bebas') : 'Terkunci'"></span>
                        </div>
                        <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden mb-4">
                            <div class="h-full rounded-full transition-all"
                                 :class="venue.status === 'approved' ? 'bg-emerald-500' : 'bg-slate-400'"
                                 :style="`width: ${(venue.availableSlots / venue.totalSlots) * 100}%`"></div>
                        </div>

                        <div class="flex items-center gap-2">
                            <button type="button" @click="openDetail(venue)" class="flex-1 py-2 px-3 rounded-xl border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50 transition text-center flex items-center justify-center gap-1">
                                <span class="material-symbols-outlined text-[16px]">info</span>
                                <span>Detail</span>
                            </button>
                            <a href="{{ route('login') }}" class="flex-1 py-2 px-3 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition text-center shadow-xs">
                                Reservasi
                            </a>
                        </div>
                    </div>
                </div>
            </template>
        </div>

        {{-- Modal Detail Spesifikasi Fasilitas --}}
        <div x-show="modalDetail" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="modalDetail = false" class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 relative">
                <button type="button" @click="modalDetail = false" class="absolute top-4 right-4 p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100">
                    <span class="material-symbols-outlined">close</span>
                </button>

                <template x-if="activeVenue">
                    <div>
                        <div class="flex items-center gap-2 text-xs font-semibold text-slate-500 mb-1">
                            <span class="px-2 py-0.5 rounded bg-slate-100 font-mono text-slate-800" x-text="activeVenue.id"></span>
                            <span class="uppercase tracking-wider" x-text="activeVenue.category"></span>
                        </div>
                        <h2 class="text-xl font-bold text-slate-900 mb-1" x-text="activeVenue.name"></h2>
                        <p class="text-xs text-slate-500 flex items-center gap-1 mb-4">
                            <span class="material-symbols-outlined text-[16px]">location_on</span>
                            <span x-text="activeVenue.building"></span>
                        </p>

                        <p class="text-xs text-slate-600 mb-4 leading-relaxed" x-text="activeVenue.desc"></p>

                        <div class="grid grid-cols-2 gap-3 bg-slate-50 p-3.5 rounded-2xl mb-4 border border-slate-100 text-xs">
                            <div>
                                <span class="text-slate-400 block">Kapasitas Maksimal:</span>
                                <span class="text-sm font-bold text-slate-900" x-text="activeVenue.capacity + ' Orang'"></span>
                            </div>
                            <div>
                                <span class="text-slate-400 block">Jam Operasional:</span>
                                <span class="text-sm font-bold text-slate-900">07:00 - 20:00 WIB</span>
                            </div>
                        </div>

                        <div class="mb-6">
                            <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">Peralatan Tersedia:</h4>
                            <div class="flex flex-wrap gap-1.5">
                                <template x-for="item in activeVenue.equipment" :key="item">
                                    <span class="px-2.5 py-1 rounded-lg text-xs font-medium bg-slate-100 text-slate-700 border border-slate-200/80" x-text="item"></span>
                                </template>
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center justify-end gap-2 pt-3 border-t border-slate-100">
                            <button type="button" @click="modalDetail = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-500 text-xs font-semibold hover:bg-slate-50">
                                Batal
                            </button>
                            <a :href="'{{ url('/public/availability') }}?facility=' + activeVenue.id" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50 flex items-center gap-1.5">
                                <span class="material-symbols-outlined text-[16px]">calendar_month</span> Cek Jadwal
                            </a>
                            <a href="{{ route('login') }}" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 shadow-xs">
                                Masuk untuk Reservasi
                            </a>
                        </div>
                    </div>
                </template>
            </div>
        </div>

    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('catalogData', () => ({
                search: '{{ request('search', '') }}',
                selectedCategory: '{{ request('category', 'semua') }}',
                selectedBuilding: '{{ request('building', 'semua') }}',
                modalDetail: false,
                activeVenue: null,
                venues: @json($facilities), // Backend mem-filter ini
                
                // Autocomplete
                suggestions: [],
                showSuggestions: false,
                isSearching: false,

                async fetchSuggestions() {
                    if (this.search.trim() === '') {
                        this.suggestions = [];
                        this.showSuggestions = false;
                        return;
                    }

                    this.isSearching = true;
                    this.showSuggestions = true;

                    try {
                        const response = await fetch(`/api/facilities/search?q=${encodeURIComponent(this.search)}`);
                        if (response.ok) {
                            this.suggestions = await response.json();
                        }
                    } catch (e) {
                        console.error('Error fetching autocomplete', e);
                    } finally {
                        this.isSearching = false;
                    }
                },

                openDetail(v) {
                    this.activeVenue = v;
                    this.modalDetail = true;
                    this.showSuggestions = false;
                }
            }));
        });
    </script>
</x-public-layout>
