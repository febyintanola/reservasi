<?php

namespace App\Controllers;

use App\Models\RoomModel;
use App\Models\BookingRuangModel;

class RoomController extends BaseController
{
    public function index() {
        $model = new RoomModel();
        $data['rooms'] = $model->findAll();
        return view('user/ruang/index', $data);
    }

    public function ajaxCheckAvailability()
    {
        $tanggal = $this->request->getGet('tanggal');
        $jam_mulai = $this->request->getGet('jam_mulai');
        $jam_selesai = $this->request->getGet('jam_selesai');

        $roomModel = new \App\Models\RoomModel();
        $rooms = $roomModel->findAll();

        $bookingModel = new \App\Models\BookingRuangModel();

        foreach ($rooms as &$room) {
            
            $conflict = $bookingModel->where('room_id', $room['id'])
            ->where('tanggal', $tanggal)
            ->groupStart()
            ->where("jam_mulai <", $jam_selesai)
            ->where("jam_selesai >", $jam_mulai)
            ->groupEnd()
            ->whereIn('status', ['pending', 'approved'])
            ->first();
            
            $room['tersedia'] = $conflict ? false : true;
    }
    return $this->response->setJSON($rooms);
}

    public function checkAvailability()
    {
        $tanggal = $this->request->getPost('tanggal');
        $jam_mulai = $this->request->getPost('jam_mulai');
        $jam_selesai = $this->request->getPost('jam_selesai');

        $roomModel = new RoomModel();
        $rooms = $roomModel->findAll();

        $bookingModel = new BookingRuangModel();

        foreach ($rooms as &$room) {
            $conflict = $bookingModel->where('room_id', $room['id'])
                ->where('tanggal', $tanggal)
                ->groupStart()
                    ->where("jam_mulai <", $jam_selesai)
                    ->where("jam_selesai >", $jam_mulai)
                ->groupEnd()
                ->whereIn('status', ['pending', 'approved'])
                ->first();

            $room['tersedia'] = $conflict ? false : true;
        }

        return view('user/ruang/available', [
            'rooms' => $rooms,
            'tanggal' => $tanggal,
            'jam_mulai' => $jam_mulai,
            'jam_selesai' => $jam_selesai,
        ]);
    }
      public function bookingForm()
    {
        $roomId     = $this->request->getGet('room');
        $tanggal    = $this->request->getGet('tanggal');
        $jamMulai   = $this->request->getGet('jam_mulai');
        $jamSelesai = $this->request->getGet('jam_selesai');

        // ✅ Ambil user_id dari session
        $userid = session()->get('user_id');

        // Cek kalau user_id tidak tersedia
        if (!$userid) {
            return redirect()->to('/login')->with('error', 'Silakan login dulu.');
        }

        // Ambil data ruangan dari DB
        $roomModel = new \App\Models\RoomModel();
        $room = $roomModel->find($roomId);

        // Kirim semua data ke view
        return view('user/ruang/form', [
            'room'       => $room,
            'roomId'     => $roomId,
            'tanggal'    => $tanggal,
            'jamMulai'   => $jamMulai,
            'jamSelesai' => $jamSelesai,
            'userid'     => $userid 
        ]);
    }



    public function saveBooking()
    {
        $bookingModel = new BookingRuangModel();

        $data = [
            'acara'      => $this->request->getPost('acara'),
            'tanggal'    => $this->request->getPost('tanggal'),
            'jam_mulai'  => $this->request->getPost('jam_mulai'),
            'jam_selesai'=> $this->request->getPost('jam_selesai'),
            'peserta'    => $this->request->getPost('peserta'),
            'task'       => $this->request->getPost('Task'),
            'kebutuhan'  => implode(',', $this->request->getPost('kebutuhan') ?? []),
            'keterangan' => $this->request->getPost('keterangan'),
            'procost'    => $this->request->getPost('Procost'),
            'exptype'    => $this->request->getPost('exptype'),
            'room_id'    => $this->request->getPost('room_id'),
            'user_id'    => $this->request->getPost('user_id')
        ];

        $bookingModel->save($data);

        return view('user/ruang/konfirmasi', ['data' => $data]);
    }

    // Add the store method to handle room creation
    public function store()
    {
        // Collect POST data
        $data = [
            'nama_ruangan' => $this->request->getPost('nama'),
            'lokasi'       => $this->request->getPost('lokasi'),
            'kapasitas'    => $this->request->getPost('kapasitas'),
            'jenis'        => $this->request->getPost('jenis'),
        ];

        // Optional: handle image upload
        $foto = $this->request->getFile('foto');
        if ($foto && $foto->isValid() && !$foto->hasMoved()) {
            $allowed = ['image/jpeg', 'image/png', 'image/gif'];
            if (in_array($foto->getMimeType(), $allowed)) {
                $newName = $foto->getRandomName();
                $foto->move(WRITEPATH . 'uploads', $newName);
                $data['ruangrapat_url'] = base_url('writable/uploads/' . $newName);
            }
        }
        $roomModel = new \App\Models\RoomModel();

        // Insert data into the database
        if ($roomModel->insert($data)) {
            return redirect()->to('/admin/ruang')->with('success', 'Ruangan berhasil ditambahkan.');
        } else {
            return redirect()->back()->with('error', 'Gagal menambahkan ruangan.');
        }
    }

    // Admin list rooms
    public function adminIndex()
    {
        $model = new RoomModel();
        $data['rooms'] = $model->findAll();
        return view('admin/ruang/list', $data);
    }

    // Admin create room form
    public function create()
    {
        $data['jenisList'] = ['Teater', 'Classroom'];
        return view('admin/ruang/tambah', $data);
    }
}
