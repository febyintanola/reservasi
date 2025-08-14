<?php

namespace App\Models;
use CodeIgniter\Model;

class DriverModel extends Model
{
    protected $table = 'drivers';
    // Include user_id to link driver record with a login account (users.id)
    // Include sim as it's used in controller when creating/updating drivers
    protected $allowedFields = ['nama', 'no_hp', 'foto_url', 'status', 'user_id', 'sim'];
} 