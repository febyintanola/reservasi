<script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
<?php
$fotoUrl = 'https://via.placeholder.com/40';
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

    <div class="px-4 pt-14"> <!-- pt-14 agar tidak ketumpuk tombol -->
      <h1 class="text-2xl font-bold text-[#1e90ff] mb-6" x-show="open">RuMa</h1>
      <nav class="space-y-1">

        <!-- Home -->
        <a href="<?= base_url('/') ?>" class="nav-link" data-active="<?= uri_string() == '' ? 'true' : 'false' ?>">
          <i class="fas fa-home text-[20px]"></i>
          <span x-show="open">Home</span>
        </a>

        <!-- Menu collapsible -->
        <div class="flex items-center justify-between px-2 py-2 rounded-lg">
          <div class="flex items-center space-x-3 text-gray-600">
            <i class="fas fa-layer-group text-[20px]"></i>
            <span x-show="open" class="truncate">Menu</span>
          </div>
          <button @click="submenuOpen = !submenuOpen" x-show="open">
            <i :class="submenuOpen ? 'fas fa-chevron-up' : 'fas fa-chevron-down'" class="text-sm text-[#1e90ff]"></i>
          </button>
        </div>

        <!-- Submenu -->
        <div x-show="submenuOpen && open" class="pl-6 space-y-1 transition-all duration-200">
          <a href="<?= base_url('/ruang') ?>" class="nav-link" data-active="<?= uri_string() == 'ruang' ? 'true' : 'false' ?>">
            <i class="fas fa-door-open text-[18px]"></i>
            <span x-show="open">Reservasi Ruang</span>
          </a>
          <a href="<?= base_url('/car/form') ?>" class="nav-link" data-active="<?= uri_string() == 'car/form' ? 'true' : 'false' ?>">
            <i class="fas fa-car-side text-[18px]"></i>
            <span x-show="open">Reservasi Mobil</span>
          </a>
        </div>

        <!-- History -->
        <a href="<?= base_url('/history') ?>" class="nav-link" data-active="<?= uri_string() == 'history' ? 'true' : 'false' ?>">
          <i class="fas fa-history text-[20px]"></i>
          <span x-show="open">History</span>
        </a>

        <hr class="border-gray-300 my-4" />

        <!-- Profile -->
        <a href="<?= base_url('/user/profile') ?>" class="nav-link" data-active="<?= uri_string() == 'user/profile' ? 'true' : 'false' ?>">
          <i class="far fa-user-circle text-[20px]"></i>
          <span x-show="open">Profile</span>
        </a>

        <!-- Logout -->
  <a href="<?= base_url('/logout') ?>" class="nav-link hover:text-red-600">
          <i class="fas fa-sign-out-alt text-[20px] text-[#1e90ff]"></i>
          <span x-show="open">Log out</span>
        </a>
      </nav>
    </div>

    <!-- User info -->
    <div class="flex items-center space-x-3 px-4 pb-6">
      <img src="<?= esc($fotoUrl) ?>"
           alt="Foto profil"
           class="w-10 h-10 rounded-full object-cover border border-gray-200" />
      <div class="hidden md:block" x-show="open">
        <p class="text-gray-900 font-semibold text-base"><?= session('nama') ?? 'User' ?></p>
        <p class="text-gray-600 text-sm"><?= session('email') ?? 'user@example.com' ?></p>
      </div>
    </div>
  </aside>
