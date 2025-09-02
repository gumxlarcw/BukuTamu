<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Halaman Layanan | BPS Provinsi Maluku Utara</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Styles -->
    <link href="<?php echo base_url('assets/form/css/bootstrap.min.css'); ?>" rel="stylesheet" />
    <link href="<?php echo base_url('assets/form/css/paper-bootstrap-wizard.css'); ?>" rel="stylesheet" />
</head>

<body>
<div class="image-container set-full-height">
    <video autoplay muted loop playsinline id="bg-video"
        style="position: absolute; top: 0; left: 0; min-width: 100%; min-height: 100%; object-fit: cover; z-index: -1;">
        <source src="<?php echo base_url('assets/form/video/bg-video.mp4'); ?>" type="video/mp4">
    </video>

    <div class="container full-height-center">
        <div class="col-md-8 col-md-offset-2">
            <div class="card wizard-card">
                <form action="<?php echo base_url('layanan/go'); ?>" method="post">
                    <div class="wizard-header text-center">
                        <h3 class="wizard-title">Pilih Layanan</h3>
                        <p class="category">Silakan pilih layanan yang ingin Anda akses.</p>
                    </div>

                    <div class="row bubble-row text-center">
                        <?php
                        $layanan = [
                            'Perpustakaan' => 'Perpustakaan',
                            'Konsultasi Statistik' => 'Konsultasi Statistik',
                            'Rekomendasi Kegiatan Statistik' => 'Rekomendasi Kegiatan Statistik',
                            'Penjualan Produk Statistik' => 'Penjualan Produk Statistik',
                            'Keperluan Pimpinan' => 'Keperluan Pimpinan',
                            'Lainnya' => 'Lainnya'
                        ];
                        foreach ($layanan as $val => $label): ?>
                            <div class="col-xs-12 col-sm-6 bubble-col">
                                <input type="radio" name="layanan" value="<?= $val ?>" id="<?= $val ?>" required>
                                <label for="<?= $val ?>" class="bubble-option"><?= $label ?></label>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="text-center" style="margin-top: 30px;">
                        <button type="submit" class="btn btn-fill btn-success">Lanjut</button>
                    </div>
                </form>

                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger" style="margin-top: 15px;">
                        <?= $this->session->flashdata('error') ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php if ($this->session->flashdata('nomor_antrian')): ?>
<script>
	window.open("<?= base_url('antrian/cetak/' . $this->session->flashdata('nomor_antrian')) ?>", "_blank");
</script>
<?php endif; ?>
</body>
</html>
