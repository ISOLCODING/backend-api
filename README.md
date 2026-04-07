# KASIRIN AJA — Backend API

Backend aplikasi kasir berbasis **Laravel 13 + Filament 5 + Laravel Sanctum**.

---

## 📋 Persyaratan

| Software | Versi Minimum |
|----------|--------------|
| PHP      | 8.3+         |
| MySQL    | 8.0+         |
| Composer | 2.x          |
| Node.js  | 20+          |
| Laragon  | 6.x (opsional) |

---

## 🚀 Instalasi

### 1. Clone Repositori

```bash
git clone <repo-url> kasirin-aja
cd kasirin-aja/backend-api
```

### 2. Install Dependencies

```bash
composer install
npm install
```

### 3. Copy & Konfigurasi `.env`

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` sesuai environment:

```env
APP_NAME="KASIRIN AJA"
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=kasirin_aja
DB_USERNAME=root
DB_PASSWORD=

CACHE_STORE=redis
SESSION_DRIVER=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 4. Buat Database

```sql
CREATE DATABASE kasirin_aja CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Migrasi + Seed

```bash
php artisan migrate --seed
```

### 6. Storage Link

```bash
php artisan storage:link
```

### 7. Jalankan Dev Server

```bash
php artisan serve
npm run dev
```

---

## 👤 Kredensial Default

| Role       | Email                      | Password | PIN    |
|------------|----------------------------|----------|--------|
| Super Admin | superadmin@kasirin.test   | password | 111111 |
| Admin       | admin@kasirin.test        | password | 222222 |
| Manager     | manager@kasirin.test      | password | 333333 |
| Kasir 1     | kasir1@kasirin.test       | password | 444444 |
| Kasir 2     | kasir2@kasirin.test       | password | 555555 |

**Admin Panel**: `http://localhost:8000/admin`

---

## 🔌 API Endpoints

Base URL: `http://localhost:8000/api`

### Authentication

| Method | Endpoint           | Auth  | Deskripsi               |
|--------|--------------------|-------|-------------------------|
| POST   | `/auth/login`      | No    | Login dengan email/pass |
| POST   | `/auth/login-pin`  | No    | Login dengan PIN code   |
| POST   | `/auth/logout`     | Yes   | Logout                  |
| GET    | `/auth/me`         | Yes   | Info user aktif         |

### Produk

| Method | Endpoint                      | Auth | Deskripsi                  |
|--------|-------------------------------|------|----------------------------|
| GET    | `/products`                   | Yes  | List produk (search, filter)|
| GET    | `/products/{id}`              | Yes  | Detail produk              |
| GET    | `/products/barcode/{barcode}` | Yes  | Cari produk by barcode     |
| GET    | `/products/low-stock`         | Yes  | Produk stok menipis        |

### Transaksi

| Method | Endpoint                    | Auth | Deskripsi                 |
|--------|-----------------------------|------|---------------------------|
| GET    | `/transactions`             | Yes  | List transaksi             |
| POST   | `/transactions`             | Yes  | Buat transaksi baru        |
| GET    | `/transactions/{id}`        | Yes  | Detail transaksi           |
| POST   | `/transactions/{id}/void`   | Yes  | Void transaksi (admin)     |

### Laporan

| Method | Endpoint                    | Auth | Deskripsi                   |
|--------|-----------------------------|------|-----------------------------|
| GET    | `/reports/daily`            | Yes  | Laporan harian              |
| GET    | `/reports/monthly`          | Yes  | Laporan bulanan             |
| GET    | `/reports/top-products`     | Yes  | Produk terlaris             |
| GET    | `/reports/sales-chart`      | Yes  | Data grafik (week/month/year)|
| GET    | `/reports/profit`           | Yes  | Laporan profit & HPP        |

### Pengaturan

| Method | Endpoint              | Auth | Deskripsi              |
|--------|-----------------------|------|------------------------|
| GET    | `/settings`           | Yes  | Pengaturan toko        |
| GET    | `/settings/printer`   | Yes  | Printer default        |

---

## 🏗️ Arsitektur

```
app/
├── Exports/           # Export Excel (ProductsExport)
├── Filament/
│   ├── Pages/         # Custom Pages (BackupPage)
│   ├── Resources/     # CRUD Resources (Product, Category, dll)
│   └── Widgets/       # Dashboard Widgets
├── Http/
│   ├── Controllers/Api/  # API Controllers
│   └── Requests/Api/     # Form Requests
├── Imports/           # Import Excel (ProductsImport)
└── Models/            # Eloquent Models
database/
├── migrations/        # 11 migration files
└── seeders/           # Seeder files
routes/
├── api.php            # API routes (Sanctum)
└── web.php            # Web routes (Filament)
```

---

## 📦 Packages Utama

| Package                      | Fungsi                              |
|------------------------------|-------------------------------------|
| `filament/filament`          | Admin panel CRUD & Widgets          |
| `laravel/sanctum`            | API authentication (Bearer Token)   |
| `spatie/laravel-permission`  | Role & Permission management        |
| `spatie/laravel-backup`      | Database backup automation          |
| `maatwebsite/excel`          | Import/Export Excel                 |
| `barryvdh/laravel-dompdf`    | Generate PDF laporan                |
| `intervention/image`          | Resize/compress gambar produk       |
| `simplesoftwareio/simple-qrcode` | Generate QR Code               |
| `predis/predis`              | Redis client (cache & session)      |

---

## 🔄 Slash Commands (Workflow)

```bash
/dev-server      # Jalankan Laravel + Vite dev server
/migrate-seed    # Migrate database + seed data
/make-filament   # Buat Filament Component baru
/make-model      # Buat Model + Migration + Seeder
/export-report   # Export laporan Excel/PDF
/manage-stock    # Manage stok produk
/backup-restore  # Backup & restore database
```

---

## 📱 Contoh Penggunaan API

Login:

```bash
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"kasir1@kasirin.test","password":"password"}'
```

Buat transaksi:

```json
POST /api/transactions
Authorization: Bearer {token}

{
  "items": [
    {"product_id": 1, "quantity": 2},
    {"product_id": 5, "quantity": 1}
  ],
  "payment_method": "cash",
  "paid": 50000,
  "discount": 0
}
```

---

## 🛡️ Keamanan

- Semua API endpoint dilindungi Sanctum Bearer Token
- Token kadaluarsa: 30 hari (password login), 12 jam (PIN login)
- Rate limiting: 60 req/menit (public), 120 req/menit (authenticated)
- Password & PIN hashed menggunakan `Hash::make()` (bcrypt)
- Void transaksi hanya bisa dilakukan oleh role `admin` atau `super_admin`
