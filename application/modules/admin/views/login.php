<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin</title>
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
        /* Fallback: ensure theme toggle truly floats bottom-left */
        .admin-theme-fab {
            position: fixed;
            left: 16px;
            bottom: 16px;
            left: calc(16px + env(safe-area-inset-left));
            bottom: calc(16px + env(safe-area-inset-bottom));
            z-index: 1046;
        }
        .admin-theme-fab .btn {
            background-color: var(--bs-body-bg);
            background-color: rgba(var(--bs-body-bg-rgb), 0.92);
            border-color: var(--bs-border-color);
            color: var(--bs-body-color);
            font-weight: 600;
        }
    </style>
</head>
<body class="admin-auth-page">
    <div class="admin-theme-fab shadow-sm">
        <button type="button" class="btn btn-sm btn-outline-secondary admin-theme-toggle" id="adminThemeFabLogin" data-theme-label="full">Theme: Light</button>
    </div>
    <main class="admin-auth d-flex align-items-center py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 d-flex justify-content-center">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <div class="text-center mb-4">
                                <div class="fw-semibold fs-4">Admin Panel</div>
                                <div class="text-body-secondary">Silakan login untuk melanjutkan</div>
                            </div>

                            <?php if ($this->session->flashdata('error')): ?>
                                <div class="alert alert-danger" role="alert">
                                    <?= htmlspecialchars($this->session->flashdata('error'), ENT_QUOTES, 'UTF-8') ?>
                                </div>
                            <?php endif; ?>

                            <form action="<?= site_url('admin/login') ?>" method="post" class="needs-validation" novalidate>
                                <input type="hidden" name="<?= $this->security->get_csrf_token_name(); ?>" value="<?= $this->security->get_csrf_hash(); ?>">

                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input
                                        type="text"
                                        id="username"
                                        name="username"
                                        class="form-control"
                                        placeholder="Masukkan username"
                                        autocomplete="username"
                                        required
                                        autofocus
                                    >
                                    <div class="invalid-feedback">Username wajib diisi.</div>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
                                    <input
                                        type="password"
                                        id="password"
                                        name="password"
                                        class="form-control"
                                        placeholder="Masukkan password"
                                        autocomplete="current-password"
                                        required
                                    >
                                    <div class="invalid-feedback">Password wajib diisi.</div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100">Login</button>
                            </form>

                            <div class="text-center mt-3">
                                <small class="text-body-secondary">Buku Tamu</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Lightweight Bootstrap validation
        (function () {
            var forms = document.querySelectorAll('.needs-validation');
            Array.prototype.slice.call(forms).forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
    </script>

    <script src="/assets/admin/js/admin-ui.js?v=20251221"></script>
</body>
</html>
