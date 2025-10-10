<?php

namespace App\Controllers;

use App\Models\CarModel;
use App\Models\DriverModel;
use App\Models\DriverAssignmentModel;
use CodeIgniter\Controller;
use DateTime;

/**
 * Controller Booking Mobil
 *
 * Mengelola pemesanan mobil oleh user dan CRUD/admin operasi terkait driver & assignment.
 */
class CarController extends BaseController
{
    public function index()
    {
        return redirect()->to('user/car/form');
    }

    /** Form pemesanan mobil (user). */
    public function form()
    {
        $session = session();
        $user_id = $session->get('user_id');

        if (!$user_id) {
            return redirect()->to('/login')->with('error', 'Silakan login dahulu.');
        }

        return view('user/car/form', ['user_id' => $user_id]);
    }

    /** Simpan pemesanan mobil dari user. */
    public function save()
    {
        $model = new CarModel();

        $tanggalPergi = $this->request->getPost('tanggal_pergi');
        $tanggalPulang = $this->request->getPost('tanggal_pulang');

        $start = new DateTime($tanggalPergi);
        $end = new DateTime($tanggalPulang);
        $jumlahHari = $start->diff($end)->days + 1; // +1 agar hari yang sama tetap dihitung 1 hari

        $pengikutArray = $this->request->getPost('nama_pengikut');
        $pengikut = implode(', ', $pengikutArray);

        $data = [
            'user_id'           => $this->request->getPost('user_id'),
            'nama'              => $this->request->getPost('nama'),
            'tujuan'            => $this->request->getPost('tujuan'),
            'pengikut'          => $pengikut,
            'jumlah_hari'       => $jumlahHari,
            'keperluan'         => $this->request->getPost('keperluan'),
            'nama_pekerjaan'    => $this->request->getPost('nama_pekerjaan'),
            'project_costing'   => $this->request->getPost('project_costing'),
            'task_number'       => $this->request->getPost('task_number'),
            'expenditure_type'  => $this->request->getPost('expenditure_type'),
            'expenditure_org'   => $this->request->getPost('expenditure_org'),
            'tanggal_pergi'     => $tanggalPergi,
            'tanggal_pulang'    => $tanggalPulang,
        ];

        $model->insert($data);

        return redirect()
            ->to('/') // atau halaman tujuan Anda
            ->with('notif', [
                'type' => 'success',
                'title' => 'Pemesanan Mobil Berhasil',
                'message' => 'Silakan tunggu konfirmasi dari admin.'
            ]);
    }
    // Admin
    public function tambahdriver()
    {
        $model = new DriverModel();

        $data = [
            'nama'      => $this->request->getPost('nama'),
            'no_hp'     => $this->request->getPost('no_hp'),
            'status'    => $this->request->getPost('status'),
        ];

        $model->insert($data);

        return redirect()->to('/admin/driver')->with('success', 'Driver berhasil ditambahkan.');
    }

    // Tampilkan form assign driver & mobil
        public function assignForm($id)
        {
            $carModel = new CarModel();
            $driverModel = new DriverModel();
            $assignmentModel = new DriverAssignmentModel();
            $booking = $carModel->find($id);
            if (!$booking) {
                return redirect()->to('admin')->with('error', 'Booking mobil tidak ditemukan');
            }
            $start = $booking['tanggal_pergi'];
            $end   = $booking['tanggal_pulang'];


            // Ambil assignment booking ini (jika edit)
            $currentAssignment = $assignmentModel->where('car_booking_id', $id)->first();
            $currentDriverId = $currentAssignment['driver_id'] ?? null;

            // Cari driver yang sibuk di rentang tanggal ini (overlap hari)
            $busyDriverIds = $assignmentModel
                ->select('driver_assignments.driver_id')
                ->join('car_bookings', 'car_bookings.id = driver_assignments.car_booking_id')
                ->where('car_bookings.id !=', $id)
                ->where('car_bookings.tanggal_pergi <=', $end)
                ->where('car_bookings.tanggal_pulang >=', $start)
                ->findColumn('driver_id');
            $busyDriverIds = $busyDriverIds ? array_unique(array_filter($busyDriverIds)) : [];

            // Ambil semua driver lalu filter yang available (kecuali driver yg sudah terpasang di booking ini tetap muncul)
            $allDrivers = $driverModel->findAll();
            $availableDrivers = array_values(array_filter($allDrivers, function($d) use ($busyDriverIds, $currentDriverId) {
                if ($currentDriverId && $d['id'] == $currentDriverId) return true; // izinkan driver sekarang
                return !in_array($d['id'], $busyDriverIds, true);
            }));

            return view('admin/car/assign', [
                'booking'    => $booking,
                'drivers'    => $availableDrivers,
                'assignment' => $currentAssignment,
                'busyIds'    => $busyDriverIds,
            ]);
        }

