<?php
namespace App\Models;

use CodeIgniter\Model;

/**
 * Relasi penugasan driver ke booking mobil.
 * Catatan: beberapa controller membaca kolom tambahan seperti start_datetime, notes, reminder_sent.
 * Tambahkan ke allowedFields jika sudah tersedia di DB.
 */
class DriverAssignmentModel extends Model
{
    protected $table = 'driver_assignments';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'car_booking_id',
        'driver_id',
        'mobil_jenis',
        'mobil_plat',
        // 'start_datetime', 'reminder_sent', 'notes'
    ];
}
