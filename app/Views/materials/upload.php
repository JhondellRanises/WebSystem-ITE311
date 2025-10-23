<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>


<div class="container mt-5">
  <div class="card shadow-sm">
    <div class="card-body">

      <nav aria-label="breadcrumb" class="mb-2">
        <ol class="breadcrumb mb-1">
          <li class="breadcrumb-item"><a class="text-decoration-none" href="<?= base_url('materials/upload') ?>">Materials</a></li>
          <li class="breadcrumb-item active" aria-current="page">Upload</li>
        </ol>
      </nav>

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
              <?php foreach ($courses as $c): ?>
                <option value="<?= (int)$c['id'] ?>" <?= ((int)$c['id'] === (int)$course_id) ? 'selected' : '' ?>>[<?= (int)$c['id'] ?>] <?= esc($c['title']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>
        <script>
          (function(){
            const sel = document.getElementById('courseSwitch');
            if(sel){
              sel.addEventListener('change', function(){
                const id = parseInt(sel.value, 10);
                if(id>0){ window.location.href = '<?= base_url('admin/course') ?>/' + id + '/upload#uploadForm'; }
              });
            }
          })();
        </script>
      <?php endif; ?>

      <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
      <?php elseif (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
      <?php endif; ?>
      <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?= esc($success) ?></div>
      <?php elseif (!empty($error)): ?>
        <div class="alert alert-danger"><?= esc($error) ?></div>
      <?php endif; ?>

      <div class="border rounded p-3 mb-3 bg-light-subtle">
        <h6 class="fw-semibold mb-3">Upload a File</h6>
        <form id="uploadForm" action="<?= base_url('admin/course/' . esc($course_id) . '/upload') ?>" method="post" enctype="multipart/form-data">
          <?= csrf_field() ?>
          <div class="mb-2">
            <label for="material_file" class="form-label">Select Material File</label>
            <input type="file" name="material_file" id="material_file" class="form-control" required>
            <div class="form-text">Allowed: PDF, DOC, PPT, JPG, PNG. Max 10MB.</div>
          </div>
          <script>
            (function(){
              if (window.location.hash === '#uploadForm') {
                const el = document.getElementById('uploadForm');
                if (el) { el.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
              }
            })();
          </script>
          <div class="d-flex gap-2">
            <button type="submit" class="btn btn-primary">Upload File</button>
          </div>
        </form>
      </div>

      <hr class="my-4">

      <h4 class="mt-4 mb-2">Uploaded Materials for this Course</h4>
      <?php if (!empty($materials)): ?>
        <div class="list-group">
          <?php foreach ($materials as $m): ?>
            <div class="list-group-item d-flex justify-content-between align-items-center">
              <div class="d-flex align-items-center gap-2">
                <a class="btn btn-sm btn-outline-primary" href="<?= base_url('materials/download/' . $m['id']) ?>" target="_blank">Open</a>
                <span class="fw-semibold"><?= esc($m['file_name']) ?></span>
              </div>
              <div class="d-flex align-items-center gap-2">
                <small class="text-muted me-2"><?= esc($m['created_at']) ?></small>
                <a href="<?= base_url('materials/download/' . $m['id']) ?>" class="btn btn-sm btn-success">Download</a>
                <a href="<?= base_url('materials/delete/' . $m['id']) ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</a>
              </div>
            </div>
          <?php endforeach; ?>
        </div>
      <?php else: ?>
        <div class="alert alert-light border mt-2 mb-0">No materials uploaded yet for this course. Use the form above to add files.</div>
      <?php endif; ?>

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
    </div>
  </div>
</div>

<?= $this->endSection() ?>
