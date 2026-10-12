{{-- 
  ====================================================================================================
  NAMA FILE      : report-history.blade.php
  LOKASI         : resources/views/user/report-history.blade.php
  FUNGSIONALITAS : Antarmuka Terpadu Riwayat & Pelacakan Tiket Kerusakan Fasilitas Kampus (USR-04 & USR-05)
  MODUL CAVA     : 12. Portal Pengguna: Pelaporan Kerusakan Fasilitas & Pelacakan Tiket (USR-04 & USR-05)
  ====================================================================================================

  DESKRIPSI ARSITEKTUR & DESAIN SISTEM:
  1. Halaman Terpadu Pelacakan Tiket Pengaduan (USR-05):
     - Berkas ini menyajikan seluruh histori pengaduan kerusakan fisik dan malfungsi fasilitas yang
       diajukan oleh sivitas akademika (`user_id === Auth::id()`).
     - Menampilkan 5 kartu metrik status agregat: Total Tiket, Baru, Diproses, Selesai, dan Ditolak.
  
  2. Pendekatan Desain Apple-esque Bento & Status Opsi B (Pure Typographic):
     - Menggunakan sudut lengkung halus (`rounded-[2rem]`), border lembut (`border-gray-100`),
       dan bayangan elegan (`shadow-[0_8px_30px_rgb(0,0,0,0.06)]`).
     - Seluruh status dirender menggunakan tipografi murni berbobot semibold dengan warna semantik
       harmonis (Amber = Baru, Sky = Diproses, Emerald = Selesai, Rose = Ditolak) tanpa menggunakan
       bulatan titik (dot indicator) maupun mikro-ikon.
  
  3. Modal Pop-up Terpadu dengan `x-teleport="body"`:
     - Modal 1: Formulir pembuatan tiket baru cepat langsung dari halaman riwayat.
     - Modal 2: Rincian lengkap tiket kerusakan, foto bukti kerusakan, serta catatan resolusi teknisi sarpras.
     - Diteleportasi ke `<body>` peramban untuk mencegah masalah clipping overflow dan z-index stacking context.
  ====================================================================================================
--}}

