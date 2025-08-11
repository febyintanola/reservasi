<?php
namespace App\Controllers;

use App\Models\UserModel;
use App\Models\UserProfileModel;

class UserController extends BaseController
{
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

        // Kirim ke view
        return view('user/profile', [
            'user'    => $user,
            'profile' => $profile
        ]);
    }

    public function update()
{
    $userId = session()->get('user_id');

    $userModel = new UserModel();
    $userProfileModel = new UserProfileModel();

    $profileData = [
        'nama'   => $this->request->getPost('nama'),
        'no_tlp' => $this->request->getPost('no_tlp'),
        'divisi' => $this->request->getPost('divisi'),
    ];

    // Tangani upload foto jika ada
    $foto = $this->request->getFile('foto');
    if ($foto && $foto->isValid() && !$foto->hasMoved()) {
        // Bisa tambahkan validasi file type, size dll di sini
        $newName = $foto->getRandomName();
        $foto->move(WRITEPATH . 'uploads', $newName);

        // Simpan path atau URL foto ke database
        $profileData['foto_url'] = base_url('writable/uploads/' . $newName);
    }

    $existingProfile = $userProfileModel->where('user_id', $userId)->first();

    if ($existingProfile) {
        $userProfileModel->update($existingProfile['id'], $profileData);
    } else {
        $profileData['user_id'] = $userId;
        $userProfileModel->insert($profileData);
    }

    return redirect()->to('/user/profile')->with('success', 'Profil berhasil diperbarui.');
}

}
