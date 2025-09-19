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
                <a class="btn-link" href="<?= site_url('admin/reports/export/rooms/excel?start_date=' . urlencode($start) . '&end_date=' . urlencode($end)) ?>">Export Excel</a>
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
                <a class="btn-link" href="<?= site_url('admin/reports/export/cars/excel?start_date=' . urlencode($start) . '&end_date=' . urlencode($end)) ?>">Export Excel</a>
                <a class="btn-link" href="<?= site_url('admin/reports/export/cars/pdf?start_date=' . urlencode($start) . '&end_date=' . urlencode($end)) ?>">Export PDF</a>
            </div>
        </div>
    </div>

    <h4>Data Terbaru - Ruang</h4>
    <table class="table" style="margin-bottom:16px;">
        <thead>
            <tr>
                <th>Tanggal</th><th>User</th><th>Ruang</th><th>Judul</th><th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (($recentRooms ?? []) as $r): ?>
                <tr>
                    <td><?= esc($roomDateKey ? ($r[$roomDateKey] ?? '') : '') ?></td>
                    <td><?= esc($roomNameKey && isset($r[$roomNameKey]) ? $r[$roomNameKey] : ($r['nama'] ?? ($r['user_id'] ?? ''))) ?></td>
                    <td><?= esc($r['room_id'] ?? '') ?></td>
                    <td><?= esc($r['acara'] ?? $r['title'] ?? '') ?></td>
                    <td><?= esc($roomStatusKey ? ($r[$roomStatusKey] ?? '') : '') ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h4>Data Terbaru - Kendaraan</h4>
    <table class="table">
        <thead>
            <tr>
                <th>Tanggal</th><th>User</th><th>Mobil</th><th>Keperluan</th><th>Status</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach (($recentCars ?? []) as $r): ?>
                <tr>
                    <td><?= esc($carDateKey ? ($r[$carDateKey] ?? '') : '') ?></td>
                    <td><?= esc($carNameKey && isset($r[$carNameKey]) ? $r[$carNameKey] : ($r['nama'] ?? ($r['user_id'] ?? ''))) ?></td>
                    <td>
                        <?php
                        $mobil = '';
                        if (!empty($r['mobil_jenis']) || !empty($r['mobil_plat'])) {
                            $mobil = trim(($r['mobil_jenis'] ?? '') . ' ' . ($r['mobil_plat'] ?? ''));
                        } elseif (!empty($r['car_id'])) {
                            $mobil = $r['car_id'];
                        } else {
                            $mobil = 'N/A';
                        }
                        ?>
                        <?= esc($mobil) ?>
                    </td>
                    <td><?= esc($r['keperluan'] ?? $r['nama_pekerjaan'] ?? '') ?></td>
                    <td><?= esc($carStatusKey ? ($r[$carStatusKey] ?? '') : ($r['status'] ?? '')) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
<?= $this->endSection() ?>