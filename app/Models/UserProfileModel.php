<?php
namespace App\Models;

use CodeIgniter\Model;

/** Model untuk tabel user_profile (biodata pengguna). */
class UserProfileModel extends Model
{
    protected $table = 'user_profile'; // FIX: nama tabel yang benar
    protected $primaryKey = 'id';

    // FIX: hanya kolom-kolom dari tabel user_profile
    protected $allowedFields = [
        'user_id', 'nama', 'divisi', 'no_tlp', 'foto_url', 'role', 'created_at', 'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /** Join profil berdasarkan email user (contoh utilitas). */
    public function getUserWithProfileByEmail($email)
    {
        return $this->db->table('users')
            ->select('users.*, user_profile.nama as profile_nama, user_profile.divisi, user_profile.no_tlp')
            ->join('user_profile', 'user_profile.user_id = users.id', 'left')
            ->where('users.email', $email)
            ->get()
            ->getRowArray();
    }
}
