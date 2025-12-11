<?php

namespace App\Controllers;

use App\Models\EnrollmentModel;
use App\Models\CourseModel;

class Student extends BaseController
{
    protected $helpers = ['form', 'url'];

    private function requireStudent()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Please log in first.');
        }
        if (session()->get('user_role') !== 'student') {
            return redirect()->to('/dashboard')->with('error', 'Access Denied: Student access only.');
        }
        return null;
    }

    public function dashboard()
    {
        if ($resp = $this->requireStudent()) return $resp;
        return view('student/dashboard');
    }

    public function courses()
    {
        if ($resp = $this->requireStudent()) return $resp;
        
        $userId = session()->get('user_id');
        $db = \Config\Database::connect();
        
        // Get enrolled courses (only approved)
        try {
            $enrolled = $db->table('enrollments e')
                ->select('c.id, c.title, c.description, u.name as instructor_name, e.status, e.enrollment_date')
                ->join('courses c', 'c.id = e.course_id')
                ->join('users u', 'u.id = c.instructor_id', 'left')
                ->where('e.user_id', $userId)
                ->where('e.status', 'approved')
                ->orderBy('c.title', 'ASC')
                ->get()->getResultArray();

            // Get pending enrollments
            $pending = $db->table('enrollments e')
                ->select('c.id, c.title, c.description, u.name as instructor_name, e.status, e.enrollment_date')
                ->join('courses c', 'c.id = e.course_id')
                ->join('users u', 'u.id = c.instructor_id', 'left')
                ->where('e.user_id', $userId)
                ->where('e.status', 'pending')
                ->orderBy('e.enrollment_date', 'DESC')
                ->get()->getResultArray();

            // Get rejected enrollments
            $rejected = $db->table('enrollments e')
                ->select('c.id, c.title, c.description, u.name as instructor_name, e.status, e.rejection_reason, e.rejected_at')
                ->join('courses c', 'c.id = e.course_id')
                ->join('users u', 'u.id = c.instructor_id', 'left')
                ->where('e.user_id', $userId)
                ->where('e.status', 'rejected')
                ->orderBy('e.rejected_at', 'DESC')
                ->get()->getResultArray();
        } catch (\Exception $e) {
            // Fallback: if status column doesn't exist, treat all enrollments as approved
            $enrolled = $db->table('enrollments e')
                ->select('c.id, c.title, c.description, u.name as instructor_name, e.enrollment_date')
                ->join('courses c', 'c.id = e.course_id')
                ->join('users u', 'u.id = c.instructor_id', 'left')
                ->where('e.user_id', $userId)
                ->orderBy('c.title', 'ASC')
                ->get()->getResultArray();
            $pending = [];
            $rejected = [];
        }

        // Get available courses (not enrolled in at all)
        $allEnrolledIds = array_column($db->table('enrollments')->select('course_id')->where('user_id', $userId)->get()->getResultArray(), 'course_id') ?: [0];
        $available = $db->table('courses c')
            ->select('c.id, c.title, c.description, c.course_code, c.units, c.semester, c.instructor_id, u.name as instructor_name')
            ->join('users u', 'u.id = c.instructor_id', 'left')
            ->whereNotIn('c.id', $allEnrolledIds)
            ->orderBy('c.title', 'ASC')
            ->get()->getResultArray();

        return view('student/courses', [ 
            'courses' => $enrolled ?? [],
            'pendingCourses' => $pending ?? [],
            'rejectedCourses' => $rejected ?? [],
            'availableCourses' => $available
        ]);
    }

    public function approveEnrollment()
    {
        if ($resp = $this->requireStudent()) return $resp;

        if ($this->request->getMethod() !== 'POST') {
            return redirect()->to('/student/courses')->with('error', 'Invalid request method.');
        }

        $courseId = (int)($this->request->getPost('course_id') ?? 0);
        $userId = session()->get('user_id');

        if (!$courseId) {
            return redirect()->to('/student/courses')->with('error', 'Course ID is required.');
        }

        $db = \Config\Database::connect();
        $enrollmentModel = new EnrollmentModel();

        // Get the enrollment record
        $enrollment = $enrollmentModel->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('status', 'pending')
            ->first();

        if (!$enrollment) {
            return redirect()->to('/student/courses')->with('error', 'Enrollment not found or already processed.');
        }

        // Update enrollment to approved
        $enrollmentModel->update($enrollment['id'], [
            'status' => 'approved',
            'approved_at' => date('Y-m-d H:i:s')
        ]);

        // Create notification for teacher/admin
        try {
            $course = $db->table('courses')->find($courseId);
            $notifModel = new \App\Models\NotificationModel();
            $notifModel->createNotification(
                $course['instructor_id'],
                session()->get('user_name') . ' has approved enrollment in ' . $course['title'] . '.'
            );
        } catch (\Throwable $e) {
            // swallow notification errors
        }

        return redirect()->to('/student/courses')->with('success', 'Enrollment approved successfully!');
    }

    public function rejectEnrollment()
    {
        if ($resp = $this->requireStudent()) return $resp;

        if ($this->request->getMethod() !== 'POST') {
            return redirect()->to('/student/courses')->with('error', 'Invalid request method.');
        }

        $courseId = (int)($this->request->getPost('course_id') ?? 0);
        $reason = trim($this->request->getPost('reason') ?? '');
        $userId = session()->get('user_id');

        if (!$courseId) {
            return redirect()->to('/student/courses')->with('error', 'Course ID is required.');
        }

        $db = \Config\Database::connect();
        $enrollmentModel = new EnrollmentModel();

        // Get the enrollment record
        $enrollment = $enrollmentModel->where('user_id', $userId)
            ->where('course_id', $courseId)
            ->where('status', 'pending')
            ->first();

        if (!$enrollment) {
            return redirect()->to('/student/courses')->with('error', 'Enrollment not found or already processed.');
        }

        // Update enrollment to rejected
        $enrollmentModel->update($enrollment['id'], [
            'status' => 'rejected',
            'rejected_at' => date('Y-m-d H:i:s'),
            'rejection_reason' => $reason ?: null
        ]);

        // Create notification for teacher/admin
        try {
            $course = $db->table('courses')->find($courseId);
            $notifModel = new \App\Models\NotificationModel();
            $notifModel->createNotification(
                $course['instructor_id'],
                session()->get('user_name') . ' has rejected enrollment in ' . $course['title'] . '.'
            );
        } catch (\Throwable $e) {
            // swallow notification errors
        }

        return redirect()->to('/student/courses')->with('success', 'Enrollment rejected successfully!');
    }

    /**
     * View student's schedule
     */
    public function mySchedule()
    {
        if ($resp = $this->requireStudent()) return $resp;

        $userId = session()->get('user_id');
        $scheduleModel = new \App\Models\ScheduleModel();

        // Get schedules for enrolled courses
        $schedules = $scheduleModel->getStudentSchedules($userId);

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

        return view('student/my_schedule', [
            'schedules' => $schedules,
            'schedulesByDay' => $schedulesByDay
        ]);
    }
}
