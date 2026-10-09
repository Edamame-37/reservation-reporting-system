<x-public-layout title="Matriks Jadwal Ketersediaan" active="availability">
    <div class="pt-24 pb-20 max-w-7xl mx-auto px-6">
        <h1 class="text-4xl font-bold tracking-tight mb-2">Matriks Jadwal.</h1>
        <p class="text-lg text-[#86868B] mb-8">Pantau ketersediaan slot waktu untuk hari ini secara transparan.</p>

        <div class="bg-white rounded-3xl border border-gray-100 p-8 shadow-[0_8px_30px_rgb(0,0,0,0.02)] overflow-x-auto">
            <div class="flex justify-between items-center mb-6 min-w-[800px]">
                <h2 class="font-semibold text-lg">Jadwal: <span class="text-[#86868B]">{{ date('d F Y') }}</span></h2>
                <div class="flex gap-4">
                    <div class="flex items-center gap-2 text-xs font-medium"><span class="w-3 h-3 rounded-full bg-gray-100 border border-gray-200"></span> Tersedia</div>
                    <div class="flex items-center gap-2 text-xs font-medium"><span class="w-3 h-3 rounded-full bg-red-100 border border-red-200"></span> Dipakai / Maintenance</div>
                </div>
            </div>

            <div class="min-w-[1000px]">
                <div class="grid grid-cols-[200px_repeat(13,1fr)] gap-2 mb-4 border-b border-gray-100 pb-4 text-xs font-medium text-[#86868B]">
                    <div>Nama Fasilitas</div>
                    <div class="text-center">07:00</div><div class="text-center">08:00</div>
                    <div class="text-center">09:00</div><div class="text-center">10:00</div>
                    <div class="text-center">11:00</div><div class="text-center">12:00</div>
                    <div class="text-center">13:00</div><div class="text-center">14:00</div>
                    <div class="text-center">15:00</div><div class="text-center">16:00</div>
                    <div class="text-center">17:00</div><div class="text-center">18:00</div>
                    <div class="text-center">19:00</div>
                </div>

                <div class="space-y-3">
                    @for ($i = 1; $i <= 5; $i++)
                    <div class="grid grid-cols-[200px_repeat(13,1fr)] gap-2 items-center">
                        <div class="text-sm font-medium text-[#1D1D1F]">Auditorium {{ $i }}</div>
                        @for ($j = 1; $j <= 13; $j++)
                        <div class="h-8 rounded-md w-full {{ ($j > 3 && $j < 6 && $i % 2 == 0) ? 'bg-red-50 border border-red-100' : 'bg-gray-50 border border-gray-100 hover:bg-gray-100 cursor-pointer transition' }}"></div>
                        @endfor
                    </div>
                    @endfor
                </div>
            </div>
        </div>
    </div>
</x-public-layout>
