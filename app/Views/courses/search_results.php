<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<div class="container mt-4">
  <div class="card shadow-sm">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
      <h5 class="mb-0">🔍 Search Results</h5>
      <a href="<?= base_url('courses') ?>" class="btn btn-sm btn-light">Back to Courses</a>
    </div>
    <div class="card-body">
      <div class="mb-3">
        <p class="text-muted">
          <strong>Search term:</strong> <code><?= esc($searchTerm) ?></code>
        </p>
        <p class="text-muted">
          <strong>Results:</strong> <span class="badge bg-primary"><?= count($courses) ?></span> course(s) found
        </p>
      </div>

      <?php if (empty($courses)): ?>
        <div class="alert alert-warning">
          <i class="fas fa-exclamation-circle"></i> 
          No courses found matching "<strong><?= esc($searchTerm) ?></strong>". 
          Try a different search term.
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead class="table-light">
              <tr>
                <th style="width:80px;">ID</th>
                <th>Title</th>
                <th>Description</th>
                <th style="width:120px;">Instructor</th>
                <th class="text-end" style="width:180px;">Action</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($courses as $c): ?>
                <tr>
                  <td><?= (int)$c['id'] ?></td>
                  <td>
                    <strong><?= esc($c['title']) ?></strong>
                  </td>
                  <td>
                    <small class="text-muted">
                      <?= esc(substr($c['description'] ?? 'No description', 0, 60)) ?>
                      <?= strlen($c['description'] ?? '') > 60 ? '...' : '' ?>
                    </small>
                  </td>
                  <td>
                    <small><?= esc($c['instructor_name'] ?? 'N/A') ?></small>
                  </td>
                  <td class="text-end">
                    <button class="btn btn-success btn-sm enroll-btn" data-course-id="<?= (int)$c['id'] ?>" data-course-title="<?= esc($c['title']) ?>">
                      <i class="fas fa-plus"></i> Enroll
                    </button>
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

<!-- Enrollment Success Modal -->
<div class="modal fade" id="enrollmentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">✅ Enrollment Successful</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p id="enrollmentMessage"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <a href="<?= base_url('student/courses') ?>" class="btn btn-primary">View My Courses</a>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
  $('.enroll-btn').on('click', function() {
    const courseId = $(this).data('course-id');
    const courseTitle = $(this).data('course-title');
    const btn = $(this);

    $.ajax({
      url: '<?= base_url('course/enroll') ?>',
      type: 'POST',
      data: { course_id: courseId },
      dataType: 'json',
      success: function(response) {
        if (response.status === 'success') {
          btn.prop('disabled', true).html('<i class="fas fa-check"></i> Enrolled');
          $('#enrollmentMessage').text('You have successfully enrolled in ' + courseTitle);
          new bootstrap.Modal(document.getElementById('enrollmentModal')).show();
        } else {
          alert('Error: ' + response.message);
        }
      },
      error: function() {
        alert('Error enrolling in course. Please try again.');
      }
    });
  });
});
</script>

<?= $this->endSection() ?>
