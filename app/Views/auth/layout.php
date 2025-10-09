<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?= esc($title ?? 'Auth') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" />
    <!-- Custom favicon/logo -->
    <link rel="icon" type="image/png" href="<?= base_url('uploads/logo.png') ?>" />
    <link rel="apple-touch-icon" href="<?= base_url('uploads/logo.png') ?>" />
    <link rel="shortcut icon" href="<?= base_url('uploads/logo.png') ?>" />
</head>
<body class="bg-gray-100 min-h-screen">
    <?= $this->renderSection('content') ?>
</body>
</html>
