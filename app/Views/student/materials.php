<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<div class="container mt-4">
  <div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
      <strong>Course Materials</strong>
      <a href="<?= base_url('dashboard') ?>" class="btn btn-sm btn-outline-secondary">Back</a>
    </div>
    <div class="card-body">
      <?php if (empty($courses)): ?>
        <p class="text-muted">You are not enrolled in any course yet.</p>
      <?php else: ?>
        <div class="mb-4">
          <label class="form-label fw-semibold">Your Courses</label>
          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead>
                <tr>
                  <th style="width:80px;">ID</th>
                  <th>Title</th>
                  <th class="text-end" style="width:160px;">Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($courses as $c): ?>
                  <tr>
                    <td><?= (int)$c['id'] ?></td>
                    <td><?= esc($c['title']) ?></td>
                    <td class="text-end">
                      <a class="btn btn-primary btn-sm" href="<?= base_url('student/materials?course_id=' . (int)$c['id']) ?>">View Materials</a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

        <h5 class="fw-semibold">Selected Course Materials</h5>
        <?php if (!empty($materials)): ?>
          <?php 
            // Group materials by exam type
            $grouped = ['Prelim' => [], 'Midterm' => [], 'Final' => []];
            foreach ($materials as $m) {
              $examType = $m['exam_type'] ?? 'Prelim';
              if (!isset($grouped[$examType])) {
                $grouped[$examType] = [];
              }
              $grouped[$examType][] = $m;
            }
          ?>
          
          <?php foreach (['Prelim', 'Midterm', 'Final'] as $examType): ?>
            <?php if (!empty($grouped[$examType])): ?>
              <div class="mb-4">
                <h6 class="fw-semibold text-primary mb-2"><?= esc($examType) ?> Materials</h6>
                <div class="table-responsive">
                  <table class="table table-striped align-middle">
                    <thead class="table-light">
                      <tr>
                        <th>#</th>
                        <th>File Name</th>
                        <th>Uploaded</th>
                        <th style="width:120px;" class="text-end">Action</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($grouped[$examType] as $m): ?>
                        <tr>
                          <td><?= (int)$m['id'] ?></td>
                          <td><?= esc($m['file_name']) ?></td>
                          <td><?= esc($m['created_at']) ?></td>
                          <td class="text-end">
                            <a class="btn btn-sm btn-primary" href="<?= site_url('/materials/download/' . $m['id']) ?>">Download</a>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              </div>
            <?php endif; ?>
          <?php endforeach; ?>
        <?php else: ?>
          <div class="alert alert-light border">No materials uploaded for this course yet.</div>
        <?php endif; ?>
      <?php endif; ?>
    </div>
  </div>
</div>

<!-- No selector JS needed; navigation uses View Materials links per course. -->

<?= $this->endSection() ?>
