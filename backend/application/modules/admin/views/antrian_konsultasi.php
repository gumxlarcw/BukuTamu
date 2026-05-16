<?php $this->load->view('layouts/admin_header'); ?>

<div class="d-flex flex-column flex-lg-row gap-2 justify-content-between align-items-lg-center mb-3">
    <div>
        <h3 class="mb-1">Antrian Konsultasi</h3>
        <div class="small text-body-secondary">Daftar kunjungan hari ini untuk layanan konsultasi.</div>
    </div>
    <div class="d-flex flex-wrap gap-2 align-items-center">
        <button type="button" class="btn btn-sm btn-outline-secondary" onclick="tesSuaraTV()">Tes Suara ke TV</button>
        <a class="btn btn-sm btn-dark" href="https://dashboard-pst.bpsmalut.com" target="_blank" rel="noopener">
            Koneksi ke Layar Antrian
        </a>
    </div>
</div>

<?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success"><?= htmlspecialchars($this->session->flashdata('success'), ENT_QUOTES, 'UTF-8') ?></div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-striped table-hover align-middle mb-0">
        <thead>
            <tr>
                <th class="text-center" style="width: 56px;">No</th>
                <th class="text-center" style="min-width: 220px;">Nama</th>
                <th class="text-center" style="min-width: 220px;">Instansi</th>
                <th class="text-center" style="min-width: 220px;">Layanan</th>
                <th class="text-center text-nowrap" style="width: 190px;">Tanggal</th>
                <th class="text-center text-nowrap" style="width: 140px;">Nomor Antrian</th>
                <th class="text-center text-nowrap" style="width: 170px;">Status</th>
                <th class="text-center text-nowrap" style="width: 260px;">Aksi</th>
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
                        <td class="text-center text-body-secondary"><?= $no++ ?></td>
                        <td><?= htmlspecialchars($k->nama, ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($k->nama_instansi, ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= $k->jenis_layanan ? htmlspecialchars($k->jenis_layanan, ENT_QUOTES, 'UTF-8') : '<span class="text-body-secondary">-</span>' ?></td>
                        <td class="text-nowrap text-center"><?= htmlspecialchars($k->date_visit, ENT_QUOTES, 'UTF-8') ?></td>
                        <td class="text-nowrap text-center"><?= $k->nomor_antrian ? htmlspecialchars($k->nomor_antrian, ENT_QUOTES, 'UTF-8') : '<span class="text-body-secondary">-</span>' ?></td>
                        <td class="text-center"><span class="badge bg-<?= $badge_class ?>"><?= $status_label ?></span></td>
                        <td class="text-center">
                            <?php if ($status === 'selesai'): ?>
                                <span class="text-body-secondary">Selesai</span>
                                <?php elseif ($status === 'menunggu_evaluasi'): ?>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="copyLink('<?= htmlspecialchars(site_url('evaluasi/isi/' . $k->id_kunjungan), ENT_QUOTES, 'UTF-8') ?>')">Copy Link Evaluasi</button>
                            <?php else: ?>
                                <div class="d-inline-flex flex-wrap gap-1 justify-content-center">
                                    <a href="<?= site_url('admin/form_konsultasi/' . $k->id_kunjungan) ?>" class="btn btn-sm btn-dark">Mulai Konsultasi</a>
                                    <a href="<?= site_url('admin/aktifkan_evaluasi/' . $k->id_kunjungan) ?>" class="btn btn-sm btn-outline-secondary" onclick="return confirm('Kirim ke tablet untuk evaluasi?')">Kirim Evaluasi</a>
                                    <button type="button" class="btn btn-sm btn-outline-secondary" onclick="panggilAntrian('<?= htmlspecialchars((string) $k->nomor_antrian, ENT_QUOTES, 'UTF-8') ?>')">Panggil</button>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="8" class="text-center text-body-secondary py-4">Belum ada kunjungan hari ini.</td></tr>
            <?php endif; ?>
        </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function tesSuaraTV() {
    const nomor = "TES";

    fetch("https://dashboard-pst.bpsmalut.com/api/update-antrian", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ nomor })
    }).then(() => {
        alert("Tes suara berhasil dikirim ke TV.");
    }).catch(err => {
        console.error("Test sound error:", err);
        alert("Gagal mengirim tes suara ke TV. Periksa koneksi network dan pastikan server dashboard berjalan.");
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
    fetch("https://dashboard-pst.bpsmalut.com/api/update-antrian", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ nomor })
    }).then(() => {
        alert("Nomor antrian " + nomor + " telah dipanggil.");
    }).catch(err => {
        console.error("Call antrian error:", err);
        alert("Gagal mengirim nomor antrian. Periksa koneksi network dan pastikan server dashboard berjalan.");
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
