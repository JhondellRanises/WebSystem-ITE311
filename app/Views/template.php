<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ITE311-RANISES</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

    <!-- Dynamic Navigation Bar (always visible, role-aware) -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?= base_url('/') ?>">ITE311-RANISES</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('home') ?>">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('about') ?>">About</a></li>
                    <li class="nav-item"><a class="nav-link" href="<?= base_url('contact') ?>">Contact</a></li>

                    <?php if(session()->has('user_role')): ?>
                        <?php $role = session()->get('user_role'); ?>
                        <?php if($role === 'admin'): ?>
                            <li class="nav-item"><a class="nav-link" href="<?= base_url('dashboard') ?>">Admin Dashboard</a></li>
                        <?php elseif($role === 'teacher'): ?>
                            <li class="nav-item"><a class="nav-link" href="<?= base_url('dashboard') ?>">Teacher Dashboard</a></li>
                        <?php elseif($role === 'student'): ?>
                            <li class="nav-item"><a class="nav-link" href="<?= base_url('dashboard') ?>">Student Dashboard</a></li>
                        <?php endif; ?>
                    <?php endif; ?>
                </ul>

                <ul class="navbar-nav ms-auto">
                    <?php if(session()->has('logged_in')): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= base_url('logout') ?>">Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="<?= base_url('register') ?>">Register</a></li>
                        <li class="nav-item"><a class="nav-link" href="<?= base_url('login') ?>">Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Dynamic Content Section -->
    <div class="container mt-5">
        <?= $this->renderSection('content') ?>
    </div>

    <!-- Bootstrap JS CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
