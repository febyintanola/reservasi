<?php
namespace App\Controllers;

use App\Models\CarModel;
use App\Models\BookingRuangModel;
use App\Models\DriverAssignmentModel;
use App\Models\DriverModel;

class AdminDashboardController extends BaseController {
    public function index()
    {
        $bookingRuangModel = new BookingRuangModel();
        $bookingMobilModel = new CarModel();

        // Ambil data reservasi ruangan
        $ruang = $bookingRuangModel
            ->select('room_bookings.id, room_bookings.acara, room_bookings.tanggal, room_bookings.status, user_profile.nama as pemesan_nama, user_profile.divisi as pemesan_divisi')
            ->join('user_profile', 'user_profile.user_id = room_bookings.user_id', 'left')
            ->findAll();

        foreach ($ruang as &$item) {
            $item['tipe'] = 'Reservasi Ruangan';
        }

        // Ambil data reservasi mobil
        $mobil = $bookingMobilModel
            ->select('car_bookings.id, car_bookings.tujuan, car_bookings.tanggal_pergi, car_bookings.status, user_profile.nama as pemesan_nama, user_profile.divisi as pemesan_divisi')
            ->join('user_profile', 'user_profile.user_id = car_bookings.user_id', 'left')
            ->findAll();

        foreach ($mobil as &$item) {
            $item['tipe'] = 'Reservasi Mobil';
            $item['acara'] = $item['tujuan'];           // samakan key acara
            $item['tanggal'] = $item['tanggal_pergi']; // samakan key tanggal
            unset($item['tujuan'], $item['tanggal_pergi']);
        }

        // Gabung data reservasi ruangan dan mobil
        $bookings = array_merge($ruang, $mobil);

        // Urutkan berdasarkan tanggal terbaru
        usort($bookings, function($a, $b) {
            return strtotime($b['tanggal']) <=> strtotime($a['tanggal']);
        });

        // Hitung total reservasi dengan status 'pending'
        $totalBerjalan = 0;
        foreach ($bookings as $booking) {
            if (strtolower($booking['status']) === 'pending') {
                $totalBerjalan++;
            }
        }

        return view('admin/dashboard', [
            'bookings'     => $bookings,
            'totalRuang'   => count($ruang),
            'totalMobil'   => count($mobil),
            'totalBerjalan'=> $totalBerjalan
        ]);
    }

