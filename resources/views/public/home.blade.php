<x-public-layout title="Beranda Publik Fasilitas Kampus" active="home">
    <div class="pt-20 pb-20">
        <main class="px-6 max-w-5xl mx-auto text-center">
            <h1 class="text-5xl md:text-7xl tracking-tight leading-tight mb-6">
                <span class="block font-extrabold text-[#86868B] mb-0.5 md:mb-0.5 tracking-widest">CAVA</span>
                <span class="font-extrabold text-[#1D1D1F]">Reserve and Report</span>
            </h1>
            <p class="text-xl text-[#86868B] max-w-2xl mx-auto mb-10 font-light leading-relaxed">Optimalisasi fasilitas kampus dalam satu platform terpadu. Pesan ruang instan dan laporkan kendala seketika.</p>
            
            <form action="{{ route('public.catalog') }}" method="GET" class="max-w-2xl mx-auto relative" x-data="{ 
                showAdvanced: false,
                openCategory: false,
                selectedCategories: [],
                minCapacity: '',
                openFacility: false,
                selectedFacilities: [],
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
                <div class="relative group">
                    <div class="absolute -inset-1 bg-gradient-to-r from-blue-100 to-indigo-100 rounded-2xl blur opacity-25 group-hover:opacity-50 transition duration-1000 group-hover:duration-200"></div>
                    <div class="relative bg-white rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-2 flex items-center border border-gray-100">
                        <span class="material-symbols-outlined text-gray-400 ml-4 mr-2">search</span>
                        <input type="text" name="search" placeholder="Cari Auditorium, Lab, atau Kelas..." class="flex-1 py-4 px-2 border-0 focus:ring-0 focus:border-transparent outline-none text-lg bg-transparent">
                        <button type="button" @click="showAdvanced = !showAdvanced" class="text-gray-400 hover:text-[#1D1D1F] p-2 mr-2 transition rounded-lg hover:bg-gray-50 focus:outline-none flex items-center justify-center">
                            <span class="material-symbols-outlined text-[24px]">tune</span>
                        </button>
                        <button type="submit" class="bg-[#1D1D1F] text-white px-8 py-3 rounded-xl font-medium hover:bg-gray-800 transition">Cari</button>
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
                            
                            <template x-for="filter in allFilters.slice(0, 5)" :key="filter.type + '-' + filter.value">
                                <span x-transition.opacity.scale.90.duration.200ms 
                                      class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-[13px] font-medium border shadow-sm"
                                      :class="filter.type === 'category' ? 'bg-blue-50 text-blue-800 border-blue-200' : (filter.type === 'capacity' ? 'bg-purple-50 text-purple-800 border-purple-200' : 'bg-emerald-50 text-emerald-800 border-emerald-200')">
                                    <span x-text="filter.type === 'category' ? (filter.value === 'Auditorium' ? 'Auditorium & Aula' : (filter.value === 'Lab' ? 'Lab Komputer' : 'Kelas Terpadu')) : (filter.type === 'capacity' ? 'Min. ' + filter.value + ' Kursi' : (filter.value === 'Proyektor' ? 'Proyektor' : 'AC Central'))"></span>
                                    <button type="button" @click.prevent.stop="removeFilter(filter)" 
                                            class="focus:outline-none transition"
                                            :class="filter.type === 'category' ? 'text-blue-500 hover:text-blue-900' : (filter.type === 'capacity' ? 'text-purple-500 hover:text-purple-900' : 'text-emerald-500 hover:text-emerald-900')">
                                        <span class="material-symbols-outlined text-[14px] font-bold">close</span>
                                    </button>
                                </span>
                            </template>
                            
                            <span x-show="allFilters.length > 5" x-transition.opacity.scale.90.duration.200ms 
                                  class="inline-flex items-center px-3 py-1.5 rounded-lg bg-gray-100 text-gray-700 text-[13px] font-medium border border-gray-200 shadow-sm">
                                <span x-text="'+' + (allFilters.length - 5) + ' lainnya'"></span>
                            </span>

                            <button type="button" x-transition.opacity.duration.200ms @click.prevent.stop="selectedCategories = []; selectedFacilities = []; minCapacity = ''" class="text-[13px] font-medium text-gray-500 hover:text-red-600 underline px-2 py-1 transition ml-1">Hapus Semua</button>
                        </div>
                    </div>

                    <!-- Advanced Search Popup (Absolute positioning slides based on pillsHeight) -->
                    <div class="absolute left-0 right-0 w-full transition-all duration-300 ease-out pointer-events-auto"
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
                             <div class="flex justify-between items-center mb-6 pb-4 border-b border-gray-100">
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
                                    <label class="flex items-center gap-3 p-3 hover:bg-gray-50 cursor-pointer transition">
                                        <input type="checkbox" value="Auditorium" x-model="selectedCategories" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="text-sm font-medium text-gray-700">Auditorium & Aula</span>
                                    </label>
                                    <label class="flex items-center gap-3 p-3 hover:bg-gray-50 cursor-pointer transition border-t border-gray-50">
                                        <input type="checkbox" value="Lab" x-model="selectedCategories" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="text-sm font-medium text-gray-700">Laboratorium Komputer</span>
                                    </label>
                                    <label class="flex items-center gap-3 p-3 hover:bg-gray-50 cursor-pointer transition border-t border-gray-50">
                                        <input type="checkbox" value="Kelas" x-model="selectedCategories" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="text-sm font-medium text-gray-700">Ruang Kelas Terpadu</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-[#1D1D1F] mb-2">Kapasitas Minimum</label>
                            <input type="number" x-model="minCapacity" placeholder="Mis: 30" class="w-full bg-gray-50 border border-gray-200 text-[#1D1D1F] text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-3 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-[#1D1D1F] mb-2">Fasilitas Pendukung</label>
                            <div class="relative mt-2">
                                <button type="button" @click="openFacility = !openFacility" class="w-full bg-gray-50 border border-gray-200 text-[#1D1D1F] text-sm rounded-xl p-3 flex justify-between items-center text-left focus:ring-blue-500 focus:border-blue-500 transition">
                                    <span x-text="selectedFacilities.length === 0 ? 'Pilih Fasilitas...' : selectedFacilities.length + ' Fasilitas Dipilih'" class="truncate mr-2"></span>
                                    <span class="material-symbols-outlined text-gray-500 text-sm transition-transform duration-200" :class="openFacility ? 'rotate-180' : ''">expand_more</span>
                                </button>
                                
                                <div x-show="openFacility" @click.away="openFacility = false" x-transition.opacity.duration.200ms class="absolute z-10 w-full mt-2 bg-white border border-gray-100 rounded-xl shadow-lg max-h-60 overflow-y-auto" style="display: none;">
                                    <label class="flex items-center gap-3 p-3 hover:bg-gray-50 cursor-pointer transition">
                                        <input type="checkbox" value="Proyektor" x-model="selectedFacilities" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="text-sm font-medium text-gray-700">Proyektor & Layar</span>
                                    </label>
                                    <label class="flex items-center gap-3 p-3 hover:bg-gray-50 cursor-pointer transition border-t border-gray-50">
                                        <input type="checkbox" value="AC" x-model="selectedFacilities" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                        <span class="text-sm font-medium text-gray-700">AC Central</span>
                                    </label>
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
        </main>
        
        <section class="max-w-7xl mx-auto px-6 py-20 mt-14" x-data="{ 
            showModal: false, 
            activeFacility: null,
            get parsedEquipment() {
                if (!this.activeFacility || !this.activeFacility.equipment) return [];
                if (Array.isArray(this.activeFacility.equipment)) return this.activeFacility.equipment;
                try { return JSON.parse(this.activeFacility.equipment); } catch(e) { return []; }
            }
        }">
            <div class="flex justify-between items-end mb-12">
                <div>
                    <h2 class="text-3xl font-bold tracking-tight mb-2">Fasilitas Unggulan</h2>
                    <p class="text-[#86868B] text-lg">Ruang terbaik untuk produktivitas maksimal.</p>
                </div>
                <a href="{{ route('public.catalog') }}" class="text-blue-600 font-medium hover:underline text-sm">Lihat Semua →</a>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                @foreach($facilities as $facility)
                <div @click="activeFacility = {{ json_encode($facility) }}; showModal = true" class="bg-white rounded-3xl p-8 border border-gray-100 hover:shadow-[0_8px_30px_rgb(0,0,0,0.06)] transition duration-300 group cursor-pointer flex flex-col">
                    <div class="h-48 rounded-2xl bg-gray-50 mb-6 flex items-center justify-center group-hover:scale-[1.02] transition duration-500 overflow-hidden">
                        @if($facility->image_path)
                            <img src="{{ asset('storage/' . $facility->image_path) }}" alt="{{ $facility->name }}" class="w-full h-full object-contain">
                        @else
                            <span class="material-symbols-outlined text-6xl text-gray-200">meeting_room</span>
                        @endif
                    </div>
                    <div class="flex justify-between items-start mb-2 gap-2">
                        <h3 class="text-xl font-semibold leading-tight group-hover:text-blue-600 transition-colors duration-300">{{ $facility->name }}</h3>
                    </div>
                    <p class="text-[#86868B] text-sm mb-6">{{ $facility->building }} • {{ $facility->capacity }} Kursi</p>
                    <div class="flex items-center text-blue-600 text-sm font-medium opacity-0 group-hover:opacity-100 -translate-x-2 group-hover:translate-x-0 transition-all duration-300 mt-auto">
                        Lihat Detail <span class="material-symbols-outlined text-[16px] ml-1">arrow_forward</span>
                    </div>
                </div>
                @endforeach
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
                        <template x-if="activeFacility && activeFacility.image_path">
                            <img :src="'/storage/' + activeFacility.image_path" :alt="activeFacility.name" class="w-full h-full object-contain">
                        </template>
                        <template x-if="!activeFacility || !activeFacility.image_path">
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
                        <p class="text-[#86868B] leading-relaxed mb-6 text-sm" x-text="activeFacility && activeFacility.description ? activeFacility.description : 'Tidak ada deskripsi tersedia.'"></p>

                        <h3 class="text-lg font-semibold mb-3 text-[#1D1D1F]">Fasilitas Pendukung</h3>
                        <div class="flex flex-wrap gap-2 mb-10">
                            <template x-if="parsedEquipment.length > 0">
                                <template x-for="item in parsedEquipment">
                                    <span class="bg-gray-100 border border-gray-200 text-gray-700 px-3 py-1.5 rounded-full text-xs font-medium" x-text="item"></span>
                                </template>
                            </template>
                            <template x-if="parsedEquipment.length === 0">
                                <span class="text-sm text-gray-500 italic">Tidak ada rincian fasilitas pendukung.</span>
                            </template>
                        </div>

                        <a href="{{ route('public.availability') }}" class="block w-full text-center bg-[#1D1D1F] text-white py-4 rounded-xl font-medium hover:bg-gray-800 transition shadow-lg shadow-black/10">
                            Cek Jadwal Ketersediaan
                        </a>
                    </div>
                </div>
            </div>

        </section>

        <!-- Pusat Informasi Section (Bento Box Redesign) -->
        <section class="max-w-7xl mx-auto px-6 py-24 border-t border-gray-100">
            <!-- Top Header -->
            <div class="mb-10">
                <h2 class="text-3xl font-bold tracking-tight text-[#1D1D1F] mb-3">Pusat Bantuan</h2>
                <p class="text-[#86868B] text-lg leading-relaxed">Panduan dan regulasi penggunaan fasilitas.</p>
            </div>
            
            <!-- 2x2 Bento Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                
                <!-- Kebijakan Privasi -->
                <a href="{{ route('public.information') }}#kebijakan" class="group relative overflow-hidden bg-[#F9F9FB] rounded-[2rem] p-8 hover:bg-white hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all duration-500 flex items-center">
                    <div class="relative z-10 w-3/4">
                        <h3 class="text-xl font-bold text-[#1D1D1F] mb-2 group-hover:text-blue-600 transition-colors duration-300">Kebijakan Privasi</h3>
                        <p class="text-sm text-[#86868B]">Transparansi pengelolaan data Anda.</p>
                    </div>
                    <!-- Emote / Giant Icon -->
                    <div class="absolute right-0 inset-y-0 flex items-center pr-2 transform scale-[1.8] translate-x-6 group-hover:scale-100 group-hover:-translate-x-2 transition-all duration-700 ease-out">
                        <span class="material-symbols-outlined text-[100px] text-gray-200/50 group-hover:text-blue-50 transition-colors duration-700 font-light">shield_lock</span>
                    </div>
                </a>

                <!-- Syarat & Ketentuan -->
                <a href="{{ route('public.information') }}#syarat" class="group relative overflow-hidden bg-[#F9F9FB] rounded-[2rem] p-8 hover:bg-white hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all duration-500 flex items-center">
                    <div class="relative z-10 w-3/4">
                        <h3 class="text-xl font-bold text-[#1D1D1F] mb-2 group-hover:text-purple-600 transition-colors duration-300">Syarat & Ketentuan</h3>
                        <p class="text-sm text-[#86868B]">Aturan main penggunaan ruang.</p>
                    </div>
                    <div class="absolute right-0 inset-y-0 flex items-center pr-2 transform scale-[1.8] translate-x-6 group-hover:scale-100 group-hover:-translate-x-2 transition-all duration-700 ease-out">
                        <span class="material-symbols-outlined text-[100px] text-gray-200/50 group-hover:text-purple-50 transition-colors duration-700 font-light">gavel</span>
                    </div>
                </a>

                <!-- Panduan Peminjaman -->
                <a href="{{ route('public.information') }}#panduan" class="group relative overflow-hidden bg-[#F9F9FB] rounded-[2rem] p-8 hover:bg-white hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all duration-500 flex items-center">
                    <div class="relative z-10 w-3/4">
                        <h3 class="text-xl font-bold text-[#1D1D1F] mb-2 group-hover:text-emerald-600 transition-colors duration-300">Panduan Peminjaman</h3>
                        <p class="text-sm text-[#86868B]">Langkah demi langkah reservasi.</p>
                    </div>
                    <div class="absolute right-0 inset-y-0 flex items-center pr-2 transform scale-[1.8] translate-x-6 group-hover:scale-100 group-hover:-translate-x-2 transition-all duration-700 ease-out">
                        <span class="material-symbols-outlined text-[100px] text-gray-200/50 group-hover:text-emerald-50 transition-colors duration-700 font-light">menu_book</span>
                    </div>
                </a>

                <!-- Lapor Kerusakan -->
                <a href="{{ route('public.information') }}#lapor" class="group relative overflow-hidden bg-[#F9F9FB] rounded-[2rem] p-8 hover:bg-white hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all duration-500 flex items-center">
                    <div class="relative z-10 w-3/4">
                        <h3 class="text-xl font-bold text-[#1D1D1F] mb-2 group-hover:text-red-600 transition-colors duration-300">Lapor Kerusakan</h3>
                        <p class="text-sm text-[#86868B]">Pusat bantuan teknis fasilitas.</p>
                    </div>
                    <div class="absolute right-0 inset-y-0 flex items-center pr-2 transform scale-[1.8] translate-x-6 group-hover:scale-100 group-hover:-translate-x-2 transition-all duration-700 ease-out">
                        <span class="material-symbols-outlined text-[100px] text-gray-200/50 group-hover:text-red-50 transition-colors duration-700 font-light">support_agent</span>
                    </div>
                </a>
                
            </div>
        </section>
    </div>
</x-public-layout>
