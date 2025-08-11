<?php

namespace App\Models;

use CodeIgniter\Model;

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
        'status'              // tambahkan jika ada field status di tabel
    ];

    public function getByUser($userId)
    {
        return $this->where('user_id', $userId)->findAll();
    }
    public function getAllBookings()
    {
        return $this->orderBy('tanggal', 'DESC')->findAll();
    }
}
