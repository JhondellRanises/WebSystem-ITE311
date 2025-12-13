<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<div class="card shadow-sm mt-3">
  <div class="card-body">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
      <h2 class="fw-bold mb-2 mb-sm-0">My Courses</h2>
    </div>

    <!-- Search Form for Courses -->
    <div class="mb-3">
      <div class="input-group input-group-sm">
        <input 
          type="text" 
          id="courseSearchInput" 
          class="form-control" 
          placeholder="Search courses by title, code, or department..."
          autocomplete="off"
        >
        <button class="btn btn-outline-secondary" type="button" id="courseClearBtn">
          Clear
        </button>
      </div>
      <small class="form-text text-muted d-block mt-2">
        Type to filter your courses instantly
      </small>
    </div>

    <!-- Results Counter -->
    <div id="courseResultsInfo" class="alert alert-info d-none mb-3">
      <i class="fas fa-info-circle"></i> Found <strong id="courseResultCount">0</strong> course(s)
    </div>

    <!-- No Results Message -->
    <div id="courseNoResults" class="alert alert-warning d-none mb-3">
      <i class="fas fa-exclamation-circle"></i> No courses found matching your search.
    </div>

    <?php if (!empty($courses)): ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>#</th>
              <th>Course Code</th>
              <th>Title</th>
              <th>Department</th>
              <th>Program</th>
              <th>Instructor</th>
              <th>Students</th>
              <th>Materials</th>
              <th>Semester</th>
              <th>Academic Year</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody id="coursesTableBody">
            <?php $i = 1; foreach ($courses as $c): ?>
              <tr class="course-row" data-title="<?= strtolower(esc($c['title'])) ?>" data-code="<?= strtolower(esc($c['course_code'] ?? '')) ?>" data-department="<?= strtolower(esc($c['department'] ?? '')) ?>">
                <td><?= $i++ ?></td>
                <td><span class="badge bg-secondary"><?= esc($c['course_code'] ?? 'N/A') ?></span></td>
                <td>
                  <div class="fw-semibold"><?= esc($c['title']) ?></div>
                  <div class="text-muted small"><?= esc(mb_strimwidth($c['description'] ?? '', 0, 60, '...')) ?></div>
                </td>
                <td><?= esc($c['department'] ?? '') ?></td>
                <td><?= esc($c['program'] ?? '') ?></td>
                <td><?= esc($c['instructor_name'] ?? 'Unassigned') ?></td>
                <td><span class="badge bg-info text-dark"><?= (int)($c['student_count'] ?? 0) ?></span></td>
                <td><span class="badge bg-primary"><?= (int)($c['material_count'] ?? 0) ?></span></td>
                <td><?= esc($c['semester'] ?? '') ?></td>
                <td><?= esc($c['academic_year'] ?? '') ?></td>
                <td class="text-end">
                  <a href="<?= base_url('teacher/students?course_id=' . $c['id']) ?>" class="btn btn-sm btn-outline-success">
                    <i class="fas fa-users"></i> View Students
                  </a>
                  <a href="<?= base_url('teacher/upload?course_id=' . $c['id']) ?>" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-upload"></i> Upload Material
                  </a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="alert alert-light border">
        <i class="fas fa-info-circle"></i> You don't have any assigned courses yet.
      </div>
    <?php endif; ?>
  </div>
</div>

<style>
  .table tbody tr:hover {
    background-color: rgba(102, 126, 234, 0.05);
  }
  
  .btn-sm {
    padding: 0.4rem 0.8rem;
    font-size: 0.875rem;
  }
  
  .badge {
    padding: 0.35rem 0.65rem;
    font-weight: 500;
  }
</style>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
  const searchInput = $('#courseSearchInput');
  const clearBtn = $('#courseClearBtn');
  const coursesTableBody = $('#coursesTableBody');
  const noResults = $('#courseNoResults');
  const resultsInfo = $('#courseResultsInfo');
  const resultCount = $('#courseResultCount');
  let allCourses = [];

  // Store initial courses data
  function storeCoursesData() {
    allCourses = [];
    $('#coursesTableBody tr.course-row').each(function() {
      allCourses.push({
        title: $(this).data('title'),
        code: $(this).data('code'),
        department: $(this).data('department'),
        html: $(this).prop('outerHTML')
      });
    });
  }

  storeCoursesData();

  // Real-time filtering for courses with character validation
  const allowedSearchPattern = /^[a-z0-9ñ\s]*$/i;
  
  searchInput.on('keyup', function() {
    const rawInput = $(this).val().trim();
    const searchTerm = rawInput.toLowerCase();
    
    // Check if search term contains special characters
    if (rawInput && !allowedSearchPattern.test(rawInput)) {
      // Invalid characters detected - don't search
      coursesTableBody.html('<tr><td colspan="11" class="text-center text-muted py-4">Please remove special characters from your search.</td></tr>');
      noResults.removeClass('d-none');
      resultsInfo.addClass('d-none');
      return;
    }
    
    if (searchTerm === '') {
      coursesTableBody.html('');
      allCourses.forEach(course => {
        coursesTableBody.append(course.html);
      });
      noResults.addClass('d-none');
      resultsInfo.addClass('d-none');
      return;
    }

    const filtered = allCourses.filter(course => 
      course.title.includes(searchTerm) ||
      course.code.includes(searchTerm) ||
      course.department.includes(searchTerm)
    );

    if (filtered.length === 0) {
      coursesTableBody.html('<tr><td colspan="11" class="text-center text-muted py-4">No courses match your search.</td></tr>');
      noResults.removeClass('d-none');
      resultsInfo.addClass('d-none');
    } else {
      coursesTableBody.html('');
      filtered.forEach(course => {
        coursesTableBody.append(course.html);
      });
      resultCount.text(filtered.length);
      resultsInfo.removeClass('d-none');
      noResults.addClass('d-none');
    }
  });

  // Clear search for courses
  clearBtn.on('click', function() {
    searchInput.val('');
    coursesTableBody.html('');
    allCourses.forEach(course => {
      coursesTableBody.append(course.html);
    });
    noResults.addClass('d-none');
    resultsInfo.addClass('d-none');
  });
});
</script>

<?= $this->endSection() ?>

