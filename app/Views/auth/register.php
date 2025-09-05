<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="container mt-5">
    <h2 class="mb-4 text-center">Register</h2>
    <div class="card shadow-sm mx-auto" style="max-width: 400px;">
        <div class="card-body">
            <form action="<?= site_url('register/store') ?>" method="post">
                <div class="mb-3">
                    <label for="name" class="form-label">Full Name</label>
                    <input type="text" name="name" class="form-control" id="name" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" id="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" id="password" required>
                </div>
                <div class="mb-3">
                    <label for="role" class="form-label">Role</label>
                    <select name="role" class="form-select" id="role" required>
                        <option value="Student">Student</option>
                        <option value="Instructor">Instructor</option>
                        <option value="Admin">Admin</option>
                    </select>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-dark">Register</button>
                </div>
                 <div class="card-footer text-center">
                    <small>Already have an account? <a href="<?= site_url('login') ?>">Login here</a></small>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
