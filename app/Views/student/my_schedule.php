<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">
  <div class="row mb-4">
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center">
        <h1 class="fw-bold">My Schedule</h1>
        <a href="<?= base_url('student/courses') ?>" class="btn btn-outline-secondary">Back to Courses</a>
      </div>
    </div>
  </div>

  <?php if (empty($schedules)): ?>
    <div class="alert alert-info">
      <i class="bi bi-info-circle"></i> You are not enrolled in any courses yet. <a href="<?= base_url('courses') ?>">Browse available courses</a>
    </div>
  <?php else: ?>
    <!-- Search Form -->
    <div class="row mb-4">
      <div class="col-md-8">
        <div class="input-group">
          <input 
            type="text" 
            id="searchInput" 
            class="form-control" 
            placeholder="Search schedules by course, instructor, room..." 
            autocomplete="off"
          >
          <button class="btn btn-secondary" type="button" id="clearBtn">
            Clear
          </button>
        </div>
        <small class="form-text text-muted mt-2">
          Type to search schedules instantly
        </small>
      </div>
    </div>

    <!-- Results Counter -->
    <div id="resultsInfo" class="alert alert-info d-none mb-3">
      Found <strong id="resultCount">0</strong> schedule(s)
    </div>

    <!-- No Results Message -->
    <div id="noResults" class="alert alert-warning d-none mb-3">
      <i class="fas fa-exclamation-circle"></i> No schedules found matching your search.
    </div>

    <!-- Weekly Schedule View -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card shadow-sm">
          <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Weekly Schedule</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover align-middle" id="schedulesTable">
                <thead class="table-light">
                  <tr>
                    <th>Day</th>
                    <th>Course</th>
                    <th>Instructor</th>
                    <th>Time</th>
                    <th>Duration</th>
                    <th>Room</th>
                    <th>Building</th>
                  </tr>
                </thead>
                <tbody id="schedulesBody">
                  <?php 
                    $dayOrder = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                    $hasSchedules = false;
                    
                    foreach ($dayOrder as $day): 
                      if (!empty($schedulesByDay[$day])):
                        $daySchedules = $schedulesByDay[$day];
                        $hasSchedules = true;
                        foreach ($daySchedules as $schedule):
                  ?>
                    <tr class="schedule-row" data-course="<?= strtolower(esc($schedule['course_code'] . ' ' . $schedule['course_title'])) ?>" data-instructor="<?= strtolower(esc($schedule['instructor_name'] ?? '')) ?>" data-room="<?= strtolower(esc($schedule['room_number'] ?? '')) ?>" data-building="<?= strtolower(esc($schedule['building'] ?? '')) ?>" data-day="<?= strtolower(esc($day)) ?>">
                      <td>
                        <span class="badge bg-info text-dark"><?= esc($day) ?></span>
                      </td>
                      <td>
                        <div class="fw-semibold"><?= esc($schedule['course_code'] ?? '') ?></div>
                        <div class="text-muted small"><?= esc($schedule['course_title']) ?></div>
                      </td>
                      <td><?= esc($schedule['instructor_name'] ?? 'Unassigned') ?></td>
                      <td>
                        <small><?= esc(date('h:i A', strtotime($schedule['start_time']))) ?> - <?= esc(date('h:i A', strtotime($schedule['end_time']))) ?></small>
                      </td>
                      <td>
                        <small><?= esc($schedule['duration_minutes'] ?? 0) ?> mins</small>
                      </td>
                      <td><?= esc($schedule['room_number'] ?? 'N/A') ?></td>
                      <td><?= esc($schedule['building'] ?? 'N/A') ?></td>
                    </tr>
                  <?php 
                        endforeach;
                      endif;
                    endforeach;
                    
                    if (!$hasSchedules):
                  ?>
                    <tr>
                      <td colspan="7" class="text-center text-muted py-4">No schedules for this week</td>
                    </tr>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Schedule Details Card -->
    <div class="row">
      <?php foreach ($schedules as $schedule): ?>
        <div class="col-md-6 col-lg-4 mb-3">
          <div class="card h-100 shadow-sm">
            <div class="card-header bg-light">
              <h6 class="mb-0 fw-bold"><?= esc($schedule['course_code'] ?? '') ?></h6>
            </div>
            <div class="card-body">
              <p class="mb-2">
                <strong>Course:</strong><br>
                <span class="text-muted"><?= esc($schedule['course_title']) ?></span>
              </p>
              <p class="mb-2">
                <strong>Instructor:</strong><br>
                <span class="text-muted"><?= esc($schedule['instructor_name'] ?? 'Unassigned') ?></span>
              </p>
              <p class="mb-2">
                <strong>Day:</strong><br>
                <span class="badge bg-info text-dark"><?= esc($schedule['day_of_week']) ?></span>
              </p>
              <p class="mb-2">
                <strong>Time:</strong><br>
                <span class="text-muted"><?= esc(date('h:i A', strtotime($schedule['start_time']))) ?> - <?= esc(date('h:i A', strtotime($schedule['end_time']))) ?></span>
              </p>
              <p class="mb-2">
                <strong>Duration:</strong><br>
                <span class="text-muted"><?= esc($schedule['duration_minutes'] ?? 0) ?> minutes</span>
              </p>
              <p class="mb-2">
                <strong>Location:</strong><br>
                <span class="text-muted">
                  <?php if ($schedule['room_number'] || $schedule['building']): ?>
                    Room <?= esc($schedule['room_number'] ?? 'N/A') ?>, <?= esc($schedule['building'] ?? 'N/A') ?>
                  <?php else: ?>
                    N/A
                  <?php endif; ?>
                </span>
              </p>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
