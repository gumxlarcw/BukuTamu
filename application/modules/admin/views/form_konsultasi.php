<?php $this->load->view('layouts/admin_header'); ?>
<?php $this->load->helper('konsultasi'); ?>

<h3>Form Konsultasi Pengunjung</h3>

<!-- Informasi Pengunjung -->
<div class="card mb-4">
    <div class="card-body">
        <h5>Data Pengunjung</h5>
        <p><strong>Nama:</strong> <?= $pengunjung->nama ?></p>
        <p><strong>Email:</strong> <?= $pengunjung->email ?></p>
        <p><strong>No HP:</strong> <?= $pengunjung->notel ?></p>
        <p><strong>Instansi:</strong> <?= $pengunjung->nama_instansi ?></p>
        <p><strong>Tanggal Datang:</strong> <?= $kunjungan->date_visit ?></p>
    </div>
</div>

<?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
<?php endif; ?>



<!-- Tombol Trigger Modal -->
<div class="text-end my-4">
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalKebutuhanData">
        Tambah Kebutuhan Data
    </button>
</div>

<!-- Tabel Kebutuhan Data -->
<h5><strong>BLOK III. Kebutuhan Data</strong></h5>
<small class="text-muted">Isikan data kebutuhan di bawah:</small>

<div class="table-responsive mt-3">
    <table class="table table-bordered table-striped table-sm" id="tabel-kebutuhan">
        <thead class="text-center table-dark">
            <tr>
                <th>#</th>
                <th>Rincian Data</th>
                <th>Wilayah Data</th>
                <th>Tahun Data</th>
                <th>Level Data</th>
                <th>Periode Data</th>
                <th>Data Diperoleh</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <tr><td colspan="9" class="text-center text-muted">Belum ada data yang ditambahkan.</td></tr>
        </tbody>
    </table>
</div>

