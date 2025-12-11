<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<div class="card shadow-sm mt-3">
  <div class="card-body">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
      <h2 class="fw-bold mb-2 mb-sm-0">Manage Students</h2>
    </div>

    <!-- Course Selection -->
    <div class="card mb-4">
      <div class="card-body">
        <div class="row g-3">
          <div class="col-md-5">
            <label class="form-label">Select Course</label>
            <form method="get" id="courseForm">
              <select name="course_id" class="form-select" id="courseSelect">
                <option value="">-- Select Course to View Students --</option>
                <?php foreach ($courses as $course): ?>
                  <option value="<?= $course['id'] ?>" <?= (int)$selectedCourseId === (int)$course['id'] ? 'selected' : '' ?>>
                    <?= esc($course['title']) ?> 
                    <?php if ($course['course_code']): ?>
                      (<?= esc($course['course_code']) ?>)
                    <?php endif; ?>
                    <?php if (session()->get('user_role') === 'admin'): ?>
                      - <?= esc($course['instructor_name'] ?? 'Unassigned') ?>
                    <?php endif; ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </form>
          </div>
          <?php if ($selectedCourseId): ?>
            <div class="col-md-5">
              <label class="form-label">Search Students</label>
              <form method="get" id="searchForm" class="d-flex gap-2">
                <input type="text" name="search" class="form-control" placeholder="Search by name or email..." value="<?= esc($search ?? '') ?>">
                <input type="hidden" name="course_id" value="<?= $selectedCourseId ?>">
                <button type="submit" class="btn btn-primary">Search</button>
              </form>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <?php if ($selectedCourse): ?>
      <!-- Course Info -->
      <div class="alert alert-info mb-3">
        <h5 class="mb-2"><?= esc($selectedCourse['title']) ?></h5>
        <div class="row">
          <div class="col-md-6">
            <strong>Course Code:</strong> <?= esc($selectedCourse['course_code'] ?? 'N/A') ?><br>
            <strong>Department:</strong> <?= esc($selectedCourse['department'] ?? '') ?><br>
            <strong>Program:</strong> <?= esc($selectedCourse['program'] ?? '') ?>
          </div>
          <div class="col-md-6">
            <strong>Semester:</strong> <?= esc($selectedCourse['semester'] ?? '') ?><br>
            <strong>Academic Year:</strong> <?= esc($selectedCourse['academic_year'] ?? '') ?><br>
            <strong>Instructor:</strong> <?= esc($selectedCourse['instructor_name'] ?? 'Unassigned') ?>
          </div>
        </div>
      </div>

      <!-- Enroll Student Section -->
      <div class="card mb-4 border-primary">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
          <h5 class="mb-0"><i class="fas fa-user-plus"></i> Enroll Student</h5>
          <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#enrollStudentModal" id="enrollStudentBtn">
            <i class="fas fa-user-plus"></i> Enroll Student
          </button>
        </div>
        <div class="card-body">
          <p class="text-muted mb-0">
            <i class="fas fa-info-circle"></i> Click the "Enroll Student" button above to view available students and enroll them in this course.
          </p>
        </div>
      </div>

      <!-- Pending Enrollments (Awaiting Student Approval) -->
      <?php if (!empty($pendingEnrollments)): ?>
        <div class="card mb-4 border-info">
          <div class="card-header bg-info text-white">
            <h5 class="mb-0"><i class="fas fa-hourglass-half"></i> Pending Enrollments - Awaiting Student Approval (<?= count($pendingEnrollments) ?>)</h5>
          </div>
          <div class="card-body">
            <div class="alert alert-info mb-3">
              <i class="fas fa-info-circle"></i> These students have been sent enrollment requests. They must approve the enrollment before appearing in the "Approved Students" list.
            </div>
            <div class="table-responsive">
              <table class="table table-hover align-middle">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Student Name</th>
                    <th>Email</th>
                    <th>Request Date</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <?php $j = 1; foreach ($pendingEnrollments as $pending): ?>
                    <tr>
                      <td><?= $j++ ?></td>
                      <td><?= esc($pending['student_name']) ?></td>
                      <td><?= esc($pending['student_email']) ?></td>
                      <td><?= $pending['enrollment_date'] ? date('M d, Y H:i', strtotime($pending['enrollment_date'])) : 'N/A' ?></td>
                      <td>
                        <span class="badge bg-warning text-dark">Waiting for Student Approval</span>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <!-- Approved Students List -->
      <div class="card">
        <div class="card-header bg-success text-white">
          <h5 class="mb-0"><i class="fas fa-check-circle"></i> Approved Students</h5>
        </div>
        <div class="card-body">
          <?php if (!empty($students)): ?>
            <div class="table-responsive">
              <table class="table table-hover align-middle">
                <thead>
                  <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Enrollment Date</th>
                    <th>Approved Date</th>
                    <th class="text-end">Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <?php $i = 1; foreach ($students as $student): ?>
                    <tr>
                      <td><?= $i++ ?></td>
                      <td>
                        <div class="fw-semibold"><?= esc($student['name']) ?></div>
                      </td>
                      <td><?= esc($student['email']) ?></td>
                      <td>
                        <?php if ($student['enrollment_date']): ?>
                          <?= date('M d, Y', strtotime($student['enrollment_date'])) ?>
                        <?php else: ?>
                          <span class="text-muted">N/A</span>
                        <?php endif; ?>
                      </td>
                      <td>
                        <?php if ($student['approved_at']): ?>
                          <?= date('M d, Y', strtotime($student['approved_at'])) ?>
                        <?php else: ?>
                          <span class="text-muted">N/A</span>
                        <?php endif; ?>
                      </td>
                      <td class="text-end">
                        <a href="<?= base_url('admin/manage-users?search=' . urlencode($student['email'])) ?>" class="btn btn-sm btn-outline-primary">
                          <i class="fas fa-user"></i> View Profile
                        </a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>

            <div class="mt-3">
              <p class="text-muted">
                <strong>Total Approved Students:</strong> <?= count($students) ?>
              </p>
            </div>
          <?php else: ?>
            <div class="alert alert-light border">
              <i class="fas fa-info-circle"></i> 
              <?php if ($search): ?>
                No approved students found matching "<?= esc($search) ?>".
              <?php else: ?>
                No approved students enrolled in this course yet.
              <?php endif; ?>
            </div>
          <?php endif; ?>
        </div>
      </div>
    <?php else: ?>
      <div class="alert alert-info">
        <i class="fas fa-info-circle"></i> Please select a course from the dropdown above to view enrolled students.
      </div>
    <?php endif; ?>
  </div>
