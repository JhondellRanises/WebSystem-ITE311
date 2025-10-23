<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ITE311-RANISES</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<!-- ✅ Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="<?= base_url('/') ?>">
      ITE311-RANISES
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav me-auto">
        <?php if (session()->get('logged_in')): ?>
          <?php $role = session()->get('user_role'); ?>

          <!-- ✅ Admin Navigation -->
          <?php if ($role === 'admin'): ?>
            <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/dashboard') ?>">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/manage-users') ?>">Manage Users</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/manage-courses') ?>">Manage Courses</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= base_url('materials/upload') ?>">Upload Material</a></li>
            

          <!-- ✅ Teacher Navigation -->
          <?php elseif ($role === 'teacher'): ?>
            <li class="nav-item"><a class="nav-link" href="<?= base_url('dashboard') ?>">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= base_url('teacher/courses') ?>">My Courses</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= base_url('teacher/assignments') ?>">Assignments</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= base_url('teacher/students') ?>">Manage Students</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= base_url('materials/upload') ?>">Upload Material</a></li>
            

          <!-- ✅ Student Navigation -->
          <?php elseif ($role === 'student'): ?>
            <li class="nav-item"><a class="nav-link" href="<?= base_url('dashboard') ?>">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= base_url('student/courses') ?>">My Courses</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= base_url('student/grades') ?>">View Grades</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= base_url('materials/student') ?>">Materials</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= base_url('announcements') ?>">Announcements</a></li>
          <?php endif; ?>

        <!-- ✅ Guest Navigation -->
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('home') ?>">Home</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('about') ?>">About</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('contact') ?>">Contact</a></li>
        <?php endif; ?>
      </ul>

      <!-- ✅ Right Side Buttons -->
      <ul class="navbar-nav ms-auto align-items-center">
        <?php if (session()->get('logged_in')): ?>
          <li class="nav-item me-3 d-flex align-items-center">
            <span class="text-light">
              Hi, <strong><?= esc(session()->get('user_name')) ?></strong> (<?= esc($role) ?>)
            </span>
          </li>
          <li class="nav-item">
            <a class="btn btn-danger text-white px-3" href="<?= base_url('logout') ?>">Logout</a>
          </li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('register') ?>">Register</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= base_url('login') ?>">Login</a></li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>


<?php
  $uri = service('uri');
  $path = strtolower(trim($uri->getPath(), '/'));
  // Works even if app is deployed under a subfolder like ITE311-RANISES/...
  $hideGlobalFlash = false;
  if (strpos($path, 'admin/course/') !== false && substr($path, -6) === 'upload') {
      $hideGlobalFlash = true;
  }
  if (strpos($path, 'materials/upload') !== false) {
      $hideGlobalFlash = true;
  }
?>
<?php if (!$hideGlobalFlash): ?>
<!-- ✅ Flash Messages -->
<div class="container mt-3">
  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <?= esc(session()->getFlashdata('success')) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <?= esc(session()->getFlashdata('error')) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<!-- ✅ Main Content -->
<div class="container mt-2">
  <?= $this->renderSection('content') ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- No modal script needed; Upload Material is now a dedicated page. -->
</body>
</html>
