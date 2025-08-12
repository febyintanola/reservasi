<?php

namespace App\Models;
use CodeIgniter\Model;

class DriverModel extends Model
{
    protected $table = 'drivers';
    protected $allowedFields = ['nama', 'no_hp'];
} 