<!-- Modal Tambah Kebutuhan Data -->
<div class="modal fade" id="modalKebutuhanData" tabindex="-1" aria-labelledby="modalKebutuhanLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalKebutuhanLabel">Tambah Kebutuhan Data</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
      </div>
      <div class="modal-body">
        <form id="formTambahKebutuhanData">
          <div class="mb-3">
              <label>1. Rincian Data</label>
              <input type="text" id="rincian_data" name="rincian_data" class="form-control" required>
          </div>
          <div class="mb-3">
              <label>2. Wilayah Jenis Data</label>
              <input type="text" id="wilayah_data" name="wilayah_data" class="form-control" required>
          </div>
          <div class="mb-3">
              <label>3. Tahun Jenis Data</label>
              <div class="d-flex gap-2">
                  <input type="number" id="tahun_awal" name="tahun_awal" class="form-control" required>
                  <span class="mx-2 text-center">sampai dengan</span>
                  <input type="number" id="tahun_akhir" name="tahun_akhir" class="form-control" required>
              </div>
          </div>
          <div class="mb-3">
              <label>4. Level Data</label>
              <select id="level_data" name="level_data" class="form-control" required>
                  <option value="" disabled selected>Pilih salah satu</option>
                  <option value="1">Nasional</option>
                  <option value="2">Provinsi</option>
                  <option value="3">Kabupaten/Kota</option>
                  <option value="4">Kecamatan</option>
                  <option value="5">Desa/Kelurahan</option>
                  <option value="6">Individu</option>
                  <option value="7">Lainnya</option>
              </select>
          </div>
          <div class="mb-3">
              <label>5. Periode Data</label>
              <select id="periode_data" name="periode_data" class="form-control" required>
                  <option value="" disabled selected>Pilih salah satu</option>
                  <option value="1">Sepuluh Tahunan</option>
                  <option value="2">Lima Tahunan</option>
                  <option value="3">Tiga Tahunan</option>
                  <option value="4">Tahunan</option>
                  <option value="5">Semesteran</option>
                  <option value="6">Triwulanan</option>
                  <option value="7">Bulanan</option>
                  <option value="8">Mingguan</option>
                  <option value="9">Harian</option>
                  <option value="10">Lainnya</option>
              </select>
          </div>
          <div class="mb-3">
              <label>6. Apakah data sudah diperoleh?</label>
              <select id="status_data" name="status_data" class="form-control" required>
                  <option value="" disabled selected>Pilih salah satu</option>
                  <option value="1">Ya, sesuai</option>
                  <option value="2">Ya, tidak sesuai</option>
                  <option value="3">Tidak diperoleh</option>
                  <option value="4">Belum Diperoleh</option>
              </select>
          </div>
          <div id="tambahan-publikasi" style="display: none;">
            <div class="mb-3">
                <label>7. Jenis Publikasi/Sumber Data</label>
                <select id="jenis_publikasi" name="jenis_publikasi" class="form-control" required>
                    <option value="" disabled selected>Pilih salah satu</option>
                    <option value="1">Publikasi</option>
                    <option value="2">Data Mikro</option>
                    <option value="3">Peta</option>
                    <option value="4">Tabulasi Data</option>
                    <option value="5">Tabel di Website</option>
                </select>
                <small>Pilih salah satu:<br>1: Publikasi, 2: Data Mikro, 3: Peta, 4: Tabulasi Data, 5: Tabel di Website</small>
            </div>

            <div class="mb-3">
                <label>8. Judul Publikasi/Sumber Data</label>
                <input type="text" id="judul_publikasi" name="judul_publikasi" class="form-control">
                <small>Tuliskan judul lengkap, misal: Provinsi Jambi Dalam Angka 2019.</small>
            </div>

            <div class="mb-3">
                <label>9. Tahun Publikasi</label>
                <input type="number" id="tahun_publikasi" name="tahun_publikasi" class="form-control">
            </div>

            <div class="mb-3">
                <label>10. Apakah data ini digunakan untuk perencanaan, monitoring, dan evaluasi pembangunan nasional?</label>
                <select id="digunakan_nasional" name="digunakan_nasional" class="form-control">
                    <option value="" disabled selected>Pilih</option>
                    <option value="1">Ya</option>
                    <option value="2">Tidak</option>
                </select>
                
            </div>

            <div class="mb-3">
                <label>11. Kualitas Data</label>
                <div id="rating-stars" class="d-flex gap-1">
                    <!-- Bintang akan di-generate oleh JS -->
                </div>
                <input type="hidden" id="kualitas" name="kualitas" required>
            </div>
        </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" id="tambahKeTabel">Tambah ke Tabel</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
      </div>
    </div>
  </div>
</div>

<!-- Form Konsultasi -->
<form action="<?= site_url('admin/simpan_konsultasi/' . $kunjungan->id_kunjungan) ?>" method="post">
    <div class="mb-3">
        <label>Ringkasan Hasil Konsultasi</label>
        <textarea name="hasil_konsultasi" class="form-control" rows="4" required></textarea>
    </div>
    
    <div id="hidden-kebutuhan-container"></div>

    <div class="text-center mt-4">
        <button class="btn btn-success">Simpan & Minta Evaluasi</button>
        <a href="<?= site_url('admin/dashboard') ?>" class="btn btn-secondary">Batal</a>
    </div>
</form>

<?php $this->load->view('layouts/admin_footer'); ?>

<!-- JavaScript untuk menambahkan baris ke tabel -->
<script>
let editingRowIndex = null;

