<?= $this->extend('user/layouts/header') ?>

<?= $this->section('content') ?>
  <main class="flex-grow flex items-center justify-center px-4">
   <div class="max-w-xs w-full border border-gray-400 rounded-md p-4 relative">
    <div class="flex items-start justify-between mb-2">
     <div class="flex items-center space-x-2">
      <i class="fas fa-info-circle text-black text-sm">
      </i>
      <span class="font-semibold text-black text-sm leading-tight">
       Reservasi Berhasill
      </span>
     </div>
     <button aria-label="Close notification" class="text-black text-sm leading-none">
      <i class="fas fa-times">
      </i>
     </button>
    </div>
    <p class="text-black text-sm leading-relaxed mb-3">
     Silahkan tunggu konfirmasi dari admin
    </p>
    <button onclick="window.location.href='/home'" class="bg-gray-800 text-white text-sm rounded-md px-3 py-1 hover:bg-gray-700 transition">
    Close
    </button>
   </div>
  </main>
 </body>
<?= $this->endSection() ?>

