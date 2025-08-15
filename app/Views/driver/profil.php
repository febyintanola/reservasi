<?= $this->extend('driver/layouts/header') ?>
<?= $this->section('content') ?>

<main class="flex-grow flex flex-col items-center px-4">
    <?php if(session()->getFlashdata('success')): ?>
        <div class="mt-6 w-full max-w-sm rounded-md bg-green-500 p-4 text-white">
            <?= esc(session()->getFlashdata('success'))?>
            </div>
            <?php endif; ?>
            <?php if(session()->getFlashdata('error')): ?>
    <div class="mt-6 w-full max-w-sm rounded-md bg-red-500 p-4 text-white">
        <?= nl2br(esc(session()->getFlashdata('error'))) ?>
    </div>
<?php endif; ?>

<form class="w-full max-w-sm space-y-6 mt-6" action="<?= site_url('driver/profile/update') ?>" method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="user_id" value="<?= esc($user['id']) ?>" />
    <div class="mb-2 relative w-24 h-24 mx-auto">
        <img id="previewFoto" src="<?= esc($profile['foto_url'] ?? 'https://via.placeholder.com/96') ?>"
             alt="User profile picture"
             class="w-24 h-24 rounded-full border border-gray-300">

        <!-- Hapus label pertama yang duplikat -->
        <label for="foto" class="absolute bottom-0 right-0 cursor-pointer bg-black text-white rounded-full p-1">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536M9 11l3 3L20.5 5.5a2.121 2.121 0 00-3-3L9 11z" />
            </svg>
        </label>
        <input type="file" id="foto" name="foto" class="hidden" accept="image/*">
    </div>
    <div>
        <label for="nama" class="block text-sm text-gray-700 mb-1">Nama</label>
        <input type="text" id="nama" name="nama" class="w-full border border-gray-300 rounded-md p-2" 
        value="<?= esc($profile['nama'] ?? '')?>">
    </div>
    <div>
        <label for="email" class="block text-sm text-gray-700 mb-1">Email</label>
            <input type="email" id="email" name="email" 
            class="w-full border border-gray-300 rounded-md p-2"
            value="<?= esc($user['email'] ?? '')?>">
        </div>
           <div>
      <label for="no_tlp" class="block text-sm text-gray-900 mb-1">No Telepon</label>
      <input id="no_tlp" name="no_tlp" type="text"
             class="w-full rounded-md border border-gray-300 px-3 py-2 text-black"
             value="<?= esc($profile['no_tlp'] ?? '') ?>" />
    </div>

    <div>
      <label for="divisi" class="block text-sm text-gray-900 mb-1">Divisi</label>
      <select id="divisi" name="divisi"
              class="w-full rounded-md border border-gray-300 px-3 py-2 text-black"
              required>
        <option disabled <?= empty($profile['divisi']) ? 'selected' : '' ?>>Pilih Divisi</option>
        <?php
        $divisiList = [
          'GM','Umum','Keuangan','Akuntansi','Anggaran','SIS','Humas','SDM',
          'Engginering','K3','DHR 3','Ahli','RPH 1','DHR 1','RPH 2','DHR 2',
          'Area Services','RPH 3','Driver'
        ];
        foreach ($divisiList as $divisi): ?>
          <option value="<?= esc($divisi) ?>" <?= (isset($profile['divisi']) && $profile['divisi'] === $divisi) ? 'selected' : '' ?>>
            <?= esc($divisi) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="flex justify-center items-center gap-6 mt-6">
      <a href="/home" class="text-black text-base">Batal</a>
      <button type="submit" class="bg-black text-white text-base rounded-md px-6 py-2">Simpan</button>
    </div>
  </form>
</main>

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
