{{-- 
  NAMA FILE      : reservation-management.blade.php
  FUNGSIONALITAS : Lembar Kerja Verifikasi, Approval & Pembatalan Darurat Petugas Sarpras (PTG-02 / PTG-03 / US-9 / US-10)
  DESKRIPSI      : Menampilkan seluruh antrean reservasi masuk dengan validasi bentrok jadwal, formulir persetujuan resmi, modal penolakan dengan input alasan wajib, serta modal pembatalan darurat sepihak (override privilege) oleh petugas dengan alasan wajib minimal 10 karakter.
  CARA KERJA     : Memanfaatkan layout <x-petugas-layout active="reservation-management">, mengelola modal interaktif penolakan dan pembatalan darurat via Alpine.js, serta memicu pengiriman PATCH/DELETE request ke endpoint operasional petugas.
--}}

@php
    // Inisialisasi fallback aman jika data belum dipass dari Controller
    $reservations = $reservations ?? collect([]);
    $totalCount = $totalCount ?? $reservations->count();
    $pendingCount = $pendingCount ?? $reservations->where('status', 'pending')->count();
    $approvedCount = $approvedCount ?? $reservations->where('status', 'approved')->count();
    $cancelledCount = $cancelledCount ?? $reservations->where('status', 'cancelled')->count();
    $rejectedCount = $rejectedCount ?? $reservations->where('status', 'rejected')->count();
@endphp

