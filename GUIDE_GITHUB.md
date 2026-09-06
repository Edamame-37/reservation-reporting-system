# Panduan Kolaborasi Tim Menggunakan Git & GitHub

Dokumen ini adalah **Standar Operasional Prosedur (SOP)** wajib bagi seluruh anggota tim *developer* dalam proyek ini. Kesalahan dalam menggunakan Git dapat menyebabkan kode yang sudah susah payah dikerjakan hilang atau tertimpa oleh kodingan anggota lain. 

Harap baca, pahami, dan ikuti skema alur kerja (*GitHub Flow*) di bawah ini!

> **Alamat Repositori Proyek:**  
> `https://github.com/Edamame-37/reservation-reporting-system.git`

---

## 1. Aturan Emas (*Golden Rules*)
1. 🛑 **DILARANG KERAS** melakukan proses *coding* atau menyimpan riwayat (*commit*) secara langsung di cabang utama (`main` atau `master`).
2. 🌿 **SELALU BUAT CABANG (*BRANCH*) BARU** untuk setiap satu tugas/fitur spesifik yang sedang Anda kerjakan.
3. 🤝 **JANGAN PERNAH MENGGABUNGKAN (*MERGE*) KODE ANDA SENDIRI**. Selalu gunakan sistem *Pull Request (PR)* di GitHub dan minta anggota tim lain untuk memeriksa kode Anda (*Code Review*) sebelum tombol *Merge* ditekan.

### Struktur dan Hierarki Penamaan Branch
Untuk menjaga kerapian repositori, penamaan cabang (*branch*) tidak boleh asal. Gunakan hierarki *prefix* dalam tabel berikut:

| Prefix / Nama Branch | Penjelasan & Hierarki Level | Contoh Penamaan |
|---|---|---|
| **`main` / `master`** | Cabang level tertinggi (Produksi). Hanya berisi kode stabil yang siap dinilai. Dilarang *coding* di sini. | `main` |
| **`develop`** | Cabang integrasi (Level 2). Tempat berkumpulnya semua fitur yang sudah selesai sebelum dirilis ke `main`. | `develop` |
| **`feature/`** | Cabang level 3. Tempat Anda bekerja membuat fitur/modul baru. | `feature/form-reservasi` |
| **`bugfix/`** | Cabang level 3. Tempat memperbaiki *error* minor dari fitur yang sedang dikembangkan. | `bugfix/tombol-pesan-mati` |
| **`hotfix/`** | Cabang darurat level 1. Untuk memperbaiki *bug* fatal yang sudah terlanjur rilis di `main`. | `hotfix/database-crash` |
| **`docs/`** | Khusus pembuatan/perubahan dokumen tanpa menyentuh kode sistem. | `docs/readme-update` |

**Alur Penggabungan Kode (*Merge Flow*):**
1. **Skenario Lurus (Jika fitur berjalan lancar TANPA BUG):**
   `feature` ➔ di-*merge* ke ➔ `develop` ➔ jika semua aman, `develop` di-*merge* ke ➔ `main`
2. **Skenario Perbaikan (Jika terdapat BUG pada saat pengembangan):**
   `feature` ➔ buat cabang ➔ `bugfix` (untuk memperbaiki) ➔ hasil perbaikan di-*merge* ke ➔ `develop` ➔ lalu ke ➔ `main`

---

## 2. Standar Penamaan Pesan (*Conventional Commits*)
Saat Anda menyimpan progres (*commit*), pesannya tidak boleh asal ketik (seperti: `"benerin error"`, `"update"`, `"gatau apaan"`). Gunakan standar industri dengan menambahkan **awalan (prefix)**:

