<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Libraries\LdapAuth;
use App\Models\DriverModel;
use App\Models\UserModel;
use App\Models\UserProfileModel;
use CodeIgniter\I18n\Time;

class AuthController extends BaseController
{
    /** Tampilkan form registrasi. */
    public function register()
    {
        return view('auth/register');
    }

    /** Simpan data registrasi user baru. */
    public function storeRegister()
    {
        $validation = \Config\Services::validation();

        $validation->setRules([
            'nama'     => 'required|trim',
            'email'    => 'required|valid_email|is_unique[users.email]|trim',
            'password' => 'required|min_length[6]|trim',
            'divisi'   => 'required|trim',
        ]);

        if (! $validation->withRequest($this->request)->run()) {
            return redirect()->back()->withInput()->with('errors', $validation->getErrors());
        }

        $divisiList = [
            'gm','umum','keuangan','akuntansi','anggaran','sis','humas','sdm',
            'engginering','k3','dhr 3','ahli','rph 1','dhr 1','rph 2','dhr 2',
            'area service','rph 3','driver'
        ];

        $inputDivisi = strtolower(trim($this->request->getPost('divisi')));
        $divisi = array_values(array_filter($divisiList, static fn ($d) => $d === $inputDivisi));
        if ($divisi === []) {
            log_message('error', 'Divisi tidak valid: ' . $inputDivisi);
            return redirect()->back()->withInput()->with('error', 'Divisi tidak valid.');
        }

        $divisi = $divisi[0];
        $role = match ($divisi) {
            'umum'   => 'admin',
            'driver' => 'driver',
            default  => 'user',
        };

        $users = new UserModel();
        $profiles = new UserProfileModel();

        $userId = $users->insert([
            'email'    => $this->request->getPost('email'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_BCRYPT),
        ]);

        $profiles->insert([
            'user_id' => $userId,
            'nama'    => $this->request->getPost('nama'),
            'divisi'  => $divisi,
            'role'    => $role,
        ]);

        if ($role === 'driver') {
            $drivers = new DriverModel();
            $existingDriver = $drivers->where('user_id', $userId)->first();
            if (! $existingDriver) {
                $drivers->insert([
                    'user_id' => $userId,
                    'nama'    => $this->request->getPost('nama'),
                    'status'  => 'aktif',
                ]);
            }
        }

        return redirect()->to('/login')->with('success', 'Registrasi berhasil. Silakan login.');
    }

    /** Tampilkan form login. */
    public function login()
    {
        return view('auth/login');
    }

    /** Proses login dengan integrasi Active Directory + fallback user lokal via Shield. */
    public function loginProcess()
    {
        $emailOrUser = trim((string) $this->request->getPost('email'));
        $password = (string) $this->request->getPost('password');

        if ($emailOrUser === '' || $password === '') {
            return redirect()->back()->with('error', 'Email/username dan password wajib diisi.')->withInput();
        }

        helper('auth');

        if (auth()->loggedIn()) {
            auth()->logout();
        }

        session()->remove(['isLoggedIn', 'authProvider', 'role', 'user', 'user_id']);

        $users = new UserModel();
        $profiles = new UserProfileModel();

        // 1) Coba autentikasi ke Active Directory
        $ldap = new LdapAuth();
        $ldapResult = $ldap->authenticate($emailOrUser, $password);
        $division = null;

        if ($ldapResult['ok'] === true) {
            $user = $users->where('email', $ldapResult['user']['mail'])
                          ->orWhere('username', $ldapResult['user']['sam'])
                          ->first();

            $division = $this->extractDivisionFromDn($ldapResult['user']['dn'] ?? '') ?? null;

            if (! $user) {
                $userId = $users->insert([
                    'username' => $ldapResult['user']['sam'],
                    'email'    => $ldapResult['user']['mail'],
                    'password' => null,
                ]);

                $profiles->insert([
                    'user_id' => $userId,
                    'nama'    => $ldapResult['user']['name'],
                    'divisi'  => $division,
                    'role'    => 'user',
                ]);

                $user = $users->find($userId);
            } else {
                $updateData = ['nama' => $ldapResult['user']['name']];
                if ($division !== null) {
                    $updateData['divisi'] = $division;
                }

                $profiles->where('user_id', $user['id'])
                    ->set($updateData)
                    ->update();
            }

            $profile = $profiles->where('user_id', $user['id'])->first();
            $role = $profile['role'] ?? 'user';
            $nama = $profile['nama'] ?? $ldapResult['user']['name'];
            $divisi = $profile['divisi'] ?? $division;

            auth()->loginById($user['id']);

            session()->regenerate(true);
            session()->set([
                'isLoggedIn'   => true,
                'authProvider' => 'ad',
                'role'         => $role,
                'user' => [
                    'id'    => $user['id'],
                    'name'  => $nama,
                    'email' => $user['email'],
                    'role'  => $role,
                    'division' => $divisi,
                    'upn'   => $ldapResult['user']['upn'],
                    'sam'   => $ldapResult['user']['sam'],
                    'dn'    => $ldapResult['user']['dn'],
                ],
            ]);
            session()->set('user_id', $user['id']);

            return $this->redirectByRole($role);
        }

        // 2) Fallback user lokal
        $user = $users->where('email', $emailOrUser)
                      ->orWhere('username', $emailOrUser)
                      ->first();

        if ($user && ! empty($user['password']) && password_verify($password, $user['password'])) {
            $profile = $profiles->where('user_id', $user['id'])->first();
            $role = $profile['role'] ?? 'user';
            $nama = $profile['nama'] ?? ($user['username'] ?? $user['email']);
            $divisi = $profile['divisi'] ?? null;

            auth()->loginById($user['id']);

            session()->regenerate(true);
            session()->set([
                'isLoggedIn'   => true,
                'authProvider' => 'local',
                'role'         => $role,
                'user' => [
                    'id'    => $user['id'],
                    'name'  => $nama,
                    'email' => $user['email'],
                    'role'  => $role,
                    'division' => $divisi,
                    'upn'   => $user['upn'] ?? null,
                    'sam'   => $user['sam'] ?? $user['username'],
                    'dn'    => $user['dn'] ?? null,
                ],
            ]);
            session()->set('user_id', $user['id']);

            return $this->redirectByRole($role);
        }

        return redirect()->back()
            ->with('error', 'Login gagal: ' . ($ldapResult['error'] ?? 'Invalid credentials'))
            ->withInput();
    }

