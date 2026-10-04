{{-- 
  NAMA FILE      : dashboard.blade.php
  FUNGSIONALITAS : Dasbor Operasional Petugas Sarpras
  DESKRIPSI      : Menampilkan metrik beban kerja operasional, antrean verifikasi reservasi prioritas,
                   serta pemantauan laporan kerusakan terkini secara terstruktur dan efisien.
  CARA KERJA     : Terintegrasi dengan layout <x-petugas-layout>, Alpine.js mengelola modal pratinjau cepat.
--}}

@php
    $pendingReservationsCount = $pendingReservationsCount ?? 0;
    $newReportsCount = $newReportsCount ?? 0;
    $lockedFacilitiesCount = $lockedFacilitiesCount ?? 0;
    $recentReservations = $recentReservations ?? collect([]);
    $recentReports = $recentReports ?? collect([]);
@endphp

<x-petugas-layout title="Dasbor Operasional Sarpras" active="dashboard">
    <div x-data="{
        showModal: false,
        showRejectModal: false,
        selected: null,
        selectedId: null,
        applicantName: '',
        ticketCode: '',
        openDetail(item) {
            this.selected = item;
            this.showModal = true;
        },
        openReject(id, name, ticket) {
            this.selectedId = id;
            this.applicantName = name;
            this.ticketCode = ticket;
            this.showModal = false;
            this.showRejectModal = true;
        }
    }" class="flex flex-col gap-6">

        {{-- 1. Header Halaman Profesional --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 pb-2 border-b border-slate-200/80">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-tight">Dasbor Operasional Sarpras</h1>
                <p class="text-xs text-slate-500 mt-0.5">Pemantauan antrean permohonan reservasi fasilitas kampus dan tiket laporan kerusakan.</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="location.reload()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-xs font-medium text-slate-700 hover:bg-slate-50 transition shadow-2xs cursor-pointer">
                    <span class="material-symbols-outlined text-[16px] text-slate-500">sync</span>
                    <span>Sinkron Data</span>
                </button>
            </div>
        </div>

        {{-- 2. Ringkasan Metrik Operasional (3 Bento KPI Cards) --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            {{-- KPI 1: Antrean Reservasi --}}
            <a href="{{ url('/petugas/reservation-management') }}" class="bg-white rounded-xl border border-slate-200/80 p-4 sm:p-5 shadow-2xs hover:border-slate-300 hover:bg-slate-50/40 transition-colors flex flex-col justify-between group">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-500">Antrean Reservasi</span>
                        <span class="text-[11px] px-2 py-0.5 rounded-full font-bold bg-slate-100 text-slate-700">{{ $pendingReservationsCount }}</span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-slate-900 mt-2">{{ $pendingReservationsCount }}</div>
                </div>
                <div class="text-[11px] text-slate-500 mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between">
                    <span>Permohonan menunggu verifikasi</span>
                    <span class="font-medium text-slate-700 group-hover:translate-x-0.5 transition-transform">Kelola antrean &rarr;</span>
                </div>
            </a>

            {{-- KPI 2: Laporan Kerusakan --}}
            <a href="{{ url('/petugas/report-management') }}" class="bg-white rounded-xl border border-slate-200/80 p-4 sm:p-5 shadow-2xs hover:border-slate-300 hover:bg-slate-50/40 transition-colors flex flex-col justify-between group">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-500">Laporan Kerusakan</span>
                        <span class="text-[11px] px-2 py-0.5 rounded-full font-bold bg-slate-100 text-slate-700">{{ $newReportsCount }}</span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-slate-900 mt-2">{{ $newReportsCount }}</div>
                </div>
                <div class="text-[11px] text-slate-500 mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between">
                    <span>Tiket kerusakan aktif</span>
                    <span class="font-medium text-slate-700 group-hover:translate-x-0.5 transition-transform">Tindak lanjuti &rarr;</span>
                </div>
            </a>

            {{-- KPI 3: Fasilitas Terkunci / Perbaikan --}}
            <a href="{{ url('/petugas/report-management') }}" class="bg-white rounded-xl border border-slate-200/80 p-4 sm:p-5 shadow-2xs hover:border-slate-300 hover:bg-slate-50/40 transition-colors flex flex-col justify-between group">
                <div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-medium text-slate-500">Mode Perbaikan</span>
                        <span class="text-[11px] px-2 py-0.5 rounded-full font-bold bg-slate-100 text-slate-700">{{ $lockedFacilitiesCount }}</span>
                    </div>
                    <div class="text-2xl font-bold font-mono text-slate-900 mt-2">{{ $lockedFacilitiesCount }}</div>
                </div>
                <div class="text-[11px] text-slate-500 mt-3 pt-2.5 border-t border-slate-100 flex items-center justify-between">
                    <span>Ruang dinonaktifkan sementara</span>
                    <span class="font-medium text-slate-700 group-hover:translate-x-0.5 transition-transform">Cek fasilitas &rarr;</span>
                </div>
            </a>
        </div>

        {{-- 3. Antrean Prioritas Verifikasi Reservasi --}}
        <section class="bg-white rounded-xl border border-slate-200/80 shadow-2xs overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between gap-2">
                <h2 class="text-sm font-bold text-slate-900">Antrean Prioritas Verifikasi Reservasi</h2>
                <a href="{{ url('/petugas/reservation-management') }}" class="text-xs font-medium text-slate-600 hover:text-slate-900 inline-flex items-center gap-1 transition">
                    <span>Lihat seluruh antrean ({{ $pendingReservationsCount }})</span>
                    <span>&rarr;</span>
                </a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($recentReservations as $reservation)
                    @php
                        $payload = [
                            'id' => $reservation->id,
                            'ticket_code' => $reservation->ticket_code ?? 'TKT-' . $reservation->id,
                            'name' => $reservation->user->name ?? 'Pemohon',
                            'identity' => $reservation->user->identity_number ?? '-',
                            'email' => $reservation->user->email ?? '-',
                            'department' => $reservation->user->department ?? 'Sivitas Akademika',
                            'venue' => $reservation->facility->name ?? 'Fasilitas Kampus',
                            'building' => $reservation->facility->building ?? 'Gedung Sarpras',
                            'floor' => $reservation->facility->floor_location ?? null,
                            'capacity' => $reservation->facility->capacity ?? '-',
                            'date' => !empty($reservation->reservation_date) ? $reservation->reservation_date->translatedFormat('d F Y') : '-',
                            'time' => substr($reservation->start_time ?? '00:00', 0, 5) . ' - ' . substr($reservation->end_time ?? '00:00', 0, 5) . ' WIB',
                            'slots' => $reservation->total_slots ?? 1,
                            'purpose' => $reservation->purpose ?? '-',
                            'participants' => $reservation->participants_count ?? 1,
                            'permit_url' => $reservation->permit_letter_path ? asset('storage/' . $reservation->permit_letter_path) : null,
                        ];
                    @endphp

                    <div class="px-5 py-3.5 hover:bg-slate-50/60 transition-colors flex flex-col md:flex-row md:items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="text-xs text-slate-500 flex flex-wrap items-center gap-2">
                                <span class="font-mono font-medium text-slate-700">{{ $reservation->ticket_code ?? 'TKT-' . $reservation->id }}</span>
                                <span class="text-slate-300">&bull;</span>
                                <span class="font-medium text-slate-700">{{ !empty($reservation->reservation_date) ? $reservation->reservation_date->translatedFormat('d M Y') : '-' }}</span>
                                <span class="text-slate-300">&bull;</span>
                                <span class="font-mono text-slate-700">{{ substr($reservation->start_time ?? '00:00', 0, 5) }} - {{ substr($reservation->end_time ?? '00:00', 0, 5) }} WIB</span>
                            </div>

                            <div class="text-sm font-semibold text-slate-900 mt-1 truncate">
                                {{ $reservation->facility->name ?? 'Fasilitas Kampus' }}
                                <span class="text-xs font-normal text-slate-500">({{ $reservation->facility->building ?? 'Gedung' }})</span>
                            </div>

                            <div class="text-xs text-slate-500 mt-0.5">
                                Pemohon: <span class="text-slate-700 font-medium">{{ $reservation->user->name ?? 'Pemohon' }}</span>
                            </div>
                        </div>

                        <div class="flex items-center justify-between md:justify-end gap-3 shrink-0 pt-2 md:pt-0 border-t md:border-t-0 border-slate-100">
                            <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-700">
                                Menunggu Verifikasi
                            </span>

                            <button type="button" @click='openDetail(@json($payload))' class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-xs font-medium text-slate-700 hover:bg-slate-900 hover:text-white hover:border-slate-900 transition-colors shadow-2xs cursor-pointer">
                                Tinjau Permohonan
                            </button>
                        </div>
                    </div>
                @empty
                    <div class="py-10 text-center text-xs text-slate-400">
                        Tidak ada permohonan reservasi yang menunggu verifikasi saat ini.
                    </div>
                @endforelse
            </div>
        </section>

        {{-- 4. Laporan Kerusakan Baru --}}
        <section class="bg-white rounded-xl border border-slate-200/80 shadow-2xs overflow-hidden">
            <div class="px-5 py-3.5 border-b border-slate-100 flex items-center justify-between gap-2">
                <h2 class="text-sm font-bold text-slate-900">Laporan Kerusakan Terbaru</h2>
                <a href="{{ url('/petugas/report-management') }}" class="text-xs font-medium text-slate-600 hover:text-slate-900 inline-flex items-center gap-1 transition">
                    <span>Lihat seluruh laporan ({{ $newReportsCount }})</span>
                    <span>&rarr;</span>
                </a>
            </div>

            <div class="divide-y divide-slate-100">
                @forelse($recentReports as $report)
                    <div class="px-5 py-3.5 hover:bg-slate-50/60 transition-colors flex flex-col md:flex-row md:items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="text-xs text-slate-500 flex flex-wrap items-center gap-2">
                                <span class="font-mono font-medium text-slate-700">{{ $report->report_code ?? 'RPT-' . $report->id }}</span>
                                <span class="text-slate-300">&bull;</span>
                                <span>{{ !empty($report->created_at) ? \Carbon\Carbon::parse($report->created_at)->translatedFormat('d M Y, H:i') : '-' }} WIB</span>
                                <span class="text-slate-300">&bull;</span>
                                <span class="text-slate-600">{{ $report->category ?? 'Umum' }}</span>
                            </div>

                            <div class="text-sm font-semibold text-slate-900 mt-1 truncate">
                                {{ $report->facility->name ?? 'Fasilitas Kampus' }}
                            </div>

                            <p class="text-xs text-slate-600 mt-0.5 truncate">
                                {{ $report->description ?? '-' }}
                            </p>
                        </div>

                        <div class="flex items-center justify-between md:justify-end gap-3 shrink-0 pt-2 md:pt-0 border-t md:border-t-0 border-slate-100">
                            <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-700 capitalize">
                                {{ $report->status ?? 'baru' }}
                            </span>

                            <a href="{{ url('/petugas/report-management') }}" class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-xs font-medium text-slate-700 hover:bg-slate-900 hover:text-white hover:border-slate-900 transition-colors shadow-2xs">
                                Kelola Tiket
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="py-10 text-center text-xs text-slate-400">
                        Tidak ada laporan kerusakan aktif yang tertunda saat ini.
                    </div>
                @endforelse
            </div>
        </section>

        {{-- 5. Modal Pratinjau Cepat Saat Klik "Tinjau Permohonan" --}}
        <div x-show="showModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="showModal = false" class="bg-white rounded-2xl max-w-2xl w-full p-6 shadow-xl border border-slate-200 relative">
                <button type="button" @click="showModal = false" class="absolute top-5 right-5 p-1 rounded-lg text-slate-400 hover:text-slate-700 hover:bg-slate-100 transition">
                    <span class="material-symbols-outlined text-[20px]">close</span>
                </button>

                <template x-if="selected">
                    <div class="flex flex-col gap-5">
                        {{-- Header Modal --}}
                        <div class="pr-8">
                            <div class="flex items-center gap-2">
                                <span class="font-mono text-base font-bold text-slate-900" x-text="selected.ticket_code"></span>
                                <span class="px-2 py-0.5 rounded text-[11px] font-medium bg-slate-100 text-slate-700">Menunggu Verifikasi</span>
                            </div>
                            <p class="text-xs text-slate-500 mt-0.5">Peninjauan permohonan reservasi ruang kampus</p>
                        </div>

                        {{-- Rincian Permohonan (Grid 2 Kolom) --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            {{-- Kolom 1: Profil Pemohon --}}
                            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80">
                                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-2">Identitas Pemohon</div>
                                <div class="font-semibold text-slate-900 text-sm" x-text="selected.name"></div>
                                <div class="text-xs text-slate-600 mt-2 space-y-1">
                                    <p><span class="text-slate-500">NIM / NIP:</span> <span class="font-mono text-slate-800" x-text="selected.identity"></span></p>
                                    <p><span class="text-slate-500">Prodi / Lembaga:</span> <span class="text-slate-800" x-text="selected.department"></span></p>
                                    <p><span class="text-slate-500">Email:</span> <span class="text-slate-800" x-text="selected.email"></span></p>
                                </div>
                            </div>

                            {{-- Kolom 2: Ruang & Jadwal --}}
                            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80">
                                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-2">Fasilitas & Waktu</div>
                                <div class="font-semibold text-slate-900 text-sm" x-text="selected.venue"></div>
                                <div class="text-xs text-slate-600 mt-2 space-y-1">
                                    <p><span class="text-slate-500">Lokasi:</span> <span class="text-slate-800" x-text="selected.building + (selected.floor ? ' (Lt. ' + selected.floor + ')' : '')"></span></p>
                                    <p><span class="text-slate-500">Kapasitas:</span> <span class="text-slate-800" x-text="selected.capacity + ' Kursi'"></span></p>
                                    <p><span class="text-slate-500">Jadwal:</span> <span class="font-mono font-medium text-slate-900" x-text="selected.time"></span> (<span x-text="selected.slots + ' slot'"></span>)</p>
                                </div>
                            </div>

                            {{-- Kolom Penuh: Tujuan Kegiatan & Berkas --}}
                            <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-200/80 md:col-span-2">
                                <div class="text-[11px] font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Tujuan Kegiatan</div>
                                <p class="text-xs text-slate-800 leading-relaxed bg-white p-3 rounded-lg border border-slate-200/80" x-text="selected.purpose"></p>
                                
                                <div class="mt-3 flex flex-wrap items-center justify-between gap-3 text-xs">
                                    <div class="text-slate-600">
                                        Estimasi Peserta: <strong class="text-slate-900" x-text="selected.participants + ' Orang'"></strong>
                                    </div>
                                    <div>
                                        <template x-if="selected.permit_url">
                                            <a :href="selected.permit_url" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-900 text-white font-medium hover:bg-slate-800 transition shadow-2xs">
                                                <span class="material-symbols-outlined text-[14px]">attachment</span>
                                                <span>Buka Berkas Surat Izin</span>
                                            </a>
                                        </template>
                                        <template x-if="!selected.permit_url">
                                            <span class="text-slate-400 italic">Tidak melampirkan berkas fisik</span>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Footer Aksi Modal --}}
                        <div class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-3 border-t border-slate-100">
                            <div class="flex items-center gap-3">
                                <button type="button" @click="showModal = false" class="px-3.5 py-1.5 rounded-lg border border-slate-200 text-slate-700 text-xs font-medium hover:bg-slate-50 transition cursor-pointer">
                                    Tutup
                                </button>
                                <a :href="'{{ url('/petugas/reservation-management') }}?status=pending&search=' + selected.ticket_code" class="text-xs font-medium text-slate-500 hover:text-slate-900">
                                    Buka di Lembar Antrean &rarr;
                                </a>
                            </div>

                            <div class="flex items-center gap-2">
                                <button type="button" @click="openReject(selected.id, selected.name, selected.ticket_code)" class="px-3.5 py-1.5 rounded-lg border border-slate-200 text-rose-700 hover:bg-rose-50 hover:border-rose-200 transition text-xs font-medium cursor-pointer">
                                    Tolak Permohonan
                                </button>
                                <form :action="'{{ url('/petugas/reservations') }}/' + selected.id + '/approve'" method="POST" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" onclick="return confirm('Konfirmasi persetujuan permohonan ini?')" class="px-4 py-1.5 rounded-lg bg-slate-900 text-white text-xs font-medium hover:bg-slate-800 transition shadow-2xs cursor-pointer">
                                        Setujui Permohonan
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- 6. Modal Tolak Permohonan --}}
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
                        <label for="dashboard_rejection_reason" class="block text-xs font-medium text-slate-700 mb-1">
                            Alasan Penolakan:
                        </label>
                        <textarea id="dashboard_rejection_reason" name="rejection_reason" rows="3" required placeholder="Contoh: Jadwal bertabrakan dengan kegiatan universitas atau ruang sedang dalam perawatan..." class="w-full p-3 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-1 focus:ring-slate-900 focus:bg-white transition"></textarea>
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

    </div>
</x-petugas-layout>
