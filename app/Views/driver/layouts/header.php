<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="<?= base_url('css/theme.css') ?>" rel="stylesheet"/>
        <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
        <!-- Custom favicon/logo -->
        <link rel="icon" type="image/png" href="<?= base_url('uploads/logo.png') ?>" />
        <link rel="apple-touch-icon" href="<?= base_url('uploads/logo.png') ?>" />
        <link rel="shortcut icon" href="<?= base_url('uploads/logo.png') ?>" />
    </head>
    <body class="flex bg-white min-h-screen">
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
                    $hdrFotoUrl = preg_match('#^https?://#i', $p['foto_url']) ? $p['foto_url'] : base_url($p['foto_url']);
                    $hdrHasPhoto = true;
                }
            } catch (\Throwable $e) {
                // ignore lookup failures
            }
        }

        if (! $hdrHasPhoto && ! empty($sessionUser['avatar'])) {
            $hdrFotoUrl = $sessionUser['avatar'];
            $hdrHasPhoto = true;
        }
?>
<!-- Main Content Area -->
 <div class="flex-1 flex flex-col">
    <header class="flex items-center justify-between px-4 py-3 border-b border-white bg-white">
        <div class="flex-1 flex justify-center">
            <div class="flex items-center gap-2">
                <img src="<?= base_url('uploads/logo.png') ?>" alt="Logo RuMa" class="h-7 w-7 object-contain" />
                <div class="text-black text-base font-semibold">RuMa</div>
            </div>
        </div>
        <div class="flex items-center ml-auto gap-3">
            <a href="<?= base_url('/logout') ?>" class="inline-flex items-center px-3 py-1.5 rounded-md text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                <i class="fas fa-sign-out-alt mr-2"></i>
                Logout
            </a>
            <a href="<?= site_url('driver/profile') ?>" class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center overflow-hidden">
                <?php if ($hdrHasPhoto): ?>
                    <img src="<?= esc($hdrFotoUrl) ?>" alt="Profil" class="w-8 h-8 object-cover"
                         onerror="this.onerror=null;this.src='<?= esc($defaultAvatar) ?>';" />
                <?php else: ?>
                    <i class="fas fa-user text-gray-400 text-xs"></i>
                <?php endif; ?>
            </a>
        </div>
    </header>
    <main class="flex-1 p-6 overflow-y-auto">
        <?= $this->renderSection('content') ?>
    </main>
    </div>
</body>
</html>





