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
    <h1 class="text-center font-semibold text-lg mb-6 text-black">Daftar Driver</h1>

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
        <?php if (!empty($drivers)): ?>
            <?php foreach ($drivers as $d): ?>
                <?php 
                    $fotoPath = !empty($d['foto_url']) ? base_url($d['foto_url']) : 'https://via.placeholder.com/160x120?text=Driver';
                ?>
                <article class="border rounded-md overflow-hidden bg-white shadow-sm">
                    <div class="w-full h-32 bg-gray-100 overflow-hidden flex items-center justify-center">
                        <img src="<?= esc($fotoPath) ?>" alt="Foto <?= esc($d['nama']) ?>" class="object-cover w-full h-full"/>
                    </div>
                    <div class="p-3 text-black text-sm space-y-1">
                        <p class="font-semibold text-base truncate" title="<?= esc($d['nama']) ?>"><?= esc($d['nama']) ?></p>
                        <p class="text-gray-600 text-xs">No HP: <?= esc($d['no_hp'] ?? '-') ?></p>
                        <p class="flex items-center gap-1">
                            <?php $st = $d['status'] ?? 'Available'; ?>
                                                        <?php
                                                            $badge = 'badge-neutral';
                                                            if (strtolower($st) === 'available') { $badge = 'badge-success'; }
                                                        ?>
                                                        <span class="badge text-[10px] uppercase tracking-wide font-semibold <?= $badge ?>">
                                                                <?= esc($st) ?>
                                                        </span>
                        </p>
                        <div class="pt-2 flex gap-2">
                            <a href="<?= base_url('admin/driver/edit/' . $d['id']) ?>" class="btn-primary btn-sm">Edit</a>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="col-span-3 text-center text-gray-500">Belum ada driver.</p>
        <?php endif; ?>
    </section>

    <div class="flex justify-center mt-8">
    <a href="<?= base_url('admin/driver/tambah') ?>" 
       class="bg-gray-900 text-white text-sm px-5 py-2 rounded flex items-center space-x-2">
        <span>Tambahkan Driver</span>
            <i class="fas fa-plus"></i>
        </a>
    </div>
</main>

<?= $this->endSection() ?>
    <article class="flex items-center space-x-4 rounded-lg p-1">