{{-- Memanggil komponen master layout khusus portal pengguna dengan prop active="report-history" --}}
<x-app-layout title="Status & Riwayat Pelaporan Kerusakan" active="report-history">
    
    {{-- 
      Blok @php: Daftar Kategori Kerusakan Terstandar untuk Modal Formulir Terpadu
    --}}
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

    {{-- 
      Pembungkus Konten Utama dengan State Reaktif Alpine.js:
      - showCreateModal : Visibilitas modal formulir pengaduan kerusakan baru.
      - showDetailModal : Visibilitas modal rincian informasi dan resolusi tiket.
      - selectedReport  : Objek data laporan yang sedang dipilih untuk ditinjau.
      - submitting      : State pencegah double submission saat mengirim form.
      - fileName, imagePreview, fileError : Pengelolaan unggahan gambar di sisi klien.
    --}}
    <div x-data="{
        showCreateModal: {{ $errors->any() || request('action') === 'create' ? 'true' : 'false' }},
        showDetailModal: false,
        selectedReport: null,
        submitting: false,
        fileName: '',
        imagePreview: null,
        fileError: '',

        // Membuka modal rincian laporan
        openDetail(report) {
            this.selectedReport = report;
            this.showDetailModal = true;
        },

        // Handler pemilihan dan validasi file foto di sisi peramban
        handleFile(e) {
            const file = e.target.files[0];
            this.fileError = '';
            if (file) {
                // 1. Validasi Ukuran File (Maksimal 2 MB / 2048 KB)
                if (file.size > 2 * 1024 * 1024) {
                    this.fileError = 'Ukuran berkas melebihi 2 MB (terbaca: ' + (file.size / (1024 * 1024)).toFixed(2) + ' MB). Harap pilih foto yang lebih kecil.';
                    e.target.value = '';
                    this.fileName = '';
                    this.imagePreview = null;
                    return;
                }
                // 2. Validasi Format Berkas Gambar
                if (!['image/jpeg', 'image/png', 'image/jpg'].includes(file.type)) {
                    this.fileError = 'Format file tidak didukung. Harap pilih gambar bertipe JPG atau PNG.';
                    e.target.value = '';
                    this.fileName = '';
                    this.imagePreview = null;
                    return;
                }

                // 3. Konversi ke DataURL untuk Pratinjau Klien
                this.fileName = file.name;
                const reader = new FileReader();
                reader.onload = (event) => {
                    this.imagePreview = event.target.result;
                };
                reader.readAsDataURL(file);
            }
        },

        // Reset foto terpilih
        resetFile() {
            this.imagePreview = null;
            this.fileName = '';
            this.fileError = '';
            const input = document.getElementById('photo-file');
            if (input) input.value = '';
        }
    }" class="space-y-10">

        {{-- Toolbar Aksi Cepat: Tombol Laporkan Kendala Baru --}}
        <div class="flex items-center justify-end">
            <button type="button" 
                    @click="showCreateModal = true" 
                    class="inline-flex items-center justify-center gap-2 px-6 py-2.5 rounded-xl bg-[#1D1D1F] text-white text-sm font-medium hover:bg-black transition shadow-sm active:scale-[0.98] cursor-pointer">
                <span class="material-symbols-outlined text-[18px]">add</span>
                <span>Laporkan Kendala</span>
            </button>
        </div>

        {{-- Flash Session Sukses (Soft Apple Tone) --}}
        @if (session('success'))
            <div class="p-6 rounded-[2rem] bg-emerald-50/70 border border-emerald-100 text-emerald-900 flex items-center gap-4">
                <span class="material-symbols-outlined text-emerald-600 text-[24px] shrink-0">check_circle</span>
                <div>
                    <h4 class="text-sm font-bold">Laporan Berhasil Terkirim!</h4>
                    <p class="text-xs text-emerald-700 mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        {{-- Flash Session Error Validasi Server --}}
        @if ($errors->any())
            <div class="p-6 rounded-[2rem] bg-rose-50/70 border border-rose-100 text-rose-900 flex items-start gap-4">
                <span class="material-symbols-outlined text-rose-600 text-[24px] shrink-0 mt-0.5">error</span>
                <div class="space-y-1">
                    <h4 class="text-sm font-bold">Terdapat Kesalahan Pengisian Formulir!</h4>
                    <ul class="list-disc list-inside text-xs text-rose-700 space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        {{-- ========================================================================================== --}}
        {{-- SEKSI 1: BENTO GRID 5 KARTU METRIK RINGKASAN STATUS LAPORAN KERUSAKAN                      --}}
        {{-- ========================================================================================== --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 sm:gap-6">
            {{-- Metrik 1: Semua Tiket Laporan --}}
            <a href="{{ request()->fullUrlWithQuery(['status' => 'all', 'page' => 1]) }}" 
               class="bg-white rounded-[2rem] p-6 border transition-all duration-200 {{ $activeStatus === 'all' ? 'border-[#1D1D1F] shadow-[0_8px_30px_rgb(0,0,0,0.06)]' : 'border-gray-100 hover:border-gray-300 shadow-[0_8px_30px_rgb(0,0,0,0.02)]' }}">
                <span class="text-xs text-[#86868B] font-medium block">Total Tiket</span>
                <span class="text-3xl sm:text-4xl font-extrabold tracking-tight text-[#1D1D1F] mt-2 block">{{ $counts['all'] ?? 0 }}</span>
                <span class="text-[11px] text-[#86868B] mt-2 block">Semua pengaduan</span>
            </a>

            {{-- Metrik 2: Laporan Baru (Belum Diverifikasi) --}}
            <a href="{{ request()->fullUrlWithQuery(['status' => 'baru', 'page' => 1]) }}" 
               class="bg-white rounded-[2rem] p-6 border transition-all duration-200 {{ $activeStatus === 'baru' ? 'border-[#1D1D1F] shadow-[0_8px_30px_rgb(0,0,0,0.06)]' : 'border-gray-100 hover:border-gray-300 shadow-[0_8px_30px_rgb(0,0,0,0.02)]' }}">
                <span class="text-xs text-amber-800 font-medium block">Laporan Baru</span>
                <span class="text-3xl sm:text-4xl font-extrabold tracking-tight text-[#1D1D1F] mt-2 block">{{ $counts['baru'] ?? 0 }}</span>
                <span class="text-[11px] text-[#86868B] mt-2 block">Menunggu verifikasi</span>
            </a>

            {{-- Metrik 3: Sedang Diproses Teknisi --}}
            <a href="{{ request()->fullUrlWithQuery(['status' => 'diproses', 'page' => 1]) }}" 
               class="bg-white rounded-[2rem] p-6 border transition-all duration-200 {{ $activeStatus === 'diproses' ? 'border-[#1D1D1F] shadow-[0_8px_30px_rgb(0,0,0,0.06)]' : 'border-gray-100 hover:border-gray-300 shadow-[0_8px_30px_rgb(0,0,0,0.02)]' }}">
                <span class="text-xs text-sky-800 font-medium block">Sedang Diproses</span>
                <span class="text-3xl sm:text-4xl font-extrabold tracking-tight text-[#1D1D1F] mt-2 block">{{ $counts['diproses'] ?? 0 }}</span>
                <span class="text-[11px] text-[#86868B] mt-2 block">Dalam tindak lanjut</span>
            </a>

            {{-- Metrik 4: Selesai Ditangani & Diperbaiki --}}
            <a href="{{ request()->fullUrlWithQuery(['status' => 'selesai', 'page' => 1]) }}" 
               class="bg-white rounded-[2rem] p-6 border transition-all duration-200 {{ $activeStatus === 'selesai' ? 'border-[#1D1D1F] shadow-[0_8px_30px_rgb(0,0,0,0.06)]' : 'border-gray-100 hover:border-gray-300 shadow-[0_8px_30px_rgb(0,0,0,0.02)]' }}">
                <span class="text-xs text-emerald-800 font-medium block">Selesai Ditangani</span>
                <span class="text-3xl sm:text-4xl font-extrabold tracking-tight text-[#1D1D1F] mt-2 block">{{ $counts['selesai'] ?? 0 }}</span>
                <span class="text-[11px] text-[#86868B] mt-2 block">Telah terselesaikan</span>
            </a>

            {{-- Metrik 5: Ditolak Petugas --}}
            <a href="{{ request()->fullUrlWithQuery(['status' => 'ditolak', 'page' => 1]) }}" 
               class="bg-white rounded-[2rem] p-6 border transition-all duration-200 {{ $activeStatus === 'ditolak' ? 'border-[#1D1D1F] shadow-[0_8px_30px_rgb(0,0,0,0.06)]' : 'border-gray-100 hover:border-gray-300 shadow-[0_8px_30px_rgb(0,0,0,0.02)]' }}">
                <span class="text-xs text-rose-800 font-medium block">Ditolak</span>
                <span class="text-3xl sm:text-4xl font-extrabold tracking-tight text-[#1D1D1F] mt-2 block">{{ $counts['ditolak'] ?? 0 }}</span>
                <span class="text-[11px] text-[#86868B] mt-2 block">Tidak memenuhi syarat</span>
            </a>
        </div>

        {{-- ========================================================================================== --}}
        {{-- SEKSI 2: SEARCH BAR & INDIKATOR FILTER AKTIF (SEARCH & FILTER PILLS)                       --}}
        {{-- ========================================================================================== --}}
        <div class="space-y-3">
            <form action="{{ url()->current() }}" method="GET" class="w-full">
                {{-- Mempertahankan status filter aktif saat pencarian dieksekusi --}}
                @if (request('status') && request('status') !== 'all')
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                <div class="relative group">
                    <div class="absolute -inset-1 bg-gradient-to-r from-blue-100 to-indigo-100 rounded-2xl blur opacity-25 group-hover:opacity-50 transition duration-1000 group-hover:duration-200"></div>
                    <div class="relative bg-white rounded-2xl shadow-[0_8px_30px_rgb(0,0,0,0.04)] p-1.5 flex items-center border border-gray-100">
                        <span class="material-symbols-outlined text-gray-400 ml-3 mr-2 text-[20px]">search</span>
                        <input type="text" 
                               name="search" 
                               value="{{ request('search') }}" 
                               placeholder="Cari berdasarkan kode tiket, fasilitas, gedung, atau kendala..." 
                               class="flex-1 py-2.5 px-2 border-0 focus:ring-0 focus:border-transparent outline-none text-sm md:text-base bg-transparent text-[#1D1D1F] placeholder:text-[#86868B]" 
                               autocomplete="off">
                        @if (request('search'))
                            <a href="{{ request()->fullUrlWithQuery(['search' => null, 'page' => 1]) }}" 
                               class="text-gray-400 hover:text-[#1D1D1F] p-1 mr-1 transition rounded-lg hover:bg-gray-50 flex items-center justify-center" 
                               title="Hapus kata kunci pencarian">
                                <span class="material-symbols-outlined text-[18px]">close</span>
                            </a>
                        @endif
                        <button type="submit" 
                                class="bg-[#1D1D1F] text-white px-5 py-2.5 rounded-xl text-sm font-medium hover:bg-gray-800 transition shrink-0 cursor-pointer shadow-sm">
                            Cari
                        </button>
                    </div>
                </div>
            </form>

            {{-- Lencana (Pills) Filter Aktif --}}
            @if ((request('status') && request('status') !== 'all') || request()->filled('search'))
                <div class="flex flex-wrap items-center gap-2 pt-1 pb-1">
                    <span class="text-xs font-medium text-gray-500 py-1 mr-1">Filter Aktif:</span>

                    {{-- Pill Status --}}
                    @if (request('status') && request('status') !== 'all')
                        @php
                            $statusLabels = [
                                'baru'     => 'Laporan Baru',
                                'diproses' => 'Sedang Diproses',
                                'selesai'  => 'Selesai Ditangani',
                                'ditolak'  => 'Ditolak',
                            ];
                            $statusLabel = $statusLabels[request('status')] ?? ucfirst(request('status'));
                        @endphp
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-gray-100 text-[#1D1D1F] border border-gray-200 shadow-sm">
                            <span>Status: {{ $statusLabel }}</span>
                            <a href="{{ request()->fullUrlWithQuery(['status' => 'all', 'page' => 1]) }}" 
                               class="text-gray-400 hover:text-rose-600 transition" 
                               title="Hapus filter status">
                                <span class="material-symbols-outlined text-[14px] font-bold">close</span>
                            </a>
                        </span>
                    @endif

                    {{-- Pill Kata Kunci --}}
                    @if (request()->filled('search'))
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium bg-blue-50 text-blue-800 border border-blue-200 shadow-sm">
                            <span>Pencarian: "{{ request('search') }}"</span>
                            <a href="{{ request()->fullUrlWithQuery(['search' => null, 'page' => 1]) }}" 
                               class="text-blue-500 hover:text-blue-900 transition" 
                               title="Hapus kata kunci pencarian">
                                <span class="material-symbols-outlined text-[14px] font-bold">close</span>
                            </a>
                        </span>
                    @endif

                    {{-- Reset Filter --}}
                    <a href="{{ route('user.report-history') }}" 
                       class="text-xs font-medium text-gray-500 hover:text-rose-600 underline px-2 py-1 transition ml-1">
                        Hapus Semua
                    </a>
                </div>
            @endif
        </div>

        {{-- ========================================================================================== --}}
        {{-- SEKSI 3: DAFTAR KARTU TIKET KERUSAKAN BERSIH (PURE TYPOGRAPHIC STATUS)                     --}}
        {{-- ========================================================================================== --}}
        <div class="space-y-4">
            @forelse ($reports as $report)
                {{-- Persiapan Objek Data Detail Tiket untuk Modal Alpine.js --}}
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

                {{-- Kartu Tiket Kerusakan --}}
                <div class="bg-white p-7 sm:p-8 rounded-[2rem] border border-gray-100 hover:shadow-[0_8px_30px_rgb(0,0,0,0.06)] transition duration-300">
                    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
                        
                        {{-- Kolom Kiri: Status Pure Typographic, Info Fasilitas, dan Uraian Masalah --}}
                        <div class="space-y-2 flex-1">
                            <div class="flex flex-wrap items-center gap-2">
                                {{-- Status Opsi B: Pure Typographic Status (Tanpa Icon, Tanpa Dot) --}}
                                @if ($report->status === 'baru')
                                    <span class="text-xs font-semibold tracking-wide text-amber-800">
                                        Laporan Baru
                                    </span>
                                @elseif ($report->status === 'diproses')
                                    <span class="text-xs font-semibold tracking-wide text-sky-800">
                                        Sedang Ditangani Teknisi
                                    </span>
                                @elseif ($report->status === 'selesai')
                                    <span class="text-xs font-semibold tracking-wide text-emerald-800">
                                        Selesai Ditangani
                                    </span>
                                @elseif ($report->status === 'ditolak')
                                    <span class="text-xs font-semibold tracking-wide text-rose-700">
                                        Laporan Ditolak
                                    </span>
                                @endif

                                <span class="text-xs text-[#86868B]">• {{ $report->created_at->translatedFormat('d M Y, H:i') }} WIB</span>
                                <span class="text-xs text-[#86868B]">• {{ $report->category }}</span>
                            </div>

                            <h3 class="text-xl font-bold text-[#1D1D1F]">
                                {{ $report->facility->name ?? 'Fasilitas Kampus' }}
                            </h3>

                            <p class="text-sm text-[#86868B]">
                                {{ ($report->facility->building ?? 'Kampus') . ($report->facility->floor_location ? ' • ' . $report->facility->floor_location : '') }}
                            </p>

                            <p class="text-xs text-[#86868B] line-clamp-2 pt-0.5">
                                {{ $report->description }}
                            </p>

                            {{-- Catatan Resolusi Teknisi Sarpras (Jika Sudah Ada Tindak Lanjut) --}}
                            @if (!empty($report->resolution_note))
                                <div class="p-3.5 rounded-xl bg-[#F9F9FB] text-xs text-[#86868B] mt-2">
                                    <strong class="text-[#1D1D1F]">Catatan Teknisi ({{ $report->handler->name ?? 'Tim Sarpras' }}):</strong> {{ $report->resolution_note }}
                                </div>
                            @endif
                        </div>
                        
                        {{-- Kolom Kanan: Tombol Buka Detail Laporan --}}
                        <div class="flex items-center gap-3 shrink-0 pt-4 lg:pt-0 border-t lg:border-t-0 border-gray-100 self-stretch lg:self-center justify-end">
                            <button type="button" 
                                    @click="openDetail({{ json_encode($reportDetail) }})" 
                                    class="bg-[#1D1D1F] text-white px-5 py-2.5 rounded-xl text-xs font-medium hover:bg-black transition cursor-pointer">
                                Detail Laporan
                            </button>
                        </div>

                    </div>
                </div>
            @empty
                {{-- Keadaan Kosong (Empty State) --}}
                <div class="bg-white rounded-[2rem] p-12 text-center border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)] space-y-4">
                    <div class="w-16 h-16 rounded-full bg-[#F5F5F7] text-[#86868B] flex items-center justify-center mx-auto">
                        <span class="material-symbols-outlined text-[32px]">handyman</span>
                    </div>
                    <div>
                        <h4 class="text-lg font-bold text-[#1D1D1F]">Belum Ada Riwayat Laporan</h4>
                        <p class="text-sm text-[#86868B] max-w-md mx-auto mt-1">
                            @if (request('search') || request('status', 'all') !== 'all')
                                Tidak ditemukan tiket pengaduan yang sesuai dengan kriteria filter atau pencarian Anda.
                            @else
                                Anda belum pernah mengajukan laporan kerusakan atau kendala fasilitas kampus.
                            @endif
                        </p>
                    </div>
                    <div>
                        <button type="button" 
                                @click="showCreateModal = true" 
                                class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-[#1D1D1F] text-white text-sm font-medium hover:bg-black transition shadow-sm cursor-pointer">
                            <span class="material-symbols-outlined text-[18px]">add</span>
                            <span>Laporkan Kerusakan Sekarang</span>
                        </button>
                    </div>
                </div>
            @endforelse
        </div>

        {{-- Navigasi Paginasi Laravel (Pagination Links) --}}
        @if (isset($reports) && $reports->hasPages())
            <div class="pt-4">
                {{ $reports->links() }}
            </div>
        @endif

        {{-- ========================================================================================== --}}
        {{-- MODAL POP-UP 1: FORMULIR PELAPORAN KERUSAKAN BARU (DITELEPORTASI KE <body>)                --}}
        {{-- ========================================================================================== --}}
        <template x-teleport="body">
            <div x-show="showCreateModal" 
                 x-cloak 
                 class="fixed inset-0 z-[100] overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 sm:p-6"
                 @click.self="showCreateModal = false">
                
                <div class="bg-white rounded-[32px] max-w-2xl w-full p-8 border border-black/[0.05] shadow-[0_24px_64px_rgba(0,0,0,0.12)] relative my-auto max-h-[85vh] overflow-y-auto no-scrollbar font-sans">
                    
                    {{-- Tombol Tutup Modal --}}
                    <button type="button" 
                            @click="showCreateModal = false" 
                            class="absolute top-6 right-6 p-2 rounded-full text-[#86868B] hover:text-[#1D1D1F] hover:bg-[#F5F5F7] transition cursor-pointer">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>

                    <div class="mb-6 space-y-1">
                        <h2 class="text-2xl font-bold tracking-tight text-[#1D1D1F]">Pelaporan Kerusakan Fasilitas</h2>
                        <p class="text-sm text-[#86868B]">Sampaikan kendala kerusakan atau malfungsi sarana kampus agar segera ditangani tim teknisi sarpras.</p>
                    </div>

                    {{-- Form Upload Pelaporan --}}
                    <form action="{{ route('user.reports.store') }}" 
                          method="POST" 
                          enctype="multipart/form-data" 
                          @submit="submitting = true" 
                          class="space-y-6">
                        @csrf

                        {{-- 1. Pilihan Fasilitas Rusak --}}
                        <div>
                            <label for="rep-facility" class="block text-xs font-semibold text-[#86868B] uppercase tracking-wider mb-2">
                                Fasilitas / Ruangan Bermasalah <span class="text-rose-500">*</span>
                            </label>
                            <select id="rep-facility" 
                                    name="facility_id" 
                                    required 
                                    class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-4 py-3 rounded-xl text-sm {{ $errors->has('facility_id') ? 'ring-2 ring-rose-400 bg-rose-50/50' : '' }} focus:outline-none focus:ring-1 focus:ring-black/10 focus:bg-white transition cursor-pointer">
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

                        {{-- 2. Kategori Kerusakan --}}
                        <div>
                            <label for="rep-category" class="block text-xs font-semibold text-[#86868B] uppercase tracking-wider mb-2">
                                Kategori Kerusakan Sarana
                            </label>
                            <select id="rep-category" 
                                    name="category" 
                                    class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] px-4 py-3 rounded-xl text-sm focus:outline-none focus:ring-1 focus:ring-black/10 focus:bg-white transition cursor-pointer">
                                <option value="">-- Pilih Kategori Kendala (Opsional) --</option>
                                @foreach ($categories as $cat)
                                    <option value="{{ $cat }}" {{ old('category') === $cat ? 'selected' : '' }}>
                                        {{ $cat }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- 3. Deskripsi Masalah (Min. 10 Karakter) --}}
                        <div>
                            <label for="rep-desc" class="block text-xs font-semibold text-[#86868B] uppercase tracking-wider mb-2">
                                Deskripsi Kerusakan / Lokasi Spesifik <span class="text-rose-500">*</span>
                            </label>
                            <textarea id="rep-desc" 
                                      name="description" 
                                      rows="3" 
                                      required 
                                      minlength="10" 
                                      maxlength="1000" 
                                      placeholder="Jelaskan secara spesifik kerusakan sarana, misalnya: 'Unit AC di dekat pintu keluar mengeluarkan suara bising dan proyektor tidak merespon kabel HDMI...' (Maks. 1.000 karakter)" 
                                      class="w-full bg-[#F5F5F7] border-0 text-[#1D1D1F] placeholder:text-[#86868B] p-4 rounded-xl text-sm {{ $errors->has('description') ? 'ring-2 ring-rose-400 bg-rose-50/50' : '' }} focus:outline-none focus:ring-1 focus:ring-black/10 focus:bg-white transition leading-relaxed">{{ old('description') }}</textarea>
                            @error('description')
                                <p class="mt-1 text-xs text-rose-600 font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        {{-- 4. Unggah Foto Bukti Kerusakan --}}
                        <div>
                            <label class="block text-xs font-semibold text-[#86868B] uppercase tracking-wider mb-2">
                                Unggah Foto Bukti Kerusakan <span class="text-rose-500">*</span>
                                <span class="text-[#86868B] font-normal lowercase">(Maks. 2 MB - JPG/PNG)</span>
                            </label>

                            <div x-show="fileError" x-cloak class="mb-2 p-3 rounded-xl bg-rose-50 border border-rose-100 text-rose-800 text-xs flex items-center gap-2">
                                <span class="material-symbols-outlined text-[16px] text-rose-600 shrink-0">warning</span>
                                <span x-text="fileError"></span>
                            </div>

                            <div class="border border-dashed border-gray-200 bg-[#F9F9FB] rounded-2xl p-6 text-center hover:bg-gray-50 transition">
                                <template x-if="!imagePreview">
                                    <div class="flex flex-col items-center">
                                        <span class="material-symbols-outlined text-[#86868B] text-[32px] mb-1">add_photo_alternate</span>
                                        <span class="text-xs font-semibold text-[#1D1D1F]">Pilih foto bukti dari perangkat Anda</span>
                                        <span class="text-[11px] text-[#86868B] mt-0.5">Format JPG / PNG hingga ukuran 2 MB</span>
                                        <label for="photo-file" class="mt-3 px-4 py-2 rounded-xl bg-white border border-gray-200 text-xs font-medium text-[#1D1D1F] hover:bg-gray-50 shadow-xs cursor-pointer transition">
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
                                        <img :src="imagePreview" alt="Pratinjau Foto" class="h-32 w-auto object-cover rounded-xl border border-gray-100 mb-2 shadow-xs">
                                        <span class="text-xs font-mono font-medium text-[#1D1D1F] truncate max-w-xs" x-text="fileName"></span>
                                        <button type="button" @click="resetFile()" class="mt-2 text-xs font-medium text-rose-600 hover:text-rose-800 hover:underline flex items-center gap-1 cursor-pointer">
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
                        <div class="pt-4 border-t border-gray-100 flex items-center justify-end gap-3">
                            <button type="button" 
                                    @click="showCreateModal = false" 
                                    class="px-5 py-2.5 text-sm font-medium text-[#86868B] hover:text-[#1D1D1F] transition cursor-pointer">
                                Batal
                            </button>
                            <button type="submit" 
                                    :disabled="submitting" 
                                    class="px-6 py-2.5 rounded-xl bg-[#1D1D1F] text-white text-sm font-medium hover:bg-black disabled:opacity-50 transition shadow-sm flex items-center gap-2 cursor-pointer">
                                <span x-show="!submitting" class="material-symbols-outlined text-[18px]">send</span>
                                <span x-show="submitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                                <span x-text="submitting ? 'Mengirim...' : 'Kirim Laporan Kerusakan'"></span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </template>

        {{-- ========================================================================================== --}}
        {{-- MODAL POP-UP 2: DETAIL LENGKAP LAPORAN KERUSAKAN & RESOLUSI TEKNISI                        --}}
        {{-- ========================================================================================== --}}
        <template x-teleport="body">
            <div x-show="showDetailModal" 
                 x-cloak 
                 class="fixed inset-0 z-[100] overflow-y-auto bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 sm:p-6"
                 @click.self="showDetailModal = false">
                
                <div class="bg-white rounded-[32px] max-w-lg w-full p-8 border border-black/[0.05] shadow-[0_24px_64px_rgba(0,0,0,0.12)] relative my-auto max-h-[85vh] overflow-y-auto no-scrollbar font-sans">
                    
                    {{-- Tombol Tutup Modal --}}
                    <button type="button" 
                            @click="showDetailModal = false" 
                            class="absolute top-6 right-6 p-2 rounded-full text-[#86868B] hover:text-[#1D1D1F] hover:bg-[#F5F5F7] transition cursor-pointer">
                        <span class="material-symbols-outlined text-[20px]">close</span>
                    </button>

                    <template x-if="selectedReport">
                        <div class="space-y-6">
                            <div>
                                {{-- Status Opsi B: Pure Typographic Status --}}
                                <div class="mb-2">
                                    <template x-if="selectedReport.status === 'baru'">
                                        <span class="text-xs font-semibold tracking-wide text-amber-800">
                                            Laporan Baru • <span x-text="selectedReport.created_at"></span>
                                        </span>
                                    </template>
                                    <template x-if="selectedReport.status === 'diproses'">
                                        <span class="text-xs font-semibold tracking-wide text-sky-800">
                                            Sedang Ditangani Teknisi • <span x-text="selectedReport.created_at"></span>
                                        </span>
                                    </template>
                                    <template x-if="selectedReport.status === 'selesai'">
                                        <span class="text-xs font-semibold tracking-wide text-emerald-800">
                                            Selesai Ditangani • <span x-text="selectedReport.created_at"></span>
                                        </span>
                                    </template>
                                    <template x-if="selectedReport.status === 'ditolak'">
                                        <span class="text-xs font-semibold tracking-wide text-rose-700">
                                            Laporan Ditolak • <span x-text="selectedReport.created_at"></span>
                                        </span>
                                    </template>
                                </div>

                                <h2 class="text-2xl font-bold tracking-tight text-[#1D1D1F]" x-text="selectedReport.facility"></h2>
                                <p class="text-sm text-[#86868B] mt-1" x-text="selectedReport.building + ' • ' + selectedReport.category"></p>
                            </div>

                            {{-- Uraian Deskripsi Kendala --}}
                            <div class="bg-[#F9F9FB] rounded-2xl p-5 space-y-1.5 text-xs text-[#86868B]">
                                <span class="font-semibold text-[#1D1D1F] block text-sm">Uraian Kendala:</span>
                                <p class="leading-relaxed" x-text="selectedReport.description"></p>
                            </div>

                            {{-- Pratinjau Foto Bukti Kerusakan --}}
                            <template x-if="selectedReport.photoUrl">
                                <div class="space-y-2">
                                    <span class="text-xs font-semibold text-[#86868B] uppercase tracking-wider block">Foto Bukti Kerusakan:</span>
                                    <div class="w-full h-56 bg-[#F5F5F7] rounded-2xl overflow-hidden flex items-center justify-center">
                                        <img :src="selectedReport.photoUrl" 
                                             :alt="'Bukti ' + selectedReport.id" 
                                             class="w-full h-full object-contain">
                                    </div>
                                </div>
                            </template>

                            {{-- Rincian Tindak Lanjut & Resolusi Teknisi --}}
                            <div class="p-5 rounded-2xl bg-[#F9F9FB] text-xs text-[#86868B] space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="font-semibold text-[#1D1D1F] text-sm">Tindak Lanjut & Resolusi</span>
                                    <span class="capitalize font-medium" x-text="selectedReport.status"></span>
                                </div>

                                {{-- Catatan Resolusi Jika Sudah Diisi --}}
                                <template x-if="selectedReport.resolution_note">
                                    <div>
                                        <p class="leading-relaxed text-[#1D1D1F]" x-text="selectedReport.resolution_note"></p>
                                        <div class="mt-2 text-[11px] text-[#86868B]">
                                            Ditangani oleh: <span class="font-semibold text-[#1D1D1F]" x-text="selectedReport.handler"></span>
                                            <template x-if="selectedReport.resolved_at">
                                                <span> • Selesai: <span x-text="selectedReport.resolved_at"></span></span>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                {{-- Pesan Menunggu Jika Belum Ada Catatan Resolusi --}}
                                <template x-if="!selectedReport.resolution_note">
                                    <p class="italic text-[11px]">
                                        <span x-text="selectedReport.status === 'baru' ? 'Menunggu penugasan dan evaluasi oleh petugas sarpras.' : 'Sedang dalam penanganan teknisi sarpras.'"></span>
                                    </p>
                                </template>
                            </div>

                            {{-- Tombol Tutup --}}
                            <div class="pt-2 flex justify-end">
                                <button type="button" 
                                        @click="showDetailModal = false" 
                                        class="px-6 py-2.5 rounded-xl bg-[#1D1D1F] text-white text-xs font-medium hover:bg-black transition cursor-pointer">
                                    Tutup
                                </button>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </template>

    </div>
</x-app-layout>
