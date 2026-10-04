{{-- 
  NAMA FILE      : report-management.blade.php
  FUNGSIONALITAS : Lembar Kerja Manajemen Tiket Kerusakan Fasilitas & Pemblokiran Ruang Petugas Sarpras
  DESKRIPSI      : Menampilkan seluruh antrean tiket kerusakan fasilitas kampus, mengubah status progres (Baru/Diproses/Selesai/Ditolak),
                   menginput catatan resolusi teknisi, serta mengunci fasilitas ke status 'Dalam Perbaikan' / Maintenance Mode secara profesional.
  CARA KERJA     : Memanfaatkan layout <x-petugas-layout active="report-management">, mengelola modal pembaruan status dan saklar toggle
                   pemblokiran jadwal maintenance via Alpine.js/Blade form, serta memicu pengiriman PATCH request ke endpoint operasional petugas.
--}}

@php
    $reports = $reports ?? collect([]);
    $totalCount = $totalCount ?? $reports->count();
    $newCount = $newCount ?? $reports->where('status', 'baru')->count();
    $inProgressCount = $inProgressCount ?? $reports->where('status', 'diproses')->count();
    $resolvedCount = $resolvedCount ?? $reports->where('status', 'selesai')->count();
    $rejectedCount = $rejectedCount ?? $reports->where('status', 'ditolak')->count();
    $lockedFacilityCount = $lockedFacilityCount ?? $reports->filter(function($r) {
        return !empty($r->is_facility_locked) || ($r->facility?->status === 'dalam perbaikan');
    })->pluck('facility_id')->unique()->count();
@endphp

