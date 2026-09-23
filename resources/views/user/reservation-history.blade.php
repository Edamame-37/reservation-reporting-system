{{-- 
  NAMA FILE      : reservation-history.blade.php
  FUNGSIONALITAS : Halaman Lengkap Riwayat & Detail Reservasi Pengguna
  DESKRIPSI      : Menampilkan daftar seluruh tiket permohonan reservasi pengguna dari basis data dengan tab filter status, pencarian dinamis, modal detail lengkap, dan paginasi.
  CARA KERJA     : Menerima koleksi $reservations dari ReservationController@history, menyediakan filter URL query parameters, serta modal pop-up interaktif via Alpine.js.
--}}

<x-app-layout title="Riwayat Lengkap Reservasi Saya" active="reservation-history">
    <div x-data="{
        showDetailModal: false,
        showCancelModal: false,
        selectedTicket: null,
        openDetail(t) {
            this.selectedTicket = t;
            this.showDetailModal = true;
        },
        openCancel(t) {
            this.selectedTicket = t;
            this.showCancelModal = true;
        }
    }">

        {{-- Breadcrumb & Header --}}
        <div class="mb-6">
            <div class="flex items-center gap-2 text-xs text-slate-500 mb-2 font-medium">
                <a href="{{ url('/user/dashboard') }}" class="hover:text-slate-900 transition">Dasbor Saya</a>
                <span class="material-symbols-outlined text-[14px]">chevron_right</span>
                <span class="text-slate-900">Riwayat Lengkap Reservasi</span>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Riwayat Lengkap Permohonan Reservasi</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">Daftar seluruh reservasi aktif dan arsip peminjaman ruang Anda di universitas.</p>
                </div>
                <a href="{{ url('/user/reservation-form') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-900 text-white text-xs sm:text-sm font-semibold hover:bg-slate-800 transition shadow-xs">
                    <span class="material-symbols-outlined text-[18px]">add_circle</span>
                    <span>Ajukan Reservasi Baru</span>
                </a>
            </div>
        </div>

        {{-- Flash Session Sukses --}}
        @if (session('success'))
            <div class="mb-6 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-900 flex items-center gap-3 shadow-xs">
                <span class="material-symbols-outlined text-emerald-600 text-[24px]">check_circle</span>
                <div>
                    <h4 class="text-sm font-bold">Permohonan Berhasil Dikirim!</h4>
                    <p class="text-xs text-emerald-700">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        {{-- Filter Tabs & Pencarian --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
            {{-- Tabs Status --}}
            @php
                $statusTabs = [
                    'all'       => 'Semua',
                    'pending'   => 'Menunggu',
                    'approved'  => 'Disetujui',
                    'rejected'  => 'Ditolak',
                    'cancelled' => 'Dibatalkan',
                ];
                $currStatus = request('status', $activeStatus ?? 'all');
            @endphp
            <div class="flex items-center gap-1 overflow-x-auto w-full md:w-auto p-1 bg-slate-100 rounded-xl text-xs font-medium">
                @foreach ($statusTabs as $statusKey => $statusLabel)
                    <a href="{{ route('user.reservation-history', array_merge(request()->query(), ['status' => $statusKey, 'page' => 1])) }}"
                       class="px-3 py-1.5 rounded-lg transition whitespace-nowrap {{ $currStatus === $statusKey ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                        {{ $statusLabel }} ({{ $counts[$statusKey] ?? 0 }})
                    </a>
                @endforeach
            </div>

            {{-- Input Pencarian --}}
            <form action="{{ route('user.reservation-history') }}" method="GET" class="relative w-full md:w-72">
                <input type="hidden" name="status" value="{{ $currStatus }}">
                <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-[18px]">search</span>
                <input type="text" name="search" value="{{ request('search', $keyword ?? '') }}" placeholder="Cari kode tiket / ruang..." class="w-full pl-9 pr-8 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-slate-900 focus:bg-white transition">
                @if (request('search'))
                    <a href="{{ route('user.reservation-history', ['status' => $currStatus]) }}" class="absolute right-2.5 top-2 text-slate-400 hover:text-slate-600">
                        <span class="material-symbols-outlined text-[16px]">close</span>
                    </a>
                @endif
            </form>
        </div>

        {{-- Tabel Riwayat Lengkap --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden mb-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                            <th class="py-3 px-4">Kode Tiket</th>
                            <th class="py-3 px-4">Fasilitas & Lokasi</th>
                            <th class="py-3 px-4">Jadwal & Waktu</th>
                            <th class="py-3 px-4">Tujuan Penggunaan</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($reservations as $res)
                            @php
                                $isHMinus1 = false;
                                try {
                                    $resDate = \Carbon\Carbon::parse($res->reservation_date);
                                    $isHMinus1 = $resDate->isFuture() && now()->diffInDays($resDate, false) >= 1 && in_array($res->status, ['pending', 'approved']);
                                } catch (\Exception $e) {
                                    $isHMinus1 = false;
                                }

                                $ticketData = [
                                    'id'          => $res->ticket_code,
                                    'venue'       => $res->facility->name ?? 'Fasilitas Kampus',
                                    'building'    => ($res->facility->building ?? 'Gedung Kampus') . ($res->facility->floor_location ? ' - ' . $res->facility->floor_location : ''),
                                    'date'        => \Carbon\Carbon::parse($res->reservation_date)->translatedFormat('d M Y'),
                                    'time'        => substr($res->start_time, 0, 5) . ' - ' . substr($res->end_time, 0, 5) . ' WIB',
                                    'slots'       => $res->total_slots ?? 1,
                                    'purpose'     => $res->purpose,
                                    'participants'=> $res->participants_count ?? 1,
                                    'status'      => $res->status,
                                    'reviewer'    => $res->reviewer->name ?? 'Belum Diverifikasi',
                                    'reviewed_at' => $res->reviewed_at ? \Carbon\Carbon::parse($res->reviewed_at)->translatedFormat('d M Y H:i') : null,
                                    'officerNote' => $res->rejection_reason ?? $res->cancellation_reason ?? ($res->status === 'approved' ? 'Telah disetujui oleh Petugas Sarpras. Harap menjaga kebersihan fasilitas.' : 'Sedang dalam antrean evaluasi dan verifikasi staf sarpras.'),
                                    'canCancel'   => $isHMinus1,
                                ];
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition">
                                <td class="py-3.5 px-4 font-mono font-bold text-slate-800">
                                    {{ $res->ticket_code }}
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-bold text-slate-900">{{ $res->facility->name ?? 'Fasilitas Kampus' }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $res->facility->building ?? '-' }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="text-slate-800 font-medium">
                                        {{ \Carbon\Carbon::parse($res->reservation_date)->translatedFormat('d M Y') }}
                                    </div>
                                    <div class="text-[11px] text-slate-500 font-mono">
                                        {{ substr($res->start_time, 0, 5) }} - {{ substr($res->end_time, 0, 5) }} WIB ({{ $res->total_slots ?? 1 }} Slot)
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 max-w-xs truncate text-slate-600" title="{{ $res->purpose }}">
                                    {{ $res->purpose }}
                                </td>
                                <td class="py-3.5 px-4">
                                    @if ($res->status === 'pending')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs bg-amber-50 text-amber-800 border border-amber-200/60 font-medium">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            Menunggu Persetujuan
                                        </span>
                                    @elseif ($res->status === 'approved')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs bg-emerald-50 text-emerald-800 border border-emerald-200/60 font-semibold">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                            Disetujui Petugas
                                        </span>
                                    @elseif ($res->status === 'rejected')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs bg-rose-50 text-rose-700 border border-rose-200/60 font-medium">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            Ditolak
                                        </span>
                                    @elseif ($res->status === 'cancelled')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs bg-slate-100 text-slate-600 border border-slate-200 font-medium">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                            Dibatalkan
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-xs bg-blue-50 text-blue-700 border border-blue-200 font-medium">
                                            <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                            {{ ucfirst($res->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <button type="button" @click="openDetail({{ json_encode($ticketData) }})" class="px-2.5 py-1 rounded-lg border border-slate-200 text-slate-700 font-semibold hover:bg-slate-50 transition">
                                            Detail
                                        </button>
                                        @if ($isHMinus1)
                                            <button type="button" @click="openCancel({{ json_encode($ticketData) }})" class="px-2.5 py-1 rounded-lg bg-rose-50 text-rose-700 border border-rose-200 font-semibold hover:bg-rose-100 transition" title="Batalkan Reservasi (Maksimal H-1)">
                                                Batal
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-12 px-4 text-center">
                                    <div class="max-w-sm mx-auto flex flex-col items-center">
                                        <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center mb-3">
                                            <span class="material-symbols-outlined text-[28px]">calendar_month</span>
                                        </div>
                                        <h4 class="text-sm font-bold text-slate-900 mb-1">Belum Ada Riwayat Reservasi</h4>
                                        <p class="text-xs text-slate-500 mb-4">
                                            @if (request('search') || request('status', 'all') !== 'all')
                                                Tidak ditemukan reservasi yang sesuai dengan kriteria filter atau pencarian Anda.
                                            @else
                                                Anda belum pernah mengajukan permohonan reservasi fasilitas kampus.
                                            @endif
                                        </p>
                                        <a href="{{ url('/user/reservation-form') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-slate-900 text-white text-xs font-semibold hover:bg-slate-800 transition">
                                            <span class="material-symbols-outlined text-[16px]">add</span>
                                            <span>Ajukan Reservasi Sekarang</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Navigasi Paginasi Laravel --}}
            @if ($reservations->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $reservations->links() }}
                </div>
            @endif
        </div>

        {{-- Modal Detail Reservasi --}}
        <div x-show="showDetailModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="showDetailModal = false" class="bg-white rounded-3xl max-w-lg w-full p-6 shadow-2xl border border-slate-200 relative">
                <button type="button" @click="showDetailModal = false" class="absolute top-4 right-4 p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100">
                    <span class="material-symbols-outlined">close</span>
                </button>

                <template x-if="selectedTicket">
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="px-2.5 py-0.5 rounded-md bg-slate-100 font-mono text-xs font-bold text-slate-800" x-text="selectedTicket.id"></span>
                            <span class="text-xs font-semibold text-slate-500">Detail Permohonan</span>
                        </div>
                        <h2 class="text-lg font-bold text-slate-900" x-text="selectedTicket.venue"></h2>
                        <p class="text-xs text-slate-500 mb-4" x-text="selectedTicket.building"></p>

                        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-100 mb-4 space-y-2 text-xs">
                            <div class="flex justify-between">
                                <span class="text-slate-500">Tanggal Kegiatan:</span>
                                <span class="font-bold text-slate-900" x-text="selectedTicket.date"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Rentang Waktu:</span>
                                <span class="font-mono font-bold text-slate-900" x-text="selectedTicket.time + ' (' + selectedTicket.slots + ' Slot)'"></span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-slate-500">Estimasi Peserta:</span>
                                <span class="font-bold text-slate-900" x-text="selectedTicket.participants + ' Orang'"></span>
                            </div>
                            <div class="pt-2 border-t border-slate-200/60">
                                <span class="text-slate-500 block mb-0.5">Tujuan Penggunaan:</span>
                                <span class="text-slate-800" x-text="selectedTicket.purpose"></span>
                            </div>
                        </div>

                        <div class="mb-6">
                            <span class="text-xs font-bold text-slate-700 block mb-1">Catatan / Keterangan Petugas:</span>
                            <div class="p-3 rounded-xl border text-xs" :class="selectedTicket.status === 'rejected' ? 'bg-rose-50 text-rose-800 border-rose-200' : 'bg-blue-50/70 text-blue-900 border-blue-100'">
                                <p x-text="selectedTicket.officerNote"></p>
                                <template x-if="selectedTicket.reviewed_at">
                                    <span class="block mt-1 text-[10px] text-slate-400" x-text="'Diverifikasi oleh: ' + selectedTicket.reviewer + ' pada ' + selectedTicket.reviewed_at"></span>
                                </template>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                            <button type="button" @click="showDetailModal = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50">
                                Tutup
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Modal Konfirmasi Pembatalan Mandiri H-1 --}}
        <div x-show="showCancelModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="showCancelModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200 text-center">
                <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center mx-auto mb-3">
                    <span class="material-symbols-outlined text-[28px]">warning</span>
                </div>
                <h3 class="text-base font-bold text-slate-900 mb-1">Konfirmasi Pembatalan Reservasi</h3>
                <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                    Sesuai ketentuan, pembatalan mandiri hanya diizinkan maksimal <strong>H-1 sebelum jadwal</strong>. Slot waktu yang dilepas akan langsung terbuka kembali di kalender ketersediaan umum.
                </p>

                <div class="flex items-center justify-center gap-2">
                    <button type="button" @click="showCancelModal = false" class="flex-1 px-4 py-2 rounded-xl border border-slate-200 text-slate-700 text-xs font-semibold hover:bg-slate-50">
                        Kembali
                    </button>
                    <!-- Tautan pembatalan mandiri untuk modul USR-03 -->
                    <button type="button" @click="alert('Fitur pembatalan mandiri terintegrasi pada modul USR-03.'); showCancelModal = false;" class="flex-1 px-4 py-2 rounded-xl bg-rose-600 text-white text-xs font-semibold hover:bg-rose-700 shadow-xs">
                        Lanjutkan Pembatalan
                    </button>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
