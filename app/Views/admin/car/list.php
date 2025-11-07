<?= $this->extend('admin/layouts/header') ?>
<?= $this->section('content') ?>

<main class="max-w-7xl mx-auto px-4 py-6">
    <h1 class="text-center font-semibold text-lg mb-6 text-primary">Daftar Driver</h1>

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

    <?php $defaultAvatar = base_url('public/css/default-avatar.png'); ?>

    <section class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-x-8 gap-y-6 max-w-5xl mx-auto">
        <?php if (!empty($drivers)): ?>
            <?php foreach ($drivers as $d): ?>
                <?php 
                    $fotoPath = $defaultAvatar;
                    if (! empty($d['foto_url'])) {
                        $fotoPath = preg_match('#^https?://#i', $d['foto_url']) ? $d['foto_url'] : base_url($d['foto_url']);
                    }
                ?>
                <article class="card overflow-hidden">
                    <div class="w-full h-32 bg-primary-soft overflow-hidden flex items-center justify-center">
                        <img src="<?= esc($fotoPath) ?>" alt="Foto <?= esc($d['nama']) ?>" class="object-cover w-full h-full"
                             onerror="this.onerror=null;this.src='<?= esc($defaultAvatar) ?>';"/>
                    </div>
                    <div class="p-3 text-sm space-y-1">
                        <p class="font-semibold text-base truncate" title="<?= esc($d['nama']) ?>"><?= esc($d['nama']) ?></p>
                        <p class="text-muted text-xs">No HP: <?= esc($d['no_hp'] ?? '-') ?></p>
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
                            <a href="<?= base_url('admin/driver/edit/' . $d['id']) ?>" class="btn btn-primary btn-sm">Edit</a>
                            <form action="<?= base_url('admin/driver/delete/' . $d['id']) ?>" method="post" onsubmit="return confirm('Yakin ingin menghapus driver ini? Tindakan ini tidak dapat dibatalkan.');">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                            </form>
                        </div>
                    </div>
                </article>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="col-span-3 text-center text-muted">Belum ada driver.</p>
        <?php endif; ?>
    </section>

    <div class="flex justify-center mt-8">
    <a href="<?= base_url('admin/driver/tambah') ?>" 
       class="btn btn-primary text-sm px-5 py-2 rounded flex items-center space-x-2">
        <span>Tambahkan Driver</span>
            <i class="fas fa-plus"></i>
        </a>
    </div>
</main>

<?= $this->endSection() ?>
    <article class="flex items-center space-x-4 rounded-lg p-1">
