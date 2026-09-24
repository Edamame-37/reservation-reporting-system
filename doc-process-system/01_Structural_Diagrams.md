# Structural Diagrams (Diagram Struktur)

Kategori ini memodelkan elemen-elemen statis dari sistem CAVA, seperti kelas, objek, komponen, dan node fisik. Diagram-diagram di bawah ini menjadi panduan arsitektural bagi tim pengembang dalam membangun fondasi aplikasi.

## 1. Class Diagram
Class Diagram memetakan entitas basis data (Models) dan struktur pembangun sistem menggunakan prinsip Object-Oriented Programming (OOP) di Laravel.

**Penjelasan:**
- `User` berelasi dengan `Role` (melalui Spatie Permission).
- `User` memiliki banyak (`hasMany`) `Reservation` dan `DamageReport`.
- `Facility` memiliki banyak `Reservation` dan `DamageReport`.
- Menggunakan visibilitas: `+` (public), `-` (private/protected).

```mermaid
classDiagram
    class User {
        +int id
        +string name
        +string email
        -string password
        +string identity_number
        +string status
        +createReservation()
        +reportDamage()
    }

    class Role {
        +int id
        +string name
        +string guard_name
    }

    class Facility {
        +int id
        +string name
        +string type
        +int capacity
        +string status_aktif
        +checkAvailability()
        +updateStatus()
    }

    class Reservation {
        +int id
        +int user_id
        +int facility_id
        +datetime start_time
        +datetime end_time
        +string status
        +string tujuan
        +approve()
        +reject()
        +cancel()
    }

    class DamageReport {
        +int id
        +int user_id
        +int facility_id
        +string category
        +string description
        +string photo_path
        +string status
        +processReport()
        +resolveReport()
    }

    User "1" -- "*" Reservation : hasMany
    User "1" -- "*" DamageReport : hasMany
    User "*" -- "*" Role : hasRole
    Facility "1" -- "*" Reservation : hasMany
    Facility "1" -- "*" DamageReport : hasMany
    Reservation "1" -- "0..1" DamageReport : relatedTo
```

## 2. Component Diagram
Menggambarkan bagaimana modul-modul perangkat lunak dipecah dan bagaimana mereka saling bergantung (dependency).

**Penjelasan:**
- Sistem dibagi menjadi 3 layar (layer): Presentation (UI), Business Logic (Backend), dan Data (Database/Storage).
- Komponen Frontend bergantung pada Backend API.
- Backend bergantung pada Autentikasi (Breeze) dan Otorisasi (Spatie), serta terhubung ke MySQL.

```mermaid
flowchart TD
    subgraph Presentation Layer
        UI[Frontend UI\nBlade, Tailwind, AlpineJS]
        Calendar[FullCalendar.js Component]
    end

    subgraph Business Logic Layer
        Auth[Auth Module\nLaravel Breeze]
        RBAC[Authorization Module\nSpatie Permission]
        ResCtrl[Reservation Controller]
        RepCtrl[Report Controller]
        FacCtrl[Facility Controller]
    end

    subgraph Data Layer
        DB[(MySQL Database)]
        Storage[Local File Storage]
    end

    UI --> Auth
    UI --> ResCtrl
    UI --> RepCtrl
    Calendar --> ResCtrl
    
    Auth --> RBAC
    ResCtrl --> DB
    RepCtrl --> DB
    FacCtrl --> DB
    RepCtrl --> Storage
```

## 3. Deployment Diagram
Memetakan arsitektur eksekusi sistem, yaitu letak instalasi komponen perangkat lunak (Artifacts) ke dalam perangkat keras (Nodes).

**Penjelasan:**
- **Client Node:** Perangkat pengguna (PC/Mobile) mengakses lewat protokol HTTPS.
- **Web Server Node:** Menjalankan sistem operasi (Linux/Windows) dengan Apache/Nginx yang menampung *source code* Laravel.
- **Database Node:** Server basis data yang beroperasi pada port 3306.

```mermaid
flowchart TD
    subgraph Client Device
        Browser[Web Browser / Mobile Browser]
    end

    subgraph Web Server Node
        Apache[Apache / Nginx Web Server]
        PHP[PHP 8.2 Runtime]
        Laravel[CAVA Laravel Application]
        
        Apache --> PHP
        PHP --> Laravel
    end

    subgraph Database Server Node
        MySQL[(MySQL 8 Database)]
    end

    Browser -- HTTPS --> Apache
    Laravel -- TCP/IP : 3306 --> MySQL
```

## 4. Package Diagram
Mengelompokkan elemen-elemen sistem ke dalam folder/paket tingkat tinggi untuk mengatur kompleksitas aplikasi MVC.

**Penjelasan:**
- Menggambarkan struktur MVC (Model, View, Controller) di Laravel.
- Paket `Controllers` dibagi lagi menjadi sub-paket berdasarkan aktor/domain.

```mermaid
flowchart TD
    subgraph app
        subgraph Http
            subgraph Controllers
                AdminC[Admin Controllers]
                UserC[User Controllers]
                AuthC[Auth Controllers]
                OfficerC[Petugas Controllers]
            end
        end
        subgraph Models
            Entities[Eloquent Models]
        end
    end

    subgraph resources
        subgraph views
            AdminV[Admin Views]
            UserV[User Views]
            AuthV[Auth Views]
        end
    end

    Controllers -. uses .-> Models
    Controllers -. renders .-> views
```