    // Detail booking ruang (lama)
    public function detail($id)
    {
        $bookingRuangModel = new BookingRuangModel();
        $ruang = $bookingRuangModel
            ->select('room_bookings.*, user_profile.nama as pemesan_nama, user_profile.divisi as pemesan_divisi')
            ->join('user_profile', 'user_profile.user_id = room_bookings.user_id', 'left')
            ->where('room_bookings.id', $id)
            ->first();
        if (!$ruang) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Booking ruang tidak ditemukan.');
        }
        try {
            $roomModel = model('App\\Models\\RoomModel');
            $room = $roomModel->find($ruang['room_id'] ?? null);
        } catch (\Throwable $e) {
            $room = null;
        }
        $data = [
            'id' => $ruang['id'],
            'pemesan_nama' => $ruang['pemesan_nama'] ?? '-',
            'pemesan_divisi' => $ruang['pemesan_divisi'] ?? '-',
            'judul' => $ruang['acara'] ?? '-',
            'tanggal' => $ruang['tanggal'] ?? '-',
            'waktu_mulai' => $ruang['jam_mulai'] ?? '-',
            'waktu_selesai' => $ruang['jam_selesai'] ?? '-',
            'lokasi' => $room['nama_ruangan'] ?? '-',
            'peserta' => $ruang['peserta'] ?? '-',
            'task' => $ruang['Task'] ?? '-',
            'permohonan' => $ruang['kebutuhan'] ?? '-',
            'keterangan' => $ruang['keterangan'] ?? '-',
            'status' => $ruang['status'] ?? '-',
        ];
        return view('admin/ruang/detail', $data);
    }

    // Detail booking mobil (baru)
    public function detailMobil($id)
    {
        $carModel = new CarModel();
        $mobil = $carModel
            ->select('car_bookings.*, user_profile.nama as pemesan_nama, user_profile.divisi as pemesan_divisi')
            ->join('user_profile', 'user_profile.user_id = car_bookings.user_id', 'left')
            ->where('car_bookings.id', $id)
            ->first();
        if (!$mobil) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Booking mobil tidak ditemukan.');
        }
        // Ambil data assignment (jika sudah di-assign)
        $assignmentModel = new DriverAssignmentModel();
        $assignment = $assignmentModel->where('car_booking_id', $id)->first();
        $driver = null;
        if ($assignment && !empty($assignment['driver_id'])) {
            $driverModel = new DriverModel();
            $driver = $driverModel->find($assignment['driver_id']);
        }
        return view('admin/car/detail', [
            'booking' => $mobil,
            'assignment' => $assignment,
            'driver' => $driver,
        ]);
    }

    public function approve($id)
    {
        $model = new BookingRuangModel();
        $booking = $model->find($id);
        if (!$booking) {
            return redirect()->to(base_url('admin/dashboard'))
                ->with('error', 'Booking tidak ditemukan');
        }
        $model->update($id, ['status' => 'accepted']);
        return redirect()->to(base_url('admin/booking/detail/' . $id))
            ->with('message', 'Booking telah disetujui');
    }

    public function reject($id)
    {
        $model = new BookingRuangModel();
        $booking = $model->find($id);
        if (!$booking) {
            return redirect()->to(base_url('admin/dashboard'))
                ->with('error', 'Booking tidak ditemukan');
        }
        $model->update($id, ['status' => 'rejected']);
        return redirect()->to(base_url('admin/booking/detail/' . $id))
            ->with('message', 'Booking telah ditolak');
    }

    // APPROVE booking mobil
    public function approveCar($id)
    {
        $carModel = new CarModel();
        $booking = $carModel->find($id);
        if (!$booking) {
            return redirect()->to(base_url('admin'))
                ->with('error', 'Booking mobil tidak ditemukan');
        }
        $carModel->update($id, ['status' => 'accepted']);
        return redirect()->to(base_url('admin/car/detailMobil/' . $id))
            ->with('message', 'Booking mobil disetujui');
    }

    // REJECT booking mobil
    public function rejectCar($id)
    {
        $carModel = new CarModel();
        $booking = $carModel->find($id);
        if (!$booking) {
            return redirect()->to(base_url('admin'))
                ->with('error', 'Booking mobil tidak ditemukan');
        }
        $carModel->update($id, ['status' => 'rejected']);
        return redirect()->to(base_url('admin/car/detailMobil/' . $id))
            ->with('message', 'Booking mobil ditolak');
    }

    protected CarModel $carModel;
    
    public function __construct()
    {
        $this->carModel = new CarModel();
    }

    public function carindex()
    {
        $bookings = $this->carModel->orderBy('tanggal_pergi', 'DESC')->findAll();
        return view('admin/car/list', ['bookings' => $bookings]);
    }

    public function tambah()
    {
        $rules =[
            'nama'            => 'required|min_length[2]',
            'tujuan'          => 'required',
            'keperluan'       => 'required|in_list[Dinas,Proyek]',
            'tanggal_pergi'   => 'required|valid_date',
            'tanggal_pulang'  => 'required|valid_date',
            'status'          => 'permit_empty|in_list[pending,accepted,rejected]'
        ];
        if (!$this->validate($rules)){
            return redirect()->back()->withInput()->with('error', implode("\n", $this->validator->getErrors()));
        }

        $tanggalPergi  = $this->request->getPost('tanggal_pergi');
        $tanggalPulang = $this->request->getPost('tanggal_pulang');
        $jumlahHari    = $this->request->getPost('jumlah_hari'); 

        $data = [
            'nama'            =>$this->request->getpost('nama'),
            'user_id'         =>$this->request->getpost('user_id'),
            'tujuan'          =>$this->request->getpost('tujuan'),
            'pengikut'        =>$this->request->getpost('pengikut'),
            'tanggal_pergi'   =>$tanggalPergi,
            'tanggal_pulang'  =>$tanggalPulang,
            'jumlah_hari'     =>$jumlahHari,
            'keperluan'       =>$this->request->getpost('keperluan'),
            'nama_pekerjaan'  =>$this->request->getpost('nama_pekerjaan'),
            'project_costing'  =>$this->request->getpost('project_costing'),
            'task_number'      => $this->request->getPost('task_number'),
            'expenditure_type' => $this->request->getPost('expenditure_type'),
            'expenditure_org'  => $this->request->getPost('expenditure_org'),
            'status'           =>$this->request->getpost('status')
        ];$this->carModel->insert($data);
        return redirect()->to(base_url('admin/car'))->with('success', 'Booking mobil berhasil ditambahkan');

    }

    private function hitungHari(string $tanggalPergi, string $tanggalPulang): int
    {
        try {
            $start = new DateTime($tanggalPergi);
            $end   = new DateTime($tanggalPulang);
            return $start->diff($end)->days + 1;
        } catch (\Throwable $e) {
            return 1;
        }
    }
    
}
