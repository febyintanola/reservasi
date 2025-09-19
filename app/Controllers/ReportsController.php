<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\BookingRuangModel;
use App\Models\DriverAssignmentModel;
use App\Libraries\ExportService;

class ReportsController extends BaseController
{
    /**
     * Pilih nama kolom pertama yang tersedia dari daftar kandidat.
     * Digunakan untuk menyesuaikan perbedaan skema/tabel (mis. created_at vs tanggal).
     *
     * @param array $row        Contoh baris dari DB untuk deteksi kolom
     * @param array $candidates Daftar nama kolom yang mungkin ada (urutkan dari prioritas tertinggi)
     * @return string|null      Nama kolom yang ditemukan atau null jika tidak ada
     */
    private function pickExistingKey(array $row, array $candidates): ?string
    {
        foreach ($candidates as $c) {
            if (array_key_exists($c, $row)) {
                return $c;
            }
        }
        return null;
    }

    /**
     * Halaman laporan ringkas (statistik + data terbaru) untuk ruang dan kendaraan.
     * Rentang tanggal default: bulan berjalan.
     */
    public function index()
    {
        $start = $this->request->getGet('start_date') ?? date('Y-m-01');
        $end   = $this->request->getGet('end_date')   ?? date('Y-m-t');

        $roomModel = new BookingRuangModel();
        $carModel  = new \App\Models\CarModel();

    // Deteksi kolom dinamis (menghindari error bila skema berbeda antar lingkungan)
        $roomSample = $roomModel->select('*')->limit(1)->first() ?? [];
        $carSample  = $carModel->select('*')->limit(1)->first() ?? [];

        $roomDateCol   = $this->pickExistingKey($roomSample, ['tanggal','created_at','jam_mulai']);
        $carDateCol    = $this->pickExistingKey($carSample,  ['tanggal_pergi','created_at','tanggal_pulang']);
        $roomStatusCol = $this->pickExistingKey($roomSample, ['status','status_booking','approval_status']);
        $carStatusCol  = $this->pickExistingKey($carSample,  ['status','status_assignment','approval_status']);

        // Statistik ruang
        $roomQuery = $roomModel;
        if ($roomDateCol) {
            $roomQuery = $roomQuery
                ->where("$roomDateCol >=", $start)
                ->where("$roomDateCol <=", $end);
        }
        $roomTotal = $roomQuery->countAllResults(false);

        $roomByStatus = [];
        if ($roomStatusCol) {
            $roomByStatusQuery = $roomModel->select("$roomStatusCol AS status, COUNT(*) as total");
            if ($roomDateCol) {
                $roomByStatusQuery
                    ->where("$roomDateCol >=", $start)
                    ->where("$roomDateCol <=", $end);
            }
            $roomByStatus = $roomByStatusQuery->groupBy($roomStatusCol)->findAll();
        }

        // Statistik kendaraan
        $carQuery = $carModel;
        if ($carDateCol) {
            $carQuery = $carQuery
                ->where("$carDateCol >=", $start)
                ->where("$carDateCol <=", $end);
        }
        $carTotal = $carQuery->countAllResults(false);

        $carByStatus = [];
        if ($carStatusCol) {
            $carByStatusQuery = $carModel->select("$carStatusCol AS status, COUNT(*) as total");
            if ($carDateCol) {
                $carByStatusQuery
                    ->where("$carDateCol >=", $start)
                    ->where("$carDateCol <=", $end);
            }
            $carByStatus = $carByStatusQuery->groupBy($carStatusCol)->findAll();
        }

        // Data terbaru (10 data terakhir)
        // Enrich rooms with user name (from user_profile.nama if available)
        $recentRoomsQuery = $roomModel->select('room_bookings.*, user_profile.nama AS user_name')
            ->join('user_profile', 'user_profile.user_id = room_bookings.user_id', 'left');
        if ($roomDateCol) {
            $recentRoomsQuery = $recentRoomsQuery
                ->where("room_bookings.$roomDateCol >=", $start)
                ->where("room_bookings.$roomDateCol <=", $end)
                ->orderBy("room_bookings.$roomDateCol", 'DESC');
        } else {
            $recentRoomsQuery = $recentRoomsQuery->orderBy('room_bookings.id', 'DESC');
        }
        $recentRooms = $recentRoomsQuery->limit(10)->find();

        // Enrich cars with booking info and requester name from car_bookings
        $recentCarsQuery = $carModel
            ->select('car_bookings.*, user_profile.nama AS user_name, driver_assignments.mobil_jenis, driver_assignments.mobil_plat')
            ->join('user_profile', 'user_profile.user_id = car_bookings.user_id', 'left')
            ->join('driver_assignments', 'driver_assignments.car_booking_id = car_bookings.id', 'left');
        if ($carDateCol) {
            // If car_bookings has a date column detected
            $recentCarsQuery = $recentCarsQuery
                ->where("car_bookings.$carDateCol >=", $start)
                ->where("car_bookings.$carDateCol <=", $end)
                ->orderBy("car_bookings.$carDateCol", 'DESC');
        } else {
            // Fall back to car_bookings date range if available
            $recentCarsQuery = $recentCarsQuery
                ->where('car_bookings.tanggal_pergi >=', $start)
                ->where('car_bookings.tanggal_pergi <=', $end)
                ->orderBy('car_bookings.tanggal_pergi', 'DESC');
        }
        $recentCars = $recentCarsQuery->limit(10)->find();

        return view('admin/reports', [
            'start' => $start,
            'end'   => $end,
            'roomTotal' => $roomTotal,
            'roomByStatus' => $roomByStatus,
            'carTotal' => $carTotal,
            'carByStatus' => $carByStatus,
            'recentRooms' => $recentRooms,
            'recentCars'  => $recentCars,
            'roomDateCol' => $roomDateCol,
            'carDateCol'  => $carDateCol,
            // Fields for display in view
            'roomUserNameField' => 'user_name',
            'carUserNameField'  => 'user_name',
            'carDateDisplayCol' => $carDateCol ?: 'tanggal_pergi',
            'roomStatusCol' => $roomStatusCol,
            'carStatusCol'  => $carStatusCol,
        ]);
    }