document.addEventListener('DOMContentLoaded', function () {
    const statusSelect = document.getElementById('status_data');
    const tambahanPublikasi = document.getElementById('tambahan-publikasi');

    document.getElementById('tambahKeTabel').addEventListener('click', function () {
        const rincian = document.getElementById('rincian_data').value;
        const wilayah = document.getElementById('wilayah_data').value;
        const tahun_awal = document.getElementById('tahun_awal').value;
        const tahun_akhir = document.getElementById('tahun_akhir').value;
        const levelSelect = document.getElementById('level_data');
        const periodeSelect = document.getElementById('periode_data');
        const statusSelect = document.getElementById('status_data');

        const level = levelSelect.options[levelSelect.selectedIndex].text;
        const periode = periodeSelect.options[periodeSelect.selectedIndex].text;
        const status = statusSelect.options[statusSelect.selectedIndex].text;

        const levelVal = levelSelect.value;
        const periodeVal = periodeSelect.value;
        const statusVal = statusSelect.value;

        const jenis_publikasi = document.getElementById('jenis_publikasi').value;
        const judul_publikasi = document.getElementById('judul_publikasi').value;
        const tahun_publikasi = document.getElementById('tahun_publikasi').value;
        const digunakan_nasional = document.getElementById('digunakan_nasional').value;
        const kualitas = document.getElementById('kualitas').value;

        if (!rincian || !wilayah || !tahun_awal || !tahun_akhir || !levelVal || !periodeVal || !statusVal) {
            alert("Harap lengkapi semua field utama.");
            return;
        }

        if ((statusVal === '1' || statusVal === '2') && (
            !jenis_publikasi || !judul_publikasi || !tahun_publikasi || !digunakan_nasional || !kualitas
        )) {
            alert("Harap lengkapi semua pertanyaan tambahan (7–11).");
            return;
        }

        const table = document.getElementById('tabel-kebutuhan').getElementsByTagName('tbody')[0];
        const hiddenContainer = document.getElementById('hidden-kebutuhan-container');

        if (table.rows.length === 1 && table.rows[0].cells[0].colSpan === 9) {
            table.deleteRow(0);
        }

        // Update atau insert baris
        let row;
        if (editingRowIndex !== null) {
            row = table.rows[editingRowIndex];
            row.innerHTML = '';
            hiddenContainer.children[editingRowIndex].remove(); // Hapus input hidden lama
        } else {
            row = table.insertRow();
        }

        const idx = row.rowIndex + 1;

        row.innerHTML = `
            <td class="text-center">${idx}</td>
            <td>${rincian}</td>
            <td>${wilayah}</td>
            <td>${tahun_awal} - ${tahun_akhir}</td>
            <td>${level}</td>
            <td>${periode}</td>
            <td>${status}</td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-warning me-1" onclick="editBaris(this)">Edit</button>
                <button type="button" class="btn btn-sm btn-danger" onclick="hapusBaris(this)">Hapus</button>
            </td>
        `;

        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'kebutuhan_data[]';
        input.value = JSON.stringify({
            rincian_data: rincian,
            wilayah_data: wilayah,
            tahun_awal: tahun_awal,
            tahun_akhir: tahun_akhir,
            level_data: levelVal,
            periode_data: periodeVal,
            status_data: statusVal,
            jenis_publikasi: jenis_publikasi,
            judul_publikasi: judul_publikasi,
            tahun_publikasi: tahun_publikasi,
            digunakan_nasional: digunakan_nasional,
            kualitas: kualitas
        });
        if (editingRowIndex !== null) {
            hiddenContainer.insertBefore(input, hiddenContainer.children[editingRowIndex]);
        } else {
            hiddenContainer.appendChild(input);
        }

        editingRowIndex = null;

        document.getElementById('formTambahKebutuhanData').reset();
        if (tambahanPublikasi) tambahanPublikasi.style.display = 'none';
        document.getElementById('kualitas').value = '';
        document.querySelectorAll('#rating-stars span').forEach(el => el.style.color = '#ccc');

        const modal = bootstrap.Modal.getInstance(document.getElementById('modalKebutuhanData'));
        modal.hide();
        updateNomorUrut();

    });

    // Tampilkan isian tambahan bila status sesuai/tidak sesuai
    statusSelect.addEventListener('change', function () {
        const val = this.value;
        if (val === '1' || val === '2') {
            tambahanPublikasi.style.display = 'block';
        } else {
            tambahanPublikasi.style.display = 'none';
            tambahanPublikasi.querySelectorAll('input, select').forEach(el => el.value = '');
            document.getElementById('kualitas').value = '';
            document.querySelectorAll('#rating-stars span').forEach(el => el.style.color = '#ccc');
        }
    });

    generateRatingStars();
});

