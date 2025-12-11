<?php

namespace App\Controllers;

use App\Models\ScheduleModel;
use App\Models\CourseModel;
use App\Models\UserModel;

class ManageSchedule extends BaseController
{
    protected $helpers = ['form', 'url'];
    protected $scheduleModel;
    protected $courseModel;
    protected $userModel;

    public function __construct()
    {
        $this->scheduleModel = new ScheduleModel();
        $this->courseModel = new CourseModel();
        $this->userModel = new UserModel();
    }

    private function requireAdmin()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Please log in first.');
        }
        if (session()->get('user_role') !== 'admin') {
            return redirect()->to('/announcements')->with('error', 'Access Denied: Insufficient Permissions.');
        }
        return null;
    }

    /**
     * Display all schedules with management interface
     */
    public function index()
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $search = trim($this->request->getGet('q') ?? '');
        $courseFilter = (int)($this->request->getGet('course_id') ?? 0);

        $schedules = $this->scheduleModel->getAllSchedules();

        // Filter by search term
        if (!empty($search)) {
            $schedules = array_filter($schedules, function($s) use ($search) {
                $searchLower = strtolower($search);
                return stripos($s['course_title'], $search) !== false ||
                       stripos($s['course_code'], $search) !== false ||
                       stripos($s['instructor_name'], $search) !== false ||
                       stripos($s['room_number'], $search) !== false;
            });
        }

        // Filter by course
        if ($courseFilter > 0) {
            $schedules = array_filter($schedules, function($s) use ($courseFilter) {
                return $s['course_id'] == $courseFilter;
            });
        }

        $courses = $this->courseModel->select('id, title, course_code, instructor_id')
            ->orderBy('title', 'ASC')
            ->findAll();

        $instructors = $this->userModel->where('role', 'teacher')
            ->orderBy('name', 'ASC')
            ->findAll();

        // Create a mapping of course_id => instructor_id for JavaScript
        $courseInstructorMap = [];
        foreach ($courses as $course) {
            $courseInstructorMap[$course['id']] = $course['instructor_id'];
        }

        $data = [
            'schedules' => $schedules,
            'courses' => $courses,
            'instructors' => $instructors,
            'courseInstructorMap' => json_encode($courseInstructorMap),
            'q' => $search,
            'courseFilter' => $courseFilter,
        ];

        return view('admin/ManageSchedule', $data);
    }

    /**
     * Store a new schedule
     */
    public function store()
    {
        if ($resp = $this->requireAdmin()) return $resp;

        // Log all POST data for debugging
        log_message('info', 'Schedule store() called');
        log_message('info', 'POST data: ' . json_encode($this->request->getPost()));

        $rules = [
            'course_id' => 'required|integer|greater_than[0]',
            'instructor_id' => 'required|integer|greater_than[0]',
            'day_of_week' => 'required|in_list[Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday]',
            'day_of_week_end' => 'permit_empty|in_list[Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday]',
            'start_time' => 'required|regex_match[/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/]',
            'end_time' => 'required|regex_match[/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/]',
            'room_number' => 'permit_empty|string|max_length[50]',
            'building' => 'permit_empty|string|max_length[100]',
            'capacity' => 'permit_empty|integer|greater_than[0]',
            'notes' => 'permit_empty|string',
        ];

        if (!$this->validate($rules)) {
            log_message('error', 'Schedule validation failed: ' . json_encode($this->validator->getErrors()));
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $startTime = $this->request->getPost('start_time');
        $endTime = $this->request->getPost('end_time');
        $instructorId = (int)$this->request->getPost('instructor_id');
        $dayOfWeek = $this->request->getPost('day_of_week');
        $dayOfWeekEnd = $this->request->getPost('day_of_week_end') ?: $dayOfWeek;

        // Format day range for storage (e.g., "Monday-Friday")
        $dayRange = $dayOfWeek;
        if ($dayOfWeekEnd && $dayOfWeekEnd !== $dayOfWeek) {
            $dayRange = $dayOfWeek . '-' . $dayOfWeekEnd;
        }

        // Check for time conflicts
        if (!$this->scheduleModel->isTimeSlotAvailable($instructorId, $dayOfWeek, $startTime, $endTime)) {
            return redirect()->back()->withInput()->with('error', 'Instructor has a schedule conflict at this time.');
        }

        // Calculate duration
        $startDateTime = new \DateTime('2000-01-01 ' . $startTime);
        $endDateTime = new \DateTime('2000-01-01 ' . $endTime);
        $durationMinutes = $endDateTime->diff($startDateTime)->i + ($endDateTime->diff($startDateTime)->h * 60);

        $data = [
            'course_id' => (int)$this->request->getPost('course_id'),
            'instructor_id' => $instructorId,
            'day_of_week' => $dayRange,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'room_number' => $this->request->getPost('room_number'),
            'building' => $this->request->getPost('building'),
            'duration_minutes' => $durationMinutes,
            'capacity' => $this->request->getPost('capacity'),
            'notes' => $this->request->getPost('notes'),
            'is_active' => true,
        ];

        try {
            if ($this->scheduleModel->insert($data)) {
                return redirect()->to('/admin/manage-schedules')->with('success', 'Schedule created successfully.');
            } else {
                $error = $this->scheduleModel->errors();
                log_message('error', 'Schedule insert failed: ' . json_encode($error));
                return redirect()->back()->withInput()->with('error', 'Failed to create schedule: ' . (is_array($error) ? implode(', ', $error) : $error));
            }
        } catch (\Exception $e) {
            log_message('error', 'Schedule insert exception: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Error creating schedule: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing schedule
     */
    public function update($id)
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $schedule = $this->scheduleModel->find($id);
        if (!$schedule) {
            return redirect()->to('/admin/manage-schedules')->with('error', 'Schedule not found.');
        }

        $rules = [
            'course_id' => 'required|integer|greater_than[0]',
            'instructor_id' => 'required|integer|greater_than[0]',
            'day_of_week' => 'required|in_list[Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday]',
            'day_of_week_end' => 'permit_empty|in_list[Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday]',
            'start_time' => 'required|regex_match[/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/]',
            'end_time' => 'required|regex_match[/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/]',
            'room_number' => 'permit_empty|string|max_length[50]',
            'building' => 'permit_empty|string|max_length[100]',
            'capacity' => 'permit_empty|integer|greater_than[0]',
            'notes' => 'permit_empty|string',
        ];

        if (!$this->validate($rules)) {
            log_message('error', 'Schedule update validation failed: ' . json_encode($this->validator->getErrors()));
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $startTime = $this->request->getPost('start_time');
        $endTime = $this->request->getPost('end_time');
        $instructorId = (int)$this->request->getPost('instructor_id');
        $dayOfWeek = $this->request->getPost('day_of_week');
        $dayOfWeekEnd = $this->request->getPost('day_of_week_end') ?: $dayOfWeek;

        // Format day range for storage (e.g., "Monday-Friday")
        $dayRange = $dayOfWeek;
        if ($dayOfWeekEnd && $dayOfWeekEnd !== $dayOfWeek) {
            $dayRange = $dayOfWeek . '-' . $dayOfWeekEnd;
        }

        // Check for time conflicts (excluding current schedule)
        if (!$this->scheduleModel->isTimeSlotAvailable($instructorId, $dayOfWeek, $startTime, $endTime, $id)) {
            return redirect()->back()->withInput()->with('error', 'Instructor has a schedule conflict at this time.');
        }

        // Calculate duration
        $startDateTime = new \DateTime('2000-01-01 ' . $startTime);
        $endDateTime = new \DateTime('2000-01-01 ' . $endTime);
        $durationMinutes = $endDateTime->diff($startDateTime)->i + ($endDateTime->diff($startDateTime)->h * 60);

        $data = [
            'course_id' => (int)$this->request->getPost('course_id'),
            'instructor_id' => $instructorId,
            'day_of_week' => $dayRange,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'room_number' => $this->request->getPost('room_number'),
            'building' => $this->request->getPost('building'),
            'duration_minutes' => $durationMinutes,
            'capacity' => $this->request->getPost('capacity'),
            'notes' => $this->request->getPost('notes'),
        ];

        try {
            if ($this->scheduleModel->update($id, $data)) {
                log_message('info', 'Schedule ' . $id . ' updated successfully');
                return redirect()->to('/admin/manage-schedules')->with('success', 'Schedule updated successfully.');
            } else {
                $error = $this->scheduleModel->errors();
                log_message('error', 'Schedule update failed: ' . json_encode($error));
                return redirect()->back()->withInput()->with('error', 'Failed to update schedule: ' . (is_array($error) ? implode(', ', $error) : $error));
            }
        } catch (\Exception $e) {
            log_message('error', 'Schedule update exception: ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Error updating schedule: ' . $e->getMessage());
        }
    }

    /**
     * Delete a schedule
     */
    public function delete($id)
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $schedule = $this->scheduleModel->find($id);
        if (!$schedule) {
            log_message('warning', 'Delete attempt on non-existent schedule: ' . $id);
            return redirect()->to('/admin/manage-schedules')->with('error', 'Schedule not found.');
        }

        try {
            if ($this->scheduleModel->delete($id)) {
                log_message('info', 'Schedule ' . $id . ' deleted successfully');
                return redirect()->to('/admin/manage-schedules')->with('success', 'Schedule deleted successfully.');
            } else {
                $error = $this->scheduleModel->errors();
                log_message('error', 'Schedule delete failed: ' . json_encode($error));
                return redirect()->to('/admin/manage-schedules')->with('error', 'Failed to delete schedule.');
            }
        } catch (\Exception $e) {
            log_message('error', 'Schedule delete exception: ' . $e->getMessage());
            return redirect()->to('/admin/manage-schedules')->with('error', 'Error deleting schedule: ' . $e->getMessage());
        }
    }

    /**
     * Toggle schedule active status
     */
    public function toggleActive($id)
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $schedule = $this->scheduleModel->find($id);
        if (!$schedule) {
            log_message('warning', 'Toggle attempt on non-existent schedule: ' . $id);
            return redirect()->to('/admin/manage-schedules')->with('error', 'Schedule not found.');
        }

        try {
            $newStatus = !$schedule['is_active'];
            if ($this->scheduleModel->update($id, ['is_active' => $newStatus])) {
                $message = $newStatus ? 'Schedule activated.' : 'Schedule deactivated.';
                log_message('info', 'Schedule ' . $id . ' status toggled to ' . ($newStatus ? 'active' : 'inactive'));
                return redirect()->to('/admin/manage-schedules')->with('success', $message);
            } else {
                $error = $this->scheduleModel->errors();
                log_message('error', 'Schedule toggle failed: ' . json_encode($error));
                return redirect()->to('/admin/manage-schedules')->with('error', 'Failed to update schedule status.');
            }
        } catch (\Exception $e) {
            log_message('error', 'Schedule toggle exception: ' . $e->getMessage());
            return redirect()->to('/admin/manage-schedules')->with('error', 'Error updating schedule status: ' . $e->getMessage());
        }
    }

    /**
     * Get schedule data for AJAX edit modal
     */
    public function getSchedule($id)
    {
        if (!session()->get('logged_in') || session()->get('user_role') !== 'admin') {
            return $this->response->setStatusCode(403)->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized'
            ]);
        }

        $schedule = $this->scheduleModel->find($id);
        if (!$schedule) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Schedule not found.'
            ]);
        }

        return $this->response->setStatusCode(200)->setJSON([
            'status' => 'success',
            'schedule' => $schedule
        ]);
    }

    /**
     * Get enrollments for a specific schedule
     */
    public function getScheduleEnrollments($scheduleId)
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $schedule = $this->scheduleModel->find($scheduleId);
        if (!$schedule) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Schedule not found.'
            ]);
        }

        $enrollmentModel = new \App\Models\EnrollmentModel();
        $enrollments = $enrollmentModel->select('enrollments.*, users.name, users.email')
            ->join('users', 'users.id = enrollments.user_id', 'left')
            ->where('enrollments.course_id', $schedule['course_id'])
            ->where('enrollments.status', 'approved')
            ->orderBy('users.name', 'ASC')
            ->findAll();

        return $this->response->setJSON([
            'status' => 'success',
            'enrollments' => $enrollments,
            'schedule' => $schedule
        ]);
    }
}

