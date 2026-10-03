# Rencana Implementasi: Halaman Informasi (Kebijakan, Syarat & Ketentuan, dan Bantuan)

**Branch Target**: `feature/information-page`  
**Fitur**: Portal Pusat Informasi Publik CAVA (Campus Venue Access)  
**Dokumen Terkait**: `CASE_PROJECT.md`, `RULE_FRONTEND.md`, `TECHSTACK.md`, `GUIDE_GITHUB.md`

---

## 1. Latar Belakang & Tujuan
Pada bagian bawah *landing page* dan komponen *footer* (`<x-cava.footer>`) terdapat tiga tautan krusial:
1. **Kebijakan** (*Kebijakan Privasi & Operasional Sarpras*)
2. **Syarat dan Ketentuan** (*Aturan Peminjaman Fasilitas & Pelaporan Kerusakan*)
3. **Bantuan** (*Pusat Bantuan, Panduan Alur, FAQ, & Kontak Helpdesk Sarpras*)

Sebelumnya, tautan-tautan tersebut masih berupa placeholder (`href="#"`) tanpa halaman tujuan. Fitur ini bertujuan untuk:
- Merumuskan isi konten resmi yang komprehensif, kredibel, dan sesuai konteks birokrasi universitas.
- Membangun antarmuka terpadu (*Information Hub*) dengan navigasi tab dinamis (Alpine.js) dan URL parameter/hash sinkron.
- Memperbarui komponen footer global dan menambahkan seksi *quick-access card* di bagian bawah *landing page*.

---

## 2. Rincian Muatan Konten (3 Pilar Informasi)

### A. Kebijakan (Privacy & Asset Operational Policy)
- **Perlindungan Data Pribadi Sivitas**: Menjamin data identitas akun (NIM/NIP, email institusi, program studi, kontak) dienkripsi dan hanya dipakai untuk verifikasi administrasi perizinan kampus.
- **Kerahasiaan Identitas Pelapor (*Whistleblower Protection*)**: Melindungi privasi pelapor kerusakan fasilitas dari intimidasi pihak mana pun; hanya diproses oleh Petugas Sarpras terkait.
- **Kebijakan Hak Pembatalan Darurat (*Force Majeure / Rektorat Override*)**: Hak prerogatif Rektorat dan Petugas Sarpras membatalkan reservasi secara darurat demi agenda kenegaraan atau pimpinan kampus (sesuai modul `PTG-03`).
- **Kebijakan Mode Pemeliharaan (*Maintenance Lock*)**: Ketentuan penguncian otomatis fasilitas jika sarpras mengalami kerusakan fatal (sesuai modul `PTG-05`).
- **Audit Jejak Digital & BMN**: Pencatatan log seluruh riwayat peminjaman dan aduan untuk pelaporan aset Barang Milik Negara/Universitas secara transparan.

### B. Syarat dan Ketentuan (Terms of Service & Rules of Use - 4 Bagian Terpadu)
- **Kelayakan Akun Pemohon**: Pengguna wajib memiliki akun terdaftar dan berstatus aktif (`active`) yang disetujui Admin. Dilarang memindahtangankan akun ke pihak luar.
- **Tata Cara & Waktu Pengajuan Reservasi**:
  - Reservasi wajib diajukan paling lambat H-2 (48 jam) sebelum kegiatan dan maksimal 30 hari ke depan.
  - Slot waktu dihitung per kelipatan 30 menit dalam rentang operasional 07:00 – 20:00 WIB.
- **Kewajiban Pengguna Fasilitas**:
  - Wajib menjaga ketertiban, kebersihan ruangan, dan tidak memindahkan aset tetap.
  - Mematikan AC, proyektor, serta lampu sebelum meninggalkan ruangan.
  - Dilarang merokok dan dilarang makan berat di laboratorium komputer atau smart classroom.
- **Ketentuan Pelaporan Kerusakan**:
  - Laporan wajib melampirkan foto bukti kondisi fisik kerusakan yang valid dan keterangan lokasi yang jelas.
  - Dilarang membuat tiket pengaduan palsu atau tidak bertanggung jawab.
*(Catatan revisi: Bagian sanksi & konsekuensi dihilangkan sesuai permintaan, sehingga murni 4 ketentuan pokok).*

### C. Bantuan (Help Center, FAQ, & Helpdesk Sarpras)
- **Panduan Singkat Alur Sistem (Step-by-Step Guide)**:
  - *Alur Reservasi*: Katalog Ruang ➔ Cek Slot ➔ Login/Daftar ➔ Isi Form Reservasi ➔ Persetujuan Petugas ➔ Unduh Bukti Reservasi.
  - *Alur Lapor Kerusakan*: Temukan Kerusakan ➔ Ambil Foto ➔ Unggah Tiket ➔ Penanganan Teknisi ➔ Selesai Diperbaiki.
