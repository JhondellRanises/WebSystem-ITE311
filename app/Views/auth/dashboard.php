<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<h2 class="mb-4 text-center">Dashboard</h2>

<?php if(session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
<?php endif; ?>
<?php if(session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
<?php endif; ?>

<!-- Welcome Card -->
<div class="card shadow-lg mx-auto mb-5" style="max-width: 600px; border-radius: 15px;">
    <div class="card-body text-center p-5">
        <h3 class="card-title mb-3">Welcome!</h3>
        <p class="fs-4 mb-4">
        <strong><?= esc($user_name) ?></strong>
        </p>
        <p class="text-muted mb-4">Role: <strong><?= esc($user_role) ?></strong></p>
        <a href="<?= site_url('logout') ?>" class="btn btn-dark btn-sm px-4 py-2 shadow-sm">
            <i class="bi bi-box-arrow-right"></i> Logout
        </a>
    </div>
</div>

<?= $this->endSection() ?>