let allSchedules = [];

$(document).ready(function() {
  const searchInput = $('#searchInput');
  const clearBtn = $('#clearBtn');
  const schedulesBody = $('#schedulesBody');
  const noResults = $('#noResults');
  const resultsInfo = $('#resultsInfo');
  const resultCount = $('#resultCount');

  // Store initial schedules data
  function storeSchedulesData() {
    allSchedules = [];
    $('#schedulesTable tbody tr.schedule-row').each(function() {
      const course = this.getAttribute('data-course') || '';
      const instructor = this.getAttribute('data-instructor') || '';
      const room = this.getAttribute('data-room') || '';
      const building = this.getAttribute('data-building') || '';
      const day = this.getAttribute('data-day') || '';
      
      allSchedules.push({
        course: String(course).toLowerCase(),
        instructor: String(instructor).toLowerCase(),
        room: String(room).toLowerCase(),
        building: String(building).toLowerCase(),
        day: String(day).toLowerCase(),
        html: $(this).prop('outerHTML')
      });
    });
  }

  storeSchedulesData();

  // Client-side filtering
  searchInput.on('keyup', function() {
    const searchTerm = $(this).val().toLowerCase().trim();
    
    if (searchTerm === '') {
      // Show all schedules
      schedulesBody.html('');
      allSchedules.forEach(schedule => {
        schedulesBody.append(schedule.html);
      });
      noResults.addClass('d-none');
      resultsInfo.addClass('d-none');
      return;
    }

    // Filter schedules
    const filtered = allSchedules.filter(schedule => {
      const matches = schedule.course.includes(searchTerm) || 
                      schedule.instructor.includes(searchTerm) || 
                      schedule.room.includes(searchTerm) || 
                      schedule.building.includes(searchTerm) ||
                      schedule.day.includes(searchTerm);
      return matches;
    });

    // Display filtered results
    if (filtered.length === 0) {
      schedulesBody.html('<tr><td colspan="7" class="text-center text-muted py-4">No schedules match your search.</td></tr>');
      noResults.removeClass('d-none');
      resultsInfo.addClass('d-none');
    } else {
      schedulesBody.html('');
      filtered.forEach(schedule => {
        schedulesBody.append(schedule.html);
      });
      resultCount.text(filtered.length);
      resultsInfo.removeClass('d-none');
      noResults.addClass('d-none');
    }
  });

  // Clear search
  clearBtn.on('click', function() {
    searchInput.val('');
    schedulesBody.html('');
    allSchedules.forEach(schedule => {
      schedulesBody.append(schedule.html);
    });
    noResults.addClass('d-none');
    resultsInfo.addClass('d-none');
  });
});
</script>

<?= $this->endSection() ?>
