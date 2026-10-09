<x-public-layout title="Katalog Fasilitas" active="catalog">
    <div class="pt-24 pb-20 max-w-7xl mx-auto px-6">
        <h1 class="text-4xl font-bold tracking-tight mb-2">Katalog Fasilitas.</h1>
        <p class="text-lg text-[#86868B] mb-8">Temukan ruang yang sesuai dengan kebutuhan spesifik kegiatan Anda.</p>
        
        <div class="flex gap-3 mb-10 overflow-x-auto hide-scroll pb-2">
            <a href="{{ url('/public/catalog') }}" class="px-5 py-2.5 rounded-full bg-[#1D1D1F] text-white text-sm font-medium whitespace-nowrap">Semua Fasilitas</a>
            <a href="{{ url('/public/catalog?type=Auditorium') }}" class="px-5 py-2.5 rounded-full bg-white border border-gray-200 text-[#1D1D1F] hover:border-gray-400 transition text-sm font-medium whitespace-nowrap">Auditorium & Aula</a>
            <a href="{{ url('/public/catalog?type=Lab') }}" class="px-5 py-2.5 rounded-full bg-white border border-gray-200 text-[#1D1D1F] hover:border-gray-400 transition text-sm font-medium whitespace-nowrap">Laboratorium Komputer</a>
            <a href="{{ url('/public/catalog?type=Kelas') }}" class="px-5 py-2.5 rounded-full bg-white border border-gray-200 text-[#1D1D1F] hover:border-gray-400 transition text-sm font-medium whitespace-nowrap">Ruang Kelas Terpadu</a>
        </div>

        <div class="grid md:grid-cols-3 lg:grid-cols-4 gap-6">
            @for ($i = 1; $i <= 8; $i++)
            <div class="bg-white rounded-2xl p-6 border border-gray-100 hover:shadow-[0_8px_30px_rgb(0,0,0,0.06)] transition duration-300 group cursor-pointer" onclick="window.location.href='{{ url('/public/availability') }}'">
                <div class="h-40 rounded-xl bg-gray-50 mb-5 flex items-center justify-center group-hover:bg-gray-100 transition">
                    <span class="material-symbols-outlined text-5xl text-gray-300">meeting_room</span>
                </div>
                <h3 class="font-semibold text-[#1D1D1F] mb-1">Ruang Seminar B{{ $i }}</h3>
                <p class="text-xs text-[#86868B] mb-4">Gedung B • Kapasitas 40 Orang</p>
                <div class="flex items-center justify-between">
                    <span class="flex items-center gap-1.5 text-xs font-medium text-emerald-600"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Tersedia</span>
                    <span class="material-symbols-outlined text-gray-300 group-hover:text-[#1D1D1F] transition text-[18px]">arrow_forward</span>
                </div>
            </div>
            @endfor
        </div>
    </div>
</x-public-layout>
