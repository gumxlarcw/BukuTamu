<?php $this->load->view('layouts/admin_header'); ?>

<h3>Form Konsultasi Pengunjung</h3>

<!-- Informasi Pengunjung -->
<div class="card mb-4">
    <div class="card-body">
        <h5>Data Pengunjung</h5>
        <p><strong>Nama:</strong> <?= htmlspecialchars($pengunjung->nama, ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($pengunjung->email, ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>No HP:</strong> <?= htmlspecialchars($pengunjung->notel, ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Instansi:</strong> <?= htmlspecialchars($pengunjung->nama_instansi, ENT_QUOTES, 'UTF-8') ?></p>
        <p><strong>Tanggal Datang:</strong> <?= htmlspecialchars($kunjungan->date_visit, ENT_QUOTES, 'UTF-8') ?></p>
    </div>
</div>

<?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($this->session->flashdata('error'), ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<!-- Form Konsultasi -->
<form action="<?= site_url('admin/simpan_konsultasi/' . $kunjungan->id_kunjungan) ?>" method="post">
    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
    <div class="mb-3">
        <label>Ringkasan Hasil Konsultasi</label>
        <textarea name="hasil_konsultasi" class="form-control" rows="4" required></textarea>
    </div>

    
    <div class="text-center mt-4">
        <button class="btn btn-success">Simpan & Minta Evaluasi</button>
        <a href="<?= site_url('admin/dashboard') ?>" class="btn btn-secondary">Batal</a>
    </div>
</form>

<?php $this->load->view('layouts/admin_footer'); ?>
