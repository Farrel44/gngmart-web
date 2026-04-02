# GnG Mart

Website e-commerce untuk penjualan produk secara online. Proyek sekolah oleh Kelompok 1, XII PPLG 3 — SMK Telkom Purwokerto (TP 2025/2026).

## Tech Stack

| Layer       | Technology                           |
|-------------|--------------------------------------|
| Backend     | Laravel 11 (PHP 8.2+)               |
| Frontend    | Blade, Alpine.js 3, Tailwind CSS 3  |
| Admin Panel | Filament 3                           |
| Build Tool  | Vite 6                               |
| Database    | MySQL                                |
| Payment     | Midtrans (Sandbox/Production)        |
| Search      | Laravel Scout (database driver)      |

## Prerequisites

- PHP >= 8.2 dengan ekstensi: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `gd`, `intl`, `mbstring`, `openssl`, `pdo_mysql`, `xml`, `zip`
- Composer 2.x
- Node.js >= 18 dan npm
- MySQL >= 8.0
- Git

## Installation

### 1. Clone repository

```bash
git clone https://github.com/Farrel44/gngmart-web.git
cd gngmart-web
```

### 2. Install dependencies

```bash
composer install
npm install
```

### 3. Environment setup

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` dan sesuaikan konfigurasi database:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=gngmart_web
DB_USERNAME=root
DB_PASSWORD=
```

Untuk payment gateway Midtrans, isi key sandbox/production:

```env
MIDTRANS_SERVER_KEY=your-server-key
MIDTRANS_CLIENT_KEY=your-client-key
MIDTRANS_IS_PRODUCTION=false
```

### 4. Create database

Buat database MySQL sebelum menjalankan migration:

```sql
CREATE DATABASE gngmart_web CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 5. Run migration and seeder

```bash
php artisan migrate --seed
```

Seeder akan membuat:
- 1 akun admin (`admin@gngmart.com` / `password`)
- 1 akun user test (`test@example.com` / `password`)
- Kategori, produk, carousel slides, dan promosi contoh

### 6. Storage link

```bash
php artisan storage:link
```

### 7. Build frontend assets

Development (dengan HMR):

```bash
npm run dev
```

Production:

```bash
npm run build
```

## Running the Application

### Development (semua service sekaligus)

```bash
composer dev
```

Perintah ini menjalankan secara bersamaan:
- Laravel server (`http://localhost:8000`)
- Queue listener
- Vite dev server (HMR)

### Manual (masing-masing terminal)

```bash
# Terminal 1 — Laravel server
php artisan serve

# Terminal 2 — Queue worker
php artisan queue:listen --tries=1

# Terminal 3 — Vite
npm run dev
```

## Access Points

| URL                        | Keterangan           |
|----------------------------|----------------------|
| `http://localhost:8000`    | Halaman utama (customer) |
| `http://localhost:8000/admin` | Admin panel (Filament)  |

### Default Accounts

| Role  | Email               | Password   |
|-------|---------------------|------------|
| Admin | `admin@gngmart.com` | `password` |
| User  | `test@example.com`  | `password` |

## Project Structure

```
app/
  Filament/          # Admin panel resources, widgets, pages
  Http/Controllers/  # Customer-facing controllers
  Models/            # Eloquent models
  Services/          # Business logic (PriceCalculationService)
  Providers/         # Service providers
  View/Components/   # Blade components
config/              # Application configuration
database/
  factories/         # Model factories
  migrations/        # Database migrations
  seeders/           # Database seeders
resources/views/     # Blade templates
routes/
  web.php            # Customer routes
  auth.php           # Authentication routes
tests/               # PHPUnit tests
```

## Architecture

### Authentication

Aplikasi menggunakan dua sistem auth terpisah:

- **Customer** (`web` guard) — Model `User`, Laravel Breeze, session-based
- **Admin** (`admin` guard) — Model `Admin`, Filament panel di `/admin`

### Domain Models

- **Product** — belongsTo Category, hasMany ProductImages. Scout searchable (full-text). Slug-based routing.
- **Category** — hasMany Products. Slug-based routing.
- **Cart / CartItem** — One cart per user.
- **Order** — State machine: `pending` → `paid` → `processing` → `shipped` → `completed` (dan `cancelled`). HasMany OrderItems, hasOne Payment.
- **Promotion** — Berlaku untuk Products dan/atau Categories via pivot tables. Range tanggal + flag aktif.
- **Payment** — Mendukung COD dan Midtrans. Status: `pending`, `success`, `failed`.

### Pricing

`PriceCalculationService` menangani seluruh logika harga: deteksi promosi, resolusi konflik (diskon tertinggi menang), fallback ke `discount_price`, dan kalkulasi harga efektif.

## Testing

```bash
# Jalankan semua test
./vendor/bin/phpunit

# Jalankan test spesifik
./vendor/bin/phpunit --filter=TestClassName
./vendor/bin/phpunit tests/Feature/CartTest.php
```

Testing menggunakan database terpisah (`gngmart_web_test`). Buat database tersebut sebelum menjalankan test:

```sql
CREATE DATABASE gngmart_web_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## Common Commands

```bash
# Format kode
php artisan pint

# Sync search index
php artisan scout:sync

# Clear cache
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Reset database
php artisan migrate:fresh --seed
```

## Troubleshooting

**Migration gagal dengan "table not found"**

AppServiceProvider membutuhkan tabel `categories`. Jika tabel belum ada (fresh install), jalankan migration terlebih dahulu. Guard `Schema::hasTable()` sudah ditambahkan untuk menangani kasus ini.

**Vite manifest not found**

Jalankan `npm run build` atau `npm run dev` sebelum mengakses halaman.

**Queue jobs tidak diproses**

Pastikan queue listener berjalan: `php artisan queue:listen`. Atau gunakan `composer dev` yang menjalankan semua service.

## Team

Kelompok 1, XII PPLG 3 — SMK Telkom Purwokerto

## License

Proyek ini dibuat untuk keperluan pendidikan.
