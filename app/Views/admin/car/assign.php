<?= $this->extend('admin/layouts/header') ?>
<?= $this->section('content') ?>

<main class="p-6 bg-white min-h-screen">
    <h1 class="text-2xl font-bold mb-6">Assign Driver & Mobil</h1>

    <form action="<?= base_url('admin/car/assign_save/' . ($booking['id'] ?? '')) ?>" method="post" class="bg-white rounded-lg shadow p-6 max-w-lg mx-auto">
        <?= csrf_field() ?>
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Driver</label>
            <select name="driver_id" class="w-full border rounded px-3 py-2" required>
                <option value="">-- Pilih Driver --</option>
                <?php foreach ($drivers as $driver): ?>
                    <option value="<?= $driver['id'] ?>" <?= isset($booking['driver_id']) && $booking['driver_id'] == $driver['id'] ? 'selected' : '' ?>>
                        <?= esc($driver['nama']) ?> (<?= esc($driver['no_hp'] ?? '-') ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-4">
            <label class="block mb-1 font-semibold">Jenis Mobil</label>
            <input type="text" name="mobil_jenis" value="<?= esc($assignment['mobil_jenis'] ?? '') ?>" class="w-full border rounded px-3 py-2" placeholder="Contoh: Avanza / Xpander" required />
        </div>
        <div class="mb-4">
            <label class="block mb-1 font-semibold">No Plat Mobil</label>
            <input type="text" name="mobil_plat" value="<?= esc($assignment['mobil_plat'] ?? '') ?>" class="w-full border rounded px-3 py-2" placeholder="Contoh: B 1234 CD" required />
        </div>
        <div class="flex gap-3 mt-6">
            <a href="<?= base_url('/admin/car/detailMobil/' . ($booking['id'] ?? '')) ?>" class="px-4 py-2 rounded bg-gray-300 hover:bg-gray-400 text-gray-800">Batal</a>
            <button type="submit" class="px-4 py-2 rounded bg-blue-600 hover:bg-blue-700 text-white">Simpan Assign</button>
        </div>
    </form>
</main>

<?= $this->endSection() ?>
