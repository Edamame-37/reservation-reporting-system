@php
    $currentCategory = request('category', 'semua');
    $categoryIndexes = ['semua' => 1];
    $idx = 2;
    foreach($categories as $cat) {
        $categoryIndexes[$cat] = $idx++;
    }
    $subIndex = $categoryIndexes[$currentCategory] ?? 1;
@endphp
<x-public-layout title="Katalog Fasilitas" active="catalog">
    <div class="pt-24 pb-20 max-w-7xl mx-auto px-6" x-data="{ 
        showModal: false, 
        activeFacility: null 
    }" x-init="
        @if(request()->has('auto_open') && count($facilities) > 0)
            $nextTick(() => {
                activeFacility = {{ json_encode($facilities->first()) }};
                showModal = true;
            });
        @endif
    ">
        <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-6 mb-24">
            <div>
                <h1 class="text-4xl font-bold tracking-tight mb-2">Katalog Fasilitas</h1>
                <p class="text-lg text-[#86868B]">Temukan ruang yang sesuai dengan kebutuhan spesifik kegiatan Anda.</p>
            </div>
            
            <form action="{{ route('public.catalog') }}" method="GET" class="w-full md:w-96 relative group shrink-0" x-data="{ 
                showAdvanced: false,
                openCategory: false,
                selectedCategories: {{ json_encode(request('categories', [])) }},
                minCapacity: '{{ request('capacity', '') }}',
                openFacility: false,
                selectedFacilities: {{ json_encode(request('equipments', [])) }},
                get allFilters() {
                    let filters = [];
                    this.selectedCategories.forEach(c => filters.push({ type: 'category', value: c }));
                    if (this.minCapacity !== '') filters.push({ type: 'capacity', value: this.minCapacity });
                    this.selectedFacilities.forEach(f => filters.push({ type: 'facility', value: f }));
                    return filters;
                },
                removeFilter(filter) {
                    if (filter.type === 'category') {
                        let idx = this.selectedCategories.indexOf(filter.value);
                        if(idx > -1) this.selectedCategories.splice(idx, 1);
                    } else if (filter.type === 'capacity') {
                        this.minCapacity = '';
                    } else if (filter.type === 'facility') {
                        let idx = this.selectedFacilities.indexOf(filter.value);
                        if(idx > -1) this.selectedFacilities.splice(idx, 1);
                    }
                }
            }">
                <div class="relative group" x-data="smartSearch('catalog')" @click.outside="open = false">
                    @if(request()->has('category') && request('category') !== 'semua')
                        <input type="hidden" name="category" value="{{ request('category') }}">
                    @endif
                    <div class="absolute -inset-1 bg-gradient-to-r from-blue-100 to-indigo-100 rounded-2xl blur opacity-25 group-hover:opacity-50 transition duration-1000 group-hover:duration-200"></div>
                    <div class="relative bg-white rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-1.5 flex items-center border border-gray-100">
                        <span class="material-symbols-outlined text-gray-400 ml-3 mr-2 text-[20px]">search</span>
                        <input type="text" name="search" x-model="query" @focus="if(query.length > 0) open = true" value="{{ request('search') }}" placeholder="Cari nama ruang, gedung..." class="flex-1 py-2.5 px-2 border-0 focus:ring-0 focus:border-transparent outline-none text-base bg-transparent" autocomplete="off">
                        <button type="button" @click="showAdvanced = !showAdvanced" class="text-gray-400 hover:text-[#1D1D1F] p-1 mr-1 transition rounded-lg hover:bg-gray-50 focus:outline-none flex items-center justify-center">
                            <span class="material-symbols-outlined text-[20px]">tune</span>
                        </button>
                        <button type="submit" class="bg-[#1D1D1F] text-white px-5 py-2.5 rounded-xl text-sm font-medium hover:bg-gray-800 transition">Cari</button>
                    </div>

                    <!-- Smart Search Dropdown -->
                    <div x-show="open" 
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-2"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         x-transition:leave="transition ease-in duration-150"
                         x-transition:leave-start="opacity-100 translate-y-0"
                         x-transition:leave-end="opacity-0 translate-y-2"
                         class="absolute top-full left-0 right-0 mt-2 bg-white rounded-2xl shadow-xl border border-gray-100 overflow-hidden z-50 pointer-events-auto"
                         style="display: none;">
                         
                        <!-- Loading State -->
                        <div x-show="loading" class="p-4 flex items-center justify-center text-gray-500">
                            <svg class="animate-spin h-5 w-5 mr-3 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-sm font-medium">Mencari...</span>
                        </div>

                        <!-- Results -->
                        <div x-show="!loading" class="flex flex-col">
                            <!-- Text Suggestions -->
                            <template x-if="suggestions.length > 0">
                                <div class="px-2 pt-2">
                                    <div class="px-3 py-1.5 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Rekomendasi</div>
                                    <template x-for="text in suggestions">
                                        <button type="button" @click="query = text; open = false; $el.closest('form').submit()" class="w-full text-left px-3 py-2 rounded-xl hover:bg-gray-50 flex items-center gap-3 transition">
                                            <span class="material-symbols-outlined text-gray-400 text-[18px]">search</span>
                                            <span class="text-sm text-gray-700" x-html="highlight(text)"></span>
                                        </button>
                                    </template>
                                </div>
                            </template>

                            <!-- Facility Suggestions -->
                            <template x-if="facilities.length > 0">
                                <div class="px-2 pb-2" :class="suggestions.length > 0 ? 'border-t border-gray-100 mt-2 pt-2' : 'pt-2'">
                                    <div class="px-3 py-1.5 text-[10px] font-semibold text-gray-400 uppercase tracking-wider">Fasilitas</div>
                                    <template x-for="f in facilities">
                                        <a :href="'/public/catalog?search=' + encodeURIComponent(f.name) + '&auto_open=1'" class="w-full text-left px-3 py-2 rounded-xl hover:bg-gray-50 flex items-center gap-3 transition">
                                            <span class="material-symbols-outlined text-blue-500 text-[18px]">meeting_room</span>
                                            <div class="flex flex-col">
                                                <span class="text-sm font-medium text-gray-900" x-html="highlight(f.name)"></span>
                                                <span class="text-[10px] text-gray-500" x-text="f.building"></span>
                                            </div>
                                        </a>
                                    </template>
                                </div>
                            </template>

                            <!-- Empty State Warning -->
                            <template x-if="emptyState">
                                <div class="p-6 text-center text-gray-500">
                                    <span class="material-symbols-outlined text-4xl mb-2 text-yellow-500">warning</span>
                                    <p class="text-sm">Ketikkan sesuatu atau pilih filter untuk memulai pencarian</p>
                                </div>
                            </template>

                            <!-- No Results -->
                            <template x-if="!emptyState && suggestions.length === 0 && facilities.length === 0">
                                <div class="p-6 text-center text-gray-500">
                                    <span class="material-symbols-outlined text-4xl mb-2 text-gray-300">search_off</span>
                                    <p class="text-sm">Tidak ditemukan hasil untuk "<span class="font-medium text-gray-700" x-text="query"></span>"</p>
                                </div>
                            </template>

                            <!-- Action Buttons -->
                            <template x-if="suggestions.length > 0 || facilities.length > 0">
                                <div class="bg-gray-50 p-2.5 border-t border-gray-100 flex gap-2">
                                    <button type="submit" class="w-full flex justify-center items-center gap-2 bg-white border border-gray-200 text-[#1D1D1F] px-4 py-2 rounded-xl text-sm font-medium hover:bg-gray-100 transition">
                                        Lihat Selengkapnya
                                    </button>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Absolute Container for Filters and Popup -->
                <div class="absolute top-full left-0 right-0 w-full z-40 pointer-events-none"
                     x-data="{ pillsHeight: 0 }"
                     x-init="
                        const observer = new ResizeObserver(entries => {
                            pillsHeight = entries[0].target.offsetHeight;
                        });
                        if ($refs.pillContainer) {
                            observer.observe($refs.pillContainer);
                        }
                     ">
                     
                    <!-- Filter Aktif (Absolute) -->
                    <div class="absolute top-0 left-0 right-0 w-full pointer-events-auto"
                         x-ref="pillContainer">
                        <div x-show="allFilters.length > 0" 
                             x-transition.opacity.duration.300ms 
                             class="flex flex-wrap items-center gap-2 pt-4 pb-1"
                             style="display: none;">
                            <span class="text-sm font-medium text-gray-500 py-1 mr-1">Filter Aktif:</span>
                            
                            <template x-for="filter in allFilters.slice(0, 3)" :key="filter.type + '-' + filter.value">
                                <span x-transition.opacity.scale.90.duration.200ms 
                                      class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[13px] font-medium border shadow-sm"
                                      :class="filter.type === 'category' ? 'bg-blue-50 text-blue-800 border-blue-200' : (filter.type === 'capacity' ? 'bg-purple-50 text-purple-800 border-purple-200' : 'bg-emerald-50 text-emerald-800 border-emerald-200')">
                                    <span x-text="filter.type === 'capacity' ? 'Min. ' + filter.value + ' Kursi' : filter.value"></span>
                                    <button type="button" @click.prevent.stop="removeFilter(filter)" 
                                            class="focus:outline-none transition"
                                            :class="filter.type === 'category' ? 'text-blue-500 hover:text-blue-900' : (filter.type === 'capacity' ? 'text-purple-500 hover:text-purple-900' : 'text-emerald-500 hover:text-emerald-900')">
                                        <span class="material-symbols-outlined text-[14px] font-bold">close</span>
                                    </button>
                                </span>
                            </template>
                            
                            <span x-show="allFilters.length > 3" x-transition.opacity.scale.90.duration.200ms 
                                  class="inline-flex items-center px-3 py-1.5 rounded-lg bg-gray-100 text-gray-700 text-[13px] font-medium border border-gray-200 shadow-sm">
                                <span x-text="'+' + (allFilters.length - 3) + ' lainnya'"></span>
                            </span>

                            <button type="button" x-transition.opacity.duration.200ms @click.prevent.stop="selectedCategories = []; selectedFacilities = []; minCapacity = ''" class="text-[13px] font-medium text-gray-500 hover:text-red-600 underline px-2 py-1 transition ml-1">Hapus Semua</button>
                        </div>
                    </div>

                    <!-- Advanced Search Popup (Absolute positioning slides based on pillsHeight) -->
                    <div class="absolute right-0 md:min-w-[600px] transition-all duration-300 ease-out pointer-events-auto"
                         :style="`top: ${(allFilters.length > 0) ? pillsHeight : 0}px`">
                        
                        <!-- TODO: Hardcoded advanced search panel, sesuaikan dengan rute dan backend nanti -->
                        <div x-show="showAdvanced" 
                             @click.away="showAdvanced = false"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-2"
                             x-transition:enter-end="opacity-100 translate-y-0"
                             x-transition:leave="transition ease-in duration-150"
                             x-transition:leave-start="opacity-100 translate-y-0"
                             x-transition:leave-end="opacity-0 translate-y-2"
                             class="mt-4 bg-white rounded-2xl shadow-xl border border-gray-100 p-6 z-50 text-left" style="display: none;">
                             <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-100">
                        <h3 class="font-semibold text-lg text-[#1D1D1F]">Pencarian Spesifik</h3>
                        <button type="button" @click="showAdvanced = false" class="text-gray-400 hover:text-gray-600"><span class="material-symbols-outlined text-xl">close</span></button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-[#1D1D1F] mb-2">Kategori Fasilitas</label>
                            <div class="relative mt-2">
                                <button type="button" @click="openCategory = !openCategory" class="w-full bg-gray-50 border border-gray-200 text-[#1D1D1F] text-sm rounded-xl p-3 flex justify-between items-center text-left focus:ring-blue-500 focus:border-blue-500 transition">
                                    <span x-text="selectedCategories.length === 0 ? 'Pilih Kategori...' : selectedCategories.length + ' Kategori Dipilih'" class="truncate mr-2"></span>
                                    <span class="material-symbols-outlined text-gray-500 text-sm transition-transform duration-200" :class="openCategory ? 'rotate-180' : ''">expand_more</span>
                                </button>
                                
                                <div x-show="openCategory" @click.away="openCategory = false" x-transition.opacity.duration.200ms class="absolute z-10 w-full mt-2 bg-white border border-gray-100 rounded-xl shadow-lg max-h-60 overflow-y-auto" style="display: none;">
                                    @foreach($categories as $index => $cat)
                                    <label class="flex items-center gap-3 p-3 hover:bg-gray-50 cursor-pointer transition {{ $index > 0 ? 'border-t border-gray-50' : '' }}">
                                        <input type="checkbox" name="categories[]" value="{{ $cat }}" x-model="selectedCategories" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="text-sm font-medium text-gray-700 capitalize">{{ $cat }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-[#1D1D1F] mb-2">Kapasitas Minimum</label>
                            <input type="number" name="capacity" x-model="minCapacity" placeholder="Mis: 30" class="w-full bg-gray-50 border border-gray-200 text-[#1D1D1F] text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-3 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-[#1D1D1F] mb-2">Fasilitas Pendukung</label>
                            <div class="relative mt-2">
                                <button type="button" @click="openFacility = !openFacility" class="w-full bg-gray-50 border border-gray-200 text-[#1D1D1F] text-sm rounded-xl p-3 flex justify-between items-center text-left focus:ring-blue-500 focus:border-blue-500 transition">
                                    <span x-text="selectedFacilities.length === 0 ? 'Pilih Fasilitas...' : selectedFacilities.length + ' Fasilitas Dipilih'" class="truncate mr-2"></span>
                                    <span class="material-symbols-outlined text-gray-500 text-sm transition-transform duration-200" :class="openFacility ? 'rotate-180' : ''">expand_more</span>
                                </button>
                                
                                <div x-show="openFacility" @click.away="openFacility = false" x-transition.opacity.duration.200ms class="absolute z-10 w-full mt-2 bg-white border border-gray-100 rounded-xl shadow-lg max-h-60 overflow-y-auto" style="display: none;">
                                    @foreach($equipments as $index => $eq)
                                    <label class="flex items-center gap-3 p-3 hover:bg-gray-50 cursor-pointer transition {{ $index > 0 ? 'border-t border-gray-50' : '' }}">
                                        <input type="checkbox" name="equipments[]" value="{{ $eq }}" x-model="selectedFacilities" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="text-sm font-medium text-gray-700 capitalize">{{ $eq }}</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-8 flex justify-end gap-3">
                        <button type="reset" class="px-5 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-medium hover:bg-gray-50 transition text-sm">Reset</button>
                        <button type="button" @click="showAdvanced = false" class="bg-[#1D1D1F] text-white px-5 py-2.5 rounded-xl font-medium hover:bg-gray-800 transition text-sm">Terapkan Filter</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
        </div>

        @php
            $currentCategory = request('category', 'semua');
            $hasFilterQuery = request()->filled('categories') || request()->filled('equipments') || request()->filled('capacity');
            $hasSearchQuery = request()->filled('search');
            $isSearching = $hasSearchQuery || $hasFilterQuery;
        @endphp
        
        <div class="mt-4 flex md:justify-center gap-3 mb-16 overflow-x-auto hide-scroll pb-2">
            @if($isSearching)
                <a @click="sessionStorage.setItem('categoryClicked', 'true')" href="{{ route('public.catalog', array_filter(['category' => request('category')])) }}" class="px-5 py-2.5 rounded-full text-sm font-medium whitespace-nowrap transition bg-[#1D1D1F] text-white border border-transparent flex items-center gap-2">
                    <span class="material-symbols-outlined text-[16px]">search</span>
                    Hasil: {!! $hasSearchQuery ? '"' . request('search') . '"' : 'Filter Pencarian' !!}
                    <span class="material-symbols-outlined text-[16px] ml-1 hover:text-red-400">close</span>
                </a>
            @endif
            <a @click="sessionStorage.setItem('categoryClicked', 'true')" href="{{ route('public.catalog') }}" class="px-5 py-2.5 rounded-full text-sm font-medium whitespace-nowrap transition {{ $currentCategory === 'semua' && !$isSearching ? 'bg-[#1D1D1F] text-white border border-transparent' : 'bg-white border border-gray-200 text-[#1D1D1F] hover:border-gray-400' }}">Semua Fasilitas</a>
            @foreach($categories as $cat)
                <a @click="sessionStorage.setItem('categoryClicked', 'true')" href="{{ route('public.catalog', array_filter(['category' => $cat, 'search' => request('search')])) }}" class="px-5 py-2.5 rounded-full text-sm font-medium whitespace-nowrap transition capitalize {{ $currentCategory === $cat ? 'bg-[#1D1D1F] text-white border border-transparent' : 'bg-white border border-gray-200 text-[#1D1D1F] hover:border-gray-400' }}">{{ $cat }}</a>
            @endforeach
        </div>

        <!-- Grid Container (Animated) -->
        <div class="grid md:grid-cols-3 lg:grid-cols-4 gap-6 opacity-0"
             x-data="{
                localClass: 'opacity-0',
                init() {
                    let prev = sessionStorage.getItem('prevCatalogIndex');
                    let curr = {{ $subIndex }};
                    if (prev) {
                        if (curr > prev) this.localClass = 'animate-slide-in-right';
                        else if (curr < prev) this.localClass = 'animate-slide-in-left';
                        else this.localClass = 'animate-fade-in';
                    } else {
                        this.localClass = 'animate-fade-in';
                    }
                    sessionStorage.setItem('prevCatalogIndex', curr);
                }
             }"
             :class="localClass">
            @foreach ($facilities as $facility)
            <div class="bg-white rounded-2xl p-6 border border-gray-100 hover:shadow-[0_8px_30px_rgb(0,0,0,0.06)] transition duration-300 group cursor-pointer flex flex-col" @click="activeFacility = {{ json_encode($facility) }}; showModal = true">
                <div class="h-40 rounded-xl bg-gray-50 mb-5 flex items-center justify-center group-hover:bg-gray-100 transition overflow-hidden">
                    @if($facility['image'])
                        <img src="{{ asset('storage/' . $facility['image']) }}" alt="{{ $facility['name'] }}" class="w-full h-full object-cover">
                    @else
                        <span class="material-symbols-outlined text-5xl text-gray-300">meeting_room</span>
                    @endif
                </div>
                <h3 class="font-semibold text-[#1D1D1F] mb-1 leading-tight group-hover:text-blue-600 transition-colors duration-300">{{ $facility['name'] }}</h3>
                <p class="text-xs text-[#86868B] mb-4">{{ $facility['building'] }} • Kapasitas {{ $facility['capacity'] }}</p>
                <div class="flex items-center justify-between mt-auto">
                    @if($facility['status'] === 'locked')
                        <span class="flex items-center gap-1.5 text-xs font-medium text-red-600"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Perbaikan</span>
                    @else
                        <div></div>
                    @endif
                    <span class="material-symbols-outlined text-gray-300 group-hover:text-[#1D1D1F] -translate-x-1 group-hover:translate-x-0 transition-all duration-300 text-[18px]">arrow_forward</span>
                </div>
            </div>
            @endforeach
            
            @if($facilities->isEmpty())
            <div class="col-span-full py-20 text-center text-gray-500">
                <span class="material-symbols-outlined text-6xl mb-4">search_off</span>
                <p>Tidak ada fasilitas yang ditemukan.</p>
            </div>
            @endif
        </div>

        <!-- Pop-up Modal Detail Fasilitas -->
        <div x-show="showModal" class="fixed inset-0 z-50 flex items-center justify-center px-4" x-cloak>
            <!-- Backdrop -->
            <div x-show="showModal" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0" 
                 x-transition:enter-end="opacity-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100" 
                 x-transition:leave-end="opacity-0" 
                 class="fixed inset-0 bg-black/40 backdrop-blur-sm" 
                 @click="showModal = false">
            </div>

            <!-- Modal Content -->
            <div x-show="showModal" 
                 x-transition:enter="ease-out duration-300" 
                 x-transition:enter-start="opacity-0 translate-y-8 scale-95" 
                 x-transition:enter-end="opacity-100 translate-y-0 scale-100" 
                 x-transition:leave="ease-in duration-200" 
                 x-transition:leave-start="opacity-100 translate-y-0 scale-100" 
                 x-transition:leave-end="opacity-0 translate-y-8 scale-95" 
                 class="bg-white rounded-3xl shadow-2xl w-full max-w-2xl relative z-10 overflow-hidden flex flex-col max-h-[90vh]">
                
                <button @click="showModal = false" class="absolute top-4 right-4 z-20 bg-black/50 text-white p-2 rounded-full hover:bg-black/70 transition flex items-center justify-center">
                    <span class="material-symbols-outlined block text-[20px]">close</span>
                </button>

                <div class="h-64 bg-gray-50 relative shrink-0 border-b border-gray-100">
                    <template x-if="activeFacility && activeFacility.image">
                        <img :src="'/storage/' + activeFacility.image" :alt="activeFacility.name" class="w-full h-full object-contain">
                    </template>
                    <template x-if="!activeFacility || !activeFacility.image">
                        <div class="w-full h-full flex items-center justify-center">
                            <span class="material-symbols-outlined text-7xl text-gray-200">meeting_room</span>
                        </div>
                    </template>
                </div>

                <div class="p-8 overflow-y-auto">
                    <div class="flex items-start justify-between mb-2">
                        <h2 class="text-3xl font-bold text-[#1D1D1F] leading-tight" x-text="activeFacility ? activeFacility.name : ''"></h2>
                    </div>
                    <div class="text-[#86868B] text-sm mb-6 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[16px]">location_on</span>
                        <span x-text="activeFacility ? activeFacility.building : ''"></span>
                        <span>•</span>
                        <span class="material-symbols-outlined text-[16px]">group</span>
                        <span x-text="activeFacility ? activeFacility.capacity + ' Orang' : ''"></span>
                    </div>

                    <h3 class="text-lg font-semibold mb-2 text-[#1D1D1F]">Deskripsi</h3>
                    <p class="text-[#86868B] leading-relaxed mb-6 text-sm" x-text="activeFacility && activeFacility.desc ? activeFacility.desc : 'Tidak ada deskripsi tersedia.'"></p>

                    <h3 class="text-lg font-semibold mb-3 text-[#1D1D1F]">Fasilitas Pendukung</h3>
                    <div class="flex flex-wrap gap-2 mb-10">
                        <template x-if="activeFacility && activeFacility.equipment && activeFacility.equipment.length > 0">
                            <template x-for="item in activeFacility.equipment">
                                <span class="bg-gray-100 border border-gray-200 text-gray-700 px-3 py-1.5 rounded-full text-xs font-medium" x-text="item"></span>
                            </template>
                        </template>
                        <template x-if="!activeFacility || !activeFacility.equipment || activeFacility.equipment.length === 0">
                            <span class="text-sm text-gray-500 italic">Tidak ada rincian fasilitas pendukung.</span>
                        </template>
                    </div>

                    <a :href="'{{ route('public.availability') }}?search=' + encodeURIComponent(activeFacility ? activeFacility.name : '')" class="block w-full text-center bg-[#1D1D1F] text-white py-4 rounded-xl font-medium hover:bg-gray-800 transition shadow-lg shadow-black/10">
                        Cek Jadwal Ketersediaan
                    </a>
                </div>
            </div>
        </div>

    </div>
</x-public-layout>
