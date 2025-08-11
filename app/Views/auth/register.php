<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Register - RuMa</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Quicksand&display=swap');
    body {
      font-family: 'Quicksand', sans-serif;
    }
  </style>
</head>
<body class="bg-white min-h-screen flex items-center justify-center px-4">
  <div class="w-full max-w-md bg-white rounded-lg shadow p-6">
    <div class="mb-6 text-center">
      <h1 class="text-3xl font-semibold text-gray-800">RuMa</h1>
      <p class="text-sm text-gray-500">Sistem Reservasi Ruang Rapat & Mobil</p>
    </div>

    <h2 class="text-base font-medium mb-4 text-center text-gray-700">Form Registrasi</h2>

    <?php if (session()->getFlashdata('errors')): ?>
      <div class="mb-4 bg-red-100 border border-red-300 text-red-700 px-4 py-2 rounded">
        <ul class="list-disc pl-5 text-sm">
          <?php foreach (session()->getFlashdata('errors') as $error): ?>
            <li><?= esc($error) ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endif; ?>

    <form method="post" action="<?= base_url('/register') ?>" autocomplete="off" class="space-y-4">

      <div>
        <label for="nama" class="block text-sm font-medium text-gray-700">Nama</label>
        <input
          type="text"
          id="nama"
          name="nama"
          value="<?= old('nama') ?>"
          required
          class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-gray-500 focus:border-gray-400 text-sm"
        />
      </div>

      <div>
        <label for="email" class="block text-sm font-medium text-gray-700">Email</label>
        <input
          type="email"
          id="email"
          name="email"
          value="<?= old('email') ?>"
          required
          class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-gray-500 focus:border-gray-400 text-sm"
        />
      </div>

      <div>
        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
        <input
          type="password"
          id="password"
          name="password"
          required
          class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-gray-500 focus:border-gray-400 text-sm"
        />
      </div>

      <div>
        <label for="divisi" class="block text-sm font-medium text-gray-700">Divisi</label>
        <select
          name="divisi"
          id="divisi"
          required
          class="w-full mt-1 px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-1 focus:ring-gray-500 focus:border-gray-400 text-sm bg-white"
        >
          <option value="">-- Pilih Divisi --</option>
          <?php
            $divisiList = [
              'GM','Umum','Keuangan','Akuntansi','Anggaran','SIS','Humas','SDM',
              'Engginering','K3','DHR 3','Ahli','RPH 1','DHR 1','RPH 2','DHR 2',
              'Area Service','RPH 3','Driver'
            ];
            foreach ($divisiList as $divisi):
          ?>
            <option value="<?= $divisi ?>"><?= $divisi ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <button
        type="submit"
        class="w-full bg-gray-800 text-white py-2 px-4 rounded hover:bg-gray-700 transition text-sm"
      >
        Daftar
      </button>
    </form>

    <p class="mt-4 text-center text-sm">
      Sudah punya akun?
      <a href="<?= base_url('/login') ?>" class="text-gray-800 font-medium hover:underline">Login</a>
    </p>
  </div>
</body>
</html>