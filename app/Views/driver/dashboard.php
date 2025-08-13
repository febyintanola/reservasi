<?php
// Asumsi variabel $totalBookings, $assignedBookings, dan $bookings dikirim dari controller
?>
<div class="container mt-4">
	<h2>Dashboard Driver</h2>
	<div class="row mb-4">
		<div class="col-md-6">
			<div class="card text-white bg-primary mb-3">
				<div class="card-header">Total Booking</div>
				<div class="card-body">
					<h5 class="card-title"><?= isset($totalBookings) ? $totalBookings : 0 ?></h5>
				</div>
			</div>
		</div>
		<div class="col-md-6">
			<div class="card text-white bg-success mb-3">
				<div class="card-header">Tugas Anda (Booking yang harus di-handle)</div>
				<div class="card-body">
					<h5 class="card-title"><?= isset($assignedBookings) ? $assignedBookings : 0 ?></h5>
				</div>
			</div>
		</div>
	</div>
	<div class="card">
		<div class="card-header">Daftar Booking</div>
		<div class="card-body">
			<table class="table table-bordered">
				<thead>
					<tr>
						<th>No</th>
						<th>Nama Pemesan</th>
						<th>Tanggal</th>
						<th>Tujuan</th>
						<th>Status</th>
					</tr>
				</thead>
				<tbody>
					<?php if (!empty($bookings)): ?>
						<?php foreach ($bookings as $i => $booking): ?>
							<tr>
								<td><?= $i+1 ?></td>
								<td><?= esc($booking['nama_pemesan']) ?></td>
								<td><?= esc($booking['tanggal']) ?></td>
								<td><?= esc($booking['tujuan']) ?></td>
								<td><?= esc($booking['status']) ?></td>
							</tr>
						<?php endforeach; ?>
					<?php else: ?>
						<tr><td colspan="5" class="text-center">Tidak ada data booking.</td></tr>
					<?php endif; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
