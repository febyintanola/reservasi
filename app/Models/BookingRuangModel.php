<?php

namespace App\Models;

use CodeIgniter\Model;

class BookingRuangModel extends Model
{
    protected $table = 'room_bookings';
    protected $allowedFields = [
        'acara', 'tanggal', 'jam_mulai', 'jam_selesai',
        'peserta', 'Task', 'kebutuhan', 'keterangan',
        'Procost', 'exptype', 'room_id','user_id', 'status'
    ];
    public function getByUser($userId)
    {
        return $this->where('user_id', $userId)->findAll();
    }
     public function countAllReservations()
    {
        return $this->countAllResults(); // hitung semua baris
    }
}