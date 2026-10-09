<x-public-layout title="Matriks Jadwal Ketersediaan" active="availability">
    <div class="pt-24 pb-20 max-w-7xl mx-auto px-6" x-data="availabilityMatrix()" x-init="initMatrix('{{ date('Y-m-d') }}')">
        <h1 class="text-4xl font-bold tracking-tight mb-2">Matriks Jadwal.</h1>
        <p class="text-lg text-[#86868B] mb-8">Pantau ketersediaan slot waktu secara transparan.</p>

        <div class="bg-white rounded-3xl border border-gray-100 p-8 shadow-[0_8px_30px_rgb(0,0,0,0.02)] overflow-x-auto">
            <div class="flex justify-between items-center mb-6 min-w-[800px]">
                <div class="flex items-center gap-4">
                    <h2 class="font-semibold text-lg">Jadwal: <span class="text-[#86868B]">{{ date('d F Y') }}</span></h2>
                    <span x-show="loading" class="text-xs text-blue-500 font-medium animate-pulse">Memuat data live...</span>
                </div>
                <div class="flex gap-4">
                    <div class="flex items-center gap-2 text-xs font-medium"><span class="w-3 h-3 rounded-full bg-gray-100 border border-gray-200"></span> Tersedia</div>
                    <div class="flex items-center gap-2 text-xs font-medium"><span class="w-3 h-3 rounded-full bg-red-100 border border-red-200"></span> Dipakai / Maintenance</div>
                </div>
            </div>

            <div class="min-w-[1000px]">
                <div class="grid grid-cols-[250px_repeat(26,1fr)] gap-1 mb-4 border-b border-gray-100 pb-4 text-xs font-medium text-[#86868B]">
                    <div>Nama Fasilitas</div>
                    <template x-for="time in timeLabels">
                        <div class="text-center" x-text="time"></div>
                    </template>
                </div>

                <div class="space-y-3">
                    @foreach ($facilities as $f)
                    <div class="grid grid-cols-[250px_repeat(26,1fr)] gap-1 items-center hover:bg-gray-50 p-1 rounded-lg transition">
                        <div class="pr-4">
                            <div class="text-sm font-semibold text-[#1D1D1F] leading-tight">{{ $f['name'] }}</div>
                            <div class="text-[10px] text-gray-500">{{ $f['building'] }}</div>
                        </div>
                        
                        <template x-for="timeSlot in timeSlots">
                            <div class="h-8 rounded-md w-full transition" 
                                 :class="isBooked('{{ $f['id'] }}', timeSlot) || {{ $f['locked'] ? 'true' : 'false' }} ? 'bg-red-50 border border-red-100' : 'bg-gray-50 border border-gray-200'"
                                 :title="timeSlot">
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