    // Proses simpan assign driver & mobil
        public function assignSave($id)
        {
            $assignmentModel = new \App\Models\DriverAssignmentModel();
            $driverModel = new \App\Models\DriverModel();

            $driver_id   = (int)$this->request->getPost('driver_id');
            $mobil_jenis = $this->request->getPost('mobil_jenis');
            $mobil_plat  = $this->request->getPost('mobil_plat');
            $start_datetime = $this->request->getPost('start_datetime'); // dari form
            $notes = $this->request->getPost('notes');

            $dataAssign = [
                'car_booking_id' => $id,
                'driver_id'      => $driver_id,
                'mobil_jenis'    => $mobil_jenis,
                'mobil_plat'     => $mobil_plat,
                'start_datetime' => $start_datetime,
                'reminder_sent'  => 0,
                'notes'          => $notes,
            ];

            // Insert/update assignment
            $existing = $assignmentModel->where('car_booking_id', $id)->first();
            if ($existing) {
                $assignmentModel->update($existing['id'], $dataAssign);
                $assignmentId = $existing['id'];
            } else {
                $assignmentModel->insert($dataAssign);
                $assignmentId = $assignmentModel->getInsertID();
            }

            // Kirim notifikasi ke driver
            $driver = $driverModel->find($driver_id);
            $notif = new \App\Libraries\NotificationService();
            $assignment = (object) $assignmentModel->find($assignmentId);
            // Pastikan assignment punya tanggal_pergi
            if (!isset($assignment->tanggal_pergi)) {
                $carModel = new CarModel();
                $booking = $carModel->find($id);
                if ($booking && isset($booking['tanggal_pergi'])) {
                    $assignment->tanggal_pergi = $booking['tanggal_pergi'];
                }
            }
            $notif->notifyDriverAssignment($assignment, (object)$driver);

            return redirect()->to('admin/car/detailMobil/' . $id)
                ->with('message', 'Driver & Mobil berhasil di-assign dan notifikasi dikirim.');
        }

    /**
     * Cek ketersediaan driver berdasarkan overlap tanggal (hari penuh).
     * Jika ingin mendukung jam, perlu kolom tambahan jam_mulai/jam_selesai.
     */
        private function isDriverAvailable(int $driverId, string $start, string $end, int $currentBookingId = null): bool
        {
            if ($driverId <= 0) return false;
            $assignmentModel = new DriverAssignmentModel();
            $builder = $assignmentModel
                ->select('driver_assignments.id')
                ->join('car_bookings', 'car_bookings.id = driver_assignments.car_booking_id')
                ->where('driver_assignments.driver_id', $driverId)
                ->where('car_bookings.tanggal_pergi <=', $end)
                ->where('car_bookings.tanggal_pulang >=', $start);
            if ($currentBookingId) {
                $builder->where('car_bookings.id !=', $currentBookingId);
            }
            $conflict = $builder->first();
            return $conflict ? false : true;
        }

    /* ================= ADMIN (CRUD BOOKING MANUAL) ================= */
    public function adminIndex()
    {
        $model = new CarModel();
        $bookings = $model->getAllBookings();
        return view('admin/car/list', ['bookings' => $bookings]);
    }

    /* ================= ADMIN DRIVER LIST ================= */
    public function driverList()
    {
        $driverModel = new DriverModel();
        $drivers = $driverModel->findAll();
        return view('admin/car/list', ['drivers' => $drivers]); // reuse view placeholder name changed soon
    }

    /** Form tambah driver (admin). */
    public function driverCreate()
    {
        return view('admin/car/tambah');
    }

    /** Simpan driver baru (admin). */
    public function driverStore()
    {
        $driverModel = new DriverModel();
        // Handle upload foto (optional)
        $foto = $this->request->getFile('foto');
        $fotoPath = null;
        if ($foto && $foto->isValid() && !$foto->hasMoved()) {
            // Pastikan folder public/uploads ada
            $targetDir = FCPATH . 'uploads';
            if (!is_dir($targetDir)) {
                @mkdir($targetDir, 0775, true);
            }
            $newName = 'driver_' . time() . '_' . uniqid() . '.' . $foto->getExtension();
            try {
                $foto->move($targetDir, $newName);
                $fotoPath = 'uploads/' . $newName; // relatif dari public
            } catch (\Throwable $e) {
                return redirect()->back()->withInput()->with('error', 'Gagal menyimpan foto: ' . $e->getMessage());
            }
        }

        $status = $this->request->getPost('status');
        if (!$status) {
            // Default konsisten dengan pilihan di UI (Available / On Duty)
            $status = 'Available';
        }

        $driverModel->insert([
            'nama' => $this->request->getPost('nama'),
            'no_hp' => $this->request->getPost('no_hp'),
            'foto_url' => $fotoPath,
            'status' => $status,
        ]);
        return redirect()->to('admin/driver')->with('success', 'Driver ditambahkan.');
    }

