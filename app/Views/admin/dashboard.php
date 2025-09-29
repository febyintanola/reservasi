<?= $this->extend('admin/layouts/header') ?>

<?= $this->section('content') ?>
  <main class="max-w-7xl mx-auto px-6">
    <h2 class="text-center font-semibold text-2xl text-slate-800 my-8">Dashboard Admin</h2>

    <!-- Debug banner: shows last poll status (visible on page) -->
    <div id="dashboard-debug-banner" class="mb-4 text-xs text-slate-500 text-center">
      Last poll: <span id="dashboard-debug-last">never</span>
    </div>

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
        <div id="today-room-list">
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
      </div>

      <!-- Jadwal Mobil Hari Ini -->
      <div class="card p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="font-semibold text-slate-800">Mobil · Hari Ini</h3>
          <span class="text-xs text-slate-500"><?= date('Y-m-d') ?></span>
        </div>
        <div id="today-car-list">
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
      </div>
    </section>

    <!-- Tabel Reservasi Terbaru -->
    <section class="overflow-x-auto">
      <div class="flex items-center justify-between mb-4">
        <div></div>
        <div>
          <a href="<?= base_url('admin/reports') ?>" class="btn-link">Lihat Semua (Laporan)</a>
        </div>
      </div>
      <table class="min-w-full card overflow-hidden">
        <thead class="bg-slate-50 text-xs text-slate-600 uppercase">
          <tr>
            <th class="px-6 py-3 text-left">Nama Acara</th>
            <th class="px-6 py-3 text-left">Pemesan</th>
            <th class="px-6 py-3 text-left">Tipe</th>
            <th class="px-6 py-3 text-left">Tanggal</th>
            <th class="px-6 py-3 text-left">Status</th>
            <th class="px-6 py-3 align-middle text-center">Aksi</th>
          </tr>
        </thead>
        <tbody id="reservasi-terbaru-body" class="text-sm text-slate-700 divide-y divide-gray-200">
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
                <td class="px-6 py-4 align-middle text-center">
                  <?php if (($booking['tipe'] ?? '') === 'Reservasi Mobil'): ?>
                    <a href="<?= base_url('admin/car/detailMobil/' . $booking['id']) ?>" class="btn-primary text-xs">Detail</a>
                  <?php else: ?>
                    <a href="<?= base_url('admin/booking/detail/' . $booking['id']) ?>" class="btn-primary text-xs">Detail</a>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="6" class="text-center py-4 text-gray-400">Belum ada data reservasi.</td></tr>
          <?php endif; ?>
        </tbody>

      </table>
    </section>
  </main>
