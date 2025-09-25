<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="container mt-5">
    <h2 class="mb-4 text-center fw-bold">Dashboard</h2>

    <?php if(session()->getFlashdata('success')): ?>
        <div class="alert alert-success text-center"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if(session()->getFlashdata('error')): ?>
        <div class="alert alert-danger text-center"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <div class="card shadow-lg mx-auto" style="max-width: 700px; border-radius: 15px;">
        <div class="card-body text-center p-5">
            <h3 class="card-title mb-3">
                Welcome, <span class="text-primary text-capitalize"><?= esc($user_name) ?></span> 🎉
            </h3>
            <p class="text-muted">Role: <strong class="text-capitalize"><?= esc($user_role) ?></strong></p>
            
            <hr class="my-4">

            <?php if($user_role === 'admin'): ?>
                <a href="<?= site_url('admin/manage-users') ?>" class="btn btn-primary px-4 py-2 shadow-sm">Manage Users</a>
                <a href="<?= site_url('admin/manage-courses') ?>" class="btn btn-secondary px-4 py-2 shadow-sm">Manage Courses</a>
            <?php elseif($user_role === 'teacher'): ?>
                <a href="<?= site_url('teacher/courses') ?>" class="btn btn-primary px-4 py-2 shadow-sm">My Courses</a>
                <a href="<?= site_url('teacher/assignments') ?>" class="btn btn-secondary px-4 py-2 shadow-sm">Assignments</a>
                <a href="<?= site_url('teacher/students') ?>" class="btn btn-success px-4 py-2 shadow-sm">Manage Students</a>
            <?php elseif($user_role === 'student'): ?>
                <a href="<?= site_url('student/courses') ?>" class="btn btn-primary px-4 py-2 shadow-sm">My Enrolled Courses</a>
                <a href="<?= site_url('student/grades') ?>" class="btn btn-secondary px-4 py-2 shadow-sm">View Grades</a>
            <?php endif; ?>

            <hr class="my-4">
        </div>
    </div>
</div>
<?= $this->endSection() ?>
