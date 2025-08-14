<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\UserProfileModel;
use CodeIgniter\I18n\Time;
use CodeIgniter\Controller;
use LdapRecord\Container;
use LdapRecord\Connection;
use LdapRecord\Auth\BindException;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;

class AuthController extends BaseController
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


    $divisiList = [
        'gm','umum','keuangan','akuntansi','anggaran','sis','humas','sdm',
        'engginering','k3','dhr 3','ahli','rph 1','dhr 1','rph 2','dhr 2',
        'area service','rph 3','driver'
    ];

    $inputDivisi = strtolower(trim($this->request->getPost('divisi')));

    $foundDivisi = null;
    foreach ($divisiList as $d) {
        if ($d === $inputDivisi) {
            $foundDivisi = $d;
            break;
        }
    }

    if (!$foundDivisi) {
        log_message('error', 'Divisi tidak valid: ' . $inputDivisi);
        return redirect()->back()->withInput()->with('error', 'Divisi tidak valid.');
    }

    // Set role sesuai divisi
    if ($foundDivisi === 'umum') {
        $role = 'admin';
    } elseif ($foundDivisi === 'driver') {
        $role = 'driver';
    } else {
        $role = 'user';
    }

    $userModel = new UserModel();
    $userProfileModel = new UserProfileModel();

    // Insert ke users
    $userId = $userModel->insert([
        'email'    => $this->request->getPost('email'),
        'password' => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
    ]);

    // Insert ke user_profile
    $userProfileModel->insert([
        'user_id' => $userId,
        'nama'    => $this->request->getPost('nama'),
        'divisi'  => $foundDivisi,
        'role'    => $role,
    ]);

    // Jika role driver, insert ke tabel drivers (dan relasikan user_id)
    if ($role === 'driver') {
        $driverModel = new \App\Models\DriverModel();
        // Cek apakah sudah ada driver dengan nama sama, jika belum insert
        $existing = $driverModel->where('nama', $this->request->getPost('nama'))->first();
        if (!$existing) {
            $driverModel->insert([
                'nama'    => $this->request->getPost('nama'),
                // Tambahkan user_id jika kolom tersedia di tabel drivers
                'user_id' => $userId,
                'status'  => 'aktif',
            ]);
        }
    }

    return redirect()->to('/login')->with('success', 'Registrasi berhasil. Silakan login.');
}


    public function login()
    {
        return view('auth/login');
    }

    public function loginProcess()
    {
        $userModel = new UserModel();

        $email = trim($this->request->getPost('email'));
        $password = trim($this->request->getPost('password'));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->withInput()->with('error', 'Email tidak valid');
        }

        // Cek apakah user ada terlebih dahulu
        $foundUser = $userModel->where('email', $email)->first();
        if (!$foundUser) {
            return redirect()->back()->withInput()->with('error', 'Akun tidak ditemukan.');
        }

        $user = $userModel->getUserWithProfileById($foundUser['id']);

        log_message('debug', 'Input password: "' . $password . '"');
        log_message('debug', 'Password DB (hash): "' . $user['password'] . '"');

        if (!password_verify($password, $user['password'])) {
            return redirect()->back()->withInput()->with('error', 'Password salah.');
        }

        // Ambil role & divisi
        $role   = strtolower($user['profile_role'] ?? $user['role'] ?? 'user');
        $divisi = strtolower($user['profile_divisi'] ?? $user['divisi'] ?? '');

        // Jika divisi = "umum" paksa jadi admin (auto) walau di DB belum admin
        if ($divisi === 'umum' && $role !== 'admin') {
            $role = 'admin';
            // Sinkronkan sekali ke DB agar konsisten (tidak wajib, tapi membantu)
            try {
                $userProfileModel = new UserProfileModel();
                // Update profile role jika ada record
                $userProfileModel->where('user_id', $user['id'])->set(['role' => 'admin'])->update();
            } catch (\Throwable $e) {
                log_message('error', 'Gagal sinkron role admin otomatis: ' . $e->getMessage());
            }
            // Juga pastikan kolom role di tabel users jika ada
            try {
                $userModel->update($user['id'], ['role' => 'admin']);
            } catch (\Throwable $e) {
                log_message('error', 'Gagal update role users: ' . $e->getMessage());
            }
        }

        session()->set([
            'isLoggedIn' => true,
            'user_id'    => $user['id'],
            'email'      => $user['email'],
            'nama'       => $user['profile_nama'] ?? $user['nama'],
            'role'       => $role, // gunakan role hasil perhitungan otomatis
            'divisi'     => $user['profile_divisi'] ?? $user['divisi'],
        ]);
        
        // For driver role, ensure linkage to drivers table
        if ($role === 'driver') {
            try {
                $driverModel = model('App\\Models\\DriverModel');
                // Already linked?
                $linked = $driverModel->where('user_id', $user['id'])->first();
                if (!$linked) {
                    // Try match by name
                    $match = null;
                    $name = $user['profile_nama'] ?? $user['nama'] ?? null;
                    if ($name) {
                        $match = $driverModel->where('nama', $name)->first();
                    }
                    if ($match) {
                        $driverModel->update($match['id'], ['user_id' => $user['id']]);
                    } else {
                        // Create a new driver row minimally
                        $driverModel->insert([
                            'nama'    => $name ?: ('Driver_' . $user['id']),
                            'user_id' => $user['id'],
                            'status'  => 'Available',
                        ]);
                    }
                }
            } catch (\Throwable $e) {
                // ignore linkage error
            }
        }

        if ($role === 'admin') {
            return redirect()->to('/admin');
        } elseif ($role === 'driver') {
            return redirect()->to('/driver/dashboard');
        } else {
            return redirect()->to('/home');
        }
    

        // Login via AD
        /*try {
            $connection = new Connection([
                'hosts'            => ['ad.domain.local'],
                'base_dn'          => 'DC=domain,DC=local',
                'username'         => $email,
                'password'         => $password,
                'port'             => 389,
                'use_ssl'          => false,
                'use_tls'          => false,
                'version'          => 3,
            ]);

            Container::addConnection($connection);
            $connection->connect();

            if ($connection->auth()->attempt($email, $password)) {
                $ldapUser = LdapUser::where('mail', '=', $email)->first();

                if (!$ldapUser) {
                    return redirect()->back()->with('error', 'Akun AD ditemukan, tetapi detail pengguna tidak bisa dibaca.');
                }

                $existingUser = $userModel->where('email', $email)->first();

                if (!$existingUser) {
                    // Insert user ke DB lokal
                    $newUserId = $userModel->insert([
                        'nama'     => $ldapUser->getFirstAttribute('cn'),
                        'email'    => $email,
                        'divisi'   => 'GM',
                        'role'     => 'user',
                        'password' => null,
                    ]);

                    // Insert data profil juga
                    $profileModel = new UserProfileModel();
                    $profileModel->insert([
                        'user_id' => $newUserId,
                        'nama'    => $ldapUser->getFirstAttribute('cn'),
                        'divisi'  => 'GM',
                    ]);

                    $user = $userModel->find($newUserId);
                } else {
                    $user = $existingUser;
                }

                session()->set([
                    'isLoggedIn' => true,
                    'user_id'    => $user['id'],
                    'email'      => $user['email'],
                    'nama'       => $user['nama'],
                    'role'       => $user['role'],
                    'divisi'     => $user['divisi'] ?? null,
                ]);

                return redirect()->to('/home');
            } else {
                return redirect()->back()->with('error', 'Autentikasi AD gagal.');
            }
        } catch (BindException $e) {
            return redirect()->back()->with('error', 'Gagal terhubung ke Active Directory: ' . $e->getMessage());
        }*/
    }

    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login')->with('success', 'Anda berhasil logout.');
    }

    public function profile()
    {
        return view('profile');
    }

    public function unauthorized()
    {
        // Return a simple unauthorized page with 403 status
        return \Config\Services::response()
            ->setStatusCode(403)
            ->setBody(view('auth/unauthorized'));
    }

    /* ================== FORGOT / RESET PASSWORD ================== */
    public function forgotPasswordForm()
    {
        return view('auth/forgot_password');
    }

    public function sendResetLink()
    {
        $email = trim($this->request->getPost('email'));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->withInput()->with('error', 'Email tidak valid.');
        }
        $userModel = new UserModel();
        $user = $userModel->where('email', $email)->first();
        if (!$user) {
            // Jangan bocorkan apakah email ada
            return redirect()->back()->with('message', 'Jika email terdaftar, link reset telah dikirim.');
        }
        // Buat token
        $token = bin2hex(random_bytes(32));
        $expires = Time::now()->addMinutes(30)->toDateTimeString();
        // Simpan di tabel password_resets
        $db = \Config\Database::connect();
        $db->table('password_resets')->where('email', $email)->delete(); // hapus token lama
        $db->table('password_resets')->insert([
            'email' => $email,
            'token' => hash('sha256', $token),
            'expires_at' => $expires,
            'created_at' => Time::now()->toDateTimeString(),
        ]);
        $resetLink = base_url('reset-password/' . $token);
        // Sementara: tampilkan link di flash (production harus kirim email)
        return redirect()->back()->with('message', 'Link reset (sementara tampil di sini): ' . $resetLink);
    }

    public function resetPasswordForm($token)
    {
        if (!$token) {
            return redirect()->to('/forgot-password')->with('error', 'Token tidak valid.');
        }
        return view('auth/reset_password', ['token' => $token]);
    }

    public function resetPasswordProcess()
    {
        $token = $this->request->getPost('token');
        $password = $this->request->getPost('password');
        $passwordConfirm = $this->request->getPost('password_confirm');
        if (!$token) {
            return redirect()->back()->with('error', 'Token hilang.');
        }
        if ($password !== $passwordConfirm) {
            return redirect()->back()->with('error', 'Konfirmasi password tidak cocok.');
        }
        if (strlen($password) < 6) {
            return redirect()->back()->with('error', 'Password minimal 6 karakter.');
        }
        $db = \Config\Database::connect();
        $row = $db->table('password_resets')->where('token', hash('sha256', $token))->get()->getRowArray();
        if (!$row) {
            return redirect()->to('/forgot-password')->with('error', 'Token tidak ditemukan atau sudah digunakan.');
        }
        if (strtotime($row['expires_at']) < time()) {
            $db->table('password_resets')->where('email', $row['email'])->delete();
            return redirect()->to('/forgot-password')->with('error', 'Token kedaluwarsa.');
        }
        $userModel = new UserModel();
        $user = $userModel->where('email', $row['email'])->first();
        if (!$user) {
            return redirect()->to('/forgot-password')->with('error', 'User tidak ditemukan.');
        }
        $userModel->update($user['id'], [
            'password' => password_hash($password, PASSWORD_BCRYPT)
        ]);
        // Hapus token
        $db->table('password_resets')->where('email', $row['email'])->delete();
        return redirect()->to('/login')->with('success', 'Password berhasil direset. Silakan login.');
    }
}
