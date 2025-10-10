<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Login - RuMa</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <!-- Custom favicon/logo -->
  <link rel="icon" type="image/png" href="<?= base_url('uploads/logo.png') ?>" />
  <link rel="apple-touch-icon" href="<?= base_url('uploads/logo.png') ?>" />
  <link rel="shortcut icon" href="<?= base_url('uploads/logo.png') ?>" />
  <link href="<?= base_url('css/theme.css') ?>" rel="stylesheet" />

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
      <div class="flex items-center justify-center gap-2 mb-2">
        <img src="<?= base_url('uploads/logo.png') ?>" alt="Logo RuMa" class="h-10 w-10 object-contain" />
        <h1 class="text-3xl font-semibold text-gray-800">RuMa</h1>
      </div>
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
        <div class="relative mb-4">
          <input
            type="password"
            name="password"
            id="password"
            required
            class="w-full text-sm border border-gray-300 rounded-md px-3 py-2 pr-10 focus:outline-none focus:ring-1 focus:ring-gray-400"
          />
          <button type="button" id="togglePassword" aria-label="Show password" class="absolute inset-y-0 right-0 px-3 flex items-center text-gray-500 hover:text-gray-700">
            <svg id="iconEye" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.644C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .638C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
              <circle cx="12" cy="12" r="3" />
            </svg>
            <svg id="iconEyeOff" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c1.742 0 3.393-.402 4.846-1.118M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.5a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.243 4.243L9.172 9.172" />
            </svg>
          </button>
        </div>

        <div class="flex justify-between mb-4 text-sm">
          <a href="<?= base_url('/register') ?>" class="text-gray-600 underline">Register</a>
          <a href="<?= base_url('/forgot-password') ?>" class="text-gray-600 hover:underline" title="Reset password">Forgot Password?</a>
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
<script>
  (function(){
    const input = document.getElementById('password');
    const btn = document.getElementById('togglePassword');
    if(!input || !btn) return;
    const eye = document.getElementById('iconEye');
    const eyeOff = document.getElementById('iconEyeOff');
    btn.addEventListener('click', ()=>{
      const show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      eye.classList.toggle('hidden', !show);
      eyeOff.classList.toggle('hidden', show);
      btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
    });
  })();
</script>
</html>
