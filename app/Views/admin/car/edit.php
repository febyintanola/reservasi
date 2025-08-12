<?= $this->extend('admin/layouts/header') ?>
<?= $this->section('content') ?>
<main class="max-w-4xl mx-auto px-6 py-8">
	<?php if (session()->getFlashdata('success')): ?>
		<div class="mb-4 rounded-md bg-green-50 border border-green-200 text-green-800 px-4 py-2 text-sm">
			<?= esc(session()->getFlashdata('success')) ?>
		</div>
	<?php endif; ?>
	<?php if (session()->getFlashdata('error')): ?>
		<div class="mb-4 rounded-md bg-red-50 border border-red-200 text-red-800 px-4 py-2 text-sm">
			<?= nl2br(esc(session()->getFlashdata('error'))) ?>
		</div>
	<?php endif; ?>
	<h1 class="text-center font-semibold text-lg mb-6">Edit Booking Mobil</h1>

	<form action="<?= base_url('admin/car/update/' . $booking['id']) ?>" method="POST" class="space-y-6" autocomplete="off">
		<?= csrf_field() ?>
		<div>
			<label class="block text-sm mb-1 text-black" for="nama">Nama</label>
			<input name="nama" id="nama" type="text" value="<?= esc(old('nama', $booking['nama'])) ?>" class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-black" required />
		</div>
		<div>
			<label class="block text-sm mb-1 text-black" for="tujuan">Tujuan</label>
			<input name="tujuan" id="tujuan" type="text" value="<?= esc(old('tujuan', $booking['tujuan'])) ?>" class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-black" required />
		</div>
		<div>
			<label class="block text-sm mb-1 text-black" for="keperluan">Keperluan</label>
			<select name="keperluan" id="keperluan" class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-black" required>
				<option value="Dinas" <?= (old('keperluan', $booking['keperluan'])==='Dinas')?'selected':''; ?>>Dinas</option>
				<option value="Proyek" <?= (old('keperluan', $booking['keperluan'])==='Proyek')?'selected':''; ?>>Proyek</option>
			</select>
		</div>
		<?php $isProyek = old('keperluan', $booking['keperluan'])==='Proyek'; ?>
		<div id="proyek-fields" class="space-y-4 <?= $isProyek? '' : 'hidden' ?>">
			<div>
				<label class="block text-sm mb-1 text-black" for="nama_pekerjaan">Nama Pekerjaan</label>
				<input name="nama_pekerjaan" id="nama_pekerjaan" type="text" value="<?= esc(old('nama_pekerjaan', $booking['nama_pekerjaan'])) ?>" class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm" />
			</div>
			<div>
				<label class="block text-sm mb-1 text-black" for="project_costing">Project Costing</label>
				<input name="project_costing" id="project_costing" type="text" value="<?= esc(old('project_costing', $booking['project_costing'])) ?>" class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm" />
			</div>
			<div>
				<label class="block text-sm mb-1 text-black" for="task_number">Task Number</label>
				<input name="task_number" id="task_number" type="text" value="<?= esc(old('task_number', $booking['task_number'])) ?>" class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm" />
			</div>
			<div>
				<label class="block text-sm mb-1 text-black" for="expenditure_type">Expenditure Type</label>
				<input name="expenditure_type" id="expenditure_type" type="text" value="<?= esc(old('expenditure_type', $booking['expenditure_type'])) ?>" class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm" />
			</div>
			<div>
				<label class="block text-sm mb-1 text-black" for="expenditure_org">Expenditure Org</label>
				<input name="expenditure_org" id="expenditure_org" type="text" value="<?= esc(old('expenditure_org', $booking['expenditure_org'])) ?>" class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm" />
			</div>
		</div>
		<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
			<div>
				<label class="block text-sm mb-1 text-black" for="tanggal_pergi">Tanggal Pergi</label>
				<input name="tanggal_pergi" id="tanggal_pergi" type="date" value="<?= esc(old('tanggal_pergi', $booking['tanggal_pergi'])) ?>" class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm" required />
			</div>
			<div>
				<label class="block text-sm mb-1 text-black" for="tanggal_pulang">Tanggal Pulang</label>
				<input name="tanggal_pulang" id="tanggal_pulang" type="date" value="<?= esc(old('tanggal_pulang', $booking['tanggal_pulang'])) ?>" class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm" required />
			</div>
		</div>
		<div>
			<label class="block text-sm mb-1 text-black" for="pengikut">Pengikut (pisahkan dengan koma)</label>
			<textarea name="pengikut" id="pengikut" rows="2" class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm"><?= esc(old('pengikut', $booking['pengikut'])) ?></textarea>
		</div>
		<div>
			<label class="block text-sm mb-1 text-black" for="status">Status</label>
			<select name="status" id="status" class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm" required>
				<option value="pending" <?= (old('status', $booking['status'])==='pending')?'selected':''; ?>>Pending</option>
				<option value="accepted" <?= (old('status', $booking['status'])==='accepted')?'selected':''; ?>>Accepted</option>
				<option value="rejected" <?= (old('status', $booking['status'])==='rejected')?'selected':''; ?>>Rejected</option>
			</select>
		</div>
		<div class="flex justify-center space-x-12 mt-8">
			<a class="text-black text-sm" href="<?= base_url('admin/car') ?>">Batal</a>
			<button class="bg-black text-white text-sm rounded-md px-6 py-2" type="submit">Simpan</button>
		</div>
	</form>
</main>
<script>
document.getElementById('keperluan').addEventListener('change', function(){
  const f = document.getElementById('proyek-fields');
  if(this.value === 'Proyek'){ f.classList.remove('hidden'); } else { f.classList.add('hidden'); }
});
</script>
</body>
<?= $this->endSection() ?>
