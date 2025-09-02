<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Form Evaluasi Pengunjung</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Styles -->
    <link href="<?= base_url('assets/form/css/bootstrap.min.css'); ?>" rel="stylesheet" />
    <link href="<?= base_url('assets/form/css/paper-bootstrap-wizard.css'); ?>" rel="stylesheet" />

    <style>
        body {
            background: #f0f0f0;
            padding: 20px;
        }
        .question-block {
            background: #fff;
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 20px;
            box-shadow: 0 0 5px rgba(0,0,0,0.05);
        }
        label {
            font-weight: 600;
        }
        .star-group {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-top: 10px;
        }
        .star-rating {
            direction: rtl;
            display: flex;
            justify-content: center;
            gap: 5px;
        }
        .star-rating input[type="radio"] {
            display: none;
        }
        .star-rating label {
            font-size: 1.8rem;
            color: #ccc;
            cursor: pointer;
            transition: color 0.2s;
        }
        .star-rating input[type="radio"]:checked ~ label,
        .star-rating label:hover,
        .star-rating label:hover ~ label {
            color: gold;
        }
    </style>
</head>
<body>

<div class="image-container set-full-height">
    <video autoplay muted loop playsinline id="bg-video"
        style="position: absolute; top: 0; left: 0; min-width: 100%; min-height: 100%; object-fit: cover; z-index: -1;">
        <source src="<?= base_url('assets/form/video/bg-video.mp4'); ?>" type="video/mp4">
    </video>

    <div class="container full-height-center">
        <div class="col-md-8 col-md-offset-2">
            <div class="card wizard-card">
                <form action="<?= site_url('evaluasi/submit') ?>" method="post">
                    <div class="wizard-header text-center">
                        <h3 class="wizard-title">📝 Evaluasi Pelayanan</h3>
                        <p class="category">Silakan berikan penilaian terhadap setiap indikator.</p>
                    </div>

                    <input type="hidden" name="id_kunjungan" value="<?= $id_kunjungan ?>">

                    <?php foreach ($indikator as $no => $label): ?>
                        <div class="question-block">
                            <p><strong><?= $no . '. ' . $label ?></strong></p>

                            <div class="star-group">
                                <label>Kepentingan:</label>
                                <div class="star-rating">
                                    <?php for ($i = 10; $i >= 1; $i--): ?>
                                        <input type="radio" id="kepentingan_<?= $no ?>_<?= $i ?>" name="kepentingan[<?= $no ?>]" value="<?= $i ?>" required>
                                        <label for="kepentingan_<?= $no ?>_<?= $i ?>">★</label>
                                    <?php endfor; ?>
                                </div>
                            </div>

                            <div class="star-group mt-3">
                                <label>Kepuasan:</label>
                                <div class="star-rating">
                                    <?php for ($i = 10; $i >= 1; $i--): ?>
                                        <input type="radio" id="kepuasan_<?= $no ?>_<?= $i ?>" name="kepuasan[<?= $no ?>]" value="<?= $i ?>" required>
                                        <label for="kepuasan_<?= $no ?>_<?= $i ?>">★</label>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="question-block text-center">
                        <label><strong>Seberapa puas Anda dengan pelayanan kami secara keseluruhan?</strong></label>
                        <div class="star-rating mt-3">
                            <?php for ($i = 10; $i >= 1; $i--): ?>
                                <input type="radio" id="skor<?= $i ?>" name="skor_keseluruhan" value="<?= $i ?>" required>
                                <label for="skor<?= $i ?>">★</label>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="text-center" style="margin-top: 30px;">
                        <button type="submit" class="btn btn-fill btn-success btn-lg">Kirim Evaluasi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>
