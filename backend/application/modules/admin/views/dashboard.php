<?php $this->load->view('layouts/admin_header'); ?>

<!-- Header -->
<div class="mb-3">
    <div class="d-flex flex-column flex-lg-row gap-2 justify-content-between align-items-lg-center">
        <div>
            <h3 class="mb-1">Statistik Kunjungan</h3>
            <div class="small text-body-secondary" id="dashboardFilterLabel">Filter: tidak ada</div>
        </div>
        <div class="d-flex flex-wrap gap-2 align-items-center">
            <div class="input-group input-group-sm" style="max-width: 520px;">
                <span class="input-group-text">Mulai</span>
                <input type="date" class="form-control" id="dashboardStart" aria-label="Tanggal mulai">
                <span class="input-group-text">Sampai</span>
                <input type="date" class="form-control" id="dashboardEnd" aria-label="Tanggal sampai">
                <button type="button" class="btn btn-outline-secondary" id="dashboardApply">Terapkan</button>
                <button type="button" class="btn btn-outline-secondary" id="dashboardReset" disabled>Reset</button>
            </div>
        </div>
    </div>
</div>

<!-- Cards statistik (filter-aware) -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <div class="fw-semibold">Total Kunjungan</div>
                <div class="display-6 fw-bold" id="statTotal">0</div>
                <div class="text-body-secondary">Dalam periode terpilih</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <div class="fw-semibold">Tamu Unik</div>
                <div class="display-6 fw-bold" id="statUnique">0</div>
                <div class="text-body-secondary">Berdasarkan nama pada event</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <div class="fw-semibold">Jumlah Hari</div>
                <div class="display-6 fw-bold" id="statDays">0</div>
                <div class="text-body-secondary">Rentang hari pada filter</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <div class="fw-semibold">Rata-rata / Hari</div>
                <div class="display-6 fw-bold" id="statAvg">0</div>
                <div class="text-body-secondary">Total dibagi jumlah hari</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <div class="fw-semibold">Hari Tersibuk</div>
                <div class="fs-5 fw-bold" id="statBusiestDate">-</div>
                <div class="text-body-secondary"><span id="statBusiestCount">0</span> kunjungan</div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <div class="fw-semibold">Periode Aktif</div>
                <div class="fs-5 fw-bold" id="statRange">Semua data</div>
                <div class="text-body-secondary">Kosongkan tanggal untuk tanpa filter</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <div class="fw-semibold">Selesai</div>
                <div class="display-6 fw-bold" id="statDone">0</div>
                <div class="text-body-secondary">Jumlah kunjungan selesai</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <div class="fw-semibold">Antri</div>
                <div class="display-6 fw-bold" id="statQueue">0</div>
                <div class="text-body-secondary">Jumlah kunjungan status antri</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <div class="fw-semibold">Tingkat Selesai</div>
                <div class="display-6 fw-bold" id="statCompletion">0%</div>
                <div class="text-body-secondary">Selesai ÷ (Selesai + Antri)</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <div class="fw-semibold">Rata-rata Durasi</div>
                <div class="display-6 fw-bold" id="statAvgDuration">-</div>
                <div class="text-body-secondary">Khusus data yang punya durasi</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <div class="fw-semibold">Layanan Terbanyak</div>
                <div class="fs-5 fw-bold" id="statTopService">-</div>
                <div class="text-body-secondary"><span id="statTopServiceCount">0</span> kunjungan</div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <div class="fw-semibold">Instansi Terbanyak</div>
                <div class="fs-5 fw-bold" id="statTopInstansi">-</div>
                <div class="text-body-secondary"><span id="statTopInstansiCount">0</span> kunjungan</div>
            </div>
        </div>
    </div>
</div>


