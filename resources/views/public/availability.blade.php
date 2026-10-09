<x-public-layout title="Matriks Jadwal Ketersediaan" active="availability">
    <div class="pt-24 pb-20 max-w-7xl mx-auto px-6" x-data="availabilityMatrix()" x-init="initMatrix('{{ date('Y-m-d') }}')">
        <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-6 mb-24">
            <div>
                <h1 class="text-4xl font-bold tracking-tight mb-2">Matriks Jadwal.</h1>
                <p class="text-lg text-[#86868B]">Pantau ketersediaan slot waktu secara transparan.</p>
            </div>

            <form action="{{ route('public.availability') }}" method="GET" class="w-full md:w-96 relative group shrink-0" x-data="{ 
                showAdvanced: false,
                openCategory: false,
                selectedCategories: [],
                selectedDate: '',
                minCapacity: '',
                openFacility: false,
                selectedFacilities: [],
                get allFilters() {
                    let filters = [];
                    if (this.selectedDate !== '') filters.push({ type: 'date', value: this.selectedDate });
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
                    } else if (filter.type === 'date') {
                        this.selectedDate = '';
                    }
                }
            }">
                <div class="relative group">
                    @if(request()->has('category') && request('category') !== 'semua')
                        <input type="hidden" name="category" value="{{ request('category') }}">
                    @endif
                    <div class="absolute -inset-1 bg-gradient-to-r from-blue-100 to-indigo-100 rounded-2xl blur opacity-25 group-hover:opacity-50 transition duration-1000 group-hover:duration-200"></div>
                    <div class="relative bg-white rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-1.5 flex items-center border border-gray-100">
                        <span class="material-symbols-outlined text-gray-400 ml-3 mr-2 text-[20px]">search</span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama ruang, gedung..." class="flex-1 py-2.5 px-2 border-0 focus:ring-0 focus:border-transparent outline-none text-base bg-transparent">
                        <button type="button" @click="showAdvanced = !showAdvanced" class="text-gray-400 hover:text-[#1D1D1F] p-1 mr-1 transition rounded-lg hover:bg-gray-50 focus:outline-none flex items-center justify-center">
                            <span class="material-symbols-outlined text-[20px]">tune</span>
                        </button>
                        <button type="submit" class="bg-[#1D1D1F] text-white px-5 py-2.5 rounded-xl text-sm font-medium hover:bg-gray-800 transition">Cari</button>
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
                                      :class="filter.type === 'category' ? 'bg-blue-50 text-blue-800 border-blue-200' : (filter.type === 'capacity' ? 'bg-purple-50 text-purple-800 border-purple-200' : (filter.type === 'date' ? 'bg-amber-50 text-amber-800 border-amber-200' : 'bg-emerald-50 text-emerald-800 border-emerald-200'))">
                                    <span x-text="filter.type === 'category' ? (filter.value === 'Auditorium' ? 'Auditorium' : (filter.value === 'Lab' ? 'Lab Komputer' : 'Kelas')) : (filter.type === 'capacity' ? 'Min. ' + filter.value + ' Kursi' : (filter.type === 'date' ? 'Tgl: ' + filter.value : (filter.value === 'Proyektor' ? 'Proyektor' : 'AC Central')))"></span>
                                    <button type="button" @click.prevent.stop="removeFilter(filter)" 
                                            class="focus:outline-none transition"
                                            :class="filter.type === 'category' ? 'text-blue-500 hover:text-blue-900' : (filter.type === 'capacity' ? 'text-purple-500 hover:text-purple-900' : (filter.type === 'date' ? 'text-amber-500 hover:text-amber-900' : 'text-emerald-500 hover:text-emerald-900'))">
                                        <span class="material-symbols-outlined text-[14px] font-bold">close</span>
                                    </button>
                                </span>
                            </template>
                            
                            <span x-show="allFilters.length > 3" x-transition.opacity.scale.90.duration.200ms 
                                  class="inline-flex items-center px-3 py-1.5 rounded-lg bg-gray-100 text-gray-700 text-[13px] font-medium border border-gray-200 shadow-sm">
                                <span x-text="'+' + (allFilters.length - 3) + ' lainnya'"></span>
                            </span>

                            <button type="button" x-transition.opacity.duration.200ms @click.prevent.stop="selectedCategories = []; selectedFacilities = []; minCapacity = ''; selectedDate = ''" class="text-[13px] font-medium text-gray-500 hover:text-red-600 underline px-2 py-1 transition ml-1">Hapus Semua</button>
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
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label class="block text-sm font-semibold text-[#1D1D1F] mb-2">Tanggal Jadwal</label>
                                    <input type="date" x-model="selectedDate" class="w-full bg-gray-50 border border-gray-200 text-[#1D1D1F] text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-3 outline-none">
                                </div>
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
        </div>

        <div class="bg-white rounded-3xl border border-gray-100 p-8 shadow-[0_8px_30px_rgb(0,0,0,0.02)] overflow-x-auto">
            <div class="flex justify-between items-center mb-6 min-w-[800px]">
                <div class="flex items-center gap-4">
                    <h2 class="font-semibold text-lg">Jadwal: <span class="text-[#86868B]" x-text="formatDate(currentDate)"></span></h2>
                    <span x-show="loading" class="text-xs text-blue-500 font-medium animate-pulse">Memuat data live...</span>
                </div>
                <div class="flex gap-4">
                    <div class="flex items-center gap-2 text-xs font-medium"><span class="w-3 h-3 rounded-sm bg-emerald-400 border border-emerald-500"></span> Tersedia</div>
                    <div class="flex items-center gap-2 text-xs font-medium"><span class="w-3 h-3 rounded-sm bg-red-400 border border-red-500"></span> Terpakai</div>
                    <div class="flex items-center gap-2 text-xs font-medium"><span class="w-3 h-3 rounded-sm bg-gray-300 border border-gray-400"></span> Maintenance</div>
                </div>
            </div>

            <div class="min-w-[1000px]">
                <div class="grid grid-cols-[250px_repeat(26,1fr)] gap-0 mb-4 border-b border-gray-100 pb-4 text-xs font-medium text-[#86868B]">
                    <div class="pr-4">Nama Fasilitas</div>
                    <template x-for="(time, index) in timeLabels">
                        <div class="text-center relative" :class="index > 0 ? 'border-l border-gray-200' : ''">
                            <span x-text="time === '' ? '30' : time" :class="time === '' ? 'text-[9px] absolute -left-2 top-0.5 bg-white px-1' : ''"></span>
                        </div>
                    </template>
                </div>

                <div class="space-y-3">
                    @foreach ($facilities as $f)
                    <div class="grid grid-cols-[250px_repeat(26,1fr)] gap-0 items-center hover:bg-gray-50 p-1 rounded-lg transition border border-transparent hover:border-gray-100">
                        <div class="pr-4">
                            <div class="text-sm font-semibold text-[#1D1D1F] leading-tight">{{ $f['name'] }}</div>
                            <div class="text-[10px] text-gray-500">{{ $f['building'] }}</div>
                        </div>
                        
                        <template x-for="(timeSlot, index) in timeSlots">
                            <div class="h-8 w-full transition border-r border-white/50" 
                                 :class="[
                                    index === 0 ? 'rounded-l-md' : '',
                                    index === timeSlots.length - 1 ? 'rounded-r-md border-r-0' : '',
                                    {{ $f['locked'] ? 'true' : 'false' }} ? 'bg-gray-300 hover:bg-gray-400' : (isBooked('{{ $f['id'] }}', timeSlot) ? 'bg-red-400 hover:bg-red-500' : 'bg-emerald-400 hover:bg-emerald-500')
                                 ]"
                                 :title="timeSlot + ' - ' + ({{ $f['locked'] ? 'true' : 'false' }} ? 'Maintenance' : (isBooked('{{ $f['id'] }}', timeSlot) ? 'Terpakai' : 'Tersedia'))">
                            </div>
                        </template>
                    </div>
                    @endforeach
                    
                    @if($facilities->isEmpty())
                    <div class="py-10 text-center text-gray-500 text-sm">
                        Belum ada fasilitas yang terdaftar.
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('availabilityMatrix', () => ({
                currentDate: '',
                matrixData: {},
                loading: true,
                // Label jam untuk header (hanya jam penuh untuk tampilan)
                timeLabels: [
                    '07:00','','08:00','','09:00','','10:00','','11:00','','12:00','','13:00','',
                    '14:00','','15:00','','16:00','','17:00','','18:00','','19:00',''
                ],
                // Slot waktu per 30 menit (26 slot)
                timeSlots: [
                    '07:00','07:30','08:00','08:30','09:00','09:30','10:00','10:30','11:00','11:30',
                    '12:00','12:30','13:00','13:30','14:00','14:30','15:00','15:30','16:00','16:30',
                    '17:00','17:30','18:00','18:30','19:00','19:30'
                ],
                
                initMatrix(date) {
                    this.currentDate = date;
                    this.fetchMatrix();
                },

                formatDate(dateStr) {
                    if(!dateStr) return '';
                    const d = new Date(dateStr);
                    return new Intl.DateTimeFormat('id-ID', { day: '2-digit', month: 'long', year: 'numeric' }).format(d);
                },
                
                async fetchMatrix() {
                    this.loading = true;
                    try {
                        const response = await fetch(`/api/availability/matrix/${this.currentDate}`);
                        if (response.ok) {
                            this.matrixData = await response.json();
                        } else {
                            console.error('Failed to fetch matrix data');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                    }
                    this.loading = false;
                },
                
                isBooked(facilityId, slotTime) {
                    if (!this.matrixData[facilityId]) return false;
                    return this.matrixData[facilityId].includes(slotTime);
                }
            }))
        })
    </script>
</x-public-layout>
