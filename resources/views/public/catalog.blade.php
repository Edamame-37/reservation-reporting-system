<x-public-layout title="Katalog Fasilitas" active="catalog">
    <div class="pt-24 pb-20 max-w-7xl mx-auto px-6" x-data="{ showModal: false, activeFacility: null }">
        <h1 class="text-4xl font-bold tracking-tight mb-2">Katalog Fasilitas.</h1>
        <p class="text-lg text-[#86868B] mb-8">Temukan ruang yang sesuai dengan kebutuhan spesifik kegiatan Anda.</p>
        
        <form action="{{ route('public.catalog') }}" method="GET" class="max-w-3xl mb-8 relative group">
            @if(request()->has('category') && request('category') !== 'semua')
                <input type="hidden" name="category" value="{{ request('category') }}">
            @endif
            <div class="absolute -inset-1 bg-gradient-to-r from-blue-100 to-indigo-100 rounded-2xl blur opacity-25 group-hover:opacity-50 transition duration-1000 group-hover:duration-200"></div>
            <div class="relative bg-white rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-2 flex items-center border border-gray-100">
                <span class="material-symbols-outlined text-gray-400 ml-4 mr-2">search</span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama ruang, gedung, atau alat..." class="flex-1 py-4 px-2 border-0 focus:ring-0 focus:border-transparent outline-none text-lg bg-transparent">
                <button type="submit" class="bg-[#1D1D1F] text-white px-8 py-3 rounded-xl font-medium hover:bg-gray-800 transition">Cari</button>
            </div>
        </form>

        <div class="flex gap-3 mb-10 overflow-x-auto hide-scroll pb-2">
            <a href="{{ route('public.catalog', array_filter(['search' => request('search')])) }}" class="px-5 py-2.5 rounded-full bg-[#1D1D1F] text-white text-sm font-medium whitespace-nowrap">Semua Fasilitas</a>
            <a href="{{ route('public.catalog', array_filter(['category' => 'Auditorium', 'search' => request('search')])) }}" class="px-5 py-2.5 rounded-full bg-white border border-gray-200 text-[#1D1D1F] hover:border-gray-400 transition text-sm font-medium whitespace-nowrap">Auditorium & Aula</a>
            <a href="{{ route('public.catalog', array_filter(['category' => 'Lab', 'search' => request('search')])) }}" class="px-5 py-2.5 rounded-full bg-white border border-gray-200 text-[#1D1D1F] hover:border-gray-400 transition text-sm font-medium whitespace-nowrap">Laboratorium Komputer</a>
            <a href="{{ route('public.catalog', array_filter(['category' => 'Kelas', 'search' => request('search')])) }}" class="px-5 py-2.5 rounded-full bg-white border border-gray-200 text-[#1D1D1F] hover:border-gray-400 transition text-sm font-medium whitespace-nowrap">Ruang Kelas Terpadu</a>
        </div>

        <div class="grid md:grid-cols-3 lg:grid-cols-4 gap-6">
            @foreach ($facilities as $facility)
            <div class="bg-white rounded-2xl p-6 border border-gray-100 hover:shadow-[0_8px_30px_rgb(0,0,0,0.06)] transition duration-300 group cursor-pointer flex flex-col" @click="activeFacility = {{ json_encode($facility) }}; showModal = true">
                <div class="h-40 rounded-xl bg-gray-50 mb-5 flex items-center justify-center group-hover:bg-gray-100 transition overflow-hidden">
                    @if($facility['image'])
                        <img src="{{ asset('storage/' . $facility['image']) }}" alt="{{ $facility['name'] }}" class="w-full h-full object-cover">
                    @else
                        <span class="material-symbols-outlined text-5xl text-gray-300">meeting_room</span>
                    @endif
                </div>
                <h3 class="font-semibold text-[#1D1D1F] mb-1 leading-tight">{{ $facility['name'] }}</h3>
                <p class="text-xs text-[#86868B] mb-4">{{ $facility['building'] }} • Kapasitas {{ $facility['capacity'] }}</p>
                <div class="flex items-center justify-between mt-auto">
                    @if($facility['status'] === 'locked')
                        <span class="flex items-center gap-1.5 text-xs font-medium text-red-600"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Perbaikan</span>
                    @else
                        <span class="flex items-center gap-1.5 text-xs font-medium text-emerald-600"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Tersedia ({{ $facility['availableSlots'] }} slot)</span>
                    @endif
                    <span class="material-symbols-outlined text-gray-300 group-hover:text-[#1D1D1F] transition text-[18px]">arrow_forward</span>
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

                    <a href="{{ route('public.availability') }}" class="block w-full text-center bg-[#1D1D1F] text-white py-4 rounded-xl font-medium hover:bg-gray-800 transition shadow-lg shadow-black/10">
                        Cek Jadwal Ketersediaan
                    </a>
                </div>
            </div>
        </div>

    </div>
</x-public-layout>
