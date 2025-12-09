<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<?php $errors = session()->getFlashdata('errors') ?? []; ?>

<div class="card shadow-sm mt-3">
  <div class="card-body">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
      <h2 class="fw-bold mb-2 mb-sm-0">Manage Users</h2>
      <div class="d-flex gap-2 align-items-center">
        <form class="d-flex" action="<?= base_url('admin/manage-users') ?>" method="get">
          <input type="text" class="form-control" name="q" placeholder="Search name or email" value="<?= esc($q ?? '') ?>"/>
          <button type="submit" class="btn btn-outline-secondary ms-2">Search</button>
        </form>
        <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#createModal">Add User</button>
      </div>
    </div>

    <?php if (!empty($users)): ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Email</th>
              <th>Role</th>
              <th>Status</th>
              <th>Created</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php $i = 1; foreach ($users as $u): 
              $isDeleted = !empty($u['deleted_at']);
            ?>
              <tr class="<?= $isDeleted ? 'table-secondary' : '' ?>">
                <td><?= $i++ ?></td>
                <td><?= esc($u['name']) ?></td>
                <td><?= esc($u['email']) ?></td>
                <td><span class="badge bg-secondary text-uppercase"><?= esc($u['role']) ?></span></td>
                <td>
                  <?php if ($isDeleted): ?>
                    <span class="badge bg-danger">Deleted</span>
                  <?php else: ?>
                    <span class="badge bg-success">Active</span>
                  <?php endif; ?>
                </td>
                <td><small class="text-muted"><?= esc($u['created_at'] ?? '') ?></small></td>
                <td class="text-end">
                  <?php if ($isDeleted): ?>
                    <!-- Restore button for deleted users -->
                    <form action="<?= base_url('admin/manage-users/restore/' . $u['id']) ?>" method="post" class="d-inline">
                      <?= csrf_field() ?>
                      <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Restore this user? User will be reactivated.')">
                        <i class="bi bi-arrow-counterclockwise"></i> Restore
                      </button>
                    </form>
                  <?php else: ?>
                    <!-- Edit and Delete buttons for active users -->
                    <button class="btn btn-sm btn-outline-primary js-edit" data-user-id="<?= (int)$u['id'] ?>">Edit</button>
                    <form action="<?= base_url('admin/manage-users/delete/' . $u['id']) ?>" method="post" class="d-inline">
                      <?= csrf_field() ?>
                      <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this user? You can restore it later.')">Delete</button>
                    </form>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <div>
        <?= isset($pager) ? $pager->links() : '' ?>
      </div>
    <?php else: ?>
      <div class="alert alert-light border">No users found.</div>
    <?php endif; ?>
  </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form action="<?= base_url('admin/manage-users/create') ?>" method="post">
        <?= csrf_field() ?>
        <div class="modal-body">
          <?php $nameError = $errors['name'] ?? null; if ($nameError): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <?= esc($nameError) ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          <?php endif; ?>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" for="c_name">Full Name</label>
              <input type="text" class="form-control <?= $nameError ? 'is-invalid' : '' ?>" id="c_name" name="name" value="<?= esc(old('name')) ?>" pattern="[a-zA-Z0-9\s\-\'\.]+" title="Only letters, numbers, spaces, hyphens, apostrophes, and periods are allowed. Special characters are not allowed." required>
              <div class="invalid-feedback" id="c_name_error" style="display: none;">
                The name field cannot contain special characters. Only letters, numbers, spaces, hyphens, apostrophes, and periods are allowed.
              </div>
              <?php if ($nameError): ?>
                <div class="invalid-feedback d-block"><?= esc($nameError) ?></div>
              <?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="c_email">Email</label>
              <input type="email" class="form-control" id="c_email" name="email" value="<?= esc(old('email')) ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="c_password">Password</label>
              <input type="password" class="form-control" id="c_password" name="password" required>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="c_confirm">Confirm Password</label>
              <input type="password" class="form-control" id="c_confirm" name="confirm_password" required>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="c_role">Role</label>
              <select id="c_role" name="role" class="form-select" required>
                <option value="admin" <?= old('role')==='admin'?'selected':'' ?>>Admin</option>
                <option value="teacher" <?= old('role')==='teacher'?'selected':'' ?>>Teacher</option>
                <option value="student" <?= old('role')==='student'?'selected':'' ?>>Student</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-dark">Save</button>
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
        <h5 class="modal-title">Edit User</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="editForm" action="#" method="post">
        <?= csrf_field() ?>
        <div class="modal-body">
          <?php $nameError = $errors['name'] ?? null; if ($nameError): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <?= esc($nameError) ?>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          <?php endif; ?>
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" for="e_name">Full Name</label>
              <input type="text" class="form-control <?= $nameError ? 'is-invalid' : '' ?>" id="e_name" name="name" pattern="[a-zA-Z0-9\s\-\'\.]+" title="Only letters, numbers, spaces, hyphens, apostrophes, and periods are allowed. Special characters are not allowed." required>
              <div class="invalid-feedback" id="e_name_error" style="display: none;">
                The name field cannot contain special characters. Only letters, numbers, spaces, hyphens, apostrophes, and periods are allowed.
              </div>
              <?php if ($nameError): ?>
                <div class="invalid-feedback d-block"><?= esc($nameError) ?></div>
              <?php endif; ?>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="e_email">Email</label>
              <input type="email" class="form-control" id="e_email" name="email" required>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="e_role">Role</label>
              <select id="e_role" name="role" class="form-select" required>
                <option value="admin">Admin</option>
                <option value="teacher">Teacher</option>
                <option value="student">Student</option>
              </select>
            </div>
            <div class="col-md-6">
              <label class="form-label" for="e_password">New Password</label>
              <input type="password" class="form-control" id="e_password" name="password">
            </div>
            <div class="col-md-6">
              <label class="form-label" for="e_confirm">Confirm New Password</label>
              <input type="password" class="form-control" id="e_confirm" name="confirm_password">
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-dark" id="e_submit">Update</button>
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
  const OLD = {
    name: <?= json_encode(old('name')) ?>,
    email: <?= json_encode(old('email')) ?>,
    role: <?= json_encode(strtolower(old('role') ?? '')) ?>,
  };

  if (window.jQuery) {
    $.ajaxSetup({ beforeSend: function(xhr){ if (csrfHash) xhr.setRequestHeader('X-CSRF-TOKEN', csrfHash); }});
  }

  function updateCsrf(newHash){
    if (!newHash) return;
    csrfHash = newHash;
    if (csrfMeta) csrfMeta.setAttribute('content', csrfHash);
    // also update hidden CSRF fields in forms
    document.querySelectorAll('input[name="'+csrfName+'"]').forEach(function(el){ el.value = csrfHash; });
  }

  // Real-time name validation function
  function validateNameField(inputElement, errorElement) {
    if (!inputElement) return;
    
    // Check if already validated to avoid duplicate listeners
    if (inputElement.dataset.validated === 'true') return;
    inputElement.dataset.validated = 'true';
    
    inputElement.addEventListener('input', function() {
      const value = this.value;
      const namePattern = /^[a-zA-Z0-9\s\-\'\.]*$/;
      
      if (value && !namePattern.test(value)) {
        this.classList.add('is-invalid');
        if (errorElement) errorElement.style.display = 'block';
      } else {
        this.classList.remove('is-invalid');
        if (errorElement) errorElement.style.display = 'none';
      }
    });
    
    // Also validate on paste
    inputElement.addEventListener('paste', function(e) {
      setTimeout(() => {
        const value = this.value;
        const namePattern = /^[a-zA-Z0-9\s\-\'\.]*$/;
        if (value && !namePattern.test(value)) {
          this.classList.add('is-invalid');
          if (errorElement) errorElement.style.display = 'block';
        } else {
          this.classList.remove('is-invalid');
          if (errorElement) errorElement.style.display = 'none';
        }
      }, 10);
    });
  }

  // Bootstrap 5 modal instance (no jQuery plugin)
  let editModalInstance = null;
  function getEditModal(){
    if (!editModalInstance){
      const el = document.getElementById('editModal');
      if (el) editModalInstance = new bootstrap.Modal(el);
    }
    return editModalInstance;
  }

  function openEdit(id){
    const url = '<?= base_url('admin/manage-users/show') ?>/' + id;
    const em = getEditModal();
    if (em) em.show();
    $('#e_submit').prop('disabled', true).text('Loading...');
    fetch(url, { headers: csrfHash ? { 'X-CSRF-TOKEN': csrfHash } : {} })
      .then(r => r.json())
      .then(data => {
        if (data && data.user){
          const eNameInput = document.getElementById('e_name');
          const eNameError = document.getElementById('e_name_error');
          
          if (eNameInput) {
            eNameInput.value = data.user.name || '';
            document.getElementById('e_email').value = data.user.email || '';
            document.getElementById('e_role').value = (data.user.role||'').toLowerCase();
            document.getElementById('editForm').setAttribute('action', '<?= base_url('admin/manage-users/edit') ?>/' + id);
            // If we came here due to validation errors, prefer previously submitted values
            const hasErrors = <?= json_encode(!empty($errors)) ?>;
            if (hasErrors) {
              if (OLD.name) eNameInput.value = OLD.name;
              if (OLD.email) document.getElementById('e_email').value = OLD.email;
              if (OLD.role) document.getElementById('e_role').value = OLD.role;
            }
            
            // Setup real-time validation for edit modal
            validateNameField(eNameInput, eNameError);
          }
          
          if (data.csrf) updateCsrf(data.csrf);
        } else {
          alert('Failed to load user.');
          const inst = getEditModal(); if (inst) inst.hide();
        }
      })
      .catch(() => { alert('Failed to load user.'); const inst = getEditModal(); if (inst) inst.hide(); })
      .finally(() => { $('#e_submit').prop('disabled', false).text('Update'); });
  }

  document.addEventListener('click', function(e){
    const btn = e.target.closest('.js-edit');
    if (!btn) return;
    const id = btn.getAttribute('data-user-id');
    if (id) openEdit(id);
  });

  // Apply validation to Create User modal
  const cNameInput = document.getElementById('c_name');
  const cNameError = document.getElementById('c_name_error');
  if (cNameInput) {
    validateNameField(cNameInput, cNameError);
  }

  // Auto-open modals based on context (e.g., redirects after validation errors)
  const hasErrors = <?= json_encode(!empty($errors)) ?>;
  const editId = qs.get('edit');
  if (editId && (!hasErrors || hasErrors)) { // open if redirected with ?edit=ID
    openEdit(editId);
  } else if (hasErrors && !editId) {
    const cm = document.getElementById('createModal');
    if (cm) new bootstrap.Modal(cm).show();
  }
})();
</script>

<?= $this->endSection() ?>
