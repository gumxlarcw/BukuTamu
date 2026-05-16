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

    <h6 style="margin-bottom: 20px;">📋 <strong>Hasil Konsultasi</strong></h6>
    <ol style="padding-left: 20px;">
        <li style="margin-bottom: 15px;">
            <span style="font-weight: bold;">📂 Ringkasan:</span><br>
            <div style="margin-left: 10px; margin-top: 5px;">
                <?= !empty($konsultasi) ? $konsultasi[0]->hasil_konsultasi : '<em>Tidak ada data</em>' ?>
            </div>
        </li>

        <li>
            <span style="font-weight: bold;">📚 Detail Data yang Diminta:</span>
            <ol type="a" style="margin-top: 10px; padding-left: 20px;">
                <?php foreach ($konsultasi as $k): ?>
                    <li style="margin-bottom: 15px;">
                        <div style="margin-left: 10px;">
                            <?php
                            $items = array_map('trim', explode(',', $k->rincian_data));
                            foreach ($items as $item): ?>
                                <div>▪️ <?= $item ?></div>
                            <?php endforeach; ?>
                            <?php if ($k->wilayah_data): ?>
                                <div><strong>▪️ Wilayah:</strong> <?= $k->wilayah_data ?></div>
                            <?php endif; ?>
                            <?php if ($k->tahun_awal && $k->tahun_akhir): ?>
                                <div><strong>▪️ Tahun:</strong> <?= $k->tahun_awal ?> – <?= $k->tahun_akhir ?></div>
                            <?php endif; ?>
                            <?php if ($k->judul_publikasi): ?>
                                <div><strong>▪️ Judul Publikasi:</strong> <?= $k->judul_publikasi ?></div>
                            <?php endif; ?>
                            <div style="margin-top: 5px;">
                                <small class="text-muted">📅 Input pada: <?= $k->tanggal_input ?></small>
                            </div>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ol>
        </li>
    </ol>

    <h6>⭐ Evaluasi</h6>
    <?php if ($evaluasi): ?>
        <ul>
            <?php foreach ($evaluasi as $e): ?>
                <li><strong>Indikator #<?= $e->indikator_id ?></strong> - Kepentingan: <?= $e->kepentingan ?>, Kepuasan: <?= $e->kepuasan ?></li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <p><em>Belum ada evaluasi.</em></p>
    <?php endif; ?>
</div>
