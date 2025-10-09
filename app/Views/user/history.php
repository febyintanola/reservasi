<?= $this->extend('user/layouts/header') ?>

<?= $this->section('content') ?>
<?php
  // Helper sederhana untuk warna badge status
  $statusClass = function ($status) {
    $s = strtolower(trim($status));
    switch ($s) {
      case 'diterima':
      case 'accepted':
        return 'bg-green-100 text-green-800 border border-green-200';
      case 'pending':
      case 'menunggu':
      case 'proses':
        return 'bg-yellow-100 text-yellow-800 border border-yellow-200';
      case 'ditolak':
      case 'rejected':
        return 'bg-red-100 text-red-800 border border-red-200';
      default:
        return 'bg-gray-100 text-gray-800 border border-gray-200';
    }
  };
?>
<main class="max-w-3xl mx-auto px-4 py-8">

  <!-- Toggle tombol -->
  <div class="flex gap-2 justify-center mb-6">
    <button id="btn-ruang" class="px-4 py-2 rounded tab-switch-active font-semibold">Riwayat Ruang</button>
    <button id="btn-mobil" class="px-4 py-2 rounded tab-switch font-semibold">Riwayat Mobil</button>
  </div>

  <!-- Kontainer ruangan (tersedia initial server-side render) -->
  <div id="container-ruang" data-type="ruang" data-page="1" data-nomore="false">
    <h2 class="text-xl font-bold mb-4">Riwayat Reservasi Ruang Rapat</h2>
    <div id="list-ruang">
      <?php if (!empty($historyRuang)): ?>
        <?php foreach ($historyRuang as $item): ?>
          <section class="history-item border border-primary rounded-lg p-4 mb-6 shadow-sm" data-id="<?= esc($item['id']) ?>">
            <h3 class="text-gray-800 text-lg font-semibold mb-1"><?= esc($item['judul']) ?></h3>
            <p class="text-gray-600 text-sm leading-tight">
              Tanggal: <?= esc($item['tanggal']) ?><br/>
              Waktu: <?= esc($item['waktu_mulai']) ?> - <?= esc($item['waktu_selesai']) ?><br/>
              Ruangan: <?= esc($item['lokasi']) ?><br/>
              Status:
              <span class="inline-block text-xs font-semibold rounded px-2 py-0.5 <?= $statusClass($item['status']) ?>">
                <?= esc($item['status']) ?>
              </span>
            </p>
            <div class="flex justify-end mt-3">
              <a href="<?= base_url('ruang/detail/'.$item['id']) ?>" class="btn-primary btn-sm">Detail</a>
            </div>
          </section>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="text-center text-gray-500">Belum ada data riwayat reservasi ruang rapat.</p>
      <?php endif; ?>
    </div>

    <div class="text-center py-4" id="loading-ruang" style="display:none;">Memuat...</div>
    <div class="text-center py-4" id="more-ruang">
      <button class="px-3 py-2 bg-gray-100 rounded" id="btn-more-ruang">Load more</button>
    </div>
  </div>

  <!-- Kontainer mobil (tersedia initial server-side render) -->
  <div id="container-mobil" data-type="mobil" data-page="1" data-nomore="false" style="display:none;">
    <h2 class="text-xl font-bold mb-4 mt-10">Riwayat Reservasi Mobil</h2>
    <div id="list-mobil">
      <?php if (!empty($historyMobil)): ?>
        <?php foreach ($historyMobil as $item): ?>
          <section class="history-item border border-primary rounded-lg p-4 mb-6 shadow-sm" data-id="<?= esc($item['id']) ?>">
            <h3 class="text-gray-800 text-lg font-semibold mb-1"><?= esc($item['judul']) ?></h3>
            <p class="text-gray-600 text-sm leading-tight">
              Tanggal Pergi: <?= esc($item['tanggal_pergi']) ?><br/>
              Tanggal Pulang: <?= esc($item['tanggal_pulang']) ?><br/>
              Tujuan: <?= esc($item['tujuan']) ?><br/>
              Lama: <?= esc($item['jumlah_hari']) ?> hari<br/>
              Status:
              <span class="inline-block text-xs font-semibold rounded px-2 py-0.5 <?= $statusClass($item['status']) ?>">
                <?= esc($item['status']) ?>
              </span>
            </p>
            <div class="flex justify-end mt-3">
              <a href="<?= base_url('user/car/detail/' . $item['id']) ?>" class="btn-primary btn-sm">Detail</a>
            </div>
          </section>
        <?php endforeach; ?>
      <?php else: ?>
        <p class="text-center text-gray-500">Belum ada data riwayat reservasi mobil.</p>
      <?php endif; ?>
    </div>

    <div class="text-center py-4" id="loading-mobil" style="display:none;">Memuat...</div>
    <div class="text-center py-4" id="more-mobil">
      <button class="px-3 py-2 bg-gray-100 rounded" id="btn-more-mobil">Load more</button>
    </div>
  </div>

</main>

