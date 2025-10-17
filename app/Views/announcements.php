<?= $this->extend('templates/header') ?>

<?= $this->section('content') ?>
<div class="container mt-5">

    <!-- ✅ Page Title -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold mb-0">📢 Announcements</h2>
        <?php if (session()->has('logged_in')): ?>
        <?php endif; ?>
    </div>

    <!-- ✅ Flash Messages -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>

    <!-- ✅ Announcements List -->
    <?php if (!empty($announcements)): ?>
        <div class="row">
            <?php foreach ($announcements as $a): ?>
                <div class="col-md-6 mb-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body">
                            <h5 class="card-title fw-bold"><?= esc($a['title']) ?></h5>
                            <p class="card-text"><?= esc($a['content']) ?></p>
                        </div>
                        <div class="card-footer text-muted small">
                            Posted on <?= date('F j, Y g:i A', strtotime($a['created_at'])) ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="alert alert-info text-center">
            No announcements yet.
        </div>
    <?php endif; ?>

</div>
<?= $this->endSection() ?>
