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
    <div class="card shadow-sm mb-4 border-info">
      <div class="card-header bg-info text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-hourglass-half"></i> Pending Enrollment Requests (<?= count($pendingCourses) ?>)</h5>
      </div>
      <div class="card-body">
        <div class="alert alert-info mb-3">
          <i class="fas fa-info-circle"></i> You have been enrolled in these courses by your instructor. Please approve or reject the enrollment.
        </div>
        <div class="table-responsive">
          <table class="table table-hover align-middle">
            <thead>
              <tr>
                <th>Course Title</th>
                <th>Instructor</th>
                <th>Request Date</th>
                <th>Status</th>
                <th class="text-end">Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($pendingCourses as $c): ?>
                <tr>
                  <td><strong><?= esc($c['title']) ?></strong></td>
                  <td><?= esc($c['instructor_name'] ?? 'N/A') ?></td>
                  <td><?= $c['enrollment_date'] ? date('M d, Y', strtotime($c['enrollment_date'])) : 'N/A' ?></td>
                  <td><span class="badge bg-warning text-dark">Pending Your Approval</span></td>
                  <td class="text-end">
                    <form method="post" action="<?= base_url('student/enrollment/approve') ?>" class="d-inline">
                      <?= csrf_field() ?>
                      <input type="hidden" name="course_id" value="<?= (int)$c['id'] ?>">
                      <button type="submit" class="btn btn-sm btn-success" onclick="return confirm('Approve enrollment in <?= esc($c['title']) ?>?')">
                        <i class="fas fa-check"></i> Approve
                      </button>
                    </form>
                    <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#rejectModal<?= (int)$c['id'] ?>">
                      <i class="fas fa-times"></i> Reject
                    </button>
                  </td>
                </tr>

                <!-- Reject Modal -->
                <div class="modal fade" id="rejectModal<?= (int)$c['id'] ?>" tabindex="-1">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Reject Enrollment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <form method="post" action="<?= base_url('student/enrollment/reject') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="course_id" value="<?= (int)$c['id'] ?>">
                        <div class="modal-body">
                          <p>Are you sure you want to reject the enrollment in <strong><?= esc($c['title']) ?></strong>?</p>
                          <div class="mb-3">
                            <label class="form-label">Reason (Optional)</label>
                            <textarea name="reason" class="form-control" rows="3" placeholder="Enter reason for rejection..."></textarea>
                          </div>
                        </div>
                        <div class="modal-footer">
                          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                          <button type="submit" class="btn btn-danger">Reject Enrollment</button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  <?php endif; ?>

  <!-- Rejected Enrollments Section (Only show if there are rejected courses) -->
  <?php if (isset($rejectedCourses) && is_array($rejectedCourses) && count($rejectedCourses) > 0): ?>
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
                <th style="width:100px;">CN</th>
                <th>Course Title</th>
                <th style="width:80px;">Units</th>
                <th style="width:100px;">Semester</th>
                <th style="width:120px;">Instructor</th>
                <th class="text-end" style="width:150px;">Action</th>
              </tr>
            </thead>
            <tbody id="availableCoursesBody">
              <?php foreach ($availableCourses as $c): ?>
                <tr class="available-course-row" data-title="<?= strtolower(esc($c['title'])) ?>" data-description="<?= strtolower(esc($c['description'] ?? '')) ?>" data-course-id="<?= (int)$c['id'] ?>">
                  <td><?= (int)$c['id'] ?></td>
                  <td>
                    <small class="badge bg-secondary"><?= esc($c['course_code'] ?? 'N/A') ?></small>
                  </td>
                  <td>
                    <strong><?= esc($c['title']) ?></strong>
                  </td>
                  <td>
                    <small><?= isset($c['units']) ? number_format($c['units'], 1) : 'N/A' ?></small>
                  </td>
                  <td>
                    <small><?= esc($c['semester'] ?? 'N/A') ?></small>
                  </td>
                  <td>
                    <small><?= esc($c['instructor_name'] ?? 'Unassigned') ?></small>
                  </td>
                  <td class="text-end">
                    <button class="btn btn-info btn-sm view-details-btn" data-course-id="<?= (int)$c['id'] ?>" data-bs-toggle="modal" data-bs-target="#courseDetailsModal">
                      <i class="fas fa-eye"></i> View Details
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

