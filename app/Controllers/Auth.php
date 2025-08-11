<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Controller;

class Auth extends BaseController
{
    public function register()
    {
        return view('auth/register');
    }

    public function storeRegister()
    {
        $validation = \Config\Services::validation();

        $validation->setRules([
            'nama'     => 'required|trim',
            'email'    => 'required|valid_email|is_unique[users.email]|trim',
            'password' => 'required|min_length[6]|trim',
            'divisi'   => 'required|trim',
        ]);

        if (!$validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $userModel = new UserModel();

        $divisi = $this->request->getPost('divisi');
        $role   = ($divisi === 'Umum') ? 'admin' : 'user';

        $userModel->insert([
            'nama'     => $this->request->getPost('nama'),
            'email'    => $this->request->getPost('email'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
            'divisi'   => $divisi,
            'role'     => $role
        ]);

        return redirect()->to('/login')->with('success', 'Registrasi berhasil. Silakan login.');
    }

    public function login()
    {
        return view('auth/login');
    }

    public function loginProcess()
    {
        $userModel = new UserModel();

        $email    = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        // Validasi manual email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->withInput()->with('error', 'Email tidak valid');
        }

        $user = $userModel->where('email', $email)->first();

        if ($user && password_verify($password, $user['password'])) {
            session()->set([
                'isLoggedIn' => true,
                'user_id'    => $user['id'],
                'email'      => $user['email'],
                'nama'       => $user['nama'],
                'role'       => $user['role'],
            ]);

            return redirect()->to('/home');
        } else {
            return redirect()->back()->withInput()->with('error', 'Email atau password salah');
        }
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login')->with('success', 'Anda berhasil logout.');
    }

    // (Opsional) Profil user, bisa dipindah ke UserController jika kamu sudah pakai itu
    public function profile()
    {
        return view('profile');
    }
}
