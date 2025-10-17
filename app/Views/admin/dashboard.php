<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<div class="card shadow-sm">
  <div class="card-body">
    <h1 class="mb-4">Admin Dashboard</h1>
    <p>Welcome, <strong><?= esc(session()->get('user_name')) ?></strong>!</p>

<?= $this->endSection() ?>
