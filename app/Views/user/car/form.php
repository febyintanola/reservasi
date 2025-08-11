<?= $this->extend('user/layouts/header') ?>

<?= $this->section('content') ?>

<body class="bg-white min-h-screen flex flex-col">

<!-- Main Content -->
<main class="flex-grow flex flex-col items-center px-4 py-8">
  <h2 class="font-semibold text-lg text-black mb-6">Form Pemesanan Mobil</h2>

  <form
    action="/car/save"
    method="POST"
    class="w-full max-w-md border border-gray-200 rounded-md p-6 space-y-4"
    autocomplete="off"
  >
    <input type="hidden" name="user_id" value="<?= esc($user_id) ?>" />

    <div>
      <label for="nama" class="block mb-1 text-black text-sm">Nama</label>
      <input id="nama" name="nama" type="text"
             class="w-full rounded border border-gray-300 px-3 py-2 placeholder:text-sm focus:ring-1 focus:ring-black focus:border-black" />
    </div>

    <!-- Pengikut -->
    <div>
      <label class="block mb-1 text-black text-sm">Pengikut</label>
      <div id="pengikut-nama-container" class="space-y-2 mb-2">
        <div class="flex items-center gap-2">
          <input type="text" name="nama_pengikut[]" required
                 class="flex-1 rounded border border-gray-300 px-3 py-2 placeholder:text-sm focus:ring-1 focus:ring-black focus:border-black" placeholder="Nama pengikut" />
          <button type="button"
                  class="hapus-pengikut text-red-500 text-sm px-2 py-1 border border-red-500 rounded hover:bg-red-100">
            Hapus
          </button>
        </div>
      </div>

      <div class="flex justify-center mt-2">
        <button type="button" id="btn-tambah-pengikut"
          class="flex items-center gap-1 bg-blue-500 text-white text-sm px-3 py-1 rounded-full hover:bg-blue-600 transition">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 4v16m8-8H4" />
          </svg>
          Tambah Pengikut
        </button>
      </div>
    </div>

    <div>
      <label for="tujuan" class="block mb-1 text-black text-sm">Tujuan</label>
      <input id="tujuan" name="tujuan" type="text" required
             class="w-full rounded border border-gray-300 px-3 py-2 placeholder:text-sm focus:ring-1 focus:ring-black focus:border-black" />
    </div>

    <div>
      <label for="keperluan" class="block mb-1 text-black text-sm">Keperluan</label>
      <select id="keperluan" name="keperluan" required
              class="w-full rounded border border-gray-300 px-3 py-2 text-sm focus:ring-1 focus:ring-black focus:border-black">
        <option selected disabled hidden>Pilih keperluan</option>
        <option value="Dinas">Dinas</option>
        <option value="Proyek">Proyek</option>
      </select>
    </div>

    <!-- Form khusus Proyek -->
    <div id="proyek-fields" style="display:none;">
      <div>
        <label for="nama_pekerjaan" class="block mb-1 text-black text-sm">Nama Pekerjaan</label>
        <input id="nama_pekerjaan" name="nama_pekerjaan" type="text"
               class="w-full rounded border border-gray-300 px-3 py-2 placeholder:text-sm focus:ring-1 focus:ring-black focus:border-black" />
      </div>
      <div>
        <label for="project_costing" class="block mb-1 text-black text-sm">Project Costing</label>
        <input id="project_costing" name="project_costing" type="text"
               class="w-full rounded border border-gray-300 px-3 py-2 placeholder:text-sm focus:ring-1 focus:ring-black focus:border-black" />
      </div>
      <div>
        <label for="task_number" class="block mb-1 text-black text-sm">Task Number</label>
        <input id="task_number" name="task_number" type="text"
               class="w-full rounded border border-gray-300 px-3 py-2 placeholder:text-sm focus:ring-1 focus:ring-black focus:border-black" />
      </div>
      <div>
        <label for="expenditure_type" class="block mb-1 text-black text-sm">Expenditure Type</label>
        <input id="expenditure_type" name="expenditure_type" type="text"
               class="w-full rounded border border-gray-300 px-3 py-2 placeholder:text-sm focus:ring-1 focus:ring-black focus:border-black" />
      </div>
      <div>
        <label for="expenditure_org" class="block mb-1 text-black text-sm">Expenditure Org</label>
        <input id="expenditure_org" name="expenditure_org" type="text"
               class="w-full rounded border border-gray-300 px-3 py-2 placeholder:text-sm focus:ring-1 focus:ring-black focus:border-black" />
      </div>
    </div>

    <div>
      <label for="tanggal_pergi" class="block mb-1 text-black text-sm">Tanggal Pergi</label>
      <input id="tanggal_pergi" name="tanggal_pergi" type="date" required
             class="w-full rounded border border-gray-300 px-3 py-2 placeholder:text-sm focus:ring-1 focus:ring-black focus:border-black" />
    </div>

    <div>
      <label for="tanggal_pulang" class="block mb-1 text-black text-sm">Tanggal Pulang</label>
      <input id="tanggal_pulang" name="tanggal_pulang" type="date" required
             class="w-full rounded border border-gray-300 px-3 py-2 placeholder:text-sm focus:ring-1 focus:ring-black focus:border-black" />
    </div>

    <button type="submit"
            class="w-full bg-black text-white rounded-md py-2 mt-4 text-sm font-medium">
      Kirim Permintaan
    </button>
  </form>
</main>

<script>
  // Toggle field proyek
  document.getElementById('keperluan').addEventListener('change', function () {
    const proyekFields = document.getElementById('proyek-fields');
    if (this.value === 'Proyek') {
      proyekFields.style.display = 'block';
      proyekFields.querySelectorAll('input').forEach(i => i.required = true);
    } else {
      proyekFields.style.display = 'none';
      proyekFields.querySelectorAll('input').forEach(i => {
        i.required = false;
        i.value = '';
      });
    }
  });

  // Pengikut logic
  const container = document.getElementById('pengikut-nama-container');
  const btnTambah = document.getElementById('btn-tambah-pengikut');

  function updateHapusButtons() {
    const hapusBtns = container.querySelectorAll('.hapus-pengikut');
    hapusBtns.forEach(btn => {
      btn.disabled = (container.children.length === 1);
      btn.classList.toggle('opacity-50', btn.disabled);
      btn.classList.toggle('cursor-not-allowed', btn.disabled);
    });
  }

  btnTambah.addEventListener('click', () => {
    const div = document.createElement('div');
    div.className = 'flex items-center gap-2';
    div.innerHTML = `
      <input type="text" name="nama_pengikut[]" required
             class="flex-1 rounded border border-gray-300 px-3 py-2 placeholder:text-sm focus:ring-1 focus:ring-black focus:border-black" placeholder="Nama pengikut" />
      <button type="button"
              class="hapus-pengikut text-red-500 text-sm px-2 py-1 border border-red-500 rounded hover:bg-red-100">
        Hapus
      </button>
    `;
    container.appendChild(div);
    updateHapusButtons();
  });

  container.addEventListener('click', function (e) {
    if (e.target.classList.contains('hapus-pengikut')) {
      const item = e.target.closest('.flex');
      if (container.children.length > 1) {
        container.removeChild(item);
      }
      updateHapusButtons();
    }
  });

  updateHapusButtons();
</script>

</body>

<?= $this->endSection() ?>
