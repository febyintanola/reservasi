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

}
