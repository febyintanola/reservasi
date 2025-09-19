<?php
namespace App\Controllers;

use App\Models\UserModel;
use App\Models\UserProfileModel;

/** Profil user (admin & user). */
class UserController extends BaseController
{
    /** Tampilkan halaman profil sesuai konteks (admin/user). */
    public function profile()
    {
        $session = session();
        $userId = $session->get('user_id');

        if (!$userId) {
            return redirect()->to('/login')->with('error', 'Silakan login dahulu.');
        }

        $userModel = new UserModel();
        $userProfileModel = new UserProfileModel();

        // Ambil data user
        $user = $userModel->find($userId);

        // Ambil data profil user
        $profile = $userProfileModel->where('user_id', $userId)->first();

    // Tentukan konteks (admin vs user) berdasarkan URL saat ini
    $isAdminContext = (strpos(uri_string(), 'admin') === 0) || (strpos(current_url(), '/admin') !== false);

    // Kirim ke view yang sesuai
    return view($isAdminContext ? 'admin/profile' : 'user/profile', [
            'user'    => $user,
            'profile' => $profile
        ]);
    }

    /** Update data profil dan foto (opsional). */
    public function update()
{
    $userId = session()->get('user_id');
    if (!$userId) {
        return redirect()->to('/login');
    }

    $userModel = new UserModel();
    $userProfileModel = new UserProfileModel();

    $profileData = [
        'nama'   => $this->request->getPost('nama'),
        'no_tlp' => $this->request->getPost('no_tlp'),
        'divisi' => $this->request->getPost('divisi'),
    ];

    // Tangani upload foto jika ada (simpan ke public/uploads agar dapat diakses)
    $foto = $this->request->getFile('foto');
    if ($foto && $foto->isValid() && $foto->getError() === UPLOAD_ERR_OK) {
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($foto->getMimeType(), $allowedTypes)) {
            return redirect()->back()->with('error', 'Jenis file tidak didukung');
        }

        $uploadsDir = rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads';
        if (!is_dir($uploadsDir) && !@mkdir($uploadsDir, 0755, true)) {
            return redirect()->back()->with('error', 'Gagal membuat folder uploads.');
        }
        if (!is_writable($uploadsDir)) {
            return redirect()->back()->with('error', 'Folder uploads tidak writable.');
        }

        $newName = $foto->getRandomName();
        if (!$foto->hasMoved() && $foto->move($uploadsDir, $newName)) {
            $profileData['foto_url'] = base_url('uploads/' . $newName);
        } else {
            return redirect()->back()->with('error', 'Gagal menyimpan gambar.');
        }
    }

    $existingProfile = $userProfileModel->where('user_id', $userId)->first();

    if ($existingProfile) {
        $userProfileModel->update($existingProfile['id'], $profileData);
    } else {
        $profileData['user_id'] = $userId;
        $userProfileModel->insert($profileData);
    }

    // Redirect kembali ke halaman profil sesuai scope
    $role = strtolower(session()->get('role') ?? 'user');
    $isAdminScope = (strpos(current_url(), '/admin') !== false) || ($role === 'admin' && strpos(previous_url(), '/admin') !== false);
    $dest = $isAdminScope ? '/admin/profile' : '/user/profile';
    return redirect()->to($dest)->with('success', 'Profil berhasil diperbarui.');
}

}
