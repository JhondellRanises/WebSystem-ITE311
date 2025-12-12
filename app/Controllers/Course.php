<?php

namespace App\Controllers;

use App\Models\EnrollmentModel;
use CodeIgniter\Controller;

class Course extends BaseController
{
    public function index()
    {
        $db = \Config\Database::connect();
        $courses = $db->table('courses')
                      ->select('courses.id, courses.title, courses.description, courses.instructor_id, courses.created_at, users.name as instructor_name')
                      ->join('users', 'users.id = courses.instructor_id', 'left')
                      ->orderBy('courses.title', 'ASC')
                      ->get()
                      ->getResultArray();
        
        return view('courses/index', ['courses' => $courses]);
    }

    public function search()
{
    $searchTerm = $this->request->getGet('term') ?? '';
    
    $db = \Config\Database::connect();
    $builder = $db->table('courses c')
        ->select('c.id, c.title, c.description, c.course_code, c.units, c.semester, c.instructor_id, u.name as instructor_name')
        ->join('users u', 'u.id = c.instructor_id', 'left');

    if (!empty($searchTerm)) {
        $builder->groupStart()
            ->like('c.title', $searchTerm)
            ->orLike('c.description', $searchTerm)
            ->orLike('u.name', $searchTerm)
            ->groupEnd();
    }

    $courses = $builder->orderBy('c.title', 'ASC')
        ->get()
        ->getResultArray();

    if ($this->request->isAJAX()) {
        return $this->response->setJSON($courses);
    }

    return view('student/courses', ['courses' => $courses]);
}

    public function details($course_id = null)
    {
        if (!$course_id) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Course ID is required.'
            ])->setStatusCode(400);
        }

        $db = \Config\Database::connect();
        $course = $db->table('courses c')
            ->select('c.id, c.title, c.description, c.course_code, c.units, c.semester, c.term, c.academic_year, c.department, c.program, c.schedule, c.instructor_id, u.name as instructor_name')
            ->join('users u', 'u.id = c.instructor_id', 'left')
            ->where('c.id', $course_id)
            ->get()
            ->getRowArray();

        if (!$course) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Course not found.'
            ])->setStatusCode(404);
        }

        return $this->response->setJSON($course);
    }

    public function enroll()
    {
        // ✅ Check session
        if (!session()->get('logged_in')) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'You must be logged in to enroll.'
            ]);
        }

        // ✅ Only teachers and admins can enroll students
        $userRole = session()->get('user_role');
        if ($userRole === 'student') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Students cannot self-enroll. Please ask your teacher to enroll you.'
            ]);
        }

        $user_id = session()->get('user_id');
        $course_id = $this->request->getPost('course_id');
        $student_id = $this->request->getPost('student_id') ?? $user_id; // Allow enrolling other students

        // ✅ Validate course_id
        if (empty($course_id)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'No course selected.'
            ]);
        }

        $enrollmentModel = new EnrollmentModel();

        // ✅ Check if already enrolled (pending or approved)
        $existingEnrollment = $enrollmentModel->where('user_id', $student_id)
            ->where('course_id', $course_id)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existingEnrollment) {
            $status = $existingEnrollment['status'] === 'pending' ? 'pending approval' : 'already enrolled';
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Student is ' . $status . ' in this course.'
            ]);
        }

        // ✅ Check if previously rejected - allow re-enrollment
        $rejectedEnrollment = $enrollmentModel->where('user_id', $student_id)
            ->where('course_id', $course_id)
            ->where('status', 'rejected')
            ->first();

        if ($rejectedEnrollment) {
            // Update rejected enrollment back to pending
            $enrollmentModel->update($rejectedEnrollment['id'], [
                'status' => 'pending',
                'enrollment_date' => date('Y-m-d H:i:s'),
                'rejected_at' => null,
                'rejection_reason' => null
            ]);
        } else {
            // ✅ Insert new enrollment with pending status (requires student approval)
            $data = [
                'user_id' => $student_id,
                'course_id' => $course_id,
                'enrollment_date' => date('Y-m-d H:i:s'),
                'status' => 'pending'
            ];
            $enrollmentModel->insert($data);
        }

        // Create notification for the STUDENT to approve enrollment
        try {
            $db = \Config\Database::connect();
            $course = $db->table('courses')
                ->select('courses.title, users.name as teacher_name')
                ->join('users', 'users.id = courses.instructor_id', 'left')
                ->where('courses.id', $course_id)
                ->get()
                ->getRowArray();
            
            if ($course) {
                $notifModel = new \App\Models\NotificationModel();
                $teacherName = $course['teacher_name'] ?? 'Your teacher';
                $notifModel->createNotification(
                    (int)$student_id, 
                    'You have been enrolled in ' . ($course['title'] ?? 'a course') . ' by ' . $teacherName . '. Please approve or reject this enrollment.'
                );
            }
        } catch (\Throwable $e) {
            // swallow notification errors to not block enrollment
        }
        
        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Student enrolled successfully! Waiting for student approval.'
        ]);
    }
}
