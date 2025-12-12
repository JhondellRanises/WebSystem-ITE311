<?php

namespace App\Controllers;

use App\Models\MaterialModel;
use App\Models\UserModel;
use App\Models\CourseModel;
use App\Models\EnrollmentModel;
use CodeIgniter\Controller;

class Admin extends BaseController
{
    public function dashboard()
    {
        // ✅ Check if logged in
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Please log in first.');
        }

        // ✅ Only admins can access
        if (session()->get('user_role') !== 'admin') {
            return redirect()->to('/announcements')
                             ->with('error', 'Access Denied: Insufficient Permissions.');
        }

        $db = \Config\Database::connect();
        $userModel = new UserModel();
        $courseModel = new CourseModel();
        $enrollmentModel = new EnrollmentModel();
        $materialModel = new MaterialModel();

        // Get statistics
        $totalUsers = $userModel->countAll();
        $totalTeachers = $userModel->where('role', 'teacher')->countAllResults();
        $totalStudents = $userModel->where('role', 'student')->countAllResults();
        $totalCourses = $courseModel->countAll();
        $totalEnrollments = $enrollmentModel->countAll();
        $approvedEnrollments = $enrollmentModel->where('status', 'approved')->countAllResults();
        $pendingEnrollments = $enrollmentModel->where('status', 'pending')->countAllResults();
        $totalMaterials = $materialModel->countAll();

        // Get recent courses
        $recentCourses = $courseModel->select('courses.*, users.name as instructor_name')
            ->join('users', 'users.id = courses.instructor_id', 'left')
            ->orderBy('courses.created_at', 'DESC')
            ->limit(5)
            ->findAll();

        // Get recent enrollments
        $recentEnrollments = $db->table('enrollments e')
            ->select('e.*, c.title as course_title, u.name as student_name, t.name as teacher_name')
            ->join('courses c', 'c.id = e.course_id')
            ->join('users u', 'u.id = e.user_id')
            ->join('users t', 't.id = c.instructor_id', 'left')
            ->orderBy('e.enrollment_date', 'DESC')
            ->limit(10)
            ->get()->getResultArray();

        $data = [
            'user_name' => session()->get('user_name'),
            'totalUsers' => $totalUsers,
            'totalTeachers' => $totalTeachers,
            'totalStudents' => $totalStudents,
            'totalCourses' => $totalCourses,
            'totalEnrollments' => $totalEnrollments,
            'approvedEnrollments' => $approvedEnrollments,
            'pendingEnrollments' => $pendingEnrollments,
            'totalMaterials' => $totalMaterials,
            'recentCourses' => $recentCourses,
            'recentEnrollments' => $recentEnrollments,
        ];

        return view('admin/dashboard', $data);
    }
}
