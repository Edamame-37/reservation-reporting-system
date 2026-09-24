# 05. Entity Relationship Diagram (ERD)

## Tujuan
Memvisualisasikan struktur tabel, kolom, tipe data, dan hubungan (relasi/kardinalitas) antar entitas persisten di dalam basis data MySQL CAVA, sesuai dengan skema migrasi aktual.

## Analisis Tabel & Kolom Utama

### 1. `users` (Tabel Pengguna)
Penyimpan seluruh entitas sivitas akademika.
- **Primary Key:** `id`.
- Atribut penting: `email` (Unik), `identity_number` (NIM/NIP), `role` (enum: admin, petugas, mahasiswa, dll), `status` (pending/active/rejected).

### 2. `facilities` (Tabel Master Fasilitas)
Penyimpan data ruangan atau peralatan.
- **Primary Key:** `id`.
- Atribut penting: `code` (Unik), `category` (enum), `capacity` (int), `equipment` (JSON data pendukung), `status` (aktif/dalam perbaikan/nonaktif).

### 3. `reservations` (Tabel Transaksi Peminjaman)
- **Primary Key:** `id`.
- **Foreign Keys:**
  - `user_id` merujuk ke `users(id)` (Siapa yang meminjam).
  - `facility_id` merujuk ke `facilities(id)` (Ruang apa yang dipinjam).
  - `reviewed_by` merujuk ke `users(id)` (Petugas mana yang me-review).
- Atribut penting: `ticket_code` (Unik), `start_time`, `end_time`, `status`.

### 4. `damage_reports` (Tabel Tiket Kerusakan)
- **Primary Key:** `id`.
- **Foreign Keys:**
  - `user_id` merujuk ke `users(id)`.
  - `facility_id` merujuk ke `facilities(id)`.
  - `handled_by` merujuk ke `users(id)` (Teknisi/Petugas yang menangani).
- Atribut penting: `report_code` (Unik), `attachment_photo`, `status`, `is_facility_locked`.

## Penjelasan Relasi (Kardinalitas)
- Sebuah `User` bisa memiliki `0` hingga banyak (`N`) `Reservation` dan `DamageReport`.
- Sebuah `Facility` bisa terkait dengan `0` hingga banyak (`N`) `Reservation` dan `DamageReport`.
- Hubungan tabel transaksi (`reservations` dan `damage_reports`) terhadap tabel referensi (master) bersifat wajb, karena ada aturan `Cascade On Delete` di tingkat database.

---

## Kode Diagram Mermaid (erDiagram)

```mermaid
erDiagram
    USERS {
        bigint id PK
        varchar(150) name
        varchar(150) email "UNIQUE"
        varchar(255) password
        varchar(50) identity_number "INDEX"
        enum role "admin, petugas, mahasiswa..."
        enum status "pending, active, rejected"
        timestamp created_at
    }

    FACILITIES {
        bigint id PK
        varchar(30) code "UNIQUE"
        varchar(150) name
        enum category "lab, kelas, auditorium..."
        int capacity
        json equipment
        enum status "aktif, dalam perbaikan, nonaktif"
        timestamp created_at
    }

    RESERVATIONS {
        bigint id PK
        varchar(40) ticket_code "UNIQUE"
        bigint user_id FK
        bigint facility_id FK
        date reservation_date "INDEX"
        time start_time
        time end_time
        enum status "pending, approved, rejected, cancelled..."
        bigint reviewed_by FK "NULLABLE"
    }

    DAMAGE_REPORTS {
        bigint id PK
        varchar(40) report_code "UNIQUE"
        bigint user_id FK
        bigint facility_id FK
        varchar(100) category
        varchar(255) attachment_photo "NULLABLE"
        enum status "baru, diproses, selesai, ditolak"
        boolean is_facility_locked
        bigint handled_by FK "NULLABLE"
    }

    %% Relasi (Kardinalitas)
    USERS ||--o{ RESERVATIONS : "mengajukan"
    USERS ||--o{ DAMAGE_REPORTS : "melaporkan"
    
    USERS ||--o{ RESERVATIONS : "menyetujui (reviewed_by)"
    USERS ||--o{ DAMAGE_REPORTS : "menangani (handled_by)"
    
    FACILITIES ||--o{ RESERVATIONS : "dipinjam_dalam"
    FACILITIES ||--o{ DAMAGE_REPORTS : "tercatat_dalam"
```
