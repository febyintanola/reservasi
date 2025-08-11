<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title><?= $title ?? 'RuMa App' ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Quicksand&display=swap');
    body {
      font-family: 'Quicksand', sans-serif;
    }
  </style>
</head>
<body class="bg-white min-h-screen flex" x-data="{ open: true, submenuOpen: false }">

  <!-- Sidebar -->
  <aside x-data="{ open: true, submenuOpen: false }"
    :class="open ? 'w-64' : 'w-16'"
    class="transition-all duration-300 bg-white border-r border-white flex flex-col justify-between min-h-screen relative"
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
        <a href="<?= base_url('/') ?>" class="flex items-center space-x-3 px-2 py-2 rounded-lg
          <?= uri_string() == '' ? 'bg-[#eaf5ff] border-l-4 border-[#1e90ff] text-[#1e90ff]' : 'text-gray-600 hover:text-[#1e90ff]' ?>">
          <i class="fas fa-home text-[20px]"></i>
          <span x-show="open">Home</span>
        </a>

        <!-- Menu collapsible -->
        <div class="flex items-center justify-between px-2 py-2 rounded-lg <?= in_array(uri_string(), ['ruang', 'car/form']) ? 'bg-[#eaf5ff] border-l-4 border-[#1e90ff]' : '' ?>">
          <div class="flex items-center space-x-3 text-gray-600">
            <i class="fas fa-layer-group text-[20px] <?= in_array(uri_string(), ['ruang', 'car/form']) ? 'text-[#1e90ff]' : '' ?>"></i>
            <span x-show="open" class="truncate <?= in_array(uri_string(), ['ruang', 'car/form']) ? 'text-[#1e90ff]' : '' ?>">Menu</span>
          </div>
          <button @click="submenuOpen = !submenuOpen" x-show="open">
            <i :class="submenuOpen ? 'fas fa-chevron-up' : 'fas fa-chevron-down'" class="text-sm text-[#1e90ff]"></i>
          </button>
        </div>

        <!-- Submenu -->
        <div x-show="submenuOpen && open" class="pl-6 space-y-1 transition-all duration-200">
          <a href="<?= base_url('admin/ruang') ?>" class="flex items-center space-x-2 text-lg px-2 py-1 rounded-lg
            <?= uri_string() == 'ruang' ? 'text-[#1e90ff] font-medium' : 'text-gray-600 hover:text-[#1e90ff]' ?>">
            <i class="fas fa-door-open text-[18px]"></i>
            <span x-show="open">Reservasi Ruang</span>
          </a>
          <a href="<?= base_url('/car/form') ?>" class="flex items-center space-x-2 text-lg px-2 py-1 rounded-lg
            <?= uri_string() == 'car/form' ? 'text-[#1e90ff] font-medium' : 'text-gray-600 hover:text-[#1e90ff]' ?>">
            <i class="fas fa-car-side text-[18px]"></i>
            <span x-show="open">Reservasi Mobil</span>
          </a>
        </div>

        <!-- History -->
        <a href="<?= base_url('/history') ?>" class="flex items-center space-x-3 px-2 py-2 rounded-lg
          <?= uri_string() == 'history' ? 'bg-[#eaf5ff] border-l-4 border-[#1e90ff] text-[#1e90ff]' : 'text-gray-600 hover:text-[#1e90ff]' ?>">
          <i class="fas fa-history text-[20px]"></i>
          <span x-show="open">History</span>
        </a>

        <hr class="border-gray-300 my-4" />

        <!-- Profile -->
        <a href="<?= base_url('/user/profile') ?>" class="flex items-center space-x-3 px-2 py-2 rounded-lg
          <?= uri_string() == 'user/profile' ? 'bg-[#eaf5ff] border-l-4 border-[#1e90ff] text-[#1e90ff]' : 'text-gray-600 hover:text-[#1e90ff]' ?>">
          <i class="far fa-user-circle text-[20px]"></i>
          <span x-show="open">Profile</span>
        </a>

        <!-- Logout -->
        <a href="<?= base_url('/logout') ?>" class="flex items-center space-x-3 px-2 py-2 rounded-lg text-gray-600 hover:text-red-600">
          <i class="fas fa-sign-out-alt text-[20px] text-[#1e90ff]"></i>
          <span x-show="open">Log out</span>
        </a>
      </nav>
    </div>

    <!-- User info -->
    <div class="flex items-center space-x-3 px-4 pb-6">
      <img src="https://storage.googleapis.com/a1aa/image/2d46a359-39a6-430a-d9f2-176171c41bfb.jpg"
           alt="Foto profil"
           class="w-10 h-10 rounded-full object-cover" />
      <div class="hidden md:block" x-show="open">
        <p class="text-gray-900 font-semibold text-base"><?= session('nama') ?? 'User' ?></p>
        <p class="text-gray-600 text-sm"><?= session('email') ?? 'user@example.com' ?></p>
      </div>
    </div>
  </aside>

</body>
</html>
