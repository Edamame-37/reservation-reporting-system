# Behavioral Diagrams (Diagram Perilaku)

Kategori ini memodelkan aspek dinamis dari sistem CAVA, yaitu interaksi pengguna, proses alur bisnis, serta siklus hidup objek seiring berjalannya waktu.

## 1. Use Case Diagram
Mendefinisikan batas ruang lingkup sistem dan fungsionalitas yang disediakan berdasarkan perspektif aktor (pengguna).

**Penjelasan:**
- Terdapat 4 aktor: `Pengunjung`, `Pengguna Sivitas`, `Petugas`, `Admin`.
- Aktor mewarisi hak akses (misal: Pengguna Sivitas juga bisa melakukan apa yang bisa dilakukan Pengunjung).
- Fitur "Mengajukan Reservasi" **wajib** (`<<include>>`) "Login".
- Fitur "Melaporkan Kerusakan" bisa diperluas (`<<extend>>`) dengan "Unggah Foto Bukti".

```mermaid
flowchart LR
    %% Actors
    Guest([Pengunjung])
    User([Pengguna Sivitas])
    Officer([Petugas])
    Admin([Admin])

    %% Use Cases
    UC_Katalog(Melihat Katalog Fasilitas)
    UC_Login(Login)
    UC_Reservasi(Mengajukan Reservasi)
    UC_Lapor(Melaporkan Kerusakan)
    UC_Upload(Unggah Foto Bukti)
    UC_Approve(Approve/Reject Reservasi)
    UC_Master(Kelola Master Fasilitas)

    %% Relationships
    Guest --> UC_Katalog
    User --> UC_Katalog
    User --> UC_Reservasi
    User --> UC_Lapor

    Officer --> UC_Approve
    Admin --> UC_Master

    UC_Reservasi -. "<<include>>" .-> UC_Login
    UC_Lapor -. "<<include>>" .-> UC_Login
    UC_Upload -. "<<extend>>" .-> UC_Lapor
```

## 2. Activity Diagram
Memodelkan logika prosedural, aliran kerja (*workflow*), dan proses bisnis secara berurutan. Di bawah ini adalah Alur Pengajuan Reservasi.

**Penjelasan:**
- Dimulai dari pengguna memilih fasilitas dan tanggal.
- Terjadi *Decision* (Percabangan) oleh sistem untuk mengecek status ketersediaan.
- Terdapat aksi asinkron/paralel (*Fork/Join*) saat notifikasi dikirimkan kepada pengguna dan petugas setelah status diubah.

```mermaid
stateDiagram-v2
    [*] --> PilihFasilitas: Pengguna masuk form
    PilihFasilitas --> IsiJadwal: Pilih tanggal & jam (Max 20.00)
    IsiJadwal --> ValidasiSistem: Submit form
    
    state ValidasiSistem {
        state if_state <<choice>>
        [*] --> if_state
        if_state --> JadwalBentrok: Tidak Tersedia
        if_state --> SimpanPending: Tersedia (lockForUpdate)
    }
    
    JadwalBentrok --> PilihFasilitas: Redirect (Error Message)
    SimpanPending --> AntreanPetugas
    
    state AntreanPetugas {
        state approve_choice <<choice>>
        [*] --> approve_choice
        approve_choice --> Disetujui: Petugas klik Approve
        approve_choice --> Ditolak: Petugas klik Reject
    }
    
    Disetujui --> KirimNotif
    Ditolak --> KirimNotif
    
    KirimNotif --> [*]
```

## 3. State Machine Diagram (Statechart)
Memodelkan siklus hidup (*lifecycle*) dari sebuah objek (misalnya Objek Reservasi dan Objek Laporan Kerusakan) akibat dari berbagai kejadian (*events*).

**Penjelasan:**
- Objek **Reservasi**: Status default adalah `Pending`. Jika disetujui menjadi `Approved`, jika ditolak menjadi `Rejected`. Reservasi bisa di-batal mandiri menjadi `Cancelled`.
- Objek **Fasilitas**: Berubah dari `Tersedia` menjadi `Dalam Perbaikan` jika ada pelaporan yang disetujui.

```mermaid
stateDiagram-v2
    %% Siklus Hidup Reservasi
    state "Siklus Hidup Objek Reservasi" as ReservasiLifecycle {
        [*] --> Pending : Dibuat (Submit Form)
        Pending --> Approved : Trigger Petugas (Setuju)
        Pending --> Rejected : Trigger Petugas (Tolak)
        Pending --> Cancelled : Trigger Pengguna (Batal H-1)
        
        Approved --> Cancelled : Trigger Petugas (Override/Darurat)
        Approved --> Completed : Trigger Waktu Terlewati
        
        Rejected --> [*]
        Cancelled --> [*]
        Completed --> [*]
    }
```

```mermaid
stateDiagram-v2
    %% Siklus Hidup Fasilitas
    state "Siklus Hidup Status Fasilitas" as FacilityLifecycle {
        [*] --> Tersedia
        Tersedia --> Dipesan : Reservasi Masuk
        Dipesan --> Tersedia : Waktu Selesai / Batal
        
        Tersedia --> DalamPerbaikan : Laporan Kerusakan Diproses
        DalamPerbaikan --> Tersedia : Laporan Diselesaikan
        
        Tersedia --> NonAktif : Trigger Admin (Disable)
        NonAktif --> Tersedia : Trigger Admin (Enable)
    }
```
