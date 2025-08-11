<?= $this->extend('admin/layouts/header') ?>

<?= $this->section('content') ?>

<main class="max-w-7xl mx-auto px-4 py-6">
    <h1 class="text-center font-semibold text-lg mb-6 text-black">Daftar Ruang Rapat</h1>

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
        <?php if (!empty($rooms)): ?>
            <?php foreach ($rooms as $room): ?>
                                <article class="border rounded-md overflow-hidden">
                    <img src="<?= esc($room['ruangrapat_url'] ?: 'https://via.placeholder.com/150') ?>" alt="<?= esc($room['nama_ruangan'] ?? 'Ruang') ?>" 
                         class="w-full h-24 object-cover bg-gray-200"/>
                                        <div class="mt-2 p-3 text-black text-sm">
                        <p><?= esc($room['nama_ruangan']) ?></p>
                        <p class="font-semibold">Lokasi: <?= esc($room['lokasi']) ?></p>
                        <p class="text-gray-500 text-xs">Kapasitas: <?= esc($room['kapasitas']) ?> Orang</p>
                        <p class="text-black text-sm font-normal">Jenis: <?= esc($room['jenis']) ?></p>
                                                <div class="mt-2 flex gap-2">
                                                    <a href="<?= base_url('admin/ruang/edit/' . $room['id']) ?>" class="inline-block bg-blue-600 text-white text-xs px-3 py-1 rounded">Edit</a>
                                                </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="col-span-3 text-center text-gray-500">Tidak ada ruang rapat.</p>
        <?php endif; ?>
    </section>

    <div class="flex justify-center mt-8">
        <a href="<?= base_url('admin/ruang/tambah') ?>" 
           class="bg-gray-900 text-white text-sm px-5 py-2 rounded flex items-center space-x-2">
            <span>Tambahkan Ruang Rapat</span>
            <i class="fas fa-plus"></i>
        </a>
    </div>
</main>
</body>
<?= $this->endSection() ?>
