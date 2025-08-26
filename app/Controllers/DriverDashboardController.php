<?php

namespace App\Controllers;

use App\Models\CarModel;
use App\Models\DriverAssignmentModel;
use App\Models\BookingRuangModel;
use App\Models\UserProfileModel;
use CodeIgniter\HTTP\RedirectResponse;

class DriverDashboardController extends BaseController
{
	protected CarModel $carModel;
	protected DriverAssignmentModel $assignmentModel;
	protected BookingRuangModel $roomBookingModel;
	protected UserProfileModel $profileModel;

	public function __construct()
	{
		$this->carModel = new CarModel();
		$this->assignmentModel = new DriverAssignmentModel();
		$this->roomBookingModel = new BookingRuangModel();
		$this->profileModel = new UserProfileModel();
	}

	/**
	 * Dashboard utama driver.
	 */
	public function index()
	{
		$driverUserId = session('user_id');
		if (!$driverUserId) {
			return redirect()->to('/login');
		}

		// Ambil semua assignment yang terkait driver ini.
		$allJobs = $this->getDriverJobs($driverUserId);

		// Kategorisasi: hari ini, berjalan (running), riwayat (history)
		$todayDate = date('Y-m-d');
		$jobsToday = [];
		$jobsRunning = [];
		$jobsHistory = [];

		foreach ($allJobs as $job) {
			$start = $job['tanggal_pergi'];
			$end   = $job['tanggal_pergi']; // fallback jika tidak ada tanggal_pulang
			if (!empty($job['tanggal_pulang'])) {
				$end = $job['tanggal_pulang'];
			}
			$status = strtolower($job['status'] ?? '');
			$isRunning = ($status === 'ongoing') || ($start <= $todayDate && $end >= $todayDate && in_array($status, ['accepted','approved','ongoing']));
			$isToday = ($start <= $todayDate && $end >= $todayDate);
			$isHistory = ($end < $todayDate) || in_array($status, ['done','rejected']);

			if ($isRunning) {
				$jobsRunning[] = $job;
				continue; // jangan duplikasi di today
			}
			if ($isHistory) {
				$jobsHistory[] = $job;
				continue;
			}
			if ($isToday) {
				$jobsToday[] = $job;
				continue;
			}
			// Jika bukan running / history / today (masa depan) masukkan ke today agar tetap terlihat nantinya? Atau kategori future.
			// Untuk sekarang kita treat sebagai today jika tanggal mulai == hari ini, jika masa depan abaikan.
		}

		$stats = [
			'rooms'   => $this->countBookedRooms(),
			'cars'    => $this->countAssignedCars($driverUserId),
			'running' => $this->countRunning($driverUserId),
		];

		$taskSummary = [
			'today'   => count($jobsToday),
			'running' => count($jobsRunning),
			'history' => count($jobsHistory),
		];

		return view('driver/dashboard', [
			'stats' => $stats,
			'jobsToday' => $jobsToday,
			'jobsRunning' => $jobsRunning,
			'jobsHistory' => $jobsHistory,
			'taskSummary' => $taskSummary,
			// For backward compatibility with the view that expects `$jobs`
			'jobs' => $allJobs,
		]);
	}

	/**
	 * Detail satu penugasan (booking mobil) berdasarkan driver assignment atau car booking id.
	 */
	public function show(int $id)
	{
		$driverUserId = session('user_id');
		if (!$driverUserId) {
			return redirect()->to('/login');
		}

		$assignment = $this->assignmentModel
			->where('car_booking_id', $id)
			->first();

		if (!$assignment) {
			return redirect()->to('driver/dashboard')->with('error', 'Penugasan tidak ditemukan');
		}
		// Permission: allow if assignment.driver_id matches either current users.id (legacy)
		// or matches drivers.id that is linked to this user via drivers.user_id
		$allowed = ((int)$assignment['driver_id'] === (int)$driverUserId);
		if (!$allowed) {
			try {
				$driverModel = model('App\\Models\\DriverModel');
				$currentDriver = $driverModel->where('user_id', $driverUserId)->first();
				if ($currentDriver && (int)$assignment['driver_id'] === (int)$currentDriver['id']) {
					$allowed = true;
				}
				// Fallback: cocokkan berdasarkan nama profil vs nama driver yang ditugaskan
				if (!$allowed) {
					$assignedDriver = $driverModel->find((int)$assignment['driver_id']);
					if ($assignedDriver) {
						$profile = $this->profileModel->where('user_id', $driverUserId)->first();
						$pn = strtolower(trim($profile['nama'] ?? ''));
						$dn = strtolower(trim($assignedDriver['nama'] ?? ''));
						if ($pn !== '' && $pn === $dn) {
							$allowed = true;
						}
					}
				}
			} catch (\Throwable $e) {}
		}
		if (!$allowed) return redirect()->to('/unauthorized');

		$booking = $this->carModel->find($id);
		if (!$booking) {
			return redirect()->to('driver/dashboard')->with('error', 'Booking mobil tidak ditemukan');
		}

		// Ambil data pemesan
		$pemesan = null;
		try {
			$pemesan = $this->profileModel
				->select('user_profile.nama, users.email')
				->join('users', 'users.id = user_profile.user_id', 'left')
				->where('user_profile.user_id', $booking['user_id'])
				->first();
		} catch (\Throwable $e) {
			$pemesan = null;
		}

		return view('driver/job_detail', [
			'booking'    => $booking,
			'assignment' => $assignment,
			'pemesan'    => $pemesan,
		]);
	}

