<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Menunggu Evaluasi</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: sans-serif;
            background: #f5f5f5;
            text-align: center;
            padding-top: 50px;
        }

        h1 {
            font-size: 2rem;
            color: #333;
        }

        .spinner {
            margin: 30px auto;
            width: 50px;
            height: 50px;
            border: 6px solid #ccc;
            border-top-color: #0d6efd;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .sub {
            color: #777;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <h1>👤 Mohon Tunggu</h1>
    <div class="spinner"></div>
    <p class="sub">Layar ini akan otomatis menampilkan form evaluasi jika ada pengunjung.</p>

    <script>
        async function checkForEvaluation() {
            try {
                const res = await fetch("<?= site_url('evaluasi/kunjungan_terbaru') ?>");
                const data = await res.json();

                if (data.status === 'ada') {
                    window.location.href = "<?= site_url('evaluasi/isi/') ?>" + data.id_kunjungan;
                }
            } catch (e) {
                console.error("Gagal cek evaluasi:", e);
            }
        }

        // Jalankan polling setiap 5 detik
        setInterval(checkForEvaluation, 5000);
    </script>
</body>
</html>
