{{-- 
  NAMA FILE      : dashboard.blade.php
  FUNGSIONALITAS : Halaman Dasbor Utama Pengguna Mahasiswa & Dosen (CAVA Portal)
  DESKRIPSI      : Menampilkan antarmuka Apple-esque Bento dengan kartu sorotan jadwal terdekat, 
                   4 pintasan cepat Bento, riwayat reservasi terkini (maks 3 item), serta status laporan kerusakan terkini.
                   Mengadopsi Status Opsi B: Pure Typographic (bebas dari bulatan titik/dot dan ikon mikro pada status).
  CARA KERJA     : Menggunakan layout <x-app-layout active="dashboard">, menjaga header dan sidebar bawaan tetap utuh.
--}}

<x-app-layout title="Dasbor Pengguna" active="dashboard">
    <div class="space-y-10 font-sans text-[#1D1D1F]">
        {{-- SPOTLIGHT HERO: KARTU SOROTAN BERSIH TANPA KOTAK BADGE & TANPA DOT --}}
        <section class="bg-white rounded-[2rem] p-8 md:p-10 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.03)] flex flex-col md:flex-row md:items-center justify-between gap-6">
            @if($upcomingReservation)
                <div class="space-y-3 flex-1">
                    {{-- Status Opsi B: Pure Typographic --}}
                    <div>
                        <span class="text-xs font-semibold tracking-wide text-emerald-800">
                            Jadwal Terdekat • Disetujui
                        </span>
                    </div>

                    <h2 class="text-2xl font-bold tracking-tight text-[#1D1D1F]">
                        {{ $upcomingReservation->purpose }}
                    </h2>

                    <p class="text-sm text-[#86868B]">
                        {{ $upcomingReservation->facility->name ?? 'Fasilitas' }}, {{ $upcomingReservation->facility->building ?? 'Gedung' }} • {{ \Carbon\Carbon::parse($upcomingReservation->reservation_date)->isoFormat('dddd, D MMMM Y') }} ({{ substr($upcomingReservation->start_time, 0, 5) }} - {{ substr($upcomingReservation->end_time, 0, 5) }} WIB)
                    </p>
                </div>

                <div class="shrink-0 self-start md:self-center">
                    <a href="{{ url('/user/reservation-history') }}" class="inline-flex items-center gap-2 bg-[#1D1D1F] text-white px-6 py-3 rounded-xl font-medium hover:bg-gray-800 transition text-sm">
                        <span>Buka Detail Tiket</span>
                        <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                    </a>
                </div>
            @else
                <div class="space-y-2 flex-1">
                    <div>
                        <span class="text-xs font-semibold tracking-wide text-slate-500">
                            Jadwal Terdekat
                        </span>
                    </div>

                    <h2 class="text-2xl font-bold tracking-tight text-[#1D1D1F]">
                        Belum Ada Jadwal Terdekat
                    </h2>

                    <p class="text-sm text-[#86868B]">
                        Anda belum memiliki permohonan reservasi ruangan yang disetujui untuk waktu dekat.
                    </p>
                </div>

                <div class="shrink-0 self-start md:self-center">
                    <a href="{{ url('/user/reservation-form') }}" class="inline-flex items-center gap-2 bg-[#1D1D1F] text-white px-6 py-3 rounded-xl font-medium hover:bg-gray-800 transition text-sm">
                        <span>Ajukan Reservasi Baru</span>
                        <span class="material-symbols-outlined text-[16px]">add</span>
                    </a>
                </div>
            @endif
        </section>

        {{-- 3. 4 PINTASAN BENTO (PERSIS PUSAT BANTUAN public/home.blade.php) --}}
        <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            
            {{-- Kartu 1: Ajukan Reservasi --}}
            <a href="{{ url('/user/reservation-form') }}" class="group relative overflow-hidden bg-[#F9F9FB] rounded-[2rem] p-8 hover:bg-white hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all duration-500 flex flex-col justify-between min-h-[160px] cursor-pointer">
                <div class="relative z-10">
                    <h3 class="text-xl font-bold text-[#1D1D1F] mb-1 group-hover:text-black transition-colors">Ajukan Reservasi</h3>
                    <p class="text-sm text-[#86868B]">Peminjaman ruang kuliah atau lab.</p>
                </div>
                <div class="relative z-10 inline-flex items-center gap-1 text-sm font-medium text-[#1D1D1F] mt-4">
                    <span>Buka Formulir</span>
                    <span class="material-symbols-outlined text-[16px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
                </div>
                <div class="absolute right-0 inset-y-0 flex items-center pr-2 transform scale-[1.6] translate-x-4 group-hover:scale-100 group-hover:-translate-x-2 transition-all duration-700 ease-out pointer-events-none select-none">
                    <span class="material-symbols-outlined text-[90px] text-gray-200/50 group-hover:text-gray-300/40 transition-colors duration-700 font-light">meeting_room</span>
                </div>
            </a>

            {{-- Kartu 2: Riwayat Reservasi --}}
            <a href="{{ url('/user/reservation-history') }}" class="group relative overflow-hidden bg-[#F9F9FB] rounded-[2rem] p-8 hover:bg-white hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all duration-500 flex flex-col justify-between min-h-[160px] cursor-pointer">
                <div class="relative z-10">
                    <h3 class="text-xl font-bold text-[#1D1D1F] mb-1 group-hover:text-black transition-colors">Riwayat Reservasi</h3>
                    <p class="text-sm text-[#86868B]">Pantau tiket dan pembatalan H-1.</p>
                </div>
                <div class="relative z-10 inline-flex items-center gap-1 text-sm font-medium text-[#1D1D1F] mt-4">
                    <span>Lihat Semua Tiket</span>
                    <span class="material-symbols-outlined text-[16px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
                </div>
                <div class="absolute right-0 inset-y-0 flex items-center pr-2 transform scale-[1.6] translate-x-4 group-hover:scale-100 group-hover:-translate-x-2 transition-all duration-700 ease-out pointer-events-none select-none">
                    <span class="material-symbols-outlined text-[90px] text-gray-200/50 group-hover:text-gray-300/40 transition-colors duration-700 font-light">calendar_month</span>
                </div>
            </a>

            {{-- Kartu 3: Lapor Kerusakan --}}
            <a href="{{ url('/user/report-form') }}" class="group relative overflow-hidden bg-[#F9F9FB] rounded-[2rem] p-8 hover:bg-white hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all duration-500 flex flex-col justify-between min-h-[160px] cursor-pointer">
                <div class="relative z-10">
                    <h3 class="text-xl font-bold text-[#1D1D1F] mb-1 group-hover:text-black transition-colors">Lapor Kerusakan</h3>
                    <p class="text-sm text-[#86868B]">Pusat bantuan teknis fasilitas.</p>
                </div>
                <div class="relative z-10 inline-flex items-center gap-1 text-sm font-medium text-[#1D1D1F] mt-4">
                    <span>Kirim Laporan</span>
                    <span class="material-symbols-outlined text-[16px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
                </div>
                <div class="absolute right-0 inset-y-0 flex items-center pr-2 transform scale-[1.6] translate-x-4 group-hover:scale-100 group-hover:-translate-x-2 transition-all duration-700 ease-out pointer-events-none select-none">
                    <span class="material-symbols-outlined text-[90px] text-gray-200/50 group-hover:text-gray-300/40 transition-colors duration-700 font-light">support_agent</span>
                </div>
            </a>

            {{-- Kartu 4: Status Laporan --}}
            <a href="{{ url('/user/report-history') }}" class="group relative overflow-hidden bg-[#F9F9FB] rounded-[2rem] p-8 hover:bg-white hover:shadow-[0_8px_30px_rgb(0,0,0,0.08)] transition-all duration-500 flex flex-col justify-between min-h-[160px] cursor-pointer">
                <div class="relative z-10">
                    <h3 class="text-xl font-bold text-[#1D1D1F] mb-1 group-hover:text-black transition-colors">Status Perbaikan</h3>
                    <p class="text-sm text-[#86868B]">Tindak lanjut oleh teknisi.</p>
                </div>
                <div class="relative z-10 inline-flex items-center gap-1 text-sm font-medium text-[#1D1D1F] mt-4">
                    <span>Pantau Progres</span>
                    <span class="material-symbols-outlined text-[16px] group-hover:translate-x-1 transition-transform">arrow_forward</span>
                </div>
                <div class="absolute right-0 inset-y-0 flex items-center pr-2 transform scale-[1.6] translate-x-4 group-hover:scale-100 group-hover:-translate-x-2 transition-all duration-700 ease-out pointer-events-none select-none">
                    <span class="material-symbols-outlined text-[90px] text-gray-200/50 group-hover:text-gray-300/40 transition-colors duration-700 font-light">verified</span>
                </div>
            </a>

        </section>

        {{-- 4. DUA KOLOM: RIWAYAT TERKINI & LAPORAN KERUSAKAN (LUAS, BERNAPAS, TIDAK DEMPET) --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 lg:gap-10">
            
            {{-- Kolom Kiri: Riwayat Permohonan Terkini --}}
            <section class="bg-white rounded-[2rem] p-8 sm:p-10 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.03)] space-y-6">
                <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                    <div>
                        <h3 class="text-xl font-bold text-[#1D1D1F]">Riwayat Permohonan Terkini</h3>
                        <p class="text-xs text-[#86868B] mt-0.5">Menampilkan {{ $recentReservations->take(3)->count() }} dari total {{ $totalReservations }} pengajuan Anda</p>
                    </div>
                    <a href="{{ url('/user/reservation-history') }}" class="text-sm font-semibold text-[#1D1D1F] hover:text-blue-600 flex items-center gap-1 transition">
                        <span>Lihat Semua ({{ $totalReservations }})</span>
                        <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                    </a>
                </div>

                <div class="space-y-4">
                    @forelse($recentReservations->take(3) as $reservation)
                        <a href="{{ url('/user/reservation-history') }}" class="bg-[#F9F9FB] rounded-2xl p-5 sm:p-6 border border-transparent hover:border-gray-200/80 hover:bg-white hover:shadow-[0_8px_24px_rgba(0,0,0,0.05)] transition-all duration-300 cursor-pointer group space-y-3 block">
                            <div class="flex items-start justify-between gap-4">
                                <h4 class="font-bold text-[#1D1D1F] text-base group-hover:text-blue-600 transition leading-snug">
                                    {{ $reservation->facility->name ?? 'Fasilitas Kampus' }}
                                </h4>
                                {{-- Status Opsi B: Pure Typographic --}}
                                <div class="shrink-0 pt-0.5">
                                    @if ($reservation->status === 'approved')
                                        <span class="text-xs font-semibold tracking-wide text-emerald-800">Disetujui</span>
                                    @elseif ($reservation->status === 'pending')
                                        <span class="text-xs font-semibold tracking-wide text-amber-800">Menunggu</span>
                                    @elseif ($reservation->status === 'rejected')
                                        <span class="text-xs font-semibold tracking-wide text-rose-700">Ditolak</span>
                                    @elseif ($reservation->status === 'cancelled')
                                        <span class="text-xs font-semibold tracking-wide text-slate-500">Dibatalkan</span>
                                    @else
                                        <span class="text-xs font-semibold tracking-wide text-slate-600 capitalize">{{ $reservation->status }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-[#86868B] pt-1 border-t border-gray-100/70">
                                <span>{{ \Carbon\Carbon::parse($reservation->reservation_date)->isoFormat('dddd, D MMMM Y') }}</span>
                                <span>•</span>
                                <span>{{ substr($reservation->start_time, 0, 5) }} - {{ substr($reservation->end_time, 0, 5) }} WIB</span>
                                <span>•</span>
                                <span class="text-[#1D1D1F] font-medium truncate max-w-[220px]">{{ $reservation->purpose }}</span>
                            </div>
                        </a>
                    @empty
                        <div class="py-10 text-center text-sm text-[#86868B] rounded-2xl border border-dashed border-gray-200 bg-[#F9F9FB]">
                            Belum ada riwayat permohonan reservasi ruangan.
                        </div>
                    @endforelse
                </div>
            </section>

            {{-- Kolom Kanan: Status Laporan Kerusakan --}}
            <section class="bg-white rounded-[2rem] p-8 sm:p-10 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.03)] flex flex-col justify-between space-y-6">
                <div class="space-y-6">
                    <div class="flex items-center justify-between pb-4 border-b border-gray-100">
                        <div>
                            <h3 class="text-xl font-bold text-[#1D1D1F]">Status Laporan Kerusakan</h3>
                            <p class="text-xs text-[#86868B] mt-0.5">Penanganan fasilitas aktif oleh tim sarpras</p>
                        </div>
                        <a href="{{ url('/user/report-history') }}" class="text-sm font-semibold text-[#1D1D1F] hover:text-blue-600 flex items-center gap-1 transition">
                            <span>Lihat Semua ({{ $totalDamageReports }})</span>
                            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                        </a>
                    </div>

                    <div class="space-y-4">
                        @forelse($activeDamageReports->take(2) as $report)
                            <a href="{{ url('/user/report-history') }}" class="bg-[#F9F9FB] rounded-2xl p-5 sm:p-6 border border-transparent hover:border-gray-200/80 hover:bg-white hover:shadow-[0_8px_24px_rgba(0,0,0,0.05)] transition-all duration-300 cursor-pointer group space-y-3 block">
                                <div class="flex items-start justify-between gap-4">
                                    <h4 class="font-bold text-[#1D1D1F] text-base group-hover:text-blue-600 transition leading-snug">
                                        {{ $report->title ?? 'Laporan Kerusakan' }}
                                    </h4>
                                    {{-- Status Opsi B: Pure Typographic --}}
                                    <div class="shrink-0 pt-0.5">
                                        @if ($report->status === 'baru')
                                            <span class="text-xs font-semibold tracking-wide text-amber-800">Laporan Baru</span>
                                        @elseif ($report->status === 'diproses')
                                            <span class="text-xs font-semibold tracking-wide text-sky-800">Sedang Ditangani</span>
                                        @elseif ($report->status === 'selesai')
                                            <span class="text-xs font-semibold tracking-wide text-emerald-800">Selesai Diperbaiki</span>
                                        @elseif ($report->status === 'ditolak')
                                            <span class="text-xs font-semibold tracking-wide text-rose-700">Ditolak</span>
                                        @else
                                            <span class="text-xs font-semibold tracking-wide text-slate-600 capitalize">{{ $report->status }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-[#86868B] pt-1 border-t border-gray-100/70">
                                    <span>{{ $report->facility->name ?? 'Fasilitas' }} ({{ $report->facility->building ?? 'Gedung' }})</span>
                                    <span>•</span>
                                    <span>Diperbarui {{ $report->updated_at->diffForHumans() }}</span>
                                </div>
                            </a>
                        @empty
                            <div class="py-10 text-center text-sm text-[#86868B] rounded-2xl border border-dashed border-gray-200 bg-[#F9F9FB]">
                                Tidak ada laporan kerusakan fasilitas yang sedang aktif.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="pt-2">
                    <a href="{{ url('/user/report-form') }}" class="w-full py-3.5 rounded-xl bg-[#F9F9FB] hover:bg-gray-100 text-[#1D1D1F] text-sm font-semibold transition flex items-center justify-center gap-2 border border-gray-200/60">
                        <span>Laporkan Kerusakan Baru</span>
                        <span class="material-symbols-outlined text-[18px]">arrow_forward</span>
                    </a>
                </div>
            </section>

        </div>

    </div>
</x-app-layout>
