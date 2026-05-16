<?php $this->load->view('layouts/admin_header'); ?>

<div class="d-flex flex-column flex-lg-row gap-2 justify-content-between align-items-lg-center mb-3">
    <div>
        <h3 class="mb-1">Daftar Tamu</h3>
        <div class="small text-body-secondary">
            Total: <?= isset($tamu) ? (int) count($tamu) : 0 ?> data
        </div>
    </div>
    <div class="d-flex flex-nowrap gap-2 align-items-center overflow-auto">
        <div class="input-group input-group-sm flex-grow-1" style="min-width: 220px; max-width: 520px;">
            <span class="input-group-text">Cari</span>
            <input type="search" class="form-control" id="tamuSearch" placeholder="Nama / email / instansi…" aria-label="Cari tamu">
        </div>
    </div>
</div>

<!-- Flash message -->
<?php if ($this->session->flashdata('success')): ?>
    <div class="alert alert-success">
        <?= htmlspecialchars($this->session->flashdata('success'), ENT_QUOTES, 'UTF-8') ?>
    </div>
<?php endif; ?>

<div class="card shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-sm table-striped table-hover align-middle mb-0" id="tamuTable">
                <thead>
                    <tr>
                        <th scope="col" class="text-center" style="width: 56px;">No</th>
                        <th scope="col" class="text-center" style="min-width: 220px;">Nama</th>
                        <th scope="col" class="text-center" style="min-width: 220px;">Email</th>
                        <th scope="col" class="text-center text-nowrap" style="width: 150px;">Jenis Kelamin</th>
                        <th scope="col" class="text-center text-nowrap" style="width: 110px;">Pendidikan</th>
                        <th scope="col" class="text-center" style="min-width: 220px;">Instansi</th>
                        <th scope="col" class="text-center text-nowrap" style="width: 160px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($tamu)): ?>
                        <?php $no = 1; foreach ($tamu as $row): ?>
                            <tr class="tamu-row" data-search="<?= htmlspecialchars(strtolower(trim(($row->nama ?? '') . ' ' . ($row->email ?? '') . ' ' . ($row->nama_instansi ?? ''))), ENT_QUOTES, 'UTF-8') ?>">
                                <td class="text-body-secondary tamu-no text-center"><?= $no++ ?></td>
                                <td class="fw-semibold"><?= htmlspecialchars($row->nama ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td><?= htmlspecialchars($row->email ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-nowrap text-center"><?= htmlspecialchars($row->jeniskelamin ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-nowrap text-center">
                                    <?php
                                        switch ((string) ($row->pendidikan ?? '')) {
                                            case '1': echo '≤ SLTA'; break;
                                            case '2': echo 'D1/D2/D3'; break;
                                            case '3': echo 'D4/S1'; break;
                                            case '4': echo 'S2'; break;
                                            case '5': echo 'S3'; break;
                                            default: echo '-';
                                        }
                                    ?>
                                </td>
                                <td><?= htmlspecialchars($row->nama_instansi ?? '-', ENT_QUOTES, 'UTF-8') ?></td>
                                <td class="text-nowrap text-center">
                                    <div class="btn-group btn-group-sm" role="group" aria-label="Aksi">
                                        <a href="<?= site_url('admin/edit/' . $row->id_user) ?>" class="btn btn-outline-secondary">Edit</a>
                                        <a href="<?= site_url('admin/delete/' . $row->id_user) ?>" class="btn btn-outline-danger" onclick="return confirm('Yakin ingin menghapus data ini?')">Hapus</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center text-body-secondary py-4">Belum ada data tamu.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="d-flex flex-column flex-md-row gap-2 justify-content-between align-items-md-center mt-3">
    <div class="small text-body-secondary" id="tamuPagingInfo"></div>
    <div class="d-flex gap-2 align-items-center justify-content-md-end">
        <div class="input-group input-group-sm" style="width: 220px;">
            <span class="input-group-text">Per halaman</span>
            <select class="form-select" id="tamuPerPage" aria-label="Jumlah data per halaman">
                <option value="10">10</option>
                <option value="20" selected>20</option>
                <option value="50">50</option>
                <option value="100">100</option>
                <option value="500">500</option>
                <option value="1000">1000</option>
            </select>
        </div>
        <nav aria-label="Paging tamu">
            <ul class="pagination pagination-sm mb-0" id="tamuPagination"></ul>
        </nav>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchEl = document.getElementById('tamuSearch');
    const perPageEl = document.getElementById('tamuPerPage');
    const rows = Array.prototype.slice.call(document.querySelectorAll('#tamuTable tbody .tamu-row'));
    const pagingInfoEl = document.getElementById('tamuPagingInfo');
    const paginationEl = document.getElementById('tamuPagination');

    let currentPage = 1;

    function getPerPage() {
        const v = perPageEl ? Number(perPageEl.value) : 20;
        return (Number.isFinite(v) && v > 0) ? v : 20;
    }

    function getQuery() {
        return (searchEl && searchEl.value ? searchEl.value : '').toString().trim().toLowerCase();
    }

    function getFilteredRows() {
        const q = getQuery();
        if (!q) return rows.slice();
        return rows.filter(function (r) {
            const hay = (r.getAttribute('data-search') || '');
            return hay.indexOf(q) !== -1;
        });
    }

    function setAllRowsHidden() {
        rows.forEach(function (r) { r.style.display = 'none'; });
    }

    function ensurePageBounds(pageCount) {
        if (pageCount < 1) pageCount = 1;
        if (currentPage < 1) currentPage = 1;
        if (currentPage > pageCount) currentPage = pageCount;
    }

    function formatRangeText(startIndex, endIndex, total) {
        if (total <= 0) return 'Tidak ada data yang cocok.';
        return 'Menampilkan ' + (startIndex + 1) + '–' + endIndex + ' dari ' + total + ' data';
    }

    function pageList(pageCount, current, maxVisible) {
        const pages = [];
        if (pageCount <= maxVisible) {
            for (let i = 1; i <= pageCount; i++) pages.push(i);
            return pages;
        }

        const side = Math.floor((maxVisible - 3) / 2); // reserve first,last, and 2 ellipses
        let start = Math.max(2, current - side);
        let end = Math.min(pageCount - 1, current + side);

        if (current <= (2 + side)) {
            start = 2;
            end = 2 + (maxVisible - 3);
        }
        if (current >= (pageCount - 1 - side)) {
            end = pageCount - 1;
            start = pageCount - 1 - (maxVisible - 3);
        }

        pages.push(1);
        if (start > 2) pages.push('…');
        for (let i = start; i <= end; i++) pages.push(i);
        if (end < pageCount - 1) pages.push('…');
        pages.push(pageCount);
        return pages;
    }

    function buildPageItem(label, page, disabled, active) {
        const li = document.createElement('li');
        li.className = 'page-item' + (disabled ? ' disabled' : '') + (active ? ' active' : '');
        const a = document.createElement('a');
        a.className = 'page-link';
        a.href = '#';
        a.textContent = label;
        if (!disabled && !active) {
            a.addEventListener('click', function (e) {
                e.preventDefault();
                currentPage = page;
                render();
            });
        } else {
            a.addEventListener('click', function (e) { e.preventDefault(); });
        }
        li.appendChild(a);
        return li;
    }

    function render() {
        const perPage = getPerPage();
        const filtered = getFilteredRows();
        const total = filtered.length;
        const pageCount = Math.max(1, Math.ceil(total / perPage));

        ensurePageBounds(pageCount);
        const startIndex = total ? (perPage * (currentPage - 1)) : 0;
        const endIndexExclusive = total ? Math.min(total, startIndex + perPage) : 0;

        setAllRowsHidden();
        for (let i = startIndex; i < endIndexExclusive; i++) {
            const r = filtered[i];
            r.style.display = '';
            const noEl = r.querySelector('.tamu-no');
            if (noEl) noEl.textContent = String(i + 1);
        }

        if (pagingInfoEl) {
            pagingInfoEl.textContent = formatRangeText(startIndex, endIndexExclusive, total);
        }

        if (paginationEl) {
            paginationEl.innerHTML = '';
            if (total > 0) {
                paginationEl.appendChild(buildPageItem('«', Math.max(1, currentPage - 1), currentPage === 1, false));
                const list = pageList(pageCount, currentPage, 7);
                list.forEach(function (p) {
                    if (p === '…') {
                        const li = document.createElement('li');
                        li.className = 'page-item disabled';
                        const span = document.createElement('span');
                        span.className = 'page-link';
                        span.textContent = '…';
                        li.appendChild(span);
                        paginationEl.appendChild(li);
                    } else {
                        paginationEl.appendChild(buildPageItem(String(p), p, false, p === currentPage));
                    }
                });
                paginationEl.appendChild(buildPageItem('»', Math.min(pageCount, currentPage + 1), currentPage === pageCount, false));
            }
        }
    }

    if (searchEl) {
        searchEl.addEventListener('input', function () {
            currentPage = 1;
            render();
        });
    }
    if (perPageEl) {
        perPageEl.addEventListener('change', function () {
            currentPage = 1;
            render();
        });
    }

    render();
});
</script>

<?php $this->load->view('layouts/admin_footer'); ?>
