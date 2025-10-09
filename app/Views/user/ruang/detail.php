<?= $this->extend('user/layouts/header') ?>
<?= $this->section('content') ?>

<main class="p-6 bg-white min-h-screen">
  <h1 class="text-2xl font-bold mb-6">Detail Reservasi Ruang Rapat</h1>

  <div class="overflow-x-auto bg-white rounded-lg shadow p-6">
    <table class="min-w-full table-auto text-sm text-left text-gray-700">
      <tbody class="divide-y divide-gray-200">
        <tr>
          <th class="px-4 py-2">Acara</th>
          <td class="px-4 py-2"><?= esc($judul) ?></td>
        </tr>
        <tr>
          <th class="px-4 py-2">Tanggal</th>
          <td class="px-4 py-2"><?= esc($tanggal) ?></td>
        </tr>
        <tr>
          <th class="px-4 py-2">Waktu Mulai</th>
          <td class="px-4 py-2"><?= esc($waktu_mulai) ?></td>
        </tr>
        <tr>
          <th class="px-4 py-2">Waktu Selesai</th>
          <td class="px-4 py-2"><?= esc($waktu_selesai) ?></td>
        </tr>
        <tr>
          <th class="px-4 py-2">Lokasi</th>
          <td class="px-4 py-2"><?= esc($lokasi) ?></td>
        </tr>
        <tr>
          <th class="px-4 py-2">Peserta</th>
          <td class="px-4 py-2"><?= esc($peserta) ?></td>
        </tr>
        <tr>
          <th class="px-4 py-2">Task</th>
          <td class="px-4 py-2"><?= esc($task) ?></td>
        </tr>
        <tr>
          <th class="px-4 py-2">Permohonan</th>
          <td class="px-4 py-2"><?= esc($permohonan) ?></td>
        </tr>
        <tr>
          <th class="px-4 py-2">Keterangan</th>
          <td class="px-4 py-2"><?= esc($keterangan) ?></td>
        </tr>
        <tr>
          <th class="px-4 py-2">Status</th>
          <td class="px-4 py-2">
            <?php
              $badgeClass = 'badge-neutral';
              if (in_array(strtolower($status), ['accepted','diterima'])) { $badgeClass = 'badge-success'; }
              elseif (in_array(strtolower($status), ['pending','menunggu','proses'])) { $badgeClass = 'badge-pending'; }
              elseif (in_array(strtolower($status), ['rejected','ditolak'])) { $badgeClass = 'badge-danger'; }
            ?>
            <span class="badge text-xs font-semibold <?= $badgeClass ?>"><?= esc($status) ?></span>
          </td>
        </tr>
      </tbody>
    </table>

  <a href="<?= base_url('history?jenis=ruang') ?>" class="inline-block mt-4 link-primary">← Kembali ke History</a>
  </div>
</main>

<?= $this->endSection() ?>
