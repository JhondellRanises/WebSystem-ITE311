<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<div class="container mt-4">
  <div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center bg-primary text-white">
      <h5 class="mb-0">📚 Available Courses</h5>
      <a href="<?= base_url('dashboard') ?>" class="btn btn-sm btn-light">Back to Dashboard</a>
    </div>
    <div class="card-body">
      <!-- Search Form -->
      <div class="row mb-4">
        <div class="col-md-8">
          <div class="input-group">
            <input 
              type="text" 
              id="searchInput" 
              class="form-control" 
              placeholder="🔍 Search courses by title or description..."
              autocomplete="off"
            >
            <button class="btn btn-primary" type="button" id="searchBtn">
              <i class="fas fa-search"></i> Search
            </button>
            <button class="btn btn-secondary" type="button" id="clearBtn">
              Clear
            </button>
          </div>
          <small class="form-text text-muted mt-2">
            Type to filter courses instantly (client-side) or click Search for comprehensive results (server-side)
          </small>
        </div>
      </div>

      <!-- Loading Spinner -->
      <div id="loadingSpinner" class="text-center d-none mb-3">
        <div class="spinner-border text-primary" role="status">
          <span class="visually-hidden">Loading...</span>
        </div>
        <p class="text-muted mt-2">Searching courses...</p>
      </div>

      <!-- Results Counter -->
      <div id="resultsInfo" class="alert alert-info d-none mb-3">
        Found <strong id="resultCount">0</strong> course(s)
      </div>

      <!-- No Results Message -->
      <div id="noResults" class="alert alert-warning d-none mb-3">
        <i class="fas fa-exclamation-circle"></i> No courses found matching your search.
      </div>

      <!-- Courses Table -->
      <div class="table-responsive">
        <table class="table table-hover align-middle" id="coursesTable">
          <thead class="table-light">
            <tr>
              <th style="width:80px;">ID</th>
              <th>Title</th>
              <th>Description</th>
              <th style="width:120px;">Instructor</th>
              <th class="text-end" style="width:180px;">Action</th>
            </tr>
          </thead>
          <tbody id="coursesBody">
            <?php if (empty($courses)): ?>
              <tr>
                <td colspan="5" class="text-center text-muted py-4">
                  No courses available. Check back later!
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($courses as $c): ?>
                <tr class="course-row" data-title="<?= strtolower(esc($c['title'])) ?>" data-description="<?= strtolower(esc($c['description'] ?? '')) ?>">
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
                    <small><?= esc($c['instructor_name'] ?? 'N/A') ?></small>
                  </td>
                  <td class="text-end">
                    <?php if (session()->get('user_role') === 'student'): ?>
                      <span class="badge bg-secondary">Enroll via Teacher</span>
                    <?php else: ?>
                      <button class="btn btn-success btn-sm enroll-btn" data-course-id="<?= (int)$c['id'] ?>" data-course-title="<?= esc($c['title']) ?>">
                        <i class="fas fa-plus"></i> Enroll Student
                      </button>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<!-- Enrollment Success Modal -->
