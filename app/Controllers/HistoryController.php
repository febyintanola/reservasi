<?php
namespace App\Controllers;

use App\Models\BookingRuangModel;
use App\Models\CarModel;
use App\Models\RoomModel;
use CodeIgniter\Controller;

/**
 * Riwayat reservasi user (ruangan dan mobil)
 */
class HistoryController extends BaseController
{
    private const PER_PAGE = 10;

    public function index()
    {
        $session = session();
        $userId = $session->get('user_id');
        if (!$userId) return redirect()->to('/login');

        $bookingRuangModel = new BookingRuangModel();
        $carModel = new CarModel();
        $ruangModel = new RoomModel();
        $perPage = self::PER_PAGE;

        // History ruang - limit 10, order DESC
        $ruangBookings = $bookingRuangModel->where('user_id', $userId)->orderBy('tanggal', 'DESC')->findAll($perPage);
        $historyRuang = array_map(function($row) use ($ruangModel) {
            $ruangan = $ruangModel->find($row['room_id']);
            $namaRuangan = is_array($ruangan) ? ($ruangan['nama_ruangan'] ?? '-') : ($ruangan->nama_ruangan ?? '-');
            return [
                'id' => $row['id'],
                'judul' => $row['acara'],
                'tanggal' => $row['tanggal'],
                'waktu_mulai' => $row['jam_mulai'],
                'waktu_selesai' => $row['jam_selesai'],
                'lokasi' => $namaRuangan,
                'status' => $row['status']
            ];
        }, $ruangBookings);

        // History mobil - limit 10, order DESC
        $mobilBookings = $carModel->where('user_id', $userId)->orderBy('tanggal_pergi', 'DESC')->findAll($perPage);
        $historyMobil = array_map(function($row) {
            return [
                'id' => $row['id'],
                'judul' => $row['keperluan'],
                'tanggal_pergi' => $row['tanggal_pergi'] ?? '-',
                'tanggal_pulang' => $row['tanggal_pulang'] ?? '-',
                'tujuan' => $row['tujuan'],
                'jumlah_hari' => $row['jumlah_hari'],
                'status' => $row['status']
            ];
        }, $mobilBookings);

    return view('user/history', [
            'historyRuang' => $historyRuang,
            'historyMobil' => $historyMobil
        ]);
    }

    /** AJAX load more history */
    public function load()
    {
        $session = session();
        $userId = $session->get('user_id');
        if (!$userId) {
            return $this->response->setJSON(['error' => 'Unauthorized'])->setStatusCode(401);
        }

        $type = $this->request->getGet('type'); // 'ruang' or 'mobil'
        $page = (int) $this->request->getGet('page') ?: 1;
        $perPage = self::PER_PAGE;
        $offset = ($page - 1) * $perPage;

        $items = [];
        if ($type === 'ruang') {
            $bookingRuangModel = new BookingRuangModel();
            $ruangModel = new RoomModel();
            $bookings = $bookingRuangModel->where('user_id', $userId)->orderBy('tanggal', 'DESC')->findAll($perPage, $offset);
            foreach ($bookings as $row) {
                $ruangan = $ruangModel->find($row['room_id']);
                $namaRuangan = is_array($ruangan) ? ($ruangan['nama_ruangan'] ?? '-') : ($ruangan->nama_ruangan ?? '-');
                $items[] = [
                    'id' => $row['id'],
                    'judul' => $row['acara'],
                    'tanggal' => $row['tanggal'],
                    'waktu_mulai' => $row['jam_mulai'],
                    'waktu_selesai' => $row['jam_selesai'],
                    'lokasi' => $namaRuangan,
                    'status' => $row['status']
                ];
            }
        } elseif ($type === 'mobil') {
            $carModel = new CarModel();
            $bookings = $carModel->where('user_id', $userId)->orderBy('tanggal_pergi', 'DESC')->findAll($perPage, $offset);
            foreach ($bookings as $row) {
                $items[] = [
                    'id' => $row['id'],
                    'judul' => $row['keperluan'],
                    'tanggal_pergi' => $row['tanggal_pergi'] ?? '-',
                    'tanggal_pulang' => $row['tanggal_pulang'] ?? '-',
                    'tujuan' => $row['tujuan'],
                    'jumlah_hari' => $row['jumlah_hari'],
                    'status' => $row['status']
                ];
            }
        }

        return $this->response->setJSON(['items' => $items]);
    }

    /** Detail riwayat booking ruang untuk user. */
    public function detailRuang($id)
    {
        $session = session();
        $userId = $session->get('user_id');
        if (!$userId) return redirect()->to('/login');

        $bookingRuangModel = new BookingRuangModel();
        $ruangModel = new RoomModel();

        $booking = $bookingRuangModel->find($id);
        if (!$booking || $booking['user_id'] != $userId) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Booking tidak ditemukan atau tidak milik Anda.');
        }

    $ruangan = $ruangModel->find($booking['room_id']);
    $namaRuangan = is_array($ruangan) ? ($ruangan['nama_ruangan'] ?? '-') : ($ruangan->nama_ruangan ?? '-');

        $data = [
            'id' => $booking['id'],
            'judul' => $booking['acara'],
            'tanggal' => $booking['tanggal'],
            'waktu_mulai' => $booking['jam_mulai'],
            'waktu_selesai' => $booking['jam_selesai'],
            'lokasi' => $namaRuangan,
            'peserta' => $booking['peserta'] ?? '-',         
            'task' => $booking['Task'] ?? '-',                
            'permohonan' => $booking['kebutuhan'] ?? '-',     
            'keterangan' => $booking['keterangan'] ?? '-',
            'status' => $booking['status'],
        ];

        return view('user/ruang/detail', $data);
    }

    /** Detail riwayat booking mobil untuk user. */
    public function detailMobil($id)
    {
        $session = session();
        $userId = $session->get('user_id');
        if (!$userId) return redirect()->to('/login');

        $carModel = new CarModel(); // ✅ Pastikan ini ADA

        $booking = $carModel->find($id);
        if (!$booking || $booking['user_id'] != $userId) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound("Reservasi mobil tidak ditemukan");
        }

        $data = [
            'id' => $booking['id'],
            'nama' => $booking['nama'],
            'tanggal_pergi' => $booking['tanggal_pergi'],
            'tanggal_pulang' => $booking['tanggal_pulang'],
            'pengikut' => $booking['pengikut'],
            'tujuan' => $booking['tujuan'],
            'jumlah_hari' => $booking['jumlah_hari'],
            'keperluan' => $booking['keperluan'],
            'nama_pekerjaan' => $booking['nama_pekerjaan'],
            'project_costing' => $booking['project_costing'],
            'task_number' => $booking['task_number'],
            'expenditure_type' => $booking['expenditure_type'],
            'expenditure_org' => $booking['expenditure_org'],
            'status' => $booking['status'],
        ];

        return view('user/car/detail', ['booking' => $booking]);
    }
}
