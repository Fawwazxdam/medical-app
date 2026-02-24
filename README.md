# Medical Appointment System

Sistem manajemen appointment klinik/dokter berbasis web menggunakan Laravel dan Filament.

## Fitur Utama

- **Manajemen Appointment** - CS dapat membuat dan melihat daftar appointment pasien
- **Doctor Console** - Dokter dapat melihat daftar pasien hari ini dan melakukan pemeriksaan
- **Rekam Medis** - Otomatis tersimpan setelah pemeriksaan selesai
- **Role-based Access** - Akses halaman berdasarkan role (Admin, CS, Doctor)

## Role & Akses

| Role | Akses Halaman |
|------|---------------|
| Admin | Semua halaman |
| CS (Customer Service) | Buat Appointment, Daftar Appointment |
| Doctor | Doctor Console |

## Alur Aplikasi

```
┌─────────────────────────────────────────────────────────────────┐
│                        ALUR APPOINTMENT                         │
└─────────────────────────────────────────────────────────────────┘

1. CS Membuat Appointment
   ┌─────────┐     ┌──────────────┐     ┌────────────┐
   │  Pasien │────▶│      CS      │────▶│  Booking   │
   │  Datang │     │ Buat Appointment    │  (Waiting) │
   └─────────┘     └──────────────┘     └────────────┘
                         │
                         ▼
                  Pilih Pasien (baru/lama)
                  Pilih Dokter
                  Tentukan Tanggal & Waktu

2. Dokter Memeriksa Pasien
   ┌────────────┐     ┌──────────────┐     ┌────────────┐
   │  Booking   │────▶│    Doctor    │────▶│  Booking   │
   │  (Waiting) │     │ Mulai Periksa│     │ (Finished) │
   └────────────┘     └──────────────┘     └────────────┘
                            │
                            ▼
                     Isi Diagnosis & Notes
                            │
                            ▼
                    ┌───────────────┐
                    │ Medical Record│
                    │   Tersimpan   │
                    └───────────────┘

3. Status Booking
   ┌──────────┐  ──────────────────────────────────────────────▶
   │ Waiting  │  Pasien menunggu giliran
   ├──────────┤
   │InProgress│  Sedang diperiksa dokter (opsional)
   ├──────────┤
   │ Finished │  Pemeriksaan selesai, rekam medis tersimpan
   ├──────────┤
   │Cancelled │  Appointment dibatalkan
   └──────────┘
```

## Teknologi

- **Backend**: Laravel 11
- **Admin Panel**: Filament PHP v3
- **Database**: PostgreSQL
- **Frontend**: Tailwind CSS

## Instalasi

### Prasyarat

- PHP >= 8.2
- Composer
- Node.js & NPM
- PostgreSQL

### Langkah Instalasi

1. **Clone Repository**
   ```bash
   git clone <repository-url>
   cd medical-app
   ```

2. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Setup Environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Konfigurasi Database** (edit file `.env`)
   ```env
   # SQLite (default)
   DB_CONNECTION=sqlite
   
   # atau MySQL
   DB_CONNECTION=postgres
   DB_HOST=127.0.0.1
   DB_PORT=5432
   DB_DATABASE=medical_app
   DB_USERNAME=
   DB_PASSWORD=
   ```

5. **Jalankan Migrasi & Seeder**
   ```bash
   php artisan migrate
   php artisan db:seed --class=UserSeeder
   ```

6. **Build Assets**
   ```bash
   npm run build
   ```

7. **Jalankan Aplikasi**
   ```bash
   php artisan serve
   ```

8. **Akses Aplikasi**
   
   Buka browser: `http://localhost:8000/admin`

## Akun Default

Setelah menjalankan seeder, gunakan akun berikut untuk login:

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@g.c | password |
| Doctor | doctor@g.c | password |

> **Catatan**: Password default adalah "password" (dari UserFactory). Pastikan untuk mengganti password pada environment production.

## Struktur Model

```
User (Admin/CS/Doctor)
  │
  ├── hasMany ──▶ Booking (sebagai doctor)
  │
  └── hasMany ──▶ MedicalRecord (sebagai doctor)

Patient
  │
  ├── hasMany ──▶ Booking
  │
  └── hasMany ──▶ MedicalRecord

Booking (Appointment)
  │
  ├── belongsTo ──▶ Patient
  ├── belongsTo ──▶ User (Doctor)
  └── hasOne ──▶ MedicalRecord

MedicalRecord
  │
  ├── belongsTo ──▶ Booking
  ├── belongsTo ──▶ Patient
  └── belongsTo ──▶ User (Doctor)
```

## Pengembangan

### Membuat CS User

Untuk membuat user CS baru, gunakan tinker atau buat seeder:

```bash
php artisan tinker
```

```php
\App\Models\User::create([
    'name' => 'CS Name',
    'email' => 'cs@g.c',
    'password' => bcrypt('password'),
    'role' => 'cs',
]);
```

### Membuat Doctor User

```php
\App\Models\User::create([
    'name' => 'Dr. Name',
    'email' => 'doctor2@g.c',
    'password' => bcrypt('password'),
    'role' => 'doctor',
]);
```

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
