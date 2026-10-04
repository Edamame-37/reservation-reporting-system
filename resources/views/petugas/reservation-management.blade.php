{{-- 
  NAMA FILE      : reservation-management.blade.php
  FUNGSIONALITAS : Lembar Kerja Manajemen & Approval Antrean Reservasi Petugas Sarpras
  DESKRIPSI      : Menampilkan antrean permohonan reservasi dengan prinsip Progressive Disclosure:
                   tampilan awal hanya memuat informasi esensial yang lapang & bersih, sementara rincian
                   lengkap (surat izin, tujuan kegiatan, identitas lengkap) dibuka via modal peninjauan.
  CARA KERJA     : Terintegrasi dengan <x-petugas-layout active="reservation-management">, pagination server-side,
                   dan modal Alpine.js untuk peninjauan, persetujuan, penolakan, serta pembatalan darurat.
--}}

@php
    $currentStatus = $currentStatus ?? 'pending';
    $search = $search ?? '';
@endphp

<x-petugas-layout title="Manajemen & Approval Reservasi" active="reservation-management">
    <div x-data="{
        showRejectModal: false,
        showCancelModal: false,
        showDetailModal: false,
        selectedId: null,
        applicantName: '',
        ticketCode: '',
        venueName: '',
        selectedDetail: null,

        openReject(id, name, code) {
            this.selectedId = id;
            this.applicantName = name;
            this.ticketCode = code;
            this.showDetailModal = false;
            this.showRejectModal = true;
        },

        openCancel(id, code, venue, applicant) {
            this.selectedId = id;
            this.ticketCode = code;
            this.venueName = venue;
            this.applicantName = applicant || '';
            this.showDetailModal = false;
            this.showCancelModal = true;
        },

        openDetail(data) {
            this.selectedDetail = data;
            this.showDetailModal = true;
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
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Manajemen Antrean Reservasi</h1>
                <p class="text-xs text-slate-500 mt-0.5">
                    Verifikasi permohonan peminjaman fasilitas kampus, alokasi slot ruangan, dan peninjauan riwayat.
                </p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="location.reload()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-xs font-medium text-slate-700 hover:bg-slate-50 transition shadow-2xs cursor-pointer">
                    <span class="material-symbols-outlined text-[16px] text-slate-500">sync</span>
                    <span>Sinkron Data</span>
                </button>
            </div>
        </div>

        {{-- 3. Bento Status KPI Cards (Langsung Berfungsi Sebagai Filter Cepat) --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Card 1: Menunggu Verifikasi --}}
            <a href="{{ route('petugas.reservation-management', ['status' => 'pending', 'search' => $search]) }}" class="p-4 sm:p-5 rounded-xl border transition-all flex flex-col justify-between {{ $currentStatus === 'pending' ? 'bg-slate-50/90 border-slate-900 ring-1 ring-slate-900 shadow-2xs' : 'bg-white border-slate-200/80 hover:border-slate-300' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium {{ $currentStatus === 'pending' ? 'text-slate-900 font-semibold' : 'text-slate-500' }}">Menunggu Verifikasi</span>
                    <span class="text-[11px] px-2 py-0.5 rounded-full font-bold {{ $currentStatus === 'pending' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700' }}">{{ $pendingCount }}</span>
                </div>
                <div class="text-2xl sm:text-3xl font-bold font-mono text-slate-900 mt-3">{{ $pendingCount }}</div>
                <div class="text-[11px] text-slate-500 mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between">
                    <span>Permohonan baru</span>
                    <span class="font-medium text-slate-700">&rarr;</span>
                </div>
            </a>

            {{-- Card 2: Disetujui --}}
            <a href="{{ route('petugas.reservation-management', ['status' => 'approved', 'search' => $search]) }}" class="p-4 sm:p-5 rounded-xl border transition-all flex flex-col justify-between {{ $currentStatus === 'approved' ? 'bg-slate-50/90 border-slate-900 ring-1 ring-slate-900 shadow-2xs' : 'bg-white border-slate-200/80 hover:border-slate-300' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium {{ $currentStatus === 'approved' ? 'text-slate-900 font-semibold' : 'text-slate-500' }}">Disetujui</span>
                    <span class="text-[11px] px-2 py-0.5 rounded-full font-bold {{ $currentStatus === 'approved' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700' }}">{{ $approvedCount }}</span>
                </div>
                <div class="text-2xl sm:text-3xl font-bold font-mono text-slate-900 mt-3">{{ $approvedCount }}</div>
                <div class="text-[11px] text-slate-500 mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between">
                    <span>Terkonfirmasi aktif</span>
                    <span class="font-medium text-slate-700">&rarr;</span>
                </div>
            </a>

            {{-- Card 3: Ditolak --}}
            <a href="{{ route('petugas.reservation-management', ['status' => 'rejected', 'search' => $search]) }}" class="p-4 sm:p-5 rounded-xl border transition-all flex flex-col justify-between {{ $currentStatus === 'rejected' ? 'bg-slate-50/90 border-slate-900 ring-1 ring-slate-900 shadow-2xs' : 'bg-white border-slate-200/80 hover:border-slate-300' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium {{ $currentStatus === 'rejected' ? 'text-slate-900 font-semibold' : 'text-slate-500' }}">Ditolak</span>
                    <span class="text-[11px] px-2 py-0.5 rounded-full font-bold {{ $currentStatus === 'rejected' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700' }}">{{ $rejectedCount }}</span>
                </div>
                <div class="text-2xl sm:text-3xl font-bold font-mono text-slate-900 mt-3">{{ $rejectedCount }}</div>
                <div class="text-[11px] text-slate-500 mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between">
                    <span>Tidak disetujui</span>
                    <span class="font-medium text-slate-700">&rarr;</span>
                </div>
            </a>

            {{-- Card 4: Dibatalkan --}}
            <a href="{{ route('petugas.reservation-management', ['status' => 'cancelled', 'search' => $search]) }}" class="p-4 sm:p-5 rounded-xl border transition-all flex flex-col justify-between {{ $currentStatus === 'cancelled' ? 'bg-slate-50/90 border-slate-900 ring-1 ring-slate-900 shadow-2xs' : 'bg-white border-slate-200/80 hover:border-slate-300' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-medium {{ $currentStatus === 'cancelled' ? 'text-slate-900 font-semibold' : 'text-slate-500' }}">Dibatalkan</span>
                    <span class="text-[11px] px-2 py-0.5 rounded-full font-bold {{ $currentStatus === 'cancelled' ? 'bg-slate-900 text-white' : 'bg-slate-100 text-slate-700' }}">{{ $cancelledCount }}</span>
                </div>
                <div class="text-2xl sm:text-3xl font-bold font-mono text-slate-900 mt-3">{{ $cancelledCount }}</div>
                <div class="text-[11px] text-slate-500 mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between">
                    <span>Mandiri / Darurat</span>
                    <span class="font-medium text-slate-700">&rarr;</span>
                </div>
            </a>
        </div>

        {{-- 4. Toolbar Pencarian & Status Aktif --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 bg-white p-3.5 rounded-xl border border-slate-200/80 shadow-2xs">
            <div class="flex items-center gap-2 text-xs">
                <span class="text-slate-500">Filter Aktif:</span>
                <span class="font-semibold text-slate-900 capitalize">
                    @if($currentStatus === 'pending') Menunggu Verifikasi
                    @elseif($currentStatus === 'approved') Disetujui
                    @elseif($currentStatus === 'rejected') Ditolak
                    @elseif($currentStatus === 'cancelled') Dibatalkan
                    @else Semua Status
                    @endif
                </span>
                <span class="text-slate-300">&bull;</span>
                <a href="{{ route('petugas.reservation-management', ['status' => 'semua', 'search' => $search]) }}" class="text-xs font-medium text-slate-600 hover:text-slate-900 underline">
                    Lihat Semua ({{ $totalCount }})
                </a>
            </div>

            <form method="GET" action="{{ route('petugas.reservation-management') }}" class="relative w-full sm:w-72">
                <input type="hidden" name="status" value="{{ $currentStatus }}">
                <span class="material-symbols-outlined absolute left-3 top-2 text-slate-400 text-[18px]">search</span>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari tiket, pemohon, ruang..." class="w-full pl-9 pr-8 py-1.5 text-xs bg-slate-50 border border-slate-200 rounded-lg focus:outline-none focus:ring-1 focus:ring-slate-900 focus:bg-white transition">
                @if($search)
                    <a href="{{ route('petugas.reservation-management', ['status' => $currentStatus]) }}" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-700">
                        <span class="material-symbols-outlined text-[16px]">close</span>
                    </a>
                @endif
            </form>
        </div>

        {{-- 5. Daftar Antrean Reservasi Lapang (Spacious Bento List - Progressive Disclosure) --}}
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-2xs overflow-hidden">
            <div class="divide-y divide-slate-100">
                @forelse($reservations as $reservation)
                    @php
                        $endDateTime = \Carbon\Carbon::parse($reservation->reservation_date->format('Y-m-d') . ' ' . $reservation->end_time);
                        $isFinished = $reservation->status === 'completed' || $endDateTime->isPast();

                        $detailPayload = [
                            'id' => $reservation->id,
                            'ticket_code' => $reservation->ticket_code ?? 'TKT-' . $reservation->id,
                            'status' => $reservation->status,
                            'is_finished' => $isFinished,
                            'applicant_name' => $reservation->user->name ?? 'Pemohon Sivitas',
                            'applicant_identity' => $reservation->user->identity_number ?? '-',
                            'applicant_department' => $reservation->user->department ?? '-',
                            'applicant_email' => $reservation->user->email ?? '-',
                            'applicant_role' => $reservation->user->role ?? 'mahasiswa',
                            'facility_name' => $reservation->facility->name ?? 'Fasilitas',
                            'facility_building' => $reservation->facility->building ?? '-',
                            'facility_floor' => $reservation->facility->floor_location ?? null,
                            'facility_capacity' => $reservation->facility->capacity ?? '-',
                            'reservation_date' => $reservation->reservation_date->translatedFormat('d F Y'),
                            'start_time' => substr($reservation->start_time ?? '00:00', 0, 5),
                            'end_time' => substr($reservation->end_time ?? '00:00', 0, 5),
                            'total_slots' => $reservation->total_slots ?? 1,
                            'purpose' => $reservation->purpose ?? '-',
                            'participants_count' => $reservation->participants_count ?? 1,
                            'permit_letter_path' => $reservation->permit_letter_path ? asset('storage/' . $reservation->permit_letter_path) : null,
                            'rejection_reason' => $reservation->rejection_reason ?? null,
                            'cancellation_reason' => $reservation->cancellation_reason ?? null,
                            'reviewed_by' => $reservation->reviewer->name ?? null,
                            'reviewed_at' => $reservation->reviewed_at ? $reservation->reviewed_at->translatedFormat('d M Y H:i') : null,
                        ];
                    @endphp

                    {{-- Baris Antrean Lapang & Bersih --}}
                    <div class="px-5 sm:px-6 py-4 sm:py-5 hover:bg-slate-50/70 transition-colors flex flex-col md:flex-row md:items-center justify-between gap-4">
                        {{-- Data Esensial Kiri: Waktu, Ruang, dan Pemohon --}}
                        <div class="min-w-0 flex-1">
                            <div class="text-xs text-slate-500 flex flex-wrap items-center gap-2">
                                <span class="font-mono text-slate-700 font-medium">{{ $reservation->ticket_code ?? 'TKT-' . $reservation->id }}</span>
                                <span class="text-slate-300">&bull;</span>
                                <span class="font-medium text-slate-700">{{ !empty($reservation->reservation_date) ? $reservation->reservation_date->translatedFormat('d M Y') : '-' }}</span>
                                <span class="text-slate-300">&bull;</span>
                                <span class="font-mono text-slate-700">{{ substr($reservation->start_time ?? '00:00', 0, 5) }} - {{ substr($reservation->end_time ?? '00:00', 0, 5) }} WIB</span>
                                @if($isFinished)
                                    <span class="text-[10px] text-slate-500 bg-slate-100 px-1.5 py-0.5 rounded">Selesai</span>
                                @endif
                            </div>

                            <div class="text-sm font-semibold text-slate-900 mt-1 truncate">
                                {{ $reservation->facility->name ?? 'Fasilitas Kampus' }}
                                <span class="text-xs font-normal text-slate-500">({{ $reservation->facility->building ?? 'Gedung' }})</span>
                            </div>

                            <div class="text-xs text-slate-500 mt-0.5">
                                Pemohon: <span class="font-medium text-slate-700">{{ $reservation->user->name ?? 'Pemohon' }}</span>
                            </div>
                        </div>

                        {{-- Data Kanan: Status & Aksi Selaras dengan Dasbor Operasional --}}
                        <div class="flex items-center justify-between md:justify-end gap-3 shrink-0 pt-3 md:pt-0 border-t md:border-t-0 border-slate-100">
                            <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-700">
                                {{ $reservation->status === 'pending' ? 'Menunggu Verifikasi' : ($reservation->status === 'approved' ? 'Disetujui' : ($reservation->status === 'rejected' ? 'Ditolak' : 'Dibatalkan')) }}
                            </span>

                            @if($reservation->status === 'pending')
                                <button type="button" @click='openDetail(@json($detailPayload))' class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-xs font-medium text-slate-700 hover:bg-slate-900 hover:text-white hover:border-slate-900 transition-colors shadow-2xs cursor-pointer">
                                    Tinjau Permohonan
                                </button>
                            @else
                                <button type="button" @click='openDetail(@json($detailPayload))' class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-xs font-medium text-slate-700 hover:bg-slate-900 hover:text-white hover:border-slate-900 transition-colors shadow-2xs cursor-pointer">
                                    Lihat Detail
                                </button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="py-16 px-4 text-center text-xs text-slate-400">
                        @if($currentStatus === 'pending')
                            Tidak ada permohonan reservasi baru yang menunggu persetujuan.
                        @else
                            Tidak ditemukan data reservasi untuk filter yang dipilih.
                        @endif
                    </div>
                @endforelse
            </div>

            {{-- Paginasi Server-Side Laravel --}}
            @if ($reservations->total() > 0)
                <div class="p-3.5 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                    <div class="text-slate-500">
                        Menampilkan <strong class="text-slate-900">{{ $reservations->firstItem() ?? 0 }}</strong> - <strong class="text-slate-900">{{ $reservations->lastItem() ?? 0 }}</strong> dari <strong class="text-slate-900">{{ $reservations->total() }}</strong> data
                    </div>
                    <div>
                        {{ $reservations->links() }}
                    </div>
                </div>
            @endif
        </div>

        {{-- 6. Modal Detail Reservasi Lengkap (Progressive Disclosure) --}}
        <div x-show="showDetailModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="showDetailModal = false" class="bg-white rounded-2xl max-w-2xl w-full p-6 sm:p-7 shadow-xl border border-slate-200 relative">
                <button type="button" @click="showDetailModal = false" class="absolute top-5 right-5 p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>

                <template x-if="selectedDetail">
                    <div class="flex flex-col gap-5">
                        {{-- Header Modal --}}
                        <div class="pr-8">
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-base font-bold text-slate-900" x-text="selectedDetail.ticket_code"></span>
                                <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-700" x-text="selectedDetail.status === 'pending' ? 'Menunggu Verifikasi' : (selectedDetail.status === 'approved' ? 'Disetujui' : (selectedDetail.status === 'rejected' ? 'Ditolak' : 'Dibatalkan'))"></span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">Peninjauan permohonan reservasi fasilitas kampus</p>
                        </div>

                        {{-- Rincian Bento Grid 2 Kolom --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Kolom 1: Data Pemohon --}}
                            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80">
                                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-2">Identitas Pemohon</div>
                                <div class="font-semibold text-slate-900 text-sm" x-text="selectedDetail.applicant_name"></div>
                                <div class="text-xs text-slate-600 mt-1.5 space-y-1">
                                    <p><span class="text-slate-500">NIM / NIP:</span> <span class="font-mono text-slate-800" x-text="selectedDetail.applicant_identity"></span></p>
                                    <p><span class="text-slate-500">Prodi / Unit:</span> <span class="text-slate-800" x-text="selectedDetail.applicant_department"></span></p>
                                    <p><span class="text-slate-500">Email:</span> <span class="text-slate-800" x-text="selectedDetail.applicant_email"></span></p>
                                </div>
                            </div>

                            {{-- Kolom 2: Fasilitas & Waktu --}}
                            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80">
                                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-2">Fasilitas & Waktu</div>
                                <div class="font-semibold text-slate-900 text-sm" x-text="selectedDetail.facility_name"></div>
                                <div class="text-xs text-slate-600 mt-1.5 space-y-1">
                                    <p><span class="text-slate-500">Lokasi:</span> <span class="text-slate-800" x-text="selectedDetail.facility_building + (selectedDetail.facility_floor ? ' (Lt. ' + selectedDetail.facility_floor + ')' : '')"></span></p>
                                    <p><span class="text-slate-500">Kapasitas:</span> <span class="text-slate-800" x-text="selectedDetail.facility_capacity + ' Kursi'"></span></p>
                                    <p><span class="text-slate-500">Jadwal:</span> <span class="font-mono font-medium text-slate-900" x-text="selectedDetail.reservation_date + ', ' + selectedDetail.start_time + ' - ' + selectedDetail.end_time + ' WIB'"></span> (<span x-text="selectedDetail.total_slots + ' slot'"></span>)</p>
                                </div>
                            </div>

                            {{-- Kolom 3: Tujuan Kegiatan & Berkas Surat Izin (Penuh) --}}
                            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 md:col-span-2">
                                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Tujuan Kegiatan</div>
                                <p class="text-xs text-slate-800 leading-relaxed bg-white p-3 rounded-lg border border-slate-200/80" x-text="selectedDetail.purpose || '-'"></p>
                                
                                <div class="mt-3 flex flex-wrap items-center justify-between gap-3 text-xs">
                                    <div class="text-slate-600">
                                        Estimasi Peserta: <strong class="text-slate-900" x-text="(selectedDetail.participants_count || 1) + ' Orang'"></strong>
                                    </div>
                                    <div>
                                        <template x-if="selectedDetail.permit_letter_path">
                                            <a :href="selectedDetail.permit_letter_path" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-900 text-white font-medium hover:bg-slate-800 transition shadow-2xs">
                                                <span class="material-symbols-outlined text-[14px]">attachment</span>
                                                <span>Buka Berkas Surat Izin</span>
                                            </a>
                                        </template>
                                        <template x-if="!selectedDetail.permit_letter_path">
                                            <span class="text-slate-400 italic">Tidak melampirkan berkas fisik</span>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- Kolom 4: Alasan Penolakan / Pembatalan --}}
                            <template x-if="selectedDetail.rejection_reason">
                                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 md:col-span-2">
                                    <div class="text-[11px] font-semibold text-rose-700 uppercase tracking-wider mb-1">Alasan Penolakan</div>
                                    <p class="text-xs text-slate-800" x-text="selectedDetail.rejection_reason"></p>
                                </div>
                            </template>

                            <template x-if="selectedDetail.cancellation_reason">
                                <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200 md:col-span-2">
                                    <div class="text-[11px] font-semibold text-slate-700 uppercase tracking-wider mb-1">Alasan Pembatalan</div>
                                    <p class="text-xs text-slate-800" x-text="selectedDetail.cancellation_reason"></p>
                                </div>
                            </template>

                            {{-- Log Peninjauan --}}
                            <template x-if="selectedDetail.reviewed_by || selectedDetail.reviewed_at">
                                <div class="p-3 rounded-xl bg-slate-100 border border-slate-200/80 md:col-span-2 text-xs flex items-center justify-between text-slate-600">
                                    <span>Ditinjau oleh: <strong class="text-slate-900" x-text="selectedDetail.reviewed_by"></strong></span>
                                    <span x-text="'Waktu: ' + selectedDetail.reviewed_at"></span>
                                </div>
                            </template>
                        </div>

                        {{-- Footer Aksi Modal: Langsung Setujui / Tolak / Batal Darurat --}}
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-slate-100">
                            <div>
                                <button type="button" @click="showDetailModal = false" class="px-3.5 py-1.5 rounded-lg border border-slate-200 text-slate-700 text-xs font-medium hover:bg-slate-50 transition">
                                    Tutup
                                </button>
                            </div>

                            <div class="flex items-center gap-2">
                                {{-- Jika status PENDING: Tombol Tolak dan Setujui --}}
                                <template x-if="selectedDetail.status === 'pending'">
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="openReject(selectedDetail.id, selectedDetail.applicant_name, selectedDetail.ticket_code)" class="px-3.5 py-1.5 rounded-lg border border-slate-200 text-rose-700 hover:bg-rose-50 hover:border-rose-200 transition text-xs font-medium cursor-pointer">
                                            Tolak Permohonan
                                        </button>
                                        <form :action="'{{ url('/petugas/reservations') }}/' + selectedDetail.id + '/approve'" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" onclick="return confirm('Konfirmasi persetujuan reservasi ini? Slot jadwal akan resmi dialokasikan.')" class="px-4 py-1.5 rounded-lg bg-slate-900 text-white text-xs font-medium hover:bg-slate-800 transition shadow-2xs cursor-pointer">
                                                Setujui Permohonan
                                            </button>
                                        </form>
                                    </div>
                                </template>

                                {{-- Jika status APPROVED & belum lewat: Tombol Batalkan Paksa --}}
                                <template x-if="selectedDetail.status === 'approved' && !selectedDetail.is_finished">
                                    <button type="button" @click="openCancel(selectedDetail.id, selectedDetail.ticket_code, selectedDetail.facility_name, selectedDetail.applicant_name)" class="px-3.5 py-1.5 rounded-lg border border-rose-200 text-rose-700 hover:bg-rose-50 transition text-xs font-medium cursor-pointer">
                                        Batalkan Paksa (Darurat)
                                    </button>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- 7. Modal Tolak Permohonan --}}
        <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="showRejectModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-slate-900">
                        Tolak Permohonan Reservasi
                    </h3>
                    <button type="button" @click="showRejectModal = false" class="text-slate-400 hover:text-slate-700">
                        <span class="material-symbols-outlined text-[18px]">close</span>
                    </button>
                </div>
                <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                    Menolak permohonan tiket <strong class="text-slate-900 font-mono" x-text="ticketCode"></strong> untuk pemohon <strong class="text-slate-900" x-text="applicantName"></strong>. Masukkan alasan resmi:
                </p>

                <form :action="'{{ url('/petugas/reservations') }}/' + selectedId + '/reject'" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="mb-4">
                        <label for="rejection_reason" class="block text-xs font-medium text-slate-700 mb-1">
                            Alasan Penolakan:
                        </label>
                        <textarea id="rejection_reason" name="rejection_reason" rows="3" required placeholder="Contoh: Jadwal bertabrakan dengan kegiatan universitas atau ruang sedang dalam perawatan..." class="w-full p-3 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-900 focus:bg-white transition"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <button type="button" @click="showRejectModal = false" class="px-3.5 py-1.5 rounded-lg border border-slate-200 text-slate-700 text-xs font-medium hover:bg-slate-50 transition">
                            Batal
                        </button>
                        <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-rose-600 text-white text-xs font-medium hover:bg-rose-700 shadow-2xs transition">
                            Kirim Penolakan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- 8. Modal Pembatalan Darurat Sepihak oleh Petugas --}}
        <div x-show="showCancelModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="showCancelModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-xl border border-slate-200">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-bold text-slate-900">
                        Pembatalan Darurat (Override Petugas)
                    </h3>
                    <button type="button" @click="showCancelModal = false" class="text-slate-400 hover:text-slate-700">
                        <span class="material-symbols-outlined text-[18px]">close</span>
                    </button>
                </div>

                <div class="p-3 mb-4 rounded-lg bg-slate-50 border border-slate-200 text-slate-700 text-xs leading-relaxed">
                    <strong>Pemberitahuan:</strong> Anda akan membatalkan reservasi tiket <strong class="text-slate-900 font-mono" x-text="ticketCode"></strong> pada ruang <strong class="text-slate-900" x-text="venueName"></strong>. Slot waktu terkait akan segera dibuka kembali.
                </div>

                <form :action="'{{ url('/petugas/reservations') }}/' + selectedId + '/force-cancel'" method="POST">
                    @csrf
                    @method('DELETE')

                    <div class="mb-4">
                        <label for="alasan_batal" class="block text-xs font-medium text-slate-700 mb-1">
                            Alasan Pembatalan Darurat:
                        </label>
                        <textarea id="alasan_batal" name="alasan_batal" rows="3" required minlength="10" placeholder="Jelaskan alasan darurat (contoh: Gangguan instalasi listrik mendadak, ruangan harus diperbaiki segera)..." class="w-full p-3 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-900 focus:bg-white transition"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <button type="button" @click="showCancelModal = false" class="px-3.5 py-1.5 rounded-lg border border-slate-200 text-slate-700 text-xs font-medium hover:bg-slate-50 transition">
                            Kembali
                        </button>
                        <button type="submit" class="px-3.5 py-1.5 rounded-lg bg-rose-600 text-white text-xs font-medium hover:bg-rose-700 shadow-2xs transition">
                            Eksekusi Pembatalan
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-petugas-layout>
