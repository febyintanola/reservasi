<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>403 - Tidak Berhak</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center p-6">
  <div class="bg-white shadow rounded-lg p-8 max-w-lg w-full text-center">
    <h1 class="text-3xl font-semibold text-red-600 mb-3">403</h1>
    <p class="text-gray-700 mb-6">Anda tidak memiliki hak untuk mengakses halaman ini.</p>
    <div class="space-x-3">
      <a href="<?= base_url('/') ?>" class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded">Ke Beranda</a>
      <a href="<?= base_url('/logout') ?>" class="inline-block bg-gray-200 hover:bg-gray-300 text-gray-800 px-4 py-2 rounded">Ganti Akun</a>
    </div>
  </div>
</body>
</html>
