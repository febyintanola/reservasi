<?= $this->extend('admin/layouts/header') ?>
<?= $this->section('content') ?>

<main class="p-6 bg-white min-h-screen">
  <h1 class="text-2xl font-bold mb-6">Detail Reservasi Ruang Rapat</h1>

  <div class="overflow-x-auto bg-white rounded-lg shadow p-6">
    <?php if (session()->getFlashdata('message')): ?>
      <div class="mb-4 p-3 rounded bg-green-50 text-green-700 text-sm">
        <?= esc(session()->getFlashdata('message')) ?>
      </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
      <div class="mb-4 p-3 rounded bg-red-50 text-red-700 text-sm">
        <?= esc(session()->getFlashdata('error')) ?>
      </div>
    <?php endif; ?>
    <table class="min-w-full table-auto text-sm text-left text-gray-700">
      <tbody class="divide-y divide-gray-200">
        <tr>
          <th class="px-4 py-2">Nama Pemesan</th>
          <td class="px-4 py-2"><?= esc($pemesan_nama ?? '-') ?></td>
        </tr>
        <tr>
          <th class="px-4 py-2">Divisi</th>
          <td class="px-4 py-2"><?= esc($pemesan_divisi ?? '-') ?></td>
        </tr>
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
            <?php $s = strtolower(trim($status ?? '')); ?>
            <span class="px-2 py-1 text-xs rounded-full <?= $s === 'accepted'
              ? 'bg-green-200 text-green-800'
              : (($s === 'rejected' || $s === 'ditolak') ? 'bg-red-200 text-red-800' : 'bg-yellow-200 text-yellow-800') ?>">
              <?= esc($status) ?>
            </span>
          </td>
        </tr>
      </tbody>
    </table>

    <div class="mt-6 flex items-center gap-3">
      <a href="<?= base_url('admin/dashboard') ?>" class="text-blue-600 hover:underline text-sm">← Kembali</a>

      <?php if (($status ?? '') !== 'accepted'): ?>
      <form action="<?= base_url('admin/booking/approve/' . ($id ?? '')) ?>" method="post" onsubmit="return confirm('Setujui reservasi ini?');">
        <?= csrf_field() ?>
        <button type="submit" class="px-4 py-2 rounded bg-green-600 hover:bg-green-700 text-white text-sm">Setujui</button>
      </form>
      <?php endif; ?>

      <?php if (($status ?? '') !== 'rejected'): ?>
      <form action="<?= base_url('admin/booking/reject/' . ($id ?? '')) ?>" method="post" onsubmit="return confirm('Tolak reservasi ini?');">
        <?= csrf_field() ?>
        <button type="submit" class="px-4 py-2 rounded bg-red-600 hover:bg-red-700 text-white text-sm">Tolak</button>
      </form>
      <?php endif; ?>
    </div>
  </div>
</main>

<?= $this->endSection() ?>
