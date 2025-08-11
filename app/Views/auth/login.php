<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login - RuMa</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Quicksand&display=swap');
    body {
      font-family: 'Quicksand', sans-serif;
    }
  </style>
</head>
<body class="bg-white min-h-screen flex items-center justify-center px-4">
  <div class="w-full max-w-sm">
    <div class="text-center mb-6">
      <h1 class="text-4xl font-semibold text-gray-800 mb-2">RuMa</h1>
      <p class="text-gray-500 text-sm">Sistem Reservasi Ruang Rapat & Mobil</p>
    </div>

    <div class="border border-gray-200 rounded-md p-6 shadow-sm">
      <h2 class="text-base font-semibold mb-4 text-center">Login</h2>

      <!-- Alert Error -->
      <?php if (session()->getFlashdata('error')): ?>
        <div class="bg-red-100 text-red-700 text-sm rounded p-2 mb-4">
          <?= session()->getFlashdata('error') ?>
        </div>
      <?php endif; ?>

      <!-- Alert Success (misalnya setelah logout atau register) -->
      <?php if (session()->getFlashdata('success')): ?>
        <div class="bg-green-100 text-green-700 text-sm rounded p-2 mb-4">
          <?= session()->getFlashdata('success') ?>
        </div>
      <?php endif; ?>

      <form method="post" action="<?= base_url('/login') ?>" autocomplete="off">
        <?= csrf_field() ?> <!-- Tambahan keamanan CSRF -->

        <label for="email" class="block text-xs mb-1">Email</label>
        <input
          type="email"
          name="email"
          id="email"
          required
          value="<?= old('email') ?>"
          class="w-full text-sm border border-gray-300 rounded-md px-3 py-2 mb-4 focus:outline-none focus:ring-1 focus:ring-gray-400"
        />

        <label for="password" class="block text-xs mb-1">Password</label>
        <input
          type="password"
          name="password"
          id="password"
          required
          class="w-full text-sm border border-gray-300 rounded-md px-3 py-2 mb-4 focus:outline-none focus:ring-1 focus:ring-gray-400"
        />

        <div class="flex justify-between mb-4 text-sm">
          <a href="<?= base_url('/register') ?>" class="text-gray-600 underline">Register</a>
          <a href="#" class="text-gray-400 cursor-not-allowed" title="Belum tersedia">Forgot Password?</a>
        </div>

        <button
          type="submit"
          class="w-full bg-gray-900 text-white text-sm rounded-md py-2 hover:bg-gray-700 transition"
        >
          Log In
        </button>
      </form>
    </div>
  </div>
</body>
</html>
