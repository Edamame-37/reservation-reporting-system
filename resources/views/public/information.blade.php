{{-- 
====================================================================================================
SISTEM INFORMASI RESERVASI & PELAPORAN FASILITAS KAMPUS (CAVA)
MODUL 18: PUSAT INFORMASI PUBLIK, KEBIJAKAN, SYARAT & BANTUAN SIVITAS
BERKAS: resources/views/public/information.blade.php
====================================================================================================

DESKRIPSI BERKAS:
Halaman statuter publik terpadu yang mempublikasikan dokumen kebijakan privasi, transparansi penggunaan
cookies & sesi peramban, syarat & ketentuan peminjaman fasilitas kampus, panduan alur reservasi, serta
prosedur helpdesk pengaduan kerusakan fisik aset kampus. Menggunakan desain Apple-esque Minimalist.

PERAN ARSITEKTURAL DALAM SISTEM CAVA:
1. Akuntabilitas & Kepatuhan Privasi (Privacy Transparency):
   Menyediakan tabel inventarisasi resmi seluruh cookie yang digunakan sistem CAVA, mencakup nama cookie,
   kategori (preferensi, fungsional, esensial, keamanan, otentikasi), masa retensi, dan tujuan operasional.
2. Regulasi Operasional Peminjaman (Statutory Rules):
   Menegaskan batas waktu mutlak Pembatalan Mandiri H-1 (Regulasi BR-USR03-02), jam operasional kampus
   (Senin-Sabtu 07:00-20:00 WIB), sanksi keterlambatan pengosongan ruangan, serta ganti rugi fisik.
3. Fitur Interaktif Scroll Spy (IntersectionObserver API):
   Menggunakan pustaka bawaan peramban via Alpine.js untuk mendeteksi posisi gulir layar pengguna dan
   secara dinamis menyorot item navigasi sidebar yang sedang dibaca tanpa lagging.
====================================================================================================
--}}

