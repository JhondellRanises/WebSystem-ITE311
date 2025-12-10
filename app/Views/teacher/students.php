<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<div class="card shadow-sm mt-3">
  <div class="card-body">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
      <h2 class="fw-bold mb-2 mb-sm-0">Manage Students</h2>
    </div>

    <!-- Course Selection -->
    <div class="card mb-4">
      <div class="card-body">
        <form method="get" class="row g-3">
          <div class="col-md-5">
            <label class="form-label">Select Course</label>
            <select name="course_id" class="form-select" onchange="this.form.submit()">
              <option value="">-- Select Course to View Students --</option>
              <?php foreach ($courses as $course): ?>
                <option value="<?= $course['id'] ?>" <?= $selectedCourseId == $course['id'] ? 'selected' : '' ?>>
                  <?= esc($course['title']) ?> 
                  <?php if ($course['course_code']): ?>
                    (<?= esc($course['course_code']) ?>)
                  <?php endif; ?>
                  <?php if (session()->get('user_role') === 'admin'): ?>
                    - <?= esc($course['instructor_name'] ?? 'Unassigned') ?>
                  <?php endif; ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>
          <?php if ($selectedCourseId): ?>
            <div class="col-md-5">
              <label class="form-label">Search Students</label>
              <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="<?= esc($search ?? '') ?>">
              <input type="hidden" name="course_id" value="<?= $selectedCourseId ?>">
            </div>
            <div class="col-md-2">
              <label class="form-label">&nbsp;</label>
              <button type="submit" class="btn btn-primary w-100">Search</button>
            </div>
          <?php endif; ?>
        </form>
      </div>
    </div>

    <?php if ($selectedCourse): ?>
      <!-- Course Info -->
      <div class="alert alert-info mb-3">
        <h5 class="mb-2"><?= esc($selectedCourse['title']) ?></h5>
        <div class="row">
          <div class="col-md-6">
            <strong>Course Code:</strong> <?= esc($selectedCourse['course_code'] ?? 'N/A') ?><br>
            <strong>Department:</strong> <?= esc($selectedCourse['department'] ?? '') ?><br>
            <strong>Program:</strong> <?= esc($selectedCourse['program'] ?? '') ?>
          </div>
          <div class="col-md-6">
            <strong>Semester:</strong> <?= esc($selectedCourse['semester'] ?? '') ?><br>
            <strong>Academic Year:</strong> <?= esc($selectedCourse['academic_year'] ?? '') ?><br>
            <strong>Instructor:</strong> <?= esc($selectedCourse['instructor_name'] ?? 'Unassigned') ?>
          </div>
        </div>
      </div>

      <!-- Pending Enrollments -->
      <?php if (!empty($pendingEnrollments)): ?>
        <div class="card mb-4 border-warning">
          <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="fas fa-clock"></i> Pending Enrollment Requests (<?= count($pendingEnrollments) ?>)</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover align-middle">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Student Name</th>
                    <th>Email</th>
                    <th>Request Date</th>
                    <th class="text-end">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php $j = 1; foreach ($pendingEnrollments as $pending): ?>
                    <tr>
                      <td><?= $j++ ?></td>
                      <td><?= esc($pending['student_name']) ?></td>
                      <td><?= esc($pending['student_email']) ?></td>
                      <td><?= $pending['enrollment_date'] ? date('M d, Y H:i', strtotime($pending['enrollment_date'])) : 'N/A' ?></td>
                      <td class="text-end">
                        <form action="<?= base_url('teacher/enrollments/approve/' . $pending['id']) ?>" method="post" class="d-inline">
                          <?= csrf_field() ?>
                          <input type="hidden" name="course_id" value="<?= $selectedCourseId ?>">
                          <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve enrollment for <?= esc($pending['student_name']) ?>?')">
                            <i class="fas fa-check"></i> Approve
                          </button>
                        </form>
                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal<?= $pending['id'] ?>">
                          <i class="fas fa-times"></i> Reject
                        </button>
                      </td>
                    </tr>

                    <!-- Reject Modal -->
                    <div class="modal fade" id="rejectModal<?= $pending['id'] ?>" tabindex="-1">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <div class="modal-header">
                            <h5 class="modal-title">Reject Enrollment Request</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                          </div>
                          <form action="<?= base_url('teacher/enrollments/reject/' . $pending['id']) ?>" method="post">
                            <?= csrf_field() ?>
                            <input type="hidden" name="course_id" value="<?= $selectedCourseId ?>">
                            <div class="modal-body">
                              <p>Are you sure you want to reject the enrollment request from <strong><?= esc($pending['student_name']) ?></strong>?</p>
                              <div class="mb-3">
                                <label class="form-label">Rejection Reason (Optional)</label>
                                <textarea name="rejection_reason" class="form-control" rows="3" placeholder="Enter reason for rejection..."></textarea>
                              </div>
                            </div>
                            <div class="modal-footer">
                              <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                              <button type="submit" class="btn btn-danger">Reject Enrollment</button>
                            </div>
                          </form>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <!-- Approved Students List -->
      <div class="card">
        <div class="card-header bg-success text-white">
          <h5 class="mb-0"><i class="fas fa-check-circle"></i> Approved Students</h5>
        </div>
        <div class="card-body">
          <?php if (!empty($students)): ?>
            <div class="table-responsive">
              <table class="table table-hover align-middle">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Enrollment Date</th>
                    <th>Approved Date</th>
                    <th class="text-end">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php $i = 1; foreach ($students as $student): ?>
                    <tr>
                      <td><?= $i++ ?></td>
                      <td>
                        <div class="fw-semibold"><?= esc($student['name']) ?></div>
                      </td>
                      <td><?= esc($student['email']) ?></td>
                      <td>
                        <?php if ($student['enrollment_date']): ?>
                          <?= date('M d, Y', strtotime($student['enrollment_date'])) ?>
                        <?php else: ?>
                          <span class="text-muted">N/A</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <?php if ($student['approved_at']): ?>
                          <?= date('M d, Y', strtotime($student['approved_at'])) ?>
                        <?php else: ?>
                          <span class="text-muted">N/A</span>
                        <?php endif; ?>
                      </td>
                      <td class="text-end">
                        <a href="<?= base_url('admin/manage-users?search=' . urlencode($student['email'])) ?>" class="btn btn-sm btn-outline-primary">
                          <i class="fas fa-user"></i> View Profile
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

            <div class="mt-3">
              <p class="text-muted">
                <strong>Total Approved Students:</strong> <?= count($students) ?>
              </p>
            </div>
          <?php else: ?>
            <div class="alert alert-light border">
              <i class="fas fa-info-circle"></i> 
              <?php if ($search): ?>
                No approved students found matching "<?= esc($search) ?>".
              <?php else: ?>
                No approved students enrolled in this course yet.
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php else: ?>
      <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> Please select a course from the dropdown above to view enrolled students.
      </div>
    <?php endif; ?>
  </div>
</div>

<?= $this->endSection() ?>

