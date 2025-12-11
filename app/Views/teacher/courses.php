<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<div class="card shadow-sm mt-3">
  <div class="card-body">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
      <h2 class="fw-bold mb-2 mb-sm-0">My Courses</h2>
    </div>

    <?php if (!empty($courses)): ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Course Code</th>
              <th>Title</th>
              <th>Department</th>
              <th>Program</th>
              <th>Instructor</th>
              <th>Students</th>
              <th>Materials</th>
              <th>Semester</th>
              <th>Academic Year</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php $i = 1; foreach ($courses as $c): ?>
              <tr>
                <td><?= $i++ ?></td>
                <td><span class="badge bg-secondary"><?= esc($c['course_code'] ?? 'N/A') ?></span></td>
                <td>
                  <div class="fw-semibold"><?= esc($c['title']) ?></div>
                  <div class="text-muted small"><?= esc(mb_strimwidth($c['description'] ?? '', 0, 60, '...')) ?></div>
                </td>
                <td><?= esc($c['department'] ?? '') ?></td>
                <td><?= esc($c['program'] ?? '') ?></td>
                <td><?= esc($c['instructor_name'] ?? 'Unassigned') ?></td>
                <td><span class="badge bg-info text-dark"><?= (int)($c['student_count'] ?? 0) ?></span></td>
                <td><span class="badge bg-primary"><?= (int)($c['material_count'] ?? 0) ?></span></td>
                <td><?= esc($c['semester'] ?? '') ?></td>
                <td><?= esc($c['academic_year'] ?? '') ?></td>
                <td class="text-end">
                  <a href="<?= base_url('teacher/students?course_id=' . $c['id']) ?>" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-users"></i> View Students
                  </a>
                  <a href="<?= base_url('teacher/upload?course_id=' . $c['id']) ?>" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-upload"></i> Upload Material
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="alert alert-light border">
        <i class="fas fa-info-circle"></i> You don't have any assigned courses yet.
      </div>
    <?php endif; ?>
  </div>
</div>

<style>
  .table tbody tr:hover {
    background-color: rgba(102, 126, 234, 0.05);
  }
  
  .btn-sm {
    padding: 0.4rem 0.8rem;
    font-size: 0.875rem;
  }
  
  .badge {
    padding: 0.35rem 0.65rem;
    font-weight: 500;
  }
</style>
<?= $this->endSection() ?>

