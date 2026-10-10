# CAVA - Public Interface Design Guidelines

Dokumen ini adalah **Single Source of Truth** untuk segala keputusan desain (UI/UX) dan arsitektur *frontend* pada halaman publik CAVA. Gunakan dokumen ini sebagai acuan utama saat melakukan *refactoring* atau penambahan fitur di sesi *chat* berikutnya agar konsistensi desain tetap terjaga.

---

## 1. Filosofi & Gaya Visual
**Tema:** *Apple-esque Minimalist* dipadukan dengan efek *Glassmorphism*.
*   **Kesan:** Bersih, lapang, modern, eksklusif, dan interaktif namun tetap fungsional.
*   **Ruang Negatif (White Space):** Penggunaan *padding* dan *margin* yang ekstra lega (menggunakan skala `p-8`, `mb-10`, `gap-6` dari Tailwind) agar antarmuka tidak terasa sesak.

### 1.1. Palet Warna (Tailwind CSS)
*   **Background Utama:** `#FAFAFC` (abu-abu sangat muda/bersih)
*   **Teks Utama (Headers & Body):** `#1D1D1F` (abu-abu gelap nyaris hitam khas perangkat modern)
*   **Teks Sekunder (Mute/Subtitles):** `#86868B`
*   **Surface / Cards:** `bg-white` (putih murni untuk memberikan kontras terhadap *background* utama)
*   **Aksen (Warna Tematik Filter/Interaksi):** 
    *   *Default/Neutral Focus:* `ring-blue-500` / `border-blue-500`
    *   *Kategori Pil:* `bg-blue-50 text-blue-800`
    *   *Kapasitas Pil:* `bg-purple-50 text-purple-800`
    *   *Tanggal Pil:* `bg-amber-50 text-amber-800`
    *   *Fasilitas Pil:* `bg-emerald-50 text-emerald-800`

### 1.2. Tipografi
*   **Font Family:** `Inter`, sans-serif.
*   **Penggunaan Bobot (Weight):**
    *   `font-light` / `font-normal`: Paragraf dan teks deskriptif ringan.
    *   `font-medium`: Tombol (*buttons*), label navigasi, label *input*.
    *   `font-semibold`: Sub-judul (h3, h2 sekunder).
    *   `font-bold` / `tracking-tight`: Judul utama (Hero section, h1).

### 1.3. Border & Shadows (Bentuk)
*   **Border Radius:** 
    *   `rounded-xl` (Bilah pencarian, *input*, tombol, pil *filter*).
    *   `rounded-2xl` / `rounded-[2rem]`: Kartu (*Cards*) atau komponen kontainer besar.
    *   `rounded-3xl`: *Pop-up* modal besar.
*   **Shadows:** Penggunaan *shadow* kustom yang sangat halus untuk memberikan ilusi *depth* tanpa berlebihan.
    *   *Default Hover:* `shadow-[0_8px_30px_rgb(0,0,0,0.04)]` atau `0.08` untuk penekanan.

---

## 2. Standar Komponen Utama

### 2.1. Navigasi / Header
*   Berada dalam elemen `<nav>` yang melayang secara statis di atas.
*   *Class* wajib: `fixed w-full z-40 glass-panel`.
*   **Transisi:** Header **TIDAK BOLEH** bergeser atau beranimasi ketika perpindahan halaman terjadi. Harus selalu *fixed* di posisinya.
*   *Glassmorphism CSS*:
    ```css
    .glass-panel { 
        background: rgba(255, 255, 255, 0.7); 
        backdrop-filter: blur(12px); 
        -webkit-backdrop-filter: blur(12px); 
        border-bottom: 1px solid rgba(0, 0, 0, 0.05); 
    }
    ```

### 2.2. Smart Search & Filter (Bilah Pencarian Cerdas)
*   Pencarian harus dapat merender otomatis hasil rekomendasi *string* (text) dan objek (Fasilitas) setelah jeda ketik (*debounce* 1 detik).
*   *Dropdown* Smart Search dibuat menggunakan kontrol `x-data="smartSearch('...')"` dan mendengarkan event window untuk ditutup.
*   Filter Lanjutan ("Pencarian Spesifik") dikontrol menggunakan *boolean* independen (misal: `showAdvanced`), namun status *open*-nya diintegrasikan dengan logika global agar tidak tumpang tindih.

### 2.3. Modal & Pop-up
*   Semua Modal (dialog besar atau detail fasilitas) **HARUS** diposisikan dengan direktif `x-teleport="body"`.
*   **Alasan:** Halaman memiliki animasi menggunakan properti *CSS transform* / *opacity* yang menyebabkan terciptanya *Stacking Context* baru di *browser*, membatasi *z-index* elemen anak. Teleportasi melepaskan Modal ke luar konteks tersebut agar bisa menutupi elemen lain (termasuk *Header* `z-40`).
*   **Z-index Modal:** `z-50`.
*   **Backdrop Blur:** `bg-black/40 backdrop-blur-sm`.

---

## 3. Sistem Transisi Antar-Halaman
Halaman publik memiliki struktur SPA-like (Single Page Application-like) dengan animasi antar perpindahan rute (diatur dalam `pageTransition()` Alpine).

*   Setiap rute memancarkan variabel `currentIndex` (1: Home, 2: Catalog, 3: Availability, dst).
*   Jika indeks baru **> (lebih besar)** dari indeks lama = Animasi geser masuk dari **KANAN** (`animate-slide-in-right`).
*   Jika indeks baru **< (lebih kecil)** dari indeks lama = Animasi geser masuk dari **KIRI** (`animate-slide-in-left`).
*   Jika tidak ada histori atau melakukan penyegaran halaman (F5) = Animasi **Fade In** standar.
*   Jika berpindah pada indeks yang sama akibat *query/filter* saja, dan ditandai oleh fungsi pengubah _session storage_ (`categoryClicked`), jangan lakukan efek kedip (opacity 100).

---

## 4. Manajemen State (Event Global Alpine JS)
Untuk menghindari masalah elemen melayang (*pop-up, dropdown, filter panel*) yang menumpuk / tumpang tindih, CAVA menggunakan sistem pancaran (*dispatch*) *window-level event*.

Setiap kali elemen melayang dibuka (ditekan atau di-*focus*), elemen tersebut WAJIB memanggil salah satu dari perintah *dispatch* berikut:

1.  **`$dispatch('close-other-popups')`**
    *   **Kegunaan:** Menutup semua jenis panel tambahan (termasuk *dropdown* filter "Pilih Kategori", "Pilih Fasilitas", atau Panel Pencarian Spesifik).
    *   **Contoh:** Ditaruh pada `@focus` di dalam `<input type="text">` milik *smart search*.
2.  **`$dispatch('close-smart-search')`**
    *   **Kegunaan:** Menutup panel *dropdown smart search* jika ada panel lain (seperti panel filter) yang sedang digunakan.
    *   **Contoh:** Ditaruh pada tombol-tombol yang memicu filter.

Di level pembungkus form atau body, terdapat *listener*:
```html
@close-other-popups.window="openCategory = false; openFacility = false; showAdvanced = false;"
```

---
*Gunakan file ini sebagai rujukan setiap kali Anda menangani perubahan tata letak atau UI/UX agar selaras dengan desain orisinal CAVA.*
