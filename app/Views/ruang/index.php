<?= $this->extend('layouts/header') ?>

<?= $this->section('content') ?>

<body class="bg-white font-sans">

  <main class="max-w-7xl mx-auto px-4 py-6">
    <h1 class="text-center font-semibold text-xl mb-6">Daftar Ruang Rapat</h1>

    <!-- Form Pencarian -->
    <form class="max-w-md mx-auto mb-6" id="checkForm" onsubmit="event.preventDefault(); checkAvailability();">
      <label class="block text-sm mb-1 text-black">Hari/Tanggal</label>
      <input type="date" name="tanggal" id="tanggal" class="form-control w-full rounded-full border border-gray-300 px-4 py-2 mb-3" required>

      <label class="block text-sm mb-1 text-black">Jam</label>
      <div class="flex space-x-2 mb-3">
        <input type="time" name="jam_mulai" id="jam_mulai" class="form-control w-1/2 rounded-full border border-gray-300 px-4 py-2" required>
        <input type="time" name="jam_selesai" id="jam_selesai" class="form-control w-1/2 rounded-full border border-gray-300 px-4 py-2" required>
      </div>

      <!-- Tombol Cari -->
      <div class="flex justify-center mt-2">
        <button type="submit" class="flex items-center gap-1 bg-blue-500 text-white text-sm px-3 py-1 rounded-full hover:bg-blue-600 transition">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M16 10a6 6 0 11-12 0 6 6 0 0112 0z" />
          </svg>
          Cari
        </button>
      </div>
    </form>
    
    <section id="roomList" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
      
    </section>
  </main>

  <script>
    const tanggalInput = document.getElementById('tanggal');
    const jamMulaiInput = document.getElementById('jam_mulai');
    const jamSelesaiInput = document.getElementById('jam_selesai');
    const roomList = document.getElementById('roomList');

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
            roomList.innerHTML = '<p class="text-center text-gray-500 col-span-3">Tidak ada data ruangan.</p>';
            return;
          }

          data.forEach(room => {
            const button = room.tersedia
              ? `<a href="/ruang/booking-form?room=${room.id}&tanggal=${tanggal}&jam_mulai=${jamMulai}&jam_selesai=${jamSelesai}" class="block bg-green-500 text-white text-center rounded-full mt-2 py-1 hover:bg-green-600">Pilih Ruang</a>`
              : `<div class="block bg-gray-300 text-gray-500 text-center rounded-full mt-2 py-1 cursor-not-allowed">Tidak Tersedia</div>`;

            const imageUrl = room.ruangrapat_url || 'https://via.placeholder.com/400x150?text=No+Image';
            const jenisRuang = room.jenis ? room.jenis : 'Jenis tidak diketahui';

            const card = `
              <div class="border border-gray-200 rounded-xl p-4 shadow-sm bg-white">
                <img src="${imageUrl}" alt="Foto Ruang Rapat" class="w-full h-32 object-cover rounded-md mb-2" />
                <h4 class="font-semibold mb-1">${room.nama_ruangan}</h4>
                <p class="text-sm text-gray-500">Lokasi: ${room.lokasi}</p>
                <p class="text-sm text-gray-500">Kapasitas: ${room.kapasitas} orang</p>
                <p class="text-sm font-semibold text-gray-700 mb-1">Jenis: ${jenisRuang}</p>
                ${button}
              </div>
            `;

            roomList.innerHTML += card;
          });
        });
    }
    /*tanggalInput.addEventListener('change', checkAvailability);
    jamMulaiInput.addEventListener('change', checkAvailability);
    jamSelesaiInput.addEventListener('change', checkAvailability);*/
  </script>


<?= $this->endSection() ?>
