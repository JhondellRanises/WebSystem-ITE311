<?= $this->extend('templates/header') ?>
<?= $this->section('content') ?>

<?php
  $errors = session()->getFlashdata('errors') ?? [];
  $success = session()->getFlashdata('success');
  $error = session()->getFlashdata('error');
?>

<div class="card shadow-sm mt-3">
  <div class="card-body">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
      <h2 class="fw-bold mb-2 mb-sm-0">Manage Schedules</h2>
      <div class="d-flex gap-2 align-items-center flex-wrap">
        <form class="d-flex gap-2" action="<?= base_url('admin/manage-schedules') ?>" method="get">
          <input type="text" class="form-control" name="q" placeholder="Search schedules..." value="<?= esc($q ?? '') ?>" style="min-width: 200px;"/>
          <select class="form-select" name="course_id" style="min-width: 200px;">
            <option value="">All Courses</option>
            <?php foreach ($courses as $course): ?>
              <option value="<?= $course['id'] ?>" <?= ($courseFilter == $course['id']) ? 'selected' : '' ?>>
                <?= esc($course['course_code'] . ' - ' . $course['title']) ?>
              </option>
            <?php endforeach; ?>
          </select>
          <button type="submit" class="btn btn-outline-secondary">Filter</button>
        </form>
        <button class="btn btn-dark" data-bs-toggle="modal" data-bs-target="#createModal">Add Schedule</button>
      </div>
    </div>

    <?php if ($success): ?>
      <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= esc($success) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <?php if ($error): ?>
      <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= esc($error) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>

    <?php if (!empty($schedules)): ?>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th>#</th>
              <th style="width:100px;">CN</th>
              <th>Course</th>
              <th>Instructor</th>
              <th>Day</th>
              <th>Time</th>
              <th>Duration</th>
              <th>Room</th>
              <th>Building</th>
              <th>Capacity</th>
              <th>Status</th>
              <th class="text-end">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php $i = 1; foreach ($schedules as $schedule): ?>
              <tr>
                <td><?= $i++ ?></td>
                <td>
                  <small class="badge bg-secondary"><?= esc($schedule['course_code'] ?? 'N/A') ?></small>
                </td>
                <td>
                  <div class="fw-semibold"><?= esc($schedule['course_code'] ?? '') ?></div>
                  <div class="text-muted small"><?= esc($schedule['course_title']) ?></div>
                </td>
                <td><?= esc($schedule['instructor_name'] ?? 'Unassigned') ?></td>
                <td><span class="badge bg-info text-dark"><?= esc($schedule['day_of_week']) ?></span></td>
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
                <td>
                  <?php if ($schedule['is_active']): ?>
                    <span class="badge bg-success">Active</span>
                  <?php else: ?>
                    <span class="badge bg-danger">Inactive</span>
                  <?php endif; ?>
                </td>
                <td class="text-end">
                  <button class="btn btn-sm btn-outline-info js-enrollments" data-schedule-id="<?= (int)$schedule['id'] ?>">Enrollments</button>
                  <button class="btn btn-sm btn-outline-primary js-edit" data-schedule-id="<?= (int)$schedule['id'] ?>">Edit</button>
                  <form action="<?= base_url('admin/manage-schedules/delete/' . $schedule['id']) ?>" method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this schedule?')">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php else: ?>
      <div class="alert alert-light border">No schedules found.</div>
    <?php endif; ?>
  </div>
</div>

