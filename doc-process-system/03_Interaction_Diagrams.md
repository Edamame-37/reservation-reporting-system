# Interaction Diagrams (Diagram Interaksi)

Kategori ini merupakan sub-bagian dari Behavioral Diagrams yang berfokus pada pertukaran pesan antar objek, fungsi, atau entitas sistem secara teknikal dan sekuensial.

## 1. Sequence Diagram
Diagram interaksi terpenting bagi *developer*. Menggambarkan urutan kejadian berdasarkan garis waktu (vertikal) dan pesan yang dikirim (horizontal).

**Skenario: Pengajuan Reservasi Anti-Bentrok (Concurrency Lock)**
**Penjelasan:**
- Memetakan pergerakan request dari `User` ke `Controller` ke `Database` dan membalikkan respon.
- Menunjukkan aktivasi fungsi `lockForUpdate()` di dalam *database transaction* untuk mengunci baris data agar tidak terjadi *double-booking* saat *concurrent request*.

```mermaid
sequenceDiagram
    autonumber
    actor U as Pengguna
    participant UI as View (Blade)
    participant Ctrl as ReservationController
    participant DB as MySQL Database

    U->>UI: Mengisi form reservasi (Start, End, Fasilitas)
    UI->>Ctrl: POST /reservations (submit data)
    
    activate Ctrl
    Ctrl->>Ctrl: Validasi Input (FormRequest)
    
    Ctrl->>DB: DB::transaction() BEGIN
    activate DB
    Ctrl->>DB: SELECT * FROM reservations WHERE facility_id AND jadwal_irisan FOR UPDATE
    
    alt Jadwal Bentrok
        DB-->>Ctrl: Data Ditemukan (Sudah Dipesan)
        Ctrl->>DB: DB::rollBack()
        Ctrl-->>UI: Redirect Back with Error "Jadwal Penuh"
        UI-->>U: Menampilkan Pesan Error
    else Jadwal Tersedia
        DB-->>Ctrl: Data Kosong (Aman)
        Ctrl->>DB: INSERT INTO reservations (status='pending')
        DB-->>Ctrl: OK
        Ctrl->>DB: DB::commit()
        deactivate DB
        
        Ctrl-->>UI: Redirect to Dashboard with Success
        UI-->>U: Menampilkan Tiket Reservasi Pending
    end
    deactivate Ctrl
```

## 2. Communication Diagram (Peta Interaksi)
Fokus pada struktur spasial atau peta koneksi jaringan antar objek (menggunakan penomoran) alih-alih urutan waktu linear.

**Skenario: Persetujuan Reservasi oleh Petugas**
**Penjelasan:**
- Interaksi dimulai dari Petugas (1).
- Panggilan berurutan ke Controller (1.1), Model (1.1.1), Database (1.1.2), lalu kembali ke View (1.2).

```mermaid
flowchart TD
    Officer([Petugas])
    View[Dashboard Petugas View]
    Ctrl[ReservationManagement Controller]
    Model[Reservation Model]
    DB[(Database)]

    Officer -- "1. Klik 'Approve'" --> View
    View -- "1.1 POST /admin/reservations/{id}/approve" --> Ctrl
    Ctrl -- "1.1.1 update(['status' => 'approved'])" --> Model
    Model -- "1.1.2 UPDATE reservations" --> DB
    Ctrl -- "1.2 Return Redirect" --> View
    View -- "1.3 Tampilkan Pesan Sukses" --> Officer
```

## 3. Interaction Overview Diagram (Gambaran Besar Interaksi)
Menunjukkan gambaran besar aliran kontrol yang menggabungkan elemen *Activity Diagram* dengan *Sequence Diagram*.

**Skenario: Siklus Laporan Kerusakan**
**Penjelasan:**
- Menggambarkan tiga tahap besar: `Pembuatan Laporan`, `Pemrosesan Laporan`, dan `Penyelesaian Laporan`.
- Masing-masing node mewakili blok interaksi yang bisa dibedah menjadi *Sequence Diagram* tersendiri.

```mermaid
flowchart TD
    Start((Mulai)) --> Lapor[Interaction: Pengguna Membuat Laporan]
    
    Lapor --> Cek{Status Laporan?}
    
    Cek -- Baru --> Proses[Interaction: Petugas Memproses Laporan]
    Proses --> Kunci[Action: Sistem Memblokir Fasilitas Sementara]
    
    Kunci --> Selesai[Interaction: Petugas Menutup Laporan]
    Selesai --> Buka[Action: Sistem Membuka Blokir Fasilitas]
    
    Buka --> End((Selesai))
```
