<?php $this->load->view('layouts/admin_header'); ?>

<h3>Daftar Kunjungan Hari Ini</h3>

<?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success"><?= $this->session->flashdata('success') ?></div>
<?php endif; ?>

<button class="btn btn-secondary mb-3" onclick="tesSuaraTV()">Tes Suara ke TV</button>
<a class="btn btn-info mb-3 ms-2" href="https://10.82.7.1:5443/update-antrian" target="_blank">
    Koneksi ke Layar Antrian
</a>

<table class="table table-bordered table-hover mt-4">
    <thead class="table-primary">
        <tr>
            <th>No</th>
            <th>Nama</th>
            <th>Instansi</th>
            <th>Layanan</th>
            <th>Tanggal</th>
            <th>Nomor Antrian</th>
            <th>Status</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($kunjungan)): ?>
            <?php $no = 1; foreach ($kunjungan as $k): ?>
                <?php
                    // Penentuan warna badge dan label aman
                    $status = strtolower(trim($k->status));
                    if ($status === 'selesai') {
                        $badge_class = 'success';
                    } elseif ($status === 'proses') {
                        $badge_class = 'warning';
                    } elseif ($status === 'menunggu_evaluasi') {
                        $badge_class = 'info';
                    } elseif ($status === 'antri') {
                        $badge_class = 'dark';
                    } elseif (!$status) {
                        $badge_class = 'secondary';
                        $status = 'tidak diketahui';
                    } else {
                        $badge_class = 'secondary';
                    }

                    $status_label = ucfirst($status);
                ?>
                <tr>
                    <td><?= $no++ ?></td>
                    <td><?= $k->nama ?></td>
                    <td><?= $k->nama_instansi ?></td>
                    <td><?= $k->jenis_layanan ?? '<em>-</em>' ?></td>
                    <td><?= $k->date_visit ?></td>
                    <td><?= $k->nomor_antrian ?? '-' ?></td>
                    <td><span class="badge bg-<?= $badge_class ?>"><?= $status_label ?></span></td>
                    <td>
                        <?php if ($status === 'selesai'): ?>
                            <span class="text-muted">Selesai</span>
                            <?php elseif ($status === 'menunggu_evaluasi'): ?>
                            <button class="btn btn-outline-secondary btn-sm" onclick="copyLink('<?= site_url('evaluasi/isi/' . $k->id_kunjungan) ?>')">
                                Copy Link Evaluasi
                            </button>
                        <?php else: ?>
                            <a href="<?= site_url('admin/form_konsultasi/' . $k->id_kunjungan) ?>" class="btn btn-primary btn-sm mb-1">Mulai Konsultasi</a>
                            <a href="<?= site_url('admin/aktifkan_evaluasi/' . $k->id_kunjungan) ?>" 
                            class="btn btn-warning btn-sm mb-1"
                            onclick="return confirm('Kirim ke tablet untuk evaluasi?')">Kirim Evaluasi</a>
                            <button class="btn btn-success btn-sm" onclick="panggilAntrian('<?= $k->nomor_antrian ?>')">Panggil</button>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr><td colspan="6" class="text-center">Belum ada kunjungan hari ini.</td></tr>
        <?php endif; ?>
    </tbody>
</table>

<script>
function tesSuaraTV() {
    const nomor = "TES";

    fetch("https://10.82.7.1:5443/update-antrian", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ nomor })
    }).then(() => {
        alert("Tes suara berhasil dikirim ke TV.");
    }).catch(err => {
        alert("Gagal mengirim tes suara: " + err);
    });
}
</script>


<script>
function panggilAntrian(nomor) {
    if (!nomor || nomor === '-' || nomor.trim() === '') {
        alert("Nomor antrian tidak tersedia.");
        return;
    }

    // Kirim ke client
    fetch("https://10.82.7.1:5443/update-antrian", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ nomor })
    }).then(() => {
        alert("Nomor antrian " + nomor + " telah dipanggil.");
    }).catch(err => {
        alert("Gagal mengirim ke client: " + err);
    });
}


function copyLink(link) {
    navigator.clipboard.writeText(link).then(() => {
        alert("Link evaluasi berhasil disalin!");
    }).catch(err => {
        alert("Gagal menyalin link: " + err);
    });
}


</script>

<?php $this->load->view('layouts/admin_footer'); ?>
