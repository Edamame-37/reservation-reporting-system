{{-- 
  NAMA FILE      : information.blade.php
  FUNGSIONALITAS : Pusat Informasi Publik CAVA (Kebijakan, Syarat & Ketentuan, dan Bantuan) - Bento UI Modern
  DESKRIPSI      : Menyajikan dokumentasi resmi tata tertib peminjaman fasilitas kampus, kebijakan privasi dan operasional sarpras, panduan alur, FAQ ringkas, serta kontak resmi helpdesk dengan tata letak Bento UI modern.
  CARA KERJA     : Memanfaatkan master layout <x-public-layout active="informasi">, menyediakan navigasi tab dinamis berbasis Alpine.js dengan tampilan bersih dan terpadu.
--}}

<x-public-layout title="Pusat Informasi & Bantuan" active="informasi">
    <!-- 
      ELEMEN       : Bento Hero Banner Pusat Informasi CAVA
      KEGUNAAN     : Menyambut sivitas dengan tajuk resmi pusat regulasi dan layanan sarpras.
    -->
    <section class="bg-slate-900 border border-slate-800 rounded-3xl p-6 sm:p-8 text-white shadow-xs mb-6">
        <div class="max-w-3xl">
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 text-blue-200 text-xs font-semibold mb-3">
                <span class="material-symbols-outlined text-[14px] text-blue-300">info</span>
                <span>Pusat Informasi & Regulasi Sarpras CAVA</span>
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold tracking-tight text-white mb-2">
                Kebijakan, Ketentuan & Bantuan Layanan
            </h1>
            <p class="text-xs sm:text-sm text-slate-300 font-normal leading-relaxed">
                Informasi resmi tata kelola pemanfaatan fasilitas kampus, perlindungan data sivitas, tata tertib peminjaman ruang, serta layanan bantuan Biro Sarana & Prasarana.
            </p>
        </div>
    </section>

    <!-- 
      ELEMEN       : Wadah Navigasi Tab Interaktif (Bento Tabs)
      KEGUNAAN     : Memungkinkan pengguna beralih antara Kebijakan, Syarat & Ketentuan, dan Bantuan secara instan.
      CARA KERJA   : Menyimpan status aktif pada variabel 'activeTab' dan memperbarui URL browser dengan History API tanpa reload.
    -->
    <div x-data="{ 
        activeTab: '{{ $activeSection }}',
        switchTab(tab) {
            this.activeTab = tab;
            if (window.history && window.history.replaceState) {
                window.history.replaceState(null, '', '{{ url('/informasi') }}/' + tab);
            }
        }
    }" class="space-y-6">

        {{-- Navigasi Tab Bar Bento --}}
        <div class="bg-white p-2 rounded-3xl border border-slate-200/80 shadow-xs">
            <div class="grid grid-cols-3 gap-2 w-full">
                {{-- Tab 1: Kebijakan --}}
                <button 
                    type="button" 
                    @click="switchTab('kebijakan')"
                    :class="activeTab === 'kebijakan' 
                        ? 'bg-slate-900 text-white shadow-xs' 
                        : 'bg-transparent text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                    class="flex items-center justify-center gap-2 px-3 py-2.5 rounded-2xl text-xs sm:text-sm font-semibold transition-all">
                    <span class="material-symbols-outlined text-[18px]">shield</span>
                    <span class="truncate">Kebijakan Privasi</span>
                </button>

                {{-- Tab 2: Syarat & Ketentuan --}}
                <button 
                    type="button" 
                    @click="switchTab('syarat-ketentuan')"
                    :class="activeTab === 'syarat-ketentuan' 
                        ? 'bg-slate-900 text-white shadow-xs' 
                        : 'bg-transparent text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                    class="flex items-center justify-center gap-2 px-3 py-2.5 rounded-2xl text-xs sm:text-sm font-semibold transition-all">
                    <span class="material-symbols-outlined text-[18px]">gavel</span>
                    <span class="truncate">Syarat & Ketentuan</span>
                </button>

                {{-- Tab 3: Bantuan & FAQ --}}
                <button 
                    type="button" 
                    @click="switchTab('bantuan')"
                    :class="activeTab === 'bantuan' 
                        ? 'bg-slate-900 text-white shadow-xs' 
                        : 'bg-transparent text-slate-600 hover:text-slate-900 hover:bg-slate-50'"
                    class="flex items-center justify-center gap-2 px-3 py-2.5 rounded-2xl text-xs sm:text-sm font-semibold transition-all">
                    <span class="material-symbols-outlined text-[18px]">help</span>
                    <span class="truncate">Pusat Bantuan & FAQ</span>
                </button>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- TAB 1: KEBIJAKAN PRIVASI (BENTO CONTAINER)                                --}}
        {{-- ========================================================================= --}}
        <div x-show="activeTab === 'kebijakan'" x-cloak class="space-y-6">
            <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs">
                {{-- Header Kebijakan --}}
                <div class="pb-6 border-b border-slate-100">
                    <h2 class="text-lg sm:text-xl font-bold text-slate-900">Kebijakan Privasi & Operasional Fasilitas Kampus</h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1 leading-relaxed">
                        Dokumen kebijakan resmi yang mengatur perlindungan data identitas sivitas, kerahasiaan pelapor kerusakan fasilitas, serta akuntabilitas aset Barang Milik Negara (BMN) di lingkungan universitas.
                    </p>
                </div>

                {{-- Daftar Poin Kebijakan Terpadu --}}
                <div class="divide-y divide-slate-100">
                    {{-- Poin 1 --}}
                    <div class="py-5">
                        <div class="flex items-center gap-2.5 mb-2">
                            <span class="w-6 h-6 rounded-lg bg-slate-900 text-white flex items-center justify-center text-xs font-bold shrink-0 font-mono">1</span>
                            <h3 class="text-sm sm:text-base font-bold text-slate-800">Perlindungan & Privasi Data Pribadi</h3>
                        </div>
                        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed pl-8">
                            Data identitas sivitas (NIM/NIP, alamat email kampus, program studi, dan nomor telepon seluler) yang terekam dalam portal CAVA digunakan secara eksklusif untuk keperluan verifikasi administrasi perizinan peminjaman ruang. Data ini tersimpan aman dan tidak pernah dibagikan kepada pihak luar tanpa izin.
                        </p>
                    </div>

                    {{-- Poin 2 --}}
                    <div class="py-5">
                        <div class="flex items-center gap-2.5 mb-2">
                            <span class="w-6 h-6 rounded-lg bg-slate-900 text-white flex items-center justify-center text-xs font-bold shrink-0 font-mono">2</span>
                            <h3 class="text-sm sm:text-base font-bold text-slate-800">Kerahasiaan Identitas Pelapor Kerusakan</h3>
                        </div>
                        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed pl-8">
                            Setiap sivitas yang menyampaikan keluhan atau laporan kerusakan fasilitas kampus dilindungi kerahasiaan identitasnya dari potensi intimidasi. Laporan hanya diproses secara tertutup oleh Petugas Sarpras terkait dan tim teknisi lapangan untuk kebutuhan perbaikan fisik fasilitas.
                        </p>
                    </div>

                    {{-- Poin 3 --}}
                    <div class="py-5">
                        <div class="flex items-center gap-2.5 mb-2">
                            <span class="w-6 h-6 rounded-lg bg-slate-900 text-white flex items-center justify-center text-xs font-bold shrink-0 font-mono">3</span>
                            <h3 class="text-sm sm:text-base font-bold text-slate-800">Hak Pembatalan Darurat (Force Majeure)</h3>
                        </div>
                        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed pl-8">
                            Biro Sarana & Prasarana Kampus serta pimpinan universitas memiliki hak untuk membatalkan atau merelokasi permohonan reservasi yang telah disetujui, apabila fasilitas tersebut mendesak dibutuhkan untuk agenda resmi kenegaraan, sidang darurat universitas, atau perbaikan darurat kebencanaan.
                        </p>
                    </div>

                    {{-- Poin 4 --}}
                    <div class="py-5">
                        <div class="flex items-center gap-2.5 mb-2">
                            <span class="w-6 h-6 rounded-lg bg-slate-900 text-white flex items-center justify-center text-xs font-bold shrink-0 font-mono">4</span>
                            <h3 class="text-sm sm:text-base font-bold text-slate-800">Mode Pemeliharaan Fasilitas</h3>
                        </div>
                        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed pl-8">
                            Fasilitas yang mengalami gangguan teknis berat akan langsung dialihkan statusnya ke mode pemeliharaan (maintenance) oleh petugas zona. Selama masa perbaikan berlangsung, slot jadwal ruangan terkait tidak dapat dipesan di katalog publik demi keselamatan pengguna.
                        </p>
                    </div>

                    {{-- Poin 5 --}}
                    <div class="pt-5">
                        <div class="flex items-center gap-2.5 mb-2">
                            <span class="w-6 h-6 rounded-lg bg-slate-900 text-white flex items-center justify-center text-xs font-bold shrink-0 font-mono">5</span>
                            <h3 class="text-sm sm:text-base font-bold text-slate-800">Pencatatan Riwayat & Akuntabilitas Aset</h3>
                        </div>
                        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed pl-8">
                            Seluruh pengajuan reservasi, verifikasi petugas, pembatalan mandiri, dan tiket laporan kerusakan tersimpan permanen dalam riwayat sistem. Catatan ini berfungsi sebagai dokumen rekapitulasi audit pemanfaatan aset Barang Milik Negara (BMN) di lingkungan universitas.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- TAB 2: SYARAT & KETENTUAN (BENTO CONTAINER)                               --}}
        {{-- ========================================================================= --}}
        <div x-show="activeTab === 'syarat-ketentuan'" x-cloak class="space-y-6">
            <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs">
                {{-- Header Syarat & Ketentuan --}}
                <div class="pb-6 border-b border-slate-100">
                    <h2 class="text-lg sm:text-xl font-bold text-slate-900">Syarat & Ketentuan Peminjaman Ruang dan Pelaporan</h2>
                    <p class="text-xs sm:text-sm text-slate-500 mt-1 leading-relaxed">
                        Tata tertib operasional yang wajib dipahami dan dipatuhi oleh seluruh sivitas akademika dalam memanfaatkan fasilitas bersama.
                    </p>
                </div>

                {{-- 4 Bagian Syarat & Ketentuan --}}
                <div class="divide-y divide-slate-100">
                    {{-- Bagian 1 --}}
                    <div class="py-5">
                        <div class="flex items-center gap-2.5 mb-2">
                            <span class="w-6 h-6 rounded-lg bg-slate-900 text-white flex items-center justify-center text-xs font-bold shrink-0 font-mono">1</span>
                            <h3 class="text-sm sm:text-base font-bold text-slate-800">Kelayakan Akun Pengguna</h3>
                        </div>
                        <ul class="space-y-1.5 text-xs sm:text-sm text-slate-600 list-disc list-inside pl-8">
                            <li>Pemohon harus merupakan mahasiswa aktif, dosen, atau staf kependidikan universitas yang terdaftar.</li>
                            <li>Akun pendaftaran mandiri wajib berstatus aktif setelah diverifikasi oleh Administrator Sarpras.</li>
                            <li>Akun bersifat pribadi dan dilarang dipinjamkan atau dialihkan kepada pihak lain di luar kampus.</li>
                        </ul>
                    </div>

                    {{-- Bagian 2 --}}
                    <div class="py-5">
                        <div class="flex items-center gap-2.5 mb-2">
                            <span class="w-6 h-6 rounded-lg bg-slate-900 text-white flex items-center justify-center text-xs font-bold shrink-0 font-mono">2</span>
                            <h3 class="text-sm sm:text-base font-bold text-slate-800">Batas Waktu & Slot Reservasi</h3>
                        </div>
                        <ul class="space-y-1.5 text-xs sm:text-sm text-slate-600 list-disc list-inside pl-8">
                            <li>Pengajuan reservasi wajib diajukan paling lambat <strong>H-2 (48 jam)</strong> sebelum waktu pelaksanaan kegiatan.</li>
                            <li>Waktu reservasi maksimal yang dapat dijadwalkan adalah <strong>30 hari ke depan</strong> dari tanggal pengajuan.</li>
                            <li>Durasi pemakaian menggunakan interval <strong>slot 30 menit</strong> pada rentang operasional resmi (07:00 – 20:00 WIB).</li>
                            <li>Ruangan baru dapat digunakan setelah memperoleh persetujuan resmi dari Petugas Sarpras zona terkait.</li>
                        </ul>
                    </div>

                    {{-- Bagian 3 --}}
                    <div class="py-5">
                        <div class="flex items-center gap-2.5 mb-2">
                            <span class="w-6 h-6 rounded-lg bg-slate-900 text-white flex items-center justify-center text-xs font-bold shrink-0 font-mono">3</span>
                            <h3 class="text-sm sm:text-base font-bold text-slate-800">Tata Tertib Penggunaan Ruangan</h3>
                        </div>
                        <ul class="space-y-1.5 text-xs sm:text-sm text-slate-600 list-disc list-inside pl-8">
                            <li>Peminjam wajib menjaga kebersihan ruangan dan mengembalikan susunan kursi atau meja seperti posisi semula.</li>
                            <li>Wajib mematikan AC, lampu penerangan, dan proyektor sebelum meninggalkan ruangan.</li>
                            <li>Dilarang merokok dan dilarang mengonsumsi makanan berat di dalam laboratorium komputer dan smart classroom.</li>
                            <li>Dilarang memindahkan inventaris tetap atau mengubah instalasi jaringan kabel tanpa pendampingan teknisi resmi.</li>
                        </ul>
                    </div>

                    {{-- Bagian 4 --}}
                    <div class="pt-5">
                        <div class="flex items-center gap-2.5 mb-2">
                            <span class="w-6 h-6 rounded-lg bg-slate-900 text-white flex items-center justify-center text-xs font-bold shrink-0 font-mono">4</span>
                            <h3 class="text-sm sm:text-base font-bold text-slate-800">Ketentuan Pelaporan Kerusakan Aset</h3>
                        </div>
                        <ul class="space-y-1.5 text-xs sm:text-sm text-slate-600 list-disc list-inside pl-8">
                            <li>Pelapor wajib menyertakan foto kondisi fisik aset yang rusak dan keterangan lokasi yang jelas.</li>
                            <li>Dilarang membuat laporan keluhan palsu atau informasi yang tidak dapat dipertanggungjawabkan.</li>
                            <li>Kerusakan yang terjadi saat kegiatan berlangsung wajib segera dilaporkan agar dapat ditangani teknisi sarpras dalam &lt; 24 jam.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- TAB 3: BANTUAN & FAQ (BENTO CONTAINER)                                    --}}
        {{-- ========================================================================= --}}
        <div x-show="activeTab === 'bantuan'" x-cloak class="space-y-6">
            {{-- Panduan Alur 4 Langkah --}}
            <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs">
                <h2 class="text-base sm:text-lg font-bold text-slate-900 mb-1">Tata Cara Peminjaman Ruangan</h2>
                <p class="text-xs sm:text-sm text-slate-500 mb-6">Alur mudah penggunaan fasilitas kampus dari pengecekan hingga pemakaian.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 flex flex-col justify-between">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-blue-900">Langkah 1</span>
                            <h3 class="text-sm font-bold text-slate-800 mt-1">Cek Ketersediaan</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">Lihat slot waktu yang masih kosong pada menu Matriks Jadwal.</p>
                        </div>
                        <a href="{{ url('/public/availability') }}" class="mt-3 text-xs font-semibold text-blue-900 hover:underline inline-flex items-center gap-1">
                            <span>Buka Jadwal</span>
                            <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                        </a>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 flex flex-col justify-between">
                        <div>
                            <span class="text-[11px] font-bold uppercase tracking-wider text-blue-900">Langkah 2</span>
                            <h3 class="text-sm font-bold text-slate-800 mt-1">Masuk ke Akun</h3>
                            <p class="text-xs text-slate-500 mt-1 leading-relaxed">Gunakan email sivitas terdaftar yang telah berstatus aktif.</p>
                        </div>
                        <a href="{{ route('login') }}" class="mt-3 text-xs font-semibold text-blue-900 hover:underline inline-flex items-center gap-1">
                            <span>Masuk Akun</span>
                            <span class="material-symbols-outlined text-[14px]">arrow_forward</span>
                        </a>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-blue-900">Langkah 3</span>
                        <h3 class="text-sm font-bold text-slate-800 mt-1">Ajukan Reservasi</h3>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">Pilih ruangan, tentukan tanggal, rentang jam, dan tujuan kegiatan.</p>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100">
                        <span class="text-[11px] font-bold uppercase tracking-wider text-blue-900">Langkah 4</span>
                        <h3 class="text-sm font-bold text-slate-800 mt-1">Persetujuan Petugas</h3>
                        <p class="text-xs text-slate-500 mt-1 leading-relaxed">Petugas meninjau permohonan dan menyetujui pemakaian ruangan.</p>
                    </div>
                </div>
            </div>

            {{-- Accordion FAQ Bento --}}
            <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs">
                <h2 class="text-base sm:text-lg font-bold text-slate-900 mb-1">Pertanyaan yang Sering Diajukan (FAQ)</h2>
                <p class="text-xs sm:text-sm text-slate-500 mb-4">Jawaban ringkas seputar status akun dan peminjaman fasilitas.</p>

                <div x-data="{ openFaq: null }" class="divide-y divide-slate-100">
                    {{-- FAQ 1 --}}
                    <div class="py-3">
                        <button 
                            type="button" 
                            @click="openFaq = (openFaq === 1 ? null : 1)" 
                            class="w-full text-left flex items-center justify-between gap-4 py-1 text-xs sm:text-sm font-semibold text-slate-800 hover:text-blue-900 transition">
                            <span>Mengapa status akun saya masih "Pending" dan belum bisa login?</span>
                            <span class="material-symbols-outlined text-[18px] text-slate-400 transition-transform duration-200" :class="openFaq === 1 ? 'rotate-180 text-blue-900' : ''">expand_more</span>
                        </button>
                        <div x-show="openFaq === 1" x-cloak class="mt-2 text-xs text-slate-600 leading-relaxed pr-6">
                            Akun pendaftaran mandiri memerlukan verifikasi identitas (NIM/NIP dan departemen) oleh Administrator Sarpras demi keamanan akses. Proses verifikasi biasanya membutuhkan waktu maksimal 1x24 jam kerja.
                        </div>
                    </div>

                    {{-- FAQ 2 --}}
                    <div class="py-3">
                        <button 
                            type="button" 
                            @click="openFaq = (openFaq === 2 ? null : 2)" 
                            class="w-full text-left flex items-center justify-between gap-4 py-1 text-xs sm:text-sm font-semibold text-slate-800 hover:text-blue-900 transition">
                            <span>Mengapa slot waktu yang ingin saya pilih berwarna merah atau terkunci?</span>
                            <span class="material-symbols-outlined text-[18px] text-slate-400 transition-transform duration-200" :class="openFaq === 2 ? 'rotate-180 text-blue-900' : ''">expand_more</span>
                        </button>
                        <div x-show="openFaq === 2" x-cloak class="mt-2 text-xs text-slate-600 leading-relaxed pr-6">
                            Slot tersebut sudah disetujui untuk pemohon kegiatan lain, atau ruangan sedang dalam Mode Pemeliharaan (*Maintenance Mode*) oleh teknisi Sarpras. Silakan pilih alternatif ruangan atau waktu lain.
                        </div>
                    </div>

                    {{-- FAQ 3 --}}
                    <div class="py-3">
                        <button 
                            type="button" 
                            @click="openFaq = (openFaq === 3 ? null : 3)" 
                            class="w-full text-left flex items-center justify-between gap-4 py-1 text-xs sm:text-sm font-semibold text-slate-800 hover:text-blue-900 transition">
                            <span>Apakah saya dapat membatalkan reservasi yang sudah diajukan?</span>
                            <span class="material-symbols-outlined text-[18px] text-slate-400 transition-transform duration-200" :class="openFaq === 3 ? 'rotate-180 text-blue-900' : ''">expand_more</span>
                        </button>
                        <div x-show="openFaq === 3" x-cloak class="mt-2 text-xs text-slate-600 leading-relaxed pr-6">
                            Bisa. Anda dapat membatalkan reservasi secara mandiri melalui menu Riwayat Reservasi pada portal pengguna sebelum batas minimal H-1 dari jadwal pelaksanaan.
                        </div>
                    </div>

                    {{-- FAQ 4 --}}
                    <div class="py-3">
                        <button 
                            type="button" 
                            @click="openFaq = (openFaq === 4 ? null : 4)" 
                            class="w-full text-left flex items-center justify-between gap-4 py-1 text-xs sm:text-sm font-semibold text-slate-800 hover:text-blue-900 transition">
                            <span>Bagaimana cara melaporkan sarana yang rusak di dalam ruangan?</span>
                            <span class="material-symbols-outlined text-[18px] text-slate-400 transition-transform duration-200" :class="openFaq === 4 ? 'rotate-180 text-blue-900' : ''">expand_more</span>
                        </button>
                        <div x-show="openFaq === 4" x-cloak class="mt-2 text-xs text-slate-600 leading-relaxed pr-6">
                            Masuk ke portal pengguna dan buka menu Pelaporan Kerusakan. Ambil foto kerusakan, cantumkan nama ruangan dan deskripsi singkat, lalu kirimkan laporan agar segera ditinjau dan ditangani teknisi sarpras dalam waktu kurang dari 24 jam.
                        </div>
                    </div>
                </div>
            </div>

            {{-- Kontak Biro Sarpras Bento --}}
            <div class="bg-white rounded-3xl border border-slate-200/80 p-6 sm:p-8 shadow-xs">
                <h2 class="text-base sm:text-lg font-bold text-slate-900 mb-1">Kontak & Layanan Sarpras</h2>
                <p class="text-xs sm:text-sm text-slate-500 mb-5">Hubungi kami jika memerlukan bantuan administrasi atau perizinan kegiatan khusus.</p>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs sm:text-sm">
                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 flex items-start gap-3">
                        <span class="material-symbols-outlined text-slate-600 text-[20px] shrink-0">location_on</span>
                        <div>
                            <span class="font-bold text-slate-800 block">Kantor Layanan</span>
                            <span class="text-slate-600 text-xs">Gedung Rektorat Sayap Timur, Lantai 1, Ruang Biro Sarpras</span>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 flex items-start gap-3">
                        <span class="material-symbols-outlined text-slate-600 text-[20px] shrink-0">schedule</span>
                        <div>
                            <span class="font-bold text-slate-800 block">Jam Operasional</span>
                            <span class="text-slate-600 text-xs">Senin – Jumat, pukul 08:00 – 16:00 WIB</span>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 flex items-start gap-3">
                        <span class="material-symbols-outlined text-slate-600 text-[20px] shrink-0">call</span>
                        <div>
                            <span class="font-bold text-slate-800 block">WhatsApp Helpdesk</span>
                            <a href="https://wa.me/6281122334455" target="_blank" rel="noopener noreferrer" class="text-blue-900 hover:underline font-medium text-xs">
                                +62 811-2233-4455
                            </a>
                        </div>
                    </div>

                    <div class="p-4 rounded-2xl bg-slate-50 border border-slate-100 flex items-start gap-3">
                        <span class="material-symbols-outlined text-slate-600 text-[20px] shrink-0">mail</span>
                        <div>
                            <span class="font-bold text-slate-800 block">Surel Resmi</span>
                            <a href="mailto:sarpras@kampus.ac.id" class="text-blue-900 hover:underline font-medium text-xs">
                                sarpras@kampus.ac.id
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
</x-public-layout>
