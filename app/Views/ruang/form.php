<?= $this->extend('layouts/header') ?>

<?= $this->section('content') ?>
<main class="max-w-md mx-auto px-4 py-6">
  <h2 class="font-semibold text-base mb-4 text-center">Form Booking Rapat</h2>

  <!-- Gambar dan info ruangan -->
  <div class="mb-4">
    <img class="w-full h-36 object-cover bg-gray-200" src="<?= esc($room['ruangrapat_url'] ?? 'https://via.placeholder.com/600x150') ?>" alt="Foto Ruangan" />
    <div class="mt-1 text-xs text-gray-900"><?= esc($room['nama_ruangan']) ?></div>
    <div class="text-xs font-semibold text-gray-900"><?= esc($room['lokasi']) ?></div>
    <div class="text-xs text-gray-400">Kapasitas <?= esc($room['kapasitas']) ?> orang</div>
  </div>

  <!-- Form Booking -->
  <form action="/ruang/simpan-booking" method="POST" class="space-y-4">
    <!-- Hidden Fields -->
    <input type="hidden" name="room_id" value="<?= esc($room['id']) ?>">
    <input type="hidden" name="user_id" value="<?= esc($userid) ?>">
    <input type="hidden" name="tanggal" value="<?= esc($tanggal) ?>">
    <input type="hidden" name="jam_mulai" value="<?= esc($jamMulai) ?>">
    <input type="hidden" name="jam_selesai" value="<?= esc($jamSelesai) ?>">

    <div>
      <label class="block text-xs mb-1">Divisi/Bidang</label>
      <input name="divisi" type="text" class="w-full border border-gray-300 rounded-md px-3 py-1 text-xs focus:ring-gray-400" required />
    </div>

    <div>
      <label class="block text-xs mb-1">Nama Acara</label>
      <input name="acara" type="text" class="w-full border border-gray-300 rounded-md px-3 py-1 text-xs focus:ring-gray-400" required />
    </div>

    <div>
      <label class="block text-xs mb-1">Jumlah Peserta</label>
      <input name="peserta" type="number" min="1" class="w-full border border-gray-300 rounded-md px-3 py-1 text-xs focus:ring-gray-400" required />
    </div>

    <!-- Jenis Acara: Umum atau Overhaul -->
    <div>
      <label class="block text-xs mb-1">Jenis Acara</label>
      <select id="jenis_acara" name="jenis_acara" required class="w-full border border-gray-300 rounded-md px-3 py-1 text-xs focus:ring-gray-400">
        <option value="" disabled selected hidden>Pilih Jenis Acara</option>
        <option value="Umum">Umum</option>
        <option value="Overhaul">Overhaul</option>
      </select>
    </div>

    <!-- Field khusus Overhaul -->
    <div id="overhaul-fields" style="display: none;">
      <div>
        <label class="block text-xs mb-1">Task</label>
        <input name="Task" type="text" class="w-full border border-gray-300 rounded-md px-3 py-1 text-xs focus:ring-gray-400" />
      </div>

      <div>
        <label class="block text-xs mb-1">Project Costing</label>
        <input name="Procost" type="text" class="w-full border border-gray-300 rounded-md px-3 py-1 text-xs focus:ring-gray-400" />
      </div>

      <div>
        <label class="block text-xs mb-1">Expenditure Type</label>
        <input name="exptype" type="text" class="w-full border border-gray-300 rounded-md px-3 py-1 text-xs focus:ring-gray-400" />
      </div>
    </div>

    <!-- Checklist Kebutuhan -->
    <fieldset>
      <legend class="text-xs mb-2">Permohonan</legend>
      <div class="grid grid-cols-2 gap-2 text-xs">
        <?php
        $options = [
          'Makan', 'Snack', 'Wireless Mic', 'Standing Mic', 'Speaker tambahan', 'Infocus dan screen',
          'Desktop', 'Notebook/Laptop', 'ATK', 'Spanduk', 'Backdrop', 'Panggung', 'Mini Garden', 'Foto/Dokumentasi','Hybrid Meeting'
        ];
        foreach ($options as $label): ?>
          <label class="flex items-center gap-2">
            <input type="checkbox" name="kebutuhan[]" value="<?= esc($label) ?>" class="w-4 h-4 text-black" />
            <span><?= esc($label) ?></span>
          </label>
        <?php endforeach; ?>
      </div>
    </fieldset>

    <div>
      <label class="block text-xs mb-1">Keterangan</label>
      <textarea name="keterangan" rows="3" class="w-full border border-gray-300 rounded-md px-3 py-1 text-xs focus:ring-gray-400 resize-none"></textarea>
    </div>

    <div class="flex justify-center gap-6 mt-6 text-xs">
      <a href="/ruang" class="text-black px-4 py-1 border border-gray-300 rounded-md">Batal</a>
      <button type="submit" class="bg-black text-white px-5 py-1 rounded-md">Kirim</button>
    </div>
  </form>
</main>

<script>
  // Toggle field Overhaul
  document.getElementById('jenis_acara').addEventListener('change', function () {
    const value = this.value;
    const fields = document.getElementById('overhaul-fields');
    const inputs = fields.querySelectorAll('input');

    if (value === 'Overhaul') {
      fields.style.display = 'block';
      inputs.forEach(input => input.required = true);
    } else {
      fields.style.display = 'none';
      inputs.forEach(input => {
        input.required = false;
        input.value = '';
      });
    }
  });
</script>
<?= $this->endSection() ?>
