<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<div class="container mt-4">
  <div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
      <strong>My Courses</strong>
      <a href="<?= base_url('dashboard') ?>" class="btn btn-sm btn-outline-secondary">Back</a>
    </div>
    <div class="card-body">
      <?php if (empty($courses)): ?>
        <div class="alert alert-light border">You are not enrolled in any course yet.</div>
      <?php else: ?>
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
      <?php endif; ?>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
