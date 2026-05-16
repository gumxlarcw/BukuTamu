<?php $this->load->view('layouts/admin_header'); ?>

<h3>Edit Data Tamu</h3>

<form action="<?= site_url('admin/update/' . $tamu->id_user) ?>" method="post">
    <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">
    <input type="hidden" name="id_user" value="<?= htmlspecialchars($tamu->id_user, ENT_QUOTES, 'UTF-8') ?>">

    <div class="mb-3">
        <label>Tanggal Datang</label>
        <input type="datetime-local" name="tgldatang" value="<?= date('Y-m-d\TH:i', strtotime($tamu->tgldatang)) ?>" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Nama</label>
        <input type="text" name="nama" value="<?= htmlspecialchars($tamu->nama, ENT_QUOTES, 'UTF-8') ?>" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" value="<?= htmlspecialchars($tamu->email, ENT_QUOTES, 'UTF-8') ?>" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>No HP</label>
        <input type="text" name="notel" value="<?= htmlspecialchars($tamu->notel, ENT_QUOTES, 'UTF-8') ?>" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Jenis Kelamin</label><br>
        <label><input type="radio" name="jeniskelamin" value="Laki-laki" <?= ($tamu->jeniskelamin == 'Laki-laki') ? 'checked' : '' ?>> Laki-laki</label>
        <label><input type="radio" name="jeniskelamin" value="Perempuan" <?= ($tamu->jeniskelamin == 'Perempuan') ? 'checked' : '' ?>> Perempuan</label>
    </div>

    <div class="mb-3">
        <label>Pendidikan</label>
        <select name="pendidikan" class="form-control" required>
            <option value="">Pilih</option>
            <option value="1" <?= $tamu->pendidikan == '1' ? 'selected' : '' ?>>1 : &le; SLTA</option>
            <option value="2" <?= $tamu->pendidikan == '2' ? 'selected' : '' ?>>2 : D1/D2/D3</option>
            <option value="3" <?= $tamu->pendidikan == '3' ? 'selected' : '' ?>>3 : D4/S1</option>
            <option value="4" <?= $tamu->pendidikan == '4' ? 'selected' : '' ?>>4 : S2</option>
            <option value="5" <?= $tamu->pendidikan == '5' ? 'selected' : '' ?>>5 : S3</option>
        </select>
    </div>

    <div class="mb-3">
        <label>Pekerjaan</label>
        <select name="pekerjaan" class="form-control" required>
            <option value="">Pilih</option>
            <option value="1" <?= $tamu->pekerjaan == '1' ? 'selected' : '' ?>>1 : Pelajar/Mahasiswa</option>
            <option value="2" <?= $tamu->pekerjaan == '2' ? 'selected' : '' ?>>2 : Peneliti/Dosen</option>
            <option value="3" <?= $tamu->pekerjaan == '3' ? 'selected' : '' ?>>3 : ASN/TNI/Polri</option>
            <option value="4" <?= $tamu->pekerjaan == '4' ? 'selected' : '' ?>>4 : BUMN/BUMD</option>
            <option value="5" <?= $tamu->pekerjaan == '5' ? 'selected' : '' ?>>5 : Swasta</option>
            <option value="6" <?= $tamu->pekerjaan == '6' ? 'selected' : '' ?>>6 : Wiraswasta</option>
            <option value="7" <?= $tamu->pekerjaan == '7' ? 'selected' : '' ?>>7 : Lainnya</option>
        </select>
    </div>

    <div class="mb-3">
        <label>Kategori Instansi</label>
        <select name="kategori_instansi" class="form-control" required>
            <option value="">Pilih</option>
            <?php for ($i = 1; $i <= 9; $i++): ?>
                <option value="<?= $i ?>" <?= $tamu->kategori_instansi == "$i" ? 'selected' : '' ?>><?= $i ?> : Kategori <?= $i ?></option>
            <?php endfor; ?>
        </select>
    </div>

    <div class="mb-3">
        <label>Nama Instansi</label>
        <input type="text" name="nama_instansi" value="<?= htmlspecialchars($tamu->nama_instansi, ENT_QUOTES, 'UTF-8') ?>" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Pemanfaatan</label>
        <select name="pemanfaatan" class="form-control" required>
            <option value="">Pilih</option>
            <option value="1" <?= $tamu->pemanfaatan == '1' ? 'selected' : '' ?>>1 : Tugas Sekolah/Kuliah</option>
            <option value="2" <?= $tamu->pemanfaatan == '2' ? 'selected' : '' ?>>2 : Pemerintah</option>
            <option value="3" <?= $tamu->pemanfaatan == '3' ? 'selected' : '' ?>>3 : Komersial</option>
            <option value="4" <?= $tamu->pemanfaatan == '4' ? 'selected' : '' ?>>4 : Penelitian</option>
            <option value="5" <?= $tamu->pemanfaatan == '5' ? 'selected' : '' ?>>5 : Lainnya</option>
        </select>
    </div>

    <div class="mb-3">
        <label>Pengaduan PST</label><br>
        <label><input type="radio" name="pengaduan" value="Ya" <?= $tamu->pengaduan == 'Ya' ? 'checked' : '' ?>> Ya</label>
        <label><input type="radio" name="pengaduan" value="Tidak" <?= $tamu->pengaduan == 'Tidak' ? 'checked' : '' ?>> Tidak</label>
    </div>

    <button type="submit" class="btn btn-primary">Update</button>
    <a href="<?= site_url('admin/dashboard') ?>" class="btn btn-secondary">Kembali</a>
</form>

<?php $this->load->view('layouts/admin_footer'); ?>
