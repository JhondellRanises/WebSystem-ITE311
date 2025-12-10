<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="container mt-5">
    <h2 class="mb-4 text-center">Register</h2>

    <?php if(session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>
    <?php if(session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger">
            <ul class="mb-0">
                <?php foreach(session()->getFlashdata('errors') as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mx-auto" style="max-width: 400px;">
        <div class="card-body">
            <form action="<?= site_url('register') ?>" method="post">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" id="name" value="<?= old('name') ?>" pattern="[a-zA-Z0-9\s\-\'\.]+" title="Only letters, numbers, spaces, hyphens, apostrophes, and periods are allowed. Special characters are not allowed." required>
                    <div class="invalid-feedback" id="name_error" style="display: none;">
                        The name field cannot contain special characters. Only letters, numbers, spaces, hyphens, apostrophes, and periods are allowed.
                    </div>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input
                        type="email"
                        name="email"
                        class="form-control"
                        id="email"
                        value="<?= old('email') ?>"
                        pattern="^[a-zA-Z0-9]+(?:\.?[a-zA-Z0-9]+)*@gmail\.com$"
                        title="Use a Gmail address with letters, numbers, and periods only."
                        required
                    >
                    <div class="invalid-feedback" id="email_error" style="display: none;">
                        Please use a valid Gmail address without special characters (letters, numbers, and periods only).
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" id="password" required>
                </div>

                <div class="mb-3">
                    <label for="confirm_password" class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" id="confirm_password" required>
                </div>

                <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <select name="role" class="form-select" id="role" required>
                        <option value="admin" <?= old('role') === 'admin' ? 'selected' : '' ?>>Admin</option>
                        <option value="teacher" <?= old('role') === 'teacher' ? 'selected' : '' ?>>Teacher</option>
                        <option value="student" <?= old('role') === 'student' ? 'selected' : '' ?>>Student</option>                             
                    </select>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-dark">Register</button>
                </div>

                <div class="card-footer text-center mt-2">
                    <small>Already have an account? <a href="<?= site_url('login') ?>">Login here</a></small>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function(){
  // Real-time validation for Registration form
  const nameInput = document.getElementById('name');
  const nameError = document.getElementById('name_error');
  const emailInput = document.getElementById('email');
  const emailError = document.getElementById('email_error');

  function validateName(value){
    const namePattern = /^[a-zA-Z0-9\s\-\'\.]*$/;
    return !value || namePattern.test(value);
  }

  function validateGmail(value){
    const gmailPattern = /^[a-zA-Z0-9]+(?:\.?[a-zA-Z0-9]+)*@gmail\.com$/;
    return !value || gmailPattern.test(value);
  }

  if (nameInput) {
    const handler = function() {
      const valid = validateName(this.value);
      this.classList.toggle('is-invalid', !valid);
      if (nameError) nameError.style.display = valid ? 'none' : 'block';
    };
    nameInput.addEventListener('input', handler);
    nameInput.addEventListener('paste', () => setTimeout(handler, 10));
  }

  if (emailInput) {
    const handler = function() {
      const valid = validateGmail(this.value);
      this.classList.toggle('is-invalid', !valid);
      if (emailError) emailError.style.display = valid ? 'none' : 'block';
    };
    emailInput.addEventListener('input', handler);
    emailInput.addEventListener('paste', () => setTimeout(handler, 10));
  }
})();
</script>

<?= $this->endSection() ?>
