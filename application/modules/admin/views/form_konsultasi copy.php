<?php $this->load->view('layouts/admin_header'); ?>

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

<!-- Form Konsultasi -->
<form action="<?= site_url('admin/simpan_konsultasi/' . $kunjungan->id_kunjungan) ?>" method="post">
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
