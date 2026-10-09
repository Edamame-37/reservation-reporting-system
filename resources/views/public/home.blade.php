<x-public-layout title="Beranda Publik Fasilitas Kampus" active="home">
    <div class="pt-20 pb-20">
        <main class="px-6 max-w-5xl mx-auto text-center">
            <h1 class="text-5xl md:text-7xl font-bold tracking-tight text-[#1D1D1F] leading-tight mb-6">Ruang Anda.<br>Waktu Anda.</h1>
            <p class="text-xl text-[#86868B] max-w-2xl mx-auto mb-10 font-light">Platform reservasi fasilitas kampus yang elegan, cepat, dan transparan. Temukan ruang yang sempurna untuk ide brilian Anda berikutnya.</p>
            
            <form action="{{ route('public.catalog') }}" method="GET" class="max-w-2xl mx-auto relative group">
                <div class="absolute -inset-1 bg-gradient-to-r from-blue-100 to-indigo-100 rounded-2xl blur opacity-25 group-hover:opacity-50 transition duration-1000 group-hover:duration-200"></div>
                <div class="relative bg-white rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-2 flex items-center border border-gray-100">
                    <span class="material-symbols-outlined text-gray-400 ml-4 mr-2">search</span>
                    <input type="text" name="search" placeholder="Cari Auditorium, Lab, atau Kelas..." class="flex-1 py-4 px-2 outline-none text-lg bg-transparent">
                    <button type="submit" class="bg-[#1D1D1F] text-white px-8 py-3 rounded-xl font-medium hover:bg-gray-800 transition">Cari</button>
                </div>
            </form>
        </main>
        
        <section class="max-w-7xl mx-auto px-6 py-20 mt-10">
            <div class="flex justify-between items-end mb-12">
                <div>
                    <h2 class="text-3xl font-bold tracking-tight mb-2">Fasilitas Unggulan.</h2>
                    <p class="text-[#86868B] text-lg">Ruang terbaik untuk produktivitas maksimal.</p>
                </div>
                <a href="{{ route('public.catalog') }}" class="text-blue-600 font-medium hover:underline text-sm">Lihat Semua →</a>
            </div>
            
            <div class="grid md:grid-cols-3 gap-8">
                @foreach($facilities as $facility)
                <div onclick="window.location.href='{{ route('public.availability') }}'" class="bg-white rounded-3xl p-8 border border-gray-100 hover:shadow-[0_8px_30px_rgb(0,0,0,0.04)] transition duration-300 group cursor-pointer flex flex-col">
                    <div class="h-48 rounded-2xl bg-gray-50 mb-6 flex items-center justify-center group-hover:scale-[1.02] transition duration-500 overflow-hidden">
                        @if($facility->image_path)
                            <img src="{{ asset('storage/' . $facility->image_path) }}" alt="{{ $facility->name }}" class="w-full h-full object-cover">
                        @else
                            <span class="material-symbols-outlined text-6xl text-gray-200">meeting_room</span>
                        @endif
                    </div>
                    <div class="flex justify-between items-start mb-2 gap-2">
                        <h3 class="text-xl font-semibold leading-tight">{{ $facility->name }}</h3>
                        @if($facility->status === 'dalam perbaikan')
                            <span class="flex items-center gap-1 text-xs font-medium text-red-600 bg-red-50 px-2 py-1 rounded-full whitespace-nowrap"><span class="w-1.5 h-1.5 rounded-full bg-red-500"></span> Perbaikan</span>
                        @else
                            <span class="flex items-center gap-1 text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-1 rounded-full whitespace-nowrap"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Tersedia</span>
                        @endif
                    </div>
                    <p class="text-[#86868B] text-sm mb-6">{{ $facility->building }} • {{ $facility->capacity }} Kursi</p>
                    <div class="flex items-center text-blue-600 text-sm font-medium opacity-0 group-hover:opacity-100 transition-opacity mt-auto">
                        Lihat Jadwal <span class="material-symbols-outlined text-[16px] ml-1">arrow_forward</span>
                    </div>
                </div>
                @endforeach
            </div>
        </section>
    </div>
</x-public-layout>
