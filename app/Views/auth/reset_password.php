<?= $this->extend('auth/layout') ?>
<?= $this->section('content') ?>
<div class="max-w-md mx-auto mt-10 bg-white shadow p-6 rounded">
    <h1 class="text-xl font-semibold mb-4">Reset Password</h1>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="mb-3 p-2 bg-red-100 text-red-700 text-sm rounded"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>
    <form method="post" action="<?= base_url('/reset-password') ?>" class="space-y-4">
        <?= csrf_field() ?>
        <input type="hidden" name="token" value="<?= esc($token) ?>" />
        <div>
            <label class="block text-sm font-medium mb-1">Password Baru</label>
            <input type="password" name="password" required minlength="6" class="w-full border rounded px-3 py-2" />
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">Konfirmasi Password</label>
            <input type="password" name="password_confirm" required minlength="6" class="w-full border rounded px-3 py-2" />
        </div>
        <button class="w-full bg-green-600 hover:bg-green-700 text-white py-2 rounded">Simpan Password</button>
    </form>
    <div class="mt-4 text-sm"><a class="text-blue-600 hover:underline" href="<?= base_url('/login') ?>">Kembali ke login</a></div>
</div>
<?= $this->endSection() ?>