| Prefix | Kapan Digunakan? | Contoh Command Git |
|---|---|---|
| `feat:` | Saat Anda membuat **fitur baru** yang sebelumnya tidak ada. | `git commit -m "feat: membuat form login pengguna"` |
| `fix:` | Saat Anda **memperbaiki *bug* / *error*** pada fitur yang sudah ada. | `git commit -m "fix: tombol submit sekarang bisa diklik"` |
| `docs:` | Saat Anda hanya **mengubah/menambah dokumen teks** (seperti Markdown). | `git commit -m "docs: menambahkan file panduan instalasi"` |
| `ui:` | Saat Anda fokus **merapikan desain visual/CSS** tanpa mengubah logika. | `git commit -m "ui: mengubah warna navbar menjadi biru"` |
| `refactor:`| Saat Anda **merapikan kodingan** (agar lebih bersih) tanpa mengubah fungsionalitasnya. | `git commit -m "refactor: membuang variabel yang tidak dipakai"` |

---

**⚠️ PERINGATAN:** Selalu buat pesan commit dengan menggunakan bahasa indonesia (sebagai basicnya),  **hanya gunakan bahasa inggris pada beberapa kata saja (jangan keseluruhan) jika memang benar-benar diperlukan**.

## 3. SOP Siklus Kerja Harian Programmer (*Daily Workflow*)
Ikuti **6 langkah baku** ini setiap kali Anda baru membuka laptop untuk mulai *coding*:

### Langkah 1: Cek Posisi Cabang Saat Ini
Sebelum melakukan apapun, biasakan selalu mengecek di *branch* mana posisi Anda sekarang agar tidak salah *coding* di cabang yang keliru.
```bash
# Mengecek branch yang sedang aktif (ditandai dengan warna hijau atau tanda bintang *)
git branch

# ATAU melihat detail status branch dan file yang termodifikasi
git status
```

### Langkah 2: Sinkronisasi ke Kode Terbaru
Pastikan laptop Anda mengunduh progres terbaru hasil kerja tim Anda di hari sebelumnya (biasanya dari `develop` atau `main`).
```bash
# Template format:
# git checkout <branch-tujuan>
# git pull origin <branch-tujuan>

# Contoh Eksekusi:
git checkout develop
git pull origin develop
```

### Langkah 3: Buat Ruang Kerja Baru (*Feature Branch*)
Buat cabang baru dengan nama tugas yang akan Anda pegang hari ini. Format penamaan harus mengikuti hierarki (misal: `feature/<nama-fitur>`).

**Skenario A: Jika cabang BELUM PERNAH dibuat (Baru mulai tugas baru):**
```bash
# Template format:
# git checkout -b <nama-branch-baru>

# Contoh Eksekusi:
git checkout -b feature/form-reservasi
```
*(Perintah `-b` otomatis membuat cabang baru dan memindahkan Anda ke sana. Mulai dari sini, Anda aman untuk mulai mengacak-acak kode).*

**Skenario B: Jika cabang SUDAH PERNAH dibuat (Melanjutkan sisa kerjaan kemarin):**
Dilarang menggunakan parameter `-b` jika Anda hanya ingin kembali melanjutkan pekerjaan di cabang yang sudah ada.
```bash
# Template format:
# git checkout <nama-branch-lama>

# Contoh Eksekusi:
git checkout feature/form-reservasi
```

### Langkah 4: Coding & Simpan Lokal (*Add & Commit*)
Kerjakan tugas Anda di VS Code. Jika sudah selesai (atau jika fitur sudah jalan sebagian dan Anda mau istirahat), simpan jejaknya:

**⚠️ PERINGATAN KERAS:** Dilarang menggunakan `git add .` (titik). Anda wajib mendaftarkan dan menyimpan (*commit*) file satu per satu secara spesifik agar riwayat rapi dan meminimalisir terangkutnya file sampah (seperti `.env` atau *log* error).

```bash
# Template format:
# git add <nama-file-spesifik>
# git commit -m "<prefix>: <pesan deskriptif file tersebut>"

# Contoh Eksekusi:
git add app/Http/Controllers/FacilityController.php
git commit -m "feat: menambahkan logika pengambilan data fasilitas"

git add resources/views/user/reservasi.blade.php
git commit -m "feat: merancang tampilan antarmuka form pengajuan reservasi"
```

