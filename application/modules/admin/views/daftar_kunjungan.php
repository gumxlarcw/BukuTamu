<?php $this->load->view('layouts/admin_header'); ?>

<h3>Daftar Semua Kunjungan</h3>

<form method="get" class="row g-2 mb-3">
    <div class="col-md-4">
        <input type="text" name="q" value="<?= $this->input->get('q') ?>" class="form-control" placeholder="Cari nama, instansi, layanan, status...">
    </div>
    <div class="col-md-2">
        <select name="layanan" class="form-select">
            <option value="">-- Jenis Layanan --</option>
            <?php
                $listLayanan = [
                    'Perpustakaan',
                    'Konsultasi Statistik',
                    'Rekomendasi Kegiatan Statistik',
                    'Penjualan Produk Statistik',
                    'Keperluan Pimpinan',
                    'Lainnya'
                ];
                foreach ($listLayanan as $layanan) {
                    $selected = $this->input->get('layanan') === $layanan ? 'selected' : '';
                    echo "<option value=\"$layanan\" $selected>$layanan</option>";
                }
            ?>
        </select>
    </div>
    <div class="col-md-2">
        <select name="tahun" class="form-select">
            <option value="">-- Tahun --</option>
            <?php
                $now = date('Y');
                for ($y = $now; $y >= $now - 5; $y--) {
                    $selected = $this->input->get('tahun') == $y ? 'selected' : '';
                    echo "<option value=\"$y\" $selected>$y</option>";
                }
            ?>
        </select>
    </div>
    <div class="col-md-2">
        <select name="bulan" class="form-select">
            <option value="">-- Bulan --</option>
            <?php
                for ($m = 1; $m <= 12; $m++) {
                    $val = str_pad($m, 2, '0', STR_PAD_LEFT);
                    $selected = $this->input->get('bulan') == $val ? 'selected' : '';
                    echo "<option value=\"$val\" $selected>" . date('F', mktime(0, 0, 0, $m, 1)) . "</option>";
                }
            ?>
        </select>
    </div>
    <div class="col-md-2">
        <button type="submit" class="btn btn-primary w-100">Terapkan</button>
    </div>
</form>

<table class="table table-bordered table-hover mt-4">
    <thead class="table-primary">
        <tr>
            <th>No</th>    
            <th>Timestamp</th>    
            <th>Nama</th>
            <th>Instansi</th>
            <th>Jenis Layanan</th> <!-- ✅ Tambahkan ini -->
            <th>Status</th>
            <th>Keterangan</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($kunjungan)): ?>
            <?php $no = 1; foreach ($kunjungan as $k): ?>
                <?php
                    $status = strtolower(trim($k->status));
                    if ($status === 'selesai') {
                        $badge_class = 'success';
                    } elseif ($status === 'proses') {
                        $badge_class = 'warning';
                    } elseif ($status === 'menunggu_evaluasi') {
                        $badge_class = 'info';
                    } elseif ($status === 'antri') {
                        $badge_class = 'dark';
                    } elseif (!$status) {
                        $badge_class = 'secondary';
                        $status = 'tidak diketahui';
                    } else {
                        $badge_class = 'secondary';
                    }

                    $status_label = ucfirst($status);
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= date('Y-m-d H:i:s', strtotime($k->date_visit)) ?></td>
                    <td><?= $k->nama ?></td>
                    <td><?= $k->nama_instansi ?></td>
                    <td><?= $k->jenis_layanan ?></td> <!-- ✅ Tambahkan ini -->
                    <td><span class="badge bg-<?= $badge_class ?>"><?= $status_label ?></span></td>
                    <td><?= $k->hasil_konsultasi ? $k->hasil_konsultasi : '-' ?></td>
                    <td>
                        <button class="btn btn-sm btn-primary mb-1" onclick="loadDetail(<?= $k->id_kunjungan ?>)">Detail</button>

                        <?php if (strtolower(trim($k->status)) !== 'selesai'): ?>
                            <button class="btn btn-sm btn-warning mb-1" onclick="openEditModal(<?= $k->id_kunjungan ?>, '<?= htmlspecialchars($k->jenis_layanan, ENT_QUOTES) ?>')">
                                Edit Layanan
                            </button>
                        <?php endif; ?>

                        <?php if (empty($k->hasil_konsultasi)): ?>
                            <button class="btn btn-sm btn-outline-info mb-1" onclick="openRingkasanModal(<?= $k->id_kunjungan ?>)">Isi Ringkasan</button>
                        <?php endif; ?>

                        <?php if ($status === 'menunggu_evaluasi'): ?>
                            <button class="btn btn-outline-secondary btn-sm" onclick="copyLink('<?= site_url('evaluasi/isi/' . $k->id_kunjungan) ?>')">
                                Copy Link Evaluasi
                            </button>
                        <?php endif; ?>

                        <?php if (strtolower(trim($k->status)) !== 'selesai'): ?>
                            <button class="btn btn-sm btn-secondary mb-1" onclick="openEditStatusModal(<?= $k->id_kunjungan ?>, '<?= $k->status ?>')">
                                Edit Status
                            </button>
                        <?php endif; ?>

                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="7" class="text-center">Belum ada kunjungan tercatat.</td></tr>
        <?php endif; ?>
    </tbody>
