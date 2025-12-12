<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<?php
// Set default values for variables that might not be set
$deleted_materials = $deleted_materials ?? [];
$all_materials = $all_materials ?? [];
$courses = $courses ?? [];
?>

<div class="container mt-5">
  <div class="card shadow-sm">
    <div class="card-body">
      
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h2 class="fw-bold mb-0">Upload Course Material</h2>
        <?php if (!empty($current_course)): ?>
          <span class="text-muted small">Course: <strong><?= esc($current_course['title'] ?? 'Course #'.(string)$course_id) ?></strong></span>
        <?php endif; ?>
      </div>

      <?php if (!empty($courses)): ?>
        <div class="row g-2 align-items-end mb-3">
          <div class="col-sm-6 col-md-5 col-lg-4">
            <label for="courseSwitch" class="form-label mb-1">Choose Course</label>
            <select id="courseSwitch" class="form-select">
              <option value="">-- Select Course here --</option>
              <?php foreach ($courses as $c): ?>
                <option value="<?= (int)$c['id'] ?>" <?= ((int)$c['id'] === (int)$course_id) ? 'selected' : '' ?> <?= (stripos($c['title'], 'Web Systems & Design') !== false) ? 'data-default="true"' : '' ?>><?= esc($c['title']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <script>
          (function(){
            const sel = document.getElementById('courseSwitch');
            if(sel){
              const handleCourseChange = function(){
                const id = parseInt(sel.value, 10);
                if(id>0){ window.location.href = '<?= base_url('admin/course') ?>/' + id + '/upload'; }
              };
              sel.addEventListener('change', handleCourseChange);
              // Trigger on load if no course_id is set (first time or page load)
              if(!sel.value || sel.value === '0') {
                const firstOption = sel.querySelector('option');
                if(firstOption && firstOption.value && firstOption.value !== '0') {
                  sel.value = firstOption.value;
                  handleCourseChange();
                }
              }
            }
          })();
        </script>
      <?php endif; ?>

      <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <?= esc(session()->getFlashdata('success')) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php elseif (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <?= esc(session()->getFlashdata('error')) ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      <?php endif; ?>

      <div class="border rounded p-3 mb-3 bg-light-subtle">
        <h6 class="fw-semibold mb-3">Upload a File</h6>
        <form id="uploadForm" action="<?= base_url('admin/course/') ?><?= !empty($course_id) ? (int)$course_id : '' ?>/upload" method="post" enctype="multipart/form-data">
          <?= csrf_field() ?>
          <input type="hidden" id="courseIdInput" name="course_id" value="<?= !empty($course_id) ? (int)$course_id : '' ?>">
          <div class="mb-2">
            <label for="exam_type" class="form-label">Exam Type</label>
            <select name="exam_type" id="exam_type" class="form-select" required>
              <option value="">-- Select Exam Type --</option>
              <option value="Prelim">Prelim</option>
              <option value="Midterm">Midterm</option>
              <option value="Final">Final</option>
            </select>
          </div>
          <div class="mb-2">
            <label for="material_file" class="form-label">Select Material File</label>
            <input type="file" name="material_file" id="material_file" class="form-control" accept=".pdf,.ppt,.pptx,.doc,.docx" required>
            <div class="form-text">Allowed: PDF, PPT, PPTX, DOC, DOCX. Max 10MB.</div>
          </div>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary" id="uploadBtn" <?= empty($course_id) ? 'disabled' : '' ?>>Upload File</button>
          </div>
        </form>
      </div>
      <script>
        (function(){
          const courseSwitch = document.getElementById('courseSwitch');
          const uploadBtn = document.getElementById('uploadBtn');
          const uploadForm = document.getElementById('uploadForm');
          const courseIdInput = document.getElementById('courseIdInput');
          if(courseSwitch && uploadBtn && uploadForm && courseIdInput) {
            courseSwitch.addEventListener('change', function(){
              const courseId = parseInt(this.value, 10);
              if(courseId > 0) {
                uploadBtn.disabled = false;
                courseIdInput.value = courseId;
                uploadForm.action = '<?= base_url('admin/course/') ?>' + courseId + '/upload';
              } else {
                uploadBtn.disabled = true;
              }
            });
          }
        })();
      </script>

      <hr class="my-4">

      <h4 class="mt-2 mb-2">All Uploaded Materials</h4>
      <?php if (!empty($all_materials)): ?>
        <div class="list-group">
          <?php foreach ($all_materials as $row): ?>
            <div class="list-group-item d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center gap-2">
                <span class="fw-semibold"><?= esc($row['file_name']) ?></span>
              </div>
              <div class="d-flex align-items-center gap-2">
                <small class="text-muted me-2">[<?= (int)$row['course_id'] ?>] <?= esc($row['course_title']) ?> • <?= esc($row['created_at']) ?></small>
                <a href="<?= base_url('materials/download/' . $row['id']) ?>" class="btn btn-sm btn-success">Download</a>
                <a href="<?= base_url('materials/delete/' . $row['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="alert alert-light border mt-2 mb-0">No uploaded materials found across all courses.</div>
      <?php endif; ?>

      <hr class="my-4">

      <h4 class="mt-2 mb-2">Trash (Deleted Materials)</h4>
      <?php if (!empty($deleted_materials)): ?>
        <div class="list-group">
          <?php foreach ($deleted_materials as $row): ?>
            <div class="list-group-item d-flex justify-content-between align-items-center bg-light">
              <div class="d-flex align-items-center gap-2">
                <span class="fw-semibold text-muted"><?= esc($row['file_name']) ?></span>
              </div>
              <div class="d-flex align-items-center gap-2">
                <small class="text-muted me-2">[<?= (int)$row['course_id'] ?>] <?= esc($row['course_title']) ?> • Deleted: <?= esc($row['deleted_at']) ?></small>
                <a href="<?= base_url('materials/restore/' . $row['id']) ?>" class="btn btn-sm btn-warning">Restore</a>
                <a href="<?= base_url('materials/permanent-delete/' . $row['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Permanently delete this file? This cannot be undone.')">Delete Permanently</a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="alert alert-light border mt-2 mb-0">No deleted materials in trash.</div>
      <?php endif; ?>

    </div>
  </div>
</div>

<?= $this->endSection() ?>
