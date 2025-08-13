<?= $this->extend('driver/layouts/header')?>
<?= $this->section('content') ?>

  <main class="max-w-6xl mx-auto px-6 py-12">
    <h1 class="text-center font-semibold text-lg mb-10">Daftar Pekerjaan</h1>

    <?php
      $summary = $taskSummary ?? ['today'=>0, 'running'=>0, 'history'=>0];
    ?>

    <section class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-12">
		<div class="flex items-center gap-4 p-5 bg-gradient-to-tr from-blue-50 to-blue-100 rounded-xl shadow-sm">
			<div>
				<p class="text-gray-500 text-sm mb-1">Tugas Hari Ini</p>
				<p class="text-4xl font-extrabold leading-none text-gray-900"><?= esc($summary['today'])?></p>
			</div>
		</div>
		<div class="flex items-center gap-4 p-5 bg-gradient-to-tr from-emerald-50 to-emerald-100 rounded-xl shadow-sm">
			<div>
				<p class="text-gray-500 text-sm mb-1">Sedang Berjalan</p>
				<p class="text-4xl font-extrabold leading-none text-gray-900"><?=esc($summary['running'])?></p>
			</div>
		</div>
		<div class="flex items-center gap-4 p-5 bg-gradient-to-tr from-gray-50 to-gray-100 rounded-xl shadow-sm">
			<div>
				<p class="text-gray-500 text-sm mb-1">Riwayat</p>
				<p class="text-4xl font-extrabold leading-none text-gray-900"><?= esc($summary['history'])?></p>
			</div>
		</div>
	</section>

    <section>
      <table class="w-full text-left rounded-lg overflow-hidden shadow-sm">
        <thead class="bg-gray-50">
          <tr>
            <th class="py-4 px-6 font-semibold text-gray-900">Tanggal</th>
            <th class="py-4 px-6 font-semibold text-gray-900">Tujuan</th>
            <th class="py-4 px-6 font-semibold text-gray-900">Pemesan</th>
            <th class="py-4 px-6 font-semibold text-gray-900">Status</th>
            <th class="py-4 px-6 font-semibold text-gray-900"></th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
        <?php if (empty($jobs)): ?>
          <tr><td colspan="5" class="py-6 text-center text-gray-500">Belum ada tugas.</td></tr>
        <?php else: foreach($jobs as $job): ?>
          <?php
            $status = strtolower($job['status'] ?? '');
            $badgeMap = [
              'pending'  => 'bg-yellow-100 text-yellow-700',
              'approved' => 'bg-green-100 text-green-700',
              'accepted' => 'bg-green-100 text-green-700',
              'ongoing'  => 'bg-blue-100 text-blue-700',
              'rejected' => 'bg-red-100 text-red-700',
              'done'     => 'bg-gray-200 text-gray-700',
            ];
            $cls = $badgeMap[$status] ?? 'bg-gray-100 text-gray-700';
          ?>
          <tr>
            <td class="py-4 px-6 text-gray-500"><?= esc(date('d-m-Y', strtotime($job['tanggal_pergi']))) ?></td>
            <td class="py-4 px-6 text-gray-500"><?= esc($job['tujuan'] ?? '-') ?></td>
            <td class="py-4 px-6">
              <p class="font-semibold text-gray-900 leading-tight"><?= esc($job['nama'] ?? $job['username'] ?? 'User') ?></p>
              <p class="text-gray-500 text-sm"><?= esc($job['email'] ?? '-') ?></p>
            </td>
            <td class="py-4 px-6">
              <span class="inline-block <?= $cls ?> text-xs font-semibold px-3 py-1 rounded-full"><?= ucfirst($status) ?></span>
            </td>
            <td class="py-4 px-6">
              <a href="<?= site_url('driver/jobs/' . $job['booking_id']) ?>" class="text-blue-600 border border-blue-600 rounded-full px-4 py-1 text-sm font-medium hover:bg-blue-50 transition">Detail</a>
            </td>
          </tr>
        <?php endforeach; endif; ?>
        </tbody>
      </table>
    </section>
  </main>
<?= $this->endSection() ?>