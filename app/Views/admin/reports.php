<?= $this->extend('admin/layouts/header')?>
<?= $this->section('content') ?>
<div class="container">
    <?php
    // Safeguard dynamic keys passed from controller
    $roomDateKey   = isset($roomDateCol) && is_string($roomDateCol) ? $roomDateCol : null;
    $roomStatusKey = isset($roomStatusCol) && is_string($roomStatusCol) ? $roomStatusCol : null;
    $roomNameKey   = isset($roomUserNameField) && is_string($roomUserNameField) ? $roomUserNameField : null;

    $carDateKey    = isset($carDateDisplayCol) && is_string($carDateDisplayCol) ? $carDateDisplayCol : (isset($carDateCol) && is_string($carDateCol) ? $carDateCol : null);
    $carStatusKey  = isset($carStatusCol) && is_string($carStatusCol) ? $carStatusCol : null;
    $carNameKey    = isset($carUserNameField) && is_string($carUserNameField) ? $carUserNameField : null;
    ?>
    <style>
        .report-card { border:1px solid #e5e7eb; border-radius:8px; padding:12px; background:#fff; }
        .report-grid { display:grid; grid-template-columns: repeat(2, 1fr); gap:12px; margin-bottom:16px; }
        .table { width:100%; border-collapse:separate; border-spacing:0; font-size:14px; background:#fff; border:1px solid #e5e7eb; border-radius:8px; overflow:hidden; }
        .table th { background:#f9fafb; text-align:left; padding:10px 12px; color:#111827; border-bottom:1px solid #e5e7eb; }
        .table td { padding:10px 12px; border-bottom:1px solid #f3f4f6; color:#374151; }
        .table tr:nth-child(even) td { background:#fcfcfd; }
        .table tr:hover td { background:#f8fafc; }
        .toolbar { display:flex; gap:8px; align-items:center; }
        .btn-link { color:#2563eb; text-decoration:none; font-weight:500; }
        .btn-link:hover { text-decoration:underline; }
        .kpis p { margin:4px 0; }
        @media (max-width: 800px) { .report-grid { grid-template-columns: 1fr; } }
    </style>
    <h2>Laporan & Statistik Booking</h2>
    <form method="get" class="mb-3" action="<?= site_url('admin/reports') ?>">
        <div style="display:flex; gap:8px; align-items:center;">
            <label>Dari</label>
            <input type="date" name="start_date" value="<?= esc($start) ?>">
            <label>Sampai</label>
            <input type="date" name="end_date" value="<?= esc($end) ?>">
            <button type="submit">Terapkan</button>
        </div>
    </form>

    <div class="report-grid">
        <div class="report-card">
            <h4>Ruang Rapat</h4>
            <div class="kpis"><p>Total: <b><?= esc($roomTotal) ?></b></p></div>
            <ul>
                <?php foreach (($roomByStatus ?? []) as $s): ?>
                    <li><?= esc($s['status'] ?? 'N/A') ?>: <?= esc($s['total'] ?? 0) ?></li>
                <?php endforeach; ?>
            </ul>
            <div class="toolbar">
                <!-- Export Excel removed -->
                <a class="btn-link" href="<?= site_url('admin/reports/export/rooms/pdf?start_date=' . urlencode($start) . '&end_date=' . urlencode($end)) ?>">Export PDF</a>
            </div>
        </div>
        <div class="report-card">
            <h4>Kendaraan</h4>
            <div class="kpis"><p>Total: <b><?= esc($carTotal) ?></b></p></div>
            <ul>
                <?php foreach (($carByStatus ?? []) as $s): ?>
                    <li><?= esc($s['status'] ?? 'N/A') ?>: <?= esc($s['total'] ?? 0) ?></li>
                <?php endforeach; ?>
            </ul>
            <div class="toolbar">
                <!-- Export Excel removed -->
                <a class="btn-link" href="<?= site_url('admin/reports/export/cars/pdf?start_date=' . urlencode($start) . '&end_date=' . urlencode($end)) ?>">Export PDF</a>
            </div>
        </div>
    </div>

    <h4>Data Terbaru - Ruang</h4>

            <table class="min-w-full card overflow-hidden" style="margin-bottom:16px;">
                <thead class="bg-slate-50 text-xs text-slate-600 uppercase">
                    <tr>
                        <th class="px-6 py-3 text-left">Nama Acara</th>
                        <th class="px-6 py-3 text-left">Pemesan</th>
                        <th class="px-6 py-3 text-left">Tipe</th>
                        <th class="px-6 py-3 text-left">Tanggal</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-slate-700 divide-y divide-gray-200">
                    <?php if (!empty($recentRooms)): ?>
                        <?php foreach ($recentRooms as $r): ?>
                            <?php
                                $status = strtolower($roomStatusKey ? ($r[$roomStatusKey] ?? '') : '');
                                $badgeClass = match ($status) {
                                    'pending' => 'badge bg-yellow-50 text-yellow-700 border-yellow-200',
                                    'accepted' => 'badge bg-green-50 text-green-700 border-green-200',
                                    'rejected' => 'badge bg-red-50 text-red-600 border-red-200',
                                    default => 'badge',
                                };
                            ?>
                            <tr class="bg-white">
                                <td class="px-6 py-4"><?= esc($r['acara'] ?? $r['title'] ?? '') ?></td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-slate-800"><?= esc($roomNameKey && isset($r[$roomNameKey]) ? $r[$roomNameKey] : ($r['nama'] ?? ($r['user_id'] ?? ''))) ?></div>
                                    <div class="text-xs text-slate-500">Ruang: <?= esc($r['room_id'] ?? '') ?></div>
                                </td>
                                <td class="px-6 py-4">Reservasi Ruangan</td>
                                <td class="px-6 py-4"><?= esc($roomDateKey ? ($r[$roomDateKey] ?? '') : '') ?></td>
                                <td class="px-6 py-4"><span class="<?= $badgeClass ?>"><?= ucfirst($status) ?></span></td>
                                <td class="px-6 py-4 text-right">
                                    <a href="<?= base_url('admin/booking/detail/' . ($r['id'] ?? '')) ?>" class="btn-primary text-xs">Detail</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center py-4 text-gray-400">Belum ada data reservasi ruang.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

    <h4>Data Terbaru - Kendaraan</h4>

            <table class="min-w-full card overflow-hidden">
                <thead class="bg-slate-50 text-xs text-slate-600 uppercase">
                    <tr>
                        <th class="px-6 py-3 text-left">Nama Acara</th>
                        <th class="px-6 py-3 text-left">Pemesan</th>
                        <th class="px-6 py-3 text-left">Tipe</th>
                        <th class="px-6 py-3 text-left">Tanggal</th>
                        <th class="px-6 py-3 text-left">Status</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm text-slate-700 divide-y divide-gray-200">
                    <?php if (!empty($recentCars)): ?>
                        <?php foreach ($recentCars as $r): ?>
                            <?php
                                $status = strtolower($carStatusKey ? ($r[$carStatusKey] ?? ($r['status'] ?? '')) : ($r['status'] ?? ''));
                                $badgeClass = match ($status) {
                                    'pending' => 'badge bg-yellow-50 text-yellow-700 border-yellow-200',
                                    'accepted' => 'badge bg-green-50 text-green-700 border-green-200',
                                    'rejected' => 'badge bg-red-50 text-red-600 border-red-200',
                                    default => 'badge',
                                };
                                $mobil = '';
                                if (!empty($r['mobil_jenis']) || !empty($r['mobil_plat'])) {
                                    $mobil = trim(($r['mobil_jenis'] ?? '') . ' ' . ($r['mobil_plat'] ?? ''));
                                } elseif (!empty($r['car_id'])) {
                                    $mobil = $r['car_id'];
                                } else {
                                    $mobil = 'N/A';
                                }
                            ?>
                            <tr class="bg-white">
                                <td class="px-6 py-4"><?= esc($r['keperluan'] ?? $r['nama_pekerjaan'] ?? '') ?></td>
                                <td class="px-6 py-4">
                                    <div class="font-medium text-slate-800"><?= esc($carNameKey && isset($r[$carNameKey]) ? $r[$carNameKey] : ($r['nama'] ?? ($r['user_id'] ?? ''))) ?></div>
                                    <div class="text-xs text-slate-500">Mobil: <?= esc($mobil) ?></div>
                                </td>
                                <td class="px-6 py-4">Reservasi Mobil</td>
                                <td class="px-6 py-4"><?= esc($carDateKey ? ($r[$carDateKey] ?? '') : '') ?></td>
                                <td class="px-6 py-4"><span class="<?= $badgeClass ?>"><?= ucfirst($status) ?></span></td>
                                <td class="px-6 py-4 text-right">
                                    <a href="<?= base_url('admin/car/detailMobil/' . ($r['id'] ?? '')) ?>" class="btn-primary text-xs">Detail</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center py-4 text-gray-400">Belum ada data reservasi kendaraan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
</div>
<?= $this->endSection() ?>