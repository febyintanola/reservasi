<html lang="en">
 <head>
  <meta charset="utf-8"/>
  <meta content="width=device-width, initial-scale=1" name="viewport"/>
  <title>
   Daftar Driver
  </title>
  <script src="https://cdn.tailwindcss.com">
  </script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
 </head>
 <body class="bg-white font-sans">
  <header class="flex items-center justify-between px-4 py-3 border-b border-gray-200">
   <button aria-label="Menu" class="text-black text-xl">
    <i class="fas fa-bars">
    </i>
   </button>
<?= $this->extend('admin/layouts/header') ?>
<?= $this->section('content') ?>

<main class="max-w-7xl mx-auto px-4 py-6">
    <h1 class="text-center font-semibold text-lg mb-6 text-black">Daftar Booking Mobil</h1>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="mb-4 rounded-md bg-green-50 border border-green-200 text-green-800 px-4 py-2 text-sm">
            <?= esc(session()->getFlashdata('success')) ?>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="mb-4 rounded-md bg-red-50 border border-red-200 text-red-800 px-4 py-2 text-sm">
            <?= nl2br(esc(session()->getFlashdata('error'))) ?>
        </div>
    <?php endif; ?>

    <section class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-x-8 gap-y-6 max-w-5xl mx-auto">
        <?php if (!empty($bookings)): ?>
            <?php foreach ($bookings as $b): ?>
                <article class="border rounded-md overflow-hidden bg-white shadow-sm">
                    <div class="p-3 text-black text-sm space-y-1">
                        <p class="font-semibold text-base"><?= esc($b['nama']) ?></p>
                        <p class="text-gray-600 text-xs">Tanggal: <?= esc($b['tanggal_pergi']) ?> - <?= esc($b['tanggal_pulang']) ?></p>
                        <p class="text-gray-600 text-xs">Tujuan: <?= esc($b['tujuan']) ?></p>
                        <p class="text-gray-600 text-xs">Keperluan: <?= esc($b['keperluan']) ?></p>
                        <p>
                            <span class="px-2 py-0.5 rounded-full text-[10px] uppercase tracking-wide 
                                <?php
                                $status = $b['status'] ?? 'pending';
                                echo $status === 'accepted' ? 'bg-green-200 text-green-800' : ($status === 'rejected' ? 'bg-red-200 text-red-800' : 'bg-yellow-200 text-yellow-800');
                                ?>">
                                <?= esc($status) ?>
                            </span>
                        </p>
                        <div class="pt-2 flex gap-2">
                            <a href="<?= base_url('admin/car/detail/' . $b['id']) ?>" class="inline-block bg-gray-200 hover:bg-gray-300 text-xs px-3 py-1 rounded">Detail</a>
                            <a href="<?= base_url('admin/car/edit/' . $b['id']) ?>" class="inline-block bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-1 rounded">Edit</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="col-span-3 text-center text-gray-500">Belum ada booking mobil.</p>
        <?php endif; ?>
    </section>

    <div class="flex justify-center mt-8">
        <a href="<?= base_url('admin/car/tambah') ?>" 
           class="bg-gray-900 text-white text-sm px-5 py-2 rounded flex items-center space-x-2">
            <span>Tambahkan Booking Mobil</span>
            <i class="fas fa-plus"></i>
        </a>
    </div>
</main>

<?= $this->endSection() ?>
    <article class="flex items-center space-x-4 rounded-lg p-1">
