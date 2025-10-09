<?= $this->extend('user/layouts/header') ?>

<?= $this->section('content') ?>

  <main class="max-w-7xl mx-auto px-4 py-6">
    <h1 class="text-center font-semibold text-2xl text-slate-800 mb-6">Daftar Ruang Rapat</h1>

    <!-- Form Pencarian -->
    <form class="card max-w-xl mx-auto mb-6 p-5" id="checkForm" onsubmit="event.preventDefault(); checkAvailability();">
      <div class="grid gap-4">
        <div>
          <label class="block text-sm mb-1 text-slate-700">Hari/Tanggal</label>
          <input type="date" name="tanggal" id="tanggal" class="w-full rounded-md border border-slate-300 px-3 py-2" required>
        </div>
        <div class="flex flex-col sm:flex-row gap-4">
          <div class="flex-1">
            <label class="block text-sm mb-1 text-slate-700">Jam Mulai</label>
            <input type="time" name="jam_mulai" id="jam_mulai" class="w-full rounded-md border border-slate-300 px-3 py-2" required>
          </div>
          <div class="flex-1">
            <label class="block text-sm mb-1 text-slate-700">Jam Selesai</label>
            <input type="time" name="jam_selesai" id="jam_selesai" class="w-full rounded-md border border-slate-300 px-3 py-2" required>
          </div>
        </div>
      </div>
      <div class="flex justify-end mt-4">
        <button type="submit" class="btn-primary">
          <i class="fas fa-search"></i>
          Cari
        </button>
        <button type="button" id="cariSlotBtn" class="btn-primary">
          Slot Terdekat
        </button>
      </div>
  <div id="slotTerdekatInfo" class="text-sm text-primary-brand mt-2 text-center"></div>
    </form>
    
  <section id="roomList" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
      
    </section>
  </main>

  <script>
    const tanggalInput = document.getElementById('tanggal');
    const jamMulaiInput = document.getElementById('jam_mulai');
    const jamSelesaiInput = document.getElementById('jam_selesai');
    const roomList = document.getElementById('roomList');
    const slotTerdekatInfo = document.getElementById('slotTerdekatInfo');

    function checkAvailability() {
      const tanggal = tanggalInput.value;
      const jamMulai = jamMulaiInput.value;
      const jamSelesai = jamSelesaiInput.value;

      if (!tanggal || !jamMulai || !jamSelesai) return;

      fetch(`/ruang/ajax-check?tanggal=${tanggal}&jam_mulai=${jamMulai}&jam_selesai=${jamSelesai}`)
        .then(response => response.json())
        .then(data => {
          roomList.innerHTML = '';

          if (data.length === 0) {
            roomList.innerHTML = '<p class="text-center text-slate-500 col-span-3">Tidak ada data ruangan.</p>';
            return;
          }

          data.forEach(room => {
            const button = room.tersedia
              ? `<a href="/ruang/booking-form?room=${room.id}&tanggal=${tanggal}&jam_mulai=${jamMulai}&jam_selesai=${jamSelesai}" class="btn-primary w-full justify-center mt-2">Pilih Ruang</a>`
              : `<div class="badge w-full justify-center mt-2 text-red-600 bg-red-100 border border-red-300">Tidak Tersedia</div>`;

            const imageUrl = room.ruangrapat_url || 'https://via.placeholder.com/400x150?text=No+Image';
            const jenisRuang = room.jenis ? room.jenis : 'Jenis tidak diketahui';

            const card = `
              <div class="card p-4">
                <img src="${imageUrl}" alt="Foto Ruang Rapat" class="w-full h-32 object-cover rounded-md mb-3" />
                <h4 class="font-semibold text-slate-800 mb-1">${room.nama_ruangan}</h4>
                <p class="text-sm text-slate-500">Lokasi: ${room.lokasi}</p>
                <p class="text-sm text-slate-500">Kapasitas: ${room.kapasitas} orang</p>
                <p class="text-sm font-semibold text-slate-700 mb-1">Jenis: ${jenisRuang}</p>
                ${button}
              </div>
            `;

            roomList.innerHTML += card;
          });
        });
    }

    // Tambahkan fungsi untuk cari slot terdekat
    function cariSlotTerdekat() {
      const tanggal = tanggalInput.value;
      const jamMulai = jamMulaiInput.value;
      const jamSelesai = jamSelesaiInput.value;
      const infoEl = document.getElementById('slotTerdekatInfo');

  infoEl.classList.remove('text-red-700');
  infoEl.classList.add('text-primary-brand');

      if (!tanggal || !jamMulai || !jamSelesai) {
  infoEl.classList.remove('text-primary-brand');
        infoEl.classList.add('text-red-700');
        infoEl.textContent = 'Isi tanggal, jam mulai, dan jam selesai dulu.';
        return;
      }

      infoEl.textContent = 'Mencari slot terdekat...';

      // Pakai route yang ada
      fetch(`/room/findNextAvailableSlotAnyRoom?tanggal=${encodeURIComponent(tanggal)}&jam_mulai=${encodeURIComponent(jamMulai)}&jam_selesai=${encodeURIComponent(jamSelesai)}`)
        .then(r => r.json())
        .then(res => {
          // Cukup cek slots, jangan bergantung pada res.available
          if (Array.isArray(res.slots) && res.slots.length) {
            const list = res.slots.map(s => `• ${s.room_name}: ${s.start} - ${s.end}`).join('<br>');
            slotTerdekatInfo.classList.remove('text-red-700');
            slotTerdekatInfo.classList.add('text-primary-brand');
            slotTerdekatInfo.innerHTML = `<strong>Rekomendasi slot:</strong><br>${list}`;
          } else {
            slotTerdekatInfo.classList.remove('text-primary-brand');
            slotTerdekatInfo.classList.add('text-red-700');
            slotTerdekatInfo.textContent = res.message || 'Tidak ditemukan slot alternatif.';
          }
        })
        .catch(() => {
          slotTerdekatInfo.classList.remove('text-primary-brand');
          slotTerdekatInfo.classList.add('text-red-700');
          slotTerdekatInfo.textContent = 'Gagal mengambil data. Coba lagi.';
        });
    }

    /*tanggalInput.addEventListener('change', checkAvailability);
    jamMulaiInput.addEventListener('change', checkAvailability);
    jamSelesaiInput.addEventListener('change', checkAvailability);*/

    // Ganti: tombol "Cari Terserdia" panggil cariSlotTerdekat, bukan checkAvailability
    document.getElementById('cariSlotBtn').addEventListener('click', cariSlotTerdekat);
  </script>


<?= $this->endSection() ?>
