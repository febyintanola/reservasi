<?= $this->extend('admin/layouts/header') ?>

<?= $this->section('content') ?>

<main class="max-w-7xl mx-auto px-4 py-6">
    <h1 class="text-center font-semibold text-lg mb-6 text-primary">Daftar Ruang Rapat</h1>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert-success mb-4">
                <?= esc(session()->getFlashdata('success')) ?>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert-danger mb-4">
                <?= nl2br(esc(session()->getFlashdata('error'))) ?>
            </div>
        <?php endif; ?>

    <section class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-x-8 gap-y-6 max-w-5xl mx-auto">
        <?php if (!empty($rooms)): ?>
            <?php foreach ($rooms as $room): ?>
                                <article class="card overflow-hidden">
                    <img src="<?= esc($room['ruangrapat_url'] ?: 'https://via.placeholder.com/150') ?>" alt="<?= esc($room['nama_ruangan'] ?? 'Ruang') ?>" 
                         class="w-full h-24 object-cover bg-primary-soft"/>
                                        <div class="mt-2 p-3 text-sm">
                        <p class="font-semibold"><?= esc($room['nama_ruangan']) ?></p>
                        <p>Lokasi: <?= esc($room['lokasi']) ?></p>
                        <p class="text-muted text-xs">Kapasitas: <?= esc($room['kapasitas']) ?> Orang</p>
                        <p class="text-sm font-normal">Jenis: <?= esc($room['jenis']) ?></p>
                                                <div class="mt-2 flex gap-2">
                                                    <a href="<?= base_url('admin/ruang/edit/' . $room['id']) ?>" class="btn btn-primary btn-sm">Edit</a>
                                                    <form action="<?= base_url('admin/ruang/delete/' . $room['id']) ?>" method="post" onsubmit="return confirm('Yakin ingin menghapus ruangan ini? Tindakan ini tidak dapat dibatalkan.');">
                                                        <?= csrf_field() ?>
                                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                                    </form>
                                                </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="col-span-3 text-center text-muted">Tidak ada ruang rapat.</p>
        <?php endif; ?>
    </section>

    <div class="flex justify-center mt-8">
        <a href="<?= base_url('admin/ruang/tambah') ?>" 
           class="btn btn-primary text-sm px-5 py-2 rounded flex items-center space-x-2">
            <span>Tambahkan Ruang Rapat</span>
            <i class="fas fa-plus"></i>
        </a>
    </div>
</main>
<?= $this->endSection() ?>
