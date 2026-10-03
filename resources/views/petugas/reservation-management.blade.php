{{-- 
  NAMA FILE      : reservation-management.blade.php
  FUNGSIONALITAS : Lembar Kerja Verifikasi, Approval & Pembatalan Darurat Petugas Sarpras (Bento UI & Server Pagination)
  DESKRIPSI      : Menampilkan antrean permohonan reservasi dengan default status pending (belum di-acc), paginasi server-side murni dari controller, desain Bento UI yang ringkas, peninjauan detail riwayat untuk waktu selesai & ditolak, serta modal penolakan dan pembatalan darurat.
  CARA KERJA     : Memanfaatkan <x-petugas-layout active="reservation-management">, mengelola modal Alpine.js untuk detail, penolakan, dan pembatalan, serta navigasi filter server-side dengan persistensi parameter.
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
            this.showRejectModal = true;
        },

        openCancel(id, code, venue, applicant) {
            this.selectedId = id;
            this.ticketCode = code;
            this.venueName = venue;
            this.applicantName = applicant || '';
            this.showCancelModal = true;
        },

        openDetail(data) {
            this.selectedDetail = data;
            this.showDetailModal = true;
        }
    }" class="flex flex-col gap-6">

        {{-- 1. Notifikasi Umpan Balik (Feedback Flash Message) --}}
        @if(session('success'))
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

        {{-- 2. Breadcrumb & Header Bento --}}
        <div>
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-2 font-medium">
                <a href="{{ url('/petugas/dashboard') }}" class="hover:text-slate-900 transition">Dasbor Operasional</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-slate-900">Manajemen Antrean Reservasi</span>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight flex items-center gap-2.5">
                        <span class="p-2 rounded-xl bg-slate-900 text-white shadow-xs">
                            <span class="material-symbols-outlined text-[22px]">inbox_customize</span>
                        </span>
                        <span>Antrean & Persetujuan Reservasi</span>
                    </h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1">
                        Secara standar menampilkan antrean yang belum diverifikasi. Tinjau berkas, setujui pemakaian, atau periksa riwayat lampau.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('petugas.schedule-matrix') }}" class="px-3.5 py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition shadow-xs flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[16px]">calendar_month</span>
                        <span>Buka Matriks Jadwal 30m</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- 3. Bento Grid Metric Cards (Ringkas & Terorganisir) --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Bento Card 1: Menunggu Persetujuan (Pending - Highlighted) --}}
            <a href="{{ route('petugas.reservation-management', ['status' => 'pending']) }}" class="p-4 rounded-2xl border transition shadow-xs relative overflow-hidden flex flex-col justify-between {{ $currentStatus === 'pending' ? 'bg-amber-50/70 border-amber-300 ring-2 ring-amber-400/40' : 'bg-white border-slate-200/80 hover:border-slate-300' }}">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-amber-800 uppercase tracking-wider">Menunggu (Belum di-acc)</span>
                    <span class="w-8 h-8 rounded-xl bg-amber-100 text-amber-800 flex items-center justify-center font-bold">
                        <span class="material-symbols-outlined text-[18px]">pending_actions</span>
                    </span>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-extrabold text-amber-900 tracking-tight">{{ $pendingCount }}</span>
                    <span class="text-xs text-amber-700 font-semibold">Pengajuan Baru</span>
                </div>
                <div class="mt-2 text-[11px] text-amber-800/80 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                    <span>Prioritas verifikasi petugas</span>
                </div>
            </a>

            {{-- Bento Card 2: Disetujui (Approved) --}}
            <a href="{{ route('petugas.reservation-management', ['status' => 'approved']) }}" class="p-4 rounded-2xl border transition shadow-xs relative overflow-hidden flex flex-col justify-between {{ $currentStatus === 'approved' ? 'bg-emerald-50/70 border-emerald-300 ring-2 ring-emerald-400/40' : 'bg-white border-slate-200/80 hover:border-slate-300' }}">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider">Disetujui</span>
                    <span class="w-8 h-8 rounded-xl bg-emerald-100 text-emerald-800 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[18px]">check_circle</span>
                    </span>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-extrabold text-emerald-800 tracking-tight">{{ $approvedCount }}</span>
                    <span class="text-xs text-emerald-700 font-semibold">Terkonfirmasi</span>
                </div>
                <div class="mt-2 text-[11px] text-emerald-700/80 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Jadwal aktif & riwayat</span>
                </div>
            </a>

            {{-- Bento Card 3: Ditolak (Rejected) --}}
            <a href="{{ route('petugas.reservation-management', ['status' => 'rejected']) }}" class="p-4 rounded-2xl border transition shadow-xs relative overflow-hidden flex flex-col justify-between {{ $currentStatus === 'rejected' ? 'bg-rose-50/70 border-rose-300 ring-2 ring-rose-400/40' : 'bg-white border-slate-200/80 hover:border-slate-300' }}">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-rose-800 uppercase tracking-wider">Ditolak</span>
                    <span class="w-8 h-8 rounded-xl bg-rose-100 text-rose-800 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[18px]">cancel</span>
                    </span>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-extrabold text-rose-800 tracking-tight">{{ $rejectedCount }}</span>
                    <span class="text-xs text-rose-700 font-semibold">Tiket</span>
                </div>
                <div class="mt-2 text-[11px] text-rose-700/80 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                    <span>Memiliki alasan penolakan</span>
                </div>
            </a>

            {{-- Bento Card 4: Dibatalkan (Cancelled) --}}
            <a href="{{ route('petugas.reservation-management', ['status' => 'cancelled']) }}" class="p-4 rounded-2xl border transition shadow-xs relative overflow-hidden flex flex-col justify-between {{ $currentStatus === 'cancelled' ? 'bg-slate-100 border-slate-300 ring-2 ring-slate-400/40' : 'bg-white border-slate-200/80 hover:border-slate-300' }}">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-[11px] font-bold text-slate-600 uppercase tracking-wider">Dibatalkan</span>
                    <span class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center">
                        <span class="material-symbols-outlined text-[18px]">event_busy</span>
                    </span>
                </div>
                <div class="flex items-baseline gap-2">
                    <span class="text-2xl sm:text-3xl font-extrabold text-slate-800 tracking-tight">{{ $cancelledCount }}</span>
                    <span class="text-xs text-slate-500 font-semibold">Tiket</span>
                </div>
                <div class="mt-2 text-[11px] text-slate-500 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                    <span>Mandiri / Darurat Petugas</span>
                </div>
            </a>
        </div>

        {{-- 4. Bento Filter Bar & Server Search --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs flex flex-col md:flex-row items-center justify-between gap-4">
            {{-- Tab Filter Navigasi Server-Side (Langsung link ke controller) --}}
            <div class="flex items-center gap-1.5 p-1 bg-slate-100 rounded-xl text-xs font-semibold w-full md:w-auto overflow-x-auto">
                <a href="{{ route('petugas.reservation-management', ['status' => 'pending', 'search' => $search]) }}" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap flex items-center gap-1.5 {{ $currentStatus === 'pending' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    <span>Menunggu (Belum di-acc)</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $currentStatus === 'pending' ? 'bg-amber-100 text-amber-800' : 'bg-slate-200 text-slate-700' }}">{{ $pendingCount }}</span>
                </a>
                <a href="{{ route('petugas.reservation-management', ['status' => 'approved', 'search' => $search]) }}" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap flex items-center gap-1.5 {{ $currentStatus === 'approved' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    <span>Disetujui</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $currentStatus === 'approved' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-700' }}">{{ $approvedCount }}</span>
                </a>
                <a href="{{ route('petugas.reservation-management', ['status' => 'rejected', 'search' => $search]) }}" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap flex items-center gap-1.5 {{ $currentStatus === 'rejected' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    <span>Ditolak</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $currentStatus === 'rejected' ? 'bg-rose-100 text-rose-800' : 'bg-slate-200 text-slate-700' }}">{{ $rejectedCount }}</span>
                </a>
                <a href="{{ route('petugas.reservation-management', ['status' => 'cancelled', 'search' => $search]) }}" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap flex items-center gap-1.5 {{ $currentStatus === 'cancelled' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    <span>Dibatalkan</span>
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-200 text-slate-700">{{ $cancelledCount }}</span>
                </a>
                <a href="{{ route('petugas.reservation-management', ['status' => 'semua', 'search' => $search]) }}" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap flex items-center gap-1.5 {{ $currentStatus === 'semua' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                    <span>Semua ({{ $totalCount }})</span>
                </a>
            </div>

            {{-- Formulir Pencarian Server-Side --}}
            <form method="GET" action="{{ route('petugas.reservation-management') }}" class="relative w-full md:w-72">
                <input type="hidden" name="status" value="{{ $currentStatus }}">
                <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-[18px]">search</span>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari tiket, nama, ruang..." class="w-full pl-9 pr-9 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-slate-900 focus:bg-white transition">
                @if($search)
                    <a href="{{ route('petugas.reservation-management', ['status' => $currentStatus]) }}" class="absolute right-3 top-2.5 text-slate-400 hover:text-slate-700">
                        <span class="material-symbols-outlined text-[16px]">close</span>
                    </a>
                @endif
            </form>
        </div>

        {{-- 5. Tabel Antrean Server-Side Bento Layout --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                            <th class="py-3.5 px-4">Tiket & Pemohon</th>
                            <th class="py-3.5 px-4">Fasilitas Diminta</th>
                            <th class="py-3.5 px-4">Tanggal & Slot Waktu</th>
                            <th class="py-3.5 px-4">Tujuan & Surat Izin</th>
                            <th class="py-3.5 px-4">Status</th>
                            <th class="py-3.5 px-4 text-right">Keputusan Operasional</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($reservations as $reservation)
                            @php
                                // Periksa apakah waktu reservasi sudah lewat / selesai
                                $endDateTime = \Carbon\Carbon::parse($reservation->reservation_date->format('Y-m-d') . ' ' . $reservation->end_time);
                                $isFinished = $reservation->status === 'completed' || $endDateTime->isPast();

                                // Data JSON aman untuk Detail Modal
                                $detailPayload = [
                                    'id' => $reservation->id,
                                    'ticket_code' => $reservation->ticket_code,
                                    'status' => $reservation->status,
                                    'is_finished' => $isFinished,
                                    'applicant_name' => $reservation->user->name ?? 'Pemohon Sivitas',
                                    'applicant_identity' => $reservation->user->identity_number ?? '-',
                                    'applicant_department' => $reservation->user->department ?? '-',
                                    'applicant_email' => $reservation->user->email ?? '-',
                                    'applicant_role' => $reservation->user->role ?? 'mahasiswa',
                                    'facility_name' => $reservation->facility->name ?? 'Fasilitas',
                                    'facility_building' => $reservation->facility->building ?? '-',
                                    'facility_floor' => $reservation->facility->floor_location ?? '-',
                                    'facility_capacity' => $reservation->facility->capacity ?? '-',
                                    'reservation_date' => $reservation->reservation_date->translatedFormat('d F Y'),
                                    'start_time' => substr($reservation->start_time, 0, 5),
                                    'end_time' => substr($reservation->end_time, 0, 5),
                                    'total_slots' => $reservation->total_slots,
                                    'purpose' => $reservation->purpose,
                                    'participants_count' => $reservation->participants_count,
                                    'permit_letter_path' => $reservation->permit_letter_path ? asset('storage/' . $reservation->permit_letter_path) : null,
                                    'rejection_reason' => $reservation->rejection_reason,
                                    'cancellation_reason' => $reservation->cancellation_reason,
                                    'reviewed_by' => $reservation->reviewer->name ?? null,
                                    'reviewed_at' => $reservation->reviewed_at ? $reservation->reviewed_at->translatedFormat('d M Y H:i') : null,
                                ];
                            @endphp

                            <tr class="hover:bg-slate-50/80 transition-colors">
                                {{-- Kolom Tiket & Pemohon --}}
                                <td class="py-3 px-4 align-top">
                                    <div class="font-mono text-xs font-bold text-slate-900 flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-[15px] text-blue-900">confirmation_number</span>
                                        <span>{{ $reservation->ticket_code ?? 'TKT-' . $reservation->id }}</span>
                                    </div>
                                    <div class="font-semibold text-slate-800 mt-1">
                                        {{ $reservation->user->name ?? 'Pemohon' }}
                                    </div>
                                    <div class="text-[11px] text-slate-500">
                                        {{ $reservation->user->identity_number ? $reservation->user->identity_number . ' • ' : '' }}{{ $reservation->user->email ?? '-' }}
                                    </div>
                                    @if(!empty($reservation->user->department))
                                        <span class="text-[10px] text-blue-900 font-semibold mt-0.5 block">
                                            {{ $reservation->user->department }}
                                        </span>
                                    @endif
                                </td>

                                {{-- Kolom Fasilitas --}}
                                <td class="py-3 px-4 align-top">
                                    <div class="font-bold text-slate-900">{{ $reservation->facility->name ?? 'Fasilitas Kampus' }}</div>
                                    <div class="text-[11px] text-slate-500">
                                        {{ $reservation->facility->building ?? 'Gedung Kampus' }}
                                        @if(!empty($reservation->facility->capacity))
                                            • ({{ $reservation->facility->capacity }} Kursi)
                                        @endif
                                    </div>
                                </td>

                                {{-- Kolom Tanggal & Slot --}}
                                <td class="py-3 px-4 align-top">
                                    <div class="font-bold text-slate-900">
                                        {{ !empty($reservation->reservation_date) ? $reservation->reservation_date->translatedFormat('d M Y') : '-' }}
                                    </div>
                                    <div class="text-[11px] text-slate-600 font-mono mt-0.5">
                                        {{ substr($reservation->start_time ?? '00:00', 0, 5) }} - {{ substr($reservation->end_time ?? '00:00', 0, 5) }} WIB 
                                        @if(!empty($reservation->total_slots))
                                            <span class="text-slate-400">({{ $reservation->total_slots }} slot)</span>
                                        @endif
                                    </div>
                                    @if($isFinished)
                                        <span class="inline-flex items-center gap-1 text-[10px] text-slate-500 font-semibold bg-slate-100 px-1.5 py-0.2 rounded mt-1">
                                            <span class="material-symbols-outlined text-[11px]">history</span>
                                            <span>Waktu Selesai</span>
                                        </span>
                                    @endif
                                </td>

                                {{-- Kolom Tujuan & Surat Izin --}}
                                <td class="py-3 px-4 align-top max-w-xs">
                                    <p class="text-slate-800 line-clamp-2 leading-relaxed">{{ $reservation->purpose ?? '-' }}</p>
                                    <div class="mt-1 flex items-center gap-2 text-[11px]">
                                        @if(!empty($reservation->participants_count))
                                            <span class="text-slate-500">Peserta: {{ $reservation->participants_count }} org</span>
                                        @endif
                                        @if(!empty($reservation->permit_letter_path))
                                            <a href="{{ asset('storage/' . $reservation->permit_letter_path) }}" target="_blank" class="inline-flex items-center gap-0.5 text-blue-900 font-semibold hover:underline">
                                                <span class="material-symbols-outlined text-[13px]">attachment</span>
                                                <span>Surat Izin</span>
                                            </a>
                                        @endif
                                    </div>
                                </td>

                                {{-- Kolom Status (TULISAN 'SLOT TERKUNCI' TELAH DIHAPUS SESUAI PERINTAH #2) --}}
                                <td class="py-3 px-4 align-top">
                                    <div class="flex flex-col gap-1 items-start">
                                        <x-cava.status-badge :status="$reservation->status ?? 'pending'" />

                                        @if($reservation->status === 'pending')
                                            <span class="inline-flex items-center gap-1 text-[10px] text-amber-800 font-semibold bg-amber-50 px-2 py-0.5 rounded-md border border-amber-200/60">
                                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                                <span>Menunggu Verifikasi</span>
                                            </span>
                                        @elseif($reservation->status === 'cancelled' && !empty($reservation->cancellation_reason))
                                            <div class="mt-1 text-[11px] text-slate-700 bg-slate-100 p-2 rounded-lg border border-slate-200/80 max-w-xs">
                                                <strong class="text-rose-700">Alasan Batal:</strong> {{ Str::limit($reservation->cancellation_reason, 50) }}
                                            </div>
                                        @elseif($reservation->status === 'rejected' && !empty($reservation->rejection_reason))
                                            <div class="mt-1 text-[11px] text-rose-700 bg-rose-50 p-2 rounded-lg border border-rose-200/60 max-w-xs">
                                                <strong>Alasan Tolak:</strong> {{ Str::limit($reservation->rejection_reason, 50) }}
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                {{-- Kolom Keputusan Operasional --}}
                                <td class="py-3 px-4 align-top text-right whitespace-nowrap">
                                    @if($reservation->status === 'pending')
                                        {{-- AKSI STATUS PENDING: SETUJUI & TOLAK --}}
                                        <div class="flex items-center justify-end gap-1.5">
                                            <form action="{{ url('/petugas/reservations/' . $reservation->id . '/approve') }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" onclick="return confirm('Setujui reservasi {{ $reservation->ticket_code }}? Slot waktu fasilitas akan resmi dialokasikan.')" class="px-2.5 py-1.5 rounded-xl bg-slate-900 text-white font-semibold hover:bg-slate-800 transition shadow-xs flex items-center gap-1 text-xs">
                                                    <span class="material-symbols-outlined text-[15px]">check_circle</span>
                                                    <span>Setujui</span>
                                                </button>
                                            </form>

                                            <button type="button" @click="openReject('{{ $reservation->id }}', '{{ addslashes($reservation->user->name ?? 'Pemohon') }}', '{{ $reservation->ticket_code }}')" class="px-2 py-1.5 rounded-xl border border-slate-200 text-rose-700 hover:bg-rose-50 transition font-semibold text-xs">
                                                Tolak
                                            </button>

                                            <button type="button" @click='openDetail(@json($detailPayload))' class="p-1.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 transition" title="Lihat Detail Pengajuan">
                                                <span class="material-symbols-outlined text-[16px]">visibility</span>
                                            </button>
                                        </div>

                                    @elseif($reservation->status === 'approved')
                                        {{-- AKSI STATUS APPROVED: JIKA WAKTU SUDAH KELAR -> BUTTON LIHAT DETAIL RIWAYAT --}}
                                        <div class="flex items-center justify-end gap-1.5">
                                            @if($isFinished)
                                                <button type="button" @click='openDetail(@json($detailPayload))' class="px-3 py-1.5 rounded-xl border border-slate-300 text-slate-700 bg-white hover:bg-slate-50 transition font-semibold text-xs inline-flex items-center gap-1 shadow-2xs">
                                                    <span class="material-symbols-outlined text-[15px] text-slate-500">history</span>
                                                    <span>Lihat Detail Riwayat</span>
                                                </button>
                                            @else
                                                <button type="button" @click='openDetail(@json($detailPayload))' class="p-1.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 transition" title="Lihat Detail Reservasi">
                                                    <span class="material-symbols-outlined text-[16px]">visibility</span>
                                                </button>

                                                <button type="button" @click="openCancel('{{ $reservation->id }}', '{{ $reservation->ticket_code }}', '{{ addslashes($reservation->facility->name ?? 'Fasilitas') }}', '{{ addslashes($reservation->user->name ?? 'Pemohon') }}')" class="px-2.5 py-1.5 rounded-xl border border-rose-300 text-rose-700 bg-rose-50 hover:bg-rose-100 transition font-semibold text-xs inline-flex items-center gap-1 shadow-2xs" title="Pembatalan Darurat Petugas (Override)">
                                                    <span class="material-symbols-outlined text-[15px]">event_busy</span>
                                                    <span>Batalkan Paksa</span>
                                                </button>
                                            @endif
                                        </div>

                                    @elseif($reservation->status === 'rejected')
                                        {{-- AKSI STATUS REJECTED: BUTTON LIHAT DETAIL RIWAYAT (SESUAI PERINTAH #2) --}}
                                        <div class="flex items-center justify-end">
                                            <button type="button" @click='openDetail(@json($detailPayload))' class="px-3 py-1.5 rounded-xl border border-rose-200 text-rose-700 bg-rose-50/60 hover:bg-rose-100 transition font-semibold text-xs inline-flex items-center gap-1 shadow-2xs">
                                                <span class="material-symbols-outlined text-[15px]">history</span>
                                                <span>Lihat Detail Riwayat</span>
                                            </button>
                                        </div>

                                    @elseif($reservation->status === 'cancelled')
                                        {{-- AKSI STATUS CANCELLED: BUTTON LIHAT DETAIL RIWAYAT --}}
                                        <div class="flex items-center justify-end">
                                            <button type="button" @click='openDetail(@json($detailPayload))' class="px-3 py-1.5 rounded-xl border border-slate-200 text-slate-700 bg-slate-50 hover:bg-slate-100 transition font-semibold text-xs inline-flex items-center gap-1 shadow-2xs">
                                                <span class="material-symbols-outlined text-[15px] text-slate-500">history</span>
                                                <span>Lihat Detail Riwayat</span>
                                            </button>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 px-4 text-center">
                                    <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                        <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-500 flex items-center justify-center mb-3">
                                            <span class="material-symbols-outlined text-[28px]">inbox</span>
                                        </div>
                                        <h4 class="text-sm font-bold text-slate-900">Tidak Ada Antrean Reservasi</h4>
                                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                            @if($currentStatus === 'pending')
                                                Tidak ada permohonan reservasi baru yang menunggu persetujuan.
                                            @else
                                                Tidak ditemukan data reservasi untuk status atau filter yang dipilih.
                                            @endif
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- 6. Paginasi Server-Side Laravel (BUKAN Gimmick Frontend) --}}
            @if ($reservations->total() > 0)
                <div class="p-4 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
                    <div class="text-slate-500">
                        Menampilkan <strong class="text-slate-900">{{ $reservations->firstItem() ?? 0 }}</strong> sampai <strong class="text-slate-900">{{ $reservations->lastItem() ?? 0 }}</strong> dari total <strong class="text-slate-900">{{ $reservations->total() }}</strong> reservasi
                    </div>
                    <div>
                        {{ $reservations->links() }}
                    </div>
                </div>
            @endif
        </div>

        {{-- 7. Modal Detail Reservasi Lengkap (Bento Modal) --}}
        <div x-show="showDetailModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="showDetailModal = false" class="bg-white rounded-3xl max-w-2xl w-full p-6 shadow-2xl border border-slate-200 relative animate-in fade-in zoom-in-95 duration-200">
                <button type="button" @click="showDetailModal = false" class="absolute top-5 right-5 p-1 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition">
                    <span class="material-symbols-outlined">close</span>
                </button>

                <template x-if="selectedDetail">
                    <div class="flex flex-col gap-5">
                        {{-- Header Modal Bento --}}
                        <div class="flex items-center gap-3 pr-8">
                            <span class="p-2.5 rounded-2xl bg-blue-50 text-blue-900">
                                <span class="material-symbols-outlined text-[24px]">description</span>
                            </span>
                            <div>
                                <div class="flex items-center gap-2">
                                    <span class="font-mono text-base font-extrabold text-slate-900" x-text="selectedDetail.ticket_code"></span>
                                    <span :class="{
                                        'bg-amber-100 text-amber-800': selectedDetail.status === 'pending',
                                        'bg-emerald-100 text-emerald-800': selectedDetail.status === 'approved',
                                        'bg-rose-100 text-rose-800': selectedDetail.status === 'rejected',
                                        'bg-slate-100 text-slate-800': selectedDetail.status === 'cancelled'
                                    }" class="text-[10px] px-2.5 py-0.5 rounded-full font-bold uppercase" x-text="selectedDetail.status"></span>
                                </div>
                                <p class="text-xs text-slate-500 mt-0.5">Detail riwayat dan spesifikasi permohonan reservasi</p>
                            </div>
                        </div>

                        {{-- Bento Grid Cards Inside Modal --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3.5">
                            {{-- Bento Card 1: Data Pemohon --}}
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[16px] text-blue-900">person</span>
                                    <span>Identitas Pemohon</span>
                                </div>
                                <div class="font-bold text-slate-900 text-sm" x-text="selectedDetail.applicant_name"></div>
                                <div class="text-xs text-slate-600 mt-1.5 space-y-1">
                                    <p><strong class="text-slate-700">NIM / NIP:</strong> <span class="font-mono" x-text="selectedDetail.applicant_identity"></span></p>
                                    <p><strong class="text-slate-700">Unit / Prodi:</strong> <span x-text="selectedDetail.applicant_department"></span></p>
                                    <p><strong class="text-slate-700">Email:</strong> <span x-text="selectedDetail.applicant_email"></span></p>
                                </div>
                            </div>

                            {{-- Bento Card 2: Fasilitas & Waktu --}}
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80">
                                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[16px] text-emerald-800">domain</span>
                                    <span>Ruang & Waktu</span>
                                </div>
                                <div class="font-bold text-slate-900 text-sm" x-text="selectedDetail.facility_name"></div>
                                <div class="text-xs text-slate-600 mt-1.5 space-y-1">
                                    <p><strong class="text-slate-700">Lokasi:</strong> <span x-text="selectedDetail.facility_building"></span></p>
                                    <p><strong class="text-slate-700">Tanggal:</strong> <span x-text="selectedDetail.reservation_date"></span></p>
                                    <p><strong class="text-slate-700">Waktu:</strong> <span class="font-mono font-bold text-slate-900" x-text="selectedDetail.start_time + ' - ' + selectedDetail.end_time + ' WIB'"></span> (<span x-text="selectedDetail.total_slots + ' slot'"></span>)</p>
                                </div>
                            </div>

                            {{-- Bento Card 3: Keperluan & Surat Izin (Full Width) --}}
                            <div class="p-4 rounded-2xl bg-slate-50 border border-slate-200/80 md:col-span-2">
                                <div class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-2 flex items-center gap-1.5">
                                    <span class="material-symbols-outlined text-[16px] text-amber-800">description</span>
                                    <span>Tujuan Kegiatan & Surat Izin</span>
                                </div>
                                <p class="text-xs text-slate-800 leading-relaxed font-medium bg-white p-3 rounded-xl border border-slate-200" x-text="selectedDetail.purpose || '-'"></p>
                                
                                <div class="mt-3 flex flex-wrap items-center justify-between gap-3 text-xs">
                                    <div class="flex items-center gap-2">
                                        <span class="text-slate-500">Jumlah Peserta:</span>
                                        <strong class="text-slate-900" x-text="(selectedDetail.participants_count || 1) + ' Orang'"></strong>
                                    </div>
                                    <div>
                                        <template x-if="selectedDetail.permit_letter_path">
                                            <a :href="selectedDetail.permit_letter_path" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-50 text-blue-900 border border-blue-200 font-semibold hover:bg-blue-100 transition shadow-2xs">
                                                <span class="material-symbols-outlined text-[15px]">attachment</span>
                                                <span>Lihat Berkas Surat Izin</span>
                                            </a>
                                        </template>
                                        <template x-if="!selectedDetail.permit_letter_path">
                                            <span class="text-slate-400 italic">Tidak melampirkan berkas fisik</span>
                                        </template>
                                    </div>
                                </div>
                            </div>

                            {{-- Bento Card 4: Catatan Penolakan / Pembatalan Jika Ada --}}
                            <template x-if="selectedDetail.rejection_reason">
                                <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 md:col-span-2">
                                    <div class="text-[11px] font-bold text-rose-800 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-[16px] text-rose-700">cancel</span>
                                        <span>Alasan Resmi Penolakan</span>
                                    </div>
                                    <p class="text-xs text-rose-900 font-medium" x-text="selectedDetail.rejection_reason"></p>
                                </div>
                            </template>

                            <template x-if="selectedDetail.cancellation_reason">
                                <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 md:col-span-2">
                                    <div class="text-[11px] font-bold text-amber-800 uppercase tracking-wider mb-1 flex items-center gap-1.5">
                                        <span class="material-symbols-outlined text-[16px] text-amber-700">warning</span>
                                        <span>Alasan Pembatalan</span>
                                    </div>
                                    <p class="text-xs text-amber-900 font-medium" x-text="selectedDetail.cancellation_reason"></p>
                                </div>
                            </template>

                            {{-- Bento Card 5: Log Verifikasi Petugas --}}
                            <template x-if="selectedDetail.reviewed_by || selectedDetail.reviewed_at">
                                <div class="p-3.5 rounded-2xl bg-slate-100 border border-slate-200 md:col-span-2 text-xs flex items-center justify-between">
                                    <div class="flex items-center gap-2 text-slate-700">
                                        <span class="material-symbols-outlined text-[18px] text-slate-900">verified_user</span>
                                        <span>Ditinjau oleh: <strong class="text-slate-900" x-text="selectedDetail.reviewed_by"></strong></span>
                                    </div>
                                    <div class="text-[11px] text-slate-500" x-text="'Waktu: ' + selectedDetail.reviewed_at"></div>
                                </div>
                            </template>
                        </div>

                        {{-- Footer Aksi Modal --}}
                        <div class="flex items-center justify-end gap-2 pt-2 border-t border-slate-100">
                            <button type="button" @click="showDetailModal = false" class="px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition shadow-xs">
                                Tutup
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- 8. Modal Tolak Permohonan --}}
        <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="showRejectModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 animate-in fade-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-base font-bold text-rose-700 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[20px]">cancel</span>
                        <span>Tolak Permohonan Reservasi</span>
                    </h3>
                    <button type="button" @click="showRejectModal = false" class="text-slate-400 hover:text-slate-700">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                    Menolak tiket <strong class="text-slate-900 font-mono" x-text="ticketCode"></strong> untuk pemohon <strong class="text-slate-900" x-text="applicantName"></strong>. Masukkan alasan penolakan resmi:
                </p>

                <form :action="'{{ url('/petugas/reservations') }}/' + selectedId + '/reject'" method="POST">
                    @csrf
                    @method('PATCH')

                    <div class="mb-4">
                        <label for="rejection_reason" class="block text-xs font-bold text-slate-700 mb-1">
                            Alasan Penolakan (Wajib Diisi):
                        </label>
                        <textarea id="rejection_reason" name="rejection_reason" rows="3" required placeholder="Contoh: Jadwal bertabrakan dengan agenda resmi universitas atau ruangan sedang dalam perbaikan sarpras..." class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-slate-900 focus:bg-white transition"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <button type="button" @click="showRejectModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50 transition">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 text-white text-xs font-semibold hover:bg-rose-700 shadow-xs transition">
                            Kirim Penolakan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- 9. Modal Pembatalan Darurat Sepihak oleh Petugas (PTG-03 / US-10) --}}
        <div x-show="showCancelModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="showCancelModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 animate-in fade-in zoom-in-95 duration-200">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-base font-bold text-rose-700 flex items-center gap-1.5">
                        <span class="material-symbols-outlined text-[20px]">warning</span>
                        <span>Pembatalan Darurat (Override Petugas)</span>
                    </h3>
                    <button type="button" @click="showCancelModal = false" class="text-slate-400 hover:text-slate-700">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="p-3 mb-4 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs leading-relaxed flex items-start gap-2">
                    <span class="material-symbols-outlined text-[18px] text-amber-700 shrink-0 mt-0.5">info</span>
                    <div>
                        <strong>Peringatan Tindakan Sepihak:</strong> Anda akan membatalkan reservasi tiket <strong class="text-slate-900 font-mono" x-text="ticketCode"></strong> untuk pemohon <strong class="text-slate-900" x-text="applicantName"></strong> pada ruang <strong class="text-slate-900" x-text="venueName"></strong>. Slot waktu terkait akan segera dibebaskan di matriks ketersediaan.
                    </div>
                </div>

                <form :action="'{{ url('/petugas/reservations') }}/' + selectedId + '/force-cancel'" method="POST">
                    @csrf
                    @method('DELETE')

                    <div class="mb-4">
                        <label for="alasan_batal" class="block text-xs font-bold text-slate-700 mb-1">
                            Alasan Pembatalan Darurat (Wajib, Min. 10 Karakter):
                        </label>
                        <textarea id="alasan_batal" name="alasan_batal" rows="3" required minlength="10" placeholder="Jelaskan alasan darurat pembatalan sepihak (contoh: Terjadi kebocoran pipa pendingin ruangan mendadak, ruangan harus segera diperbaiki teknisi sarpras)..." class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-slate-900 focus:bg-white transition"></textarea>
                        <p class="text-[11px] text-slate-400 mt-1">Alasan pembatalan resmi ini akan tersimpan pada sistem.</p>
                    </div>

                    <div class="flex items-center justify-end gap-2">
                        <button type="button" @click="showCancelModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50 transition">
                            Kembali
                        </button>
                        <button type="submit" class="px-4 py-2 rounded-xl bg-rose-600 text-white text-xs font-semibold hover:bg-rose-700 shadow-xs transition flex items-center gap-1">
                            <span class="material-symbols-outlined text-[15px]">event_busy</span>
                            <span>Eksekusi Batal Darurat</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-petugas-layout>
