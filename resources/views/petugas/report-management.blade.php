{{-- 
  NAMA FILE      : report-management.blade.php
  FUNGSIONALITAS : Lembar Kerja Manajemen Tiket Kerusakan Fasilitas Petugas Sarpras (PTG-04 / US-11)
  DESKRIPSI      : Menampilkan seluruh antrean tiket kerusakan fasilitas kampus, mengubah status progres (Baru/Diproses/Selesai/Ditolak), menginput catatan resolusi teknisi, serta mengunci fasilitas ke status 'Dalam Perbaikan' (UR11, UR12).
  CARA KERJA     : Memanfaatkan layout <x-petugas-layout active="report-management">, mengelola modal pembaruan status dan sinkronisasi kalender maintenance via Alpine.js, serta memicu pengiriman PATCH request ke endpoint operasional petugas.
--}}

@php
    // Inisialisasi fallback aman jika data belum dipass dari Controller
    $reports = $reports ?? collect([]);
    $totalCount = $totalCount ?? $reports->count();
    $newCount = $newCount ?? $reports->where('status', 'baru')->count();
    $inProgressCount = $inProgressCount ?? $reports->where('status', 'diproses')->count();
    $resolvedCount = $resolvedCount ?? $reports->where('status', 'selesai')->count();
    $rejectedCount = $rejectedCount ?? $reports->where('status', 'ditolak')->count();
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
        },
        matchesFilter(status, content) {
            const matchStatus = (this.activeTab === 'semua') || (this.activeTab === status);
            const matchSearch = !this.search || content.toLowerCase().includes(this.search.toLowerCase());
            return matchStatus && matchSearch;
        }
    }" class="flex flex-col gap-6">

        {{-- 1. Notifikasi Umpan Balik (Feedback Flash Message) --}}
        @if(session('success'))
            <!-- 
              ELEMEN       : Banner Notifikasi Sukses
              KEGUNAAN     : Memberikan umpan balik positif ketika status tiket kerusakan berhasil diperbarui.
            -->
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px] text-emerald-600">check_circle</span>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-800">
                    <span class="material-symbols-outlined text-[16px]">close</span>
                </button>
            </div>
        @endif

        @if(session('error') || $errors->any())
            <!-- 
              ELEMEN       : Banner Notifikasi Galat
              KEGUNAAN     : Menampilkan kegagalan validasi atau kesalahan input pada saat pembaruan status laporan.
            -->
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center justify-between shadow-xs">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-[20px] text-rose-600">error</span>
                    <span class="font-medium">
                        {{ session('error') ?? $errors->first() }}
                    </span>
                </div>
                <button type="button" @click="$el.parentElement.remove()" class="text-rose-500 hover:text-rose-800">
                    <span class="material-symbols-outlined text-[16px]">close</span>
                </button>
            </div>
        @endif

        {{-- 2. Breadcrumb & Header --}}
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-2 font-medium">
                <a href="{{ url('/petugas/dashboard') }}" class="hover:text-slate-900 transition">Dasbor Operasional</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-slate-900">Manajemen Tiket Kerusakan</span>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Manajemen Laporan Kerusakan & Fasilitas</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        Pantau keluhan sarana kampus, ubah status progres perbaikan, input catatan resolusi, dan kunci jadwal ruang yang rusak (UR11, UR12).
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-700 shadow-xs">
                        Total Tiket: <strong class="text-slate-900">{{ $totalCount }} Laporan</strong>
                    </span>
                </div>
            </div>
        </div>

        {{-- 3. Filter & Search Bar --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-1 p-1 bg-slate-100 rounded-xl text-xs font-medium w-full md:w-auto overflow-x-auto">
                <button type="button" @click="activeTab = 'semua'" :class="activeTab === 'semua' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                    Semua ({{ $totalCount }})
                </button>
                <button type="button" @click="activeTab = 'baru'" :class="activeTab === 'baru' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                    Baru ({{ $newCount }})
                </button>
                <button type="button" @click="activeTab = 'diproses'" :class="activeTab === 'diproses' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                    Sedang Diproses ({{ $inProgressCount }})
                </button>
                <button type="button" @click="activeTab = 'selesai'" :class="activeTab === 'selesai' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                    Selesai ({{ $resolvedCount }})
                </button>
                <button type="button" @click="activeTab = 'ditolak'" :class="activeTab === 'ditolak' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                    Ditolak ({{ $rejectedCount }})
                </button>
            </div>

            <div class="relative w-full md:w-64">
                <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-[18px]">search</span>
                <input type="text" x-model="search" placeholder="Cari tiket / ruang / pelapor..." class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-slate-900 focus:bg-white transition">
            </div>
        </div>

        {{-- 4. Tabel Tiket Kerusakan Lengkap --}}
        <!-- 
          ELEMEN       : Tabel Daftar Tiket Kerusakan Fasilitas
          KEGUNAAN     : Menyajikan daftar tiket keluhan sarana kampus dengan informasi foto, status penanganan, dan catatan resolusi teknisi.
          CARA KERJA   : Melakukan perulangan Blade dinamis @forelse($reports as $report) dengan filter reaktif via Alpine.js.
        -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                            <th class="py-3 px-4">Tiket & Pelapor</th>
                            <th class="py-3 px-4">Fasilitas & Kategori</th>
                            <th class="py-3 px-4">Deskripsi & Bukti Foto</th>
                            <th class="py-3 px-4">Status Tiket</th>
                            <th class="py-3 px-4">Status Kalender (UR12)</th>
                            <th class="py-3 px-4">Catatan Resolusi (UR11)</th>
                            <th class="py-3 px-4 text-right">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($reports as $report)
                            @php
                                $searchContent = ($report->report_code ?? '') . ' ' . ($report->user->name ?? '') . ' ' . ($report->facility->name ?? '') . ' ' . ($report->category ?? '') . ' ' . ($report->description ?? '');
                            @endphp
                            <tr x-show="matchesFilter('{{ $report->status }}', '{{ addslashes($searchContent) }}')" class="hover:bg-slate-50/70 transition">
                                <td class="py-3.5 px-4 align-top whitespace-nowrap">
                                    <div class="font-mono font-bold text-slate-800">
                                        {{ $report->report_code ?? 'RPT-' . $report->id }}
                                    </div>
                                    <div class="text-[11px] text-slate-700 font-medium mt-0.5">
                                        {{ $report->user->name ?? 'Sivitas Kampus' }}
                                    </div>
                                    <span class="text-[10px] text-slate-400 block mt-0.5">
                                        {{ !empty($report->created_at) ? \Carbon\Carbon::parse($report->created_at)->translatedFormat('d M Y • H:i') . ' WIB' : '-' }}
                                    </span>
                                </td>

                                <td class="py-3.5 px-4 align-top">
                                    <div class="font-bold text-slate-900">
                                        {{ $report->facility->name ?? 'Fasilitas Kampus' }}
                                    </div>
                                    <div class="text-[11px] text-slate-500">
                                        {{ $report->facility->building ?? 'Gedung Kampus' }}
                                        @if(!empty($report->facility->floor_location))
                                            • Lt. {{ $report->facility->floor_location }}
                                        @endif
                                    </div>
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700 inline-block mt-1">
                                        {{ $report->category ?? 'Umum' }}
                                    </span>
                                </td>

                                <td class="py-3.5 px-4 align-top max-w-xs">
                                    <p class="text-slate-700 line-clamp-2 leading-relaxed">
                                        {{ $report->description ?? '-' }}
                                    </p>
                                    @if(!empty($report->attachment_photo))
                                        <div class="mt-2 flex items-center gap-1.5">
                                            <button type="button" @click="openPhoto('{{ asset('storage/' . $report->attachment_photo) }}')" class="inline-flex items-center gap-1 text-[11px] font-semibold text-blue-900 bg-blue-50 border border-blue-200/60 px-2 py-0.5 rounded-md hover:bg-blue-100 transition">
                                                <span class="material-symbols-outlined text-[13px]">image</span>
                                                <span>Lihat Bukti Foto</span>
                                            </button>
                                        </div>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 align-top">
                                    <x-cava.status-badge :status="$report->status ?? 'baru'" />
                                </td>

                                <td class="py-3.5 px-4 align-top">
                                    @if(!empty($report->is_facility_locked))
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs bg-rose-50 text-rose-700 border border-rose-200 font-semibold">
                                            <span class="material-symbols-outlined text-[13px]">lock</span>
                                            <span>Terkunci (Perbaikan)</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs bg-emerald-50 text-emerald-800 border border-emerald-200 font-medium">
                                            <span class="material-symbols-outlined text-[13px]">check</span>
                                            <span>Kalender Terbuka</span>
                                        </span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 align-top max-w-xs">
                                    @if(!empty($report->resolution_note))
                                        <div class="text-[11px] text-slate-700 bg-slate-50 border border-slate-200/60 p-2 rounded-lg leading-relaxed">
                                            {{ $report->resolution_note }}
                                        </div>
                                    @else
                                        <span class="text-slate-400 text-xs italic">Belum ada catatan</span>
                                    @endif
                                </td>

                                <td class="py-3.5 px-4 align-top text-right whitespace-nowrap">
                                    <button type="button" @click="openUpdate('{{ $report->id }}', '{{ $report->report_code }}', '{{ addslashes($report->facility->name ?? 'Fasilitas') }}', '{{ addslashes($report->description ?? '') }}', '{{ $report->status }}', {{ !empty($report->is_facility_locked) ? 'true' : 'false' }}, '{{ addslashes($report->resolution_note ?? '') }}')" class="px-3 py-1.5 rounded-xl bg-slate-900 text-white font-semibold hover:bg-slate-800 transition shadow-xs text-xs inline-flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">edit_note</span>
                                        <span>Perbarui Status</span>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <!-- 
                              ELEMEN       : State Kosong (Empty State)
                              KEGUNAAN     : Memberi umpan balik bahwa tidak ada keluhan sarana kampus yang terabaikan.
                            -->
                            <tr>
                                <td colspan="7" class="py-12 px-4 text-center">
                                    <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-3">
                                            <span class="material-symbols-outlined text-[28px]">check_circle</span>
                                        </div>
                                        <h4 class="text-sm font-bold text-slate-900">Semua Laporan Kerusakan Beres!</h4>
                                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                            Tidak ada tiket laporan kerusakan yang tertunda. Seluruh fasilitas dan sarana kampus dalam kondisi optimal.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 5. Modal Perbarui Status Tiket & Kunci Fasilitas (PTG-04 / US-11 / UR12) --}}
        <!-- 
          ELEMEN       : Modal Interaktif Pembaruan Status & Resolusi Kerusakan
          KEGUNAAN     : Memungkinkan petugas mengubah status laporan (baru/diproses/selesai/ditolak), mencatat resolusi perbaikan, dan mengunci ruang di kalender.
          CARA KERJA   : Terbuka saat showStatusModal = true. Mengirim PATCH request ke /petugas/reports/{id} dengan validasi kondisional catatan resolusi.
        -->
        <div x-show="showStatusModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="showStatusModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[20px] text-slate-700">build</span>
                        <span>Perbarui Status Penanganan & Fasilitas</span>
                    </h3>
                    <button type="button" @click="showStatusModal = false" class="text-slate-400 hover:text-slate-700">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="space-y-4">
                    {{-- Pratinjau Info Singkat Tiket --}}
                    <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-100 text-xs">
                        <span class="text-slate-400 font-mono block mb-0.5" x-text="selectedReport.code"></span>
                        <span class="font-bold text-slate-900 text-sm block" x-text="selectedReport.venue"></span>
                        <p class="text-slate-600 mt-1 leading-relaxed" x-text="selectedReport.desc"></p>
                    </div>

                    <!-- 
                      ROUTE: POST /petugas/reports/{id} (dengan @method('PATCH'))
                      FUNGSI: Memperbarui status penanganan laporan kerusakan dan menyimpan catatan resolusi teknisi (PTG-04 / US-11).
                    -->
                    <form :action="'{{ url('/petugas/reports') }}/' + selectedReport.id" method="POST" class="space-y-4">
                        @csrf
                        @method('PATCH')

                        {{-- Pilihan Dropdown Status Tiket (UR11) --}}
                        <div>
                            <label for="status-select" class="block text-xs font-bold text-slate-700 mb-1.5">
                                Status Progres Tiket (UR11):
                            </label>
                            <select id="status-select" name="status" x-model="selectedReport.status" class="w-full h-10 px-3 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-slate-900 focus:bg-white transition">
                                <option value="baru">Laporan Baru</option>
                                <option value="diproses">Sedang Diproses Teknisi</option>
                                <option value="selesai">Selesai Ditangani</option>
                                <option value="ditolak">Ditolak (Tidak Valid / Duplikat)</option>
                            </select>
                        </div>

                        {{-- Toggle Fasilitas Dalam Perbaikan (UR12) --}}
                        <div class="p-3.5 rounded-2xl bg-amber-50/70 border border-amber-200/80">
                            <label class="flex items-start gap-2.5 cursor-pointer">
                                <input type="checkbox" name="is_facility_locked" value="1" x-model="selectedReport.facilityLocked" class="mt-0.5 rounded text-slate-900 focus:ring-slate-900 w-4 h-4">
                                <div>
                                    <span class="text-xs font-bold text-amber-950 block">Tandai Fasilitas 'Dalam Perbaikan' (Kunci Jadwal UR12)</span>
                                    <span class="text-[11px] text-amber-800 leading-snug block mt-0.5">
                                        Fasilitas akan otomatis tidak dapat dipesan di kalender ketersediaan umum agar tidak terjadi bentrok atau pemesanan ruang yang rusak.
                                    </span>
                                </div>
                            </label>
                        </div>

                        {{-- Catatan Resolusi / Perbaikan (Kondisional Wajib saat Selesai atau Ditolak) --}}
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label for="resolution-note" class="text-xs font-bold text-slate-700">
                                    Catatan Resolusi / Tindakan Teknisi:
                                </label>
                                <span x-show="selectedReport.status === 'selesai' || selectedReport.status === 'ditolak'" class="text-[11px] text-rose-600 font-semibold">
                                    *Wajib Diisi
                                </span>
                            </div>
                            <textarea id="resolution-note" name="catatan_resolusi" x-model="selectedReport.resolutionNote" :required="selectedReport.status === 'selesai' || selectedReport.status === 'ditolak'" rows="3" placeholder="Contoh: Komponen proyektor telah diganti dengan yang baru, dilakukan pengetesan display dan berfungsi normal..." class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-slate-900 focus:bg-white transition"></textarea>
                            <p x-show="selectedReport.status === 'selesai'" class="text-[11px] text-slate-400 mt-1">
                                Catatan resolusi ini akan dapat dibaca oleh pengguna pelapor sebagai konfirmasi penanganan selesai.
                            </p>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                            <button type="button" @click="showStatusModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50 transition">
                                Batal
                            </button>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 shadow-xs transition">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- 6. Modal Lightbox Pratinjau Foto Lampiran Bukti --}}
        <div x-show="showPhotoModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/80 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="showPhotoModal = false" class="bg-white rounded-3xl max-w-2xl w-full p-4 shadow-2xl border border-slate-200">
                <div class="flex items-center justify-between mb-3 px-2">
                    <span class="text-xs font-bold text-slate-900">Lampiran Foto Bukti Kerusakan</span>
                    <button type="button" @click="showPhotoModal = false" class="text-slate-400 hover:text-slate-700">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <div class="rounded-2xl overflow-hidden bg-slate-100 max-h-[75vh] flex items-center justify-center">
                    <img :src="photoUrl" alt="Foto Bukti Kerusakan" class="max-h-[70vh] w-auto object-contain">
                </div>
            </div>
        </div>

    </div>
</x-petugas-layout>
