<?php

namespace App\Controllers;

use App\Models\CourseModel;
use App\Models\EnrollmentModel;
use App\Models\UserModel;
use App\Models\ScheduleModel;

class Teacher extends BaseController
{
    protected $helpers = ['form', 'url'];

    private function requireTeacherOrAdmin()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Please log in first.');
        }
        $role = session()->get('user_role');
        if (!in_array($role, ['teacher', 'admin'])) {
            return redirect()->to('/announcements')->with('error', 'Access Denied: Insufficient Permissions.');
        }
        return null;
    }

    public function dashboard()
    {
        if ($resp = $this->requireTeacherOrAdmin()) return $resp;
        return view('teacher/dashboard');
    }

    /**
     * My Courses - Show courses assigned to teacher (or all for admin)
     */
    public function courses()
    {
        if ($resp = $this->requireTeacherOrAdmin()) return $resp;

        $userId = session()->get('user_id');
        $role = session()->get('user_role');
        $courseModel = new CourseModel();
        
        $builder = $courseModel->select('courses.*, 
            users.name as instructor_name,
            (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = courses.id AND e.status = "approved") as student_count,
            (SELECT COUNT(*) FROM materials m WHERE m.course_id = courses.id) as material_count')
            ->join('users', 'users.id = courses.instructor_id', 'left');

        // Admin can see all courses, teachers see only their courses
        if ($role !== 'admin') {
            $builder->where('courses.instructor_id', $userId);
        }

        $courses = $builder->orderBy('courses.created_at', 'DESC')->findAll();

        return view('teacher/courses', ['courses' => $courses]);
    }

    /**
     * Manage Students - View students enrolled in teacher's courses (or all for admin)
     */
    public function students()
    {
        if ($resp = $this->requireTeacherOrAdmin()) return $resp;

        $userId = session()->get('user_id');
        $role = session()->get('user_role');
        $courseModel = new CourseModel();
        $enrollmentModel = new EnrollmentModel();

        // Get courses (all for admin, teacher's courses for teacher)
        if ($role === 'admin') {
            $courses = $courseModel->select('courses.*, users.name as instructor_name')
                ->join('users', 'users.id = courses.instructor_id', 'left')
                ->orderBy('courses.title', 'ASC')
                ->findAll();
        } else {
            $courses = $courseModel->select('courses.*, users.name as instructor_name')
                ->join('users', 'users.id = courses.instructor_id', 'left')
                ->where('courses.instructor_id', $userId)
                ->orderBy('courses.title', 'ASC')
                ->findAll();
        }

        $courseId = (int)($this->request->getGet('course_id') ?? 0);
        $search = trim($this->request->getGet('search') ?? '');

        $students = [];
        $selectedCourse = null;
        
        if ($courseId > 0) {
            // Verify teacher owns the course (or admin)
            if ($role === 'admin') {
                $selectedCourse = $courseModel->select('courses.*, users.name as instructor_name')
                    ->join('users', 'users.id = courses.instructor_id', 'left')
                    ->find($courseId);
            } else {
                $selectedCourse = $courseModel->select('courses.*, users.name as instructor_name')
                    ->join('users', 'users.id = courses.instructor_id', 'left')
                    ->where('courses.id', $courseId)
                    ->where('courses.instructor_id', $userId)
                    ->first();
            }

            if ($selectedCourse) {
                // Get enrolled students (only approved)
                $builder = $enrollmentModel->select('enrollments.id as enrollment_id, enrollments.*, users.id as user_id, users.name, users.email, users.role')
                    ->join('users', 'users.id = enrollments.user_id', 'left')
                    ->where('enrollments.course_id', $courseId)
                    ->where('enrollments.status', 'approved')
                    ->where('users.role', 'student');

                if ($search) {
                    $builder->groupStart()
                        ->like('users.name', $search)
                        ->orLike('users.email', $search)
                        ->groupEnd();
                }

                $students = $builder->orderBy('users.name', 'ASC')->findAll();
            }
        }

        // Get pending enrollments for selected course
        $pendingEnrollments = [];
        if ($courseId > 0) {
            $pendingEnrollments = $enrollmentModel->getPendingEnrollments($courseId);
        }

        return view('teacher/students', [
            'courses' => $courses,
            'students' => $students,
            'selectedCourseId' => $courseId,
            'selectedCourse' => $selectedCourse,
            'search' => $search,
            'pendingEnrollments' => $pendingEnrollments
        ]);
    }

    /**
     * Approve Enrollment
     */
    public function approveEnrollment($enrollmentId)
    {
        if ($resp = $this->requireTeacherOrAdmin()) return $resp;

        $enrollmentModel = new EnrollmentModel();
        $enrollment = $enrollmentModel->find($enrollmentId);

        if (!$enrollment) {
            return redirect()->to('/teacher/students')->with('error', 'Enrollment not found.');
        }

        // Verify teacher owns the course (or admin)
        $role = session()->get('user_role');
        $courseModel = new CourseModel();
        
        if ($role === 'admin') {
            $course = $courseModel->find($enrollment['course_id']);
        } else {
            $course = $courseModel->where('id', $enrollment['course_id'])
                ->where('instructor_id', session()->get('user_id'))
                ->first();
        }

        if (!$course) {
            return redirect()->to('/teacher/students')->with('error', 'Access denied.');
        }

        // Update enrollment status
        $enrollmentModel->update($enrollmentId, [
            'status' => 'approved',
            'approved_at' => date('Y-m-d H:i:s'),
            'rejected_at' => null,
            'rejection_reason' => null
        ]);

        // Create notification for student
        try {
            $userModel = new UserModel();
            $student = $userModel->find($enrollment['user_id']);
            $course = $courseModel->find($enrollment['course_id']);
            
            if ($student && $course) {
                $notifModel = new \App\Models\NotificationModel();
                $notifModel->createNotification(
                    (int)$enrollment['user_id'],
                    'Your enrollment in ' . $course['title'] . ' has been approved!'
                );
            }
        } catch (\Throwable $e) {
            // swallow notification errors
        }

        return redirect()->to('/teacher/students?course_id=' . $enrollment['course_id'])
            ->with('success', 'Enrollment approved successfully.');
    }

    /**
     * Reject Enrollment
     */
    public function rejectEnrollment($enrollmentId)
    {
        if ($resp = $this->requireTeacherOrAdmin()) return $resp;

        if ($this->request->getMethod() === 'GET') {
            return redirect()->to('/teacher/students');
        }

        $enrollmentModel = new EnrollmentModel();
        $enrollment = $enrollmentModel->find($enrollmentId);

        if (!$enrollment) {
            return redirect()->to('/teacher/students')->with('error', 'Enrollment not found.');
        }

        // Verify teacher owns the course (or admin)
        $role = session()->get('user_role');
        $courseModel = new CourseModel();
        
        if ($role === 'admin') {
            $course = $courseModel->find($enrollment['course_id']);
        } else {
            $course = $courseModel->where('id', $enrollment['course_id'])
                ->where('instructor_id', session()->get('user_id'))
                ->first();
        }

        if (!$course) {
            return redirect()->to('/teacher/students')->with('error', 'Access denied.');
        }

        $rejectionReason = $this->request->getPost('rejection_reason') ?? '';

        // Update enrollment status
        $enrollmentModel->update($enrollmentId, [
            'status' => 'rejected',
            'rejected_at' => date('Y-m-d H:i:s'),
            'rejection_reason' => $rejectionReason,
            'approved_at' => null
        ]);

        // Create notification for student
        try {
            $course = $courseModel->find($enrollment['course_id']);
            
            if ($course) {
                $notifModel = new \App\Models\NotificationModel();
                $message = 'Your enrollment request in ' . $course['title'] . ' has been rejected.';
                if ($rejectionReason) {
                    $message .= ' Reason: ' . $rejectionReason;
                }
                $notifModel->createNotification((int)$enrollment['user_id'], $message);
            }
        } catch (\Throwable $e) {
            // swallow notification errors
        }

        return redirect()->to('/teacher/students?course_id=' . $enrollment['course_id'])
            ->with('success', 'Enrollment rejected.');
    }

    /**
     * Search for students to enroll
     */
    /**
     * Test endpoint to verify AJAX is working
     */
    public function testSearch()
    {
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'AJAX endpoint is working!',
            'timestamp' => date('Y-m-d H:i:s'),
            'user_id' => session()->get('user_id'),
            'user_role' => session()->get('user_role')
        ]);
    }

    public function searchStudents()
    {
        if ($resp = $this->requireTeacherOrAdmin()) return $resp;

        $search = trim($this->request->getGet('q') ?? '');
        $courseId = (int)($this->request->getGet('course_id') ?? 0);

        if (!$courseId) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Course ID is required.'
            ]);
        }

        // Verify teacher owns the course (or admin)
        $role = session()->get('user_role');
        $courseModel = new CourseModel();
        
        if ($role === 'admin') {
            $course = $courseModel->find($courseId);
        } else {
            $course = $courseModel->where('id', $courseId)
                ->where('instructor_id', session()->get('user_id'))
                ->first();
        }

        if (!$course) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Course not found or access denied.'
            ]);
        }

        $userModel = new UserModel();
        $enrollmentModel = new EnrollmentModel();

        // Get student IDs with pending or approved enrollments (exclude only these, allow rejected to be re-enrolled)
        $activeEnrolledStudentIds = $enrollmentModel->select('user_id')
            ->where('course_id', $courseId)
            ->whereIn('status', ['pending', 'approved'])
            ->findAll();
        $activeEnrolledIds = array_column($activeEnrolledStudentIds, 'user_id') ?: [0];

        // Search for students (not already pending or approved)
        $builder = $userModel->select('id, name, email')
            ->where('role', 'student')
            ->whereNotIn('id', $activeEnrolledIds);

        if ($search) {
            $builder->groupStart()
                ->like('name', $search)
                ->orLike('email', $search)
                ->groupEnd();
        }

        $students = $builder->orderBy('name', 'ASC')->limit(50)->findAll();

        // Debug logging
        log_message('debug', 'searchStudents - Course ID: ' . $courseId . ', Active Enrolled IDs: ' . json_encode($activeEnrolledIds) . ', Found students: ' . count($students));

        return $this->response->setJSON([
            'status' => 'success',
            'students' => $students
        ]);
    }

    /**
     * Enroll a student in a course (auto-approved)
     */
    public function enrollStudent()
    {
        if ($resp = $this->requireTeacherOrAdmin()) return $resp;

        if ($this->request->getMethod() !== 'POST') {
            return redirect()->to('/teacher/students');
        }

        $studentId = (int)($this->request->getPost('student_id') ?? 0);
        $courseId = (int)($this->request->getPost('course_id') ?? 0);

        if (!$studentId || !$courseId) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Student ID and Course ID are required.'
                ]);
            }
            return redirect()->to('/teacher/students?course_id=' . $courseId)
                ->with('error', 'Student ID and Course ID are required.');
        }

        // Verify teacher owns the course (or admin)
        $role = session()->get('user_role');
        $courseModel = new CourseModel();
        
        if ($role === 'admin') {
            $course = $courseModel->find($courseId);
        } else {
            $course = $courseModel->where('id', $courseId)
                ->where('instructor_id', session()->get('user_id'))
                ->first();
        }

        if (!$course) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Course not found or access denied.'
                ]);
            }
            return redirect()->to('/teacher/students')
                ->with('error', 'Course not found or access denied.');
        }

        // Verify student exists
        $userModel = new UserModel();
        $student = $userModel->find($studentId);
        
        if (!$student || $student['role'] !== 'student') {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Invalid student.'
                ]);
            }
            return redirect()->to('/teacher/students?course_id=' . $courseId)
                ->with('error', 'Invalid student.');
        }

        // Check if already enrolled
        $enrollmentModel = new EnrollmentModel();
        $existing = $enrollmentModel->where('user_id', $studentId)
            ->where('course_id', $courseId)
            ->first();

        if ($existing) {
            // If rejected, allow re-enrollment as pending
            if ($existing['status'] === 'rejected') {
                $enrollmentModel->update($existing['id'], [
                    'status' => 'pending',
                    'enrollment_date' => date('Y-m-d H:i:s'),
                    'rejected_at' => null,
                    'rejection_reason' => null
                ]);
            } else {
                // Already pending or approved
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON([
                        'status' => 'error',
                        'message' => 'Student is already enrolled in this course.'
                    ]);
                }
                return redirect()->to('/teacher/students?course_id=' . $courseId)
                    ->with('error', 'Student is already enrolled in this course.');
            }
        } else {
            // Create new enrollment (requires student approval)
            $enrollmentModel->insert([
                'user_id' => $studentId,
                'course_id' => $courseId,
                'enrollment_date' => date('Y-m-d H:i:s'),
                'status' => 'pending'
            ]);
        }

        // Create notification for student to approve enrollment
        try {
            $notifModel = new \App\Models\NotificationModel();
            $notifModel->createNotification(
                $studentId,
                'You have been enrolled in ' . $course['title'] . ' by ' . (session()->get('user_name') ?? 'the instructor') . '. Please approve or reject this enrollment.'
            );
        } catch (\Throwable $e) {
            // swallow notification errors
        }

        // Handle AJAX requests
        if ($this->request->isAJAX()) {
            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Student enrolled successfully!'
            ]);
        }

        return redirect()->to('/teacher/students?course_id=' . $courseId)
            ->with('success', 'Student enrolled successfully!');
    }

    /**
     * View teacher's schedule
     */
    public function mySchedule()
    {
        if ($resp = $this->requireTeacherOrAdmin()) return $resp;

        $userId = session()->get('user_id');
        $role = session()->get('user_role');
        $scheduleModel = new ScheduleModel();

        // Get schedules for this teacher (or all for admin)
        if ($role === 'admin') {
            $schedules = $scheduleModel->getAllSchedules();
        } else {
            $schedules = $scheduleModel->getInstructorSchedules($userId);
        }

        // Group schedules by day of week (handle day ranges like "Monday-Friday")
        $schedulesByDay = [];
        $dayOrder = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        
        foreach ($dayOrder as $day) {
            $schedulesByDay[$day] = [];
        }

        foreach ($schedules as $schedule) {
            $dayRange = $schedule['day_of_week'];
            
            // Handle day ranges (e.g., "Monday-Friday")
            if (strpos($dayRange, '-') !== false) {
                list($startDay, $endDay) = explode('-', $dayRange);
                $startDay = trim($startDay);
                $endDay = trim($endDay);
                
                // Add schedule to all days in the range
                $inRange = false;
                foreach ($dayOrder as $day) {
                    if ($day === $startDay) {
                        $inRange = true;
                    }
                    if ($inRange) {
                        $schedulesByDay[$day][] = $schedule;
                    }
                    if ($day === $endDay) {
                        $inRange = false;
                    }
                }
            } else {
                // Single day
                if (isset($schedulesByDay[$dayRange])) {
                    $schedulesByDay[$dayRange][] = $schedule;
                }
            }
        }

        // Sort each day's schedules by start time
        foreach ($schedulesByDay as &$daySchedules) {
            usort($daySchedules, function($a, $b) {
                return strcmp($a['start_time'], $b['start_time']);
            });
        }

        return view('teacher/my_schedule', [
            'schedules' => $schedules,
            'schedulesByDay' => $schedulesByDay
        ]);
    }

    /**
     * Remove student from course
     */
    public function removeStudent()
    {
        if ($resp = $this->requireTeacherOrAdmin()) return $resp;

        if ($this->request->getMethod() !== 'POST') {
            session()->setFlashdata('error', 'Invalid request method.');
            return redirect()->back();
        }

        $enrollmentId = (int)($this->request->getPost('enrollment_id') ?? 0);
        $courseId = (int)($this->request->getPost('course_id') ?? 0);

        if (!$enrollmentId || !$courseId) {
            session()->setFlashdata('error', 'Enrollment ID and Course ID are required.');
            return redirect()->back();
        }

        $db = \Config\Database::connect();

        // Get enrollment record using query builder
        $enrollment = $db->table('enrollments')
            ->where('id', $enrollmentId)
            ->where('course_id', $courseId)
            ->get()
            ->getRowArray();

        if (!$enrollment) {
            session()->setFlashdata('error', 'Enrollment not found.');
            return redirect()->back();
        }

        // Verify teacher owns this course (unless admin)
        if (session()->get('user_role') === 'teacher') {
            $course = $db->table('courses')
                ->where('id', $courseId)
                ->where('instructor_id', session()->get('user_id'))
                ->get()
                ->getRowArray();
            
            if (!$course) {
                session()->setFlashdata('error', 'Unauthorized.');
                return redirect()->back();
            }
        }

        // Delete the enrollment from database
        try {
            $deleted = $db->table('enrollments')
                ->where('id', $enrollmentId)
                ->delete();
            
            if ($deleted) {
                session()->setFlashdata('success', 'Student removed from course successfully.');
            } else {
                session()->setFlashdata('error', 'Failed to remove student.');
            }
        } catch (\Exception $e) {
            session()->setFlashdata('error', 'Error removing student: ' . $e->getMessage());
        }

        // Always redirect back to students page
        return redirect()->to('teacher/students?course_id=' . $courseId);
    }

    /**
     * Get student profile data
     */
    public function studentProfile($studentId = null)
    {
        if ($resp = $this->requireTeacherOrAdmin()) return $resp;

        if (!$studentId) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Student ID is required.'
            ])->setStatusCode(400);
        }

        $userModel = new UserModel();
        $student = $userModel->find($studentId);

        if (!$student) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Student not found.'
            ])->setStatusCode(404);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'student' => $student
        ]);
    }
}
