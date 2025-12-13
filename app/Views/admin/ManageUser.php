<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<?php
  $errors = session()->getFlashdata('errors') ?? [];
  $nameError = $errors['name'] ?? null;
  $emailError = $errors['email'] ?? null;
?>

<div class="card shadow-sm mt-3">
  <div class="card-body">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
      <h2 class="fw-bold mb-2 mb-sm-0">Manage Users</h2>
      <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#createModal">Add User</button>
    </div>

    <!-- Search Form for Users -->
    <div class="mb-3">
      <div class="input-group input-group-sm">
        <input 
          type="text" 
          id="userSearchInput" 
          class="form-control" 
          placeholder="Search users by name or email..."
          autocomplete="off"
        >
        <button class="btn btn-outline-secondary" type="button" id="userClearBtn">
          Clear
        </button>
      </div>
      <small class="form-text text-muted d-block mt-2">
        Type to filter users instantly
      </small>
    </div>

    <!-- Results Counter -->
    <div id="userResultsInfo" class="alert alert-info d-none mb-3">
      <i class="fas fa-info-circle"></i> Found <strong id="userResultCount">0</strong> user(s)
    </div>

    <!-- No Results Message -->
    <div id="userNoResults" class="alert alert-warning d-none mb-3">
      <i class="fas fa-exclamation-circle"></i> No users found matching your search.
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
          <tbody id="userTableBody">
            <?php $i = 1; foreach ($users as $u): 
              $isDeleted = !empty($u['deleted_at']);
              $isAdmin = $u['role'] === 'admin';
              $isCurrentUser = (int)$u['id'] === (int)session()->get('user_id');
            ?>
              <tr class="user-row <?= $isDeleted ? 'table-secondary' : '' ?>" data-name="<?= strtolower(esc($u['name'])) ?>" data-email="<?= strtolower(esc($u['email'])) ?>">
                <td><?= $i++ ?></td>
                <td><?= esc($u['name']) ?></td>
                <td><?= esc($u['email']) ?></td>
                <td>
                  <span class="badge <?= $isAdmin ? 'bg-danger' : 'bg-secondary' ?> text-uppercase">
                    <?= esc($u['role']) ?>
                  </span>
                </td>
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
                    <?php if (!$isAdmin): ?>
                      <form action="<?= base_url('admin/manage-users/restore/' . $u['id']) ?>" method="post" class="d-inline">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Restore this user? User will be reactivated.')">
                          <i class="bi bi-arrow-counterclockwise"></i> Restore
                        </button>
                      </form>
                    <?php else: ?>
                      <span class="text-muted small">Admin (Cannot restore)</span>
                    <?php endif; ?>
                  <?php else: ?>
                    <!-- Edit and Delete buttons for active users -->
                    <?php if (!$isCurrentUser): ?>
                      <!-- Show Edit button for all users except current user -->
                      <button class="btn btn-sm btn-outline-primary js-edit" data-user-id="<?= (int)$u['id'] ?>">Edit</button>
                    <?php endif; ?>
                    
                    <?php if (!$isAdmin && !$isCurrentUser): ?>
                      <!-- Show Delete button only for non-admin users and not current user -->
                      <form action="<?= base_url('admin/manage-users/delete/' . $u['id']) ?>" method="post" class="d-inline">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this user? You can restore it later.')">Delete</button>
                      </form>
                    <?php elseif ($isAdmin): ?>
                      <!-- Admin user: show protected message (cannot delete) -->
                      <span class="text-muted small">Admin (Protected)</span>
                    <?php elseif ($isCurrentUser): ?>
                      <!-- Current user: show disabled message -->
                      <span class="text-muted small">(Your account)</span>
                    <?php endif; ?>
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
          <?php if ($nameError): ?>
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
              <input
                type="email"
                class="form-control <?= $emailError ? 'is-invalid' : '' ?>"
                id="c_email"
                name="email"
                value="<?= esc(old('email')) ?>"
                pattern="^[a-zA-Z0-9]+(?:\.?[a-zA-Z0-9]+)*@gmail\.com$"
                title="Use a Gmail address with letters, numbers, and periods only."
                required
              >
              <div class="invalid-feedback" id="c_email_error" style="display: none;">
                Please use a valid Gmail address without special characters (letters, numbers, and periods only).
              </div>
              <?php if ($emailError): ?>
                <div class="invalid-feedback d-block"><?= esc($emailError) ?></div>
              <?php endif; ?>
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
          <?php if ($nameError): ?>
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
              <input
                type="email"
                class="form-control <?= $emailError ? 'is-invalid' : '' ?>"
                id="e_email"
                name="email"
                pattern="^[a-zA-Z0-9]+(?:\.?[a-zA-Z0-9]+)*@gmail\.com$"
                title="Use a Gmail address with letters, numbers, and periods only."
                required
              >
              <div class="invalid-feedback" id="e_email_error" style="display: none;">
                Please use a valid Gmail address without special characters (letters, numbers, and periods only).
              </div>
              <?php if ($emailError): ?>
                <div class="invalid-feedback d-block"><?= esc($emailError) ?></div>
              <?php endif; ?>
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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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

  // Real-time Gmail validation function
  function validateGmailField(inputElement, errorElement) {
    if (!inputElement) return;

    if (inputElement.dataset.validated === 'true') return;
    inputElement.dataset.validated = 'true';

    const gmailPattern = /^[a-zA-Z0-9]+(?:\.?[a-zA-Z0-9]+)*@gmail\.com$/;

    function runValidation(target){
      const value = target.value;
      const isValid = !value || gmailPattern.test(value);
      target.classList.toggle('is-invalid', !isValid);
      if (errorElement) errorElement.style.display = isValid ? 'none' : 'block';
    }

    inputElement.addEventListener('input', function() { runValidation(this); });
    inputElement.addEventListener('paste', function() { setTimeout(() => runValidation(this), 10); });
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
            validateGmailField(document.getElementById('e_email'), document.getElementById('e_email_error'));
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
  const cEmailInput = document.getElementById('c_email');
  const cEmailError = document.getElementById('c_email_error');
  if (cNameInput) {
    validateNameField(cNameInput, cNameError);
  }
  if (cEmailInput) {
    validateGmailField(cEmailInput, cEmailError);
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

// ========== MANAGE USERS SEARCH ==========
$(document).ready(function() {
  const searchInput = $('#userSearchInput');
  const clearBtn = $('#userClearBtn');
  const userTableBody = $('#userTableBody');
  const noResults = $('#userNoResults');
  const resultsInfo = $('#userResultsInfo');
  const resultCount = $('#userResultCount');
  let allUsers = [];

  // Store initial users data
  function storeUsersData() {
    allUsers = [];
    $('#userTableBody tr.user-row').each(function() {
      allUsers.push({
        name: $(this).data('name'),
        email: $(this).data('email'),
        html: $(this).prop('outerHTML')
      });
    });
  }

  storeUsersData();

  // Real-time filtering for users with character validation
  const allowedSearchPattern = /^[a-z0-9ñ\s]*$/i;
  
  searchInput.on('keyup', function() {
    const rawInput = $(this).val().trim();
    const searchTerm = rawInput.toLowerCase();
    
    // Check if search term contains special characters
    if (rawInput && !allowedSearchPattern.test(rawInput)) {
      // Invalid characters detected - don't search
      userTableBody.html('<tr><td colspan="7" class="text-center text-muted py-4">Please remove special characters from your search.</td></tr>');
      noResults.removeClass('d-none');
      resultsInfo.addClass('d-none');
      return;
    }
    
    if (searchTerm === '') {
      userTableBody.html('');
      allUsers.forEach(user => {
        userTableBody.append(user.html);
      });
      noResults.addClass('d-none');
      resultsInfo.addClass('d-none');
      return;
    }

    const filtered = allUsers.filter(user => 
      user.name.includes(searchTerm) ||
      user.email.includes(searchTerm)
    );

    if (filtered.length === 0) {
      userTableBody.html('<tr><td colspan="7" class="text-center text-muted py-4">No users match your search.</td></tr>');
      noResults.removeClass('d-none');
      resultsInfo.addClass('d-none');
    } else {
      userTableBody.html('');
      filtered.forEach(user => {
        userTableBody.append(user.html);
      });
      resultCount.text(filtered.length);
      resultsInfo.removeClass('d-none');
      noResults.addClass('d-none');
    }
  });

  // Clear search for users
  clearBtn.on('click', function() {
    searchInput.val('');
    userTableBody.html('');
    allUsers.forEach(user => {
      userTableBody.append(user.html);
    });
    noResults.addClass('d-none');
    resultsInfo.addClass('d-none');
  });
});
</script>

<?= $this->endSection() ?>
