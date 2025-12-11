<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">
  <div class="row mb-4">
    <div class="col-12">
      <div class="d-flex justify-content-between align-items-center">
        <h1 class="fw-bold">My Schedule</h1>
        <a href="<?= base_url('teacher/courses') ?>" class="btn btn-outline-secondary">Back to Courses</a>
      </div>
    </div>
  </div>

  <?php if (empty($schedules)): ?>
    <div class="alert alert-info">
      <i class="bi bi-info-circle"></i> No schedules assigned yet.
    </div>
  <?php else: ?>
    <!-- Weekly Schedule View -->
    <div class="row mb-4">
      <div class="col-12">
        <div class="card shadow-sm">
          <div class="card-header bg-primary text-white">
            <h5 class="mb-0">Weekly Schedule</h5>
          </div>
          <div class="card-body">
            <div class="table-responsive">
              <table class="table table-hover align-middle">
                <thead class="table-light">
                  <tr>
                    <th>Day</th>
                    <th>Course</th>
                    <th>Time</th>
                    <th>Duration</th>
                    <th>Room</th>
                    <th>Building</th>
                    <th>Capacity</th>
                  </tr>
                </thead>
                <tbody>
                  <?php 
                    $dayOrder = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                    $hasSchedules = false;
                    
                    foreach ($dayOrder as $day): 
                      if (!empty($schedulesByDay[$day])):
                        $daySchedules = $schedulesByDay[$day];
                        $hasSchedules = true;
                        foreach ($daySchedules as $schedule):
                  ?>
                    <tr>
                      <td>
                        <span class="badge bg-info text-dark"><?= esc($day) ?></span>
                      </td>
                      <td>
                        <div class="fw-semibold"><?= esc($schedule['course_code'] ?? '') ?></div>
                        <div class="text-muted small"><?= esc($schedule['course_title']) ?></div>
                      </td>
                      <td>
                        <small><?= esc(date('h:i A', strtotime($schedule['start_time']))) ?> - <?= esc(date('h:i A', strtotime($schedule['end_time']))) ?></small>
                      </td>
                      <td>
                        <small><?= esc($schedule['duration_minutes'] ?? 0) ?> mins</small>
                      </td>
                      <td><?= esc($schedule['room_number'] ?? 'N/A') ?></td>
                      <td><?= esc($schedule['building'] ?? 'N/A') ?></td>
                      <td>
                        <?php if ($schedule['capacity']): ?>
                          <span class="badge bg-secondary"><?= esc($schedule['capacity']) ?></span>
                        <?php else: ?>
                          <span class="text-muted">-</span>
                        <?php endif; ?>
                      </td>
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
              <?php if ($schedule['capacity']): ?>
                <p class="mb-2">
                  <strong>Capacity:</strong><br>
                  <span class="badge bg-secondary"><?= esc($schedule['capacity']) ?></span>
                </p>
              <?php endif; ?>
              <?php if ($schedule['notes']): ?>
                <p class="mb-0">
                  <strong>Notes:</strong><br>
                  <span class="text-muted small"><?= esc($schedule['notes']) ?></span>
                </p>
              <?php endif; ?>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<?= $this->endSection() ?>
