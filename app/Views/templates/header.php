<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ITE311-RANISES</title>
  <meta name="csrf-token-name" content="<?= esc(csrf_token()) ?>">
  <meta name="csrf-token" content="<?= esc(csrf_hash()) ?>">
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
            <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/manage-users') ?>">Manage User</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/manage-courses') ?>">Manage Courses</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/manage-schedules') ?>">Manage Schedule</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/courses') ?>">My Courses</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/students') ?>">Manage Students</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= base_url('admin/upload') ?>">Upload Material</a></li>
            

          <!-- ✅ Teacher Navigation -->
          <?php elseif ($role === 'teacher'): ?>
            <li class="nav-item"><a class="nav-link" href="<?= base_url('teacher/dashboard') ?>">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= base_url('teacher/courses') ?>">My Courses</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= base_url('teacher/my-schedule') ?>">My Schedule</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= base_url('teacher/assignments') ?>">Assignments</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= base_url('teacher/students') ?>">Manage Students</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= base_url('teacher/upload') ?>">Upload Material</a></li>
            

          <!-- ✅ Student Navigation -->
          <?php elseif ($role === 'student'): ?>
            <li class="nav-item"><a class="nav-link" href="<?= base_url('dashboard') ?>">Dashboard</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= base_url('student/courses') ?>">My Courses</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= base_url('student/my-schedule') ?>">My Schedule</a></li>
            <li class="nav-item"><a class="nav-link" href="<?= base_url('student/grades') ?>">View Grades</a></li>
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
          <li class="nav-item dropdown me-3" data-bs-auto-close="outside">
            <a class="nav-link dropdown-toggle position-relative" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <span>🔔</span>
              <span id="notifBadge" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="display:none;">0</span>
            </a>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notifDropdown" style="min-width: 320px;">
              <li class="px-3 py-2 d-flex justify-content-between align-items-center">
                <span class="fw-semibold">Notifications</span>
                <button id="markAllReadBtn" class="btn btn-sm btn-link text-primary" style="padding: 0; font-size: 0.85rem; display: none;">Mark All Read</button>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li id="notifEmpty" class="px-3 text-muted">No notifications.</li>
              <li class="px-0">
                <div id="notifMenu" class="list-group list-group-flush" style="max-height: 320px; overflow-y: auto;"></div>
              </li>
            </ul>
          </li>
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
  // Hide on all upload pages to avoid duplicate messages
  if (strpos($path, '/upload') !== false) {
      $hideGlobalFlash = true;
  }
  // Avoid double alerts on announcements pages (broad match)
  if (strpos($path, 'announcements') !== false) {
      $hideGlobalFlash = true;
  }
  // Avoid double alerts on manage-schedules pages
  if (strpos($path, 'manage-schedules') !== false) {
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
<script>
  (function(){
    if (!<?= json_encode((bool)session()->get('logged_in')) ?>) return;
    const badge = document.getElementById('notifBadge');
    const menu = document.getElementById('notifMenu');
    const emptyRow = document.getElementById('notifEmpty');
    const csrfName = document.querySelector('meta[name="csrf-token-name"]')?.getAttribute('content');
    let csrfHash = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    // Always include CSRF header on AJAX
    $.ajaxSetup({
      beforeSend: function(xhr, settings){
        if (csrfHash) {
          xhr.setRequestHeader('X-CSRF-TOKEN', csrfHash);
        }
      }
    });

    function render(list){
      menu.innerHTML = '';
      const markAllBtn = document.getElementById('markAllReadBtn');
      if (!list || list.length === 0){
        emptyRow.style.display = 'block';
        if (markAllBtn) markAllBtn.style.display = 'none';
        return;
      }
      emptyRow.style.display = 'none';
      if (markAllBtn) markAllBtn.style.display = 'inline-block';
      list.forEach(function(n){
        const item = document.createElement('div');
        item.className = 'list-group-item d-flex justify-content-between align-items-start';
        
        const contentWrapper = document.createElement('div');
        contentWrapper.className = 'flex-grow-1 me-2';
        
        const msg = document.createElement('div');
        msg.textContent = n.message;
        
        const timeWrapper = document.createElement('div');
        timeWrapper.className = 'text-muted small mt-1';
        
        if (n.created_at) {
          const date = new Date(n.created_at);
          const options = { 
            year: 'numeric', 
            month: 'short', 
            day: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true,
            timeZone: 'Asia/Manila'
          };
          timeWrapper.textContent = date.toLocaleString('en-US', options) + ' (PHT)';
        } else {
          timeWrapper.textContent = 'Just now';
        }
        
        contentWrapper.appendChild(msg);
        contentWrapper.appendChild(timeWrapper);
        
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-sm btn-outline-secondary';
        btn.textContent = 'Mark Read';
        btn.setAttribute('data-notif-id', n.id);
        
        item.appendChild(contentWrapper);
        item.appendChild(btn);
        menu.appendChild(item);
      });
    }

    function fetchNotifs(){
      $.get('<?= base_url('notifications') ?>', { limit: 100 })
        .done(function(res){
          const c = parseInt(res.count || 0, 10);
          if (c > 0){ badge.style.display = 'inline-block'; badge.textContent = c; }
          else { badge.style.display = 'none'; }
          render(res.notifications || []);
          if (res && res.csrf){
            csrfHash = res.csrf;
            const meta = document.querySelector('meta[name="csrf-token"]');
            if (meta) meta.setAttribute('content', csrfHash);
          }
        })
        .fail(function(xhr){ console.error('GET /notifications failed', xhr?.status, xhr?.responseText); });
    }

    // Handler for Mark All as Read button
    $(document).on('click', '#markAllReadBtn', function(e){
      e.preventDefault();
      e.stopPropagation();
      const $btn = $(this);
      $btn.prop('disabled', true).text('...');
      const data = {};
      if (csrfName) data[csrfName] = csrfHash;
      
      $.ajax({
        url: '<?= base_url('notifications/mark_all_read') ?>',
        type: 'POST',
        data: data,
        dataType: 'json',
        headers: {
          'X-CSRF-TOKEN': csrfHash
        },
        success: function(res){
          if(res && res.csrf){
            csrfHash = res.csrf;
            const meta = document.querySelector('meta[name="csrf-token"]');
            if (meta) meta.setAttribute('content', csrfHash);
          }
          fetchNotifs();
        },
        error: function(xhr){
          console.error('mark_all_read POST failed', xhr?.status, xhr?.responseText);
          // Fallback to GET if POST blocked by CSRF settings
          $.ajax({
            url: '<?= base_url('notifications/mark_all_read') ?>',
            type: 'GET',
            dataType: 'json',
            success: function(res){ fetchNotifs(); },
            error: function(){
              console.error('mark_all_read GET also failed');
              alert('Failed to mark all as read. Please try again.');
              fetchNotifs();
            }
          });
        }
      });
    });

    // Delegated handler for Mark Read
    $(document).on('click', '#notifMenu button[data-notif-id]', function(e){
      e.preventDefault();
      e.stopPropagation();
      const id = $(this).data('notif-id');
      const $btn = $(this);
      $btn.prop('disabled', true).text('...');
      const data = { id: id };
      if (csrfName) data[csrfName] = csrfHash;
      // Optimistic UI: remove item now and decrement badge
      const parentItem = $btn.closest('.list-group-item');
      if (parentItem.length) parentItem.remove();
      const currentCount = parseInt(badge.textContent || '0', 10);
      const newCount = Math.max(0, currentCount - 1);
      if (newCount > 0) { badge.style.display = 'inline-block'; badge.textContent = newCount; }
      else { badge.style.display = 'none'; badge.textContent = '0'; }

      $.post('<?= base_url('notifications/mark_read') ?>', data)
        .done(function(res){
          if(res && res.csrf){
            csrfHash = res.csrf;
            const meta = document.querySelector('meta[name="csrf-token"]');
            if (meta) meta.setAttribute('content', csrfHash);
          }
          fetchNotifs();
        })
        .fail(function(xhr){
          // Fallback to GET if POST blocked by CSRF settings
          $.get('<?= base_url('notifications/mark_read') ?>', { id: id })
            .done(function(res){ fetchNotifs(); })
            .fail(function(){
              console.error('mark_read failed', xhr?.status, xhr?.responseText);
              alert('Failed to mark as read. Please try again.');
              fetchNotifs();
            });
        });
    });

    fetchNotifs();
    setInterval(fetchNotifs, 10000);
  })();
</script>
<!-- No modal script needed; Upload Material is now a dedicated page. -->
</body>
</html>
