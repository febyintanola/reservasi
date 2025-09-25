<?= $this->extend('admin/layouts/header') ?>

<?= $this->section('content') ?>
  <main class="max-w-7xl mx-auto px-6">
    <h2 class="text-center font-semibold text-2xl text-slate-800 my-8">Dashboard Admin</h2>

  <!-- Ringkasan (KPI) -->
   <section class="grid sm:grid-cols-5 gap-6 mb-10">
      <div class="card p-6 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
          <i class="fas fa-list-check"></i>
        </div>
        <div>
          <p class="text-slate-500 text-sm">Menunggu Persetujuan</p>
          <p class="text-3xl font-bold text-slate-800"><?= esc($totalBerjalan ?? 0) ?></p>
        </div>
      </div>
      <div class="card p-6 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
          <i class="fas fa-calendar-day"></i>
        </div>
        <div>
          <p class="text-slate-500 text-sm">Reservasi Hari Ini</p>
          <p class="text-3xl font-bold text-slate-800"><?= esc($reservasiHariIni ?? 0) ?></p>
        </div>
      </div>
      <div class="card p-6 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
          <i class="fas fa-door-open"></i>
        </div>
        <div>
          <p class="text-slate-500 text-sm">Total Ruangan Dipesan</p>
          <p class="text-3xl font-bold text-slate-800"><?= esc($totalRuang) ?></p>
        </div>
      </div>
      <div class="card p-6 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
          <i class="fas fa-car"></i>
        </div>
        <div>
          <p class="text-slate-500 text-sm">Total Mobil Dipesan</p>
          <p class="text-3xl font-bold text-slate-800"><?= esc($totalMobil ?? 0) ?></p>
        </div>
      </div>
      <div class="card p-6 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
          <i class="fas fa-gauge-high"></i>
        </div>
        <div>
          <p class="text-slate-500 text-sm">Utilisasi Ruang (hari ini)</p>
          <p class="text-3xl font-bold text-slate-800"><?= esc($utilisasiRuangPersen ?? 0) ?>%</p>
          <p class="text-xs text-slate-500"><?= esc($ruangDipakaiHariIni ?? 0) ?>/<?= esc($totalRooms ?? 0) ?> ruang terpakai</p>
        </div>
      </div>
    </section>


    <!-- Jadwal Hari Ini: Ruang Rapat & Mobil -->
    <section class="grid md:grid-cols-2 gap-6 mb-8">
      <!-- Jadwal Ruang Rapat Hari Ini -->
      <div class="card p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-semibold text-slate-800">Ruang Rapat · Hari Ini</h3>
          <span class="text-xs text-slate-500"><?= date('Y-m-d') ?></span>
        </div>
        <?php if (!empty($todayRoomSchedule ?? [])): ?>
          <ul class="divide-y divide-slate-200">
            <?php foreach ($todayRoomSchedule as $r): ?>
              <li class="py-3 flex items-start justify-between">
                <div>
                  <div class="font-medium text-slate-800"><?= esc($r['acara'] ?? '-') ?></div>
                  <div class="text-xs text-slate-500">
                    <?= esc($r['nama_ruangan'] ?? '-') ?> · <?= esc($r['jam_mulai'] ?? '-') ?> - <?= esc($r['jam_selesai'] ?? '-') ?>
                  </div>
                  <div class="text-xs text-slate-500">
                    Pemesan: <?= esc($r['pemesan_nama'] ?? '—') ?> (<?= esc($r['pemesan_divisi'] ?? '-') ?>)
                  </div>
                </div>
                <div class="text-xs">
                  <a href="<?= base_url('admin/booking/detail/' . $r['id']) ?>" class="text-blue-600 hover:underline">Detail</a>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p class="text-sm text-slate-500">Tidak ada jadwal ruang rapat hari ini.</p>
        <?php endif; ?>
      </div>

      <!-- Jadwal Mobil Hari Ini -->
      <div class="card p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-semibold text-slate-800">Mobil · Hari Ini</h3>
          <span class="text-xs text-slate-500"><?= date('Y-m-d') ?></span>
        </div>
        <?php if (!empty($todayCarSchedule ?? [])): ?>
          <ul class="divide-y divide-slate-200">
            <?php foreach ($todayCarSchedule as $c): ?>
              <li class="py-3 flex items-start justify-between">
                <div class="flex gap-3">
                  <img src="<?= esc($c['driver_foto'] ?? base_url('public/css/default-avatar.png')) ?>" alt="Driver" class="w-10 h-10 rounded-full object-cover border border-slate-200">
                  <div>
                    <div class="font-medium text-slate-800">Tujuan: <?= esc($c['tujuan'] ?? '-') ?></div>
                    <div class="text-xs text-slate-500">Driver: <?= esc($c['driver_nama'] ?? 'Belum ditugaskan') ?></div>
                    <div class="text-xs text-slate-500">Mobil: <?= esc($c['mobil_jenis'] ?? '-') ?> <?= esc($c['mobil_plat'] ? '· ' . $c['mobil_plat'] : '') ?></div>
                    <div class="text-xs text-slate-500">Pemesan: <?= esc($c['pemesan_nama'] ?? '—') ?> (<?= esc($c['pemesan_divisi'] ?? '-') ?>)</div>
                    <div class="text-xs text-slate-500">Periode: <?= esc($c['tanggal_pergi'] ?? '-') ?> s/d <?= esc($c['tanggal_pulang'] ?? '-') ?></div>
                  </div>
                </div>
                <div class="text-xs">
                  <a href="<?= base_url('admin/car/detailMobil/' . $c['id']) ?>" class="text-blue-600 hover:underline">Detail</a>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php else: ?>
          <p class="text-sm text-slate-500">Tidak ada jadwal mobil hari ini.</p>
        <?php endif; ?>
      </div>
    </section>

    <!-- Tabel Reservasi Terbaru -->
    <section class="overflow-x-auto">
      <table class="min-w-full card overflow-hidden">
        <thead class="bg-slate-50 text-xs text-slate-600 uppercase">
  <tr>
    <th class="px-6 py-3 text-left">Nama Acara</th>
    <th class="px-6 py-3 text-left">Pemesan</th>
    <th class="px-6 py-3 text-left">Tipe</th>
    <th class="px-6 py-3 text-left">Tanggal</th>
    <th class="px-6 py-3 text-left">Status</th>
  <th class="px-6 py-3 text-right">Aksi</th>
  </tr>