function hapusBaris(btn) {
    const row = btn.closest('tr');
    const index = row.rowIndex - 1;
    row.remove();

    const hiddenInputs = document.querySelectorAll('#hidden-kebutuhan-container input');
    if (hiddenInputs[index]) hiddenInputs[index].remove();

    // Update nomor baris
    const rows = document.querySelectorAll('#tabel-kebutuhan tbody tr');
    rows.forEach((r, i) => {
        r.cells[0].innerText = i + 1;
    });

    editingRowIndex = null;
    updateNomorUrut();

}

function editBaris(btn) {
    const row = btn.closest('tr');
    const cells = row.getElementsByTagName('td');

    const rincian = cells[1].textContent.trim();
    const wilayah = cells[2].textContent.trim();
    const tahun = cells[3].textContent.trim().split(' - ');
    const level = cells[4].textContent.trim();
    const periode = cells[5].textContent.trim();
    const status = cells[6].textContent.trim();

    document.getElementById('rincian_data').value = rincian;
    document.getElementById('wilayah_data').value = wilayah;
    document.getElementById('tahun_awal').value = tahun[0];
    document.getElementById('tahun_akhir').value = tahun[1];

    selectDropdownByText('level_data', level);
    selectDropdownByText('periode_data', periode);
    selectDropdownByText('status_data', status);

    // Ambil data dari input hidden sesuai baris
    const hiddenInputs = document.querySelectorAll('#hidden-kebutuhan-container input');
    const index = row.rowIndex - 1;
    const jsonData = JSON.parse(hiddenInputs[index].value);

    // Isi kembali field tambahan
    document.getElementById('jenis_publikasi').value = jsonData.jenis_publikasi || '';
    document.getElementById('judul_publikasi').value = jsonData.judul_publikasi || '';
    document.getElementById('tahun_publikasi').value = jsonData.tahun_publikasi || '';
    document.getElementById('digunakan_nasional').value = jsonData.digunakan_nasional || '';
    document.getElementById('kualitas').value = jsonData.kualitas || '';
    if (jsonData.kualitas) setRating(jsonData.kualitas);

    // Tampilkan field tambahan jika status sesuai
    document.getElementById('status_data').dispatchEvent(new Event('change'));

    const modal = new bootstrap.Modal(document.getElementById('modalKebutuhanData'));
    modal.show();

    editingRowIndex = index;
    updateNomorUrut();
}

function updateNomorUrut() {
    const rows = document.querySelectorAll('#tabel-kebutuhan tbody tr');
    rows.forEach((row, i) => {
        const nomorCell = row.querySelector('td:first-child');
        if (nomorCell) nomorCell.textContent = i + 1;
    });
} 

function selectDropdownByText(id, text) {
    const select = document.getElementById(id);
    for (let option of select.options) {
        if (option.text.trim() === text.trim()) {
            select.value = option.value;
            break;
        }
    }
}

function generateRatingStars() {
    const ratingContainer = document.getElementById('rating-stars');
    ratingContainer.innerHTML = '';
    for (let i = 1; i <= 10; i++) {
        const star = document.createElement('span');
        star.innerHTML = '&#9733;';
        star.dataset.value = i;
        star.style.fontSize = '24px';
        star.style.cursor = 'pointer';
        star.style.color = '#ccc';
        star.addEventListener('click', function () {
            setRating(i);
        });
        ratingContainer.appendChild(star);
    }
}

function setRating(rating) {
    const stars = document.querySelectorAll('#rating-stars span');
    stars.forEach((star, index) => {
        star.style.color = (index < rating) ? '#ffc107' : '#ccc';
    });
    document.getElementById('kualitas').value = rating;
}
</script>