<div class="modal fade" id="enrollmentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">✅ Enrollment Successful</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p id="enrollmentMessage"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <a href="<?= base_url('student/courses') ?>" class="btn btn-primary">View My Courses</a>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
  const searchInput = $('#searchInput');
  const searchBtn = $('#searchBtn');
  const clearBtn = $('#clearBtn');
  const coursesBody = $('#coursesBody');
  const coursesTable = $('#coursesTable');
  const loadingSpinner = $('#loadingSpinner');
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
        description: $(this).data('description'),
        html: $(this).prop('outerHTML')
      });
    });
  }

  storeCoursesData();

  // Client-side filtering
  searchInput.on('keyup', function() {
    const searchTerm = $(this).val().toLowerCase().trim();
    
    if (searchTerm === '') {
      // Show all courses
      coursesBody.html('');
      allCourses.forEach(course => {
        coursesBody.append(course.html);
      });
      noResults.addClass('d-none');
      resultsInfo.addClass('d-none');
      attachEnrollListeners();
      return;
    }

    // Filter courses
    const filtered = allCourses.filter(course => 
      course.title.includes(searchTerm) || course.description.includes(searchTerm)
    );

    // Display filtered results
    if (filtered.length === 0) {
      coursesBody.html('<tr><td colspan="5" class="text-center text-muted py-4">No courses match your search.</td></tr>');
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

    attachEnrollListeners();
  });

  // Server-side search
  searchBtn.on('click', function() {
    const searchTerm = searchInput.val().trim();
    
    if (searchTerm === '') {
      alert('Please enter a search term');
      return;
    }

    loadingSpinner.removeClass('d-none');
    noResults.addClass('d-none');
    resultsInfo.addClass('d-none');

    $.ajax({
      url: '<?= base_url('courses/search') ?>',
      type: 'GET',
      data: { q: searchTerm },
      dataType: 'json',
      success: function(response) {
        loadingSpinner.addClass('d-none');
        
        if (response.status === 'success' && response.data.length > 0) {
          coursesBody.html('');
          response.data.forEach(course => {
            const instructorName = course.instructor_name || 'N/A';
            const description = course.description ? course.description.substring(0, 60) : 'No description';
            const descSuffix = course.description && course.description.length > 60 ? '...' : '';
            
            const row = `
              <tr class="course-row" data-title="${course.title.toLowerCase()}" data-description="${(course.description || '').toLowerCase()}">
                <td>${course.id}</td>
                <td><strong>${escapeHtml(course.title)}</strong></td>
                <td><small class="text-muted">${escapeHtml(description)}${descSuffix}</small></td>
                <td><small>${escapeHtml(instructorName)}</small></td>
                <td class="text-end">
                  <button class="btn btn-success btn-sm enroll-btn" data-course-id="${course.id}" data-course-title="${escapeHtml(course.title)}">
                    <i class="fas fa-plus"></i> Enroll
                  </button>
                </td>
              </tr>
            `;
            coursesBody.append(row);
          });
          
          resultCount.text(response.count);
          resultsInfo.removeClass('d-none');
          noResults.addClass('d-none');
        } else {
          coursesBody.html('<tr><td colspan="5" class="text-center text-muted py-4">No courses found.</td></tr>');
          noResults.removeClass('d-none');
          resultsInfo.addClass('d-none');
        }
        
        attachEnrollListeners();
      },
      error: function(xhr, status, error) {
        loadingSpinner.addClass('d-none');
        alert('Error searching courses. Please try again.');
        console.error('Search error:', error);
      }
    });
  });

  // Clear search
  clearBtn.on('click', function() {
    searchInput.val('');
    coursesBody.html('');
    allCourses.forEach(course => {
      coursesBody.append(course.html);
    });
    noResults.addClass('d-none');
    resultsInfo.addClass('d-none');
    attachEnrollListeners();
  });

  // Enroll button handler
  function attachEnrollListeners() {
    $('.enroll-btn').off('click').on('click', function() {
      const courseId = $(this).data('course-id');
      const courseTitle = $(this).data('course-title');
      const btn = $(this);

      $.ajax({
        url: '<?= base_url('course/enroll') ?>',
        type: 'POST',
        data: { course_id: courseId },
        dataType: 'json',
        success: function(response) {
          if (response.status === 'success') {
            btn.prop('disabled', true).html('<i class="fas fa-check"></i> Enrolled');
            $('#enrollmentMessage').text('You have successfully enrolled in ' + courseTitle);
            new bootstrap.Modal(document.getElementById('enrollmentModal')).show();
          } else {
            alert('Error: ' + response.message);
          }
        },
        error: function() {
          alert('Error enrolling in course. Please try again.');
        }
      });
    });
  }

  // Initialize enroll listeners
  attachEnrollListeners();

  // Helper function to escape HTML
  function escapeHtml(text) {
    const map = {
      '&': '&amp;',
      '<': '&lt;',
      '>': '&gt;',
      '"': '&quot;',
      "'": '&#039;'
    };
    return text.replace(/[&<>"']/g, m => map[m]);
  }
});
</script>

<?= $this->endSection() ?>
