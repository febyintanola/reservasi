# Aplikasi Reservasi (Ruang Rapat & Kendaraan)

Aplikasi internal untuk mengelola reservasi ruang rapat dan pemesanan kendaraan dinas.
Dibangun dengan CodeIgniter 4 (PHP 8.1+). Mendukung role Admin, User, dan Driver.

## Fitur Utama

- Reservasi ruang rapat (cek ketersediaan per jam, form booking, riwayat)
- Pemesanan kendaraan (isi form, lihat detail, riwayat)
- Penugasan driver & kendaraan oleh admin (assign, sinkron status driver)
- Dashboard Admin (ringkasan booking + detail)
- Dashboard Driver (tugas hari ini/berjalan/riwayat, update status, unggah foto opsional)
- Laporan & Export (Excel/PDF) untuk ruang/ken. [butuh dependency export]
- Notifikasi email (opsional) ke driver saat penugasan

## Persyaratan

- PHP 8.1 atau lebih baru
- Ekstensi PHP: intl, mbstring, json (default), curl (opsional)
- Database: MySQL/MariaDB
- Composer untuk mengelola dependency

## Instalasi Cepat

1) Clone repo ini, lalu install dependency composer

2) Salin file `env` menjadi `.env` dan atur konfigurasi dasar:

- App URL
  - `app.baseURL = 'http://localhost:8080/'` (sesuaikan)
- Database
  - `database.default.hostname`, `database.default.database`, `database.default.username`, `database.default.password`
- Email (opsional, untuk notifikasi)
  - `email.fromEmail`, `email.fromName` jika memakai `NotificationService`

3) Migrasi skema (bila tersedia) dan siapkan tabel yang dipakai aplikasi: 
- `users`, `user_profile`
- `room_bookings`, `rooms`
- `car_bookings`
- `drivers`, `driver_assignments`
- `password_resets` (untuk fitur lupa password)
- `notifications` (jika mencatat notifikasi)

4) Jalankan server pengembangan:

```powershell
php spark serve
```

Lalu buka di browser: http://localhost:8080

## Akun & Role

- User umum mendaftar via menu Register
- Role otomatis berdasarkan divisi (contoh di `AuthController::storeRegister()`):
  - `umum` => admin
  - `driver` => driver
  - selain itu => user
- Admin bisa mengakses: `/admin`
- Driver mengakses: `/driver/dashboard`

## Rute Penting (ringkas)

- Public
  - `GET /login`, `POST /login`
  - `GET /register`, `POST /register`
  - Lupa/Reset password: `GET/POST /forgot-password`, `GET /reset-password/{token}`, `POST /reset-password`

- User (login & role:user)
  - Home: `GET /` atau `/home`
  - Ruang: `GET /ruang`, `POST /ruang/check`, `GET /ruang/booking-form`, `POST /ruang/save-booking`
  - Mobil: `GET /car/form`, `POST /car/save`
  - Profil: `GET /user/profile`, `POST /profile/update`
  - Riwayat: `GET /history`

- Admin (login & role:admin)
  - Dashboard: `GET /admin`
  - Car bookings Admin: `GET /admin/car`, `GET /admin/car/detail/{id}`, CRUD dasar
  - Assign Driver/Mobil: `GET /admin/car/assign/{bookingId}`, `POST /admin/car/assign_save/{bookingId}`
  - Ruang Admin: `GET /admin/ruang`, `GET /admin/ruang/create`, `POST /admin/ruang/store`, edit/update
  - Laporan: `GET /admin/reports` + export Excel/PDF

- Driver (login & role:driver)
  - Dashboard: `GET /driver/dashboard`
  - Detail tugas: `GET /driver/jobs/{bookingId}`
  - Update status tugas: `POST /driver/jobs/{bookingId}/status`

Detail lengkap ada di `app/Config/Routes.php`.

## Export Excel/PDF (opsional)

Fitur export di `app/Libraries/ExportService.php` memerlukan paket berikut:

- Excel: `phpoffice/phpspreadsheet`
- PDF: `dompdf/dompdf`

Install via Composer:

```powershell
composer require phpoffice/phpspreadsheet:^1.29 ; composer require dompdf/dompdf:^2.0
```

## Struktur Direktori (ringkas)

- `app/Controllers` – logika HTTP (Admin, User, Driver, Reports, dll.)
- `app/Models` – akses database (CarModel, BookingRuangModel, dsb.)
- `app/Libraries` – layanan (ExportService, NotificationService)
- `app/Filters` – filter auth/role
- `app/Views` – tampilan
- `public/` – web root (index.php, assets)

## Catatan Implementasi

- Beberapa tempat menggunakan normalisasi untuk hasil query (array vs object) agar aman di semua environment.
- Status driver disinkronkan ketika booking mobil berubah status (accepted/ongoing => On Duty, done/rejected => Available jika tidak ada tugas lain yang berjalan).
- Notifikasi email driver (opsional) lewat `NotificationService` – pastikan konfigurasi email di `.env` atau di `Config\\Email.php`.
- Di routes ada entri `RuangRapatController` (opsional). Jika controller tersebut belum tersedia, nonaktifkan rute terkait atau tambahkan controllernya.

## Pengembangan

- Jalankan server dev: `php spark serve`
- Unit test (jika tersedia): `composer test`

## Lisensi

MIT (mengikuti lisensi CodeIgniter Starter). Lihat file LICENSE.
