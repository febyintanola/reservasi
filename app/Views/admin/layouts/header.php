<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title><?= $title ?? 'SIREMO' ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex bg-white min-h-screen">
  
  <!-- Sidebar -->
  <?= $this->include('admin/layouts/sidebar') ?>

  <!-- Main Content Area -->
  <div class="flex-1 flex flex-col">
    <header class="flex items-center justify-between px-4 py-3 border-b border-white bg-white">
      <div class="flex-1 flex justify-center">
        <div class="text-black text-base font-normal">RuMa</div>
      </div>
      <a href="<?= base_url('/user/profile') ?>" class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center ml-auto">
        <i class="fas fa-user text-gray-400 text-sm"></i>
      </a>
    </header>

    <main class="flex-1 p-6 overflow-y-auto">
      <?= $this->renderSection('content') ?>
    </main>
  </div>
</body>
 <script>
    // Toggle Sidebar
    const toggleBtn = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');

    toggleBtn.addEventListener('click', () => {
      sidebar.classList.toggle('-ml-64');
    });
  </script>


</html>
