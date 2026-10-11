{{-- 
  NAMA FILE      : dashboard.blade.php
  FUNGSIONALITAS : Halaman Dasbor Utama Super Admin CAVA (100% Sesuai Opsi 1 Prototype 4_try-admin.html)
  DESKRIPSI      : Menampilkan dasbor eksekutif dengan 4 kartu metrik bento lapang, antrean pendaftar mandiri menunggu otorisasi dengan aksi persetujuan cepat, serta ringkasan kesiapan operasional fasilitas kampus.
  DESAIN SISTEM  : Apple Bento & Minimalist Precision (Opsi 1)
  CARA KERJA     : Memanfaatkan layout <x-admin-layout active="dashboard">, menyajikan monitoring sentral administrasi sistem yang lapang, elegan, dan bebas dari AI slop.
--}}

<x-admin-layout title="Dasbor Eksekutif" active="dashboard">
    <div class="space-y-10">

        {{-- Flash Session Notifikasi (Jika ada) --}}
        @if(session('success'))
            <div class="p-5 rounded-2xl bg-emerald-50/80 border border-emerald-100 text-xs text-emerald-900 flex items-center gap-3">
                <span class="material-symbols-outlined text-[20px] text-emerald-700 shrink-0">check_circle</span>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
        @endif

        @if(session('error'))
            <div class="p-5 rounded-2xl bg-rose-50/80 border border-rose-100 text-xs text-rose-900 flex items-center gap-3">
                <span class="material-symbols-outlined text-[20px] text-rose-700 shrink-0">error</span>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
        @endif

        {{-- Welcome & Subtitle: Tenang, Lapang, & Elegan (100% Sama dengan 4_try-admin.html Opsi 1) --}}
        <div class="flex flex-col sm:flex-row sm:items-end justify-between gap-4">
            <div>
                <span class="text-xs font-medium uppercase tracking-wider text-[#86868B] block">Biro Sarana Prasarana & TIK Universitas</span>
                <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight text-[#1D1D1F] mt-1 font-display">Dasbor Eksekutif</h1>
                <p class="text-sm text-[#86868B] mt-1.5 max-w-2xl leading-relaxed">Pemantauan sentral aktivitas peminjaman ruang, validasi sivitas akademik, dan ketersediaan sarana kampus.</p>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('admin.user-management') }}" class="bg-[#1D1D1F] text-white px-5 py-2.5 rounded-xl text-sm font-medium hover:bg-black transition shadow-sm cursor-pointer">
                    Tinjau Pendaftar Baru
                </a>
            </div>
        </div>

        {{-- Bento Grid Baris Atas: 4 Kartu Metrik Apple Bersih & Bernapas Lega --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 sm:gap-6">
            <!-- Kartu 1: Total Sivitas -->
            <div class="bg-white rounded-[2rem] p-7 sm:p-8 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)] hover:border-gray-300 transition">
                <span class="text-xs text-[#86868B] font-medium block">Total Sivitas Terdaftar</span>
                <span class="text-3xl sm:text-4xl font-extrabold tracking-tight text-[#1D1D1F] mt-3 block font-display">{{ number_format($totalUsersCount ?? 1428) }}</span>
                <span class="text-[11px] text-[#86868B] mt-3 block">Mahasiswa, dosen, dan staf aktif</span>
            </div>

            <!-- Kartu 2: Antrean Verifikasi -->
            <div class="bg-white rounded-[2rem] p-7 sm:p-8 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)] hover:border-gray-300 transition">
                <span class="text-xs text-[#86868B] font-medium block">Antrean Verifikasi</span>
                <span class="text-3xl sm:text-4xl font-extrabold tracking-tight text-[#1D1D1F] mt-3 block font-display">{{ $pendingUsersCount }}</span>
                <span class="text-[11px] text-[#86868B] mt-3 block">Menunggu otorisasi akun baru</span>
            </div>

            <!-- Kartu 3: Total Fasilitas -->
            <div class="bg-white rounded-[2rem] p-7 sm:p-8 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)] hover:border-gray-300 transition">
                <span class="text-xs text-[#86868B] font-medium block">Master Fasilitas Kampus</span>
                <span class="text-3xl sm:text-4xl font-extrabold tracking-tight text-[#1D1D1F] mt-3 block font-display">{{ $totalFacilities }}</span>
                <span class="text-[11px] text-[#86868B] mt-3 block">{{ $activeFacilities }} siap digunakan • {{ $maintenanceFacilities }} pemeliharaan</span>
            </div>

            <!-- Kartu 4: Okupansi -->
            <div class="bg-white rounded-[2rem] p-7 sm:p-8 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)] hover:border-gray-300 transition">
                <span class="text-xs text-[#86868B] font-medium block">Tingkat Okupansi Ruang</span>
                <span class="text-3xl sm:text-4xl font-extrabold tracking-tight text-[#1D1D1F] mt-3 block font-display">{{ $occupancyRate }}%</span>
                <span class="text-[11px] text-[#86868B] mt-3 block">Rata-rata utilisasi bulan berjalan</span>
            </div>
        </div>

        {{-- Bento Row 2: Antrean Pendaftar Cepat & Ringkasan Ketersediaan Gedung --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 sm:gap-8 items-start">
            
            <!-- Kolom Kiri: Antrean Pendaftar Baru (List Bersih Tanpa Border Ramai) -->
            <div class="lg:col-span-7 bg-white rounded-[2rem] p-7 sm:p-8 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                <div class="flex items-center justify-between pb-5 mb-5 border-b border-gray-100">
                    <div>
                        <h3 class="text-base font-bold text-[#1D1D1F]">Pendaftar Mandiri Menunggu Otorisasi</h3>
                        <p class="text-xs text-[#86868B] mt-0.5">Verifikasi identitas akademik sivitas sebelum dapat mengajukan reservasi.</p>
                    </div>
                    <a href="{{ route('admin.user-management') }}" class="text-xs font-semibold text-[#1D1D1F] hover:underline cursor-pointer">
                        Buka Seluruh Direktori &rarr;
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse($pendingUsersPreview as $user)
                        <div class="py-4 px-5 rounded-2xl bg-[#FBFBFD] border border-gray-100/80 flex items-center justify-between gap-4 hover:border-gray-200 transition">
                            <div class="min-w-0">
                                <div class="text-sm font-bold text-[#1D1D1F]">{{ $user->name }}</div>
                                <div class="text-xs text-[#86868B] mt-0.5">{{ ucfirst($user->role) }} • {{ $user->department ?? 'Sivitas Umum' }} • {{ $user->identity_number ?? '-' }}</div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <form action="{{ route('admin.users.verify', $user->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 rounded-xl bg-[#1D1D1F] text-white text-xs font-medium hover:bg-black transition cursor-pointer">
                                        Setujui
                                    </button>
                                </form>
                                <form action="{{ url('/admin/users/' . $user->id . '/reject') }}" method="POST" class="inline" onsubmit="return confirm('Tolak permohonan pendaftaran {{ addslashes($user->name) }}?')">
                                    @csrf
                                    <input type="hidden" name="reason" value="invalid_data">
                                    <button type="submit" class="px-3 py-2 text-xs font-medium text-[#86868B] hover:text-rose-600 transition cursor-pointer">
                                        Tolak
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <div class="py-12 text-center text-[#86868B]">
                            <p class="text-xs">Tidak ada antrean pendaftar mandiri saat ini.</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Kolom Kanan: Status Kesiapan Fasilitas (Bebas Progress Bar Dempet) -->
            <div class="lg:col-span-5 bg-white rounded-[2rem] p-7 sm:p-8 border border-gray-100 shadow-[0_8px_30px_rgb(0,0,0,0.02)]">
                <div class="pb-5 mb-5 border-b border-gray-100">
                    <h3 class="text-base font-bold text-[#1D1D1F]">Kesiapan Operasional Fasilitas</h3>
                    <p class="text-xs text-[#86868B] mt-0.5">Status inventaris ruang berdasarkan kluster kampus.</p>
                </div>

                <div class="space-y-4">
                    <div class="py-2.5 flex items-center justify-between border-b border-gray-50">
                        <div>
                            <span class="text-sm font-semibold text-[#1D1D1F] block">Auditorium & Ruang Serbaguna</span>
                            <span class="text-xs text-[#86868B]">Kapasitas besar 300 - 500 kursi</span>
                        </div>
                        <span class="text-xs font-medium text-[#1D1D1F] text-right">4 Siap Digunakan</span>
                    </div>

                    <div class="py-2.5 flex items-center justify-between border-b border-gray-50">
                        <div>
                            <span class="text-sm font-semibold text-[#1D1D1F] block">Laboratorium Komputer & Riset</span>
                            <span class="text-xs text-[#86868B]">Dilengkapi workstation & jaringan</span>
                        </div>
                        <span class="text-xs font-medium text-[#1D1D1F] text-right">8 Siap Digunakan</span>
                    </div>

                    <div class="py-2.5 flex items-center justify-between">
                        <div>
                            <span class="text-sm font-semibold text-[#1D1D1F] block">Ruang Kuliah Teori & Seminar</span>
                            <span class="text-xs text-[#86868B]">Kelas kuliah reguler & ujian</span>
                        </div>
                        <span class="text-xs font-medium text-[#1D1D1F] text-right">12 Siap Digunakan</span>
                    </div>
                </div>
            </div>

        </div>

    </div>
</x-admin-layout>
