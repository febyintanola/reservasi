<?php

namespace App\Controllers;

use App\Models\CarModel;
use App\Models\DriverModel;
use CodeIgniter\Controller;
use DateTime;

class CarController extends BaseController
{
    public function index()
    {
        return redirect()->to('user/car/form');
    }

    public function form()
    {
        $session = session();
        $user_id = $session->get('user_id');

        if (!$user_id) {
            return redirect()->to('/login')->with('error', 'Silakan login dahulu.');
        }

        return view('user/car/form', ['user_id' => $user_id]);
    }

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

        return view('user/car/konfirmasi', ['data' => $data]);
    }
    //Admin 
    public function tambahdriver()
    {
        $model = new DriverModel();

        $data = [
            'nama'      => $this->request->getPost('nama'),
            'sim'       => $this->request->getPost('sim'),
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
            $booking = $carModel->find($id);
            if (!$booking) {
                return redirect()->to('admin')->with('error', 'Booking mobil tidak ditemukan');
            }
            $drivers = $driverModel->findAll();
            // Ambil assignment jika sudah ada
            $assignment = model('App\\Models\\DriverAssignmentModel')
                ->where('car_booking_id', $id)
                ->first();
            return view('admin/car/assign', [
                'booking' => $booking,
                'drivers' => $drivers,
                'assignment' => $assignment
            ]);
        }

        // Proses simpan assign driver & mobil
        public function assignSave($id)
        {
            $carModel = new CarModel();
            $booking = $carModel->find($id);
            if (!$booking) {
                return redirect()->to('admin')->with('error', 'Booking mobil tidak ditemukan');
            }
            $driver_id = $this->request->getPost('driver_id');
            $mobil_jenis = $this->request->getPost('mobil_jenis');
            $mobil_plat = $this->request->getPost('mobil_plat');

            $assignmentModel = model('App\\Models\\DriverAssignmentModel');
            $existing = $assignmentModel->where('car_booking_id', $id)->first();
            $dataAssign = [
                'car_booking_id' => $id,
                'driver_id' => $driver_id,
                'mobil_jenis' => $mobil_jenis,
                'mobil_plat' => $mobil_plat,
            ];
            if ($existing) {
                $assignmentModel->update($existing['id'], $dataAssign);
            } else {
                $assignmentModel->insert($dataAssign);
            }
            return redirect()->to('admin/car/detailMobil/' . $id)->with('message', 'Driver & Mobil berhasil di-assign.');
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

    public function driverCreate()
    {
        return view('admin/car/tambah');
    }

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
            'sim' => $this->request->getPost('sim'),
            'no_hp' => $this->request->getPost('no_hp'),
            'foto_url' => $fotoPath,
            'status' => $status,
        ]);
        return redirect()->to('admin/driver')->with('success', 'Driver ditambahkan.');
    }

    public function driverEdit($id)
    {
        $driverModel = new DriverModel();
        $driver = $driverModel->find($id);
        if (!$driver) return redirect()->to('admin/driver')->with('error','Driver tidak ditemukan');
        return view('admin/car/edit', ['driver' => $driver]);
    }

    public function driverUpdate($id)
    {
        $driverModel = new DriverModel();
        $driver = $driverModel->find($id);
        if (!$driver) return redirect()->to('admin/driver')->with('error','Driver tidak ditemukan');
        $dataUpdate = [
            'nama' => $this->request->getPost('nama'),
            'sim' => $this->request->getPost('sim'),
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
                $data['foto_url'] = base_url('uploads/' . $newName);
            } else {
                return redirect()->back()->withInput()->with('error', 'Gagal menyimpan gambar.');
            }
        }
        $driverModel->update($id,$dataUpdate);
        return redirect()->to('admin/driver')->with('success','Driver diperbarui.');
    }

    public function create()
    {
        return view('admin/car/tambah');
    }

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

    public function edit($id)
    {
        $model = new CarModel();
        $booking = $model->find($id);
        if (!$booking) {
            return redirect()->to('admin/car')->with('error', 'Data booking tidak ditemukan');
        }
        return view('admin/car/edit', ['booking' => $booking]);
    }

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
        $model->update($id, $data);
        return redirect()->to('admin/car')->with('success', 'Booking mobil berhasil diperbarui');
    }

    public function detail($id)
    {
        $model = new CarModel();
        $booking = $model->find($id);
        if (!$booking) {
            return redirect()->to('admin/car')->with('error', 'Data booking tidak ditemukan');
        }
        return view('admin/car/detail', ['booking' => $booking]);
    }

}
