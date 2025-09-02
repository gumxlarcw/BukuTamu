<?php $this->load->view('layouts/admin_header'); ?>

<h3>Tambah Data Tamu</h3>

<form action="<?= site_url('admin/insert') ?>" method="post">
    <div class="mb-3">
        <label>Tanggal Datang</label>
        <input type="datetime-local" name="tgldatang" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Nama</label>
        <input type="text" name="nama" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Email</label>
        <input type="email" name="email" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>No HP</label>
        <input type="text" name="notel" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Jenis Kelamin</label><br>
        <label><input type="radio" name="jeniskelamin" value="Laki-laki" required> Laki-laki</label>
        <label><input type="radio" name="jeniskelamin" value="Perempuan" required> Perempuan</label>
    </div>

    <div class="mb-3">
        <label>Pendidikan</label>
        <select name="pendidikan" class="form-control" required>
            <option value="">Pilih</option>
            <option value="1">1 : &le; SLTA</option>
            <option value="2">2 : D1/D2/D3</option>
            <option value="3">3 : D4/S1</option>
            <option value="4">4 : S2</option>
            <option value="5">5 : S3</option>
        </select>
    </div>

    <div class="mb-3">
        <label>Pekerjaan</label>
        <select name="pekerjaan" class="form-control" required>
            <option value="">Pilih</option>
            <option value="1">1 : Pelajar/Mahasiswa</option>
            <option value="2">2 : Peneliti/Dosen</option>
            <option value="3">3 : ASN/TNI/Polri</option>
            <option value="4">4 : BUMN/BUMD</option>
            <option value="5">5 : Swasta</option>
            <option value="6">6 : Wiraswasta</option>
            <option value="7">7 : Lainnya</option>
        </select>
    </div>

    <div class="mb-3">
        <label>Kategori Instansi</label>
        <select name="kategori_instansi" class="form-control" required>
            <option value="">Pilih</option>
            <option value="1">1 : Lembaga Negara</option>
            <option value="2">2 : Kementerian & Lembaga Pemerintah</option>
            <option value="3">3 : TNI/POLRI/BIN/Kejaksaan</option>
            <option value="4">4 : Pemerintah Daerah</option>
            <option value="5">5 : Lembaga Internasional</option>
            <option value="6">6 : Lembaga Penelitian & Pendidikan</option>
            <option value="7">7 : BUMN/BUMD</option>
            <option value="8">8 : Swasta</option>
            <option value="9">9 : Lainnya</option>
        </select>
    </div>

    <div class="mb-3">
        <label>Nama Instansi</label>
        <input type="text" name="nama_instansi" class="form-control" required>
    </div>

    <div class="mb-3">
        <label>Pemanfaatan</label>
        <select name="pemanfaatan" class="form-control" required>
            <option value="">Pilih</option>
            <option value="1">1 : Tugas Sekolah/Kuliah</option>
            <option value="2">2 : Pemerintah</option>
            <option value="3">3 : Komersial</option>
            <option value="4">4 : Penelitian</option>
            <option value="5">5 : Lainnya</option>
        </select>
    </div>

    <div class="mb-3">
        <label>Pengaduan PST</label><br>
        <label><input type="radio" name="pengaduan" value="Ya" required> Ya</label>
        <label><input type="radio" name="pengaduan" value="Tidak" required> Tidak</label>
    </div>

    <button type="submit" class="btn btn-success">Simpan</button>
    <a href="<?= site_url('admin/dashboard') ?>" class="btn btn-secondary">Kembali</a>
</form>

<?php $this->load->view('layouts/admin_footer'); ?>
