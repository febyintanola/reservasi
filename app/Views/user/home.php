
  <?= $this->extend('user/layouts/header') ?>

  <?= $this->section('content') ?>
  <!-- Greeting -->
  <div class="text-center mt-6">
    <h2 class="text-xl font-semibold text-slate-800">Hai, <?= esc(session('nama')) ?> 👋</h2>
    <p class="text-slate-600 text-sm mt-1">Selamat datang di <strong>RuMa</strong><br class="sm:hidden" />
      Sistem Reservasi Mobil & Ruang Rapat</p>
  </div>

  <!-- CTA Cards -->
  <main class="flex-grow grid grid-cols-1 sm:grid-cols-2 gap-6 max-w-3xl mx-auto px-4 mt-8">
    <a href="<?= base_url('/ruang') ?>" class="card p-6 flex items-center justify-between hover:shadow-lg transition">
      <div>
        <h3 class="text-lg font-semibold text-slate-800">Reservasi Ruangan</h3>
        <p class="text-slate-500 text-sm">Pilih ruang rapat dan atur waktu yang diinginkan.</p>
      </div>
  <span class="w-12 h-12 rounded-full icon-circle-primary inline-flex items-center justify-center">
        <i class="fas fa-door-open"></i>
      </span>
    </a>

    <a href="<?= base_url('/car/form') ?>" class="card p-6 flex items-center justify-between hover:shadow-lg transition">
      <div>
        <h3 class="text-lg font-semibold text-slate-800">Reservasi Driver</h3>
        <p class="text-slate-500 text-sm">Ajukan perjalanan dinas dengan driver perusahaan.</p>
      </div>
  <span class="w-12 h-12 rounded-full icon-circle-primary inline-flex items-center justify-center">
        <i class="fas fa-car"></i>
      </span>
    </a>
  </main>

<?= $this->endSection() ?>

