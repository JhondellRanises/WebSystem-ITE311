<!-- app/Views/templates/header.php -->
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
