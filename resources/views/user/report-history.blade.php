{{-- 
  NAMA FILE      : report-history.blade.php
  FUNGSIONALITAS : Halaman Lengkap Status & Riwayat Pelaporan Kerusakan (USR-05)
  DESKRIPSI      : Menampilkan daftar seluruh tiket laporan kerusakan fasilitas yang diajukan pengguna dari basis data dengan tab filter status, pencarian dinamis, modal foto bukti riil dari storage, dan catatan resolusi teknisi.
  CARA KERJA     : Menerima data $reports dan $counts dari ReportController@history, mendukung URL query filter, serta modal pratinjau foto via Alpine.js.
--}}

<x-app-layout title="Riwayat Lengkap Laporan Kerusakan" active="report-history">
    <div x-data="{
        showPhotoModal: false,
        selectedReport: null,
        openPhoto(r) {
            this.selectedReport = r;
            this.showPhotoModal = true;
        }
    }">

        {{-- Breadcrumb & Header --}}
        <div class="mb-6">
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-2 font-medium">
                <a href="{{ url('/user/dashboard') }}" class="hover:text-slate-900 transition">Dasbor Saya</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-slate-900">Status Laporan Kerusakan</span>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Status & Riwayat Pelaporan Kerusakan</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Pantau tahapan penanganan teknisi sarpras terhadap laporan kendala sarana yang Anda kirimkan.</p>
                </div>
                <a href="{{ url('/user/report-form') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-900 text-white text-xs sm:text-sm font-semibold hover:bg-slate-800 transition shadow-xs">
                    <span class="material-symbols-outlined text-[18px]">add_circle</span>
                    <span>Buat Laporan Baru</span>
                </a>
            </div>
        </div>

        {{-- Flash Session Sukses --}}
        @if (session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-center gap-3 shadow-xs">
                <span class="material-symbols-outlined text-emerald-600 text-[24px]">check_circle</span>
                <div>
                    <h4 class="text-sm font-bold">Berhasil!</h4>
                    <p class="text-xs text-emerald-700">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        {{-- Panel Filter & Pencarian --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 mb-6 shadow-xs">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                
                {{-- Tab Filter Status --}}
                <div class="flex items-center gap-1.5 overflow-x-auto pb-2 lg:pb-0 scrollbar-none text-xs font-semibold">
                    @php
                        $statusTabs = [
                            'all'      => ['label' => 'Semua', 'count' => $counts['all'] ?? 0],
                            'baru'     => ['label' => 'Baru', 'count' => $counts['baru'] ?? 0],
                            'diproses' => ['label' => 'Diproses', 'count' => $counts['diproses'] ?? 0],
                            'selesai'  => ['label' => 'Selesai', 'count' => $counts['selesai'] ?? 0],
                            'ditolak'  => ['label' => 'Ditolak', 'count' => $counts['ditolak'] ?? 0],
                        ];
                    @endphp

                    @foreach ($statusTabs as $key => $tab)
                        @php
                            $isActive = ($activeStatus === $key);
                            $url = request()->fullUrlWithQuery(['status' => $key, 'page' => 1]);
                        @endphp
                        <a href="{{ $url }}" 
                           class="px-3.5 py-2 rounded-xl transition flex items-center gap-2 whitespace-nowrap {{ $isActive ? 'bg-slate-900 text-white shadow-xs' : 'bg-slate-100/70 text-slate-600 hover:bg-slate-200/70' }}">
                            <span>{{ $tab['label'] }}</span>
                            <span class="px-1.5 py-0.5 rounded-full text-[10px] {{ $isActive ? 'bg-slate-800 text-white' : 'bg-slate-200 text-slate-700' }}">
                                {{ $tab['count'] }}
                            </span>
                        </a>
                    @endforeach
                </div>

                {{-- Kolom Pencarian Kata Kunci --}}
                <form action="{{ url()->current() }}" method="GET" class="relative w-full lg:w-72">
                    @if (request('status') && request('status') !== 'all')
                        <input type="hidden" name="status" value="{{ request('status') }}">
                    @endif
                    <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">search</span>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Cari kode tiket, fasilitas..." 
                           class="w-full pl-9 pr-8 py-2 rounded-xl border-slate-200 text-xs focus:border-slate-900 focus:ring-slate-900 placeholder-slate-400">
                    @if (request('search'))
                        <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                            <span class="material-symbols-outlined text-[16px]">close</span>
                        </a>
                    @endif
                </form>

            </div>
        </div>

        {{-- Tabel Laporan Lengkap --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden mb-8">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-200/80">
                            <th class="py-3 px-4">ID Tiket & Waktu</th>
                            <th class="py-3 px-4">Fasilitas & Kategori</th>
                            <th class="py-3 px-4">Deskripsi Kerusakan</th>
                            <th class="py-3 px-4">Status Penanganan</th>
                            <th class="py-3 px-4">Catatan Resolusi Teknisi</th>
                            <th class="py-3 px-4 text-right">Foto Bukti</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($reports as $report)
                            @php
                                $photoData = [
                                    'id'       => $report->report_code,
                                    'venue'    => $report->facility->name ?? 'Fasilitas Kampus',
                                    'desc'     => $report->description,
                                    'photoUrl' => $report->attachment_photo ? asset('storage/' . $report->attachment_photo) : null,
                                ];
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3.5 px-4">
                                    <div class="font-mono font-bold text-slate-800">{{ $report->report_code }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $report->created_at->translatedFormat('d M Y • H:i') }} WIB</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900">{{ $report->facility->name ?? 'Fasilitas Kampus' }}</div>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600 inline-block mt-0.5">
                                        {{ $report->category }}
                                    </span>
                                </td>
                                <td class="py-3.5 px-4 max-w-xs text-slate-700">
                                    {{ $report->description }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @if ($report->status === 'baru')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs bg-amber-50 text-amber-800 border border-amber-200/60 font-medium">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            Laporan Baru
                                        </span>
                                    @elseif ($report->status === 'diproses')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs bg-sky-50 text-sky-800 border border-sky-200/60 font-semibold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-pulse"></span>
                                            Sedang Diproses
                                        </span>
                                    @elseif ($report->status === 'selesai')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs bg-emerald-50 text-emerald-800 border border-emerald-200/60 font-semibold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                            Selesai Ditangani
                                        </span>
                                    @elseif ($report->status === 'ditolak')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs bg-rose-50 text-rose-700 border border-rose-200/60 font-medium">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            Ditolak
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-xs">
                                    @if ($report->resolution_note)
                                        <div class="text-slate-700 font-medium">{{ $report->resolution_note }}</div>
                                        <div class="text-[10px] text-slate-400 mt-0.5">
                                            Ditangani oleh: {{ $report->handler->name ?? 'Staf Sarpras' }}
                                            @if ($report->resolved_at)
                                                ({{ \Carbon\Carbon::parse($report->resolved_at)->translatedFormat('d M Y') }})
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-slate-400 italic text-[11px]">
                                            {{ $report->status === 'baru' ? 'Menunggu peninjauan teknisi sarpras' : 'Dalam penanganan teknisi' }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    @if ($report->attachment_photo)
                                        <button type="button" 
                                                @click="openPhoto({{ json_encode($photoData) }})" 
                                                class="px-2.5 py-1 rounded-lg border border-slate-200 text-slate-700 font-semibold hover:bg-slate-50 transition inline-flex items-center gap-1">
                                            <span class="material-symbols-outlined text-[14px]">image</span>
                                            <span>Lihat</span>
                                        </button>
                                    @else
                                        <span class="text-[11px] text-slate-400">Tidak ada</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 px-4 text-center">
                                    <div class="max-w-sm mx-auto flex flex-col items-center">
                                        <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                                            <span class="material-symbols-outlined text-[28px]">build_circle</span>
                                        </div>
                                        <h4 class="text-sm font-bold text-slate-900 mb-1">Belum Ada Riwayat Laporan</h4>
                                        <p class="text-xs text-slate-500 mb-4">
                                            @if (request('search') || request('status', 'all') !== 'all')
                                                Tidak ditemukan tiket pengaduan yang sesuai dengan kriteria filter atau pencarian Anda.
                                            @else
                                                Anda belum pernah mengajukan pengaduan kerusakan fasilitas kampus.
                                            @endif
                                        </p>
                                        <a href="{{ url('/user/report-form') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition">
                                            <span class="material-symbols-outlined text-[16px]">add</span>
                                            <span>Buat Laporan Baru</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Navigasi Paginasi Laravel --}}
            @if (isset($reports) && $reports->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $reports->links() }}
                </div>
            @endif
        </div>

        {{-- Modal Foto Bukti Riil --}}
        <div x-show="showPhotoModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="showPhotoModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 relative">
                <button type="button" @click="showPhotoModal = false" class="absolute top-4 right-4 p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100">
                    <span class="material-symbols-outlined">close</span>
                </button>

                <h3 class="text-sm font-bold text-slate-900 mb-1">Lampiran Foto Bukti Kerusakan</h3>
                <template x-if="selectedReport">
                    <div>
                        <p class="text-xs text-slate-500 mb-3" x-text="'Fasilitas: ' + selectedReport.venue"></p>
                        <div class="w-full h-72 bg-slate-100 rounded-2xl overflow-hidden border border-slate-200 mb-3 flex items-center justify-center relative">
                            <img :src="selectedReport.photoUrl" 
                                 :alt="'Bukti ' + selectedReport.id" 
                                 class="w-full h-full object-contain">
                        </div>
                        <div class="text-xs text-slate-600 bg-slate-50 p-3 rounded-xl border border-slate-100">
                            <span class="font-bold text-slate-800" x-text="selectedReport.id"></span>: 
                            <span x-text="selectedReport.desc"></span>
                        </div>
                    </div>
                </template>
            </div>
        </div>

    </div>
</x-app-layout>
