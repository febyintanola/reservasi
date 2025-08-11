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
    <h1 class="text-center font-semibold text-lg mb-6">
     Edit Ruang Rapat
    </h1>
    <div class="relative bg-gray-300 rounded-md h-40 mb-8 flex items-center justify-center">
     <img id="previewFoto" alt="Preview gambar ruang rapat" class="object-cover h-full w-full rounded-md" height="160" src="<?= esc($room['ruangrapat_url'] ?: 'https://via.placeholder.com/600x160?text=Preview+Ruang') ?>" width="600"/>
     <label for="foto" aria-label="Add" class="absolute bottom-2 right-2 bg-white rounded-full p-2 text-black border border-black hover:bg-gray-100 flex items-center justify-center w-10 h-10 cursor-pointer">
      <svg aria-hidden="true" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
       <line x1="12" x2="12" y1="5" y2="19"></line>
       <line x1="5" x2="19" y1="12" y2="12"></line>
      </svg>
     </label>
    </div>
    <form action="<?= base_url('admin/ruang/update/' . $room['id']) ?>" method="POST" enctype="multipart/form-data" class="space-y-6" id="editForm">
     <?= csrf_field() ?>
      <input type="file" id="foto" name="foto" accept="image/*" class="hidden" />
      <div>
       <label class="block text-sm mb-1 text-black" for="nama">
        Nama Ruangan
       </label>
       <input name="nama" class="w-full rounded-md border border-gray-300 px-4 py-2 text-black text-sm focus:outline-none focus:ring-2 focus:ring-black" id="nama" type="text" value="<?= esc(old('nama', $room['nama_ruangan'])) ?>" required/>
      </div>
      <div>
       <label class="block text-sm mb-1 text-black" for="lokasi">
        Lokasi
       </label>
       <input name="lokasi" class="w-full rounded-md border border-gray-300 px-4 py-2 text-black text-sm focus:outline-none focus:ring-2 focus:ring-black" id="lokasi" type="text" value="<?= esc(old('lokasi', $room['lokasi'])) ?>" required/>
      </div>
      <div>
       <label class="block text-sm mb-1 text-black" for="kapasitas">
        Kapasitas
       </label>
       <input name="kapasitas" class="w-full rounded-md border border-gray-300 px-4 py-2 text-black text-sm focus:outline-none focus:ring-2 focus:ring-black" id="kapasitas" type="number" min="1" value="<?= esc(old('kapasitas', $room['kapasitas'])) ?>" required/>
      </div>
      <div>
       <label class="block text-sm mb-1 text-black" for="jenis">
        Jenis
       </label>
       <select name="jenis" class="w-48 rounded-md border border-gray-300 px-4 py-2 text-black text-sm focus:outline-none focus:ring-2 focus:ring-black" id="jenis" required>
        <option disabled>Pilih jenis</option>
        <?php if (!empty($jenisList)) : ?>
          <?php foreach ($jenisList as $j): ?>
            <option value="<?= esc($j) ?>" <?= ($j === old('jenis', $room['jenis'])) ? 'selected' : '' ?>><?= esc($j) ?></option>
          <?php endforeach; ?>
        <?php endif; ?>
       </select>
      </div>
      <div class="flex justify-center space-x-12 mt-8">
       <a class="text-black text-sm" href="<?= base_url('admin/ruang') ?>">Batal</a>
       <button class="bg-black text-white text-sm rounded-md px-6 py-2" type="submit">
        Simpan
       </button>
      </div>
     </form>
   </main>
   <script>
     const inputFoto = document.getElementById('foto');
     const preview = document.getElementById('previewFoto');
     if (inputFoto && preview) {
       inputFoto.addEventListener('change', (e) => {
         const file = e.target.files && e.target.files[0];
         if (!file) return;
         const reader = new FileReader();
         reader.onload = (ev) => { preview.src = ev.target.result; };
         reader.readAsDataURL(file);
       });
     }
   </script>
  </body>
<?= $this->endSection() ?>