</table>
<!-- Modal -->
<div class="modal fade" id="detailModal" tabindex="-1" aria-labelledby="detailModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content" id="modalContent">
      <!-- Konten detail akan dimuat via AJAX di sini -->
    </div>
  </div>
</div>

<div class="modal fade" id="editLayananModal" tabindex="-1" aria-labelledby="editLayananModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" action="<?= site_url('admin/update_jenis_layanan') ?>">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Jenis Layanan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_kunjungan" id="edit_id_kunjungan">
          <div class="mb-3">
            <label for="jenis_layanan" class="form-label">Jenis Layanan</label>
            <select class="form-select" name="jenis_layanan" id="edit_jenis_layanan" required>
                <option value="">-- Pilih Jenis Layanan --</option>
                <option value="Perpustakaan">Perpustakaan</option>
                <option value="Konsultasi Statistik">Konsultasi Statistik</option>
                <option value="Rekomendasi Kegiatan Statistik">Rekomendasi Kegiatan Statistik</option>
                <option value="Penjualan Produk Statistik">Penjualan Produk Statistik</option>
                <option value="Keperluan Pimpinan">Keperluan Pimpinan</option>
                <option value="Lainnya">Lainnya</option>
            </select>
        </div>
        
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Simpan</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        </div>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="ringkasanModal" tabindex="-1" aria-labelledby="ringkasanModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" action="<?= site_url('admin/update_ringkasan') ?>">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Isi Ringkasan Kunjungan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_kunjungan" id="ringkasan_id_kunjungan">
          <div class="mb-3">
            <label for="ringkasan" class="form-label">Ringkasan</label>
            <textarea name="ringkasan" class="form-control" id="ringkasan" rows="4" required></textarea>
          </div>
        </div>
        

        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Simpan</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        </div>
        </div>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="editStatusModal" tabindex="-1" aria-labelledby="editStatusModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="post" action="<?= site_url('admin/update_status_kunjungan') ?>">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Edit Status Kunjungan</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="id_kunjungan" id="edit_status_id_kunjungan">
          <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select class="form-select" name="status" id="edit_status_select" required>
              <option value="">-- Pilih Status --</option>
              <option value="antri">Antri</option>
              <option value="proses">Proses</option>
              <option value="menunggu_evaluasi">Menunggu Evaluasi</option>
              <option value="selesai">Selesai</option>
            </select>
          </div>
        </div>
        
        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Simpan</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        </div>
        </div>
      </div>
    </form>
  </div>
</div>

<script>
function loadDetail(id) {
    fetch("<?= site_url('admin/detail_kunjungan/') ?>" + id)
        .then(res => res.text())
        .then(html => {
            document.getElementById("modalContent").innerHTML = html;
            new bootstrap.Modal(document.getElementById('detailModal')).show();
        })
        .catch(err => alert("Gagal memuat detail: " + err));
}

function copyLink(link) {
    navigator.clipboard.writeText(link).then(() => {
        alert("Link evaluasi berhasil disalin!");
    }).catch(err => {
        alert("Gagal menyalin link: " + err);
    });
}

function openEditModal(id, layanan) {
    document.getElementById('edit_id_kunjungan').value = id;
    document.getElementById('edit_jenis_layanan').value = layanan;
    new bootstrap.Modal(document.getElementById('editLayananModal')).show();
}

function openRingkasanModal(id) {
    document.getElementById('ringkasan_id_kunjungan').value = id;
    document.getElementById('ringkasan').value = '';
    new bootstrap.Modal(document.getElementById('ringkasanModal')).show();
}

function openEditStatusModal(id, status) {
    document.getElementById('edit_status_id_kunjungan').value = id;
    document.getElementById('edit_status_select').value = status;
    new bootstrap.Modal(document.getElementById('editStatusModal')).show();
}

</script>

<?php $this->load->view('layouts/admin_footer'); ?>
