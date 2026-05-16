<?php $active = $this->uri->segment(2); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Panel</title>
    <script>
        (function () {
            var pref = localStorage.getItem('adminTheme') || 'light';
            var theme = (pref === 'dark') ? 'dark' : 'light';
            document.documentElement.setAttribute('data-bs-theme', theme);
            document.documentElement.setAttribute('data-admin-theme-pref', theme);
        })();
    </script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/assets/admin/css/admin.css?v=20251221" rel="stylesheet">
    <style>
        body { overflow-x: hidden; }
        .sidebar {
            min-height: 100vh;
            background: var(--bs-white);
            color: var(--bs-dark);
            padding: 0;
        }

        [data-bs-theme="dark"] .sidebar {
            background: var(--bs-black);
            color: var(--bs-light);
        }
        .sidebar a {
            color: inherit;
            text-decoration: none;
            display: block;
            padding: 8px 0;
        }
        .sidebar a:hover {
            background-color: rgba(var(--bs-body-color-rgb), 0.08);
            border-radius: 5px;
        }
        .sidebar a.active {
            background-color: rgba(var(--bs-primary-rgb), 0.12);
            border-left: 4px solid var(--bs-primary);
            padding-left: 16px;
            font-weight: bold;
            color: var(--bs-primary);
            border-radius: 5px;
        }
        .content { padding: 30px; }
        @media (max-width: 767.98px) {
            .content { padding: 16px; }
        }

        /* Theme toggle button - bottom right corner */
        .admin-theme-fab {
            position: fixed !important;
            bottom: 16px !important;
            right: 16px !important;
            z-index: 1050 !important;
        }
    </style>
</head>
<body class="bg-body admin-layout">

<div class="admin-theme-fab">
    <button type="button" class="btn btn-sm btn-outline-secondary admin-theme-toggle" id="adminThemeFab" data-theme-label="full">Theme: Light</button>
</div>

<div class="admin-sidebar-show-fab shadow-sm">
    <button type="button" class="btn btn-sm btn-outline-secondary admin-sidebar-toggle" id="adminSidebarShowFab" aria-label="Expand sidebar" title="Expand sidebar">&#9776;</button>
</div>

<div class="container-fluid">
<div class="row">
    <div class="col-auto sidebar offcanvas-md offcanvas-start" tabindex="-1" id="adminSidebar" aria-labelledby="adminSidebarLabel">
        <div class="offcanvas-header d-md-none">
            <h5 class="offcanvas-title" id="adminSidebarLabel">Admin Panel</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Tutup"></button>
        </div>
        <div class="offcanvas-body p-0 d-flex flex-column">
            <div class="p-3 flex-grow-1">
                <div class="d-flex align-items-center justify-content-between">
                    <h4 class="m-0 d-none d-md-block">Admin Panel</h4>
                    <button type="button" class="btn btn-sm btn-outline-secondary admin-sidebar-toggle d-none d-md-inline-flex" id="adminSidebarToggle" aria-label="Collapse sidebar" title="Collapse sidebar">&#9776;</button>
                </div>
                <hr class="opacity-25">
                <a href="<?= site_url('admin/dashboard') ?>" class="<?= $active == 'dashboard' ? 'active' : '' ?>">Dashboard</a>
                <a href="<?= site_url('admin/daftar_tamu') ?>" class="<?= $active == 'daftar_tamu' ? 'active' : '' ?>">Daftar Tamu</a>
                <a href="<?= site_url('admin/antrian_konsultasi') ?>" class="<?= $active == 'antrian_konsultasi' ? 'active' : '' ?>">Antrian Konsultasi</a>
                <a href="<?= site_url('admin/daftar_kunjungan') ?>" class="<?= $active == 'daftar_kunjungan' ? 'active' : '' ?>">Daftar Kunjungan</a>
                <a href="<?= site_url('admin/tambah_kunjungan_manual') ?>" class="<?= $active == 'tambah_kunjungan_manual' ? 'active' : '' ?>">Tambah Kunjungan Manual</a>
            </div>
            <div class="p-3 border-top">
                <a href="<?= site_url('admin/logout') ?>" class="btn btn-sm btn-outline-danger w-100">
                    <i class="ti-power-off"></i> Logout
                </a>
            </div>
        </div>
    </div>
    <div class="col content">
        <div class="d-flex d-md-none justify-content-between align-items-center mb-3">
            <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminSidebar" aria-controls="adminSidebar">Menu</button>
        </div>