</thead>
<tbody class="text-sm text-slate-700 divide-y divide-gray-200">
  <?php if (!empty($bookings)): ?>
    <?php foreach ($bookings as $booking): ?>
      <tr class="bg-white">
        <td class="px-6 py-4"><?= esc($booking['acara']) ?></td>
        <td class="px-6 py-4">
          <div class="font-medium text-slate-800"><?= esc($booking['pemesan_nama'] ?? '—') ?></div>
          <div class="text-xs text-slate-500"><?= esc($booking['pemesan_divisi'] ?? '') ?></div>
        </td>
        <td class="px-6 py-4"><?= esc($booking['tipe']) ?></td>
        <td class="px-6 py-4"><?= esc($booking['tanggal']) ?></td>
        <td class="px-6 py-4">
          <?php
            $status = strtolower($booking['status']);
            $badgeClass = match ($status) {
              'pending' => 'badge bg-yellow-50 text-yellow-700 border-yellow-200',
              'accepted' => 'badge bg-green-50 text-green-700 border-green-200',
              'rejected' => 'badge bg-red-50 text-red-600 border-red-200',
              default => 'badge',
            };
          ?>
          <span class="<?= $badgeClass ?>"><?= ucfirst($status) ?></span>
        </td>
        <td class="px-6 py-4 text-right">
          <?php if (($booking['tipe'] ?? '') === 'Reservasi Mobil'): ?>
            <a href="<?= base_url('admin/car/detailMobil/' . $booking['id']) ?>" class="btn-primary text-xs">Detail</a>
          <?php else: ?>
            <a href="<?= base_url('admin/booking/detail/' . $booking['id']) ?>" class="btn-primary text-xs">Detail</a>
          <?php endif; ?>
        </td>
      </tr>
    <?php endforeach; ?>
  <?php else: ?>
    <tr><td colspan="5" class="text-center py-4 text-gray-400">Belum ada data reservasi.</td></tr>
  <?php endif; ?>
</tbody>

      </table>
    </section>
  </main>
<?= $this->endSection() ?>