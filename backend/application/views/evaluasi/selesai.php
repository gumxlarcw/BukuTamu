<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Terima Kasih</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #e9f5ec;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            flex-direction: column;
            text-align: center;
        }
        h1 {
            font-size: 2rem;
            margin-bottom: 20px;
        }
        .thanks-box {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 10px rgba(0,0,0,0.05);
        }
    </style>
</head>
<body>

<div class="thanks-box">
    <h1>🙏 Terima kasih atas penilaian Anda!</h1>
    <p>Halaman ini akan kembali ke tampilan awal dalam <span id="countdown">5</span> detik...</p>
</div>

<script>
    let count = 5;
    const countdown = document.getElementById('countdown');

    const interval = setInterval(() => {
        count--;
        countdown.textContent = count;

        if (count <= 0) {
            clearInterval(interval);
            window.location.href = "<?= site_url('evaluasi/tunggu') ?>";
        }
    }, 1000);
</script>

</body>
</html>
