<div class="modal-header">
    <h5 class="modal-title">Detail Kunjungan - <?= $pengunjung->nama ?></h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
</div>
<div class="modal-body">
    <h6>🧍 Data Pengunjung</h6>
    <ul>
        <li><strong>Nama:</strong> <?= $pengunjung->nama ?></li>
        <li><strong>Email:</strong> <?= $pengunjung->email ?></li>
        <li><strong>No HP:</strong> <?= $pengunjung->notel ?></li>
        <li><strong>Instansi:</strong> <?= $pengunjung->nama_instansi ?></li>
        <li><strong>Jenis Kelamin:</strong> <?= $pengunjung->jeniskelamin ?></li>
        <li><strong>Pendidikan:</strong> <?= $pengunjung->pendidikan ?></li>
        <li><strong>Pekerjaan:</strong> <?= $pengunjung->pekerjaan ?></li>
    </ul>
    <hr>

    <h6>📋 Ringkasan Konsultasi</h6>
    <p><?= !empty($konsultasi) ? $konsultasi[0]->hasil_konsultasi : '<em>Belum ada ringkasan.</em>' ?></p>

    <h6>📊 Detail Kebutuhan Data</h6>
    <?php if (!empty($konsultasi)): ?>
        <ol type="a">
            <?php foreach ($konsultasi as $k): ?>
                <li style="margin-bottom: 10px;">
                    <?php if ($k->rincian_data): ?>
                        <div><strong>▪️ Rincian:</strong> <?= $k->rincian_data ?></div>
                    <?php endif; ?>
                    <?php if ($k->wilayah_data): ?>
                        <div><strong>▪️ Wilayah:</strong> <?= $k->wilayah_data ?></div>
                    <?php endif; ?>
                    <?php if ($k->tahun_awal || $k->tahun_akhir): ?>
                        <div><strong>▪️ Tahun:</strong> <?= $k->tahun_awal ?> - <?= $k->tahun_akhir ?></div>
                    <?php endif; ?>
                    <?php if ($k->judul_publikasi): ?>
                        <div><strong>▪️ Judul Publikasi:</strong> <?= $k->judul_publikasi ?></div>
                    <?php endif; ?>
                    <small class="text-muted">📅 Diinput: <?= $k->tanggal_input ?></small>
                </li>
            <?php endforeach; ?>
        </ol>
    <?php else: ?>
        <p><em>Belum ada data detail.</em></p>
    <?php endif; ?>

    <h6>⭐ Evaluasi Pengunjung</h6>
    <?php if (!empty($evaluasi)): ?>
        <ul>
            <?php foreach ($evaluasi as $e): ?>
                <li><strong>Indikator #<?= $e->indikator_id ?></strong> — Kepentingan: <?= $e->kepentingan ?>, Kepuasan: <?= $e->kepuasan ?></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p><em>Belum ada evaluasi.</em></p>
    <?php endif; ?>

</div>
