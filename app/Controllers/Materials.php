<?php

namespace App\Controllers;

use App\Models\MaterialModel;
use App\Models\EnrollmentModel;

class Materials extends BaseController
{
    protected $helpers = ['form', 'url'];

    public function upload($course_id = null)
    {
        if (!session()->get('logged_in') || !in_array(session()->get('user_role'), ['admin', 'teacher'])) {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }
        
        // Check for course_id in query parameter if not provided in route
        if ($course_id === null) {
            $queryCourseid = $this->request->getGet('course_id');
            if (!empty($queryCourseid)) {
                $course_id = (int)$queryCourseid;
            }
        }
        
        // If no course is provided, show a blank chooser page
        if ($course_id === null) {
            $db = \Config\Database::connect();
            $builder = $db->table('courses')->select('id, title');
            if (session()->get('user_role') === 'teacher') {
                $builder->where('instructor_id', session()->get('user_id'));
            }
            $courses = $builder->orderBy('title','ASC')->get()->getResultArray();

            // Build aggregated materials list (admin: all; teacher: only own courses)
            $allMaterials = [];
            try {
                $builderAll = $db->table('materials m')
                    ->select('m.id, m.file_name, m.created_at, m.course_id, c.title as course_title')
                    ->join('courses c', 'c.id = m.course_id')
                    ->where('m.deleted_at', null);
                if (session()->get('user_role') === 'teacher') {
                    $builderAll->where('c.instructor_id', session()->get('user_id'));
                }
                $allMaterials = $builderAll->orderBy('m.created_at','DESC')->get()->getResultArray();
            } catch (\Exception $e) {
                // If join fails, try without course title
                try {
                    $builderAll = $db->table('materials')
                        ->where('deleted_at', null);
                    if (session()->get('user_role') === 'teacher') {
                        $builderAll->where('instructor_id', session()->get('user_id'));
                    }
                    $allMaterials = $builderAll->orderBy('created_at','DESC')->get()->getResultArray();
                } catch (\Exception $e2) {
                    $allMaterials = [];
                }
            }

            // Get deleted materials
            $deletedMaterials = [];
            try {
                $materialModel = new MaterialModel();
                $deletedMaterials = $materialModel->getDeletedMaterials();
                if (session()->get('user_role') === 'teacher') {
                    $deletedMaterials = array_filter($deletedMaterials, function($m) use ($db) {
                        try {
                            $course = $db->table('courses')->where('id', $m['course_id'])->get()->getRowArray();
                            return $course && $course['instructor_id'] == session()->get('user_id');
                        } catch (\Exception $e) {
                            return false;
                        }
                    });
                }
            } catch (\Exception $e) {
                $deletedMaterials = [];
            }

            $viewPath = (session()->get('user_role') === 'admin') ? 'admin/upload' : 'teacher/upload';
            return view($viewPath, [
                'course_id' => null,
                'current_course' => null,
                'materials' => [],
                'courses' => $courses,
                'all_materials' => $allMaterials,
                'deleted_materials' => $deletedMaterials,
            ]);
        }

        $db = \Config\Database::connect();
        $current_course = $db->table('courses')->select('id, title')->where('id', $course_id)->get()->getRowArray();
        $materialModel = new MaterialModel();
        $materials = $materialModel->getMaterialsByCourse($course_id);

        // Build aggregated materials list (admin: all, teacher: own courses)
        $role = session()->get('user_role');
        $userId = session()->get('user_id');
        $allMaterials = [];
        try {
            $builderAll = $db->table('materials m')
                ->select('m.id, m.file_name, m.created_at, m.course_id, c.title as course_title')
                ->join('courses c', 'c.id = m.course_id')
                ->where('m.deleted_at', null);
            if ($role === 'teacher') {
                $builderAll->where('c.instructor_id', $userId);
            }
            $allMaterials = $builderAll->orderBy('m.created_at','DESC')->get()->getResultArray();
        } catch (\Exception $e) {
            // If join fails, try without course title
            try {
                $builderAll = $db->table('materials')
                    ->where('deleted_at', null);
                if ($role === 'teacher') {
                    $builderAll->where('instructor_id', $userId);
                }
                $allMaterials = $builderAll->orderBy('created_at','DESC')->get()->getResultArray();
            } catch (\Exception $e2) {
                $allMaterials = [];
            }
        }

        // Get deleted materials
        $deletedMaterials = [];
        try {
            $materialModel = new MaterialModel();
            $deletedMaterials = $materialModel->getDeletedMaterials();
            if ($role === 'teacher') {
                $deletedMaterials = array_filter($deletedMaterials, function($m) use ($db, $userId) {
                    try {
                        $course = $db->table('courses')->where('id', $m['course_id'])->get()->getRowArray();
                        return $course && $course['instructor_id'] == $userId;
                    } catch (\Exception $e) {
                        return false;
                    }
                });
            }
        } catch (\Exception $e) {
            $deletedMaterials = [];
        }

        if ($this->request->getMethod() === 'GET') {
            // Also load other courses for quick navigation
            $builder = $db->table('courses')->select('id, title');
            if (session()->get('user_role') === 'teacher') {
                $builder->where('instructor_id', session()->get('user_id'));
            }
            $courses = $builder->orderBy('title','ASC')->get()->getResultArray();
            $viewPath = (session()->get('user_role') === 'admin') ? 'admin/upload' : 'teacher/upload';
            return view($viewPath, [
                'course_id' => $course_id,
                'current_course' => $current_course,
                'materials' => $materials,
                'courses' => $courses,
                'all_materials' => $allMaterials,
                'deleted_materials' => $deletedMaterials,
            ]);
        }

        if ($this->request->getMethod() === 'POST') {
            if (empty($course_id)) {
                return redirect()->back()->with('error', 'Please select a course first.');
            }
            
            // Get exam type from form
            $examType = $this->request->getPost('exam_type');
            $validExamTypes = ['Prelim', 'Midterm', 'Final'];
            
            if (empty($examType) || !in_array($examType, $validExamTypes)) {
                return redirect()->back()->with('error', 'Please select a valid exam type (Prelim, Midterm, or Final).')->withInput();
            }
            
            // Accept both 'material_file' (new) and 'material' (legacy)
            $fileField = $this->request->getFile('material_file') && $this->request->getFile('material_file')->isValid()
                ? 'material_file' : 'material';

            $file = $this->request->getFile($fileField);
            
            // Check if file exists and is valid
            if (!$file || !$file->isValid()) {
                return redirect()->back()->with('error', 'Please select a valid file.')->withInput();
            }
            
            // Get file extension
            $fileExtension = strtolower($file->getClientExtension());
            $allowedExtensions = ['pdf', 'ppt', 'pptx', 'doc', 'docx'];
            
            // Check file extension
            if (!in_array($fileExtension, $allowedExtensions)) {
                $extensionList = implode(', ', array_map('strtoupper', $allowedExtensions));
                return redirect()->back()->with('error', 'Invalid file type. Only ' . $extensionList . ' files are allowed. Video files (MP4, AVI, MOV, etc.) are not supported.')->withInput();
            }
            
            // Check file size (10MB max)
            if ($file->getSize() > 10240 * 1024) {
                return redirect()->back()->with('error', 'File size exceeds 10MB limit.')->withInput();
            }
            
            $rules = [
                $fileField => 'uploaded['.$fileField.']|max_size['.$fileField.',10240]|ext_in['.$fileField.',pdf,ppt,pptx,doc,docx]'
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()->with('error', 'Invalid file upload.')->withInput();
            }


            $originalName = $file->getClientName();
            
            // Check if file with same name already exists in this course
            $existingFile = $db->table('materials')
                ->where('course_id', $course_id)
                ->where('file_name', $originalName)
                ->where('deleted_at', null)
                ->get()
                ->getRowArray();
            
            if ($existingFile) {
                return redirect()->back()->with('error', 'A file with the name "' . esc($originalName) . '" already exists in this course. Please rename the file or delete the existing one first.');
            }
            $uploadRoot = WRITEPATH . 'uploads/materials/course_' . $course_id;
            if (!is_dir($uploadRoot)) {
                mkdir($uploadRoot, 0777, true);
            }

            $newName = time() . '_' . preg_replace('/[^A-Za-z0-9_\.-]/', '_', $originalName);
            if (!$file->move($uploadRoot, $newName)) {
                return redirect()->back()->with('error', 'Failed to move uploaded file.');
            }

            $relativePath = 'uploads/materials/course_' . $course_id . '/' . $newName;
            $materialModel = new MaterialModel();
            $materialModel->insertMaterial([
                'course_id' => (int) $course_id,
                'file_name' => $originalName,
                'file_path' => $relativePath,
                'exam_type' => $examType,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            // Send notifications to all enrolled students
            try {
                $course = $db->table('courses')->where('id', $course_id)->get()->getRowArray();
                $courseTitle = $course ? esc($course['title']) : 'Course #' . $course_id;
                $uploaderName = session()->get('user_name') ?? 'Teacher';
                
                // Get all enrolled students in this course (both approved and pending)
                $enrolledStudents = $db->table('enrollments')
                    ->select('user_id')
                    ->where('course_id', (int)$course_id)
                    ->whereIn('status', ['approved', 'pending'])
                    ->get()
                    ->getResultArray();
                
                error_log('Found ' . count($enrolledStudents) . ' enrolled students for course ' . $course_id);
                
                if (!empty($enrolledStudents)) {
                    $notificationModel = new \App\Models\NotificationModel();
                    $message = "New material uploaded to {$courseTitle}: {$originalName} ({$examType}) by {$uploaderName}";
                    
                    foreach ($enrolledStudents as $student) {
                        $userId = (int)$student['user_id'];
                        if ($userId > 0) {
                            $result = $notificationModel->createNotification($userId, $message);
                            error_log('Notification for user ' . $userId . ': ' . ($result ? 'created' : 'failed'));
                        }
                    }
                }
            } catch (\Exception $e) {
                error_log('Material upload notification error: ' . $e->getMessage());
            }

            return redirect()->to('/admin/course/' . $course_id . '/upload')->with('success', 'Material uploaded successfully.');
        }
    }

    public function delete($material_id)
    {
        if (!session()->get('logged_in') || !in_array(session()->get('user_role'), ['admin', 'teacher'])) {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $db = \Config\Database::connect();
        $material = $db->table('materials')->where('id', $material_id)->get()->getRowArray();
        if (!$material) {
            return redirect()->back()->with('error', 'Material not found.');
        }

        // Check if deleted_at column exists
        try {
            $columns = $db->getFieldData('materials');
            $hasDeletedAt = false;
            foreach ($columns as $column) {
                if ($column->name === 'deleted_at') {
                    $hasDeletedAt = true;
                    break;
                }
            }
            
            if ($hasDeletedAt) {
                // Soft delete - set deleted_at timestamp
                $db->table('materials')->where('id', $material_id)->update(['deleted_at' => date('Y-m-d H:i:s')]);
                return redirect()->back()->with('success', 'Material deleted. You can restore it from the Trash section.');
            } else {
                // Hard delete if column doesn't exist
                $fullPath = WRITEPATH . $material['file_path'];
                if (is_file($fullPath)) {
                    @unlink($fullPath);
                }
                $db->table('materials')->where('id', $material_id)->delete();
                return redirect()->back()->with('success', 'Material deleted successfully.');
            }
        } catch (\Exception $e) {
            // If error checking columns, do hard delete
            $fullPath = WRITEPATH . $material['file_path'];
            if (is_file($fullPath)) {
                @unlink($fullPath);
            }
            $db->table('materials')->where('id', $material_id)->delete();
            return redirect()->back()->with('success', 'Material deleted successfully.');
        }
    }

    public function restore($material_id)
    {
        if (!session()->get('logged_in') || !in_array(session()->get('user_role'), ['admin', 'teacher'])) {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $db = \Config\Database::connect();
        $material = $db->table('materials')->where('id', $material_id)->get()->getRowArray();
        if (!$material) {
            return redirect()->back()->with('error', 'Material not found.');
        }

        // Restore the material
        $db->table('materials')->where('id', $material_id)->update(['deleted_at' => null]);
        return redirect()->back()->with('success', 'Material restored successfully.');
    }

    public function permanentDelete($material_id)
    {
        if (!session()->get('logged_in') || !in_array(session()->get('user_role'), ['admin', 'teacher'])) {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $db = \Config\Database::connect();
        $material = $db->table('materials')->where('id', $material_id)->get()->getRowArray();
        if (!$material) {
            return redirect()->back()->with('error', 'Material not found.');
        }

        // Permanently delete the file
        $fullPath = WRITEPATH . $material['file_path'];
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }

        // Hard delete from database
        $db->table('materials')->where('id', $material_id)->delete();
        return redirect()->back()->with('success', 'Material permanently deleted.');
    }

    public function download($material_id)
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Please log in first.');
        }

        $materialModel = new MaterialModel();
        $enrollmentModel = new EnrollmentModel();

        $material = $materialModel->find($material_id);
        if (!$material) {
            return redirect()->back()->with('error', 'Material not found.');
        }

        $user_id = session()->get('user_id');
        $course_id = $material['course_id'];

        $isAdminOrTeacher = in_array(session()->get('user_role'), ['admin', 'teacher']);
        
        // For students, check if they have approved enrollment
        if ($isAdminOrTeacher) {
            $hasAccess = true;
        } else {
            // Check if enrollment exists and is approved
            try {
                $hasAccess = $enrollmentModel->isApproved($user_id, $course_id);
            } catch (\Exception $e) {
                // Fallback: if status column doesn't exist, check if enrolled at all
                $hasAccess = $enrollmentModel->isAlreadyEnrolled($user_id, $course_id);
            }
        }

        if (!$hasAccess) {
            return redirect()->back()->with('error', 'Access restricted to enrolled students with approved enrollment.');
        }

        $fullPath = WRITEPATH . $material['file_path'];
        if (!is_file($fullPath)) {
            return redirect()->back()->with('error', 'File not found on server.');
        }

        return $this->response->download($fullPath, null)->setFileName($material['file_name']);
    }

    public function student()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Please log in first.');
        }
        if (session()->get('user_role') !== 'student') {
            return redirect()->to('/dashboard');
        }

        $db = \Config\Database::connect();
        $userId = session()->get('user_id');
        
        // Get enrolled courses (only approved enrollments)
        try {
            $enrolled = $db->table('enrollments e')
                ->select('c.id, c.title, c.description, u.name as instructor_name')
                ->join('courses c', 'c.id = e.course_id')
                ->join('users u', 'u.id = c.instructor_id', 'left')
                ->where('e.user_id', $userId)
                ->where('e.status', 'approved')
                ->orderBy('c.title', 'ASC')
                ->get()->getResultArray();
        } catch (\Exception $e) {
            // Fallback: if status column doesn't exist, treat all enrollments as approved
            $enrolled = $db->table('enrollments e')
                ->select('c.id, c.title, c.description, u.name as instructor_name')
                ->join('courses c', 'c.id = e.course_id')
                ->join('users u', 'u.id = c.instructor_id', 'left')
                ->where('e.user_id', $userId)
                ->orderBy('c.title', 'ASC')
                ->get()->getResultArray();
        }

        if (empty($enrolled)) {
            return view('student/materials', [
                'courses' => [],
                'course_id' => null,
                'materials' => [],
            ]);
        }

        $courseId = (int) ($this->request->getGet('course_id') ?? $enrolled[0]['id']);
        $materialModel = new MaterialModel();
        $materials = $materialModel->getMaterialsByCourse($courseId);

        return view('student/materials', [
            'courses' => $enrolled,
            'course_id' => $courseId,
            'materials' => $materials,
        ]);
    }

    public function studentCourses()
    {
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Please log in first.');
        }
        if (session()->get('user_role') !== 'student') {
            return redirect()->to('/dashboard');
        }

        $db = \Config\Database::connect();
        $userId = session()->get('user_id');
        
        // Try to get enrollments with status column, fallback if column doesn't exist
        try {
            // Get enrolled courses (only approved)
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
            ->select('c.id, c.title, c.description, c.instructor_id, u.name as instructor_name')
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
}
