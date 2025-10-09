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
  
# Aplikasi Reservasi (Ruang Rapat & Kendaraan)

Ini adalah aplikasi internal sederhana untuk mengelola reservasi ruang rapat dan pemesanan kendaraan dinas.
Dibangun dengan CodeIgniter 4 dan kompatibel dengan PHP 8.1+. Aplikasi menyediakan peran (roles) utama: admin, user, dan driver.

## Ringkasan Fungsionalitas

- Reservasi ruang rapat: cek ketersediaan per jam, formulir booking, dan riwayat pengguna.
- Pemesanan kendaraan dinas: pengajuan pemesanan, detail booking, dan riwayat.
- Penugasan driver dan kendaraan oleh admin. Status driver disinkronkan otomatis berdasarkan tugas.
- Dashboard Admin: ringkasan booking dan aksi administratif (assign, edit, laporan).
- Dashboard Driver: daftar tugas hari ini, detail tugas, dan update status (accepted, on-going, done).
- Export laporan ke Excel/PDF (opsional, butuh dependency tambahan).
- Notifikasi email sederhana (opsional), jika konfigurasi email tersedia.

## Persyaratan

- PHP 8.1 atau lebih baru
- Ekstensi PHP: intl, mbstring, json (default), curl (opsional)
- Database: MySQL / MariaDB
- Composer (dependency manager PHP)

Catatan: saya menulis README ini berdasarkan struktur proyek saat ini. Jika Anda mengubah major framework/versi, sesuaikan requirements.

## Instalasi & Konfigurasi (Windows, PowerShell)

1. Clone repository dan masuk ke folder proyek:

```powershell
git clone <repo-url>
cd reservasi
```

2. Install dependency Composer:

```powershell
composer install
```

3. Salin file lingkungan dan atur konfigurasi dasar:

```powershell
cp env .env
```

Buka `.env` dan perbarui nilai penting:

- app.baseURL (contoh: `http://localhost:8080/`)
- database.default.hostname, database.default.database, database.default.username, database.default.password
- email.* jika ingin mengaktifkan notifikasi

Jika Anda menggunakan PowerShell pada Windows dan `cp` tidak tersedia, gunakan:

```powershell
Copy-Item env .env
```

4. Migrasi basis data (jika disediakan migration):

```powershell
php spark migrate
```

Jika tidak ada migration, pastikan tabel berikut ada sesuai skema aplikasi:

- users, user_profile
- rooms, room_bookings
- car_bookings
- drivers, driver_assignments
- password_resets (opsional)
- notifications (opsional)

5. Jalankan server pengembangan:

```powershell
php spark serve
```

Buka browser ke: http://localhost:8080

## Akun & Peran (Roles)

- Pendaftaran: pengguna baru dapat mendaftar melalui form Register (jika route aktif).
- Penentuan role pada registrasi dibuat sederhana di `AuthController::storeRegister()` (cek kode untuk logika divisi => role mapping).
- Akses khusus:
  - Admin: akses area `/admin` dan laporan
  - Driver: akses `/driver/dashboard`

## Rute Penting

Untuk daftar lengkap rute, cek file `app/Config/Routes.php`. Berikut ringkasan rute yang sering dipakai:

- Public: `/login`, `/register`, `/forgot-password`, `/reset-password/{token}`
- User: `/`, `/home`, `/ruang` (check/booking), `/car/form`, `/user/profile`, `/history`
- Admin: `/admin`, `/admin/car`, `/admin/ruang`, `/admin/reports` (export)
- Driver: `/driver/dashboard`, `/driver/jobs/{bookingId}`

Gunakan POST untuk aksi seperti save, assign, dan update status.

## Export Laporan (opsional)

Untuk fitur export Excel/PDF gunakan paket:

- Excel: phpoffice/phpspreadsheet
- PDF: dompdf/dompdf

Install via Composer bila diperlukan:

```powershell
composer require phpoffice/phpspreadsheet:^1.29 ; composer require dompdf/dompdf:^2.0
```

Fungsi export ada di `app/Libraries/ExportService.php`.

## Menjalankan Unit Tests (PHPUnit) — Windows

Project sudah dilengkapi skeleton `phpunit.xml.dist`. Untuk menjalankan unit test di Windows (PowerShell):

```powershell
vendor\bin\phpunit -c phpunit.xml.dist
```

Jika Anda ingin menjalankan satu file test:

```powershell
vendor\bin\phpunit tests\unit\SomeTest.php -c phpunit.xml.dist
```

Coverage (opsional) membutuhkan Xdebug atau PCOV. Contoh perintah (dengan Xdebug aktif):

```powershell
vendor\bin\phpunit --coverage-html writable\coverage -c phpunit.xml.dist
```

Catatan: Pastikan group `tests` environment (mis. `.env.testing`) diatur jika Anda memerlukan konfigurasi database khusus untuk test.

## Debugging & Troubleshooting

- Error koneksi DB: periksa konfigurasi di `.env` dan `app/Config/Database.php`.
- Email tidak terkirim: cek konfigurasi SMTP di `.env` atau `app/Config/Email.php`.
- Permasalahan akses file/uploads: periksa permission pada folder `writable/`.

## Struktur Direktori (singkat)

- `app/Controllers` – controller aplikasi (Admin, Auth, CarController, RoomController, dsb.)
- `app/Models` – model database (CarModel, BookingRuangModel, dsb.)
- `app/Libraries` – layanan tambahan (ExportService, NotificationService)
- `app/Views` – tampilan
- `public/` – web root untuk assets dan `index.php`

## Catatan Pengembang

- Beberapa bagian menggunakan normalisasi hasil query (array vs object) agar kompatibel di beberapa environment.
- Status driver disinkronkan saat booking berubah status. Lihat implementasi di model `DriverAssignmentModel` atau lokasi update status booking.
- Jika Anda butuh fitur export/notification, instal dependency composer yang diperlukan.

## Kontribusi

- Fork, buat branch baru, lalu ajukan Pull Request. Ikuti standar coding yang ada di proyek.

## Lisensi

MIT
