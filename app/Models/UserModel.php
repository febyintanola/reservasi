<?php
namespace App\Models;

use CodeIgniter\Model;

/** Model user (akun login). */
class UserModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'name','email','username','password','role','upn','sam','dn'  // ganti password_hash jadi password
    ];
    protected $useTimestamps = true;
    protected $useSoftDeletes = true;

    /** Ambil user + profil (join) berdasarkan users.id. */
    public function getUserWithProfileById($id)
    {
        return $this->select('users.*, 
                user_profile.nama as profile_nama, 
                user_profile.no_tlp as profile_no_tlp, 
                user_profile.divisi as profile_divisi, 
                user_profile.role as profile_role')
            ->join('user_profile', 'user_profile.user_id = users.id', 'left')
            ->where('users.id', $id)
            ->first();
    }

}
