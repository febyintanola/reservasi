<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= $title ?? 'RuMA' ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="<?= base_url('css/theme.css') ?>" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <!-- Custom favicon/logo -->
  <link rel="icon" type="image/png" href="<?= base_url('uploads/logo.png') ?>" />
  <link rel="apple-touch-icon" href="<?= base_url('uploads/logo.png') ?>" />
  <link rel="shortcut icon" href="<?= base_url('uploads/logo.png') ?>" />
</head>
<body class="flex bg-white min-h-screen">
<?php
$hdrFotoUrl = 'https://via.placeholder.com/32';
$uid = session('user_id');
if ($uid) {
  try {
    $profileModel = model('App\\Models\\UserProfileModel');
    $p = $profileModel->where('user_id', $uid)->first();
    if (!empty($p['foto_url'])) {
      $hdrFotoUrl = $p['foto_url'];
    }
  } catch (\Throwable $e) {
    // ignore
  }
}
?>
  
  <!-- Sidebar -->
  <?= $this->include('user/layouts/sidebar') ?>

  <!-- Main Content Area -->
  <div class="flex-1 flex flex-col">
    <header class="flex items-center justify-between px-4 py-3 border-b border-white bg-white">
      <div class="flex-1 flex justify-center">
        <div class="flex items-center gap-2">
          <img src="<?= base_url('uploads/logo.png') ?>" alt="Logo RuMa" class="h-7 w-7 object-contain" />
          <div class="text-black text-base font-semibold">RuMa</div>
        </div>
      </div>
      <a href="<?= base_url('/user/profile') ?>" class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center ml-auto overflow-hidden">
        <img src="<?= esc($hdrFotoUrl) ?>" alt="Profil" class="w-8 h-8 object-cover" />
      </a>
    </header>

    <main class="flex-1 p-6 overflow-y-auto">
      <?= $this->renderSection('content') ?>
    </main>
  </div>

  <?php $notif = session()->getFlashdata('notif'); ?>

  <div id="toast-notif"
       class="fixed inset-0 z-50 flex items-center justify-center bg-black/30 transition-opacity duration-300 <?= $notif ? '' : 'opacity-0 pointer-events-none' ?>">
    <div class="w-full max-w-lg sm:max-w-xl border border-gray-300 rounded-lg bg-white shadow-lg p-6 relative">
      <div class="flex items-start justify-between mb-3">
        <div class="flex items-center gap-2">
          <span class="text-black text-lg">ℹ️</span>
          <span class="font-semibold text-black text-base leading-tight">
            <?= esc($notif['title'] ?? 'Berhasil') ?>
          </span>
        </div>
        <button type="button" id="toast-close" class="text-black text-lg leading-none hover:opacity-70">
          ✖
        </button>
      </div>
      <p class="text-black text-sm sm:text-base leading-relaxed">
        <?= esc($notif['message'] ?? 'Aksi berhasil diproses.') ?>
      </p>
    </div>
  </div>

  <script>
    (function () {
      const toast = document.getElementById('toast-notif');
      const closeBtn = document.getElementById('toast-close');
      const shouldShow = <?= $notif ? 'true' : 'false' ?>;

      if (shouldShow) {
  // Auto-hide setelah 4 detik
  const hide = () => toast.classList.add('opacity-0', 'pointer-events-none');
  setTimeout(hide, 4000);
  if (closeBtn) closeBtn.addEventListener('click', hide);
      }
    })();
  </script>
</body>
 <script>
    // Toggle Sidebar
    const toggleBtn = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');

    toggleBtn.addEventListener('click', () => {
  if (sidebar) sidebar.classList.toggle('-ml-64');
    });
  </script>


</html>
