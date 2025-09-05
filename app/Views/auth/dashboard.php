<!DOCTYPE html>
<html>
<head>
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h2 class="mb-4 text-center">Dashboard</h2>
        <div class="card shadow-sm mx-auto text-center" style="max-width: 450px;">
            <div class="card-body">
                <p class="fs-4 mb-4">
                    Welcome, <strong><?= esc($name) ?></strong>!
                </p>
                <p class="text-muted">You are now logged in to your account.</p>
                <a href="<?= base_url('logout') ?>" class="btn btn-dark btn-sm mt-3">
                    <i class="bi bi-box-arrow-right"></i> Logout
                </a>
            </div>
        </div>
    </div>
</body>
</html>
