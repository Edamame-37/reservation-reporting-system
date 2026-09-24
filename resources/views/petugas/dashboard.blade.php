{{-- 
  NAMA FILE      : dashboard.blade.php
  FUNGSIONALITAS : Halaman Antarmuka Dasbor Utama & Antrean Petugas Operasional (US-8 / PTG-01)
  DESKRIPSI      : Menampilkan metrik antrean SLA (reservasi pending, laporan kerusakan baru/aktif, fasilitas perbaikan), tabel ringkasan 5 reservasi prioritas terbaru, dan tabel ringkasan 5 laporan kerusakan baru dengan aksi cepat langsung ke halaman verifikasi/manajemen.
  CARA KERJA     : Menggunakan layout <x-petugas-layout active="dashboard">. Menerima variabel data agregat ($pendingReservationsCount, $newReportsCount, $lockedFacilitiesCount) dan ringkasan antrean ($recentReservations, $recentReports). Menampilkan empty state ilustrasi 'Semua antrean beres' bila antrean kosong.
--}}

@php
    // Inisialisasi fallback aman untuk mode mockup / pratinjau sebelum controller database aktif
    $pendingReservationsCount = $pendingReservationsCount ?? 0;
    $newReportsCount = $newReportsCount ?? 0;
    $lockedFacilitiesCount = $lockedFacilitiesCount ?? 0;
    $recentReservations = $recentReservations ?? collect([]);
    $recentReports = $recentReports ?? collect([]);
@endphp