<x-petugas-layout title="Manajemen Tiket Kerusakan Fasilitas" active="report-management">
    <div x-data="{
        showStatusModal: false,
        showPhotoModal: false,
        photoUrl: '',
        activeTab: 'semua',
        search: '',
        selectedReport: {
            id: '',
            code: '',
            venue: '',
            desc: '',
            status: 'baru',
            facilityLocked: false,
            resolutionNote: ''
        },
        openUpdate(id, code, venue, desc, status, locked, note) {
            this.selectedReport = {
                id: id,
                code: code,
                venue: venue,
                desc: desc,
                status: status,
                facilityLocked: Boolean(locked),
                resolutionNote: note || ''
            };
            this.showStatusModal = true;
        },
        openPhoto(url) {
            this.photoUrl = url;
            this.showPhotoModal = true;
        }
    }" class="flex flex-col gap-6">

        {{-- 1. Notifikasi Flash Message --}}
        @if(session('success'))
            <div class="p-3.5 rounded-xl bg-slate-900 text-white text-xs flex items-center justify-between shadow-2xs">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-emerald-400">check_circle</span>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-slate-400 hover:text-white">
                    <span class="material-symbols-outlined text-[16px]">close</span>
                </button>
            </div>
        @endif

        @if(session('error') || (isset($errors) && $errors->any()))
            <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center justify-between shadow-2xs">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-rose-600">error</span>
                    <span class="font-medium">
                        {{ session('error') ?? ($errors->first() ?? 'Terjadi kesalahan') }}
                    </span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-rose-500 hover:text-rose-800">
                    <span class="material-symbols-outlined text-[16px]">close</span>
                </button>
            </div>
        @endif

        {{-- 2. Header Halaman Profesional --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200/80">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Manajemen Laporan Kerusakan & Fasilitas</h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    Penanganan tiket keluhan sarana kampus, pembaruan status teknisi, dan pengaturan status perbaikan fasilitas.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-medium text-slate-700 shadow-2xs">
                    Total: <strong class="text-slate-900 font-mono">{{ $totalCount }}</strong> Laporan
                </span>
                <span class="px-3 py-1.5 rounded-lg bg-white border border-slate-200 text-xs font-medium text-slate-700 shadow-2xs flex items-center gap-1.5">
                    <span class="w-1.5 h-1.5 rounded-full {{ $lockedFacilityCount > 0 ? 'bg-rose-500' : 'bg-slate-400' }}"></span>
                    <span>Mode Perbaikan: <strong class="text-slate-900 font-mono">{{ $lockedFacilityCount }}</strong> Ruang</span>
                </span>
            </div>
        </div>

        {{-- 3. Filter & Search Bar Server-Side --}}
        <div class="bg-white rounded-xl border border-slate-200/80 p-3 shadow-2xs flex flex-col md:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-1 p-1 bg-slate-100 rounded-lg text-xs font-medium w-full md:w-auto overflow-x-auto">
                <a href="{{ route('petugas.report-management', ['status' => 'semua', 'search' => $search ?? '']) }}" class="px-3 py-1.5 rounded-md transition whitespace-nowrap {{ ($currentStatus ?? 'semua') === 'semua' ? 'bg-white text-slate-900 font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}">
                    Semua ({{ $totalCount }})
                </a>
                <a href="{{ route('petugas.report-management', ['status' => 'baru', 'search' => $search ?? '']) }}" class="px-3 py-1.5 rounded-md transition whitespace-nowrap {{ ($currentStatus ?? '') === 'baru' ? 'bg-white text-slate-900 font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}">
                    Baru ({{ $newCount }})
                </a>
                <a href="{{ route('petugas.report-management', ['status' => 'diproses', 'search' => $search ?? '']) }}" class="px-3 py-1.5 rounded-md transition whitespace-nowrap {{ ($currentStatus ?? '') === 'diproses' ? 'bg-white text-slate-900 font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}">
                    Diproses ({{ $inProgressCount }})
                </a>
                <a href="{{ route('petugas.report-management', ['status' => 'selesai', 'search' => $search ?? '']) }}" class="px-3 py-1.5 rounded-md transition whitespace-nowrap {{ ($currentStatus ?? '') === 'selesai' ? 'bg-white text-slate-900 font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}">
                    Selesai ({{ $resolvedCount }})
                </a>
                <a href="{{ route('petugas.report-management', ['status' => 'ditolak', 'search' => $search ?? '']) }}" class="px-3 py-1.5 rounded-md transition whitespace-nowrap {{ ($currentStatus ?? '') === 'ditolak' ? 'bg-white text-slate-900 font-bold shadow-2xs' : 'text-slate-600 hover:text-slate-900' }}">
                    Ditolak ({{ $rejectedCount }})
                </a>
            </div>

            <form method="GET" action="{{ route('petugas.report-management') }}" class="relative w-full md:w-72">
                <input type="hidden" name="status" value="{{ $currentStatus ?? 'semua' }}">
                <span class="material-symbols-outlined absolute left-3 top-2 text-slate-400 text-[18px]">search</span>
                <input type="text" name="search" value="{{ $search ?? '' }}" placeholder="Cari tiket, ruang, pelapor..." class="w-full pl-9 pr-8 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-slate-900 focus:bg-white transition">
                @if(!empty($search))
                    <a href="{{ route('petugas.report-management', ['status' => $currentStatus ?? 'semua']) }}" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-700">
                        <span class="material-symbols-outlined text-[16px]">close</span>
                    </a>
                @endif
            </form>
        </div>

        {{-- 4. Tabel Tiket Kerusakan Lengkap (Monochrome Bento Table) --}}
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-2xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 font-medium border-b border-slate-200">
                            <th class="py-3 px-4">Tiket & Pelapor</th>
                            <th class="py-3 px-4">Fasilitas</th>
                            <th class="py-3 px-4">Deskripsi Masalah</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4">Mode Perbaikan</th>
                            <th class="py-3 px-4">Catatan Resolusi</th>
                            <th class="py-3 px-4 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($reports as $report)
                            @php
                                $isFacilityLocked = !empty($report->is_facility_locked) || ($report->facility?->status === 'dalam perbaikan');
                                $facilityName = $report->facility->name ?? 'Fasilitas Terkait';
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="py-3 px-4 align-top whitespace-nowrap">
                                    <div class="font-mono font-bold text-slate-900">
                                        {{ $report->report_code ?? 'RPT-' . $report->id }}
                                    </div>
                                    <div class="font-medium text-slate-800 mt-0.5">
                                        {{ $report->user->name ?? 'Sivitas Kampus' }}
                                    </div>
                                    <span class="text-[10px] text-slate-400 block mt-0.5">
                                        {{ !empty($report->created_at) ? \Carbon\Carbon::parse($report->created_at)->translatedFormat('d M Y • H:i') . ' WIB' : '-' }}
                                    </span>
                                </td>

                                <td class="py-3 px-4 align-top">
                                    <div class="font-semibold text-slate-900">
                                        {{ $report->facility->name ?? 'Fasilitas Kampus' }}
                                    </div>
                                    <div class="text-[11px] text-slate-500">
                                        {{ $report->facility->building ?? 'Gedung' }}
                                        @if(!empty($report->facility->floor_location))
                                            &bull; Lt. {{ $report->facility->floor_location }}
                                        @endif
                                    </div>
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-slate-100 text-slate-600 inline-block mt-1">
                                        {{ $report->category ?? 'Umum' }}
                                    </span>
                                </td>

                                <td class="py-3 px-4 align-top max-w-xs">
                                    <p class="text-slate-700 line-clamp-2 leading-relaxed">
                                        {{ $report->description ?? '-' }}
                                    </p>
                                    @if(!empty($report->attachment_photo))
                                        <div class="mt-1.5">
                                            <button type="button" @click="openPhoto('{{ asset('storage/' . $report->attachment_photo) }}')" class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-700 hover:text-slate-950 underline cursor-pointer">
                                                <span class="material-symbols-outlined text-[13px]">image</span>
                                                <span>Lihat Bukti Foto</span>
                                            </button>
                                        </div>
                                    @endif
                                </td>

                                <td class="py-3 px-4 align-top">
                                    <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-700 capitalize">
                                        {{ $report->status ?? 'baru' }}
                                    </span>
                                </td>

                                {{-- Kolom Saklar Mode Perbaikan --}}
                                <td class="py-3 px-4 align-top whitespace-nowrap">
                                    @if(!empty($report->facility_id))
                                        <form action="{{ url('/petugas/facilities/' . $report->facility_id . '/toggle-maintenance') }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin {{ $isFacilityLocked ? 'MEMBUKA KUNCI dan mengembalikan fasilitas ' . addslashes($facilityName) . ' ke status Aktif' : 'MENGUNCI fasilitas ' . addslashes($facilityName) . ' ke Mode Perbaikan' }}?');" class="inline-block">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" title="{{ $isFacilityLocked ? 'Buka kunci fasilitas' : 'Kunci fasilitas' }}" class="group flex items-center gap-2 p-1.5 pr-2.5 rounded-lg border text-left transition-colors shadow-2xs {{ $isFacilityLocked ? 'bg-slate-900 border-slate-900 text-white' : 'bg-white border-slate-200 text-slate-700 hover:bg-slate-50' }}">
                                                <div class="relative w-6 h-3.5 rounded-full transition-colors {{ $isFacilityLocked ? 'bg-white/30' : 'bg-slate-200' }}">
                                                    <div class="absolute top-0.5 {{ $isFacilityLocked ? 'right-0.5 bg-white' : 'left-0.5 bg-slate-400' }} w-2.5 h-2.5 rounded-full transition-all"></div>
                                                </div>
                                                <span class="text-[11px] font-medium">
                                                    {{ $isFacilityLocked ? 'Terkunci' : 'Normal' }}
                                                </span>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-slate-400 text-xs italic">-</span>
                                    @endif
                                </td>

                                <td class="py-3 px-4 align-top max-w-xs">
                                    @if(!empty($report->resolution_note))
                                        <div class="text-[11px] text-slate-700 bg-slate-50 border border-slate-200/60 p-2 rounded-lg leading-relaxed">
                                            {{ $report->resolution_note }}
                                        </div>
                                    @else
                                        <span class="text-slate-400 text-xs italic">Belum ada catatan</span>
                                    @endif
                                </td>

                                <td class="py-3 px-4 align-top text-right whitespace-nowrap">
                                    <button type="button" @click="openUpdate('{{ $report->id }}', '{{ $report->report_code }}', '{{ addslashes($report->facility->name ?? 'Fasilitas') }}', '{{ addslashes($report->description ?? '') }}', '{{ $report->status }}', {{ $isFacilityLocked ? 'true' : 'false' }}, '{{ addslashes($report->resolution_note ?? '') }}')" class="px-2.5 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-900 hover:text-white hover:border-slate-900 transition-colors shadow-2xs text-xs font-medium">
                                        Perbarui Status
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-12 px-4 text-center text-xs text-slate-400">
                                    Tidak ada tiket laporan kerusakan yang tertunda saat ini.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Navigasi Paginasi Server-Side Laravel --}}
            @if ($reports->total() > 0)
                <div class="p-3.5 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                    <div class="text-slate-500">
                        Menampilkan <strong class="text-slate-900">{{ $reports->firstItem() ?? 0 }}</strong> - <strong class="text-slate-900">{{ $reports->lastItem() ?? 0 }}</strong> dari <strong class="text-slate-900">{{ $reports->total() }}</strong> laporan
                    </div>
                    <div>
                        {{ $reports->links() }}
                    </div>
                </div>
            @endif
        </div>

        {{-- 5. Modal Perbarui Status Tiket & Kunci Fasilitas --}}
        <div x-show="showStatusModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="showStatusModal = false" class="bg-white rounded-2xl max-w-lg w-full p-6 shadow-xl border border-slate-200">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-slate-900">
                        Perbarui Status Penanganan & Fasilitas
                    </h3>
                    <button type="button" @click="showStatusModal = false" class="text-slate-400 hover:text-slate-700">
                        <span class="material-symbols-outlined text-[18px]">close</span>
                    </button>
                </div>

                <div class="space-y-4">
                    {{-- Pratinjau Info Singkat Tiket --}}
                    <div class="bg-slate-50 p-3 rounded-xl border border-slate-100 text-xs">
                        <span class="text-slate-400 font-mono block mb-0.5" x-text="selectedReport.code"></span>
                        <span class="font-semibold text-slate-900 text-sm block" x-text="selectedReport.venue"></span>
                        <p class="text-slate-600 mt-1 leading-relaxed" x-text="selectedReport.desc"></p>
                    </div>

                    <form :action="'{{ url('/petugas/reports') }}/' + selectedReport.id" method="POST" class="space-y-4">
                        @csrf
                        @method('PATCH')

                        {{-- Pilihan Status Tiket --}}
                        <div>
                            <label for="status-select" class="block text-xs font-medium text-slate-700 mb-1.5">
                                Status Penanganan:
                            </label>
                            <select id="status-select" name="status" x-model="selectedReport.status" class="w-full h-9 px-3 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-900 focus:bg-white transition">
                                <option value="baru">Laporan Baru</option>
                                <option value="diproses">Sedang Diproses Teknisi</option>
                                <option value="selesai">Selesai Ditangani</option>
                                <option value="ditolak">Ditolak (Tidak Valid / Duplikat)</option>
                            </select>
                        </div>

                        {{-- Toggle Fasilitas Dalam Perbaikan --}}
                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-200">
                            <label class="flex items-start gap-2.5 cursor-pointer">
                                <input type="checkbox" name="is_facility_locked" value="1" x-model="selectedReport.facilityLocked" class="mt-0.5 rounded text-slate-900 focus:ring-slate-900 w-4 h-4">
                                <div>
                                    <span class="text-xs font-semibold text-slate-900 block">Kunci Fasilitas ke Mode Perbaikan</span>
                                    <span class="text-[11px] text-slate-500 leading-snug block mt-0.5">
                                        Fasilitas akan otomatis tidak dapat dipesan di kalender agar tidak terjadi bentrok.
                                    </span>
                                </div>
                            </label>
                        </div>

                        {{-- Catatan Resolusi --}}
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="resolution-note" class="text-xs font-medium text-slate-700">
                                    Catatan Resolusi / Tindakan Teknisi:
                                </label>
                                <span x-show="selectedReport.status === 'selesai' || selectedReport.status === 'ditolak'" class="text-[11px] text-rose-600 font-medium">
                                    *Wajib Diisi
                                </span>
                            </div>
                            <textarea id="resolution-note" name="catatan_resolusi" x-model="selectedReport.resolutionNote" :required="selectedReport.status === 'selesai' || selectedReport.status === 'ditolak'" rows="3" placeholder="Contoh: Komponen telah diganti dan fasilitas telah diuji normal..." class="w-full p-2.5 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-900 focus:bg-white transition"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                            <button type="button" @click="showStatusModal = false" class="px-3.5 py-1.5 rounded-lg border border-slate-200 text-slate-700 text-xs font-medium hover:bg-slate-50 transition">
                                Batal
                            </button>
                            <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-slate-900 text-white text-xs font-medium hover:bg-slate-800 shadow-2xs transition">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- 6. Modal Lightbox Pratinjau Foto --}}
        <div x-show="showPhotoModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/70 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="showPhotoModal = false" class="bg-white rounded-2xl max-w-2xl w-full p-4 shadow-xl border border-slate-200">
                <div class="flex items-center justify-between mb-3 px-2">
                    <span class="text-xs font-semibold text-slate-900">Lampiran Foto Bukti Kerusakan</span>
                    <button type="button" @click="showPhotoModal = false" class="text-slate-400 hover:text-slate-700">
                        <span class="material-symbols-outlined text-[18px]">close</span>
                    </button>
                </div>
                <div class="rounded-xl overflow-hidden bg-slate-50 max-h-[75vh] flex items-center justify-center p-2">
                    <img :src="photoUrl" alt="Foto Bukti Kerusakan" class="max-h-[70vh] w-auto object-contain rounded-lg">
                </div>
            </div>
        </div>

    </div>
</x-petugas-layout>
