<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<div class="container mt-4">
  <!-- My Enrolled Courses Section -->
  <div class="card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0"><i class="fas fa-book-open"></i> My Enrolled Courses</h5>
    </div>
    <div class="card-body">
      <!-- Search Form for Enrolled Courses -->
      <div class="mb-3">
        <div class="input-group input-group-sm">
          <input 
            type="text" 
            id="searchInput" 
            class="form-control" 
            placeholder="Search your enrolled courses by title..."
            autocomplete="off"
          >
          <button class="btn btn-outline-secondary" type="button" id="clearBtn">
            Clear
          </button>
        </div>
        <small class="form-text text-muted d-block mt-2">
          Type to filter your enrolled courses instantly
        </small>
      </div>

      <!-- Results Counter -->
      <div id="resultsInfo" class="alert alert-info d-none mb-3">
        <i class="fas fa-info-circle"></i> Found <strong id="resultCount">0</strong> course(s)
      </div>

      <!-- No Results Message -->
      <div id="noResults" class="alert alert-warning d-none mb-3">
        <i class="fas fa-exclamation-circle"></i> No courses found matching your search.
      </div>

      <?php if (empty($courses)): ?>
        <div class="alert alert-light border">
          <i class="fas fa-info-circle"></i> You are not enrolled in any approved courses yet. <a href="#availableCourses">Browse available courses below</a>
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover align-middle" id="coursesTable">
            <thead class="table-light">
              <tr>
                <th style="width:80px;">ID</th>
                <th>Course Title</th>
                <th class="text-end" style="width:200px;">Action</th>
              </tr>
            </thead>
            <tbody id="coursesBody">
              <?php foreach ($courses as $c): ?>
                <tr class="course-row" data-title="<?= strtolower(esc($c['title'])) ?>">
                  <td><?= (int)$c['id'] ?></td>
                  <td>
                    <strong><?= esc($c['title']) ?></strong>
                  </td>
                  <td class="text-end">
                    <a class="btn btn-primary btn-sm" href="<?= base_url('student/materials?course_id=' . (int)$c['id']) ?>">
                      <i class="fas fa-book"></i> View Materials
                    </a>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- Pending Enrollments Section -->
  <?php if (!empty($pendingCourses ?? [])): ?>
    <div class="card shadow-sm mb-4 border-warning">
      <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-clock"></i> Pending Enrollment Requests (<?= count($pendingCourses) ?>)</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th>Course Title</th>
                <th>Instructor</th>
                <th>Request Date</th>
                <th>Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($pendingCourses as $c): ?>
                <tr>
                  <td><strong><?= esc($c['title']) ?></strong></td>
                  <td><?= esc($c['instructor_name'] ?? 'N/A') ?></td>
                  <td><?= $c['enrollment_date'] ? date('M d, Y', strtotime($c['enrollment_date'])) : 'N/A' ?></td>
                  <td><span class="badge bg-warning text-dark">Pending Approval</span></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <div class="alert alert-info mt-3 mb-0">
          <i class="fas fa-info-circle"></i> Your enrollment requests are waiting for instructor approval.
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- Rejected Enrollments Section -->
  <?php if (!empty($rejectedCourses ?? [])): ?>
    <div class="card shadow-sm mb-4 border-danger">
      <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-times-circle"></i> Rejected Enrollment Requests (<?= count($rejectedCourses) ?>)</h5>
      </div>
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th>Course Title</th>
                <th>Instructor</th>
                <th>Rejected Date</th>
                <th>Reason</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($rejectedCourses as $c): ?>
                <tr>
                  <td><strong><?= esc($c['title']) ?></strong></td>
                  <td><?= esc($c['instructor_name'] ?? 'N/A') ?></td>
                  <td><?= $c['rejected_at'] ? date('M d, Y', strtotime($c['rejected_at'])) : 'N/A' ?></td>
                  <td>
                    <?php if ($c['rejection_reason']): ?>
                      <span class="text-danger"><?= esc($c['rejection_reason']) ?></span>
                    <?php else: ?>
                      <span class="text-muted">No reason provided</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- Available Courses Section -->
  <div class="card shadow-sm mb-4" id="availableCourses">
    <div class="card-header d-flex justify-content-between align-items-center">
      <h5 class="mb-0"><i class="fas fa-graduation-cap"></i> Available Courses to Enroll</h5>
    </div>
    <div class="card-body">
      <!-- Search Form for Available Courses -->
      <div class="mb-3">
        <div class="input-group input-group-sm">
          <input 
            type="text" 
            id="availableSearchInput" 
            class="form-control" 
            placeholder="Search available courses by title or description..."
            autocomplete="off"
          >
          <button class="btn btn-outline-secondary" type="button" id="availableClearBtn">
            Clear
          </button>
        </div>
        <small class="form-text text-muted d-block mt-2">
          Type to filter available courses instantly
        </small>
      </div>

      <!-- Results Counter for Available Courses -->
      <div id="availableResultsInfo" class="alert alert-info d-none mb-3">
        <i class="fas fa-info-circle"></i> Found <strong id="availableResultCount">0</strong> course(s)
      </div>

      <!-- No Results Message for Available Courses -->
      <div id="availableNoResults" class="alert alert-warning d-none mb-3">
        <i class="fas fa-exclamation-circle"></i> No courses found matching your search.
      </div>

      <!-- Alert for Enrollment Feedback -->
      <div id="enrollmentAlert" class="alert d-none mb-3" role="alert"></div>

      <?php if (!isset($availableCourses) || empty($availableCourses)): ?>
        <div class="alert alert-light border">
          <i class="fas fa-info-circle"></i> No available courses at this time.
        </div>
      <?php else: ?>
        <div class="table-responsive">
          <table class="table table-hover align-middle" id="availableCoursesTable">
            <thead class="table-light">
              <tr>
                <th style="width:80px;">ID</th>
                <th>Course Title</th>
                <th>Description</th>
                <th style="width:120px;">Instructor</th>
                <th class="text-end" style="width:150px;">Action</th>
              </tr>
            </thead>
            <tbody id="availableCoursesBody">
              <?php foreach ($availableCourses as $c): ?>
                <tr class="available-course-row" data-title="<?= strtolower(esc($c['title'])) ?>" data-description="<?= strtolower(esc($c['description'] ?? '')) ?>">
                  <td><?= (int)$c['id'] ?></td>
                  <td>
                    <strong><?= esc($c['title']) ?></strong>
                  </td>
                  <td>
                    <small class="text-muted">
                      <?= esc(substr($c['description'] ?? 'No description', 0, 60)) ?>
                      <?= strlen($c['description'] ?? '') > 60 ? '...' : '' ?>
                    </small>
                  </td>
                  <td>
                    <small><?= esc($c['instructor_name'] ?? 'Unassigned') ?></small>
                  </td>
                  <td class="text-end">
                    <button class="btn btn-success btn-sm enroll-btn" data-course-id="<?= (int)$c['id'] ?>" data-course-title="<?= esc($c['title']) ?>">
                      <i class="fas fa-plus"></i> Enroll
                    </button>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
  // ========== ENROLLED COURSES SEARCH (CLIENT-SIDE) ==========
  const searchInput = $('#searchInput');
  const clearBtn = $('#clearBtn');
  const coursesBody = $('#coursesBody');
  const noResults = $('#noResults');
  const resultsInfo = $('#resultsInfo');
  const resultCount = $('#resultCount');
  let allCourses = [];

  // Store initial courses data
  function storeCoursesData() {
    allCourses = [];
    $('#coursesTable tbody tr.course-row').each(function() {
      allCourses.push({
        id: $(this).find('td').eq(0).text(),
        title: $(this).data('title'),
        html: $(this).prop('outerHTML')
      });
    });
  }

  storeCoursesData();

  // Real-time filtering for enrolled courses
  searchInput.on('keyup', function() {
    const searchTerm = $(this).val().toLowerCase().trim();
    
    if (searchTerm === '') {
      coursesBody.html('');
      allCourses.forEach(course => {
        coursesBody.append(course.html);
      });
      noResults.addClass('d-none');
      resultsInfo.addClass('d-none');
      return;
    }

    const filtered = allCourses.filter(course => 
      course.title.includes(searchTerm)
    );

    if (filtered.length === 0) {
      coursesBody.html('<tr><td colspan="3" class="text-center text-muted py-4">No courses match your search.</td></tr>');
      noResults.removeClass('d-none');
      resultsInfo.addClass('d-none');
    } else {
      coursesBody.html('');
      filtered.forEach(course => {
        coursesBody.append(course.html);
      });
      resultCount.text(filtered.length);
      resultsInfo.removeClass('d-none');
      noResults.addClass('d-none');
    }
  });

  // Clear search for enrolled courses
  clearBtn.on('click', function() {
    searchInput.val('');
    coursesBody.html('');
    allCourses.forEach(course => {
      coursesBody.append(course.html);
    });
    noResults.addClass('d-none');
    resultsInfo.addClass('d-none');
  });

  // ========== AVAILABLE COURSES SEARCH (SERVER-SIDE AJAX) ==========
  const availableSearchInput = $('#availableSearchInput');
  const availableClearBtn = $('#availableClearBtn');
  const availableCoursesBody = $('#availableCoursesBody');
  const availableCoursesTable = $('#availableCoursesTable');
  const availableNoResults = $('#availableNoResults');
  const availableResultsInfo = $('#availableResultsInfo');
  const availableResultCount = $('#availableResultCount');
  let allAvailableCourses = [];
  let searchTimeout;

  // Store available courses data on page load
  function storeAvailableCoursesData() {
    allAvailableCourses = [];
    $('#availableCoursesTable tbody tr.available-course-row').each(function() {
      allAvailableCourses.push({
        id: $(this).find('td').eq(0).text(),
        title: $(this).data('title'),
        description: $(this).data('description'),
        html: $(this).prop('outerHTML')
      });
    });
  }

  storeAvailableCoursesData();

  // AJAX search for available courses
  function performAjaxSearch(searchTerm) {
    $.ajax({
      url: '<?= base_url('courses/search') ?>',
      type: 'GET',
      data: { term: searchTerm },
      dataType: 'json',
      beforeSend: function() {
        availableCoursesTable.css('opacity', '0.5');
      },
      success: function(response) {
        availableCoursesTable.css('opacity', '1');
        
        if (response.length === 0) {
          availableCoursesBody.html('<tr><td colspan="5" class="text-center text-muted py-4">No courses found matching your search.</td></tr>');
          availableNoResults.removeClass('d-none');
          availableResultsInfo.addClass('d-none');
        } else {
          // Build table rows from AJAX response
          let html = '';
          response.forEach(function(course) {
            html += `
              <tr class="available-course-row" data-title="${course.title.toLowerCase()}" data-description="${(course.description || '').toLowerCase()}">
                <td>${course.id}</td>
                <td><strong>${course.title}</strong></td>
                <td><small class="text-muted">${(course.description || 'No description').substring(0, 60)}${(course.description || '').length > 60 ? '...' : ''}</small></td>
                <td><small>${course.instructor_name || 'N/A'}</small></td>
                <td class="text-end">
                  <button class="btn btn-success btn-sm enroll-btn" data-course-id="${course.id}" data-course-title="${course.title}">
                    <i class="fas fa-plus"></i> Enroll
                  </button>
                </td>
              </tr>`;
          });
          availableCoursesBody.html(html);
          availableResultCount.text(response.length);
          availableResultsInfo.removeClass('d-none');
          availableNoResults.addClass('d-none');
          attachEnrollListeners();
        }
      },
      error: function(xhr, status, error) {
        availableCoursesTable.css('opacity', '1');
        console.error('AJAX Error:', error);
        availableCoursesBody.html('<tr><td colspan="5" class="text-center text-danger py-4">Error loading courses. Please try again.</td></tr>');
        availableNoResults.addClass('d-none');
        availableResultsInfo.addClass('d-none');
      }
    });
  }

  // Real-time search with debouncing
  availableSearchInput.on('keyup', function() {
    clearTimeout(searchTimeout);
    const searchTerm = $(this).val().toLowerCase().trim();
    
    if (searchTerm === '') {
      // Show all available courses
      availableCoursesBody.html('');
      allAvailableCourses.forEach(course => {
        availableCoursesBody.append(course.html);
      });
      availableNoResults.addClass('d-none');
      availableResultsInfo.addClass('d-none');
      attachEnrollListeners();
      return;
    }

    // Debounce AJAX search
    searchTimeout = setTimeout(() => {
      performAjaxSearch(searchTerm);
    }, 500);
  });

  // Clear search for available courses
  availableClearBtn.on('click', function() {
    clearTimeout(searchTimeout);
    availableSearchInput.val('');
    availableCoursesBody.html('');
    allAvailableCourses.forEach(course => {
      availableCoursesBody.append(course.html);
    });
    availableNoResults.addClass('d-none');
    availableResultsInfo.addClass('d-none');
    attachEnrollListeners();
  });

  // ========== ENROLLMENT FUNCTIONALITY ==========
  function attachEnrollListeners() {
    $('.enroll-btn').off('click').on('click', function() {
      const courseId = $(this).data('course-id');
      const courseTitle = $(this).data('course-title');
      const button = $(this);

      $.ajax({
        url: "<?= base_url('course/enroll') ?>",
        type: "POST",
        data: {
          course_id: courseId,
          '<?= csrf_token() ?>': '<?= csrf_hash() ?>'
        },
        dataType: 'json',
        success: function(response) {
          const alertBox = $('#enrollmentAlert');
          if (response.status === 'success') {
            alertBox
              .removeClass('d-none alert-danger')
              .addClass('alert-success')
              .html('<i class="fas fa-check-circle"></i> ' + response.message);

            button.prop('disabled', true).html('<i class="fas fa-clock"></i> Pending');
            
            $('html, body').animate({scrollTop: alertBox.offset().top - 100}, 300);
          } else {
            alertBox
              .removeClass('d-none alert-success')
              .addClass('alert-danger')
              .html('<i class="fas fa-exclamation-circle"></i> ' + response.message);
          }
        },
        error: function(xhr) {
          $('#enrollmentAlert')
            .removeClass('d-none alert-success')
            .addClass('alert-danger')
            .html('<i class="fas fa-exclamation-circle"></i> An error occurred. Please try again.');
          console.error(xhr.responseText);
        }
      });
    });
  }

  // Initialize enroll listeners
  attachEnrollListeners();
});
</script>

<?= $this->endSection() ?>
