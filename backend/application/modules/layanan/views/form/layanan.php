<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Halaman Layanan | BPS Provinsi Maluku Utara</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Styles -->
    <link href="<?php echo base_url('assets/form/css/bootstrap.min.css'); ?>" rel="stylesheet" />
    <link href="<?php echo base_url('assets/form/css/paper-bootstrap-wizard.css'); ?>" rel="stylesheet" />
    <link href="<?php echo base_url('assets/form/css/kiosk.css') . '?v=' . @filemtime(FCPATH . 'assets/form/css/kiosk.css'); ?>" rel="stylesheet" />
    <link href="<?php echo base_url('assets/form/css/themify-icons.css'); ?>" rel="stylesheet" />
</head>

<body>
<div class="image-container set-full-height kiosk-page">
    <video autoplay muted loop playsinline preload="metadata" id="bg-video"
        style="position: absolute; top: 0; left: 0; min-width: 100%; min-height: 100%; object-fit: cover; z-index: -1;">
        <source src="<?php echo base_url('assets/form/video/bg-video.mp4'); ?>" type="video/mp4">
    </video>

    <div class="container full-height-center">
        <div class="col-md-12">
            <div class="card wizard-card">
                <form action="<?php echo base_url('layanan/go'); ?>" method="post">
                    <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
                    <div class="wizard-header text-center">
                        <div class="kiosk-step">Langkah 1 dari 3</div>
                        <h3 class="wizard-title" style="margin-top: 0;">Pilih Layanan</h3>
                        <p class="category">Pilih layanan untuk membantu kami memproses kunjungan Anda lebih cepat.</p>
                        <div class="kiosk-microcopy">Waktu pengisian &plusmn; 3 menit.</div>
                    </div>

                    <div class="text-center" style="margin-top: 6px; margin-bottom: 6px;">
                        <span class="label label-success kiosk-badge" id="selectedLayananLabel" style="display:none;"></span>
                        <div id="selectedLayananHint" class="category" style="margin-top: 8px;">Belum ada layanan dipilih.</div>
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

                        $descs = [
                            'Perpustakaan' => 'Akses koleksi publikasi, referensi, dan layanan perpustakaan.',
                            'Konsultasi Statistik' => 'Konsultasi data/metadata, cara akses, dan pemahaman statistik.',
                            'Rekomendasi Kegiatan Statistik' => 'Saran kegiatan statistik (survei/pendataan) agar sesuai standar.',
                            'Penjualan Produk Statistik' => 'Pembelian data mikro atau publikasi statistik.',
                            'Keperluan Pimpinan' => 'Keperluan kedinasan/koordinasi yang ditujukan kepada pimpinan.',
                            'Lainnya' => 'Pertemuan dengan pegawai, perbaikan sarana prasarana, menghadiri acara, atau keperluan lain.'
                        ];

                        $icons = [
                            'Perpustakaan' => 'ti-book',
                            'Konsultasi Statistik' => 'ti-comments',
                            'Rekomendasi Kegiatan Statistik' => 'ti-clipboard',
                            'Penjualan Produk Statistik' => 'ti-shopping-cart',
                            'Keperluan Pimpinan' => 'ti-crown',
                            'Lainnya' => 'ti-more'
                        ];
                        foreach ($layanan as $val => $label):
                            $id = 'layanan_' . preg_replace('/[^a-z0-9]+/i', '_', strtolower($val));
                            $icon = $icons[$val] ?? 'ti-more';
                            $desc = $descs[$val] ?? '';
                        ?>
                            <div class="col-xs-12 col-sm-6 bubble-col">
                                <input type="radio" name="layanan" value="<?= htmlspecialchars($val, ENT_QUOTES, 'UTF-8') ?>" id="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>" required>
                                <label for="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>" class="bubble-option" data-layanan-label="<?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?>">
                                    <span class="layanan-option" aria-hidden="false">
                                        <span class="layanan-icon" aria-hidden="true"><i class="<?= htmlspecialchars($icon, ENT_QUOTES, 'UTF-8') ?>"></i></span>
                                        <span class="layanan-text"><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php if (!empty($desc)): ?>
                                            <span class="layanan-desc text-muted"><?= htmlspecialchars($desc, ENT_QUOTES, 'UTF-8') ?></span>
                                        <?php endif; ?>
                                        <span class="layanan-selected" aria-hidden="true"><i class="ti-check"></i> Dipilih</span>
                                    </span>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="text-center" style="margin-top: 16px;">
                        <button type="submit" class="btn btn-fill btn-success btn-lg" id="btnLanjut" disabled>Lanjut</button>
                        <div id="btnHint" class="kiosk-microcopy" style="margin-top: 8px;">Pilih layanan untuk mengaktifkan tombol.</div>
                    </div>
                </form>

                <?php if ($this->session->flashdata('error')): ?>
                    <div class="alert alert-danger" style="margin-top: 15px;">
                        <?= htmlspecialchars($this->session->flashdata('error'), ENT_QUOTES, 'UTF-8') ?>
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

<script>
(function () {
    const btn = document.getElementById('btnLanjut');
    const label = document.getElementById('selectedLayananLabel');
    const hint = document.getElementById('selectedLayananHint');
    const btnHint = document.getElementById('btnHint');
    const radios = document.querySelectorAll('input[name="layanan"]');

    function updateSelected() {
        const checked = document.querySelector('input[name="layanan"]:checked');
        if (!checked) {
            btn.disabled = true;
            label.style.display = 'none';
            hint.textContent = 'Belum ada layanan dipilih.';
            btnHint.textContent = 'Pilih layanan untuk mengaktifkan tombol.';
            return;
        }

        btn.disabled = false;
        const value = checked.value || '';
        label.style.display = 'inline-block';
        label.textContent = 'Layanan terpilih: ' + value;
        hint.textContent = '';
        btnHint.textContent = 'Anda bisa lanjut atau ganti pilihan.';
    }

    radios.forEach(r => r.addEventListener('change', updateSelected));
    updateSelected();
})();
</script>
</body>
</html>
