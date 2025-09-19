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
    public function index()
    {
        $session = session();
        $userId = $session->get('user_id');
        if (!$userId) return redirect()->to('/login');

        $bookingRuangModel = new BookingRuangModel();
        $carModel = new CarModel();
        $ruangModel = new RoomModel();

        // History ruang
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
        }, $bookingRuangModel->getByUser($userId));

        // History mobil
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
        }, $carModel->getByUser($userId));

    return view('user/history', [
            'historyRuang' => $historyRuang,
            'historyMobil' => $historyMobil
        ]);
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
