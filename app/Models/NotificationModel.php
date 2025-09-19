<?php

namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table='notifications';
    protected $primaryKey='id';
    protected $allowedFields=['user_id','title','message','link','is_read'];
    protected $useTimestamps=true;
}