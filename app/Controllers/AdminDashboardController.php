<?php
namespace App\Controllers;

use App\Models\CarModel;
use App\Models\BookingRuangModel;
use App\Models\DriverAssignmentModel;
use App\Models\DriverModel;
use App\Models\RoomModel;

/**
 * Dashboard Admin
 *
 * Menggabungkan data reservasi ruang dan mobil, menampilkan ringkasan,
 * dan menyediakan aksi approve/reject untuk kedua jenis reservasi.
 */
class AdminDashboardController extends BaseController {
    public function index()
    {
        $bookingRuangModel = new BookingRuangModel();
        $bookingMobilModel = new CarModel();
        $roomModel         = new RoomModel();

        $today = date('Y-m-d');

        // Ambil data reservasi ruangan beserta pemesan (profil)
        $ruang = $bookingRuangModel
            ->select('room_bookings.id, room_bookings.acara, room_bookings.tanggal, room_bookings.status, user_profile.nama as pemesan_nama, user_profile.divisi as pemesan_divisi')
            ->join('user_profile', 'user_profile.user_id = room_bookings.user_id', 'left')
            ->findAll();

        foreach ($ruang as &$item) {
            $item['tipe'] = 'Reservasi Ruangan';
        }

        // Ambil data reservasi mobil beserta pemesan (profil)
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

        // Hitung total reservasi dengan status 'pending' (menunggu persetujuan)
        $totalBerjalan = 0;
        $reservasiHariIni = 0;
        $todayBookings = [];
        foreach ($bookings as $booking) {
            $status = strtolower($booking['status'] ?? '');
            if ($status === 'pending') {
                $totalBerjalan++;
            }
            if (!empty($booking['tanggal']) && substr($booking['tanggal'], 0, 10) === $today) {
                $reservasiHariIni++;
                $todayBookings[] = $booking;
            }
        }

        // Utilisasi ruang: berapa ruang terpakai hari ini (pending/accepted) dibanding total ruang
        $totalRooms = (int) $roomModel->countAllResults();
        $ruangDipakaiHariIni = 0;
        if ($totalRooms > 0) {
            $roomsBookedToday = $bookingRuangModel
                ->select('room_id, status')
                ->where('tanggal', $today)
                ->whereIn('status', ['pending', 'accepted'])
                ->findAll();
            $uniqueRoomIds = [];
            foreach ($roomsBookedToday as $rb) {
                if (!empty($rb['room_id'])) {
                    $uniqueRoomIds[$rb['room_id']] = true;
                }
            }
            $ruangDipakaiHariIni = count($uniqueRoomIds);
        }
        $utilisasiRuangPersen = $totalRooms > 0 ? round(($ruangDipakaiHariIni / $totalRooms) * 100) : 0;

        // Jadwal hari ini (detail) - Ruang Rapat
        $todayRoomSchedule = $bookingRuangModel
            ->select('room_bookings.id, room_bookings.acara, room_bookings.tanggal, room_bookings.jam_mulai, room_bookings.jam_selesai, rooms.nama_ruangan, user_profile.nama as pemesan_nama, user_profile.divisi as pemesan_divisi, room_bookings.status')
            ->join('rooms', 'rooms.id = room_bookings.room_id', 'left')
            ->join('user_profile', 'user_profile.user_id = room_bookings.user_id', 'left')
            ->where('room_bookings.tanggal', $today)
            ->orderBy('room_bookings.jam_mulai', 'ASC')
            ->findAll();

        // Jadwal hari ini (detail) - Mobil, include driver assignment & driver data
        $assignmentModel = new DriverAssignmentModel();
        $driverModel     = new DriverModel();
        // booking yang aktif di hari ini: antara tanggal_pergi dan tanggal_pulang menyertakan hari ini
        $todayCarSchedule = $bookingMobilModel
            ->select('car_bookings.id, car_bookings.tujuan, car_bookings.tanggal_pergi, car_bookings.tanggal_pulang, car_bookings.status, user_profile.nama as pemesan_nama, user_profile.divisi as pemesan_divisi')
            ->join('user_profile', 'user_profile.user_id = car_bookings.user_id', 'left')
            ->groupStart()
                ->where('car_bookings.tanggal_pergi <=', $today)
                ->where('car_bookings.tanggal_pulang >=', $today)
            ->groupEnd()
            ->orderBy('car_bookings.tanggal_pergi', 'ASC')
            ->findAll();

        // Tambahkan info assignment dan driver (nama, foto, plat/jenis jika ada)
        foreach ($todayCarSchedule as &$c) {
            $assign = $assignmentModel->where('car_booking_id', $c['id'])->first();
            $c['driver_nama'] = null;
            $c['driver_foto'] = null;
            $c['mobil_plat']  = $assign['mobil_plat'] ?? null;
            $c['mobil_jenis'] = $assign['mobil_jenis'] ?? null;
            if ($assign && !empty($assign['driver_id'])) {
                // Coba asumsikan driver_id mengarah ke drivers.id, jika tidak ada coba map via users.id
                $driver = $driverModel->find((int)$assign['driver_id']);
                if (!$driver) {
                    $byUser = $driverModel->where('user_id', (int)$assign['driver_id'])->first();
                    $driver = $byUser ?: null;
                }
                if ($driver) {
                    if (is_array($driver)) {
                        $c['driver_nama'] = $driver['nama'] ?? null;
                        $c['driver_foto'] = $driver['foto_url'] ?? null;
                    } else {
                        $c['driver_nama'] = $driver->nama ?? null;
                        $c['driver_foto'] = $driver->foto_url ?? null;
                    }
                }
            }
        }

        return view('admin/dashboard', [
            'bookings'     => $bookings,
            'totalRuang'   => count($ruang),
            'totalMobil'   => count($mobil),
            'totalBerjalan'=> $totalBerjalan,
            'reservasiHariIni' => $reservasiHariIni,
            'todayBookings'    => $todayBookings,
            'totalRooms'       => $totalRooms,
            'ruangDipakaiHariIni' => $ruangDipakaiHariIni,
            'utilisasiRuangPersen' => $utilisasiRuangPersen,
            'todayRoomSchedule' => $todayRoomSchedule,
            'todayCarSchedule'  => $todayCarSchedule,
        ]);
        
    }

