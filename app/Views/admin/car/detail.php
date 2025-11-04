<?= $this->extend('admin/layouts/header') ?>
<?= $this->section('content') ?>

<main class="p-6 bg-white min-h-screen">
	<h1 class="text-2xl font-bold mb-6">Detail Booking Mobil</h1>

	<div class="overflow-x-auto bg-white rounded-lg shadow p-6">
		<?php if (session()->getFlashdata('message')): ?>
			<div class="mb-4 p-3 rounded bg-green-50 text-green-700 text-sm">
				<?= esc(session()->getFlashdata('message')) ?>
			</div>
		<?php endif; ?>
		<?php if (session()->getFlashdata('error')): ?>
			<div class="mb-4 p-3 rounded bg-red-50 text-red-700 text-sm">
				<?= esc(session()->getFlashdata('error')) ?>
			</div>
		<?php endif; ?>
		<table class="min-w-full table-auto text-sm text-left text-gray-700">
			<tbody class="divide-y divide-gray-200">
				<tr>
					<th class="px-4 py-2">Nama</th>
					<td class="px-4 py-2"><?= esc($booking['nama'] ?? '-') ?></td>
				</tr>
				<tr>
					<th class="px-4 py-2">Pengikut</th>
					<td class="px-4 py-2"><?= esc($booking['pengikut'] ?? '-') ?></td>
				</tr>
				<tr>
					<th class="px-4 py-2">Tujuan</th>
					<td class="px-4 py-2"><?= esc($booking['tujuan'] ?? '-') ?></td>
				</tr>
				<tr>
					<th class="px-4 py-2">Tanggal Pergi</th>
					<td class="px-4 py-2"><?= esc($booking['tanggal_pergi'] ?? '-') ?></td>
				</tr>
				<tr>
					<th class="px-4 py-2">Tanggal Pulang</th>
					<td class="px-4 py-2"><?= esc($booking['tanggal_pulang'] ?? '-') ?></td>
				</tr>
				<tr>
					<th class="px-4 py-2">Jumlah Hari</th>
					<td class="px-4 py-2"><?= esc($booking['jumlah_hari'] ?? '-') ?> hari</td>
				</tr>
				<tr>
					<th class="px-4 py-2">Keperluan</th>
					<td class="px-4 py-2"><?= esc($booking['keperluan'] ?? '-') ?></td>
				</tr>
				<?php if (($booking['keperluan'] ?? '') === 'Proyek'): ?>
				<tr>
					<th class="px-4 py-2">Nama Pekerjaan</th>
					<td class="px-4 py-2"><?= esc($booking['nama_pekerjaan'] ?? '-') ?></td>
				</tr>
				<tr>
					<th class="px-4 py-2">Project Costing</th>
					<td class="px-4 py-2"><?= esc($booking['project_costing'] ?? '-') ?></td>
				</tr>
				<tr>
					<th class="px-4 py-2">Task Number</th>
					<td class="px-4 py-2"><?= esc($booking['task_number'] ?? '-') ?></td>
				</tr>
				<tr>
					<th class="px-4 py-2">Expenditure Type</th>
					<td class="px-4 py-2"><?= esc($booking['expenditure_type'] ?? '-') ?></td>
				</tr>
				<tr>
					<th class="px-4 py-2">Expenditure Org</th>
					<td class="px-4 py-2"><?= esc($booking['expenditure_org'] ?? '-') ?></td>
				</tr>
				<?php endif; ?>
				<tr>
					<th class="px-4 py-2">Status</th>
					<td class="px-4 py-2">
						<?php $status = $booking['status'] ?? 'pending'; ?>
						<span class="px-2 py-1 text-xs rounded-full <?= $status === 'accepted' ? 'bg-green-200 text-green-800' : ($status === 'rejected' ? 'bg-red-200 text-red-800' : 'bg-yellow-200 text-yellow-800') ?>">
							<?= esc($status) ?>
						</span>
					</td>
				</tr>
				<?php if (!empty($assignment)): ?>
				<tr>
					<th class="px-4 py-2">Driver</th>
					<td class="px-4 py-2"><?= esc($driver['nama'] ?? '-') ?><?= isset($driver['no_hp']) ? ' ('.esc($driver['no_hp']).')' : '' ?></td>
				</tr>
				<tr>
					<th class="px-4 py-2">Jenis Mobil</th>
					<td class="px-4 py-2"><?= esc($assignment['mobil_jenis'] ?? '-') ?></td>
				</tr>
				<tr>
					<th class="px-4 py-2">No Plat</th>
					<td class="px-4 py-2"><?= esc($assignment['mobil_plat'] ?? '-') ?></td>
				</tr>
				<?php endif; ?>
			</tbody>
		</table>

		<div class="mt-6 flex items-center gap-3">
			<a href="<?= base_url('/admin') ?>" class="text-blue-600 hover:underline text-sm">← Kembali</a>

			<?php if (($booking['status'] ?? '') !== 'accepted'): ?>
			<form action="<?= base_url('admin/car/approve/' . ($booking['id'] ?? '')) ?>" method="post" onsubmit="return confirm('Setujui booking mobil ini?');">
				<?= csrf_field() ?>
				<button type="submit" class="px-4 py-2 rounded bg-green-600 hover:bg-green-700 text-white text-sm">Setujui</button>
			</form>
			<?php endif; ?>

			<?php if (($booking['status'] ?? '') !== 'rejected'): ?>
			<form action="<?= base_url('admin/car/reject/' . ($booking['id'] ?? '')) ?>" method="post" onsubmit="return confirm('Tolak booking mobil ini?');">
				<?= csrf_field() ?>
				<button type="submit" class="px-4 py-2 rounded bg-red-600 hover:bg-red-700 text-white text-sm">Tolak</button>
			</form>
			<?php endif; ?>

			<!-- Tombol Assign Driver & Mobil: hanya tampil jika status accepted & belum ada assignment -->
			<?php if (($booking['status'] ?? '') === 'accepted' && empty($assignment)): ?>
				<a href="<?= base_url('admin/car/assign/' . ($booking['id'] ?? '')) ?>" class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white text-sm">Assign Driver & Mobil</a>
			<?php endif; ?>

			<?php if (!empty($assignment)): ?>
				<?php // Show 'Selesai' button when assignment exists and is not finished ?>
				<?php if (empty($assignment['completed_at'])): ?>
					<form action="<?= base_url('admin/car/assignment/finish/' . ($assignment['id'] ?? '')) ?>" method="post" onsubmit="return confirm('Tandai tugas driver ini selesai?');">
						<?= csrf_field() ?>
						<button type="submit" class="px-4 py-2 rounded bg-yellow-600 hover:bg-yellow-700 text-white text-sm">Selesai</button>
					</form>
				<?php else: ?>
					<span class="px-2 py-1 text-xs rounded-full bg-gray-200 text-gray-700">Selesai: <?= esc($assignment['completed_at']) ?></span>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</div>
</main>

<?= $this->endSection() ?>
