<?php
namespace App\Controllers;

use App\Models\CarModel;
use App\Models\BookingRuangModel;

class AdminDashboardController extends BaseController {
    public function index()
    {
        $bookingRuangModel = new BookingRuangModel();
        $bookingMobilModel = new CarModel();

        // Ambil data reservasi ruangan
        $ruang = $bookingRuangModel
            ->select('id, acara, tanggal, status')
            ->findAll();

        foreach ($ruang as &$item) {
            $item['tipe'] = 'Reservasi Ruangan';
        }

        // Ambil data reservasi mobil
        $mobil = $bookingMobilModel
            ->select('id, tujuan, tanggal_pergi, status')
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
}
