<?= $this->extend('layouts/header') ?>

<?= $this->section('content') ?>
<body class="bg-white font-sans antialiased">
  <main class="max-w-7xl mx-auto px-6">
    <h2 class="text-center font-semibold text-xl text-black my-8">Dashboard</h2>

    <!-- Ringkasan -->
   <section class="grid sm:grid-cols-3 gap-8 mb-10 text-center">
    <div class="flex flex-col items-center">
        <div class="w-20 h-20 rounded-full bg-blue-300 mb-2 flex items-center justify-center">
        <i class="fas fa-door-open text-white text-4xl"></i>
        </div>
        <p class="text-gray-500 text-sm">Total Ruangan Dipesan</p>
        <p class="text-3xl font-bold text-black"><?= esc($totalRuang) ?></p>
    </div>
    <div class="flex flex-col items-center">
        <div class="w-20 h-20 rounded-full bg-blue-300 mb-2 flex items-center justify-center">
        <i class="fas fa-car text-white text-4xl"></i>
        </div>
        <p class="text-gray-500 text-sm">Total Mobil Dipesan</p>
        <p class="text-3xl font-bold text-black"><?= esc($totalMobil) ?></p>
    </div>
    <div class="flex flex-col items-center">
        <div class="w-20 h-20 rounded-full bg-blue-300 mb-2 flex items-center justify-center">
        <i class="fas fa-spinner fa-spin text-white text-4xl"></i>
        </div>
        <p class="text-gray-500 text-sm">Reservasi Berjalan</p>
        <p class="text-3xl font-bold text-black"><?= esc($totalBerjalan) ?></p>
    </div>
    </section>


    <!-- Tabel Reservasi -->
    <section class="overflow-x-auto">
      <table class="min-w-full bg-white shadow-md rounded-lg">
        <thead class="bg-gray-100 text-xs text-gray-600 uppercase">
  <tr>
    <th class="px-6 py-3 text-left">Nama Acara</th>
    <th class="px-6 py-3 text-left">Tipe</th>
    <th class="px-6 py-3 text-left">Tanggal</th>
    <th class="px-6 py-3 text-left">Status</th>
    <th class="px-6 py-3"></th> <!-- Kolom untuk tombol -->
  </tr>
</thead>
<tbody class="text-sm text-gray-700 divide-y divide-gray-200">
  <?php if (!empty($bookings)): ?>
    <?php foreach ($bookings as $booking): ?>
      <tr class="bg-white">
        <td class="px-6 py-4"><?= esc($booking['acara']) ?></td>
        <td class="px-6 py-4"><?= esc($booking['tipe']) ?></td>
        <td class="px-6 py-4"><?= esc($booking['tanggal']) ?></td>
        <td class="px-6 py-4">
          <?php
            $status = strtolower($booking['status']);
            $badgeClass = match ($status) {
              'pending' => 'bg-yellow-200 text-yellow-700',
              'accepted' => 'bg-green-200 text-green-700',
              'rejected' => 'bg-red-200 text-red-600',
              default => 'bg-gray-200 text-gray-700',
            };
          ?>
          <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold <?= $badgeClass ?>">
            <?= ucfirst($status) ?>
          </span>
        </td>
        <td class="px-6 py-4">
          <a href="<?= base_url('admin/booking/detail/' . $booking['id']) ?>" 
             class="text-blue-600 border border-blue-600 rounded-full px-3 py-1 hover:bg-blue-50 text-xs">
            Detail
          </a>
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
</body>
<?= $this->endSection() ?>
