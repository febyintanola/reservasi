<html lang="en">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Detail Tugas</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-900">
  <main class="max-w-3xl mx-auto px-6 py-10">
    <a href="<?= site_url('driver/dashboard') ?>" class="text-sm text-blue-600 hover:underline">&larr; Kembali</a>
    <h1 class="text-2xl font-semibold mt-4 mb-6">Detail Tugas</h1>

    <?php if(session('message')): ?>
      <div class="mb-4 p-3 rounded bg-green-100 text-green-700 text-sm"><?= esc(session('message')) ?></div>
    <?php endif; ?>
    <?php if(session('error')): ?>
      <div class="mb-4 p-3 rounded bg-red-100 text-red-700 text-sm"><?= esc(session('error')) ?></div>
    <?php endif; ?>

    <?php if(!empty($booking)): ?>
      <div class="bg-white rounded shadow p-6 mb-8">
        <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div><dt class="text-xs uppercase text-gray-500">Nama Pekerjaan</dt><dd class="font-medium"><?= esc($booking['nama_pekerjaan'] ?? '-') ?></dd></div>
          <div><dt class="text-xs uppercase text-gray-500">Tujuan</dt><dd class="font-medium"><?= esc($booking['tujuan'] ?? '-') ?></dd></div>
          <div><dt class="text-xs uppercase text-gray-500">Tanggal Pergi</dt><dd class="font-medium"><?= esc(date('d-m-Y', strtotime($booking['tanggal_pergi']))) ?></dd></div>
          <div><dt class="text-xs uppercase text-gray-500">Tanggal Pulang</dt><dd class="font-medium"><?= esc(date('d-m-Y', strtotime($booking['tanggal_pulang']))) ?></dd></div>
          <div><dt class="text-xs uppercase text-gray-500">Pengikut</dt><dd class="font-medium"><?= esc($booking['pengikut'] ?? '-') ?></dd></div>
          <div><dt class="text-xs uppercase text-gray-500">Status</dt><dd class="font-medium"><?= esc(ucfirst($booking['status'] ?? '-')) ?></dd></div>
        </dl>
      </div>
    <?php endif; ?>

    <h2 class="text-lg font-semibold mb-3">Pemesan</h2>
    <div class="bg-white rounded shadow p-6 mb-8">
      <p class="font-medium"><?= esc($pemesan['nama'] ?? 'Tidak tersedia') ?></p>
      <p class="text-sm text-gray-500"><?= esc($pemesan['email'] ?? '-') ?></p>
    </div>

    <h2 class="text-lg font-semibold mb-3">Update Status</h2>
    <form method="post" action="<?= site_url('driver/jobs/'.$booking['id'].'/status') ?>" class="bg-white rounded shadow p-6 space-y-4">
      <?= csrf_field() ?>
      <div>
        <label for="status" class="block text-sm font-medium mb-1">Status</label>
        <select id="status" name="status" class="w-full border rounded px-3 py-2">
          <?php $current = strtolower($booking['status'] ?? ''); ?>
          <?php foreach(['accepted','ongoing','done','rejected'] as $s): ?>
            <option value="<?= $s ?>" <?= $current===$s?'selected':'' ?>><?= ucfirst($s) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 text-sm">Simpan</button>
      </div>
    </form>
  </main>
</body>
</html>