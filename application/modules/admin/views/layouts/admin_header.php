<?php $active = $this->uri->segment(2); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { overflow-x: hidden; }
        .sidebar {
            min-height: 100vh;
            background: #0d6efd;
            color: white;
            padding: 20px;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 8px 0;
        }
        .sidebar a:hover {
            background-color: rgba(255, 255, 255, 0.2);
            border-radius: 5px;
        }
        .sidebar a.active {
            background-color: rgba(255, 255, 255, 0.2);
            border-left: 4px solid #ffc107;
            padding-left: 16px;
            font-weight: bold;
            color: #ffc107;
            border-radius: 5px;
        }
        .content { padding: 30px; }
    </style>
</head>
<body class="bg-light">

<div class="container-fluid">
<div class="row">
    <div class="col-md-2 sidebar">
        <h4>Admin Panel</h4>
        <hr>
        <a href="<?= site_url('admin/dashboard') ?>" class="<?= $active == 'dashboard' ? 'active' : '' ?>">Dashboard</a>
        <a href="<?= site_url('admin/daftar_tamu') ?>" class="<?= $active == 'daftar_tamu' ? 'active' : '' ?>">Daftar Tamu</a>
        <a href="<?= site_url('admin/tambah') ?>" class="<?= $active == 'tambah' ? 'active' : '' ?>">Tambah Tamu</a>
        <a href="<?= site_url('admin/antrian_konsultasi') ?>" class="<?= $active == 'antrian_konsultasi' ? 'active' : '' ?>">Antrian Konsultasi</a>
        <a href="<?= site_url('admin/daftar_kunjungan') ?>" class="<?= $active == 'daftar_kunjungan' ? 'active' : '' ?>">Daftar Kunjungan</a>
        <a href="<?= site_url('admin/logout') ?>">Logout</a>
    </div>
    <div class="col-md-10 content">
