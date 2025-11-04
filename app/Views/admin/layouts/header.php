<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= $title ?? 'RuMa' ?></title>
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
<body class="flex bg-slate-50 min-h-screen">
  <?php
  $defaultAvatar = base_url('public/css/default-avatar.png');
  $sessionUser   = session('user') ?? [];
  $hdrFotoUrl    = $defaultAvatar;
  $hdrHasPhoto   = false;
  $uid = session('user_id') ?? ($sessionUser['id'] ?? null);

  if ($uid) {
    try {
      $profileModel = model('App\\Models\\UserProfileModel');
      $p = $profileModel->where('user_id', $uid)->first();
      if (! empty($p['foto_url'])) {
        $hdrFotoUrl = $p['foto_url'];
        $hdrHasPhoto = true;
      }
    } catch (\Throwable $e) {
      // ignore
    }
  }

  if (! $hdrHasPhoto && ! empty($sessionUser['avatar'])) {
    $hdrFotoUrl = $sessionUser['avatar'];
    $hdrHasPhoto = true;
  }
  ?>
  
  <!-- Sidebar -->
  <?= $this->include('admin/layouts/sidebar') ?>

  <!-- Main Content Area -->
  <div class="flex-1 flex flex-col">
    <header class="app-header flex items-center justify-between px-4 py-3 bg-white">
      <div class="flex items-center gap-2">
        <img src="<?= base_url('uploads/logo.png') ?>" alt="Logo RuMa" class="h-8 w-8 object-contain" />
        <span class="text-xl font-semibold text-slate-800">RuMa</span>
      </div>
      <a href="<?= base_url('/admin/profile') ?>" class="w-9 h-9 rounded-full bg-gray-100 flex items-center justify-center overflow-hidden ring-1 ring-slate-200">
        <?php if ($hdrHasPhoto): ?>
          <img src="<?= esc($hdrFotoUrl) ?>" alt="Profil" class="w-9 h-9 object-cover"
               onerror="this.onerror=null;this.src='<?= esc($defaultAvatar) ?>';" />
        <?php else: ?>
          <i class="fas fa-user text-gray-400 text-sm"></i>
        <?php endif; ?>
      </a>
    </header>

    <main class="flex-1 p-6 overflow-y-auto">
      <?= $this->renderSection('content') ?>
    </main>
  </div>
</body>
<script>
  const inputFoto = document.getElementById('foto');
  const previewFoto = document.getElementById('previewFoto');

  if (inputFoto) {
    inputFoto.addEventListener('change', (e) => {
      const file = e.target.files[0];
      if (!file) return;
      const reader = new FileReader();
      reader.onload = function (evt) {
        if (previewFoto) previewFoto.src = evt.target.result;
      };
      reader.readAsDataURL(file);
    });
  }
</script>


</html>
