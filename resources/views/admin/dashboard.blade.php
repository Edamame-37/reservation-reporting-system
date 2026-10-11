{{-- 
  NAMA FILE      : dashboard.blade.php
  FUNGSIONALITAS : Halaman Dasbor Utama Super Admin CAVA (Desain Apple Bento Minimalis)
  DESKRIPSI      : Menampilkan ringkasan metrik eksekutif tata kelola kampus (Verifikasi User Pending, Total Fasilitas, Okupansi Rata-rata, Tiket Kerusakan), antrean otorisasi sivitas baru, pratinjau master fasilitas, dan utilisasi gedung.
  CARA KERJA     : Memanfaatkan layout <x-admin-layout active="dashboard">, menyajikan monitoring sentral administrasi sistem yang lapang, elegan, dan bebas dari AI slop.
--}}

<x-admin-layout title="Dasbor Super Admin" active="dashboard">
    <div class="space-y-8 sm:space-y-10">

        {{-- Flash Session Sukses (Soft Apple Tone) --}}
        @if(session('success'))
            <div class="p-6 rounded-[2rem] bg-emerald-50/70 border border-emerald-100 text-emerald-900 flex items-center gap-4">
                <span class="material-symbols-outlined text-emerald-600 text-[24px] shrink-0">check_circle</span>
                <div>
                    <h4 class="text-sm font-bold">Aksi Berhasil Dieksekusi</h4>
                    <p class="text-xs text-emerald-700 mt-0.5">{{ session('success') }}</p>
                </div>
            </div>
        @endif

        {{-- Flash Session Error / Peringatan Validasi --}}
        @if(session('error') || (isset($errors) && $errors->any()))
            <div class="p-6 rounded-[2rem] bg-rose-50/70 border border-rose-100 text-rose-900 flex items-start gap-4">
                <span class="material-symbols-outlined text-rose-600 text-[24px] shrink-0 mt-0.5">error</span>
                <div class="space-y-1">
                    <h4 class="text-sm font-bold">Terjadi Kendala pada Tindakan Sistem</h4>
                    <p class="text-xs text-rose-700">{{ session('error') ?? (isset($errors) ? $errors->first() : '') }}</p>
                </div>
            </div>
        @endif

        {{-- Header Dasbor Eksekutif --}}
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <span class="text-xs font-medium uppercase tracking-wider text-[#86868B] block">Biro Sarana Prasarana & TIK Universitas</span>
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-[#1D1D1F] mt-1 font-display">Dasbor Eksekutif</h1>
                <p class="text-sm text-[#86868B] mt-1.5 max-w-2xl leading-relaxed">Pemantauan sentral aktivitas peminjaman ruang, validasi pendaftar sivitas akademik, dan ketersediaan sarana kampus.</p>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('admin.user-management') }}" class="inline-flex items-center justify-center px-5 py-2.5 rounded-xl bg-[#1D1D1F] text-white text-sm font-medium hover:bg-black transition shadow-sm active:scale-[0.98]">
                    Tinjau Antrean Akun
                </a>
            </div>
        </div>

        {{-- Bento Grid Baris Atas: 4 Kartu Metrik Apple Bersih & Lapang --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            {{-- Metrik 1: Antrean Verifikasi --}}
            <a href="{{ route('admin.user-management', ['search' => '']) }}" 
               class="bg-white rounded-[2rem] p-7 sm:p-8 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)] hover:border-gray-300 transition block">
                <span class="text-xs text-[#86868B] font-medium block">Antrean Akun Pending</span>
                <span class="text-3xl sm:text-4xl font-extrabold tracking-tight text-[#1D1D1F] mt-3 block">{{ $pendingUsersCount }}</span>
                <span class="text-[11px] text-amber-800 font-medium mt-3 block">+{{ $pendingUsersToday }} pendaftar baru hari ini</span>
            </a>

            {{-- Metrik 2: Master Fasilitas --}}
            <a href="{{ route('admin.facility-master') }}" 
               class="bg-white rounded-[2rem] p-7 sm:p-8 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)] hover:border-gray-300 transition block">
                <span class="text-xs text-[#86868B] font-medium block">Master Fasilitas Kampus</span>
                <span class="text-3xl sm:text-4xl font-extrabold tracking-tight text-[#1D1D1F] mt-3 block">{{ $totalFacilities }}</span>
                <span class="text-[11px] text-[#86868B] mt-3 block">{{ $activeFacilities }} aktif • {{ $maintenanceFacilities }} pemeliharaan</span>
            </a>

            {{-- Metrik 3: Okupansi --}}
            <a href="{{ route('admin.export-report') }}" 
               class="bg-white rounded-[2rem] p-7 sm:p-8 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)] hover:border-gray-300 transition block">
                <span class="text-xs text-[#86868B] font-medium block">Tingkat Okupansi Ruang</span>
                <span class="text-3xl sm:text-4xl font-extrabold tracking-tight text-[#1D1D1F] mt-3 block">{{ $occupancyRate }}%</span>
                <span class="text-[11px] text-emerald-800 font-medium mt-3 block">{{ $occupancyRate >= 70 ? 'Penggunaan sangat optimal' : 'Peminjaman ruang terjadwal' }}</span>
            </a>

            {{-- Metrik 4: Tiket Kerusakan --}}
            <div class="bg-white rounded-[2rem] p-7 sm:p-8 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)] hover:border-gray-300 transition">
                <span class="text-xs text-[#86868B] font-medium block">Tiket Kerusakan Terbuka</span>
                <span class="text-3xl sm:text-4xl font-extrabold tracking-tight text-[#1D1D1F] mt-3 block">{{ $activeDamageReports }}</span>
                <span class="text-[11px] text-[#86868B] mt-3 block">{{ $urgentDamageCount }} prioritas baru • SLA {{ $slaResolutionPercent }}%</span>
            </div>
        </div>

        {{-- Bento Grid Baris Kedua: Antrean Verifikasi Sivitas & Ringkasan Kesiapan Fasilitas --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 items-start">
            
            {{-- Kolom Kiri: Antrean Verifikasi Pendaftar Sivitas --}}
            <div class="lg:col-span-7 bg-white rounded-[2rem] p-7 sm:p-8 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                <div class="flex items-center justify-between pb-5 mb-5 border-b border-gray-100">
                    <div>
                        <h3 class="text-base font-bold text-[#1D1D1F]">Pendaftar Mandiri Menunggu Otorisasi</h3>
                        <p class="text-xs text-[#86868B] mt-0.5">Menampilkan {{ $pendingUsersPreview->count() }} dari total {{ $pendingUsersCount }} calon pengguna yang menunggu evaluasi.</p>
                    </div>
                    <a href="{{ route('admin.user-management') }}" class="text-xs font-semibold text-[#1D1D1F] hover:underline transition">
                        Kelola Seluruh Akun ({{ $totalUsersCount }}) &rarr;
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse($pendingUsersPreview as $user)
                        <div class="py-4 px-5 rounded-2xl bg-[#FBFBFD] border border-gray-100/80 flex items-center justify-between gap-4 hover:border-gray-200 transition">
                            <div class="min-w-0">
                                <div class="text-sm font-bold text-[#1D1D1F] truncate">{{ $user->name }}</div>
                                <div class="text-xs text-[#86868B] mt-0.5">
                                    {{ ucfirst($user->role ?? 'Sivitas') }} • {{ $user->department ?? 'Civitas' }} • <span class="font-mono text-[11px]">{{ $user->identity_number ?? '-' }}</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <form action="{{ route('admin.users.verify', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Verifikasi dan aktifkan akun {{ addslashes($user->name) }}?')">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 rounded-xl bg-[#1D1D1F] text-white text-xs font-medium hover:bg-black transition shadow-sm cursor-pointer">
                                        Setujui
                                    </button>
                                </form>
                                <a href="{{ route('admin.user-management', ['search' => $user->email]) }}" class="px-3 py-2 text-xs font-medium text-[#86868B] hover:text-rose-600 transition cursor-pointer">
                                    Tolak
                                </a>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center text-[#86868B]">
                            <span class="material-symbols-outlined text-gray-400 text-[32px] block mb-2">check_circle</span>
                            <p class="text-xs font-semibold text-[#1D1D1F]">Tidak ada antrean verifikasi akun baru saat ini.</p>
                            <p class="text-[11px] text-[#86868B] mt-0.5">Seluruh permohonan pendaftaran sivitas telah selesai dievaluasi.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Kolom Kanan: Ringkasan Master Fasilitas & Kesiapan Operasional --}}
            <div class="lg:col-span-5 bg-white rounded-[2rem] p-7 sm:p-8 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)] flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-5 mb-5 border-b border-gray-100">
                        <div>
                            <h3 class="text-base font-bold text-[#1D1D1F]">Ringkasan Master Fasilitas</h3>
                            <p class="text-xs text-[#86868B] mt-0.5">{{ $facilitiesPreview->count() }} dari {{ $totalFacilities }} ruang terdaftar.</p>
                        </div>
                        <a href="{{ route('admin.facility-master') }}" class="text-xs font-semibold text-[#1D1D1F] hover:underline transition">
                            Katalog Master &rarr;
                        </a>
                    </div>

                    <div class="space-y-3">
                        @forelse($facilitiesPreview as $facility)
                            <div class="py-3 px-4 rounded-2xl bg-[#FBFBFD] border border-gray-100/80 flex items-center justify-between gap-3">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2">
                                        <span class="font-bold text-sm text-[#1D1D1F] truncate">{{ $facility->name }}</span>
                                        <span class="text-[10px] font-mono text-[#86868B]">{{ $facility->code }}</span>
                                    </div>
                                    <p class="text-xs text-[#86868B] mt-0.5">{{ $facility->building }} • {{ $facility->capacity }} Kursi</p>
                                </div>
                                <span class="text-xs font-semibold shrink-0 {{ $facility->status === 'aktif' ? 'text-emerald-800' : 'text-amber-800' }}">
                                    {{ $facility->status === 'aktif' ? 'Tersedia' : 'Pemeliharaan' }}
                                </span>
                            </div>
                        @empty
                            <div class="py-8 text-center text-[#86868B]">
                                <p class="text-xs">Belum ada data fasilitas terdaftar dalam sistem.</p>
                            </div>
                        @endforelse
                    </div>
                </div>

                {{-- Action Footer Link --}}
                <div class="mt-6 pt-5 border-t border-gray-100 flex items-center justify-between text-xs">
                    <span class="text-[#86868B]">Total Kapasitas: <strong class="text-[#1D1D1F]">{{ number_format($totalCapacitySeats) }} Kursi</strong></span>
                    <a href="{{ route('admin.export-report') }}" class="text-[#1D1D1F] font-semibold hover:underline">
                        Lihat Laporan & Ekspor &rarr;
                    </a>
                </div>
            </div>

        </div>

    </div>
</x-admin-layout>