### Langkah 5: Unggah ke Server (*Push*)
Kirim riwayat lokal yang baru saja Anda kunci ke *server* GitHub agar teman Anda bisa melihat hasil *coding*-an Anda.
```bash
# Template format:
# git push origin <nama-branch-saat-ini>

# Contoh Eksekusi:
git push origin feature/form-reservasi
```

### Langkah 6: Pull Request (PR) & Code Review
Proses ini dilakukan di *website* GitHub, bukan di Terminal:
1. Buka repositori proyek di browser (`https://github.com/...`).
2. Akan muncul tombol hijau besar **"Compare & pull request"**. Klik tombol itu.
3. Beri deskripsi (opsional) tentang kodingan apa yang baru saja Anda selesaikan.
4. Klik **"Create pull request"**.
5. **STOP DI SINI.** Informasikan ke grup WhatsApp tim Anda: *"Tugasku udah selesai nih, tolong di-review"*.
6. Anggota tim lain akan membuka PR tersebut, membaca kodenya, dan jika aman/tidak ada *error*, barulah teman Anda yang menekan tombol hijau **"Merge pull request"**.

---

## 4. Contoh Simulasi Skenario Bekerja
Bayangkan **Budi** ditugaskan membuat fitur form pelaporan kerusakan (*ticketing*). Berikut rentetan aktivitas terminal yang akan Budi jalankan di hari itu:

```bash
budi@laptop:~$ git checkout main
budi@laptop:~$ git pull origin main
budi@laptop:~$ git checkout -b feature/form-laporan

# ... Budi mulai mengetik kodingan di VS Code selama 3 Jam ...
# ... Budi mengecek hasilnya di localhost dan memastikan tidak ada error ...

budi@laptop:~$ git add resources/views/laporan.blade.php
budi@laptop:~$ git commit -m "feat: merancang antarmuka form laporan kerusakan"
budi@laptop:~$ git add app/Http/Requests/LaporanRequest.php
budi@laptop:~$ git commit -m "feat: menambahkan validasi keamanan upload gambar"
budi@laptop:~$ git push origin feature/form-laporan
```
Lalu Budi membuka GitHub, membuat *Pull Request*, dan meminta **Andi** (rekan setimnya) untuk memeriksa kodenya. Setelah Andi menekan *Merge*, tugas Budi dinyatakan selesai!

---

## 5. Prosedur Penanganan Konflik (*Merge Conflict*)

**Apa itu *Conflict*?**
*Conflict* terjadi saat Anda dan teman Anda tanpa sengaja mengedit **file yang sama pada baris nomor yang sama persis**, lalu keduanya mencoba melakukan *Merge* ke `main`. Komputer akan bingung, kodingan milik siapa yang harus dipakai?

**Cara Mengatasi Conflict:**
1. Jangan panik. *Conflict* adalah hal wajar di industri *software engineering*.
2. Saat Anda melakukan `git pull origin main` di *branch* Anda, terminal akan berteriak merah: *"CONFLICT (content): Merge conflict in nama_file.php"*.
3. Buka file yang terkena konflik tersebut di **Visual Studio Code**.
4. VS Code sangat pintar. Ia akan menyorot baris yang bentrok dengan warna hijau dan biru cerah, lalu memberi Anda 3 opsi tombol yang bisa diklik di atas baris kode:
   - **`Accept Current Change`**: Anda menghapus paksa kode teman Anda, dan mempertahankan kode milik Anda.
   - **`Accept Incoming Change`**: Anda mengalah, menghapus kode Anda, dan memakai kode teman Anda.
   - **`Accept Both Changes`**: Menyimpan keduanya (kode Anda ditaruh di atas, kode teman di bawahnya).
5. Diskusikan dengan teman Anda via grup: *"Bro, file Route baris ke-45 ini pakai kodinganmu atau kodinganku?"*.
6. Setelah Anda mengklik salah satu pilihan penyelesaian di VS Code, simpan file tersebut (`Ctrl + S`), lalu daftarkan kembali ke Git:
```bash
git add routes/web.php
git commit -m "fix: menyelesaikan merge conflict pada konfigurasi route utama"
git push origin nama-branch-anda
```
Konflik berhasil diselesaikan, dan proyek kembali aman!