    /** Logout dan hapus sesi. */
    public function logout()
    {
        helper('auth');
        if (auth()->loggedIn()) {
            auth()->logout();
        }

        session()->destroy();
        return redirect()->to('/login')->with('success', 'Anda berhasil logout.');
    }

    /** Contoh halaman profil sederhana. */
    public function profile()
    {
        return view('profile');
    }

    /** Halaman unauthorized (403). */
    public function unauthorized()
    {
        return \Config\Services::response()
            ->setStatusCode(403)
            ->setBody(view('auth/unauthorized'));
    }

    /** Tampilkan form lupa password. */
    public function forgotPasswordForm()
    {
        return view('auth/forgot_password');
    }

    /** Kirim link reset (sementara ditampilkan via flash). */
    public function sendResetLink()
    {
        $email = trim($this->request->getPost('email'));
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return redirect()->back()->withInput()->with('error', 'Email tidak valid.');
        }

        $users = new UserModel();
        $user = $users->where('email', $email)->first();
        if (! $user) {
            return redirect()->back()->with('message', 'Jika email terdaftar, link reset telah dikirim.');
        }

        $token = bin2hex(random_bytes(32));
        $expires = Time::now()->addMinutes(30)->toDateTimeString();

        $db = \Config\Database::connect();
        $db->table('password_resets')->where('email', $email)->delete();
        $db->table('password_resets')->insert([
            'email'      => $email,
            'token'      => hash('sha256', $token),
            'expires_at' => $expires,
            'created_at' => Time::now()->toDateTimeString(),
        ]);

        $resetLink = base_url('reset-password/' . $token);

        return redirect()->back()->with('message', 'Link reset (sementara tampil di sini): ' . $resetLink);
    }

    /** Tampilkan form reset password. */
    public function resetPasswordForm($token)
    {
        if (! $token) {
            return redirect()->to('/forgot-password')->with('error', 'Token tidak valid.');
        }

        return view('auth/reset_password', ['token' => $token]);
    }

    /** Proses reset password berdasarkan token. */
    public function resetPasswordProcess()
    {
        $token = $this->request->getPost('token');
        $password = $this->request->getPost('password');
        $confirm = $this->request->getPost('password_confirm');

        if (! $token) {
            return redirect()->back()->with('error', 'Token hilang.');
        }

        if ($password !== $confirm) {
            return redirect()->back()->with('error', 'Konfirmasi password tidak cocok.');
        }

        if (strlen($password) < 6) {
            return redirect()->back()->with('error', 'Password minimal 6 karakter.');
        }

        $db = \Config\Database::connect();
        $resetRow = $db->table('password_resets')
            ->where('token', hash('sha256', $token))
            ->get()
            ->getRowArray();

        if (! $resetRow) {
            return redirect()->to('/forgot-password')->with('error', 'Token tidak ditemukan atau sudah digunakan.');
        }

        if (strtotime($resetRow['expires_at']) < time()) {
            $db->table('password_resets')->where('email', $resetRow['email'])->delete();
            return redirect()->to('/forgot-password')->with('error', 'Token kedaluwarsa.');
        }

        $users = new UserModel();
        $user = $users->where('email', $resetRow['email'])->first();
        if (! $user) {
            return redirect()->to('/forgot-password')->with('error', 'User tidak ditemukan.');
        }

        $users->update($user['id'], [
            'password' => password_hash($password, PASSWORD_BCRYPT),
        ]);

        $db->table('password_resets')->where('email', $resetRow['email'])->delete();

        return redirect()->to('/login')->with('success', 'Password berhasil direset. Silakan login.');
    }

    private function redirectByRole(string $role)
    {
        return match ($role) {
            'admin'  => redirect()->to('/admin'),
            'driver' => redirect()->to('/driver/dashboard'),
            default  => redirect()->to('/home'),
        };
    }

    private function extractDivisionFromDn(?string $dn): ?string
    {
        if ($dn === null || $dn === '') {
            return null;
        }

        $parts = array_map('trim', explode(',', $dn));
        foreach ($parts as $part) {
            if (stripos($part, 'OU=') === 0) {
                return substr($part, 3);
            }
        }

        return null;
    }
}
