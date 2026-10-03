{{-- 
  NAMA FILE      : report-history.blade.php
  FUNGSIONALITAS : Halaman Terpadu Pelaporan & Riwayat Status Kerusakan Sarpras (USR-04 & USR-05)
  DESKRIPSI      : Antarmuka Bento UI modern yang mengintegrasikan riwayat tiket kerusakan fasilitas, metrik status, pencarian dinamis, filter dropdown, modal detail resolusi teknisi, dan formulir pengajuan laporan baru dalam satu halaman responsif.
  CARA KERJA     : Menerima $reports, $counts, $activeStatus, dan $facilities dari ReportController@history. Formulir pelaporan baru dan detail tiket dikendalikan langsung oleh Alpine.js.
--}}

<x-app-layout title="Status & Riwayat Pelaporan Kerusakan" active="report-history">
    @php
        $categories = [
            'AC & Pendingin',
            'Kelistrikan / Stop Kontak',
            'Proyektor & Audio',
            'Mebel & Meja Kursi',
            'Jaringan & Kabel',
            'Fisik Bangunan / Pintu / Jendela',
            'Kebersihan',
            'Lainnya'
        ];
    @endphp

    <div x-data="{
        showCreateModal: {{ $errors->any() || request('action') === 'create' ? 'true' : 'false' }},
        showDetailModal: false,
        selectedReport: null,
        submitting: false,
        fileName: '',
        imagePreview: null,
        fileError: '',

        openDetail(report) {
            this.selectedReport = report;
            this.showDetailModal = true;
        },

        handleFile(e) {
            const file = e.target.files[0];
            this.fileError = '';
            if (file) {
                // Validasi batas ukuran file klien (Maks 2 MB)
                if (file.size > 2 * 1024 * 1024) {
                    this.fileError = 'Ukuran berkas melebihi 2 MB (terbaca: ' + (file.size / (1024 * 1024)).toFixed(2) + ' MB). Harap pilih foto yang lebih kecil.';
                    e.target.value = '';
                    this.fileName = '';
                    this.imagePreview = null;
                    return;
                }
                // Validasi format file
                if (!['image/jpeg', 'image/png', 'image/jpg'].includes(file.type)) {
                    this.fileError = 'Format file tidak didukung. Harap pilih gambar bertipe JPG atau PNG.';
                    e.target.value = '';
                    this.fileName = '';
                    this.imagePreview = null;
                    return;
                }

                this.fileName = file.name;
                const reader = new FileReader();
                reader.onload = (event) => {
                    this.imagePreview = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        },

        resetFile() {
            this.imagePreview = null;
            this.fileName = '';
            this.fileError = '';
            const input = document.getElementById('photo-file');
            if (input) input.value = '';
        }
    }" class="space-y-6">

        {{-- Header Judul --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-extrabold text-slate-900 tracking-tight">Pelaporan & Riwayat Kerusakan</h1>
                <p class="text-xs sm:text-sm text-slate-500 mt-1">Pantau tindak lanjut penanganan teknisi sarpras terhadap kendala sarana yang Anda laporkan secara real-time.</p>
            </div>
            <button type="button" 
                    @click="showCreateModal = true" 
                    class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-2xl bg-slate-900 text-white text-xs sm:text-sm font-semibold hover:bg-slate-800 transition shadow-sm hover:shadow active:scale-[0.98] shrink-0">
                <span class="material-symbols-outlined text-[18px]">add_circle</span>
                <span>Buat Laporan Baru</span>
            </button>
        </div>

        {{-- Flash Session Sukses --}}
        @if (session('success'))
            <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-center gap-3 shadow-xs">
                <span class="material-symbols-outlined text-emerald-600 text-[24px] shrink-0">check_circle</span>
                <div>
                    <h4 class="text-sm font-bold">Laporan Berhasil Terkirim!</h4>
                    <p class="text-xs text-emerald-700 mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        {{-- Flash Session Error / Validation Errors --}}
        @if ($errors->any())
            <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 flex items-start gap-3 shadow-xs">
                <span class="material-symbols-outlined text-rose-600 text-[24px] shrink-0 mt-0.5">error</span>
                <div>
                    <h4 class="text-sm font-bold">Terdapat Kesalahan Pengisian Formulir!</h4>
                    <ul class="list-disc list-inside text-xs text-rose-700 mt-1 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- Bento Grid Baris Atas: Metrik Ringkasan & Kartu Aksi Cepat --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">
            {{-- Bento Card 1: Ringkasan Metrik Status (7 Kolom di Desktop) --}}
            <div class="lg:col-span-7 bg-white rounded-3xl border border-slate-200/80 p-5 sm:p-6 shadow-xs flex flex-col justify-between">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-slate-100">
                    <span class="text-[11px] font-bold tracking-wider uppercase text-slate-400 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px] text-slate-500">analytics</span>
                        Rekapitulasi Tiket Pengaduan
                    </span>
                    <span class="text-xs font-semibold text-slate-500">Total: <strong class="text-slate-900">{{ $counts['all'] ?? 0 }}</strong> Tiket</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    {{-- Metrik 1: Semua Tiket --}}
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'all', 'page' => 1]) }}" 
                       class="p-3.5 rounded-2xl transition border {{ $activeStatus === 'all' ? 'bg-slate-900 text-white border-slate-900 shadow-xs' : 'bg-slate-50/80 text-slate-700 border-slate-200/60 hover:bg-slate-100' }}">
                        <span class="text-[11px] font-medium block opacity-75">Semua</span>
                        <span class="text-xl sm:text-2xl font-black mt-1 block">{{ $counts['all'] ?? 0 }}</span>
                    </a>

                    {{-- Metrik 2: Laporan Baru --}}
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'baru', 'page' => 1]) }}" 
                       class="p-3.5 rounded-2xl transition border {{ $activeStatus === 'baru' ? 'bg-amber-500 text-white border-amber-500 shadow-xs' : 'bg-amber-50/60 text-amber-900 border-amber-200/60 hover:bg-amber-100/60' }}">
                        <span class="text-[11px] font-medium block opacity-85">Baru</span>
                        <span class="text-xl sm:text-2xl font-black mt-1 block">{{ $counts['baru'] ?? 0 }}</span>
                    </a>

                    {{-- Metrik 3: Sedang Diproses --}}
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'diproses', 'page' => 1]) }}" 
                       class="p-3.5 rounded-2xl transition border {{ $activeStatus === 'diproses' ? 'bg-sky-600 text-white border-sky-600 shadow-xs' : 'bg-sky-50/60 text-sky-900 border-sky-200/60 hover:bg-sky-100/60' }}">
                        <span class="text-[11px] font-medium block opacity-85">Diproses</span>
                        <span class="text-xl sm:text-2xl font-black mt-1 block">{{ $counts['diproses'] ?? 0 }}</span>
                    </a>

                    {{-- Metrik 4: Selesai Ditangani --}}
                    <a href="{{ request()->fullUrlWithQuery(['status' => 'selesai', 'page' => 1]) }}" 
                       class="p-3.5 rounded-2xl transition border {{ $activeStatus === 'selesai' ? 'bg-emerald-600 text-white border-emerald-600 shadow-xs' : 'bg-emerald-50/60 text-emerald-900 border-emerald-200/60 hover:bg-emerald-100/60' }}">
                        <span class="text-[11px] font-medium block opacity-85">Selesai</span>
                        <span class="text-xl sm:text-2xl font-black mt-1 block">{{ $counts['selesai'] ?? 0 }}</span>
                    </a>
                </div>
            </div>

            {{-- Bento Card 2: Quick Action & Penanganan Sarpras (5 Kolom di Desktop) --}}
            <div class="lg:col-span-5 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white rounded-3xl p-5 sm:p-6 shadow-xs flex flex-col justify-between relative overflow-hidden">
                <div class="relative z-10">
                    <h3 class="text-base sm:text-lg font-bold">Temukan Sarana Rusak?</h3>
                    <p class="text-xs text-slate-300 mt-1 leading-relaxed">
                        Kirimkan laporan kerusakan AC, proyektor, kelistrikan, atau fasilitas kampus lainnya. Laporan Anda akan segera ditinjau dan direspon oleh petugas sarpras dalam waktu kurang dari 24 jam.
                    </p>
                </div>
                <div class="mt-4 pt-3 border-t border-white/10 relative z-10 flex items-center justify-between">
                    <span class="text-[11px] text-slate-400">Respon petugas &lt; 24 jam</span>
                    <button type="button" 
                            @click="showCreateModal = true" 
                            class="px-4 py-2 rounded-xl bg-white text-slate-900 text-xs font-bold hover:bg-slate-100 transition shadow-xs flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">add</span>
                        <span>Lapor Sekarang</span>
                    </button>
                </div>
            </div>
        </div>

        {{-- Bento Grid Bagian Utama: Daftar Riwayat Laporan (Bento List View) --}}
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs p-5 sm:p-7">
            
            {{-- Toolbar Pencarian & Filter Dropdown (Penyederhanaan Komponen Tanpa Tombol Berdempetan) --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-5 mb-5 border-b border-slate-100">
                <div>
                    <h2 class="text-base font-bold text-slate-900">Daftar Tiket Pengaduan</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Daftar keluhan fasilitas yang Anda laporkan beserta catatan penanganan teknisi.</p>
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    {{-- Dropdown Pemilihan Status (Menghilangkan Tombol Pill Berdempetan) --}}
                    <div class="relative min-w-[170px]">
                        <label for="status-filter" class="sr-only">Filter Status</label>
                        <select id="status-filter" 
                                onchange="window.location.href = this.value" 
                                class="w-full h-10 pl-3 pr-8 rounded-xl border border-slate-200 bg-slate-50 text-xs font-semibold text-slate-800 focus:bg-white focus:border-slate-900 focus:ring-slate-900 transition">
                            <option value="{{ request()->fullUrlWithQuery(['status' => 'all', 'page' => 1]) }}" {{ $activeStatus === 'all' ? 'selected' : '' }}>
                                Semua Status ({{ $counts['all'] ?? 0 }})
                            </option>
                            <option value="{{ request()->fullUrlWithQuery(['status' => 'baru', 'page' => 1]) }}" {{ $activeStatus === 'baru' ? 'selected' : '' }}>
                                Laporan Baru ({{ $counts['baru'] ?? 0 }})
                            </option>
                            <option value="{{ request()->fullUrlWithQuery(['status' => 'diproses', 'page' => 1]) }}" {{ $activeStatus === 'diproses' ? 'selected' : '' }}>
                                Sedang Diproses ({{ $counts['diproses'] ?? 0 }})
                            </option>
                            <option value="{{ request()->fullUrlWithQuery(['status' => 'selesai', 'page' => 1]) }}" {{ $activeStatus === 'selesai' ? 'selected' : '' }}>
                                Selesai Ditangani ({{ $counts['selesai'] ?? 0 }})
                            </option>
                            <option value="{{ request()->fullUrlWithQuery(['status' => 'ditolak', 'page' => 1]) }}" {{ $activeStatus === 'ditolak' ? 'selected' : '' }}>
                                Ditolak ({{ $counts['ditolak'] ?? 0 }})
                            </option>
                        </select>
                    </div>

                    {{-- Form Pencarian Kata Kunci --}}
                    <form action="{{ url()->current() }}" method="GET" class="relative w-full sm:w-64">
                        @if (request('status') && request('status') !== 'all')
                            <input type="hidden" name="status" value="{{ request('status') }}">
                        @endif
                        <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-[18px]">search</span>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Cari kode tiket, fasilitas..." 
                               class="w-full h-10 pl-9 pr-8 rounded-xl border border-slate-200 bg-slate-50 text-xs focus:bg-white focus:border-slate-900 focus:ring-slate-900 placeholder-slate-400 transition">
                        @if (request('search'))
                            <a href="{{ request()->fullUrlWithQuery(['search' => null]) }}" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                                <span class="material-symbols-outlined text-[16px]">close</span>
                            </a>
                        @endif
                    </form>
                </div>
            </div>

            {{-- Bento Card List View (Menggantikan Format Tabel HTML Ramai) --}}
            <div class="space-y-3">
                @forelse ($reports as $report)
                    @php
                        $reportDetail = [
                            'id'             => $report->report_code,
                            'facility'       => $report->facility->name ?? 'Fasilitas Kampus',
                            'building'       => ($report->facility->building ?? 'Kampus') . ($report->facility->floor_location ? ' • ' . $report->facility->floor_location : ''),
                            'category'       => $report->category,
                            'description'    => $report->description,
                            'status'         => $report->status,
                            'created_at'     => $report->created_at->translatedFormat('d M Y • H:i') . ' WIB',
                            'handler'        => $report->handler->name ?? 'Tim Teknisi Sarpras',
                            'resolved_at'    => $report->resolved_at ? \Carbon\Carbon::parse($report->resolved_at)->translatedFormat('d M Y • H:i') . ' WIB' : null,
                            'resolution_note'=> $report->resolution_note,
                            'photoUrl'       => $report->attachment_photo ? asset('storage/' . $report->attachment_photo) : null,
                        ];
                    @endphp

                    <div class="group bg-white rounded-2xl border border-slate-200/80 hover:border-slate-300 p-4 sm:p-5 transition hover:shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-4">
                        
                        {{-- Kolom Kiri: Kode Tiket, Tanggal & Identitas Fasilitas --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1.5">
                                <span class="px-2.5 py-0.5 rounded-lg bg-slate-100 font-mono text-xs font-bold text-slate-800">
                                    {{ $report->report_code }}
                                </span>
                                <span class="text-[11px] text-slate-400">
                                    {{ $report->created_at->translatedFormat('d M Y • H:i') }} WIB
                                </span>
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold bg-slate-100 text-slate-600">
                                    {{ $report->category }}
                                </span>
                            </div>

                            <h3 class="text-sm sm:text-base font-bold text-slate-900 group-hover:text-blue-950 transition truncate">
                                {{ $report->facility->name ?? 'Fasilitas Kampus' }}
                            </h3>
                        </div>

                        {{-- Kolom Kanan: Status & Tombol Aksi "Lihat Detail" --}}
                        <div class="flex items-center justify-between md:justify-end gap-3 pt-3 md:pt-0 border-t md:border-t-0 border-slate-100 shrink-0">
                            {{-- Status Chip --}}
                            <div>
                                @if ($report->status === 'baru')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs bg-amber-50 text-amber-800 border border-amber-200 font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                        <span>Laporan Baru</span>
                                    </span>
                                @elseif ($report->status === 'diproses')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs bg-sky-50 text-sky-800 border border-sky-200 font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-sky-500 animate-pulse"></span>
                                        <span>Sedang Diproses</span>
                                    </span>
                                @elseif ($report->status === 'selesai')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs bg-emerald-50 text-emerald-800 border border-emerald-200 font-semibold">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                        <span>Selesai Ditangani</span>
                                    </span>
                                @elseif ($report->status === 'ditolak')
                                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs bg-rose-50 text-rose-700 border border-rose-200 font-medium">
                                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                        <span>Ditolak</span>
                                    </span>
                                @endif
                            </div>

                            {{-- Tombol Lihat Detail --}}
                            <button type="button" 
                                    @click="openDetail({{ json_encode($reportDetail) }})" 
                                    class="px-3.5 py-1.5 rounded-xl border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 hover:text-slate-900 transition flex items-center gap-1.5 shadow-2xs">
                                <span class="material-symbols-outlined text-[16px] text-slate-500">visibility</span>
                                <span>Lihat Detail</span>
                            </button>
                        </div>

                    </div>
                @empty
                    <div class="py-12 px-4 text-center rounded-2xl border border-dashed border-slate-200 bg-slate-50/50">
                        <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                            <span class="material-symbols-outlined text-[28px]">build_circle</span>
                        </div>
                        <h4 class="text-sm font-bold text-slate-900 mb-1">Belum Ada Riwayat Laporan</h4>
                        <p class="text-xs text-slate-500 max-w-sm mx-auto mb-4">
                            @if (request('search') || request('status', 'all') !== 'all')
                                Tidak ditemukan tiket pengaduan yang sesuai dengan kriteria filter atau pencarian Anda.
                            @else
                                Anda belum pernah mengajukan pengaduan kendala atau kerusakan fasilitas kampus.
                            @endif
                        </p>
                        <button type="button" 
                                @click="showCreateModal = true" 
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition">
                            <span class="material-symbols-outlined text-[16px]">add</span>
                            <span>Buat Laporan Sekarang</span>
                        </button>
                    </div>
                @endforelse
            </div>

            {{-- Navigasi Paginasi Laravel --}}
            @if (isset($reports) && $reports->hasPages())
                <div class="mt-6 pt-4 border-t border-slate-100">
                    {{ $reports->links() }}
                </div>
            @endif
        </div>

        {{-- ========================================================================= --}}
        {{-- MODAL POP-UP TERPADU: FORM PENGAJUAN LAPORAN KERUSAKAN BARU               --}}
        {{-- ========================================================================= --}}
        <!-- 
          ELEMEN       : Modal Pop-up Form Pengajuan Laporan Baru
          KEGUNAAN     : Memungkinkan mahasiswa/dosen membuat laporan kerusakan fasilitas langsung dari halaman riwayat tanpa berpindah layar.
          CARA KERJA   : Dikontrol via Alpine.js showCreateModal. Otomatis terbuka jika terdapat error validasi backend.
        -->
        <div x-show="showCreateModal" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            
            <div @click.away="showCreateModal = false" 
                 class="bg-white rounded-3xl max-w-2xl w-full p-6 sm:p-8 shadow-2xl border border-slate-200 relative my-8">
                
                {{-- Tombol Tutup --}}
                <button type="button" 
                        @click="showCreateModal = false" 
                        class="absolute top-5 right-5 p-1.5 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>

                <div class="mb-5">
                    <div class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md bg-slate-100 text-[11px] font-bold text-slate-700 mb-1.5">
                        <span class="material-symbols-outlined text-[14px] text-amber-600">report_problem</span>
                        <span>Formulir Pengaduan Sarpras</span>
                    </div>
                    <h2 class="text-xl font-bold text-slate-900">Pelaporan Kerusakan Fasilitas</h2>
                    <p class="text-xs text-slate-500 mt-0.5">Sampaikan kendala kerusakan atau malfungsi sarana kampus agar segera ditangani tim sarpras.</p>
                </div>

                <!-- 
                  ROUTE: POST /user/reports
                  FUNGSI: Mengirimkan data kerusakan sarpras beserta foto bukti (JPG/PNG < 2MB)
                -->
                <form action="{{ route('user.reports.store') }}" 
                      method="POST" 
                      enctype="multipart/form-data" 
                      @submit="submitting = true" 
                      class="space-y-5">
                    @csrf

                    {{-- 1. Pilihan Fasilitas Rusak --}}
                    <div>
                        <label for="rep-facility" class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Fasilitas / Ruangan Bermasalah <span class="text-rose-500">*</span>
                        </label>
                        <select id="rep-facility" 
                                name="facility_id" 
                                required 
                                class="w-full h-11 px-3.5 bg-slate-50 rounded-xl text-xs sm:text-sm font-medium text-slate-800 border {{ $errors->has('facility_id') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-200' }} focus:border-slate-900 focus:bg-white focus:outline-none transition">
                            <option value="">-- Pilih Fasilitas / Ruangan Kampus --</option>
                            @if (isset($facilities))
                                @foreach ($facilities as $facility)
                                    <option value="{{ $facility->id }}" {{ old('facility_id') == $facility->id ? 'selected' : '' }}>
                                        {{ $facility->name }} - {{ $facility->building ?? 'Kampus' }} (Kapasitas {{ $facility->capacity ?? '-' }} Orang)
                                    </option>
                                @endforeach
                            @endif
                        </select>
                        @error('facility_id')
                            <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- 2. Kategori Kerusakan (DROPDOWN RAPI MENGGANTIKAN PILL BERDEMPETAN) --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="rep-category" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                Kategori Kerusakan Sarana
                            </label>
                            <span class="text-[11px] text-slate-400">Opsional</span>
                        </div>
                        <select id="rep-category" 
                                name="category" 
                                class="w-full h-11 px-3.5 bg-slate-50 rounded-xl text-xs sm:text-sm font-medium text-slate-800 border border-slate-200 focus:border-slate-900 focus:bg-white focus:outline-none transition">
                            <option value="">-- Pilih Kategori Kendala (Atau Kosongkan) --</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>
                                    {{ $cat }}
                                </option>
                            @endforeach
                        </select>
                        <p class="mt-1 text-[11px] text-slate-400">
                            Pilih kategori yang paling sesuai atau uraikan secara terperinci pada kotak deskripsi di bawah.
                        </p>
                    </div>

                    {{-- 3. Deskripsi Masalah (Min. 10 Karakter) --}}
                    <div>
                        <div class="flex items-center justify-between mb-1.5">
                            <label for="rep-desc" class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                Deskripsi Kerusakan / Lokasi Spesifik <span class="text-rose-500">*</span>
                            </label>
                            <span class="text-[11px] text-rose-600 font-semibold">Wajib Diisi (Min. 10 karakter)</span>
                        </div>
                        <textarea id="rep-desc" 
                                  name="description" 
                                  rows="3" 
                                  required 
                                  minlength="10"
                                  placeholder="Jelaskan secara spesifik kerusakan sarana, misalnya: 'Unit AC di dekat pintu keluar mati total dan kabel proyektor putus...'" 
                                  class="w-full p-3.5 bg-slate-50 rounded-xl text-xs sm:text-sm text-slate-800 border {{ $errors->has('description') ? 'border-rose-400 bg-rose-50/20' : 'border-slate-200' }} focus:border-slate-900 focus:bg-white focus:outline-none transition">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- 4. Unggah Foto Bukti Kerusakan --}}
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                            Unggah Foto Bukti Kerusakan <span class="text-rose-500">*</span>
                            <span class="text-slate-400 font-normal lowercase">(Maks. 2 MB - JPG/PNG)</span>
                        </label>

                        <div x-show="fileError" x-cloak class="mb-2 p-2.5 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-xs flex items-center gap-2">
                            <span class="material-symbols-outlined text-[16px] text-rose-600 shrink-0">warning</span>
                            <span x-text="fileError"></span>
                        </div>

                        <div class="border-2 border-dashed {{ $errors->has('attachment_photo') ? 'border-rose-300 bg-rose-50/20' : 'border-slate-200 bg-slate-50/50' }} rounded-2xl p-4 text-center hover:border-slate-400 transition">
                            <template x-if="!imagePreview">
                                <div class="flex flex-col items-center">
                                    <span class="material-symbols-outlined text-slate-400 text-[32px] mb-1">add_photo_alternate</span>
                                    <span class="text-xs font-semibold text-slate-700">Pilih foto bukti dari perangkat Anda</span>
                                    <span class="text-[11px] text-slate-400">Format JPG / PNG hingga ukuran 2 MB</span>
                                    <label for="photo-file" class="mt-2.5 px-3.5 py-1.5 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-700 hover:bg-slate-50 shadow-xs cursor-pointer transition">
                                        Telusuri Foto
                                    </label>
                                    <input type="file" 
                                           id="photo-file" 
                                           name="attachment_photo" 
                                           accept="image/png, image/jpeg, image/jpg" 
                                           required
                                           @change="handleFile" 
                                           class="hidden">
                                </div>
                            </template>

                            <template x-if="imagePreview">
                                <div class="flex flex-col items-center">
                                    <img :src="imagePreview" alt="Pratinjau Foto" class="h-32 w-auto object-cover rounded-xl border border-slate-200 mb-2 shadow-xs">
                                    <span class="text-xs font-mono font-medium text-slate-700 truncate max-w-xs" x-text="fileName"></span>
                                    <button type="button" @click="resetFile()" class="mt-1.5 text-xs font-semibold text-rose-600 hover:text-rose-800 hover:underline flex items-center gap-1">
                                        <span class="material-symbols-outlined text-[14px]">delete</span>
                                        <span>Ganti Foto</span>
                                    </button>
                                </div>
                            </template>
                        </div>
                        @error('attachment_photo')
                            <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- Tombol Aksi Formulir --}}
                    <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                        <button type="button" 
                                @click="showCreateModal = false" 
                                class="px-4 py-2.5 rounded-xl border border-slate-200 text-xs sm:text-sm font-semibold text-slate-700 hover:bg-slate-50 transition">
                            Batal
                        </button>
                        <button type="submit" 
                                :disabled="submitting" 
                                class="px-6 py-2.5 rounded-xl bg-slate-900 text-white text-xs sm:text-sm font-semibold hover:bg-slate-800 disabled:opacity-50 transition shadow-xs flex items-center gap-2">
                            <span x-show="!submitting" class="material-symbols-outlined text-[18px]">send</span>
                            <span x-show="submitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                            <span x-text="submitting ? 'Mengirim...' : 'Kirim Laporan Kerusakan'"></span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- MODAL POP-UP: DETAIL LENGKAP LAPORAN KERUSAKAN & RESOLUSI TEKNISI         --}}
        {{-- ========================================================================= --}}
        <div x-show="showDetailModal" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            
            <div @click.away="showDetailModal = false" 
                 class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-7 shadow-2xl border border-slate-200 relative my-8">
                
                <button type="button" 
                        @click="showDetailModal = false" 
                        class="absolute top-5 right-5 p-1.5 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>

                <template x-if="selectedReport">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="px-2.5 py-0.5 rounded-md bg-slate-100 font-mono text-xs font-bold text-slate-800" x-text="selectedReport.id"></span>
                            <span class="text-xs font-semibold text-slate-500" x-text="selectedReport.created_at"></span>
                        </div>

                        <h2 class="text-lg font-bold text-slate-900" x-text="selectedReport.facility"></h2>
                        <div class="flex items-center gap-2 mt-1 mb-4">
                            <span class="text-xs text-slate-500" x-text="selectedReport.building"></span>
                            <span class="text-slate-300">•</span>
                            <span class="px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700" x-text="selectedReport.category"></span>
                        </div>

                        {{-- Deskripsi Kendala --}}
                        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 mb-4 text-xs space-y-2">
                            <span class="font-bold text-slate-700 block">Uraian Kendala Pelapor:</span>
                            <p class="text-slate-600 leading-relaxed" x-text="selectedReport.description"></p>
                        </div>

                        {{-- Lampiran Foto Bukti --}}
                        <template x-if="selectedReport.photoUrl">
                            <div class="mb-4">
                                <span class="text-xs font-bold text-slate-700 block mb-1.5">Foto Bukti Kerusakan:</span>
                                <div class="w-full h-56 bg-slate-100 rounded-2xl overflow-hidden border border-slate-200 flex items-center justify-center">
                                    <img :src="selectedReport.photoUrl" 
                                         :alt="'Bukti ' + selectedReport.id" 
                                         class="w-full h-full object-contain">
                                </div>
                            </div>
                        </template>

                        {{-- Catatan Resolusi Teknisi --}}
                        <div class="p-4 rounded-2xl border text-xs"
                             :class="selectedReport.status === 'selesai' ? 'bg-emerald-50/70 border-emerald-200 text-emerald-900' : 'bg-slate-50 border-slate-200 text-slate-700'">
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="font-bold flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[16px]" :class="selectedReport.status === 'selesai' ? 'text-emerald-600' : 'text-slate-500'">engineering</span>
                                    <span>Tindak Lanjut & Resolusi Teknisi</span>
                                </span>
                                <span class="font-semibold text-[11px] capitalize" x-text="selectedReport.status"></span>
                            </div>

                            <template x-if="selectedReport.resolution_note">
                                <div>
                                    <p class="leading-relaxed font-medium" x-text="selectedReport.resolution_note"></p>
                                    <div class="mt-2 text-[10px] opacity-75">
                                        Ditangani oleh: <span class="font-semibold" x-text="selectedReport.handler"></span>
                                        <template x-if="selectedReport.resolved_at">
                                            <span> • Selesai: <span x-text="selectedReport.resolved_at"></span></span>
                                        </template>
                                    </div>
                                </div>
                            </template>

                            <template x-if="!selectedReport.resolution_note">
                                <p class="text-slate-400 italic text-[11px]">
                                    <span x-text="selectedReport.status === 'baru' ? 'Menunggu evaluasi awal oleh petugas sarpras.' : 'Sedang dalam penanganan teknisi sarpras.'"></span>
                                </p>
                            </template>
                        </div>

                        <div class="mt-5 flex justify-end">
                            <button type="button" 
                                    @click="showDetailModal = false" 
                                    class="px-5 py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition">
                                Tutup
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

    </div>
</x-app-layout>
