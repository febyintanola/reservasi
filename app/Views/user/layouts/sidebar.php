<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
<?php
$fotoUrl = base_url('public/css/default-avatar.png');
$uid = session('user_id');
if ($uid) {
  try {
    $profileModel = model('App\\Models\\UserProfileModel');
    $p = $profileModel->where('user_id', $uid)->first();
    if (!empty($p['foto_url'])) {
      $fotoUrl = $p['foto_url'];
    }
  } catch (\Throwable $e) {
    // ignore
  }
}
?>

  <!-- Sidebar -->
  <aside x-data="{ open: true, submenuOpen: false }"
    :class="open ? 'w-56' : 'w-14'"
    class="app-sidebar transition-all duration-300 flex flex-col justify-between min-h-screen relative"
    id="sidebar">

    <!-- Hamburger menu icon -->
    <button
      @click="open = !open; if(!open) submenuOpen = false"
      :class="open ? 'right-2' : 'left-1/2 transform -translate-x-1/2'"
      class="absolute top-4 transition-all duration-300 z-10 focus:outline-none"
      style="background: none; border: none;">
      <i class="fas fa-bars" :class="open ? 'text-lg text-black' : 'text-base text-black'"></i>
    </button>

    <div class="px-3 pt-12"> <!-- pt-14 agar tidak ketumpuk tombol -->
      <h1 class="text-2xl font-bold text-primary-brand mb-6 flex items-center gap-2" x-show="open">
        <img src="<?= base_url('uploads/logo.png') ?>" alt="Logo RuMa" class="h-8 w-8 object-contain" />
        <span>RuMa</span>
      </h1>
      <nav class="space-y-1">

        <!-- Home -->
        <a href="<?= base_url('/') ?>" class="nav-link" data-active="<?= in_array(uri_string(), ['', 'home', 'dashboard']) ? 'true' : 'false' ?>">
          <i class="fas fa-home text-[16px]"></i>
          <span x-show="open" class="text-sm">Home</span>
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
          <a href="<?= base_url('/ruang') ?>" class="nav-link" data-active="<?= uri_string() == 'ruang' ? 'true' : 'false' ?>">
            <i class="fas fa-door-open text-[14px]"></i>
            <span x-show="open" class="text-sm">Reservasi Ruang</span>
          </a>
          <a href="<?= base_url('/car/form') ?>" class="nav-link" data-active="<?= uri_string() == 'car/form' ? 'true' : 'false' ?>">
            <i class="fas fa-car-side text-[14px]"></i>
            <span x-show="open" class="text-sm">Reservasi Mobil</span>
          </a>
        </div>

        <!-- History -->
        <a href="<?= base_url('/history') ?>" class="nav-link" data-active="<?= uri_string() == 'history' ? 'true' : 'false' ?>">
          <i class="fas fa-history text-[16px]"></i>
          <span x-show="open" class="text-sm">History</span>
        </a>

        <hr class="border-gray-300 my-3" />

        <!-- Profile -->
        <a href="<?= base_url('/user/profile') ?>" class="nav-link" data-active="<?= uri_string() == 'user/profile' ? 'true' : 'false' ?>">
          <i class="far fa-user-circle text-[16px]"></i>
          <span x-show="open" class="text-sm">Profile</span>
        </a>

        <!-- Logout -->
        <a href="<?= base_url('/logout') ?>" class="nav-link hover:text-red-600">
          <i class="fas fa-sign-out-alt text-[16px] text-primary-brand"></i>
          <span x-show="open" class="text-sm">Log out</span>
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
        <p class="text-gray-600 text-xs"><?= session('email') ?? 'user@example.com' ?></p>
      </div>
    </div>
  </aside>
