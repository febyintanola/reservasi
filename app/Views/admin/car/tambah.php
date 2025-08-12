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
	<h1 class="text-center font-semibold text-lg mb-6">Tambah Booking Mobil (Manual)</h1>

	<form action="<?= base_url('admin/car/store') ?>" method="POST" class="space-y-6" autocomplete="off">
		<?= csrf_field() ?>
		<div>
			<label class="block text-sm mb-1 text-black" for="nama">Nama</label>
			<input name="nama" id="nama" type="text" class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-black" required />
		</div>
		<div>
			<label class="block text-sm mb-1 text-black" for="tujuan">Tujuan</label>
			<input name="tujuan" id="tujuan" type="text" class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-black" required />
		</div>
		<div>
			<label class="block text-sm mb-1 text-black" for="keperluan">Keperluan</label>
			<select name="keperluan" id="keperluan" class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-black" required>
				<option disabled selected>Pilih keperluan</option>
				<option value="Dinas">Dinas</option>
				<option value="Proyek">Proyek</option>
			</select>
		</div>
		<div id="proyek-fields" class="space-y-4 hidden">
			<div>
				<label class="block text-sm mb-1 text-black" for="nama_pekerjaan">Nama Pekerjaan</label>
				<input name="nama_pekerjaan" id="nama_pekerjaan" type="text" class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm" />
			</div>
			<div>
				<label class="block text-sm mb-1 text-black" for="project_costing">Project Costing</label>
				<input name="project_costing" id="project_costing" type="text" class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm" />
			</div>
			<div>
				<label class="block text-sm mb-1 text-black" for="task_number">Task Number</label>
				<input name="task_number" id="task_number" type="text" class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm" />
			</div>
			<div>
				<label class="block text-sm mb-1 text-black" for="expenditure_type">Expenditure Type</label>
				<input name="expenditure_type" id="expenditure_type" type="text" class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm" />
			</div>
			<div>
				<label class="block text-sm mb-1 text-black" for="expenditure_org">Expenditure Org</label>
				<input name="expenditure_org" id="expenditure_org" type="text" class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm" />
			</div>
		</div>
		<div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
			<div>
				<label class="block text-sm mb-1 text-black" for="tanggal_pergi">Tanggal Pergi</label>
				<input name="tanggal_pergi" id="tanggal_pergi" type="date" class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm" required />
			</div>
			<div>
				<label class="block text-sm mb-1 text-black" for="tanggal_pulang">Tanggal Pulang</label>
				<input name="tanggal_pulang" id="tanggal_pulang" type="date" class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm" required />
			</div>
		</div>
		<div>
			<label class="block text-sm mb-1 text-black" for="pengikut">Pengikut (pisahkan dengan koma)</label>
			<textarea name="pengikut" id="pengikut" rows="2" class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm"></textarea>
		</div>
		<div>
			<label class="block text-sm mb-1 text-black" for="status">Status</label>
			<select name="status" id="status" class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm" required>
				<option value="pending" selected>Pending</option>
				<option value="accepted">Accepted</option>
				<option value="rejected">Rejected</option>
			</select>
		</div>
		<div class="flex justify-center space-x-12 mt-8">
			<a class="text-black text-sm" href="<?= base_url('admin/car') ?>">Batal</a>
			<button class="bg-black text-white text-sm rounded-md px-6 py-2" type="submit">Submit</button>
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