    // Detail booking ruang
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

    // Detail booking mobil
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

    /**
     * Setujui booking mobil serta sinkronkan status driver menjadi On Duty
     * bila sudah ada penugasan.
     */
    public function approveCar($id)
    {
        $carModel = new CarModel();
        $booking = $carModel->find($id);
        if (!$booking) {
            return redirect()->to(base_url('admin'))
                ->with('error', 'Booking mobil tidak ditemukan');
        }
        $carModel->update($id, ['status' => 'accepted']);

        // Sync status driver -> On Duty ketika booking diterima (jika ada assignment)
        try {
            $assignmentModel = new DriverAssignmentModel();
            $assignment = $assignmentModel->where('car_booking_id', $id)->first();
            if ($assignment) {
                $driverModel = new DriverModel();
                $driverId = null;
                $legacyUserId = null;

                // Coba treat driver_id sebagai drivers.id (baru)
                $maybeDriver = $driverModel->find((int)$assignment['driver_id']);
                if ($maybeDriver) {
                    // Support hasil array atau object (entity)
                    if (is_array($maybeDriver)) {
                        $driverId = (int)($maybeDriver['id'] ?? 0);
                        $legacyUserId = $maybeDriver['user_id'] ?? null; // jika sudah terhubung
                    } else {
                        $driverId = (int)($maybeDriver->id ?? 0);
                        $legacyUserId = $maybeDriver->user_id ?? null;
                    }
                } else {
                    // Legacy: driver_assignments.driver_id menyimpan users.id
                    $legacyUserId = (int)$assignment['driver_id'];
                    $byUser = $driverModel->where('user_id', $legacyUserId)->first();
                    if ($byUser) {
                        $driverId = (int)$byUser['id'];
                    }
                }

                if ($driverId) {
                    // Set On Duty saat accepted
                    $driverModel->update($driverId, ['status' => 'On Duty']);
                }
            }
        } catch (\Throwable $e) {
            // abaikan jika gagal sync
        }
        return redirect()->to(base_url('admin/car/detailMobil/' . $id))
            ->with('message', 'Booking mobil disetujui');
    }

