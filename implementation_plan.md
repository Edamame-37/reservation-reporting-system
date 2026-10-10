# Implementation Plan: CAVA Design Documentation

## Objective
Membuat dokumen `DESIGN.md` yang merinci secara komprehensif seluruh aturan desain (UI/UX) yang telah diimplementasikan pada halaman publik CAVA. Dokumen ini akan menjadi referensi utama (Single Source of Truth) untuk sesi *chat* berikutnya agar konsistensi desain tetap terjaga saat melanjutkan *refactoring*.

## Tahapan Implementasi

### 1. Analisis & Pengumpulan Pola Desain (Selesai)
*   **Tema Utama:** Apple-esque Minimalist dipadukan dengan efek *Glassmorphism*.
*   **Palet Warna:** Warna latar `#FAFAFC`, teks utama `#1D1D1F`, teks sekunder `#86868B`, dan warna *accent* (*blue*, *purple*, *amber*, *emerald*, *red*).
*   **Tipografi:** *Font* `Inter` dengan hierarki ketebalan yang tegas (Light hingga Bold).
*   **Animasi & Transisi:** Transisi antar-halaman (*swipe* kiri/kanan berdasarkan indeks), efek *fade-in/fade-out* pada *dropdown* dan modal.
*   **Komponen:** *Card* melengkung (`rounded-3xl` / `rounded-2xl`), *shadow* halus saat *hover*, dan navigasi *fixed* dengan efek kaca.
*   **Interaksi UI:** Penanganan *popup* / *dropdown* ganda menggunakan arsitektur Alpine.js (`$dispatch('close-other-popups')`) dan penggunaan `x-teleport="body"` untuk menyelesaikan masalah *z-index*.

### 2. Penulisan `DESIGN.md` (Menunggu Persetujuan)
Saya akan membuat sebuah *file* `DESIGN.md` di *root directory* (*c:\my-project\reservation-reporting-system\DESIGN.md*) yang berisi bagian-bagian berikut:
*   **Filosofi & Konsep:** Visi desain (Minimalis, Modern, Elegan).
*   **Sistem Visual (Design Tokens):** Rincian kode warna, ukuran tipografi, bayangan (*shadows*), dan kelengkungan (*border-radius*).
*   **Standar Komponen Utama:** 
    *   Navigasi / *Header* (Fixed & Statis).
    *   Bilah Pencarian Cerdas (*Smart Search* & Filter).
    *   Kartu Konten (*Cards*).
    *   Modal & *Pop-up*.
*   **Standar Interaksi & Animasi:** Aturan transisi halaman dan durasi efek *hover*.
*   **Manajemen *State* (Alpine.js):** Konvensi *event global* (`close-other-popups`, `close-smart-search`) dan penggunaan *teleportasi*.

### 3. Penyelesaian
*   Memastikan `DESIGN.md` tersimpan dengan format *Markdown* yang rapi dan mudah dibaca oleh AI di sesi berikutnya.
*   Memberikan instruksi kepada pengguna untuk menyertakan referensi file ini di awal *prompt* pada sesi *chat* yang baru.
