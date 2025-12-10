<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<?php
  $errors = session()->getFlashdata('errors') ?? [];
?>

<div class="card shadow-sm mt-3">
  <div class="card-body">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
      <h2 class="fw-bold mb-2 mb-sm-0">Manage Courses</h2>
      <div class="d-flex gap-2 align-items-center">
        <form class="d-flex" action="<?= base_url('admin/manage-courses') ?>" method="get">
          <input type="text" class="form-control" name="q" placeholder="Search courses..." value="<?= esc($q ?? '') ?>"/>
          <button type="submit" class="btn btn-outline-secondary ms-2">Search</button>
        </form>
        <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#createModal">Add Course</button>
      </div>
    </div>

    <?php if (!empty($courses)): ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th>#</th>
              <th>Course Code</th>
              <th>Title</th>
              <th>Instructor</th>
              <th>Units</th>
              <th>Term</th>
              <th>Semester</th>
              <th>Academic Year</th>
              <th>Department</th>
              <th>Program</th>
              <th>Enrollments</th>
              <th>Materials</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php $i = 1; foreach ($courses as $c): ?>
              <tr>
                <td><?= $i++ ?></td>
                <td><?= esc($c['course_code'] ?? '') ?></td>
                <td>
                  <div class="fw-semibold"><?= esc($c['title']) ?></div>
                  <div class="text-muted small"><?= esc(mb_strimwidth($c['description'] ?? '', 0, 60, '...')) ?></div>
                </td>
                <td><?= esc($c['instructor_name'] ?? 'Unassigned') ?></td>
                <td><span class="badge bg-secondary"><?= esc($c['units'] ?? '0') ?></span></td>
                <td><?= esc($c['term'] ?? '') ?></td>
                <td><?= esc($c['semester'] ?? '') ?></td>
                <td><?= esc($c['academic_year'] ?? '') ?></td>
                <td><?= esc($c['department'] ?? '') ?></td>
                <td><?= esc($c['program'] ?? '') ?></td>
                <td><span class="badge bg-info text-dark"><?= (int)($c['enroll_count'] ?? 0) ?></span></td>
                <td><span class="badge bg-primary"><?= (int)($c['material_count'] ?? 0) ?></span></td>
                <td class="text-end">
                  <button class="btn btn-sm btn-outline-primary js-edit" data-course-id="<?= (int)$c['id'] ?>">Edit</button>
                  <form action="<?= base_url('admin/manage-courses/delete/' . $c['id']) ?>" method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this course? This will also remove related enrollments and materials.')">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
      <div>
        <?= isset($pager) ? $pager->links('courses') : '' ?>
      </div>
    <?php else: ?>
      <div class="alert alert-light border">No courses found.</div>
    <?php endif; ?>
  </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Create New Course</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="<?= base_url('admin/manage-courses/create') ?>" method="post">
        <?= csrf_field() ?>
        <div class="modal-body">
          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              Please correct the highlighted fields.
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          <?php endif; ?>
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label" for="c_title">Course Title <span class="text-danger">*</span></label>
              <input type="text" class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>" id="c_title" name="title" value="<?= esc(old('title')) ?>" placeholder="e.g., ITE 321 - Web Application Development" required minlength="3" maxlength="150">
              <?php if (isset($errors['title'])): ?>
                <div class="invalid-feedback d-block"><?= esc($errors['title']) ?></div>
              <?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="c_course_code">Course Number/Code</label>
              <input type="text" class="form-control <?= isset($errors['course_code']) ? 'is-invalid' : '' ?>" id="c_course_code" name="course_code" value="<?= esc(old('course_code')) ?>" placeholder="e.g., ITE321, CS101" maxlength="50">
              <?php if (isset($errors['course_code'])): ?>
                <div class="invalid-feedback d-block"><?= esc($errors['course_code']) ?></div>
              <?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="c_units">Units</label>
              <input type="number" step="0.5" min="0" max="10" class="form-control <?= isset($errors['units']) ? 'is-invalid' : '' ?>" id="c_units" name="units" value="<?= esc(old('units')) ?>" placeholder="e.g., 3">
              <small class="form-text text-muted">Optional: Number of units (0-10)</small>
              <?php if (isset($errors['units'])): ?>
                <div class="invalid-feedback d-block"><?= esc($errors['units']) ?></div>
              <?php endif; ?>
            </div>
            <div class="col-12">
              <label class="form-label" for="c_description">Description</label>
              <textarea class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>" id="c_description" name="description" rows="4" maxlength="1000" placeholder="Enter course description..."><?= esc(old('description')) ?></textarea>
              <?php if (isset($errors['description'])): ?>
                <div class="invalid-feedback d-block"><?= esc($errors['description']) ?></div>
              <?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="c_instructor">Instructor</label>
              <select id="c_instructor" name="instructor_id" class="form-select <?= isset($errors['instructor_id']) ? 'is-invalid' : '' ?>">
                <option value="">-- Select Instructor (Optional) --</option>
                <?php foreach ($instructors as $inst): ?>
                  <option value="<?= (int)$inst['id'] ?>" <?= old('instructor_id') == $inst['id'] ? 'selected' : '' ?>>
                    <?= esc($inst['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <small class="form-text text-muted">You can assign an instructor later.</small>
              <?php if (isset($errors['instructor_id'])): ?>
                <div class="invalid-feedback d-block"><?= esc($errors['instructor_id']) ?></div>
              <?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="c_academic_year">Academic Year</label>
              <select id="c_academic_year" name="academic_year" class="form-select <?= isset($errors['academic_year']) ? 'is-invalid' : '' ?>">
                <option value="">-- Select Academic Year (Optional) --</option>
                <?php foreach ($academicYears as $year): ?>
                  <option value="<?= esc($year) ?>" <?= old('academic_year') == $year ? 'selected' : '' ?>><?= esc($year) ?></option>
                <?php endforeach; ?>
              </select>
              <?php if (isset($errors['academic_year'])): ?>
                <div class="invalid-feedback d-block"><?= esc($errors['academic_year']) ?></div>
              <?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="c_semester">Semester</label>
              <select id="c_semester" name="semester" class="form-select <?= isset($errors['semester']) ? 'is-invalid' : '' ?>">
                <option value="">-- Select Semester (Optional) --</option>
                <?php foreach ($semesters as $sem): ?>
                  <option value="<?= esc($sem) ?>" <?= old('semester') == $sem ? 'selected' : '' ?>><?= esc($sem) ?></option>
                <?php endforeach; ?>
              </select>
              <small class="form-text text-muted">Select the semester for this course.</small>
              <?php if (isset($errors['semester'])): ?>
                <div class="invalid-feedback d-block"><?= esc($errors['semester']) ?></div>
              <?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="c_term">Term</label>
              <select id="c_term" name="term" class="form-select <?= isset($errors['term']) ? 'is-invalid' : '' ?>">
                <option value="">-- Select Term (Optional) --</option>
                <?php foreach ($terms as $term): ?>
                  <option value="<?= esc($term) ?>" <?= old('term') == $term ? 'selected' : '' ?>><?= esc($term) ?></option>
                <?php endforeach; ?>
              </select>
              <small class="form-text text-muted">Select the term for this course.</small>
              <?php if (isset($errors['term'])): ?>
                <div class="invalid-feedback d-block"><?= esc($errors['term']) ?></div>
              <?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="c_department">Department</label>
              <select id="c_department" name="department" class="form-select <?= isset($errors['department']) ? 'is-invalid' : '' ?>">
                <option value="">-- Select Department (Optional) --</option>
                <?php foreach ($departments as $dept): ?>
                  <option value="<?= esc($dept) ?>" <?= old('department') == $dept ? 'selected' : '' ?>><?= esc($dept) ?></option>
                <?php endforeach; ?>
              </select>
              <small class="form-text text-muted">Select the department for this course.</small>
              <?php if (isset($errors['department'])): ?>
                <div class="invalid-feedback d-block"><?= esc($errors['department']) ?></div>
              <?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="c_program">Program</label>
              <select id="c_program" name="program" class="form-select <?= isset($errors['program']) ? 'is-invalid' : '' ?>">
                <option value="">-- Select Department First --</option>
                <?php 
                // Only show programs if department is already selected (e.g., after validation errors)
                $oldDept = old('department');
                if ($oldDept && isset($departmentPrograms[$oldDept])) {
                  foreach ($departmentPrograms[$oldDept] as $prog): ?>
                    <option value="<?= esc($prog) ?>" <?= old('program') == $prog ? 'selected' : '' ?>><?= esc($prog) ?></option>
                <?php 
                  endforeach;
                }
                ?>
              </select>
              <small class="form-text text-muted">Select a department first to see available programs.</small>
              <?php if (isset($errors['program'])): ?>
                <div class="invalid-feedback d-block"><?= esc($errors['program']) ?></div>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary">Create Course</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Edit Course</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editForm" action="#" method="post">
        <?= csrf_field() ?>
        <div class="modal-body">
          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              Please correct the highlighted fields.
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          <?php endif; ?>
          <div class="row g-3">
            <div class="col-12">
              <label class="form-label" for="e_title">Course Title <span class="text-danger">*</span></label>
              <input type="text" class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>" id="e_title" name="title" placeholder="e.g., ITE 321 - Web Application Development" required minlength="3" maxlength="150">
              <?php if (isset($errors['title'])): ?>
                <div class="invalid-feedback d-block"><?= esc($errors['title']) ?></div>
              <?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="e_course_code">Course Number/Code</label>
              <input type="text" class="form-control <?= isset($errors['course_code']) ? 'is-invalid' : '' ?>" id="e_course_code" name="course_code" placeholder="e.g., ITE321, CS101" maxlength="50">
              <?php if (isset($errors['course_code'])): ?>
                <div class="invalid-feedback d-block"><?= esc($errors['course_code']) ?></div>
              <?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="e_units">Units</label>
              <input type="number" step="0.5" min="0" max="10" class="form-control <?= isset($errors['units']) ? 'is-invalid' : '' ?>" id="e_units" name="units" placeholder="e.g., 3">
              <small class="form-text text-muted">Optional: Number of units (0-10)</small>
              <?php if (isset($errors['units'])): ?>
                <div class="invalid-feedback d-block"><?= esc($errors['units']) ?></div>
              <?php endif; ?>
            </div>
            <div class="col-12">
              <label class="form-label" for="e_description">Description</label>
              <textarea class="form-control <?= isset($errors['description']) ? 'is-invalid' : '' ?>" id="e_description" name="description" rows="4" maxlength="1000" placeholder="Enter course description..."></textarea>
              <?php if (isset($errors['description'])): ?>
                <div class="invalid-feedback d-block"><?= esc($errors['description']) ?></div>
              <?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="e_instructor">Instructor</label>
              <select id="e_instructor" name="instructor_id" class="form-select <?= isset($errors['instructor_id']) ? 'is-invalid' : '' ?>">
                <option value="">-- Select Instructor (Optional) --</option>
                <?php foreach ($instructors as $inst): ?>
                  <option value="<?= (int)$inst['id'] ?>">
                    <?= esc($inst['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <small class="form-text text-muted">You can assign an instructor later.</small>
              <?php if (isset($errors['instructor_id'])): ?>
                <div class="invalid-feedback d-block"><?= esc($errors['instructor_id']) ?></div>
              <?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="e_academic_year">Academic Year</label>
              <select id="e_academic_year" name="academic_year" class="form-select <?= isset($errors['academic_year']) ? 'is-invalid' : '' ?>">
                <option value="">-- Select Academic Year (Optional) --</option>
                <?php foreach ($academicYears as $year): ?>
                  <option value="<?= esc($year) ?>"><?= esc($year) ?></option>
                <?php endforeach; ?>
              </select>
              <?php if (isset($errors['academic_year'])): ?>
                <div class="invalid-feedback d-block"><?= esc($errors['academic_year']) ?></div>
              <?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="e_semester">Semester</label>
              <select id="e_semester" name="semester" class="form-select <?= isset($errors['semester']) ? 'is-invalid' : '' ?>">
                <option value="">-- Select Semester (Optional) --</option>
                <?php foreach ($semesters as $sem): ?>
                  <option value="<?= esc($sem) ?>"><?= esc($sem) ?></option>
                <?php endforeach; ?>
              </select>
              <small class="form-text text-muted">Select the semester for this course.</small>
              <?php if (isset($errors['semester'])): ?>
                <div class="invalid-feedback d-block"><?= esc($errors['semester']) ?></div>
              <?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="e_term">Term</label>
              <select id="e_term" name="term" class="form-select <?= isset($errors['term']) ? 'is-invalid' : '' ?>">
                <option value="">-- Select Term (Optional) --</option>
                <?php foreach ($terms as $term): ?>
                  <option value="<?= esc($term) ?>"><?= esc($term) ?></option>
                <?php endforeach; ?>
              </select>
              <small class="form-text text-muted">Select the term for this course.</small>
              <?php if (isset($errors['term'])): ?>
                <div class="invalid-feedback d-block"><?= esc($errors['term']) ?></div>
              <?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="e_department">Department</label>
              <select id="e_department" name="department" class="form-select <?= isset($errors['department']) ? 'is-invalid' : '' ?>">
                <option value="">-- Select Department (Optional) --</option>
                <?php foreach ($departments as $dept): ?>
                  <option value="<?= esc($dept) ?>"><?= esc($dept) ?></option>
                <?php endforeach; ?>
              </select>
              <small class="form-text text-muted">Select the department for this course.</small>
              <?php if (isset($errors['department'])): ?>
                <div class="invalid-feedback d-block"><?= esc($errors['department']) ?></div>
              <?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="e_program">Program</label>
              <select id="e_program" name="program" class="form-select <?= isset($errors['program']) ? 'is-invalid' : '' ?>">
                <option value="">-- Select Department First --</option>
              </select>
              <small class="form-text text-muted">Select a department first to see available programs.</small>
              <?php if (isset($errors['program'])): ?>
                <div class="invalid-feedback d-block"><?= esc($errors['program']) ?></div>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="e_submit">Update Course</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
(function(){
  const qs = new URLSearchParams(window.location.search);
  const csrfMetaName = document.querySelector('meta[name="csrf-token-name"]');
  const csrfMeta = document.querySelector('meta[name="csrf-token"]');
  const csrfName = csrfMetaName ? csrfMetaName.getAttribute('content') : null;
  let csrfHash = csrfMeta ? csrfMeta.getAttribute('content') : null;

  // Department to Programs mapping
  const departmentPrograms = <?= json_encode($departmentPrograms ?? []) ?>;

  function updateCsrf(newHash){
    if (!newHash) return;
    csrfHash = newHash;
    if (csrfMeta) csrfMeta.setAttribute('content', csrfHash);
    document.querySelectorAll('input[name="'+csrfName+'"]').forEach(function(el){ el.value = csrfHash; });
  }

  // Update program dropdown based on selected department
  function updateProgramDropdown(departmentSelectId, programSelectId, selectedProgram = '') {
    const departmentSelect = document.getElementById(departmentSelectId);
    const programSelectEl = document.getElementById(programSelectId);
    
    if (!departmentSelect || !programSelectEl) return;
    
    const department = departmentSelect.value;
    
    // Clear existing options
    if (department && departmentPrograms[department]) {
      // Add programs for the selected department
      programSelectEl.innerHTML = '<option value="">-- Select Program (Optional) --</option>';
      departmentPrograms[department].forEach(function(program) {
        const option = document.createElement('option');
        option.value = program;
        option.textContent = program;
        if (selectedProgram && program === selectedProgram) {
          option.selected = true;
        }
        programSelectEl.appendChild(option);
      });
    } else {
      // No department selected, show placeholder
      programSelectEl.innerHTML = '<option value="">-- Select Department First --</option>';
    }
  }

  // Set up department change listeners
  const cDepartment = document.getElementById('c_department');
  const cProgram = document.getElementById('c_program');
  const eDepartment = document.getElementById('e_department');
  const eProgram = document.getElementById('e_program');

  if (cDepartment && cProgram) {
    // Update on change
    cDepartment.addEventListener('change', function() {
      updateProgramDropdown('c_department', 'c_program');
    });
    
    // Update on page load if department is already selected (e.g., after validation errors)
    if (cDepartment.value) {
      const selectedProgram = cProgram.value;
      updateProgramDropdown('c_department', 'c_program', selectedProgram);
    }
  }

  if (eDepartment && eProgram) {
    eDepartment.addEventListener('change', function() {
      const currentProgram = eProgram.value;
      updateProgramDropdown('e_department', 'e_program', currentProgram);
    });
  }

  // Modal helpers
  let editModalInstance = null;
  function getEditModal(){
    if (!editModalInstance){
      const el = document.getElementById('editModal');
      if (el) editModalInstance = new bootstrap.Modal(el);
    }
    return editModalInstance;
  }

  function openEdit(id){
    const url = '<?= base_url('admin/manage-courses/show') ?>/' + id;
    const em = getEditModal();
    if (em) em.show();
    const submitBtn = document.getElementById('e_submit');
    if (submitBtn) {
      submitBtn.disabled = true;
      submitBtn.textContent = 'Loading...';
    }
    fetch(url, { headers: csrfHash ? { 'X-CSRF-TOKEN': csrfHash } : {} })
      .then(r => r.json())
      .then(data => {
        if (data && data.course){
          document.getElementById('e_title').value = data.course.title || '';
          document.getElementById('e_description').value = data.course.description || '';
          document.getElementById('e_instructor').value = data.course.instructor_id || '';
          document.getElementById('e_course_code').value = data.course.course_code || '';
          document.getElementById('e_units').value = data.course.units || '';
          document.getElementById('e_term').value = data.course.term || '';
          document.getElementById('e_semester').value = data.course.semester || '';
          document.getElementById('e_academic_year').value = data.course.academic_year || '';
          
          // Set department first, then update programs
          const department = data.course.department || '';
          const program = data.course.program || '';
          document.getElementById('e_department').value = department;
          
          // Update program dropdown based on department
          if (department) {
            updateProgramDropdown('e_department', 'e_program', program);
          } else {
            document.getElementById('e_program').value = program;
          }
          
          document.getElementById('editForm').setAttribute('action', '<?= base_url('admin/manage-courses/edit') ?>/' + id);
          
          if (data.csrf) updateCsrf(data.csrf);
        } else {
          alert('Failed to load course.');
          const inst = getEditModal(); if (inst) inst.hide();
        }
      })
      .catch(() => { 
        alert('Failed to load course.'); 
        const inst = getEditModal(); 
        if (inst) inst.hide(); 
      })
      .finally(() => { 
        if (submitBtn) {
          submitBtn.disabled = false;
          submitBtn.textContent = 'Update Course';
        }
      });
  }

  document.addEventListener('click', function(e){
    const btn = e.target.closest('.js-edit');
    if (!btn) return;
    const id = btn.getAttribute('data-course-id');
    if (id) openEdit(id);
  });

  // Auto-open edit modal if redirected with ?edit=ID
  const editId = qs.get('edit');
  if (editId) {
    openEdit(editId);
  }

  // Reset program dropdown when create modal is closed
  const createModal = document.getElementById('createModal');
  if (createModal) {
    createModal.addEventListener('hidden.bs.modal', function() {
      if (cProgram) {
        cProgram.innerHTML = '<option value="">-- Select Department First --</option>';
      }
      if (cDepartment) {
        cDepartment.value = '';
      }
    });
  }

  // Reset program dropdown when edit modal is closed
  const editModal = document.getElementById('editModal');
  if (editModal) {
    editModal.addEventListener('hidden.bs.modal', function() {
      if (eProgram) {
        eProgram.innerHTML = '<option value="">-- Select Department First --</option>';
      }
      if (eDepartment) {
        eDepartment.value = '';
      }
    });
  }
})();
</script>

<?= $this->endSection() ?>
