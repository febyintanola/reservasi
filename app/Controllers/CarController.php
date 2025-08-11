<?php

namespace App\Controllers;

use App\Models\CarModel;
use CodeIgniter\Controller;
use DateTime;

class CarController extends BaseController
{
    public function index()
    {
        return redirect()->to('/car/form');
    }

    public function form()
    {
        $session = session();
        $user_id = $session->get('user_id');

        if (!$user_id) {
            return redirect()->to('/login')->with('error', 'Silakan login dahulu.');
        }

        return view('car/form', ['user_id' => $user_id]);
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

        return view('car/konfirmasi', ['data' => $data]);
    }
}
