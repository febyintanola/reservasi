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
	<h1 class="text-center font-semibold text-lg mb-6">Tambah Driver</h1>

	<form action="<?= base_url('admin/driver/store') ?>" method="POST" class="space-y-6" autocomplete="off" enctype="multipart/form-data">
		<?= csrf_field() ?>

        <div class="mb-2 relative w-24 h-24 mx-auto">
            <!-- Foto Profil -->
            <img id="previewFoto" src="<?= esc($profile['foto_url'] ?? 'https://via.placeholder.com/96') ?>"
             alt="User profile picture"
             class="w-24 h-24 rounded-full border border-gray-300 object-cover"/>

             <!--Tombol Upload Foto-->
            <label for="foto" class="absolute bottom-0 right-0 cursor-pointer bg-black bg-opacity-50 text-white text-xs rounded px-1">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 11l3 3L20.5 5.5a2.121 2.121 0 00-3-3L9 11z"/>
                </svg>
            </label>
            <input type="file" name="foto" id="foto" class="hidden" accept="image/*"/>
        </div>
		<div>
			<label class="block text-sm mb-1 text-black" for="nama">Nama</label>
			<input name="nama" id="nama" type="text" class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-black" required />
		</div>
		<div>
			<label class="block text-sm mb-1 text-black" for="no_hp">No HP</label>
			<input name="no_hp" id="no_hp" type="text" class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm" />
		</div>
		<div>
			<label class="block text-sm mb-1 text-black" for="status">Status</label>
			<select name="status" id="status" class="w-full rounded-md border border-gray-300 px-4 py-2 text-sm">
				<option value="Available">Available</option>
				<option value="On Duty">On Duty</option>
			</select>
		</div>
		<div class="flex justify-center space-x-12 mt-8">
			<a class="text-black text-sm" href="<?= base_url('admin/driver') ?>">Batal</a>
			<button class="bg-black text-white text-sm rounded-md px-6 py-2" type="submit">Submit</button>
		</div>
	</form>
</main>
</body>
<script>
  const inputFoto = document.getElementById('foto');
  const previewFoto = document.getElementById('previewFoto');

  inputFoto.addEventListener('change', (e) => {
    const file = e.target.files[0];
    if (!file) return;
    const reader = new FileReader();
    reader.onload = function (evt) {
      previewFoto.src = evt.target.result;
    };
    reader.readAsDataURL(file);
  });
</script>
<?= $this->endSection() ?>
