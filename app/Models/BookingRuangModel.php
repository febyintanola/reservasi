<?php

namespace App\Models;

use CodeIgniter\Model;

/** Model untuk tabel room_bookings (reservasi ruang rapat). */
class BookingRuangModel extends Model
{
    protected $table = 'room_bookings';
    protected $allowedFields = [
        'acara', 'tanggal', 'jam_mulai', 'jam_selesai',
        'peserta', 'Task', 'kebutuhan', 'keterangan',
        'Procost', 'exptype', 'room_id','user_id', 'status'
    ];
    /** Ambil semua booking ruang milik user tertentu. */
    public function getByUser($userId)
    {
        return $this->where('user_id', $userId)->findAll();
    }
     /** Hitung semua data reservasi (untuk statistik umum). */
     public function countAllReservations()
    {
        return $this->countAllResults(); // hitung semua baris
    }
}