<script>
  (function () {
    const dataUrl = '<?= base_url('admin/dashboard/data') ?>';

    function updateDashboard() {
      const debugLastEl = document.getElementById('dashboard-debug-last');
      fetch(dataUrl, { cache: 'no-store' })
        .then(res => res.ok ? res.json() : Promise.reject(new Error('HTTP ' + res.status)))
        .then(data => {
          if (debugLastEl) debugLastEl.textContent = new Date().toLocaleString();
          const kpiList = [
            ['menunggu-persetujuan', data.totalBerjalan ?? 0],
            ['reservasi-hari-ini', data.reservasiHariIni ?? 0],
            ['total-ruang', data.totalRuang ?? 0],
            ['total-mobil', data.totalMobil ?? 0],
            ['utilisasi-ruang', (data.utilisasiRuangPersen ?? 0) + '%'],
            ['ruang-dipakai-hari-ini', `${data.ruangDipakaiHariIni ?? 0}/${data.totalRooms ?? 0} ruang terpakai`]
          ];
          kpiList.forEach(([key, value]) => {
            const el = document.querySelector(`[data-kpi="${key}"]`);
            if (el) el.textContent = value;
          });

          const tbody = document.getElementById('reservasi-terbaru-body');
          if (!tbody) return;
          tbody.innerHTML = '';
          if (data.bookings && data.bookings.length) {
            data.bookings.forEach(booking => {
              const status = (booking.status || '').toLowerCase();
              let badgeClass = 'badge';
              if (status === 'pending') badgeClass = 'badge bg-yellow-50 text-yellow-700 border-yellow-200';
              else if (status === 'accepted') badgeClass = 'badge bg-green-50 text-green-700 border-green-200';
              else if (status === 'rejected') badgeClass = 'badge bg-red-50 text-red-600 border-red-200';
              tbody.insertAdjacentHTML('beforeend', `
                <tr class="bg-white">
                  <td class="px-6 py-4">${booking.acara}</td>
                  <td class="px-6 py-4">
                    <div class="font-medium text-slate-800">${booking.pemesan_nama ?? '—'}</div>
                    <div class="text-xs text-slate-500">${booking.pemesan_divisi ?? ''}</div>
                  </td>
                  <td class="px-6 py-4">${booking.tipe}</td>
                  <td class="px-6 py-4">${booking.tanggal}</td>
                  <td class="px-6 py-4"><span class="${badgeClass}">${(status.charAt(0) || '').toUpperCase() + status.slice(1)}</span></td>
                  <td class="px-6 py-4 align-middle text-center">
                    <a href="${booking.tipe === 'Reservasi Mobil' ? '<?= base_url('admin/car/detailMobil/') ?>' + booking.id : '<?= base_url('admin/booking/detail/') ?>' + booking.id}" class="btn-primary text-xs">Detail</a>
                  </td>
                </tr>
              `);
            });
          } else {
            tbody.innerHTML = '<tr><td colspan="6" class="text-center py-4 text-gray-400">Belum ada data reservasi.</td></tr>';
          }

          // Update today's room schedule (preserve server fallback if JS disabled)
          const roomContainer = document.getElementById('today-room-list');
          if (roomContainer) {
            if (data.todayRoomSchedule && data.todayRoomSchedule.length) {
              let html = '<ul class="divide-y divide-slate-200">';
              data.todayRoomSchedule.forEach(r => {
                html += `
                  <li class="py-3 flex items-start justify-between">
                    <div>
                      <div class="font-medium text-slate-800">${r.acara ?? '-'}</div>
                      <div class="text-xs text-slate-500">${r.nama_ruangan ?? '-'} · ${r.jam_mulai ?? '-'} - ${r.jam_selesai ?? '-'}</div>
                      <div class="text-xs text-slate-500">Pemesan: ${r.pemesan_nama ?? '—'} (${r.pemesan_divisi ?? '-'})</div>
                    </div>
                    <div class="text-xs"><a href="${'<?= base_url('admin/booking/detail/') ?>' + (r.id ?? '')}" class="text-blue-600 hover:underline">Detail</a></div>
                  </li>`;
              });
              html += '</ul>';
              roomContainer.innerHTML = html;
            } else {
              roomContainer.innerHTML = '<p class="text-sm text-slate-500">Tidak ada jadwal ruang rapat hari ini.</p>';
            }
          }

          // Update today's car schedule
          const carContainer = document.getElementById('today-car-list');
          if (carContainer) {
            if (data.todayCarSchedule && data.todayCarSchedule.length) {
              let html = '<ul class="divide-y divide-slate-200">';
              data.todayCarSchedule.forEach(c => {
                const foto = c.driver_foto || '<?= base_url('public/css/default-avatar.png') ?>';
                html += `
                  <li class="py-3 flex items-start justify-between">
                    <div class="flex gap-3">
                      <img src="${foto}" alt="Driver" class="w-10 h-10 rounded-full object-cover border border-slate-200" />
                      <div>
                        <div class="font-medium text-slate-800">Tujuan: ${c.tujuan ?? '-'}</div>
                        <div class="text-xs text-slate-500">Driver: ${c.driver_nama ?? 'Belum ditugaskan'}</div>
                        <div class="text-xs text-slate-500">Mobil: ${c.mobil_jenis ?? '-'} ${c.mobil_plat ? '· ' + c.mobil_plat : ''}</div>
                        <div class="text-xs text-slate-500">Pemesan: ${c.pemesan_nama ?? '—'} (${c.pemesan_divisi ?? '-'})</div>
                        <div class="text-xs text-slate-500">Periode: ${c.tanggal_pergi ?? '-'} s/d ${c.tanggal_pulang ?? '-'}</div>
                      </div>
                    </div>
                    <div class="text-xs"><a href="${'<?= base_url('admin/car/detailMobil/') ?>' + (c.id ?? '')}" class="text-blue-600 hover:underline">Detail</a></div>
                  </li>`;
              });
              html += '</ul>';
              carContainer.innerHTML = html;
            } else {
              carContainer.innerHTML = '<p class="text-sm text-slate-500">Tidak ada jadwal mobil hari ini.</p>';
            }
          }
        })
        .catch(err => {
          if (debugLastEl) debugLastEl.textContent = 'error: ' + (err.message || 'request failed');
          // silently ignore fetch errors to avoid breaking the page
        });
    }

  // run immediately and then every 60 seconds (1 minute)
  updateDashboard();
  setInterval(updateDashboard, 60000);
  })();
</script>
<?= $this->endSection() ?>