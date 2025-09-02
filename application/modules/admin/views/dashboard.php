<?php $this->load->view('layouts/admin_header'); ?>

<!-- Header -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3>Statistik Kunjungan</h3>
</div>

<!-- Statistik Bubble -->
<div class="row text-white mb-4">
    <div class="col-md-4">
        <div class="card bg-info shadow">
            <div class="card-body text-center">
                <h5 class="card-title">Hari Ini</h5>
                <h2><?= $total_today ?></h2>
                <p class="mb-0">Jumlah kunjungan hari ini</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-warning shadow">
            <div class="card-body text-center">
                <h5 class="card-title">Bulan Ini</h5>
                <h2><?= $total_month ?></h2>
                <p class="mb-0">Total kunjungan bulan ini</p>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card bg-success shadow">
            <div class="card-body text-center">
                <h5 class="card-title">Keseluruhan</h5>
                <h2><?= $total_all ?></h2>
                <p class="mb-0">Total kunjungan seluruh waktu</p>
            </div>
        </div>
    </div>
</div>

<!-- Statistik Tamu Unik -->
<!-- Statistik Tamu Unik -->
<div class="row text-white mb-4">
    <div class="col-md-4">
        <div class="card bg-primary shadow">
            <div class="card-body text-center">
                <h5 class="card-title">Tamu Unik</h5>
                <h2><?= $total_unique ?></h2>
                <p class="mb-0">Jumlah tamu unik yang pernah berkunjung</p>
            </div>
        </div>
    </div>
</div>


<!-- Kalender Kunjungan -->
<div class="row mb-4">
    <div class="col">
        <div class="card shadow">
            <div class="card-header bg-secondary text-white">
                <strong>Kalender Kunjungan</strong>
            </div>
            <div class="card-body">
                <div id="calendar" style="min-height: 500px;"></div>
                <hr>
                <div class="mt-4">
                    <h6><strong>Legend Warna Jenis Layanan:</strong></h6>
                    <div class="d-flex flex-wrap gap-3 mt-2">
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle me-2" style="width: 20px; height: 20px; background-color: #4e73df;"></div>
                            <span>Perpustakaan</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle me-2" style="width: 20px; height: 20px; background-color: #1cc88a;"></div>
                            <span>Konsultasi Statistik</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle me-2" style="width: 20px; height: 20px; background-color: #36b9cc;"></div>
                            <span>Rekomendasi Kegiatan Statistik</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle me-2" style="width: 20px; height: 20px; background-color: #f6c23e;"></div>
                            <span>Penjualan Produk Statistik</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle me-2" style="width: 20px; height: 20px; background-color: #e74a3b;"></div>
                            <span>Keperluan Pimpinan</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle me-2" style="width: 20px; height: 20px; background-color: #6f42c1;"></div>
                            <span>Lainnya</span>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="rounded-circle me-2" style="width: 20px; height: 20px; background-color: #6c757d;"></div>
                            <span>Tidak Diketahui</span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- FullCalendar Assets (versi stabil) -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 'auto',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: ''
        },
        events: <?= json_encode($calendar_events ?? []) ?>,
        eventColor: '#0d6efd'
    });

    calendar.render();
});
</script>
