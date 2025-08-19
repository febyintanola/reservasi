<?= $this->extend('user/layouts/header') ?>

<?= $this->section('content') ?>
<?php
  // Helper sederhana untuk warna badge status
  $statusClass = function ($status) {
    $s = strtolower(trim($status));
    switch ($s) {
      case 'diterima':
      case 'accepted':
        return 'bg-green-100 text-green-800 border border-green-200';
      case 'pending':
      case 'menunggu':
      case 'proses':
        return 'bg-yellow-100 text-yellow-800 border border-yellow-200';
      case 'ditolak':
      case 'rejected':
        return 'bg-red-100 text-red-800 border border-red-200';
      default:
        return 'bg-gray-100 text-gray-800 border border-gray-200';
    }
  };
?>
<main class="max-w-3xl mx-auto px-4 py-8">

  <!-- Riwayat Reservasi Ruang Rapat -->
  <h2 class="text-xl font-bold mb-4">Riwayat Reservasi Ruang Rapat</h2>
  <?php if (!empty($historyRuang)): ?>
    <?php foreach ($historyRuang as $item): ?>
      <section class="border border-blue-400 rounded-lg p-4 mb-6 shadow-sm">
        <h3 class="text-gray-800 text-lg font-semibold mb-1"><?= esc($item['judul']) ?></h3>
        <p class="text-gray-600 text-sm leading-tight">
          Tanggal: <?= esc($item['tanggal']) ?><br/>
          Waktu: <?= esc($item['waktu_mulai']) ?> - <?= esc($item['waktu_selesai']) ?><br/>
          Ruangan: <?= esc($item['lokasi']) ?><br/>
          Status:
          <span class="inline-block text-xs font-semibold rounded px-2 py-0.5 <?= $statusClass($item['status']) ?>">
            <?= esc($item['status']) ?>
          </span>
        </p>
        <div class="flex justify-end mt-3">
          <a href="<?= base_url('ruang/detail/'.$item['id']) ?>" class="bg-blue-600 text-white text-xs font-semibold rounded-md px-3 py-1 shadow-md">Detail</a>
        </div>
      </section>
    <?php endforeach; ?>
  <?php else: ?>
    <p class="text-center text-gray-500">Belum ada data riwayat reservasi ruang rapat.</p>
  <?php endif; ?>

  <!-- Riwayat Reservasi Mobil -->
  <h2 class="text-xl font-bold mb-4 mt-10">Riwayat Reservasi Mobil</h2>
  <?php if (!empty($historyMobil)): ?>
    <?php foreach ($historyMobil as $item): ?>
      <section class="border border-blue-400 rounded-lg p-4 mb-6 shadow-sm">
        <h3 class="text-gray-800 text-lg font-semibold mb-1"><?= esc($item['judul']) ?></h3>
        <p class="text-gray-600 text-sm leading-tight">
          Tanggal Pergi: <?= esc($item['tanggal_pergi']) ?><br/>
          Tanggal Pulang: <?= esc($item['tanggal_pulang']) ?><br/>
          Tujuan: <?= esc($item['tujuan']) ?><br/>
          Lama: <?= esc($item['jumlah_hari']) ?> hari<br/>
          Status:
          <span class="inline-block text-xs font-semibold rounded px-2 py-0.5 <?= $statusClass($item['status']) ?>">
            <?= esc($item['status']) ?>
          </span>
        </p>
        <div class="flex justify-end mt-3">
          <a href="<?= base_url('user/car/detail/' . $item['id']) ?>" class="bg-blue-600 text-white text-xs font-semibold rounded-md px-3 py-1 shadow-md">Detail</a>
        </div>
      </section>
    <?php endforeach; ?>
  <?php else: ?>
    <p class="text-center text-gray-500">Belum ada data riwayat reservasi mobil.</p>
  <?php endif; ?>

</main>
<?= $this->endSection() ?>
