<?php $this->load->view('layouts/admin_header'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Daftar Tamu</h3>
    <a href="<?= site_url('admin/tambah') ?>" class="btn btn-success">+ Tambah Tamu</a>
</div>

<!-- Flash message -->
<?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($this->session->flashdata('success')) ?>
    </div>
<?php endif; ?>

<div class="card">
    <div class="card-header bg-primary text-white">
        <strong>Data Tamu</strong>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-hover">
            <thead class="table-primary">
                <tr>
                    <th>No</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>No HP</th>
                    <th>Jenis Kelamin</th>
                    <th>Pendidikan</th>
                    <th>Instansi</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($tamu)): ?>
                    <?php $no = 1; foreach ($tamu as $row): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= htmlspecialchars($row->nama) ?></td>
                            <td><?= htmlspecialchars($row->email) ?></td>
                            <td><?= htmlspecialchars($row->notel) ?></td>
                            <td><?= htmlspecialchars($row->jeniskelamin) ?></td>
                            <td>
                                <?php
                                    switch ($row->pendidikan) {
                                        case '1': echo '≤ SLTA'; break;
                                        case '2': echo 'D1/D2/D3'; break;
                                        case '3': echo 'D4/S1'; break;
                                        case '4': echo 'S2'; break;
                                        case '5': echo 'S3'; break;
                                        default: echo '-';
                                    }
                                ?>
                            </td>
                            <td><?= htmlspecialchars($row->nama_instansi) ?></td>
                            <td>
                                <a href="<?= site_url('admin/edit/' . $row->id_user) ?>" class="btn btn-warning btn-sm">Edit</a>
                                <a href="<?= site_url('admin/delete/' . $row->id_user) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" class="text-center">Belum ada data tamu.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $this->load->view('layouts/admin_footer'); ?>
