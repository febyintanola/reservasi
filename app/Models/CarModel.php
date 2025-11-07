<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model untuk tabel car_bookings (pemesanan mobil)
 */
class CarModel extends Model
{
    protected $table = 'car_bookings';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;

    protected $allowedFields = [
        'nama',
        'user_id',
        'tujuan',
        'pengikut',
        'tanggal_pergi',      
        'tanggal_pulang',     
        'jumlah_hari',
        'keperluan',
        'nama_pekerjaan',	
        'project_costing',
        'task_number',
        'expenditure_type',
        'expenditure_org',
        'status',             // status booking mobil
        'driver_id',          // relasi ke driver yang ditugaskan
        'car_id'              // relasi ke mobil (jika ada tabel daftar mobil)
    ];

    /** Ambil semua booking mobil milik user tertentu. */
    public function getByUser($userId)
    {
        return $this->where('user_id', $userId)->orderBy('tanggal_pergi', 'DESC')->findAll();
    }
    /** Ambil semua booking mobil untuk listing admin (urut terbaru). */
    public function getAllBookings()
    {
    // gunakan tanggal_pergi sebagai acuan urutan
    return $this->orderBy('tanggal_pergi', 'DESC')->findAll();
    }
}
