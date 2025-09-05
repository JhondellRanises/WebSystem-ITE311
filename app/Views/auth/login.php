<?= $this->extend('template') ?>

<?= $this->section('content') ?>
<div class="container mt-5">
    <h2 class="mb-4 text-center">Login</h2>
    <div class="card shadow-sm mx-auto" style="max-width: 400px;">
        <div class="card-body">
            <form action="<?= site_url('login/authenticate') ?>" method="post">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" id="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" id="password" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-dark">Login</button>
                </div>
                <div class="card-footer text-center">
                    <small>Don’t have an account? <a href="<?= site_url('register') ?>">Register here</a></small>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
