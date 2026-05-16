<?php $this->load->view('layouts/admin_header'); ?>

<div class="d-flex flex-column flex-lg-row gap-2 justify-content-between align-items-lg-center mb-3">
    <div>
        <h3 class="mb-1">Tambah Kunjungan Manual</h3>
        <div class="small text-body-secondary">Tambahkan tamu yang datang secara manual untuk melengkapi data kunjungan.</div>
    </div>
    <a href="<?= site_url('admin/daftar_kunjungan') ?>" class="btn btn-sm btn-outline-secondary">Kembali</a>
</div>

<?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($this->session->flashdata('error'), ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($this->session->flashdata('success'), ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="post" action="<?= site_url('admin/simpan_kunjungan_manual') ?>">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">

            <div class="row g-3">
                <div class="col-lg-12">
                    <label for="id_user" class="form-label">Pilih Tamu <small class="text-danger">*</small></label>
                    <select class="form-select" name="id_user" id="id_user" required>
                        <option value="">-- Pilih Tamu --</option>
                        <?php foreach ($semua_tamu as $t): ?>
                            <option value="<?= $t->id_user ?>">
                                <?= htmlspecialchars($t->nama, ENT_QUOTES, 'UTF-8') ?> (<?= htmlspecialchars($t->nama_instansi, ENT_QUOTES, 'UTF-8') ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-lg-12">
                    <label for="jenis_layanan" class="form-label">Jenis Layanan <small class="text-danger">*</small></label>
                    <select class="form-select" name="jenis_layanan" id="jenis_layanan" required>
                        <option value="">-- Pilih Jenis Layanan --</option>
                        <option value="Perpustakaan">Perpustakaan</option>
                        <option value="Konsultasi Statistik">Konsultasi Statistik</option>
                        <option value="Rekomendasi Kegiatan Statistik">Rekomendasi Kegiatan Statistik</option>
                        <option value="Penjualan Produk Statistik">Penjualan Produk Statistik</option>
                        <option value="Keperluan Pimpinan">Keperluan Pimpinan</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="ti-save"></i> Simpan Kunjungan
                </button>
                <a href="<?= site_url('admin/daftar_kunjungan') ?>" class="btn btn-secondary">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<div class="mt-4">
    <div class="d-flex align-items-center gap-2 mb-3">
        <h5 class="mb-0">Tamu yang Sudah Datang Hari Ini</h5>
        <span class="badge bg-secondary"><?= count($tamu_hari_ini ?? []) ?></span>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-striped table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 56px;">No</th>
                            <th>Nama</th>
                            <th>Instansi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($tamu_hari_ini)): ?>
                            <?php $no = 1; foreach ($tamu_hari_ini as $t): ?>
                                <tr>
                                    <td class="text-center text-body-secondary"><?= $no++ ?></td>
                                    <td><?= htmlspecialchars($t->nama, ENT_QUOTES, 'UTF-8') ?></td>
                                    <td><?= htmlspecialchars($t->nama_instansi, ENT_QUOTES, 'UTF-8') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3" class="text-center text-body-secondary py-4">Belum ada tamu tercatat hari ini.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- ================== Select2 untuk Pencarian Dropdown ================== -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
    $('#id_user').select2({
        placeholder: "Cari tamu berdasarkan nama atau instansi...",
        allowClear: true,
        width: '100%',
        matcher: function(params, data) {
            if ($.trim(params.term) === '') {
                return data;
            }
            if (typeof data.text === 'undefined') {
                return null;
            }
            const term = params.term.toLowerCase();
            const text = data.text.toLowerCase();
            if (text.indexOf(term) > -1) {
                return data;
            }
            return null;
        }
    });
});
</script>

<style>
.select2-container--default .select2-selection--single {
    height: 38px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    padding: 5px 10px;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 26px;
}
</style>

<?php $this->load->view('layouts/admin_footer'); ?>