<x-petugas-layout title="Dasbor Operasional Sarpras" active="dashboard">
    <!-- 
      ELEMEN       : 3 KPI Stat Cards Operasional
      KEGUNAAN     : Menyajikan metrik beban kerja aktif petugas piket secara instan dengan pengingat SLA.
      CARA KERJA   : Menerima data agregasi kuantitas reservasi pending, laporan kerusakan aktif, dan fasilitas locked.
    -->
    <section class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <x-cava.stat-card 
            title="Antrean Reservasi Menunggu"
            :value="$pendingReservationsCount"
            subtitle="Permohonan Masuk"
            :tagText="$pendingReservationsCount > 0 ? $pendingReservationsCount . ' Menunggu Review' : 'Antrean Bersih'"
            footerText="SLA Respon < 2 Jam"
            icon="pending_actions"
            variant="primary"
        />

        <x-cava.stat-card 
            title="Antrean Laporan Kerusakan"
            :value="$newReportsCount"
            subtitle="Tiket Kerusakan Aktif"
            :tagText="$newReportsCount > 0 ? $newReportsCount . ' Perlu Tindak Lanjut' : 'Tidak Ada Tiket'"
            footerText="Target Selesai < 24 Jam"
            icon="build_circle"
            variant="error"
        />

        <x-cava.stat-card 
            title="Fasilitas Dalam Perbaikan"
            :value="$lockedFacilitiesCount"
            subtitle="Ruangan Terkunci (Locked)"
            :tagText="$lockedFacilitiesCount > 0 ? $lockedFacilitiesCount . ' Ruang Non-Aktif' : 'Semua Ruang Siap'"
            footerText="Otomatis Non-Aktif di Kalender"
            icon="domain_disabled"
            variant="tertiary"
        />
    </section>

    <!-- 
      ELEMEN       : Antrean Prioritas Verifikasi Reservasi (Maksimal 5 Data Preview + Tombol Lihat Semua)
      KEGUNAAN     : Memeriksa dan memproses permohonan reservasi masuk dengan validasi jadwal (US-8 & US-9).
      CARA KERJA   : Merender 5 data reservasi berstatus pending teratas (FIFO). Menampilkan ilustrasi 'Semua antrean beres' jika kosong.
    -->
    <section class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 mb-4 border-b border-slate-100 gap-2">
            <div>
                <h3 class="text-base font-bold text-slate-900">Antrean Prioritas Verifikasi Reservasi</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Menampilkan {{ count($recentReservations) }} permohonan terbaru dari total {{ $pendingReservationsCount }} antrean masuk.
                </p>
            </div>
            {{-- Tombol Lihat Selengkapnya menuju Antrean Lengkap --}}
            <a href="{{ url('/petugas/reservation-management') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-blue-950 hover:text-blue-700 transition">
                <span>Buka Seluruh Antrean Reservasi ({{ $pendingReservationsCount }} Data)</span>
                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                        <th class="py-3 px-4">Pemohon & Lembaga</th>
                        <th class="py-3 px-4">Fasilitas Diminta</th>
                        <th class="py-3 px-4">Jadwal Sesi 30m</th>
                        <th class="py-3 px-4">Status Antrean</th>
                        <th class="py-3 px-4 text-right">Aksi Cepat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentReservations as $reservation)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900">{{ $reservation->user->name ?? 'Pemohon' }}</div>
                                <div class="text-[11px] text-slate-500">
                                    {{ $reservation->user->identity_number ? $reservation->user->identity_number . ' • ' : '' }}{{ $reservation->user->email ?? '-' }}
                                </div>
                                @if(!empty($reservation->user->department))
                                    <span class="text-[10px] text-blue-900 font-semibold mt-0.5 block">
                                        {{ $reservation->user->department }}
                                    </span>
                                @endif
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900">{{ $reservation->facility->name ?? 'Fasilitas Kampus' }}</div>
                                <div class="text-[11px] text-slate-500">
                                    {{ $reservation->facility->building ?? 'Gedung Sarpras' }}
                                    @if(!empty($reservation->facility->capacity))
                                        • ({{ $reservation->facility->capacity }} Kursi)
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900">
                                    {{ !empty($reservation->reservation_date) ? \Carbon\Carbon::parse($reservation->reservation_date)->translatedFormat('d M Y') : '-' }}
                                </div>
                                <div class="text-[11px] text-slate-600 font-mono">
                                    {{ substr($reservation->start_time ?? '00:00', 0, 5) }} - {{ substr($reservation->end_time ?? '00:00', 0, 5) }} WIB 
                                    @if(!empty($reservation->total_slots))
                                        ({{ $reservation->total_slots }} slot)
                                    @endif
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <x-cava.status-badge :status="$reservation->status ?? 'pending'" />
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ url('/petugas/reservation-management') }}" class="px-3 py-1 rounded-lg bg-slate-900 text-white font-semibold hover:bg-slate-800 transition shadow-xs text-xs inline-flex items-center gap-1">
                                        <span>Tinjau & Setujui</span>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <!-- 
                          ELEMEN       : State Kosong (Empty State) Antrean Reservasi
                          KEGUNAAN     : Memberi umpan balik bahwa tidak ada antrean pending yang terlewat.
                        -->
                        <tr>
                            <td colspan="5" class="py-12 px-4 text-center">
                                <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-3">
                                        <span class="material-symbols-outlined text-[28px]">task_alt</span>
                                    </div>
                                    <h4 class="text-sm font-bold text-slate-900">Semua Antrean Beres!</h4>
                                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                        Tidak ada permohonan reservasi yang menunggu persetujuan saat ini. SLA operasional dalam kondisi optimal.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <!-- 
      ELEMEN       : Section Laporan Kerusakan Baru / Mendesak (Maksimal 5 Data Preview + Tombol Lihat Semua)
      KEGUNAAN     : Memantau tiket keluhan fasilitas yang membutuhkan respons cepat petugas (US-8 & US-11).
      CARA KERJA   : Merender 5 tiket kerusakan berstatus baru/diproses teratas (FIFO) dalam bentuk tabel ringkasan mini.
    -->
    <section class="bg-white rounded-2xl border border-slate-200/80 p-5 shadow-xs">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between pb-3 mb-4 border-b border-slate-100 gap-2">
            <div>
                <h3 class="text-base font-bold text-slate-900">Laporan Kerusakan Baru Perlu Tindakan</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Menampilkan {{ count($recentReports) }} tiket laporan terbaru dari total {{ $newReportsCount }} kerusakan aktif di kampus.
                </p>
            </div>
            {{-- Tombol Lihat Selengkapnya menuju Manajemen Laporan --}}
            <a href="{{ url('/petugas/report-management') }}" class="inline-flex items-center gap-1 text-xs font-semibold text-blue-950 hover:text-blue-700 transition">
                <span>Kelola Seluruh Laporan Kerusakan ({{ $newReportsCount }} Data)</span>
                <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs border-collapse">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                        <th class="py-3 px-4">Tiket & Waktu</th>
                        <th class="py-3 px-4">Fasilitas & Pelapor</th>
                        <th class="py-3 px-4">Kategori & Deskripsi Kendala</th>
                        <th class="py-3 px-4">Status Tiket</th>
                        <th class="py-3 px-4 text-right">Aksi Cepat</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentReports as $report)
                        <tr class="hover:bg-slate-50/70 transition">
                            <td class="py-3.5 px-4 whitespace-nowrap">
                                <span class="font-mono text-xs font-bold text-slate-900 block">
                                    {{ $report->report_code ?? 'RPT-' . $report->id }}
                                </span>
                                <span class="text-[11px] text-slate-400 mt-0.5 block">
                                    {{ !empty($report->created_at) ? \Carbon\Carbon::parse($report->created_at)->translatedFormat('d M Y, H:i') . ' WIB' : '-' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-slate-900 flex items-center gap-1">
                                    <span class="material-symbols-outlined text-[15px] text-slate-400">location_on</span>
                                    <span>{{ $report->facility->name ?? 'Fasilitas Kampus' }}</span>
                                </div>
                                <div class="text-[11px] text-slate-500 mt-0.5">
                                    Pelapor: {{ $report->user->name ?? 'Sivitas Kampus' }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold bg-slate-100 text-slate-700 mb-1">
                                    {{ $report->category ?? 'Umum' }}
                                </span>
                                <p class="text-xs text-slate-600 line-clamp-1">
                                    {{ $report->description ?? '-' }}
                                </p>
                            </td>
                            <td class="py-3.5 px-4">
                                <x-cava.status-badge :status="$report->status ?? 'baru'" />
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <a href="{{ url('/petugas/report-management') }}" class="px-3 py-1 rounded-lg border border-slate-200 bg-slate-50 text-slate-700 font-semibold hover:bg-slate-100 hover:text-slate-900 transition shadow-xs text-xs inline-flex items-center gap-1">
                                    <span>Proses Tiket →</span>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <!-- 
                          ELEMEN       : State Kosong (Empty State) Laporan Kerusakan
                          KEGUNAAN     : Memberi umpan balik bahwa tidak ada keluhan sarana kampus yang terabaikan.
                        -->
                        <tr>
                            <td colspan="5" class="py-12 px-4 text-center">
                                <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                    <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-3">
                                        <span class="material-symbols-outlined text-[28px]">check_circle</span>
                                    </div>
                                    <h4 class="text-sm font-bold text-slate-900">Semua Laporan Beres!</h4>
                                    <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                        Tidak ada tiket kerusakan fasilitas baru atau aktif yang membutuhkan tindakan. Semua sarana dalam kondisi operasional.
                                    </p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</x-petugas-layout>
