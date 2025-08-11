
  <?= $this->extend('layouts/header') ?>

  <?= $this->section('content') ?>
  <!-- 🧍 Greeting -->
  <div class="text-center mt-6">
    <h2 class="text-lg font-medium">Hai, <?= session('nama') ?> 👋</h2>
    <p class="text-gray-600 text-sm mt-1">Selamat datang di <strong>RuMa</strong><br class="sm:hidden" />
      Sistem Reservasi Mobil & Ruang Rapat</p>
  </div>

  <!-- 💼 Tombol Reservasi -->
  <main class="flex-grow flex flex-col items-center justify-center space-y-16 px-4 mt-8">
    <a href="<?= base_url('/ruang') ?>" class="bg-[#0088FF] text-white text-center rounded-lg w-48 h-20 flex items-center justify-center text-base font-semibold shadow hover:bg-[#0073DD] transition">
      Reservasi Ruangan
    </a>

    <a href="<?= base_url('/car') ?>" class="bg-[#0088FF] text-white text-center rounded-lg w-48 h-20 flex items-center justify-center text-base font-semibold shadow hover:bg-[#0073DD] transition">
      Reservasi Driver
    </a>
</main>

<?= $this->endSection() ?>

