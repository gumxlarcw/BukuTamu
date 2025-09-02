<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Identifikasi Pengunjung | BPS Provinsi Maluku Utara</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSS -->
    <link href="<?php echo base_url('assets/form/css/bootstrap.min.css'); ?>" rel="stylesheet" />
    <link href="<?php echo base_url('assets/form/css/paper-bootstrap-wizard.css'); ?>" rel="stylesheet" />

    <style>
        body, html {
            height: 100%;
            margin: 0;
        }

        .image-container {
            position: relative;
            min-height: 100vh;
            overflow: hidden;
        }

        #bg-video {
            position: absolute;
            top: 0;
            left: 0;
            min-width: 100%;
            min-height: 100%;
            object-fit: cover;
            z-index: -1;
        }

        .centered-container {
            position: relative;
            z-index: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
        }

        .card {
            padding: 40px 20px;
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
        }

        .btn-block {
            width: 100%;
            padding: 15px;
        }
    </style>
</head>

<body>
    <div class="image-container">
        <video autoplay muted loop playsinline id="bg-video">
            <source src="<?php echo base_url('assets/form/video/bg-video.mp4'); ?>" type="video/mp4">
        </video>

        <div class="centered-container">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-md-8 col-md-offset-2">
                        <div class="card wizard-card text-center">
                            <div class="wizard-header">
                                <h3 class="wizard-title">Apakah Anda sudah pernah mendaftar?</h3>
                                <p class="category">Jika sudah pernah mengisi data, Anda cukup melakukan verifikasi wajah.</p>
                            </div>

                            <div class="row" style="margin-top: 30px;">
                                <div class="col-sm-6">
                                    <a href="<?= base_url('selamat_datang/recognize') ?>" class="btn btn-lg btn-success btn-fill btn-block">🔍 Sudah Pernah Daftar</a>
                                </div>
                                <div class="col-sm-6">
                                    <a href="<?= base_url('layanan/auto'); ?>" class="btn btn-lg btn-primary btn-fill btn-block">🆕 Belum Pernah Daftar</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
