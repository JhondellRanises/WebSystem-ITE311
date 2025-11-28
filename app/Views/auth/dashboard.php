<?= $this->extend('templates/header') ?>

<?= $this->section('content') ?>
<div class="container mt-5">
    <h2 class="mb-4 text-center fw-bold">Dashboard</h2>

    <!-- ✅ Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success text-center"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger text-center"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <!-- ✅ Welcome Card -->
    <div class="card shadow-lg mx-auto mb-4" style="max-width: 700px; border-radius: 15px;">
        <div class="card-body text-center p-5">
            <h3 class="card-title mb-3">
                Welcome, <span class="text-primary text-capitalize"><?= esc($user_name) ?></span> 🎉
            </h3>
            <p class="text-muted">
                Role: <strong class="text-capitalize"><?= esc($user_role) ?></strong>
            </p>
        </div>
    </div>

    <?php if ($user_role === 'student'): ?>
        <!-- ✅ Enrolled Courses -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white fw-bold">My Enrolled Courses</div>
            <ul class="list-group list-group-flush" id="enrolledCourses">
                <?php if (!empty($enrolledCourses)): ?>
                    <?php foreach ($enrolledCourses as $course): ?>
                        <li class="list-group-item"><?= esc($course['title']) ?></li>
                    <?php endforeach; ?>
                <?php else: ?>
                    <li class="list-group-item text-muted no-enrollment-msg">
                        You are not enrolled in any course yet.
                    </li>
                <?php endif; ?>
            </ul>
        </div>

    <?php endif; ?>
</div>
<?= $this->endSection() ?>