    /** Form edit driver (admin). */
    public function driverEdit($id)
    {
        $driverModel = new DriverModel();
        $driver = $driverModel->find($id);
        if (!$driver) return redirect()->to('admin/driver')->with('error','Driver tidak ditemukan');
        return view('admin/car/edit', ['driver' => $driver]);
    }

    /** Update data driver (admin). */
    public function driverUpdate($id)
    {
        $driverModel = new DriverModel();
        $driver = $driverModel->find($id);
        if (!$driver) return redirect()->to('admin/driver')->with('error','Driver tidak ditemukan');
        $dataUpdate = [
            'nama' => $this->request->getPost('nama'),
            'no_hp' => $this->request->getPost('no_hp'),
            'status' => $this->request->getPost('status') ?: ($driver['status'] ?? 'Available'),
        ];

        $foto = $this->request->getFile('foto');
        if ($foto && $foto->isValid() && $foto->getError() === UPLOAD_ERR_OK) {
            $allowed = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($foto->getMimeType(), $allowed)) {
                return redirect()->back()->withInput()->with('error', 'Tipe gambar tidak didukung.');
            }
            $uploadsDir = rtrim(FCPATH, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads';
            if (!is_dir($uploadsDir) && !@mkdir($uploadsDir, 0755, true)) {
                return redirect()->back()->withInput()->with('error', 'Gagal membuat folder uploads. Periksa permission.');
            }
            if (!is_writable($uploadsDir)) {
                return redirect()->back()->withInput()->with('error', 'Folder uploads tidak writable. Periksa permission.');
            }
            $newName = $foto->getRandomName();
            if (!$foto->hasMoved() && $foto->move($uploadsDir, $newName)) {
                // Simpan path relatif agar konsisten dengan driverStore()
                $dataUpdate['foto_url'] = 'uploads/' . $newName;
            } else {
                return redirect()->back()->withInput()->with('error', 'Gagal menyimpan gambar.');
            }
        }
        $driverModel->update($id, $dataUpdate);
        return redirect()->to('admin/driver')->with('success','Driver diperbarui.');
    }

    /** Form tambah booking mobil (admin). */
    public function create()
    {
        return view('admin/car/tambah');
    }

    /** Simpan booking mobil (admin). */
    public function store()
    {
        $model = new CarModel();
        $tanggalPergi = $this->request->getPost('tanggal_pergi');
        $tanggalPulang = $this->request->getPost('tanggal_pulang');
        if ($tanggalPergi && $tanggalPulang) {
            try {
                $start = new DateTime($tanggalPergi);
                $end = new DateTime($tanggalPulang);
                $jumlahHari = $start->diff($end)->days + 1;
            } catch (\Throwable $e) {
                $jumlahHari = 1;
            }
        } else {
            $jumlahHari = 1;
        }
        $pengikut = $this->request->getPost('pengikut');
        $data = [
            'nama' => $this->request->getPost('nama'),
            'tujuan' => $this->request->getPost('tujuan'),
            'pengikut' => $pengikut,
            'tanggal_pergi' => $tanggalPergi,
            'tanggal_pulang' => $tanggalPulang,
            'jumlah_hari' => $jumlahHari,
            'keperluan' => $this->request->getPost('keperluan'),
            'nama_pekerjaan' => $this->request->getPost('nama_pekerjaan'),
            'project_costing' => $this->request->getPost('project_costing'),
            'task_number' => $this->request->getPost('task_number'),
            'expenditure_type' => $this->request->getPost('expenditure_type'),
            'expenditure_org' => $this->request->getPost('expenditure_org'),
            'status' => $this->request->getPost('status') ?? 'pending',
        ];
        $model->insert($data);
        return redirect()->to('admin/car')->with('success', 'Booking mobil berhasil ditambahkan');
    }

    /** Form edit booking mobil (admin). */
    public function edit($id)
    {
        $model = new CarModel();
        $booking = $model->find($id);
        if (!$booking) {
            return redirect()->to('admin/car')->with('error', 'Data booking tidak ditemukan');
        }
        return view('admin/car/edit', ['booking' => $booking]);
    }

