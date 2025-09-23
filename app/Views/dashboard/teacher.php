<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="container mt-5">
    <h2 class="mb-4 text-center fw-bold">Teacher Dashboard</h2>

    <?php if(session()->getFlashdata('success')): ?>
        <div class="alert alert-success text-center"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if(session()->getFlashdata('error')): ?>
        <div class="alert alert-danger text-center"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <!-- Welcome Card -->
    <div class="card shadow-lg mx-auto" style="max-width: 700px; border-radius: 15px;">
        <div class="card-body text-center p-5">
            <h3 class="card-title mb-3">Welcome, <span class="text-primary">Teacher</span> 👩‍🏫</h3>
            <p class="fs-4 mb-1"><strong><?= esc($user_name) ?></strong></p>
            <p class="text-muted">Role: <strong class="text-capitalize"><?= esc($user_role) ?></strong></p>
            
            <hr class="my-4">

            <!-- Teacher Feature Buttons -->
            <div class="d-flex flex-wrap justify-content-center gap-3">
                <a href="#" class="btn btn-primary px-4 py-2 shadow-sm">
                    <i class="bi bi-journal-bookmark"></i> My Courses
                </a>
                <a href="#" class="btn btn-secondary px-4 py-2 shadow-sm">
                    <i class="bi bi-file-earmark-text"></i> Assignments
                </a>
                <a href="#" class="btn btn-success px-4 py-2 shadow-sm">
                    <i class="bi bi-people"></i> Manage Students
                </a>
            </div>

            <hr class="my-4">

            <a href="<?= site_url('logout') ?>" class="btn btn-dark px-4 py-2 shadow-sm">
                <i class="bi bi-box-arrow-right"></i> Logout
            </a>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