<!-- Kalender Kunjungan -->
<div class="row mb-4">
    <div class="col">
        <div class="card shadow">
            <div class="card-header">
                <strong>Kalender Kunjungan</strong>
            </div>
            <div class="card-body">
                <div id="calendar" style="min-height: 500px;"></div>
                <hr>
                <div class="mt-4">
                    <h6><strong>Jenis Layanan:</strong></h6>
                    <div class="text-body-secondary mt-2">
                        Perpustakaan • Konsultasi Statistik • Rekomendasi Kegiatan Statistik • Penjualan Produk Statistik • Keperluan Pimpinan • Lainnya
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

    const startEl = document.getElementById('dashboardStart');
    const endEl = document.getElementById('dashboardEnd');
    const applyBtn = document.getElementById('dashboardApply');
    const resetBtn = document.getElementById('dashboardReset');
    const filterLabelEl = document.getElementById('dashboardFilterLabel');

    const statTotalEl = document.getElementById('statTotal');
    const statUniqueEl = document.getElementById('statUnique');
    const statDaysEl = document.getElementById('statDays');
    const statAvgEl = document.getElementById('statAvg');
    const statBusiestDateEl = document.getElementById('statBusiestDate');
    const statBusiestCountEl = document.getElementById('statBusiestCount');
    const statRangeEl = document.getElementById('statRange');

    const statDoneEl = document.getElementById('statDone');
    const statQueueEl = document.getElementById('statQueue');
    const statCompletionEl = document.getElementById('statCompletion');
    const statAvgDurationEl = document.getElementById('statAvgDuration');
    const statTopServiceEl = document.getElementById('statTopService');
    const statTopServiceCountEl = document.getElementById('statTopServiceCount');
    const statTopInstansiEl = document.getElementById('statTopInstansi');
    const statTopInstansiCountEl = document.getElementById('statTopInstansiCount');

    const allEvents = <?= json_encode($calendar_events ?? []) ?>;

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        height: 'auto',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: ''
        },
        events: allEvents,
        eventColor: '#0d6efd'
    });

    calendar.render();

    // Calendar should always show all events (no filter on calendar)
    setCalendarEvents(allEvents);

    function parseDateInput(value) {
        if (!value) return null;
        // value: YYYY-MM-DD
        const parts = value.split('-');
        if (parts.length !== 3) return null;
        const y = Number(parts[0]);
        const m = Number(parts[1]);
        const d = Number(parts[2]);
        if (!y || !m || !d) return null;
        // Use local date, midnight
        return new Date(y, m - 1, d, 0, 0, 0, 0);
    }

    function fmtDate(dateObj) {
        const y = dateObj.getFullYear();
        const m = String(dateObj.getMonth() + 1).padStart(2, '0');
        const d = String(dateObj.getDate()).padStart(2, '0');
        return `${y}-${m}-${d}`;
    }

    function setCalendarEvents(events) {
        calendar.removeAllEvents();
        calendar.addEventSource(events);
    }

    function daysBetweenInclusive(start, end) {
        const msPerDay = 24 * 60 * 60 * 1000;
        const startDay = new Date(start.getFullYear(), start.getMonth(), start.getDate(), 0, 0, 0, 0);
        const endDay = new Date(end.getFullYear(), end.getMonth(), end.getDate(), 0, 0, 0, 0);
        return Math.floor((endDay - startDay) / msPerDay) + 1;
    }

    function computeStats(events, start, end) {
        const total = events.length;
        const unique = new Set(events.map(function (ev) { return (ev.title || '').trim(); }).filter(Boolean)).size;

        let done = 0;
        let queue = 0;
        let durSum = 0;
        let durCount = 0;

        const countsByService = {};
        const countsByInstansi = {};

        const countsByDay = {};
        for (let i = 0; i < events.length; i++) {
            const key = events[i].start;
            if (!key) continue;
            countsByDay[key] = (countsByDay[key] || 0) + 1;

            const props = (events[i] && events[i].extendedProps) ? events[i].extendedProps : {};
            const status = (props.status || '').toString().toLowerCase();
            if (status === 'selesai') done++;
            if (status === 'antri') queue++;

            const dur = Number(props.durasi_detik);
            if (Number.isFinite(dur) && dur > 0) {
                durSum += dur;
                durCount++;
            }

            const layanan = (props.jenis_layanan || '').toString().trim();
            if (layanan) countsByService[layanan] = (countsByService[layanan] || 0) + 1;

            const instansi = (props.nama_instansi || '').toString().trim();
            if (instansi) countsByInstansi[instansi] = (countsByInstansi[instansi] || 0) + 1;
        }

        let busiestDate = '-';
        let busiestCount = 0;
        Object.keys(countsByDay).forEach(function (key) {
            const c = countsByDay[key];
            if (c > busiestCount) {
                busiestCount = c;
                busiestDate = key;
            }
        });

        let days = 0;
        if (start && end) {
            days = daysBetweenInclusive(start, end);
        } else if (start && !end) {
            // from start until max event date
            const dates = events.map(function (ev) { return parseDateInput(ev.start); }).filter(Boolean);
            const max = dates.length ? new Date(Math.max.apply(null, dates)) : start;
            days = daysBetweenInclusive(start, max);
        } else if (!start && end) {
            const dates = events.map(function (ev) { return parseDateInput(ev.start); }).filter(Boolean);
            const min = dates.length ? new Date(Math.min.apply(null, dates)) : end;
            days = daysBetweenInclusive(min, end);
        } else {
            // no filter: use min/max event range if available
            const dates = events.map(function (ev) { return parseDateInput(ev.start); }).filter(Boolean);
            if (dates.length) {
                const min = new Date(Math.min.apply(null, dates));
                const max = new Date(Math.max.apply(null, dates));
                days = daysBetweenInclusive(min, max);
            } else {
                days = 0;
            }
        }

        const avg = days > 0 ? (total / days) : 0;

        let topService = '-';
        let topServiceCount = 0;
        Object.keys(countsByService).forEach(function (k) {
            const c = countsByService[k];
            if (c > topServiceCount) {
                topServiceCount = c;
                topService = k;
            }
        });

        let topInstansi = '-';
        let topInstansiCount = 0;
        Object.keys(countsByInstansi).forEach(function (k) {
            const c = countsByInstansi[k];
            if (c > topInstansiCount) {
                topInstansiCount = c;
                topInstansi = k;
            }
        });

        const completionDenom = done + queue;
        const completionRate = completionDenom > 0 ? (done / completionDenom) : 0;
        const avgDurMin = durCount > 0 ? (durSum / durCount / 60) : null;

        return {
            total: total,
            unique: unique,
            days: days,
            avg: avg,
            busiestDate: busiestDate,
            busiestCount: busiestCount,
            done: done,
            queue: queue,
            completionRate: completionRate,
            avgDurMin: avgDurMin,
            topService: topService,
            topServiceCount: topServiceCount,
            topInstansi: topInstansi,
            topInstansiCount: topInstansiCount,
        };
    }

    function applyRangeFilter() {
        const start = parseDateInput(startEl.value);
        const end = parseDateInput(endEl.value);

        // Normalize end to inclusive day
        let endInclusive = end;
        if (endInclusive) {
            endInclusive = new Date(endInclusive.getFullYear(), endInclusive.getMonth(), endInclusive.getDate(), 23, 59, 59, 999);
        }

        const filtered = allEvents.filter(function (ev) {
            // ev.start is YYYY-MM-DD
            const evDate = parseDateInput(ev.start);
            if (!evDate) return false;
            if (start && evDate < start) return false;
            if (endInclusive && evDate > endInclusive) return false;
            return true;
        });

        // Cards
        const stats = computeStats(filtered, start, end);
        statTotalEl.textContent = String(stats.total);
        statUniqueEl.textContent = String(stats.unique);
        statDaysEl.textContent = String(stats.days);
        statAvgEl.textContent = stats.avg.toFixed(2);
        statBusiestDateEl.textContent = stats.busiestDate || '-';
        statBusiestCountEl.textContent = String(stats.busiestCount || 0);

        statDoneEl.textContent = String(stats.done || 0);
        statQueueEl.textContent = String(stats.queue || 0);
        statCompletionEl.textContent = (stats.completionRate * 100).toFixed(0) + '%';
        statAvgDurationEl.textContent = (stats.avgDurMin === null) ? '-' : (stats.avgDurMin.toFixed(1) + ' m');
        statTopServiceEl.textContent = stats.topService || '-';
        statTopServiceCountEl.textContent = String(stats.topServiceCount || 0);
        statTopInstansiEl.textContent = stats.topInstansi || '-';
        statTopInstansiCountEl.textContent = String(stats.topInstansiCount || 0);

        // Labels
        const labelParts = [];
        if (start) labelParts.push('Mulai: ' + fmtDate(start));
        if (end) labelParts.push('Sampai: ' + fmtDate(end));
        if (labelParts.length) {
            filterLabelEl.textContent = 'Filter: ' + labelParts.join(' • ');
            statRangeEl.textContent = labelParts.join(' s/d ');
            resetBtn.disabled = false;
        } else {
            filterLabelEl.textContent = 'Filter: tidak ada';
            statRangeEl.textContent = 'Semua data';
            resetBtn.disabled = true;
        }
    }

    applyBtn.addEventListener('click', function () {
        applyRangeFilter();
    });

    resetBtn.addEventListener('click', function () {
        startEl.value = '';
        endEl.value = '';
        applyRangeFilter();
    });

    // default (no filter)
    applyRangeFilter();
});
</script>

<?php $this->load->view('layouts/admin_footer'); ?>
