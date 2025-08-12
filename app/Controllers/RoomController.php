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
        // Validate required fields first
        $rules = [
            'nama'      => 'required|min_length[2]',
            'lokasi'    => 'required',
            'kapasitas' => 'required|is_natural_no_zero',
            'jenis'     => 'required',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode("\n", $this->validator->getErrors()));
        }

        $data = [
            'nama_ruangan' => $this->request->getPost('nama'),
            'lokasi'       => $this->request->getPost('lokasi'),
            'kapasitas'    => $this->request->getPost('kapasitas'),
            'jenis'        => $this->request->getPost('jenis'),
        ];

        // Handle image upload to public/uploads
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
                $data['ruangrapat_url'] = base_url('uploads/' . $newName);
            } else {
                return redirect()->back()->withInput()->with('error', 'Gagal menyimpan gambar.');
            }
        }

        $roomModel = new \App\Models\RoomModel();
        if ($roomModel->insert($data)) {
            return redirect()->to('/admin/ruang')->with('success', 'Ruangan berhasil ditambahkan.');
        }

        // Gather model/DB errors for easier debugging
        $modelErrors = $roomModel->errors();
        $dbError = $roomModel->db->error();
        $errMsg = '';
        if (!empty($modelErrors)) {
            $errMsg .= implode("\n", $modelErrors) . "\n";
        }
        if (!empty($dbError['message'])) {
            $errMsg .= 'DB: ' . $dbError['message'];
        }
        if ($errMsg === '') {
            $errMsg = 'Gagal menambahkan ruangan (alasan tidak diketahui).';
        }
        return redirect()->back()->withInput()->with('error', $errMsg);
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
        $data['jenisList'] = ['Theater', 'Classroom'];
        return view('admin/ruang/tambah', $data);
    }

    // Admin edit room form
    public function edit($id)
    {
        $model = new RoomModel();
        $room = $model->find($id);
        if (!$room) {
            return redirect()->to('/admin/ruang')->with('error', 'Ruangan tidak ditemukan.');
        }

        $data = [
            'room' => $room,
            'jenisList' => ['Theater', 'Classroom'],
        ];
        return view('admin/ruang/edit', $data);
    }

    // Admin update room action
    public function update($id)
    {
        $model = new RoomModel();
        $room = $model->find($id);
        if (!$room) {
            return redirect()->to('/admin/ruang')->with('error', 'Ruangan tidak ditemukan.');
        }

        $rules = [
            'nama'      => 'required|min_length[2]',
            'lokasi'    => 'required',
            'kapasitas' => 'required|is_natural_no_zero',
            'jenis'     => 'required',
        ];
        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', implode("\n", $this->validator->getErrors()));
        }

        $data = [
            'nama_ruangan' => $this->request->getPost('nama'),
            'lokasi'       => $this->request->getPost('lokasi'),
            'kapasitas'    => $this->request->getPost('kapasitas'),
            'jenis'        => $this->request->getPost('jenis'),
        ];

        // Optional image upload replacement
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
                $data['ruangrapat_url'] = base_url('uploads/' . $newName);
            } else {
                return redirect()->back()->withInput()->with('error', 'Gagal menyimpan gambar.');
            }
        }

        if ($model->update($id, $data)) {
            return redirect()->to('/admin/ruang')->with('success', 'Ruangan berhasil diperbarui.');
        }

        $modelErrors = $model->errors();
        $dbError = $model->db->error();
        $errMsg = '';
        if (!empty($modelErrors)) {
            $errMsg .= implode("\n", $modelErrors) . "\n";
        }
        if (!empty($dbError['message'])) {
            $errMsg .= 'DB: ' . $dbError['message'];
        }
        if ($errMsg === '') {
            $errMsg = 'Gagal memperbarui ruangan (alasan tidak diketahui).';
        }
        return redirect()->back()->withInput()->with('error', $errMsg);
    }
}
