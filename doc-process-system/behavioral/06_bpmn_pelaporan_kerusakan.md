# 10. BPMN: Pelaporan & Penanganan Kerusakan (Support Process)

## Tujuan
Memvisualisasikan alur bisnis (Business Process Model and Notation) operasional lapangan, yang meliputi pelaporan insiden oleh mahasiswa dan penindaklanjutan perbaikan oleh teknisi (Petugas) sambil mengotomatisasikan status ketersediaan fasilitas (maintenance mode).

## Analisis Komponen BPMN

### 1. Peserta Utama (Pools & Lanes)
- **Pool 1: Pelapor**
  - **Lane:** Pengguna (Orang yang menemukan kerusakan).
- **Pool 2: Pemeliharaan (Maintenance)**
  - **Lane Atas:** Sistem CAVA (Otomasi Backend).
  - **Lane Bawah:** Petugas Sarpras (Teknisi/Manusia).

### 2. Penjelasan Peristiwa (Events) & Tugas (Tasks)
- **(Start Event) Laporan Baru:** Pengguna melaporkan fasilitas bermasalah diiringi foto (attachment).
- **(Task & Gateway) Validasi Sistem:** Sistem mengecek ekstensi dan batas 2MB foto. Jika gagal ➔ Pengguna perbaiki form.
- **(Task) Pemrosesan Tiket:** Laporan sah, status menjadi 'Baru'. Masuk ke Dasbor Petugas.
- **(Event) Petugas Bertindak:** Petugas menekan "Sedang Diproses" untuk menandakan tiket mulai dikerjakan di lapangan.
- **(Parallel Action) Kunci Fasilitas:** Ini vital. Tindakan petugas di atas tidak hanya sekadar mengubah kata, tetapi menyuruh (mengirim sinyal ke) Sistem untuk memblokir jadwal (*maintenance block*) fasilitas tersebut dari katalog.
- **(End Event) Selesai:** Teknisi memberi log resolusi "Selesai", blokir sistem dibuka, dan Pelapor menerima notifikasi perbaikan usai.

---

## Kode Diagram Mermaid

```mermaid
flowchart TD
    %% Pool: Pelapor
    subgraph Pool_Pelapor[Pool: Sivitas Akademika]
        subgraph Lane_Pengguna[Lane: Pengguna (Pelapor)]
            Start((Insiden Ditemukan)) --> IsiLapor[Tugas: Submit Laporan & Foto]
            RevisiLapor[Tugas: Ganti Foto Sesuai Syarat] --> IsiLapor
            NotifSelesai((Selesai: Fasilitas Normal))
        end
    end

    %% Pool: Pemeliharaan
    subgraph Pool_Pemeliharaan[Pool: Manajemen Pemeliharaan CAVA]
        subgraph Lane_Sistem[Lane: Sistem CAVA]
            Validasi[Tugas Sistem: Validasi File < 2MB & MimeType]
            GateValid{Valid?}
            SimpanBaru[Tugas Sistem: Simpan Status 'Baru']
            Kunci[Tugas Sistem: Otomatis Blokir Fasilitas (Maintenance)]
            Buka[Tugas Sistem: Buka Blokir (Kembali Tersedia)]
        end
        
        subgraph Lane_Petugas[Lane: Petugas Sarpras / Teknisi]
            AmbilTiket[Tugas: Ubah Status Tiket 'Diproses']
            Perbaikan[Proses Eksternal: Memperbaiki di Lapangan]
            TutupTiket[Tugas: Isi Resolusi & Status 'Selesai']
        end
    end

    %% Alur Proses Lintas Lane
    IsiLapor --> Validasi
    Validasi --> GateValid
    
    GateValid -- Tidak --> RevisiLapor
    GateValid -- Ya --> SimpanBaru
    
    SimpanBaru --> AmbilTiket
    AmbilTiket --> Kunci
    AmbilTiket --> Perbaikan
    
    Perbaikan --> TutupTiket
    Kunci -. Menunggu .- TutupTiket
    
    TutupTiket --> Buka
    Buka --> NotifSelesai
```