    /**
     * Export data booking ruang ke Excel sesuai rentang tanggal.
     */
    public function exportRoomsExcel()
    {
        $start = $this->request->getGet('start_date') ?? date('Y-m-01');
        $end   = $this->request->getGet('end_date')   ?? date('Y-m-t');

        $roomModel = new BookingRuangModel();
        $sample = $roomModel->select('*')->limit(1)->first() ?? [];
        $roomDateCol   = $this->pickExistingKey($sample, ['tanggal','created_at','jam_mulai']);
        $roomStatusCol = $this->pickExistingKey($sample, ['status','status_booking','approval_status']);

        // Join to get requester name
        $query = $roomModel->select('room_bookings.*, user_profile.nama AS user_name')
            ->join('user_profile', 'user_profile.user_id = room_bookings.user_id', 'left');
        if ($roomDateCol) {
            $query = $query
                ->where("room_bookings.$roomDateCol >=", $start)
                ->where("room_bookings.$roomDateCol <=", $end)
                ->orderBy("room_bookings.$roomDateCol", 'DESC');
        }
        $rows = $query->findAll();

        // Header kolom file Excel
        $headers = ['Tanggal', 'Pemesan', 'Ruang', 'Acara', 'Status'];
        $data = array_map(function ($r) use ($roomDateCol, $roomStatusCol) {
            return [
                $roomDateCol ? ($r[$roomDateCol] ?? '') : '',
                $r['user_name'] ?? ($r['nama'] ?? ($r['user_id'] ?? '')),
                $r['room_id'] ?? '',
                $r['acara'] ?? '',
                $roomStatusCol ? ($r[$roomStatusCol] ?? '') : '',
            ];
        }, $rows);

        $export = new ExportService();
        return $export->exportExcel('laporan_ruang.xlsx', $headers, $data);
    }

    /**
     * Export data booking ruang ke PDF sesuai rentang tanggal.
     */
    public function exportRoomsPdf()
    {
        $start = $this->request->getGet('start_date') ?? date('Y-m-01');
        $end   = $this->request->getGet('end_date')   ?? date('Y-m-t');

        $roomModel = new BookingRuangModel();
        $sample = $roomModel->select('*')->limit(1)->first() ?? [];
        $roomDateCol   = $this->pickExistingKey($sample, ['tanggal','created_at','jam_mulai']);
        $roomStatusCol = $this->pickExistingKey($sample, ['status','status_booking','approval_status']);

        // Join to get requester name
        $query = $roomModel->select('room_bookings.*, user_profile.nama AS user_name')
            ->join('user_profile', 'user_profile.user_id = room_bookings.user_id', 'left');
        if ($roomDateCol) {
            $query = $query
                ->where("room_bookings.$roomDateCol >=", $start)
                ->where("room_bookings.$roomDateCol <=", $end)
                ->orderBy("room_bookings.$roomDateCol", 'DESC');
        }
        $rows = $query->findAll();

        $headers = ['Tanggal', 'Pemesan', 'Ruang', 'Acara', 'Status'];
        $data = array_map(function ($r) use ($roomDateCol, $roomStatusCol) {
            return [
                $roomDateCol ? ($r[$roomDateCol] ?? '') : '',
                $r['user_name'] ?? ($r['nama'] ?? ($r['user_id'] ?? '')),
                $r['room_id'] ?? '',
                $r['acara'] ?? '',
                $roomStatusCol ? ($r[$roomStatusCol] ?? '') : '',
            ];
        }, $rows);

        $export = new ExportService();
        return $export->exportPdf('laporan_ruang.pdf', 'Laporan Booking Ruang', $headers, $data);
    }

