# Aplikasi Reservasi (Ruang Rapat & Kendaraan)

Aplikasi internal untuk mengelola reservasi ruang rapat dan pemesanan kendaraan dinas.
Dibangun dengan CodeIgniter 4 (PHP 8.1+). Mendukung role Admin dan User.

## Fitur Utama

- Reservasi ruang rapat (cek ketersediaan per jam, form booking, riwayat)
- Pemesanan kendaraan (isi form, lihat detail, riwayat)
- Penugasan driver & kendaraan oleh admin (assign, sinkron status driver)
- Dashboard Admin (ringkasan booking + detail)
- Laporan & Export (Excel/PDF) untuk ruang/ken. [butuh dependency export]
- Notifikasi email (opsional) ke driver saat penugasan

## Persyaratan

- PHP 8.1 atau lebih baru
- Ekstensi PHP: intl, mbstring, json (default), curl (opsional)
- Database: MySQL/MariaDB
- Composer untuk mengelola dependency

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

## Rute Penting

Untuk daftar lengkap rute, cek file `app/Config/Routes.php`. Berikut ringkasan rute yang sering dipakai:

- Public: `/login`, `/register`, `/forgot-password`, `/reset-password/{token}`
- User: `/`, `/home`, `/ruang` (check/booking), `/car/form`, `/user/profile`, `/history`
- Admin: `/admin`, `/admin/car`, `/admin/ruang`, `/admin/reports` (export)

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
