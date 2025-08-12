<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= $title ?? 'SIREMO' ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="<?= base_url('css/theme.css') ?>" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="flex bg-slate-50 min-h-screen">
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
    } catch(\Throwable $e){
      //ignore
    }
  }
  ?>
  
  <!-- Sidebar -->
  <?= $this->include('admin/layouts/sidebar') ?>

  <!-- Main Content Area -->
  <div class="flex-1 flex flex-col">
    <header class="app-header flex items-center justify-between px-4 py-3 bg-white">
      <div class="flex items-center">
        <span class="text-xl font-semibold text-slate-800">RuMa</span>
      </div>
      <a href="<?= base_url('/admin/profile') ?>" class="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center overflow-hidden ring-1 ring-slate-200">
        <img src="<?= esc($hdrFotoUrl) ?>" alt="Profil" class="w-9 h-9 object-cover" />
      </a>
    </header>

    <main class="flex-1 p-6 overflow-y-auto">
      <?= $this->renderSection('content') ?>
    </main>
  </div>
</body>
 <script>
  // Header scripts placeholder
  </script>


</html>