	/**
	 * Update status booking mobil (driver side). Status disimpan di tabel car_bookings.status.
	 */
	public function updateStatus(int $id)
	{
		if ($this->request->getMethod() !== 'post') {
			return redirect()->to('driver/jobs/' . $id);
		}
		$driverUserId = session('user_id');
		if (!$driverUserId) {
			return redirect()->to('/login');
		}
		$assignment = $this->assignmentModel->where('car_booking_id', $id)->first();
		if (!$assignment) {
			return redirect()->to('/unauthorized');
		}
		$allowed = ((int)$assignment['driver_id'] === (int)$driverUserId);
		if (!$allowed) {
			try {
				$driverModel = model('App\\Models\\DriverModel');
				$currentDriver = $driverModel->where('user_id', $driverUserId)->first();
				if ($currentDriver && (int)$assignment['driver_id'] === (int)$currentDriver['id']) {
					$allowed = true;
				}
				if (!$allowed) {
					$assignedDriver = $driverModel->find((int)$assignment['driver_id']);
					if ($assignedDriver) {
						$profile = $this->profileModel->where('user_id', $driverUserId)->first();
						$pn = strtolower(trim($profile['nama'] ?? ''));
						$dn = strtolower(trim($assignedDriver['nama'] ?? ''));
						if ($pn !== '' && $pn === $dn) {
							$allowed = true;
						}
					}
				}
			} catch (\Throwable $e) {}
		}
		if (!$allowed) return redirect()->to('/unauthorized');

		// Normalisasi status agar "selesai/finish/approve" dll bisa diterima
		$newStatusRaw = $this->request->getPost('status');
		$newStatus    = $this->normalizeStatus($newStatusRaw);
		if ($newStatus === null) {
			return redirect()->back()->with('error', 'Status tidak valid');
		}

		// Update status booking mobil.
		$this->carModel->update($id, ['status' => $newStatus]);

		// Upload foto (opsional). Terima field name: photo atau foto
		$uploadedMsg = '';
		try {
			$file = $this->request->getFile('photo') ?: $this->request->getFile('foto');
			if ($file && $file->isValid() && !$file->hasMoved()) {
				$ext = strtolower($file->getExtension() ?: pathinfo($file->getName(), PATHINFO_EXTENSION));
				$allowedExt = ['jpg','jpeg','png','webp'];
				if (in_array($ext, $allowedExt, true)) {
					$targetDir = WRITEPATH . 'uploads' . DIRECTORY_SEPARATOR . 'driver_jobs' . DIRECTORY_SEPARATOR . $id;
					if (!is_dir($targetDir)) {
						@mkdir($targetDir, 0775, true);
					}
					$newName = uniqid('photo_', true) . '.' . $ext;
					$file->move($targetDir, $newName);
					$relativePath = 'uploads/driver_jobs/' . $id . '/' . $newName;

					// Simpan path ke DB jika ada kolomnya (contoh: proof_photo).
					// Pastikan kolom ini ditambahkan di tabel car_bookings dan di allowedFields CarModel.
					try {
						$this->carModel->update($id, ['proof_photo' => $relativePath]);
					} catch (\Throwable $e) {
						// Jika model memproteksi fields atau kolom belum ada, abaikan penyimpanan DB.
						log_message('warning', 'Gagal simpan path foto ke DB: ' . $e->getMessage());
					}
					$uploadedMsg = ' Foto terunggah.';
				} else {
					$uploadedMsg = ' Format foto tidak didukung.';
				}
			}
		} catch (\Throwable $e) {
			log_message('error', 'Upload foto gagal: ' . $e->getMessage());
		}

		// Sinkron status driver seperti sebelumnya
		try {
			$driverModel = model('App\\Models\\DriverModel');
			$driverId = null;
			$maybeDriver = $driverModel->find((int)$assignment['driver_id']);
			if ($maybeDriver) {
				$driverId = (int)$maybeDriver['id'];
			} else {
				$currentDriver = $driverModel->where('user_id', $driverUserId)->first();
				if ($currentDriver) {
					$driverId = (int)$currentDriver['id'];
				}
			}

			if ($driverId) {
				if (in_array($newStatus, ['ongoing','accepted'], true)) {
					$driverModel->update($driverId, ['status' => 'On Duty']);
				} elseif (in_array($newStatus, ['done','rejected'], true)) {
					$hasOtherOngoing = (int)$this->assignmentModel
						->select('driver_assignments.id')
						->join('car_bookings', 'car_bookings.id = driver_assignments.car_booking_id')
						->join('drivers d2', 'd2.id = driver_assignments.driver_id', 'left')
						->groupStart()
							->where('driver_assignments.driver_id', $assignment['driver_id'])
							->orWhere('d2.user_id', $driverUserId)
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
			// abaikan jika gagal update status driver
		}

		return redirect()->to('driver/jobs/' . $id)->with('message', 'Status diperbarui.' . $uploadedMsg);
	}

	// Tambahan: normalisasi berbagai label status dari UI ke nilai yang disimpan
	private function normalizeStatus(?string $status): ?string
	{
		$map = [
			'accept' => 'accepted',
			'accepted' => 'accepted',
			'approve' => 'accepted',
			'approved' => 'accepted',
			'mulai' => 'ongoing',
			'start' => 'ongoing',
			'jalan' => 'ongoing',
			'running' => 'ongoing',
			'ongoing' => 'ongoing',
			'selesai' => 'done',
			'finish' => 'done',
			'done' => 'done',
			'complete' => 'done',
			'tolak' => 'rejected',
			'batal' => 'rejected',
			'reject' => 'rejected',
			'rejected' => 'rejected',
		];
		$s = strtolower(trim((string)$status));
		return $map[$s] ?? null;
	}

	/**
	 * Ambil daftar pekerjaan (booking mobil) milik driver.
	 */
	protected function getDriverJobs(int $driverUserId): array
	{
		// Determine possible driver IDs mapped to this user
		$ids = [(int)$driverUserId]; // legacy mapping
		$currentName = null;
		try {
			$driverModel = model('App\\Models\\DriverModel');
			$currentDriver = $driverModel->where('user_id', $driverUserId)->first();
			if ($currentDriver) {
				$ids[] = (int)$currentDriver['id'];
			}
			$profile = $this->profileModel->where('user_id', $driverUserId)->first();
			$currentName = $profile['nama'] ?? null;
		} catch (\Throwable $e) {}

		$builder = $this->assignmentModel
			->select('driver_assignments.id as assignment_id, car_bookings.id as booking_id, car_bookings.tanggal_pergi, car_bookings.tanggal_pulang, car_bookings.tujuan, car_bookings.status, users.email, user_profile.nama')
			->join('car_bookings', 'car_bookings.id = driver_assignments.car_booking_id')
			->join('users', 'users.id = car_bookings.user_id', 'left')
			->join('user_profile', 'user_profile.user_id = users.id', 'left')
			->join('drivers d', 'd.id = driver_assignments.driver_id', 'left')
			->groupStart()
				->whereIn('driver_assignments.driver_id', array_unique(array_filter($ids)))
				->orWhere('d.user_id', $driverUserId)
			->groupEnd()
			->orderBy('car_bookings.tanggal_pergi', 'ASC');

		$rows = $builder->findAll();
		return $rows ?: [];
	}

	/** Hitung jumlah booking ruang rapat yang approved (hari ini dan seterusnya). */
	protected function countBookedRooms(): int
	{
		try {
			$today = date('Y-m-d');
			return (int)$this->roomBookingModel
				->whereIn('status', ['approved','pending'])
				->where('tanggal >=', $today)
				->countAllResults();
		} catch (\Throwable $e) {
			return 0;
		}
	}

	/** Hitung jumlah booking mobil yang sudah di-assign ke driver (accepted/ongoing). */
	protected function countAssignedCars(int $driverUserId): int
	{
		try {
			return (int)$this->assignmentModel
				->select('driver_assignments.id')
				->join('car_bookings', 'car_bookings.id = driver_assignments.car_booking_id')
				->join('drivers d', 'd.id = driver_assignments.driver_id', 'left')
				->groupStart()
					->where('driver_assignments.driver_id', $driverUserId)
					->orWhere('d.user_id', $driverUserId)
				->groupEnd()
				->whereIn('car_bookings.status', ['accepted','ongoing'])
				->countAllResults();
		} catch (\Throwable $e) {
			return 0;
		}
	}

	/** Hitung total reservasi berjalan (mobil dengan status ongoing + ruang sedang berjalan sekarang). */
	protected function countRunning(int $driverUserId): int
	{
		$nowDate = date('Y-m-d');
		$nowTime = date('H:i:s');
		$runningCars = 0;
		$runningRooms = 0;
		try {
			$runningCars = (int)$this->assignmentModel
				->select('driver_assignments.id')
				->join('car_bookings', 'car_bookings.id = driver_assignments.car_booking_id')
				->join('drivers d', 'd.id = driver_assignments.driver_id', 'left')
				->groupStart()
					->where('driver_assignments.driver_id', $driverUserId)
					->orWhere('d.user_id', $driverUserId)
				->groupEnd()
				->where('car_bookings.status', 'ongoing')
				->countAllResults();
		} catch (\Throwable $e) {}
		try {
			$runningRooms = (int)$this->roomBookingModel
				->groupStart()
					->where('tanggal', $nowDate)
				->groupEnd()
				->where('jam_mulai <=', $nowTime)
				->where('jam_selesai >=', $nowTime)
				->whereIn('status', ['approved','ongoing'])
				->countAllResults();
		} catch (\Throwable $e) {}
		return $runningCars + $runningRooms;
	}
}