{{--
  PEMBUNGKUS MASTER LAYOUT PUBLIK:
  - 'title'  : Judul dokumen regulasi yang tampil pada tab browser.
  - 'active' : Menandai item menu 'information' pada navbar publik.
--}}
<x-public-layout title="Pusat Informasi & Regulasi" active="information">

    {{--
      KOMPONEN REAKTIF ALPINE.JS DENGAN SCROLL SPY:
      - 'activeTab'      : Menyimpan ID seksi konten yang sedang aktif dibaca pengguna (default: 'kebijakan').
      - 'initObserver()' : Menginisialisasi objek JavaScript 'IntersectionObserver' untuk memantau elemen '.info-section'.
      - 'rootMargin'     : Offset vertikal untuk memicu pergantian tab aktif saat seksi berada di viewport tengah.
      - 'x-init="initObserver()"': Menjalankan observer secara otomatis begitu DOM halaman selesai dimuat.
    --}}
    <div class="pt-24 pb-20 max-w-7xl mx-auto px-6" 
         x-data="{ 
            activeTab: 'kebijakan',
            initObserver() {
                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            this.activeTab = entry.target.id;
                        }
                    });
                }, {
                    rootMargin: '-20% 0px -70% 0px',
                    threshold: 0
                });

                document.querySelectorAll('.info-section').forEach(section => {
                    observer.observe(section);
                });
            }
         }"
         x-init="initObserver()">
         
        <div class="flex flex-col md:flex-row gap-12">
            
            {{-- 
              1. SIDEBAR NAVIGASI STATIS / STICKY:
              Menyediakan daftar tautan seksi dengan efek gulir halus (Smooth Scrolling).
            --}}
            <div class="w-full md:w-64 shrink-0">
                <div class="sticky top-28">
                    <h3 class="text-xs font-semibold text-[#86868B] tracking-wider uppercase mb-4">Pusat Informasi</h3>
                    <ul class="space-y-3 text-sm font-medium relative">
                        {{-- Tautan Seksi 1: Kebijakan Privasi --}}
                        <li>
                            <a href="#kebijakan" @click.prevent="document.getElementById('kebijakan').scrollIntoView({behavior: 'smooth'})" 
                               class="transition block cursor-pointer" 
                               :class="activeTab === 'kebijakan' ? 'text-[#1D1D1F] font-semibold' : 'text-[#86868B] hover:text-[#1D1D1F]'">
                                Kebijakan Privasi
                            </a>
                        </li>
                        {{-- Tautan Seksi 2: Syarat & Ketentuan --}}
                        <li>
                            <a href="#syarat" @click.prevent="document.getElementById('syarat').scrollIntoView({behavior: 'smooth'})" 
                               class="transition block cursor-pointer"
                               :class="activeTab === 'syarat' ? 'text-[#1D1D1F] font-semibold' : 'text-[#86868B] hover:text-[#1D1D1F]'">
                                Syarat & Ketentuan
                            </a>
                        </li>
                        {{-- Tautan Seksi 3: Panduan Peminjaman --}}
                        <li>
                            <a href="#panduan" @click.prevent="document.getElementById('panduan').scrollIntoView({behavior: 'smooth'})" 
                               class="transition block cursor-pointer"
                               :class="activeTab === 'panduan' ? 'text-[#1D1D1F] font-semibold' : 'text-[#86868B] hover:text-[#1D1D1F]'">
                                Panduan Peminjaman
                            </a>
                        </li>
                        {{-- Tautan Seksi 4: Helpdesk & Lapor Kerusakan --}}
                        <li>
                            <a href="#lapor" @click.prevent="document.getElementById('lapor').scrollIntoView({behavior: 'smooth'})" 
                               class="transition block cursor-pointer"
                               :class="activeTab === 'lapor' ? 'text-[#1D1D1F] font-semibold' : 'text-[#86868B] hover:text-[#1D1D1F]'">
                                Lapor Kerusakan (Helpdesk)
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            
            {{-- 
              2. AREA KONTEN REGULASI UTAMA (ARTIKEL DENGAN SCROLL MARGIN TOP):
            --}}
            <div class="flex-1 max-w-3xl relative">
                
                {{-- 
                  SEKSI 1: KEBIJAKAN PRIVASI & TRANSPARANSI COOKIES
                --}}
                <div id="kebijakan" class="info-section scroll-mt-32 mb-20">
                    <h1 class="text-4xl font-bold tracking-tight mb-8">Kebijakan Privasi</h1>
                    <div class="prose prose-slate max-w-none space-y-6 text-[#1D1D1F] font-light leading-relaxed">
                        <p>Sistem Informasi Pengelolaan Fasilitas Kampus CAVA (Campus Activity & Venue Allocation) berkomitmen menjamin kerahasiaan dan keamanan data seluruh sivitas akademika. Kebijakan ini menjelaskan bagaimana data identitas pengguna, teknologi cookies, dan sesi peramban dikelola secara transparan dan bertanggung jawab.</p>
                        
                        <h2 class="text-xl font-semibold mt-8 mb-4">1. Pengumpulan Data</h2>
                        <p>Untuk menjamin akuntabilitas serta ketertiban peminjaman sarana prasarana, sistem CAVA mengumpulkan data dasar sivitas akademika saat pendaftaran akun maupun pengajuan reservasi. Data tersebut mencakup nama lengkap, Nomor Induk Mahasiswa (NIM) atau Nomor Induk Pegawai (NIP), alamat email resmi institusi, serta dokumen verifikasi Kartu Tanda Mahasiswa (KTM) atau surat tugas institusional (SOP ADM-01). Seluruh data digunakan murni untuk verifikasi hak akses, pencatatan log audit kegiatan kampus, dan tidak pernah diperjualbelikan maupun dibagikan kepada pihak ketiga di luar instansi.</p>
                        
                        <h2 class="text-xl font-semibold mt-8 mb-4">2. Penggunaan Informasi & Cookies</h2>
                        <p>Portal CAVA memanfaatkan teknologi cookie dan sesi peramban untuk mengingat preferensi tampilan Anda, memelihara sesi masuk yang aman, serta melindungi transmisi formulir dari ancaman siber. Berikut adalah rincian transparansi cookie yang diterapkan di dalam sistem:</p>

                        {{-- Tabel Transparansi Cookies CAVA --}}
                        <div class="overflow-x-auto not-prose my-6">
                            <table class="w-full text-left text-xs border border-gray-100 rounded-xl overflow-hidden">
                                <thead class="bg-gray-50 text-[#86868B] uppercase font-semibold border-b border-gray-100">
                                    <tr>
                                        <th class="py-3 px-4">Nama Cookie</th>
                                        <th class="py-3 px-4">Kategori</th>
                                        <th class="py-3 px-4">Masa Berlaku</th>
                                        <th class="py-3 px-4">Tujuan & Fungsi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 bg-white">
                                    <tr>
                                        <td class="py-3 px-4 font-mono font-medium text-[#1D1D1F]">cava_policy_accepted</td>
                                        <td class="py-3 px-4 text-gray-600">Preferensi</td>
                                        <td class="py-3 px-4 text-gray-600">365 Hari</td>
                                        <td class="py-3 px-4 text-gray-600">Mengingat konfirmasi persetujuan kebijakan cookies agar spanduk banner tidak muncul berulang kali.</td>
                                    </tr>
                                    <tr>
                                        <td class="py-3 px-4 font-mono font-medium text-[#1D1D1F]">cava_pref_category</td>
                                        <td class="py-3 px-4 text-gray-600">Fungsional</td>
                                        <td class="py-3 px-4 text-gray-600">30 Hari</td>
                                        <td class="py-3 px-4 text-gray-600">Mengingat pilihan kategori fasilitas terakhir yang Anda filter di halaman katalog publik.</td>
                                    </tr>
                                    <tr>
                                        <td class="py-3 px-4 font-mono font-medium text-[#1D1D1F]">cava_pref_building</td>
                                        <td class="py-3 px-4 text-gray-600">Fungsional</td>
                                        <td class="py-3 px-4 text-gray-600">30 Hari</td>
                                        <td class="py-3 px-4 text-gray-600">Mengingat filter gedung/zona terakhir pada matriks ketersediaan jadwal 30 menit.</td>
                                    </tr>
                                    <tr>
                                        <td class="py-3 px-4 font-mono font-medium text-[#1D1D1F]">cava_session</td>
                                        <td class="py-3 px-4 text-gray-600">Esensial</td>
                                        <td class="py-3 px-4 text-gray-600">120 Menit</td>
                                        <td class="py-3 px-4 text-gray-600">Token identitas sesi login pengguna terenkripsi untuk menjaga keamanan akun tanpa login berulang.</td>
                                    </tr>
                                    <tr>
                                        <td class="py-3 px-4 font-mono font-medium text-[#1D1D1F]">XSRF-TOKEN</td>
                                        <td class="py-3 px-4 text-gray-600">Keamanan</td>
                                        <td class="py-3 px-4 text-gray-600">Sesi Aktif</td>
                                        <td class="py-3 px-4 text-gray-600">Token keamanan bawaan untuk mencegah serangan Cross-Site Request Forgery (CSRF) pada setiap form.</td>
                                    </tr>
                                    <tr>
                                        <td class="py-3 px-4 font-mono font-medium text-[#1D1D1F]">remember_web_*</td>
                                        <td class="py-3 px-4 text-gray-600">Otentikasi</td>
                                        <td class="py-3 px-4 text-gray-600">Persisten</td>
                                        <td class="py-3 px-4 text-gray-600">Token otentikasi login otomatis jika pengguna mencentang opsi 'Ingat Saya' pada saat masuk.</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        {{-- Panel Manajemen Sesi Draf Otomatis --}}
                        <div class="p-6 bg-gray-50 rounded-2xl border border-gray-100 mt-8">
                            <p class="text-sm m-0"><strong>Manajemen Sesi & Fitur Draf Otomatis:</strong> Sistem CAVA dilengkapi fitur penyimpanan draf sementara (<code class="font-mono text-xs">reservation_draft</code> dan <code class="font-mono text-xs">report_draft</code>) yang menjaga isian formulir pemesanan atau pengaduan di sisi server saat koneksi terputus atau halaman ter-refresh secara tidak sengaja. Draf otomatis dibersihkan begitu tiket resmi berhasil diterbitkan.</p>
                        </div>
                    </div>
                </div>

                {{-- 
                  SEKSI 2: SYARAT & KETENTUAN OPERASIONAL PEMINJAMAN
                --}}
                <div id="syarat" class="info-section scroll-mt-32 mb-20 border-t border-gray-100 pt-16">
                    <h1 class="text-4xl font-bold tracking-tight mb-8">Syarat & Ketentuan</h1>
                    <div class="prose prose-slate max-w-none space-y-6 text-[#1D1D1F] font-light leading-relaxed">
                        <p>Peminjaman ruangan dan fasilitas kampus diatur guna menciptakan tertib administrasi, pemanfaatan yang adil (<em>Fair Usage</em>), dan kenyamanan pemeliharaan fasilitas bersama bagi seluruh sivitas akademika.</p>
                        
                        <h2 class="text-xl font-semibold mt-8 mb-4">Kewajiban Pengguna</h2>
                        <ul class="list-disc pl-5 space-y-2">
                            <li>Pemohon wajib memiliki akun sivitas aktif yang telah terverifikasi oleh Administrator Sarpras.</li>
                            <li>Peminjaman fasilitas umum dan laboratorium beroperasi pada hari Senin s.d. Sabtu pukul 07:00 – 20:00 WIB dalam blok matriks 30 menit.</li>
                            <li>Pengguna wajib menjaga kebersihan ruangan, mengembalikan tata letak kursi/meja ke formasi standar, serta mematikan pendingin ruangan (AC), proyektor, dan lampu sebelum meninggalkan lokasi.</li>
                            <li>Dilarang membawa makanan beraroma menyengat, merokok, atau menggunakan fasilitas di luar peruntukan izin kegiatan yang diajukan.</li>
                        </ul>

                        {{-- Regulasi Pembatalan Mandiri H-1 --}}
                        <div class="p-6 bg-gray-50 rounded-2xl border border-gray-100 mt-8">
                            <p class="text-sm m-0"><strong>Ketentuan Pembatalan Mandiri H-1 (Regulasi BR-USR03-02):</strong> Pembatalan reservasi yang telah disetujui hanya dapat dilakukan secara mandiri oleh peminjam selambat-lambatnya 24 jam sebelum jadwal mulai kegiatan. Batas waktu ini diperlukan agar slot waktu dapat segera dialokasikan kembali untuk sivitas lain yang membutuhkan. Pembatalan darurat di bawah 24 jam wajib melapor langsung ke loket Petugas Sarpras.</p>
                        </div>
                        
                        <h2 class="text-xl font-semibold mt-8 mb-4">Sanksi Pelanggaran</h2>
                        <p>Pelanggaran terhadap tata tertib peminjaman dikenai sanksi berjenjang: peringatan tertulis untuk keterlambatan pengosongan ruangan lebih dari 30 menit, penangguhan hak reservasi selama 14–30 hari kalender jika meninggalkan ruangan dalam kondisi kotor atau melakukan pembatalan sepihak berulang tanpa konfirmasi, serta kewajiban penggantian biaya ganti rugi fisik penuh dan penerusan ke Komisi Disiplin Mahasiswa bagi perusakan sarana yang disengaja.</p>
                    </div>
                </div>

                {{-- 
                  SEKSI 3: PANDUAN LANGKAH-LANGKAH PEMINJAMAN
                --}}
                <div id="panduan" class="info-section scroll-mt-32 mb-20 border-t border-gray-100 pt-16">
                    <h1 class="text-4xl font-bold tracking-tight mb-8">Panduan Peminjaman</h1>
                    <div class="prose prose-slate max-w-none space-y-6 text-[#1D1D1F] font-light leading-relaxed">
                        <p>Prosedur peminjaman fasilitas di CAVA dirancang ringkas, transparan, dan terintegrasi secara digital melalui empat tahap alur pengajuan:</p>
                        
                        <h2 class="text-xl font-semibold mt-8 mb-4">Langkah-Langkah Reservasi</h2>
                        <ol class="list-decimal pl-5 space-y-4">
                            <li><strong>Tahap Pertama:</strong> Eksplorasi katalog fasilitas untuk melihat kapasitas dan fasilitas ruangan, kemudian periksa matriks 30 menit pada menu Cek Ketersediaan untuk memastikan ruangan bebas jadwal bentrok.</li>
                            <li><strong>Tahap Kedua:</strong> Masuk ke akun Anda, klik formulir Reservasi Ruangan, tentukan tanggal serta jam peminjaman, lalu lampirkan surat izin atau proposal kegiatan (format PDF maks. 5MB) dengan proteksi draf otomatis.</li>
                            <li><strong>Tahap Ketiga:</strong> Petugas Sarpras memverifikasi permohonan dalam waktu maksimal 1x24 jam kerja untuk memeriksa kelengkapan berkas dan kelayakan kegiatan.</li>
                            <li><strong>Tahap Keempat:</strong> Setelah disetujui, tiket peminjaman digital resmi diterbitkan dengan kode unik <code class="font-mono text-xs">TKT-YYYYMMDD-XXXX</code> yang ditunjukkan saat serah terima kunci di lokasi pelaksanaan.</li>
                        </ol>
                        
                        <p>Pastikan peminjam hadir tepat waktu sesuai jadwal tiket reservasi untuk melakukan konfirmasi kehadiran (check-in) dengan petugas keamanan atau pengelola gedung setempat.</p>
                    </div>
                </div>

                {{-- 
                  SEKSI 4: HELPDESK & LAPOR KERUSAKAN FASILITAS
                --}}
                <div id="lapor" class="info-section scroll-mt-32 mb-20 border-t border-gray-100 pt-16">
                    <h1 class="text-4xl font-bold tracking-tight mb-8">Helpdesk & Lapor Kerusakan</h1>
                    <div class="prose prose-slate max-w-none space-y-6 text-[#1D1D1F] font-light leading-relaxed">
                        <p>Sivitas akademika dapat melaporkan sarana prasarana yang mengalami kerusakan atau malfungsi (seperti AC bocor, proyektor bermasalah, kursi patah, atau stopkontak padam) melalui formulir Helpdesk CAVA dengan melampirkan foto bukti kondisi fisik (< 2MB).</p>
                        
                        {{-- Prosedur Darurat Sarpras --}}
                        <div class="p-6 bg-red-50 rounded-2xl border border-red-100 mt-8">
                            <h3 class="text-red-800 font-semibold mb-2">Prosedur Darurat</h3>
                            <p class="text-sm m-0 text-red-700">Untuk insiden darurat yang memerlukan penanganan seketika di lokasi acara (seperti korsleting listrik, pipa bocor besar, atau genset padam), hubungi Posko Sarpras: (021) 555-0199 ext. 104 atau WhatsApp Siaga: +62 812-3456-7890.</p>
                        </div>
                        
                        <h2 class="text-xl font-semibold mt-8 mb-4">Estimasi Penanganan</h2>
                        <p>Setiap laporan pengaduan akan mendapatkan kode tiket pelaporan <code class="font-mono text-xs">RPT-YYYYMMDD-XXXX</code> dan diproses melalui empat tahapan status: <strong>Baru</strong> (menunggu inspeksi awal), <strong>Diproses</strong> (teknisi sedang memperbaiki di lokasi), <strong>Selesai</strong> (perbaikan tuntas dan fasilitas siap digunakan kembali), atau <strong>Ditolak</strong> (tiket duplikat atau di luar wewenang Sarpras). Fasilitas dengan kerusakan kritis akan dinonaktifkan sementara dalam <em>Maintenance Mode</em> hingga verifikasi perbaikan selesai.</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</x-public-layout>
