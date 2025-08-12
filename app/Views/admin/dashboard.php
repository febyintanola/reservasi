<?= $this->extend('admin/layouts/header') ?>

<?= $this->section('content') ?>
  <main class="max-w-7xl mx-auto px-6">
    <h2 class="text-center font-semibold text-2xl text-slate-800 my-8">Dashboard</h2>

    <!-- Ringkasan -->
   <section class="grid sm:grid-cols-3 gap-6 mb-10">
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
          <p class="text-3xl font-bold text-slate-800"><?= esc($totalMobil) ?></p>
        </div>
      </div>
      <div class="card p-6 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
          <i class="fas fa-spinner fa-spin"></i>
        </div>
        <div>
          <p class="text-slate-500 text-sm">Reservasi Berjalan</p>
          <p class="text-3xl font-bold text-slate-800"><?= esc($totalBerjalan) ?></p>
        </div>
      </div>
    </section>


    <!-- Tabel Reservasi -->
    <section class="overflow-x-auto">
      <table class="min-w-full card overflow-hidden">
        <thead class="bg-slate-50 text-xs text-slate-600 uppercase">
  <tr>
    <th class="px-6 py-3 text-left">Nama Acara</th>
    <th class="px-6 py-3 text-left">Pemesan</th>
    <th class="px-6 py-3 text-left">Tipe</th>
    <th class="px-6 py-3 text-left">Tanggal</th>
    <th class="px-6 py-3 text-left">Status</th>
    <th class="px-6 py-3"></th> <!-- Kolom untuk tombol -->
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
        <td class="px-6 py-4">
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