    /** Update booking mobil (admin). */
    public function update($id)
    {
        $model = new CarModel();
        $booking = $model->find($id);
        if (!$booking) {
            return redirect()->to('admin/car')->with('error', 'Data booking tidak ditemukan');
        }
        $tanggalPergi = $this->request->getPost('tanggal_pergi');
        $tanggalPulang = $this->request->getPost('tanggal_pulang');
        try {
            $start = new DateTime($tanggalPergi);
            $end = new DateTime($tanggalPulang);
            $jumlahHari = $start->diff($end)->days + 1;
        } catch (\Throwable $e) {
            $jumlahHari = $booking['jumlah_hari'] ?? 1;
        }
        $data = [
            'nama' => $this->request->getPost('nama'),
            'tujuan' => $this->request->getPost('tujuan'),
            'pengikut' => $this->request->getPost('pengikut'),
            'tanggal_pergi' => $tanggalPergi,
            'tanggal_pulang' => $tanggalPulang,
            'jumlah_hari' => $jumlahHari,
            'keperluan' => $this->request->getPost('keperluan'),
            'nama_pekerjaan' => $this->request->getPost('nama_pekerjaan'),
            'project_costing' => $this->request->getPost('project_costing'),
            'task_number' => $this->request->getPost('task_number'),
            'expenditure_type' => $this->request->getPost('expenditure_type'),
            'expenditure_org' => $this->request->getPost('expenditure_org'),
            'status' => $this->request->getPost('status'),
        ];
        $prevStatus = strtolower($booking['status'] ?? '');
        $model->update($id, $data);

        // Sinkronkan status driver jika status booking berubah oleh admin
        try {
            $newStatus = strtolower($data['status'] ?? '');
            if ($newStatus && $newStatus !== $prevStatus) {
                $assignmentModel = new DriverAssignmentModel();
                $assignment = $assignmentModel->where('car_booking_id', $id)->first();
                if ($assignment) {
                    $driverModel = new DriverModel();
                    $driverId = null;

                    // Coba drivers.id terlebih dahulu
                    $maybeDriver = $driverModel->find((int)$assignment['driver_id']);
                    if ($maybeDriver) {
                        $driverId = is_array($maybeDriver)
                            ? (int)($maybeDriver['id'] ?? 0)
                            : (int)($maybeDriver->id ?? 0);
                    } else {
                        // Legacy: driver_assignments.driver_id adalah users.id
                        $byUser = $driverModel->where('user_id', (int)$assignment['driver_id'])->first();
                        if ($byUser) {
                            $driverId = is_array($byUser)
                                ? (int)($byUser['id'] ?? 0)
                                : (int)($byUser->id ?? 0);
                        }
                    }

                    if ($driverId) {
                        if (in_array($newStatus, ['accepted','ongoing'], true)) {
                            $driverModel->update($driverId, ['status' => 'On Duty']);
                        } elseif (in_array($newStatus, ['done','rejected','pending'], true)) {
                            // Set Available hanya jika tidak ada booking lain yg ongoing untuk driver ini
                            $hasOtherOngoing = (int)$assignmentModel
                                ->select('driver_assignments.id')
                                ->join('car_bookings', 'car_bookings.id = driver_assignments.car_booking_id')
                                ->groupStart()
                                    ->where('driver_assignments.driver_id', $driverId)
                                    ->orWhere('driver_assignments.driver_id', (int)$assignment['driver_id'])
                                ->groupEnd()
                                ->where('car_bookings.status', 'ongoing')
                                ->where('car_bookings.id !=', $id)
                                ->countAllResults() > 0;
                            if (!$hasOtherOngoing) {
                                $driverModel->update($driverId, ['status' => 'Available']);
                            }
                        }
                    }
                }
            }
        } catch (\Throwable $e) { /* abaikan */ }
        return redirect()->to('admin/car')->with('success', 'Booking mobil berhasil diperbarui');
    }