<!-- Create/Edit Modal -->
<div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalTitle">Create New Schedule</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form id="scheduleForm" action="<?= base_url('admin/manage-schedules/store') ?>" method="post" enctype="application/x-www-form-urlencoded">
        <?= csrf_field() ?>
        <input type="hidden" id="scheduleId" name="schedule_id" value="">
        <div class="modal-body">
          <div id="formErrors"></div>
          <?php if (!empty($errors)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <strong>Please correct the following errors:</strong>
              <ul class="mb-0 mt-2">
                <?php foreach ($errors as $field => $error): ?>
                  <li><?= esc($field) ?>: <?= esc($error) ?></li>
                <?php endforeach; ?>
              </ul>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          <?php endif; ?>
          
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" for="course_id">Course <span class="text-danger">*</span></label>
              <select class="form-select <?= isset($errors['course_id']) ? 'is-invalid' : '' ?>" id="course_id" name="course_id" required>
                <option value="">Select a course</option>
                <?php foreach ($courses as $course): ?>
                  <option value="<?= $course['id'] ?>" <?= old('course_id') == $course['id'] ? 'selected' : '' ?>>
                    <?= esc($course['course_code'] . ' - ' . $course['title']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <?php if (isset($errors['course_id'])): ?>
                <div class="invalid-feedback"><?= esc($errors['course_id']) ?></div>
              <?php endif; ?>
            </div>

            <div class="col-md-6">
              <label class="form-label" for="instructor_id">Instructor <span class="text-danger">*</span></label>
              <select class="form-select <?= isset($errors['instructor_id']) ? 'is-invalid' : '' ?>" id="instructor_id" name="instructor_id" required>
                <option value="">Select an instructor</option>
                <?php foreach ($instructors as $instructor): ?>
                  <option value="<?= $instructor['id'] ?>" <?= old('instructor_id') == $instructor['id'] ? 'selected' : '' ?>>
                    <?= esc($instructor['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
              <?php if (isset($errors['instructor_id'])): ?>
                <div class="invalid-feedback"><?= esc($errors['instructor_id']) ?></div>
              <?php endif; ?>
            </div>

            <div class="col-md-6">
              <label class="form-label" for="day_range_start">Day Range <span class="text-danger">*</span></label>
              <div class="input-group">
                <select class="form-select <?= isset($errors['day_of_week']) ? 'is-invalid' : '' ?>" id="day_range_start" name="day_of_week" required>
                  <option value="">Start day</option>
                  <option value="Monday" <?= old('day_of_week') == 'Monday' ? 'selected' : '' ?>>Monday</option>
                  <option value="Tuesday" <?= old('day_of_week') == 'Tuesday' ? 'selected' : '' ?>>Tuesday</option>
                  <option value="Wednesday" <?= old('day_of_week') == 'Wednesday' ? 'selected' : '' ?>>Wednesday</option>
                  <option value="Thursday" <?= old('day_of_week') == 'Thursday' ? 'selected' : '' ?>>Thursday</option>
                  <option value="Friday" <?= old('day_of_week') == 'Friday' ? 'selected' : '' ?>>Friday</option>
                  <option value="Saturday" <?= old('day_of_week') == 'Saturday' ? 'selected' : '' ?>>Saturday</option>
                  <option value="Sunday" <?= old('day_of_week') == 'Sunday' ? 'selected' : '' ?>>Sunday</option>
                </select>
                <span class="input-group-text">to</span>
                <select class="form-select <?= isset($errors['day_of_week_end']) ? 'is-invalid' : '' ?>" id="day_range_end" name="day_of_week_end">
                  <option value="">End day</option>
                  <option value="Monday">Monday</option>
                  <option value="Tuesday">Tuesday</option>
                  <option value="Wednesday">Wednesday</option>
                  <option value="Thursday">Thursday</option>
                  <option value="Friday" selected>Friday</option>
                  <option value="Saturday">Saturday</option>
                  <option value="Sunday">Sunday</option>
                </select>
              </div>
              <small class="text-muted d-block mt-1">e.g., Monday to Friday</small>
              <?php if (isset($errors['day_of_week'])): ?>
                <div class="invalid-feedback d-block"><?= esc($errors['day_of_week']) ?></div>
              <?php endif; ?>
            </div>

            <div class="col-md-6">
              <label class="form-label" for="start_time">Start Time <span class="text-danger">*</span></label>
              <input type="time" class="form-control <?= isset($errors['start_time']) ? 'is-invalid' : '' ?>" id="start_time" name="start_time" value="<?= esc(old('start_time')) ?>" required>
              <?php if (isset($errors['start_time'])): ?>
                <div class="invalid-feedback"><?= esc($errors['start_time']) ?></div>
              <?php endif; ?>
            </div>

            <div class="col-md-6">
              <label class="form-label" for="end_time">End Time <span class="text-danger">*</span></label>
              <input type="time" class="form-control <?= isset($errors['end_time']) ? 'is-invalid' : '' ?>" id="end_time" name="end_time" value="<?= esc(old('end_time')) ?>" required>
              <?php if (isset($errors['end_time'])): ?>
                <div class="invalid-feedback"><?= esc($errors['end_time']) ?></div>
              <?php endif; ?>
            </div>

            <div class="col-md-6">
              <label class="form-label" for="room_number">Room Number</label>
              <input type="text" class="form-control" id="room_number" name="room_number" value="<?= esc(old('room_number')) ?>" placeholder="e.g., 101, A-205" maxlength="50">
            </div>

            <div class="col-md-6">
              <label class="form-label" for="building">Building</label>
              <input type="text" class="form-control" id="building" name="building" value="<?= esc(old('building')) ?>" placeholder="e.g., Science Building" maxlength="100">
            </div>

            <div class="col-md-6">
              <label class="form-label" for="capacity">Room Capacity</label>
              <input type="number" class="form-control" id="capacity" name="capacity" value="<?= esc(old('capacity')) ?>" placeholder="e.g., 50" min="1">
            </div>

            <div class="col-12">
              <label class="form-label" for="notes">Notes</label>
              <textarea class="form-control" id="notes" name="notes" rows="3" placeholder="Additional notes about this schedule..."></textarea>
            </div>

            <div class="col-12">
              <label class="form-label">Status</label>
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" checked>
                <label class="form-check-label" for="is_active">
                  Active
                </label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary" id="submitBtn">Save Schedule</button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Enrollments Modal -->
<div class="modal fade" id="enrollmentsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Enrolled Students</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="enrollmentsContent">
          <div class="text-center">
            <div class="spinner-border" role="status">
              <span class="visually-hidden">Loading...</span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
const courseInstructorMap = <?= $courseInstructorMap ?? '{}' ?>;

document.addEventListener('DOMContentLoaded', function() {
  const form = document.getElementById('scheduleForm');
  const scheduleIdInput = document.getElementById('scheduleId');
  const modalTitle = document.getElementById('modalTitle');
  const submitBtn = document.getElementById('submitBtn');
  const modal = new bootstrap.Modal(document.getElementById('createModal'));
  const courseSelect = document.getElementById('course_id');
  const instructorSelect = document.getElementById('instructor_id');

  // Auto-fill instructor when course is selected
  courseSelect.addEventListener('change', function() {
    const courseId = this.value;
    if (courseId && courseInstructorMap[courseId]) {
      instructorSelect.value = courseInstructorMap[courseId];
      // Keep enabled so the value gets submitted with the form
    } else {
      instructorSelect.value = '';
    }
  });

  // Check on form reset
  form.addEventListener('reset', function() {
    setTimeout(() => {
      instructorSelect.value = '';
      courseSelect.value = '';
    }, 0);
  });

  // Edit button handler
  document.querySelectorAll('.js-edit').forEach(btn => {
    btn.addEventListener('click', function() {
      const scheduleId = this.dataset.scheduleId;
      console.log('Loading schedule:', scheduleId);
      
      fetch('<?= base_url('admin/manage-schedules/get/') ?>' + scheduleId)
        .then(response => {
          console.log('Response status:', response.status);
          if (!response.ok) {
            throw new Error('HTTP error, status: ' + response.status);
          }
          return response.json();
        })
        .then(data => {
          console.log('Response data:', data);
          if (data.status === 'success') {
            const schedule = data.schedule;
            scheduleIdInput.value = schedule.id;
            document.getElementById('course_id').value = schedule.course_id;
            document.getElementById('instructor_id').value = schedule.instructor_id;
            
            // Handle day range (e.g., "Monday-Friday")
            const dayRange = schedule.day_of_week;
            if (dayRange.includes('-')) {
              const [startDay, endDay] = dayRange.split('-').map(d => d.trim());
              document.getElementById('day_range_start').value = startDay;
              document.getElementById('day_range_end').value = endDay;
            } else {
              document.getElementById('day_range_start').value = dayRange;
              document.getElementById('day_range_end').value = dayRange;
            }
            
            document.getElementById('start_time').value = schedule.start_time;
            document.getElementById('end_time').value = schedule.end_time;
            document.getElementById('room_number').value = schedule.room_number || '';
            document.getElementById('building').value = schedule.building || '';
            document.getElementById('capacity').value = schedule.capacity || '';
            document.getElementById('notes').value = schedule.notes || '';
            document.getElementById('is_active').checked = schedule.is_active == 1;
            
            modalTitle.textContent = 'Edit Schedule';
            submitBtn.textContent = 'Update Schedule';
            form.action = '<?= base_url('admin/manage-schedules/update/') ?>' + schedule.id;
            modal.show();
          } else {
            alert('Error: ' + (data.message || 'Unknown error'));
          }
        })
        .catch(error => {
          console.error('Fetch error:', error);
          alert('Failed to load schedule data: ' + error.message);
        });
    });
  });

  // Reset form when modal is hidden
  document.getElementById('createModal').addEventListener('hidden.bs.modal', function() {
    form.reset();
    scheduleIdInput.value = '';
    form.action = '<?= base_url('admin/manage-schedules/store') ?>';
    modalTitle.textContent = 'Create New Schedule';
    submitBtn.textContent = 'Save Schedule';
  });

  // Enrollments button handler
  document.querySelectorAll('.js-enrollments').forEach(btn => {
    btn.addEventListener('click', function() {
      const scheduleId = this.dataset.scheduleId;
      const enrollmentsModal = new bootstrap.Modal(document.getElementById('enrollmentsModal'));
      
      fetch('<?= base_url('admin/manage-schedules/enrollments/') ?>' + scheduleId)
        .then(response => {
          if (!response.ok) {
            throw new Error('HTTP error, status: ' + response.status);
          }
          return response.json();
        })
        .then(data => {
          if (data.status === 'success') {
            let html = '<table class="table table-sm">';
            html += '<thead><tr><th>Student Name</th><th>Email</th><th>Enrollment Date</th></tr></thead>';
            html += '<tbody>';
            
            if (data.enrollments.length > 0) {
              data.enrollments.forEach(enrollment => {
                html += '<tr>';
                html += '<td>' + (enrollment.name || 'N/A') + '</td>';
                html += '<td>' + (enrollment.email || 'N/A') + '</td>';
                html += '<td>' + (enrollment.enrollment_date ? new Date(enrollment.enrollment_date).toLocaleDateString() : 'N/A') + '</td>';
                html += '</tr>';
              });
            } else {
              html += '<tr><td colspan="3" class="text-center text-muted">No enrolled students</td></tr>';
            }
            
            html += '</tbody></table>';
            document.getElementById('enrollmentsContent').innerHTML = html;
            enrollmentsModal.show();
          } else {
            document.getElementById('enrollmentsContent').innerHTML = '<div class="alert alert-danger">Error: ' + (data.message || 'Unknown error') + '</div>';
          }
        })
        .catch(error => {
          console.error('Fetch error:', error);
          document.getElementById('enrollmentsContent').innerHTML = '<div class="alert alert-danger">Failed to load enrollments: ' + error.message + '</div>';
        });
    });
  });
});
</script>

<?= $this->endSection() ?>
