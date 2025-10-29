<?php

namespace App\Controllers;

use App\Models\EnrollmentModel;
use CodeIgniter\Controller;

class Course extends BaseController
{
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

        // ✅ Check if already enrolled
        if ($enrollmentModel->isAlreadyEnrolled($user_id, $course_id)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'You are already enrolled in this course.'
            ]);
        }

        // ✅ Insert enrollment
        $data = [
            'user_id' => $user_id,
            'course_id' => $course_id,
            'enrollment_date' => date('Y-m-d H:i:s')
        ];

        try {
            if ($enrollmentModel->insert($data)) {
                // Create notification for the user
                try {
                    $db = \Config\Database::connect();
                    $course = $db->table('courses')->select('title')->where('id', $course_id)->get()->getRowArray();
                    $title = $course['title'] ?? ('Course #'.(string)$course_id);
                    $notifModel = new \App\Models\NotificationModel();
                    $notifModel->createNotification((int)$user_id, 'You have been enrolled in ' . $title);
                } catch (\Throwable $e) {
                    // swallow notification errors to not block enrollment
                }
                return $this->response->setJSON([
                    'status' => 'success',
                    'message' => 'Enrollment successful!'
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