    /** Detail booking mobil (admin). */
    public function detail($id)
    {
        $model = new CarModel();
        $booking = $model->find($id);
        if (!$booking) {
            return redirect()->to('/admin')->with('error', 'Booking tidak ditemukan.');
        }

        // Ambil data assignment & driver (jika ada)
        $assignment = null;
        $driver = null;

        try {
            $assignmentModel = new DriverAssignmentModel();
            $assignment = $assignmentModel->where('car_booking_id', $id)->first();
        } catch (\Throwable $e) {
            $assignment = null;
        }

        // Pastikan array
        if (is_object($assignment)) {
            $assignment = (array) $assignment;
        }

        if ($assignment) {
            // Normalisasi nama kolom agar konsisten dengan view
            $assignment['mobil_jenis'] = $assignment['mobil_jenis'] ?? ($assignment['jenis_mobil'] ?? $assignment['jenis'] ?? null);
            $assignment['mobil_plat']  = $assignment['mobil_plat']  ?? ($assignment['no_plat'] ?? $assignment['plat'] ?? null);

            if (!empty($assignment['driver_id'])) {
                try {
                    $driverModel = new DriverModel();
                    $driverRow = $driverModel->find((int)$assignment['driver_id']);
                    if (!$driverRow) {
                        // fallback kalau driver_id adalah user_id
                        $driverRow = $driverModel->where('user_id', (int)$assignment['driver_id'])->first();
                    }
                    if ($driverRow) {
                        $driver = [
                            'nama'  => $driverRow['nama'] ?? null,
                            'no_hp' => $driverRow['no_hp'] ?? null,
                        ];
                    }
                } catch (\Throwable $e) {
                    $driver = null;
                }
            }
        }

        return view('admin/car/detail', [
            'booking'    => $booking,
            'assignment' => $assignment,
            'driver'     => $driver,
        ]);
    }

    /** Tampilkan detail booking mobil untuk user (dengan assignment driver/mobil jika ada). */
    public function userDetail($id)
    {
        $carModel = new CarModel();
        $booking = $carModel->find($id);
        if (!$booking) {
            return redirect()->to('history?jenis=mobil')->with('error', 'Data booking tidak ditemukan');
        }

        $assignment = null;
        $driver = null;

        try {
            $assignmentModel = new DriverAssignmentModel();
            $assignment = $assignmentModel->where('car_booking_id', $id)->first();
        } catch (\Throwable $e) {
            $assignment = null;
        }

        // Paksa array jika model return object
        if (is_object($assignment)) {
            $assignment = (array) $assignment;
        }

        if ($assignment) {
            // Normalisasi field agar cocok dengan view
            $assignment['mobil_jenis'] = $assignment['mobil_jenis'] ?? ($assignment['jenis_mobil'] ?? $assignment['jenis'] ?? null);
            $assignment['mobil_plat']  = $assignment['mobil_plat']  ?? ($assignment['no_plat'] ?? $assignment['plat'] ?? null);

            // Ambil driver
            if (!empty($assignment['driver_id'])) {
                try {
                    $driverModel = new DriverModel();
                    $driverRow = $driverModel->find((int)$assignment['driver_id']);
                    if (!$driverRow) {
                        // fallback kalau driver_id menyimpan user_id
                        $driverRow = $driverModel->where('user_id', (int)$assignment['driver_id'])->first();
                    }
                    if ($driverRow) {
                        $driver = [
                            'nama'  => $driverRow['nama'] ?? null,
                            'no_hp' => $driverRow['no_hp'] ?? null,
                        ];
                    }
                } catch (\Throwable $e) {
                    $driver = null;
                }
            }
        }

        return view('user/car/detail', [
            'booking'    => $booking,
            'assignment' => $assignment,
            'driver'     => $driver,
        ]);
    }

    /** Hapus driver (admin) */
    public function driverDelete($id)
    {
        $driverModel = new DriverModel();
        $driver = $driverModel->find($id);
        if (!$driver) return redirect()->to('admin/driver')->with('error','Driver tidak ditemukan');

        // Cegah hapus jika driver sedang on duty? (opsional)
        // if (strtolower($driver['status'] ?? '') === 'on duty') {
        //     return redirect()->to('admin/driver')->with('error','Tidak bisa menghapus driver yang sedang On Duty.');
        // }

        // Hapus foto jika di bawah public/uploads
        if (!empty($driver['foto_url'])) {
            $urlPath = parse_url($driver['foto_url'], PHP_URL_PATH) ?: $driver['foto_url'];
            if ($urlPath) {
                if (!str_starts_with($urlPath, '/')) {
                    $urlPath = '/' . ltrim($urlPath, '/');
                }
                if (str_starts_with($urlPath, '/uploads/')) {
                    $filePath = rtrim(FCPATH, DIRECTORY_SEPARATOR) . $urlPath;
                    if (is_file($filePath) && is_writable($filePath)) {
                        @unlink($filePath);
                    }
                }
            }
        }

        try {
            $driverModel->delete($id);
            return redirect()->to('admin/driver')->with('success','Driver berhasil dihapus.');
        } catch (\Throwable $e) {
            return redirect()->to('admin/driver')->with('error','Gagal menghapus driver: ' . $e->getMessage());
        }
    }
}
