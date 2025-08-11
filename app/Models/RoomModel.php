<?php

namespace App\Models;

use CodeIgniter\Model;

class RoomModel extends Model
{
    protected $table = 'rooms';
    protected $allowedFields = ['nama_ruangan', 'lokasi', 'kapasitas',"ruangrapat_url"];
    
}