- **Frequently Asked Questions (FAQ)**:
  - *Berapa lama waktu verifikasi akun pendaftaran mandiri?* (Maksimal 1x24 jam kerja).
  - *Mengapa slot waktu tertentu di kalender berwarna merah/terkunci?* (Ruang sedang dibooking orang lain atau dalam status perbaikan/maintenance).
  - *Apakah saya bisa membatalkan reservasi yang sudah disetujui?* (Bisa, melalui tombol 'Batalkan' di riwayat reservasi sebelum hari-H).
- **Kontak & Layanan Helpdesk**:
  - **Lokasi Kantor**: Gedung Rektorat Sayap Timur, Lantai 1, Ruang Biro Sarana & Prasarana Kampus.
  - **Jam Operasional**: Senin – Jumat, pukul 08:00 – 16:00 WIB.
  - **Hotline WhatsApp**: +62 811-2233-4455.
  - **Email Resmi**: `sarpras@kampus.ac.id`.

---

## 3. Rencana Arsitektur Teknis

### A. Routing (`routes/web.php`)
Menambahkan rute publik baru:
- `Route::get('/informasi/{section?}', [PublicInformationController::class, 'index'])->name('public.information');`
- Alias rute praktis:
  - `Route::get('/kebijakan', fn() => redirect()->route('public.information', ['section' => 'kebijakan']))->name('public.kebijakan');`
  - `Route::get('/syarat-ketentuan', fn() => redirect()->route('public.information', ['section' => 'syarat-ketentuan']))->name('public.syarat-ketentuan');`
  - `Route::get('/bantuan', fn() => redirect()->route('public.information', ['section' => 'bantuan']))->name('public.bantuan');`

### B. Controller Baru (`app/Http/Controllers/PublicInformationController.php`)
Membuat controller ramping untuk merender view `public.information` dengan validasi parameter seksi aktif (`kebijakan`, `syarat-ketentuan`, `bantuan`).

### C. View Blade (`resources/views/public/information.blade.php`)
- Memakai layout master `<x-public-layout active="informasi">`.
- Header Banner sederhana dan bersih tanpa ornamen berlebih atau emotikon.
- Navigasi Tab Interaktif ditenagai oleh Alpine.js (`x-data="{ activeTab: '{{ $section }}' }"`).
- **Tab Kebijakan**: Dibuat 1 wadah kontainer terpadu (*single container*) dengan 5 poin berurutan yang dipisahkan garis batas halus (*subtle divider*).
- **Tab Syarat & Ketentuan**: Dibuat 1 wadah kontainer terpadu (*single container*) murni 4 bagian ketentuan (bagian sanksi & konsekuensi dihilangkan).
- **Tab Bantuan**: Dibuat lebih simpel mencakup alur 4 langkah dalam kotak sederhana, accordion FAQ ringkas, dan kontak Biro Sarpras bersih.
- Menghindari penggunaan emotikon di seluruh bagian antarmuka demi kesan profesional.


### D. Pembaruan Komponen Footer (`resources/views/components/cava/footer.blade.php`)
Mengubah tautan mati:
- `Kebijakan Privasi` ➔ `href="{{ route('public.information', ['section' => 'kebijakan']) }}"`
- `Syarat & Ketentuan` ➔ `href="{{ route('public.information', ['section' => 'syarat-ketentuan']) }}"`
- `Bantuan` ➔ `href="{{ route('public.information', ['section' => 'bantuan']) }}"`

### E. Penambahan Bagian Informasi di Bagian Bawah Landing Page (`resources/views/public/home.blade.php`)
Menambahkan seksi visual sebelum footer di `home.blade.php` berisi 3 kartu ringkas (*Kebijakan, Syarat & Ketentuan, Pusat Bantuan*) yang mengajak pengguna memahami aturan kampus sebelum melakukan reservasi, lengkap dengan tombol langsung ke tab terkait.

---

## 4. Tahapan Pengerjaan (Step-by-Step Execution)
1. **Tahap 1**: Buat Controller `PublicInformationController.php`.
2. **Tahap 2**: Daftarkan rute `/informasi/{section?}` dan aliasnya pada `routes/web.php`.
3. **Tahap 3**: Buat view `resources/views/public/information.blade.php` lengkap dengan desain responsif, header komentar sesuai `RULE_FRONTEND.md`, dan konten 3 pilar.
4. **Tahap 4**: Perbarui komponen footer `resources/views/components/cava/footer.blade.php`.
5. **Tahap 5**: Perbarui `resources/views/public/home.blade.php` dengan seksi kartu informasi 3 objek.
6. **Tahap 6**: Uji coba verifikasi navigasi peramban dan pastikan tidak ada sintaks error.
7. **Tahap 7**: Buat laporan pasca-eksekusi (*walkthrough*) serta teks perintah Git commit terpisah sesuai panduan `GUIDE_GITHUB.md`.