<x-petugas-layout title="Manajemen & Approval Reservasi" active="reservation-management">
    <div x-data="{
        showRejectModal: false,
        showCancelModal: false,
        selectedId: null,
        applicantName: '',
        ticketCode: '',
        venueName: '',
        activeFilter: 'semua',
        search: '',
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
        matchesFilter(status, content) {
            const matchStatus = (this.activeFilter === 'semua') || (this.activeFilter === status);
            const matchSearch = !this.search || content.toLowerCase().includes(this.search.toLowerCase());
            return matchStatus && matchSearch;
        }
    }" class="flex flex-col gap-6">

        {{-- 1. Notifikasi Umpan Balik (Feedback Flash Message) --}}
        @if(session('success'))
            <!-- 
              ELEMEN       : Banner Notifikasi Sukses
              KEGUNAAN     : Memberikan umpan balik positif ketika reservasi berhasil disetujui, ditolak, atau dibatalkan darurat.
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
              ELEMEN       : Banner Notifikasi Galat / Peringatan Sistem
              KEGUNAAN     : Menampilkan penolakan sistem anti-bentrok atau kegagalan validasi alasan pembatalan/penolakan.
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
                <span class="text-slate-900">Manajemen & Approval Reservasi</span>
            </div>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 tracking-tight">Antrean Lengkap Persetujuan Reservasi</h1>
                    <p class="text-xs sm:text-sm text-slate-500 mt-0.5">
                        Evaluasi berkas pengajuan, tujuan kegiatan, verifikasi anti-bentrok jadwal, serta eksekusi pembatalan darurat jika terjadi keadaan mendesak.
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1.5 rounded-xl bg-white border border-slate-200 text-xs font-semibold text-slate-700 shadow-xs">
                        Total Antrean: <strong class="text-slate-900">{{ $totalCount }} Pengajuan</strong>
                    </span>
                </div>
            </div>
        </div>

        {{-- 3. Filter & Search Bar --}}
        <div class="bg-white rounded-2xl border border-slate-200/80 p-4 shadow-xs flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-1 p-1 bg-slate-100 rounded-xl text-xs font-medium w-full md:w-auto overflow-x-auto">
                <button type="button" @click="activeFilter = 'semua'" :class="activeFilter === 'semua' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                    Semua ({{ $totalCount }})
                </button>
                <button type="button" @click="activeFilter = 'pending'" :class="activeFilter === 'pending' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                    Menunggu ({{ $pendingCount }})
                </button>
                <button type="button" @click="activeFilter = 'approved'" :class="activeFilter === 'approved' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                    Disetujui ({{ $approvedCount }})
                </button>
                <button type="button" @click="activeFilter = 'cancelled'" :class="activeFilter === 'cancelled' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                    Dibatalkan ({{ $cancelledCount }})
                </button>
                <button type="button" @click="activeFilter = 'rejected'" :class="activeFilter === 'rejected' ? 'bg-white text-slate-900 font-bold shadow-xs' : 'text-slate-600 hover:text-slate-900'" class="px-3 py-1.5 rounded-lg transition whitespace-nowrap">
                    Ditolak ({{ $rejectedCount }})
                </button>
            </div>

            <div class="relative w-full md:w-64">
                <span class="material-symbols-outlined absolute left-3 top-2.5 text-slate-400 text-[18px]">search</span>
                <input type="text" x-model="search" placeholder="Cari tiket / pemohon / ruang..." class="w-full pl-9 pr-4 py-2 text-xs bg-slate-50 border border-slate-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-slate-900 focus:bg-white transition">
            </div>
        </div>

        {{-- 4. Tabel Antrean Lengkap --}}
        <!-- 
          ELEMEN       : Tabel Antrean Reservasi Komprehensif
          KEGUNAAN     : Menyajikan daftar pemesanan ruang dengan slot waktu, surat izin, status, tombol approval, dan tuas pembatalan darurat.
          CARA KERJA   : Melakukan perulangan Blade dinamis @forelse($reservations as $reservation) dengan kontrol filter reaktif via Alpine.js.
        -->
        <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs border-collapse">
                    <thead>
                        <tr class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                            <th class="py-3 px-4">Tiket & Pemohon</th>
                            <th class="py-3 px-4">Fasilitas Diminta</th>
                            <th class="py-3 px-4">Tanggal & Slot Waktu</th>
                            <th class="py-3 px-4">Tujuan & Surat Izin</th>
                            <th class="py-3 px-4">Status & Catatan</th>
                            <th class="py-3 px-4 text-right">Keputusan Operasional</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($reservations as $reservation)
                            @php
                                $searchContent = ($reservation->ticket_code ?? '') . ' ' . ($reservation->user->name ?? '') . ' ' . ($reservation->facility->name ?? '') . ' ' . ($reservation->purpose ?? '');
                            @endphp
                            <tr x-show="matchesFilter('{{ $reservation->status }}', '{{ addslashes($searchContent) }}')" class="hover:bg-slate-50/70 transition">
                                <td class="py-3.5 px-4 align-top">
                                    <div class="font-mono text-xs font-bold text-slate-900">
                                        {{ $reservation->ticket_code ?? 'TKT-' . $reservation->id }}
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
                                <td class="py-3.5 px-4 align-top">
                                    <div class="font-bold text-slate-900">{{ $reservation->facility->name ?? 'Fasilitas Kampus' }}</div>
                                    <div class="text-[11px] text-slate-500">
                                        {{ $reservation->facility->building ?? 'Gedung Sarpras' }}
                                        @if(!empty($reservation->facility->capacity))
                                            • ({{ $reservation->facility->capacity }} Kursi)
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 align-top">
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
                                <td class="py-3.5 px-4 align-top max-w-xs">
                                    <p class="text-slate-800 line-clamp-2 leading-relaxed">{{ $reservation->purpose ?? '-' }}</p>
                                    <div class="mt-1 flex items-center gap-2 text-[11px]">
                                        @if(!empty($reservation->participants_count))
                                            <span class="text-slate-500">Peserta: {{ $reservation->participants_count }} org</span>
                                        @endif
                                        @if(!empty($reservation->permit_letter_path))
                                            <a href="{{ asset('storage/' . $reservation->permit_letter_path) }}" target="_blank" class="inline-flex items-center gap-0.5 text-blue-800 font-semibold hover:underline">
                                                <span class="material-symbols-outlined text-[13px]">attachment</span>
                                                <span>Surat Izin</span>
                                            </a>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 align-top">
                                    <div class="flex flex-col gap-1 items-start">
                                        <x-cava.status-badge :status="$reservation->status ?? 'pending'" />

                                        @if($reservation->status === 'pending')
                                            <span class="inline-flex items-center gap-1 text-[10px] text-emerald-700 font-semibold bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200/60">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                                <span>Siap Dievaluasi</span>
                                            </span>
                                        @elseif($reservation->status === 'approved')
                                            <span class="inline-flex items-center gap-1 text-[10px] text-blue-700 font-semibold bg-blue-50 px-2 py-0.5 rounded-md border border-blue-200/60">
                                                <span class="material-symbols-outlined text-[12px]">lock</span>
                                                <span>Slot Terkunci</span>
                                            </span>
                                        @elseif($reservation->status === 'cancelled' && !empty($reservation->cancellation_reason))
                                            <!-- Catatan Alasan Pembatalan Darurat Petugas (PTG-03) -->
                                            <div class="mt-1 text-[11px] text-slate-700 bg-slate-100 p-2 rounded-lg border border-slate-200/80 max-w-xs">
                                                <strong class="text-rose-700">Alasan Batal:</strong> {{ $reservation->cancellation_reason }}
                                            </div>
                                        @elseif($reservation->status === 'rejected' && !empty($reservation->rejection_reason))
                                            <div class="mt-1 text-[11px] text-rose-700 bg-rose-50 p-2 rounded-lg border border-rose-200/60 max-w-xs">
                                                <strong>Alasan Tolak:</strong> {{ $reservation->rejection_reason }}
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-3.5 px-4 align-top text-right whitespace-nowrap">
                                    @if($reservation->status === 'pending')
                                        <div class="flex items-center justify-end gap-1.5">
                                            <!-- 
                                              ROUTE: POST /petugas/reservations/{id}/approve (dengan @method('PATCH'))
                                              FUNGSI: Mengeksekusi persetujuan reservasi dengan verifikasi anti-bentrok jadwal di server (PTG-02).
                                            -->
                                            <form action="{{ url('/petugas/reservations/' . $reservation->id . '/approve') }}" method="POST" class="inline">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" onclick="return confirm('Setujui reservasi {{ $reservation->ticket_code }}? Slot waktu fasilitas akan resmi dikunci.')" class="px-3 py-1.5 rounded-xl bg-slate-900 text-white font-semibold hover:bg-slate-800 transition shadow-xs flex items-center gap-1 text-xs">
                                                    <span class="material-symbols-outlined text-[15px]">check_circle</span>
                                                    <span>Setujui</span>
                                                </button>
                                            </form>

                                            <!-- Tombol pemicu modal penolakan resmi (PTG-02) -->
                                            <button type="button" @click="openReject('{{ $reservation->id }}', '{{ addslashes($reservation->user->name ?? 'Pemohon') }}', '{{ $reservation->ticket_code }}')" class="px-2.5 py-1.5 rounded-xl border border-slate-200 text-rose-700 hover:bg-rose-50 transition font-semibold text-xs">
                                                Tolak
                                            </button>
                                        </div>
                                    @elseif($reservation->status === 'approved')
                                        <div class="flex items-center justify-end">
                                            <!-- 
                                              ELEMEN       : Tombol Pembatalan Darurat (Override) oleh Petugas (PTG-03 / US-10)
                                              KEGUNAAN     : Membuka modal pembatalan darurat sepihak untuk reservasi yang telah berstatus disetujui.
                                            -->
                                            <button type="button" @click="openCancel('{{ $reservation->id }}', '{{ $reservation->ticket_code }}', '{{ addslashes($reservation->facility->name ?? 'Fasilitas') }}', '{{ addslashes($reservation->user->name ?? 'Pemohon') }}')" class="px-3 py-1.5 rounded-xl border border-rose-300 text-rose-700 bg-rose-50 hover:bg-rose-100 hover:border-rose-400 transition font-semibold text-xs inline-flex items-center gap-1 shadow-xs" title="Pembatalan Darurat Petugas (US-10)">
                                                <span class="material-symbols-outlined text-[15px]">event_busy</span>
                                                <span>Batalkan Paksa</span>
                                            </button>
                                        </div>
                                    @else
                                        <span class="text-slate-400 text-xs italic">Selesai Ditinjau</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <!-- 
                              ELEMEN       : State Kosong (Empty State)
                              KEGUNAAN     : Memberi umpan balik bahwa tidak ada permohonan reservasi di antrean.
                            -->
                            <tr>
                                <td colspan="6" class="py-12 px-4 text-center">
                                    <div class="flex flex-col items-center justify-center max-w-sm mx-auto">
                                        <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mb-3">
                                            <span class="material-symbols-outlined text-[28px]">task_alt</span>
                                        </div>
                                        <h4 class="text-sm font-bold text-slate-900">Tidak Ada Antrean Reservasi</h4>
                                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">
                                            Seluruh pengajuan reservasi fasilitas telah selesai diverifikasi atau belum ada permohonan baru yang diajukan sivitas.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 5. Modal Tolak Permohonan (PTG-02) --}}
        <!-- 
          ELEMEN       : Modal Interaktif Penolakan Reservasi
          KEGUNAAN     : Memaksa petugas memasukkan alasan penolakan secara resmi sebelum data permohonan ditolak (PTG-02).
          CARA KERJA   : Terbuka saat showRejectModal = true. Mengarahkan form PATCH ke /petugas/reservations/{id}/reject dengan payload input rejection_reason.
        -->
        <div x-show="showRejectModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="showRejectModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-1.5 text-rose-700">
                        <span class="material-symbols-outlined text-[20px]">cancel</span>
                        <span>Tolak Permohonan Reservasi</span>
                    </h3>
                    <button type="button" @click="showRejectModal = false" class="text-slate-400 hover:text-slate-700">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
                <p class="text-xs text-slate-500 mb-4 leading-relaxed">
                    Menolak tiket <strong class="text-slate-900" x-text="ticketCode"></strong> untuk pemohon <strong class="text-slate-900" x-text="applicantName"></strong>. Masukkan alasan penolakan secara resmi untuk pemohon:
                </p>

                <!-- 
                  ROUTE: POST /petugas/reservations/{id}/reject (dengan @method('PATCH'))
                  FUNGSI: Meneruskan input alasan penolakan ke server untuk menandai reservasi rejected (PTG-02).
                -->
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

        {{-- 6. Modal Pembatalan Darurat Sepihak oleh Petugas (PTG-03 / US-10) --}}
        <!-- 
          ELEMEN       : Modal Pembatalan Darurat (Override) oleh Petugas
          KEGUNAAN     : Membatalkan reservasi yang telah disetujui sebelumnya akibat kondisi darurat (atap bocor, korsleting, dll).
          CARA KERJA   : Terbuka saat showCancelModal = true. Mengirimkan DELETE request ke /petugas/reservations/{id}/force-cancel beserta alasan_batal minimal 10 karakter.
        -->
        <div x-show="showCancelModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
            <div @click.away="showCancelModal = false" class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-slate-200">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-1.5 text-rose-700">
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
                        <strong>Peringatan Tindakan Sepihak:</strong> Anda akan membatalkan reservasi tiket <strong class="text-slate-900 font-mono" x-text="ticketCode"></strong> untuk pemohon <strong class="text-slate-900" x-text="applicantName"></strong> pada ruang <strong class="text-slate-900" x-text="venueName"></strong>. Slot waktu terkait akan segera dibebaskan di kalender ketersediaan publik.
                    </div>
                </div>

                <!-- 
                  ROUTE: POST /petugas/reservations/{id}/force-cancel (dengan @method('DELETE'))
                  FUNGSI: Mengeksekusi pembatalan paksa tiket reservasi approved oleh petugas dan menyimpan alasan resmi (PTG-03 / US-10).
                -->
                <form :action="'{{ url('/petugas/reservations') }}/' + selectedId + '/force-cancel'" method="POST">
                    @csrf
                    @method('DELETE')

                    <div class="mb-4">
                        <label for="alasan_batal" class="block text-xs font-bold text-slate-700 mb-1">
                            Alasan Pembatalan Darurat (Wajib, Min. 10 Karakter):
                        </label>
                        <textarea id="alasan_batal" name="alasan_batal" rows="3" required minlength="10" placeholder="Jelaskan alasan darurat pembatalan sepihak (contoh: Terjadi kebocoran pipa pendingin ruangan mendadak, ruangan harus segera diperbaiki teknisi sarpras)..." class="w-full p-3 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:outline-none focus:ring-2 focus:ring-slate-900 focus:bg-white transition"></textarea>
                        <p class="text-[11px] text-slate-400 mt-1">Alasan pembatalan resmi ini akan dikirimkan kepada pemohon terkait.</p>
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