</div>

<!-- Enroll Student Modal (Always Available) -->
<div class="modal fade" id="enrollStudentModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">
          <i class="fas fa-user-plus"></i> Enroll Student<?= $selectedCourse ? ' in ' . esc($selectedCourse['title']) : '' ?>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <?php if ($selectedCourse): ?>
        <!-- Course Info -->
        <div class="alert alert-info mb-3">
          <strong>Course:</strong> <?= esc($selectedCourse['title']) ?> (<?= esc($selectedCourse['course_code'] ?? 'N/A') ?>)<br>
          <strong>Department:</strong> <?= esc($selectedCourse['department'] ?? 'N/A') ?>
        </div>
        <?php else: ?>
        <div class="alert alert-warning mb-3">
          <i class="fas fa-exclamation-triangle"></i> Please select a course first from the dropdown above.
        </div>
        <?php endif; ?>

        <!-- Search Input -->
        <div class="mb-3">
          <label class="form-label"><i class="fas fa-search"></i> Search Student</label>
          <input 
            type="text" 
            id="studentSearchInput" 
            class="form-control" 
            placeholder="Type student name or email to search..."
            autocomplete="off"
          >
          <small class="text-muted">Leave empty to show all available students</small>
        </div>

        <!-- Student List -->
        <div id="studentListContainer">
          <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-2 text-muted">Loading students...</p>
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
          <i class="fas fa-times"></i> Close
        </button>
      </div>
    </div>
  </div>
</div>

<script>
// Wait for document ready
if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initStudentEnrollment);
} else {
  initStudentEnrollment();
}

