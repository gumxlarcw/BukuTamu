<?php $this->load->view('layouts/admin_header'); ?>

<div class="d-flex flex-column flex-lg-row gap-2 justify-content-between align-items-lg-center mb-3">
    <div>
        <h3 class="mb-1">Tambah Data Tamu</h3>
        <div class="small text-body-secondary">Lengkapi data berikut untuk membuat tamu baru.</div>
    </div>
    <a href="<?= site_url('admin/daftar_tamu') ?>" class="btn btn-sm btn-outline-secondary">Kembali</a>
</div>

<?php if ($this->session->flashdata('error')): ?>
    <div class="alert alert-danger">
        <?= htmlspecialchars($this->session->flashdata('error'), ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body">
        <form action="<?= site_url('admin/insert') ?>" method="post">
            <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">

            <div class="row g-3">
                <div class="col-lg-6">
                    <label class="form-label" for="tgldatang">Tanggal Datang</label>
                    <input type="datetime-local" id="tgldatang" name="tgldatang" value="<?= date('Y-m-d\TH:i') ?>" class="form-control" required>
                </div>

                <div class="col-lg-6">
                    <label class="form-label" for="nama">Nama</label>
                    <input type="text" id="nama" name="nama" class="form-control" required autocomplete="name" placeholder="Nama lengkap">
                </div>

                <div class="col-lg-6">
                    <label class="form-label" for="email">Email</label>
                    <input type="email" id="email" name="email" class="form-control" required autocomplete="email" placeholder="nama@email.com">
                </div>

                <div class="col-lg-6">
                    <label class="form-label" for="notel">No HP</label>
                    <input type="tel" id="notel" name="notel" class="form-control" required autocomplete="tel" placeholder="08xxxxxxxxxx">
                </div>

                <div class="col-lg-6">
                    <label class="form-label d-block">Jenis Kelamin</label>
                    <div class="d-flex gap-3 flex-wrap">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="jeniskelamin" id="jkL" value="Laki-laki" required>
                            <label class="form-check-label" for="jkL">Laki-laki</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="jeniskelamin" id="jkP" value="Perempuan" required>
                            <label class="form-check-label" for="jkP">Perempuan</label>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3">
                    <label class="form-label" for="pendidikan">Pendidikan</label>
                    <select id="pendidikan" name="pendidikan" class="form-select" required>
                        <option value="">Pilih</option>
                        <option value="1">1 : ≤ SLTA</option>
                        <option value="2">2 : D1/D2/D3</option>
                        <option value="3">3 : D4/S1</option>
                        <option value="4">4 : S2</option>
                        <option value="5">5 : S3</option>
                    </select>
                </div>

                <div class="col-lg-3">
                    <label class="form-label" for="pekerjaan">Pekerjaan</label>
                    <select id="pekerjaan" name="pekerjaan" class="form-select" required>
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

                <div class="col-lg-6">
                    <label class="form-label" for="kategori_instansi">Kategori Instansi</label>
                    <select id="kategori_instansi" name="kategori_instansi" class="form-select" required>
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

                <div class="col-lg-6">
                    <label class="form-label" for="nama_instansi">Nama Instansi</label>
                    <input type="text" id="nama_instansi" name="nama_instansi" class="form-control" required placeholder="Nama instansi">
                </div>

                <div class="col-lg-6">
                    <label class="form-label" for="pemanfaatan">Pemanfaatan</label>
                    <select id="pemanfaatan" name="pemanfaatan" class="form-select" required>
                        <option value="">Pilih</option>
                        <option value="1">1 : Tugas Sekolah/Kuliah</option>
                        <option value="2">2 : Pemerintah</option>
                        <option value="3">3 : Komersial</option>
                        <option value="4">4 : Penelitian</option>
                        <option value="5">5 : Lainnya</option>
                    </select>
                </div>

                <div class="col-lg-6">
                    <label class="form-label d-block">Pengaduan PST</label>
                    <div class="d-flex gap-3 flex-wrap">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="pengaduan" id="pengaduanYa" value="Ya" required>
                            <label class="form-check-label" for="pengaduanYa">Ya</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="pengaduan" id="pengaduanTidak" value="Tidak" required>
                            <label class="form-check-label" for="pengaduanTidak">Tidak</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-dark">Simpan</button>
                <a href="<?= site_url('admin/daftar_tamu') ?>" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
</div>

<?php $this->load->view('layouts/admin_footer'); ?>