<script>
  (function () {
    const baseUrl = '<?= base_url() ?>';
    const perScrollThreshold = 300; // px from bottom to trigger load
    let active = 'ruang'; // default active view
    const containers = {
      ruang: document.getElementById('container-ruang'),
      mobil: document.getElementById('container-mobil')
    };

    // Toggle buttons
    const btnRuang = document.getElementById('btn-ruang');
    const btnMobil = document.getElementById('btn-mobil');
    btnRuang.addEventListener('click', () => setActive('ruang'));
    btnMobil.addEventListener('click', () => setActive('mobil'));

    function setActive(type) {
      if (type === active) return;
      active = type;
      if (type === 'ruang') {
        containers.ruang.style.display = '';
        containers.mobil.style.display = 'none';
  btnRuang.classList.add('tab-switch-active');
  btnRuang.classList.remove('tab-switch');
  btnMobil.classList.remove('tab-switch-active');
  btnMobil.classList.add('tab-switch');
      } else {
        containers.mobil.style.display = '';
        containers.ruang.style.display = 'none';
  btnMobil.classList.add('tab-switch-active');
  btnMobil.classList.remove('tab-switch');
  btnRuang.classList.remove('tab-switch-active');
  btnRuang.classList.add('tab-switch');
      }
    }

    // Scroll handler for infinite load
    let loading = false;
    window.addEventListener('scroll', onScroll);
    function onScroll() {
      const cont = containers[active];
      if (!cont || cont.dataset.nomore === 'true' || loading) return;
      const scrolledFromBottom = document.documentElement.scrollHeight - (window.scrollY + window.innerHeight);
      if (scrolledFromBottom < perScrollThreshold) {
        loadMore(active);
      }
    }

    // Fallback "Load more" buttons
    document.getElementById('btn-more-ruang').addEventListener('click', () => loadMore('ruang'));
    document.getElementById('btn-more-mobil').addEventListener('click', () => loadMore('mobil'));

    async function loadMore(type) {
      const cont = containers[type];
      if (!cont || cont.dataset.nomore === 'true') return;
      const list = document.getElementById('list-' + type);
      const loadingEl = document.getElementById('loading-' + type);
      const moreEl = document.getElementById('more-' + type);
      let page = parseInt(cont.dataset.page || '1', 10);
      page = page + 1;

      loading = true;
      loadingEl.style.display = '';
      moreEl.style.display = 'none';

      try {
        const url = `${baseUrl.replace(/\/$/,'')}/user/history/load?type=${encodeURIComponent(type)}&page=${page}`;
        const res = await fetch(url, { credentials: 'same-origin' });
        if (!res.ok) throw new Error('Network response not ok');
        const data = await res.json(); // expect { items: [ ... ] }

        if (!Array.isArray(data.items) || data.items.length === 0) {
          cont.dataset.nomore = 'true';
          moreEl.style.display = 'none';
          // optional: show message
          const msg = document.createElement('p');
          msg.className = 'text-center text-gray-500';
          msg.textContent = 'Tidak ada lagi data.';
          moreEl.parentNode.insertBefore(msg, moreEl);
        } else {
          // append items
          data.items.forEach(item => {
            const html = renderItem(type, item);
            const wrap = document.createElement('div');
            wrap.innerHTML = html;
            // append children (section)
            Array.from(wrap.children).forEach(c => list.appendChild(c));
          });
          cont.dataset.page = String(page);
          moreEl.style.display = ''; // show load more again
        }
      } catch (err) {
        console.error('Load more error', err);
        moreEl.style.display = ''; // allow retry
      } finally {
        loading = false;
        loadingEl.style.display = 'none';
      }
    }

    function escapeHtml(s) {
      if (s === null || s === undefined) return '';
      return String(s)
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
    }

    function renderBadge(status) {
      const s = String(status || '').toLowerCase().trim();
      let classes = 'bg-gray-100 text-gray-800 border border-gray-200';
      if (['diterima','accepted'].includes(s)) classes = 'bg-green-100 text-green-800 border border-green-200';
      if (['pending','menunggu','proses'].includes(s)) classes = 'bg-yellow-100 text-yellow-800 border border-yellow-200';
      if (['ditolak','rejected'].includes(s)) classes = 'bg-red-100 text-red-800 border border-red-200';
      return `<span class="inline-block text-xs font-semibold rounded px-2 py-0.5 ${classes}">${escapeHtml(status)}</span>`;
    }

    function renderItem(type, item) {
      if (type === 'ruang') {
        return `
          <section class="history-item border border-primary rounded-lg p-4 mb-6 shadow-sm" data-id="${escapeHtml(item.id)}">
            <h3 class="text-gray-800 text-lg font-semibold mb-1">${escapeHtml(item.judul)}</h3>
            <p class="text-gray-600 text-sm leading-tight">
              Tanggal: ${escapeHtml(item.tanggal)}<br/>
              Waktu: ${escapeHtml(item.waktu_mulai)} - ${escapeHtml(item.waktu_selesai)}<br/>
              Ruangan: ${escapeHtml(item.lokasi)}<br/>
              Status: ${renderBadge(item.status)}
            </p>
            <div class="flex justify-end mt-3">
              <a href="${baseUrl.replace(/\/$/,'')}/ruang/detail/${encodeURIComponent(item.id)}" class="btn-primary btn-sm">Detail</a>
            </div>
          </section>
        `;
      } else {
        return `
          <section class="history-item border border-primary rounded-lg p-4 mb-6 shadow-sm" data-id="${escapeHtml(item.id)}">
            <h3 class="text-gray-800 text-lg font-semibold mb-1">${escapeHtml(item.judul)}</h3>
            <p class="text-gray-600 text-sm leading-tight">
              Tanggal Pergi: ${escapeHtml(item.tanggal_pergi)}<br/>
              Tanggal Pulang: ${escapeHtml(item.tanggal_pulang)}<br/>
              Tujuan: ${escapeHtml(item.tujuan)}<br/>
              Lama: ${escapeHtml(item.jumlah_hari)} hari<br/>
              Status: ${renderBadge(item.status)}
            </p>
            <div class="flex justify-end mt-3">
              <a href="${baseUrl.replace(/\/$/,'')}/user/car/detail/${encodeURIComponent(item.id)}" class="btn-primary btn-sm">Detail</a>
            </div>
          </section>
        `;
      }
    }

    // initial: make sure default style set
    setActive(active);
  })();
</script>

<?= $this->endSection() ?>
