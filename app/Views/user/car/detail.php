<?= $this->extend('user/layouts/header') ?>
<?= $this->section('content') ?>

<main class="p-6 bg-white min-h-screen">
  <h1 class="text-2xl font-bold mb-6">Detail Reservasi Mobil</h1>

  <div class="overflow-x-auto bg-white rounded-lg shadow p-6">
    <table class="min-w-full table-auto text-sm text-left text-gray-700">
      <tbody class="divide-y divide-gray-200">
        <tr>
          <th class="px-4 py-2">Nama</th>
          <td class="px-4 py-2"><?= esc($booking['nama']) ?></td>
        </tr>
        <tr>
          <th class="px-4 py-2">Tanggal Pergi</th>
          <td class="px-4 py-2"><?= esc($booking['tanggal_pergi']) ?></td>
        </tr>
        <tr>
          <th class="px-4 py-2">Tanggal Pulang</th>
          <td class="px-4 py-2"><?= esc($booking['tanggal_pulang']) ?></td>
        </tr>
        <tr>
          <th class="px-4 py-2">Tujuan</th>
          <td class="px-4 py-2"><?= esc($booking['tujuan']) ?></td>
        </tr>
        <tr>
          <th class="px-4 py-2">Jumlah Hari</th>
          <td class="px-4 py-2"><?= esc($booking['jumlah_hari']) ?> hari</td>
        </tr>
        <tr>
          <th class="px-4 py-2">Keperluan</th>
          <td class="px-4 py-2"><?= esc($booking['keperluan']) ?></td>
        </tr>
        <tr>
          <th class="px-4 py-2">Status</th>
          <td class="px-4 py-2">
            <span class="px-2 py-1 text-xs rounded-full 
              <?= $booking['status'] === 'accepted' ? 'bg-green-200 text-green-800' : 'bg-yellow-200 text-yellow-800' ?>">
              <?= esc($booking['status']) ?>
            </span>
          </td>
        </tr>

        <?php if(!empty($assignment)):?>
          <tr>
            <th class="px-4 py-2">Driver</th>
            <td class="px-4 py-2">
              <?= esc($driver['nama'] ?? '-') ?>
              <?= isset($driver['no_hp']) && $driver['no_hp'] ? '(' . esc($driver['no_hp']) . ')' : '' ?>
            </td>
          </tr>
          <tr>
            <th class="px-4 py-2">Jenis Mobil</th>
            <td class="px-4 py-2"><?= esc($assignment['jenis_mobil'] ?? '-') ?></td>
          </tr>
          <tr>
            <th class="px-4 py-2">No Plat</th>
            <td class="px-4 py-2"><?= esc($assignment['no_plat'] ?? '-') ?></td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
    <a href="<?= base_url('history?jenis=mobil') ?>" class="inline-block mt-4 text-blue-600 hover:underline">← Kembali ke History</a>

  </div>
</main>

<?= $this->endSection() ?>
