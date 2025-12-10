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
        ->select('c.*, u.name as instructor_name')
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

    public function enroll()
    {
        // ✅ Check session
        if (!session()->get('logged_in')) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'You must be logged in to enroll.'
            ]);
        }

        $user_id = session()->get('user_id');
        $course_id = $this->request->getPost('course_id');

        // ✅ Validate course_id
        if (empty($course_id)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'No course selected.'
            ]);
        }

        $enrollmentModel = new EnrollmentModel();

        // ✅ Check if already enrolled (pending or approved)
        $existingEnrollment = $enrollmentModel->where('user_id', $user_id)
            ->where('course_id', $course_id)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if ($existingEnrollment) {
            $status = $existingEnrollment['status'] === 'pending' ? 'pending approval' : 'already enrolled';
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'You are ' . $status . ' in this course.'
            ]);
        }

        // ✅ Insert enrollment with pending status
        $data = [
            'user_id' => $user_id,
            'course_id' => $course_id,
            'enrollment_date' => date('Y-m-d H:i:s'),
            'status' => 'pending'
        ];

        try {
            if ($enrollmentModel->insert($data)) {
                // Create notification for the instructor
                try {
                    $db = \Config\Database::connect();
                    $course = $db->table('courses')
                        ->select('courses.title, courses.instructor_id, users.name as student_name')
                        ->join('users', 'users.id = ' . $user_id, 'left')
                        ->where('courses.id', $course_id)
                        ->get()
                        ->getRowArray();
                    
                    if ($course && $course['instructor_id']) {
                        $notifModel = new \App\Models\NotificationModel();
                        $studentName = session()->get('user_name') ?? 'A student';
                        $notifModel->createNotification(
                            (int)$course['instructor_id'], 
                            $studentName . ' has requested enrollment in ' . ($course['title'] ?? 'a course')
                        );
                    }
                } catch (\Throwable $e) {
                    // swallow notification errors to not block enrollment
                }
                
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Enrollment request submitted! Waiting for instructor approval.'
                ]);
            } else {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Enrollment failed. Please try again.'
                ]);
            }
        } catch (\Exception $e) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Database error: ' . $e->getMessage()
            ]);
        }
    }
}