    /**
     * Tolak booking mobil serta kembalikan status driver ke Available
     * jika tidak ada tugas lain yang masih berjalan.
     */
    public function rejectCar($id)
    {
        $carModel = new CarModel();
        $booking = $carModel->find($id);
        if (!$booking) {
            return redirect()->to(base_url('admin'))
                ->with('error', 'Booking mobil tidak ditemukan');
        }
        $carModel->update($id, ['status' => 'rejected']);

        // Sync status driver -> Available jika tidak ada tugas ongoing lain
        try {
            $assignmentModel = new DriverAssignmentModel();
            $assignment = $assignmentModel->where('car_booking_id', $id)->first();
            if ($assignment) {
                $driverModel = new DriverModel();
                $driverId = null;
                $legacyUserId = null;

                // Coba treat driver_id sebagai drivers.id (baru)
                $maybeDriver = $driverModel->find((int)$assignment['driver_id']);
                if ($maybeDriver) {
                    if (is_array($maybeDriver)) {
                        $driverId = (int)($maybeDriver['id'] ?? 0);
                        $legacyUserId = $maybeDriver['user_id'] ?? null;
                    } else {
                        $driverId = (int)($maybeDriver->id ?? 0);
                        $legacyUserId = $maybeDriver->user_id ?? null;
                    }
                } else {
                    // Legacy: driver_assignments.driver_id menyimpan users.id
                    $legacyUserId = (int)$assignment['driver_id'];
                    $byUser = $driverModel->where('user_id', $legacyUserId)->first();
                    if ($byUser) {
                        $driverId = (int)$byUser['id'];
                    }
                }

                if ($driverId) {
                    // Cek apakah masih ada booking lain yang ongoing untuk driver ini
                    $hasOtherOngoing = (int)$assignmentModel
                        ->select('driver_assignments.id')
                        ->join('car_bookings', 'car_bookings.id = driver_assignments.car_booking_id')
                        ->groupStart()
                            ->where('driver_assignments.driver_id', $driverId)
                            ->orWhere('driver_assignments.driver_id', $legacyUserId)
                        ->groupEnd()
                        ->where('car_bookings.status', 'ongoing')
                        ->where('car_bookings.id !=', $id)
                        ->countAllResults() > 0;
                    if (!$hasOtherOngoing) {
                        $driverModel->update($driverId, ['status' => 'Available']);
                    }
                }
            }
        } catch (\Throwable $e) {
            // abaikan jika gagal sync
        }
        return redirect()->to(base_url('admin/car/detailMobil/' . $id))
            ->with('message', 'Booking mobil ditolak');
    }

    /** @var CarModel Model untuk CRUD booking mobil (admin). */
    protected CarModel $carModel;
    
    public function __construct()
    {
        $this->carModel = new CarModel();
    }

    /**
     * Daftar booking mobil (admin)
     */
    public function carindex()
    {
        $bookings = $this->carModel->orderBy('tanggal_pergi', 'DESC')->findAll();
        return view('admin/car/list', ['bookings' => $bookings]);
    }

    /**
     * Tambah booking mobil (admin membuat manual)
     */
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

    /** Hitung jumlah hari antara tanggal pergi dan pulang (inklusif). */
    private function hitungHari(string $tanggalPergi, string $tanggalPulang): int
    {
        try {
            $start = new \DateTime($tanggalPergi);
            $end   = new \DateTime($tanggalPulang);
            return $start->diff($end)->days + 1;
        } catch (\Throwable $e) {
            return 1;
        }
    }
}
