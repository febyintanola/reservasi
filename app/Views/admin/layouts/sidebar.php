<?php
$fotoUrl = 'https://via.placeholder.com/40';
$uid= session('user_id');
if ($uid) {
  try {
    $profileModel = model('App\\Models\\UserProfileModel');
    $p = $profileModel->where('user_id',$uid)->first();
    if (!empty($p['foto_url'])) {
      $fotoUrl = $p['foto_url'];
    }
  } catch (\Throwable $e){
    //ignore
  }
}
// Determine active route for admin
$uri = trim(uri_string() ?? '', '/');
$isHome = ($uri === 'admin' || $uri === 'admin/dashboard' || $uri === '' );
$isRuang = ($uri === 'admin/ruang' || strpos($uri, 'admin/ruang') === 0);
$isCar = ($uri === 'admin/mobil' || strpos($uri, 'admin/mobil') === 0);
$isHistory = ($uri === 'admin/history');
$isProfile = ($uri === 'admin/profile');
?>
  <!-- Sidebar -->
  <aside x-data="{ open: true, submenuOpen: false }"
    :class="open ? 'w-64' : 'w-16'"
    class="app-sidebar transition-all duration-300 flex flex-col justify-between min-h-screen relative"
    id="sidebar">

    <!-- Hamburger menu icon -->
    <button
      @click="open = !open; if(!open) submenuOpen = false"
      :class="open ? 'right-2' : 'left-1/2 transform -translate-x-1/2'"
      class="absolute top-4 transition-all duration-300 z-10 focus:outline-none"
      style="background: none; border: none;">
      <i class="fas fa-bars" :class="open ? 'text-xl text-black' : 'text-lg text-black'"></i>
    </button>

    <div class="px-3 pt-12"> <!-- pt-14 agar tidak ketumpuk tombol -->
      <h1 class="text-2xl font-bold text-primary-brand mb-6 flex items-center gap-2" x-show="open">
        <img src="<?= base_url('uploads/logo.png') ?>" alt="Logo RuMa" class="h-8 w-8 object-contain" />
        <span>RuMa</span>
      </h1>
      <nav class="space-y-1">

        <!-- Home -->
        <a href="<?= base_url('/admin') ?>" class="nav-link" data-active="<?= $isHome ? 'true' : 'false' ?>">
          <i class="fas fa-home text-[16px]"></i>
          <span x-show="open">Home</span>
        </a>

        <!-- Menu collapsible -->
        <button
        type="button"
        @click="submenuOpen = !submenuOpen"
        x-show="open"
        class="nav-link w-full justify-between">
        <span class="flex items-center gap-2 text-gray-600">
          <i class="fas fa-layer-group text-[16px]"></i>
          <span class="truncate text-sm">Menu</span>
        </span>
  <i :class="submenuOpen ? 'fas fa-chevron-up' : 'fas fa-chevron-down'" class="text-xs text-primary-brand"></i>
        </button>

        <!-- Submenu -->
        <div x-show="submenuOpen && open" class="pl-5 space-y-0.5 transition-all duration-200">
          <a href="<?= base_url('admin/ruang') ?>" class="nav-link" data-active="<?= $isRuang ? 'true' : 'false' ?>">
            <i class="fas fa-door-open text-[14px]"></i>
            <span x-show="open">List Ruang</span>
          </a>
          <a href="<?= base_url('admin/driver') ?>" class="nav-link" data-active="<?= $isCar ? 'true' : 'false' ?>">
            <i class="fas fa-car-side text-[14px]"></i>
            <span x-show="open">List Driver</span>
          </a>
        </div>

        <!-- Reports -->
        <a href="<?= base_url('/admin/reports') ?>" class="nav-link" data-active="<?= $isHistory ? 'true' : 'false' ?>">
          <i class="fas fa-history text-[20px]"></i>
          <span x-show="open">Reports</span>
        </a>

        <hr class="border-gray-300 my-4" />

        <!-- Profile -->
  <a href="<?= base_url('/admin/profile') ?>" class="nav-link" data-active="<?= $isProfile ? 'true' : 'false' ?>">
          <i class="far fa-user-circle text-[16px]"></i>
          <span x-show="open">Profile</span>
        </a>  

        <!-- Logout -->
  <a href="<?= base_url('/logout') ?>" class="nav-link hover:text-red-600">
          <i class="fas fa-sign-out-alt text-[16px] text-primary-brand"></i>
          <span x-show="open">Log out</span>
        </a>
      </nav>
    </div>

    <!-- User info -->
    <div class="flex items-center space-x-2.5 px-3 pb-5">
      <img src="<?= esc($fotoUrl) ?>"
           alt="Foto profil"
           class="w-8 h-8 rounded-full object-cover border border-gray-200" />
      <div class="hidden md:block" x-show="open">
        <p class="text-gray-900 font-semibold text-sm"><?= session('nama') ?? 'User' ?></p>
        <p class="text-gray-600 text-sm"><?= session('email') ?? 'user@example.com' ?></p>
      </div>
    </div>
  </aside>
