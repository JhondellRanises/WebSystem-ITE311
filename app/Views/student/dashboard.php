<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<div class="container-fluid mt-4">
    <!-- Welcome Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="fw-bold mb-2">Student Dashboard</h1>
            <p class="text-muted">Welcome back, <strong><?= esc($user_name) ?></strong>! Here's your learning overview.</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <!-- Enrolled Courses Card -->
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100 bg-success text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Enrolled Courses</h6>
                            <h3 class="mb-0"><?= $approvedEnrollments ?></h3>
                        </div>
                        <div class="fs-1 opacity-50">📚</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Requests Card -->
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100 bg-warning text-dark">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Pending Requests</h6>
                            <h3 class="mb-0"><?= $pendingEnrollments ?></h3>
                        </div>
                        <div class="fs-1 opacity-50">⏳</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rejected Enrollments Card -->
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100 bg-danger text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Rejected</h6>
                            <h3 class="mb-0"><?= $rejectedEnrollments ?></h3>
                        </div>
                        <div class="fs-1 opacity-50">❌</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Available Courses Card -->
        <div class="col-md-3 col-sm-6 mb-3">
            <div class="card border-0 shadow-sm h-100 bg-info text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="card-title mb-1">Available Courses</h6>
                            <h3 class="mb-0"><?= $availableCoursesCount ?></h3>
                        </div>
                        <div class="fs-1 opacity-50">🔍</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Enrolled Courses and Pending Requests -->
    <div class="row mb-4">
        <!-- Enrolled Courses -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0">📖 My Enrolled Courses</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($enrolledCourses)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($enrolledCourses as $course): ?>
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
                            <p>You are not enrolled in any courses yet.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Pending Enrollment Requests -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light border-bottom">
                    <h5 class="mb-0">⏳ Pending Enrollment Requests</h5>
                </div>
                <div class="card-body p-0">
                    <?php if (!empty($pendingCourses)): ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($pendingCourses as $course): ?>
                                <div class="list-group-item">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h6 class="mb-1"><?= esc($course['title']) ?></h6>
                                            <small class="text-muted">Instructor: <?= esc($course['instructor_name'] ?? 'N/A') ?></small>
                                        </div>
                                        <div class="d-flex gap-2 align-items-center">
                                            <span class="badge bg-warning text-dark">Awaiting Approval</span>
                                            <a href="/student/courses" class="btn btn-sm btn-outline-secondary">View Courses</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="p-3 text-center text-muted">
                            <p>No pending enrollment requests.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<?= $this->endSection() ?>