function initStudentEnrollment() {
  console.log('Initializing student enrollment...');
  
  const searchInput = document.getElementById('studentSearchInput');
  const studentListContainer = document.getElementById('studentListContainer');
  const enrollStudentModal = document.getElementById('enrollStudentModal');
  const courseSelect = document.getElementById('courseSelect');
  const courseForm = document.getElementById('courseForm');
  const courseId = <?= $selectedCourseId ?? 0 ?>;
  
  console.log('Course ID:', courseId);
  console.log('Elements found - searchInput:', !!searchInput, 'container:', !!studentListContainer, 'courseSelect:', !!courseSelect);

  // Handle course selection change
  if (courseSelect && courseForm) {
    courseSelect.addEventListener('change', function() {
      console.log('Course selected:', this.value);
      if (this.value) {
        // Create a temporary form to submit
        const tempForm = document.createElement('form');
        tempForm.method = 'GET';
        tempForm.action = window.location.pathname;
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'course_id';
        input.value = this.value;
        tempForm.appendChild(input);
        document.body.appendChild(tempForm);
        tempForm.submit();
      }
    });
  }

  if (!searchInput || !studentListContainer) {
    console.error('Required elements not found!');
    return;
  }

  // Search on input
  if (searchInput) {
    let searchTimeout;
    searchInput.addEventListener('input', function() {
      clearTimeout(searchTimeout);
      const query = this.value.trim();
      searchTimeout = setTimeout(() => {
        loadStudents(query);
      }, 300);
    });

    // Search on Enter
    searchInput.addEventListener('keypress', function(e) {
      if (e.which === 13 || e.key === 'Enter') {
        e.preventDefault();
        loadStudents(this.value.trim());
      }
    });
  }

  // Modal open event
  if (enrollStudentModal) {
    enrollStudentModal.addEventListener('show.bs.modal', function() {
      console.log('Modal opening, courseId:', courseId);
      if (!courseId || courseId === 0) {
        studentListContainer.innerHTML = '<div class="alert alert-warning text-center"><i class="fas fa-exclamation-circle"></i> Please select a course first.</div>';
        return;
      }
      searchInput.value = '';
      loadStudents('');
    });
  }

  function loadStudents(query) {
    console.log('loadStudents called with query:', query, 'courseId:', courseId);
    
    if (!courseId || courseId === 0) {
      studentListContainer.innerHTML = '<div class="alert alert-warning text-center"><i class="fas fa-exclamation-circle"></i> Please select a course first.</div>';
      return;
    }

    studentListContainer.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div><p class="mt-2 text-muted">Loading students...</p></div>';

    const params = new URLSearchParams();
    params.append('q', query || '');
    params.append('course_id', courseId);

    fetch('<?= base_url('teacher/students/search') ?>?' + params.toString(), {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest'
      }
    })
    .then(response => {
      console.log('Search response status:', response.status);
      if (!response.ok) throw new Error('HTTP ' + response.status);
      return response.json();
    })
    .then(data => {
      console.log('Search data:', data);
      if (data.status === 'success' && data.students && data.students.length > 0) {
        let html = '<div class="table-responsive"><table class="table table-hover align-middle mb-0"><thead class="table-light"><tr><th style="width: 5%;">\#</th><th>Student Name</th><th>Email</th><th class="text-end" style="width: 20%;">Action</th></tr></thead><tbody>';
        
        data.students.forEach((student, index) => {
          const name = escapeHtml(student.name);
          const email = escapeHtml(student.email);
          html += '<tr><td><span class="badge bg-secondary">' + (index + 1) + '</span></td><td><strong>' + name + '</strong></td><td><small class="text-muted">' + email + '</small></td><td class="text-end"><button class="btn btn-sm btn-primary enroll-btn" data-student-id="' + student.id + '" data-student-name="' + name + '" data-student-email="' + email + '"><i class="fas fa-user-plus"></i> Enroll</button></td></tr>';
        });
        
        html += '</tbody></table><div class="mt-3 text-muted small text-center"><i class="fas fa-info-circle"></i> Found ' + data.students.length + ' student(s)</div></div>';
        studentListContainer.innerHTML = html;
        attachEnrollHandlers();
      } else {
        const msg = query ? 'No students found matching "' + query + '".' : 'No available students to enroll.';
        studentListContainer.innerHTML = '<div class="alert alert-info text-center"><i class="fas fa-info-circle"></i> ' + msg + '</div>';
      }
    })
    .catch(error => {
      console.error('Search error:', error);
      studentListContainer.innerHTML = '<div class="alert alert-danger text-center"><i class="fas fa-exclamation-circle"></i> <strong>Error: ' + error.message + '</strong></div>';
    });
  }

  function attachEnrollHandlers() {
    document.querySelectorAll('.enroll-btn').forEach(btn => {
      btn.addEventListener('click', function(e) {
        e.preventDefault();
        const studentId = this.getAttribute('data-student-id');
        const studentName = this.getAttribute('data-student-name');
        const studentEmail = this.getAttribute('data-student-email');

        if (!confirm('Enroll ' + studentName + ' (' + studentEmail + ') in this course?')) {
          return;
        }

        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Enrolling...';

        const formData = new FormData();
        formData.append('student_id', studentId);
        formData.append('course_id', courseId);
        formData.append('<?= csrf_token() ?>', '<?= csrf_hash() ?>');

        fetch('<?= base_url('teacher/students/enroll') ?>', {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          },
          body: formData
        })
        .then(response => {
          if (!response.ok) throw new Error('HTTP ' + response.status);
          return response.json();
        })
        .then(data => {
          window.location.reload();
        })
        .catch(error => {
          console.error('Enroll error:', error);
          alert('Error enrolling student: ' + error.message);
          this.disabled = false;
          this.innerHTML = '<i class="fas fa-user-plus"></i> Enroll';
        });
      });
    });
  }

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
}
</script>

<?= $this->endSection() ?>

