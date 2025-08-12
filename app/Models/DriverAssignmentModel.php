<?php
namespace App\Models;

use CodeIgniter\Model;

class DriverAssignmentModel extends Model
{
    protected $table = 'driver_assignments';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'car_booking_id',
        'driver_id',
        'mobil_jenis',
        'mobil_plat',
    ];
}