    /**
     * Export data booking mobil ke Excel sesuai rentang tanggal.
     */
    public function exportCarsExcel()
    {
        $start = $this->request->getGet('start_date') ?? date('Y-m-01');
        $end   = $this->request->getGet('end_date')   ?? date('Y-m-t');

        $carModel = new \App\Models\CarModel();
        $sample = $carModel->select('*')->limit(1)->first() ?? [];
        $carDateCol   = $this->pickExistingKey($sample, ['tanggal_pergi','created_at','tanggal_pulang']);
        $carStatusCol = $this->pickExistingKey($sample, ['status','status_booking','approval_status']);

        // Query car bookings directly with user names
        $query = $carModel
            ->select('car_bookings.*, user_profile.nama AS user_name, driver_assignments.mobil_jenis, driver_assignments.mobil_plat')
            ->join('user_profile', 'user_profile.user_id = car_bookings.user_id', 'left')
            ->join('driver_assignments', 'driver_assignments.car_booking_id = car_bookings.id', 'left');
        if ($carDateCol) {
            $query = $query
                ->where("car_bookings.$carDateCol >=", $start)
                ->where("car_bookings.$carDateCol <=", $end)
                ->orderBy("car_bookings.$carDateCol", 'DESC');
        } else {
            // Filter by car booking date if assignment lacks date
            $query = $query
                ->where('car_bookings.tanggal_pergi >=', $start)
                ->where('car_bookings.tanggal_pergi <=', $end)
                ->orderBy('car_bookings.tanggal_pergi', 'DESC');
        }
        $rows = $query->findAll();

        $headers = ['Tanggal Pergi', 'Pemesan', 'Tujuan', 'Keperluan', 'Status'];
        $data = array_map(function ($r) use ($carDateCol, $carStatusCol) {
            return [
                ($carDateCol ? ($r[$carDateCol] ?? '') : ($r['tanggal_pergi'] ?? '')),
                $r['user_name'] ?? ($r['nama'] ?? ($r['user_id'] ?? '')),
                $r['tujuan'] ?? '',
                $r['keperluan'] ?? ($r['nama_pekerjaan'] ?? ''),
                ($carStatusCol ? ($r[$carStatusCol] ?? '') : ($r['status'] ?? '')),
            ];
        }, $rows);

        $export = new ExportService();
        return $export->exportExcel('laporan_mobil.xlsx', $headers, $data);
    }

    /**
     * Export data booking mobil ke PDF sesuai rentang tanggal.
     */
    public function exportCarsPdf()
    {
        $start = $this->request->getGet('start_date') ?? date('Y-m-01');
        $end   = $this->request->getGet('end_date')   ?? date('Y-m-t');

        $carModel = new \App\Models\CarModel();
        $sample = $carModel->select('*')->limit(1)->first() ?? [];
        $carDateCol   = $this->pickExistingKey($sample, ['tanggal_pergi','created_at','tanggal_pulang']);
        $carStatusCol = $this->pickExistingKey($sample, ['status','status_booking','approval_status']);

        // Query car bookings directly with user names
        $query = $carModel
            ->select('car_bookings.*, user_profile.nama AS user_name, driver_assignments.mobil_jenis, driver_assignments.mobil_plat')
            ->join('user_profile', 'user_profile.user_id = car_bookings.user_id', 'left')
            ->join('driver_assignments', 'driver_assignments.car_booking_id = car_bookings.id', 'left');
        if ($carDateCol) {
            $query = $query
                ->where("car_bookings.$carDateCol >=", $start)
                ->where("car_bookings.$carDateCol <=", $end)
                ->orderBy("car_bookings.$carDateCol", 'DESC');
        } else {
            // Filter by car booking date if assignment lacks date
            $query = $query
                ->where('car_bookings.tanggal_pergi >=', $start)
                ->where('car_bookings.tanggal_pergi <=', $end)
                ->orderBy('car_bookings.tanggal_pergi', 'DESC');
        }
        $rows = $query->findAll();

        $headers = ['Tanggal Pergi', 'Pemesan', 'Tujuan', 'Keperluan', 'Status'];
        $data = array_map(function ($r) use ($carDateCol, $carStatusCol) {
            return [
                ($carDateCol ? ($r[$carDateCol] ?? '') : ($r['tanggal_pergi'] ?? '')),
                $r['user_name'] ?? ($r['nama'] ?? ($r['user_id'] ?? '')),
                $r['tujuan'] ?? '',
                $r['keperluan'] ?? ($r['nama_pekerjaan'] ?? ''),
                ($carStatusCol ? ($r[$carStatusCol] ?? '') : ($r['status'] ?? '')),
            ];
        }, $rows);

        $export = new ExportService();
        return $export->exportPdf('laporan_mobil.pdf', 'Laporan Booking Kendaraan', $headers, $data);
    }
}