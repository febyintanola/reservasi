<?= $this->extend('auth/layout') ?>
<?= $this->section('content') ?>
<div class="max-w-md mx-auto mt-10 bg-white shadow p-6 rounded">
    <h1 class="text-xl font-semibold mb-4">Lupa Password</h1>
    <?php if (session()->getFlashdata('message')): ?>
        <div class="mb-3 p-2 bg-green-100 text-green-700 text-sm rounded"><?= esc(session()->getFlashdata('message')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="mb-3 p-2 bg-red-100 text-red-700 text-sm rounded"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>
    <form method="post" action="<?= base_url('/forgot-password') ?>" class="space-y-4">
        <?= csrf_field() ?>
        <div>
            <label class="block text-sm font-medium mb-1">Email</label>
            <input type="email" name="email" required class="w-full border rounded px-3 py-2" placeholder="you@example.com" value="<?= esc(old('email')) ?>" />
        </div>
        <button class="w-full bg-blue-600 hover:bg-blue-700 text-white py-2 rounded">Kirim Link Reset</button>
    </form>
    <p class="text-xs text-gray-500 mt-4">Untuk sekarang link reset ditampilkan langsung setelah submit (belum kirim email).</p>
    <div class="mt-4 text-sm"><a class="text-blue-600 hover:underline" href="<?= base_url('/login') ?>">Kembali ke login</a></div>
</div>
<?= $this->endSection() ?>
