<x-public-layout title="Beranda Publik Fasilitas Kampus" active="home">
    <div class="pt-20 pb-20">
        <main class="px-6 max-w-5xl mx-auto text-center">
            <h1 class="text-5xl md:text-7xl font-bold tracking-tight text-[#1D1D1F] leading-tight mb-6">Ruang Anda.<br>Waktu Anda.</h1>
            <p class="text-xl text-[#86868B] max-w-2xl mx-auto mb-10 font-light">Platform reservasi fasilitas kampus yang elegan, cepat, dan transparan. Temukan ruang yang sempurna untuk ide brilian Anda berikutnya.</p>
            
            <form action="{{ route('public.catalog') }}" method="GET" class="max-w-2xl mx-auto relative group" x-data="{ showAdvanced: false }">
                <div class="absolute -inset-1 bg-gradient-to-r from-blue-100 to-indigo-100 rounded-2xl blur opacity-25 group-hover:opacity-50 transition duration-1000 group-hover:duration-200"></div>
                <div class="relative bg-white rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-2 flex items-center border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400 ml-4 mr-2">search</span>
                    <input type="text" name="search" placeholder="Cari Auditorium, Lab, atau Kelas..." class="flex-1 py-4 px-2 border-0 focus:ring-0 focus:border-transparent outline-none text-lg bg-transparent">
                    <button type="button" @click="showAdvanced = !showAdvanced" class="text-gray-400 hover:text-[#1D1D1F] p-2 mr-2 transition rounded-lg hover:bg-gray-50 focus:outline-none flex items-center justify-center">
                        <span class="material-symbols-outlined text-[24px]">tune</span>
                    </button>
                    <button type="submit" class="bg-[#1D1D1F] text-white px-8 py-3 rounded-xl font-medium hover:bg-gray-800 transition">Cari</button>
                </div>

                <!-- TODO: Hardcoded advanced search panel, sesuaikan dengan rute dan backend nanti -->
                <div x-show="showAdvanced" 
                     @click.away="showAdvanced = false"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 translate-y-2"
                     class="absolute top-full left-0 right-0 mt-4 bg-white rounded-2xl shadow-xl border border-gray-100 p-6 z-50 text-left" style="display: none;">
                    <div class="flex justify-between items-center mb-5 pb-4 border-b border-gray-100">
                        <h3 class="font-semibold text-lg text-[#1D1D1F]">Pencarian Spesifik</h3>
                        <button type="button" @click="showAdvanced = false" class="text-gray-400 hover:text-gray-600"><span class="material-symbols-outlined text-xl">close</span></button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label class="block text-sm font-semibold text-[#1D1D1F] mb-2">Kategori Fasilitas</label>
                            <select class="w-full bg-gray-50 border border-gray-200 text-[#1D1D1F] text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-3 outline-none">
                                <option>Semua Kategori</option>
                                <option>Auditorium & Aula</option>
                                <option>Laboratorium Komputer</option>
                                <option>Ruang Kelas Terpadu</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-[#1D1D1F] mb-2">Kapasitas Minimum (Kursi)</label>
                            <input type="number" placeholder="Mis: 30" class="w-full bg-gray-50 border border-gray-200 text-[#1D1D1F] text-sm rounded-xl focus:ring-blue-500 focus:border-blue-500 block p-3 outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-[#1D1D1F] mb-2">Fasilitas Pendukung</label>
                            <div class="flex flex-col gap-2 mt-2">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="text-sm font-medium text-gray-700">Proyektor & Layar</span>
                                </label>
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500">
                                    <span class="text-sm font-medium text-gray-700">AC Central</span>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="mt-8 flex justify-end gap-3">
                        <button type="reset" class="px-5 py-2.5 rounded-xl border border-gray-200 text-gray-600 font-medium hover:bg-gray-50 transition text-sm">Reset</button>
                        <button type="button" @click="showAdvanced = false" class="bg-[#1D1D1F] text-white px-5 py-2.5 rounded-xl font-medium hover:bg-gray-800 transition text-sm">Terapkan Filter</button>
                    </div>
                </div>
            </form>
        </main>
        
        <section class="max-w-7xl mx-auto px-6 py-20 mt-10" x-data="{ 
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
                    <h2 class="text-3xl font-bold tracking-tight mb-2">Fasilitas Unggulan.</h2>
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
                        <h3 class="text-xl font-semibold leading-tight">{{ $facility->name }}</h3>
                    </div>
                    <p class="text-[#86868B] text-sm mb-6">{{ $facility->building }} • {{ $facility->capacity }} Kursi</p>
                    <div class="flex items-center text-blue-600 text-sm font-medium opacity-0 group-hover:opacity-100 transition-opacity mt-auto">
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
    </div>
</x-public-layout>