<!-- Course Details Modal -->
<div class="modal fade" id="courseDetailsModal" tabindex="-1">
  <div class="modal-dialog modal-md">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Course Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div id="courseDetailsContent">
          <div class="text-center">
            <div class="spinner-border" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
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

  // Real-time filtering for enrolled courses with character validation
  const allowedSearchPattern = /^[a-z0-9ñ\s]*$/i;
  
  searchInput.on('keyup', function() {
    const rawInput = $(this).val().trim();
    const searchTerm = rawInput.toLowerCase();
    
    // Check if search term contains special characters
    if (rawInput && !allowedSearchPattern.test(rawInput)) {
      // Invalid characters detected - don't search
      coursesBody.html('<tr><td colspan="3" class="text-center text-muted py-4">Please remove special characters from your search.</td></tr>');
      noResults.removeClass('d-none');
      resultsInfo.addClass('d-none');
      return;
    }
    
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
          availableCoursesBody.html('<tr><td colspan="7" class="text-center text-muted py-4">No courses found matching your search.</td></tr>');
          availableNoResults.removeClass('d-none');
          availableResultsInfo.addClass('d-none');
        } else {
          // Build table rows from AJAX response
          let html = '';
          response.forEach(function(course) {
            html += `
              <tr class="available-course-row" data-title="${course.title.toLowerCase()}" data-description="${(course.description || '').toLowerCase()}" data-course-id="${course.id}">
                <td>${course.id}</td>
                <td><small class="badge bg-secondary">${course.course_code || 'N/A'}</small></td>
                <td><strong>${course.title}</strong></td>
                <td><small>${course.units ? parseFloat(course.units).toFixed(1) : 'N/A'}</small></td>
                <td><small>${course.semester || 'N/A'}</small></td>
                <td><small>${course.instructor_name || 'Unassigned'}</small></td>
                <td class="text-end">
                  <button class="btn btn-info btn-sm view-details-btn" data-course-id="${course.id}" data-bs-toggle="modal" data-bs-target="#courseDetailsModal">
                    <i class="fas fa-eye"></i> View Details
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
        availableCoursesBody.html('<tr><td colspan="7" class="text-center text-danger py-4">Error loading courses. Please try again.</td></tr>');
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

  // ========== VIEW DETAILS MODAL ==========
  $(document).on('click', '.view-details-btn', function() {
    const courseId = $(this).data('course-id');
    const courseDetailsContent = $('#courseDetailsContent');
    const enrollBtn = $('#enrollFromDetailsBtn');
    
    // Show loading spinner
    courseDetailsContent.html(`
      <div class="text-center">
        <div class="spinner-border" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
      </div>
    `);
    
    // Fetch course details via AJAX
    $.ajax({
      url: '<?= base_url('courses/details') ?>/' + courseId,
      type: 'GET',
      dataType: 'json',
      success: function(course) {
        // Build course details HTML with full information
        let detailsHtml = `
          <div class="course-details">
            <div class="mb-4 pb-3 border-bottom">
              <h6 class="text-muted mb-2">Course Code</h6>
              <p class="mb-0"><strong>${course.course_code || 'N/A'}</strong></p>
            </div>
            
            <div class="mb-4 pb-3 border-bottom">
              <h6 class="text-muted mb-2">Course Title</h6>
              <p class="mb-0"><strong class="fs-5">${course.title}</strong></p>
            </div>
            
            <div class="mb-4 pb-3 border-bottom">
              <h6 class="text-muted mb-2">Description</h6>
              <p class="mb-0">${course.description || '<em class="text-muted">No description available</em>'}</p>
            </div>
            
            <div class="row mb-4 pb-3 border-bottom">
              <div class="col-md-6 mb-3">
                <h6 class="text-muted mb-2">Units</h6>
                <p class="mb-0"><strong>${course.units ? parseFloat(course.units).toFixed(1) : 'N/A'}</strong></p>
              </div>
              <div class="col-md-6 mb-3">
                <h6 class="text-muted mb-2">Semester</h6>
                <p class="mb-0"><strong>${course.semester || 'N/A'}</strong></p>
              </div>
            </div>
            
            <div class="mb-4 pb-3 border-bottom">
              <h6 class="text-muted mb-2">Instructor</h6>
              <p class="mb-0"><strong>${course.instructor_name || 'Unassigned'}</strong></p>
            </div>
            
            <div class="mb-4 pb-3 border-bottom">
              <h6 class="text-muted mb-2">Term</h6>
              <p class="mb-0"><strong>${course.term || 'N/A'}</strong></p>
            </div>
            
            <div class="mb-4 pb-3 border-bottom">
              <h6 class="text-muted mb-2">Academic Year</h6>
              <p class="mb-0"><strong>${course.academic_year || 'N/A'}</strong></p>
            </div>
            
            <div class="row mb-4 pb-3 border-bottom">
              <div class="col-md-6 mb-3">
                <h6 class="text-muted mb-2">Department</h6>
                <p class="mb-0">${course.department || '<em class="text-muted">N/A</em>'}</p>
              </div>
              <div class="col-md-6 mb-3">
                <h6 class="text-muted mb-2">Program</h6>
                <p class="mb-0">${course.program || '<em class="text-muted">N/A</em>'}</p>
              </div>
            </div>
            
            <div class="mb-4 pb-3 border-bottom">
              <h6 class="text-muted mb-2">Schedule</h6>
              <p class="mb-0">${course.schedule || '<em class="text-muted">No schedule available</em>'}</p>
            </div>
            
            <div class="mb-3">
              <h6 class="text-muted mb-2">Course ID</h6>
              <p class="mb-0"><small class="text-muted">${course.id}</small></p>
            </div>
          </div>
        `;
        
        courseDetailsContent.html(detailsHtml);
      },
      error: function(xhr) {
        courseDetailsContent.html(`
          <div class="alert alert-danger">
            <i class="fas fa-exclamation-circle"></i> Error loading course details. Please try again.
          </div>
        `);
        console.error(xhr.responseText);
      }
    });
  });

  // Initialize enroll listeners
  attachEnrollListeners();
});
</script>

<?= $this->endSection() ?>
