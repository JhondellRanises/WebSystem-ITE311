<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<div class="container-fluid mt-4">
    <!-- Welcome Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="fw-bold mb-2">Admin Dashboard</h1>
            <p class="text-muted">Welcome back, <strong><?= esc($user_name) ?></strong>! Here's your system overview.</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <!-- Total Users Card -->
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100 bg-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Total Users</h6>
                            <h3 class="mb-0"><?= $totalUsers ?></h3>
                        </div>
                        <div class="fs-1 opacity-50">👥</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Teachers Card -->
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100 bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Teachers</h6>
                            <h3 class="mb-0"><?= $totalTeachers ?></h3>
                        </div>
                        <div class="fs-1 opacity-50">👨‍🏫</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Students Card -->
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100 bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Students</h6>
                            <h3 class="mb-0"><?= $totalStudents ?></h3>
                        </div>
                        <div class="fs-1 opacity-50">🎓</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Courses Card -->
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100 bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Courses</h6>
                            <h3 class="mb-0"><?= $totalCourses ?></h3>
                        </div>
                        <div class="fs-1 opacity-50">📚</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enrollment Statistics -->
    <div class="row mb-4">
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-2">Total Enrollments</h6>
                    <h3 class="mb-0 text-primary"><?= $totalEnrollments ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-2">Approved</h6>
                    <h3 class="mb-0 text-success"><?= $approvedEnrollments ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-2">Pending</h6>
                    <h3 class="mb-0 text-warning"><?= $pendingEnrollments ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <h6 class="card-title text-muted mb-2">Materials</h6>
                    <h3 class="mb-0 text-info"><?= $totalMaterials ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Courses -->
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0">📖 Recent Courses</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($recentCourses)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recentCourses as $course): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?= esc($course['title']) ?></h6>
                                            <small class="text-muted">Instructor: <?= esc($course['instructor_name'] ?? 'N/A') ?></small>
                                        </div>
                                            </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="p-3 text-center text-muted">
                            <p>No courses yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Recent Enrollments -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0">📝 Recent Enrollments</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($recentEnrollments)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($recentEnrollments as $enrollment): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?= esc($enrollment['student_name']) ?></h6>
                                            <small class="text-muted"><?= esc($enrollment['course_title']) ?></small>
                                        </div>
                                        <div class="d-flex gap-2 align-items-center">
                                            <span class="badge bg-<?= $enrollment['status'] === 'approved' ? 'success' : 'warning' ?>">
                                                <?= ucfirst(esc($enrollment['status'])) ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="p-3 text-center text-muted">
                            <p>No enrollments yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
