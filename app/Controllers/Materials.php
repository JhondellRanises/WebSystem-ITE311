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
        // If no course is provided, show a blank chooser page
        if ($course_id === null) {
            $db = \Config\Database::connect();
            $builder = $db->table('courses')->select('id, title');
            if (session()->get('user_role') === 'teacher') {
                $builder->where('instructor_id', session()->get('user_id'));
            }
            $courses = $builder->orderBy('title','ASC')->get()->getResultArray();

            // Build aggregated materials list (admin: all; teacher: only own courses)
            $builderAll = $db->table('materials m')
                ->select('m.id, m.file_name, m.created_at, m.course_id, c.title as course_title')
                ->join('courses c', 'c.id = m.course_id');
            if (session()->get('user_role') === 'teacher') {
                $builderAll->where('c.instructor_id', session()->get('user_id'));
            }
            $allMaterials = $builderAll->orderBy('m.created_at','DESC')->get()->getResultArray();

            $viewPath = (session()->get('user_role') === 'admin') ? 'admin/upload' : 'teacher/upload';
            return view($viewPath, [
                'course_id' => null,
                'current_course' => null,
                'materials' => [],
                'courses' => $courses,
                'all_materials' => $allMaterials,
            ]);
        }

        $db = \Config\Database::connect();
        $current_course = $db->table('courses')->select('id, title')->where('id', $course_id)->get()->getRowArray();
        $materialModel = new MaterialModel();
        $materials = $materialModel->getMaterialsByCourse($course_id);

        // Build aggregated materials list (admin: all, teacher: own courses)
        $role = session()->get('user_role');
        $userId = session()->get('user_id');
        $builderAll = $db->table('materials m')
            ->select('m.id, m.file_name, m.created_at, m.course_id, c.title as course_title')
            ->join('courses c', 'c.id = m.course_id');
        if ($role === 'teacher') {
            $builderAll->where('c.instructor_id', $userId);
        }
        $allMaterials = $builderAll->orderBy('m.created_at','DESC')->get()->getResultArray();

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
            ]);
        }

        if ($this->request->getMethod() === 'POST') {
            if (empty($course_id)) {
                return redirect()->back()->with('error', 'Please select a course first.');
            }
            // Accept both 'material_file' (new) and 'material' (legacy)
            $fileField = $this->request->getFile('material_file') && $this->request->getFile('material_file')->isValid()
                ? 'material_file' : 'material';

            $rules = [
                $fileField => 'uploaded['.$fileField.']|max_size['.$fileField.',10240]|ext_in['.$fileField.',pdf,ppt,pptx,doc,docx,xls,xlsx,zip,rar,txt,jpg,jpeg,png]'
            ];

            if (!$this->validate($rules)) {
                return redirect()->back()->with('error', 'Invalid file upload.')->withInput();
            }

            $file = $this->request->getFile($fileField);
            if (!$file->isValid()) {
                return redirect()->back()->with('error', 'File is not valid.');
            }

            $originalName = $file->getClientName();
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
                'created_at' => date('Y-m-d H:i:s')
            ]);

            return redirect()->to('/admin/course/' . $course_id . '/upload')->with('success', 'Material uploaded successfully.');
        }
    }

    public function delete($material_id)
    {
        if (!session()->get('logged_in') || !in_array(session()->get('user_role'), ['admin', 'teacher'])) {
            return redirect()->to('/login')->with('error', 'Access denied.');
        }

        $materialModel = new MaterialModel();
        $material = $materialModel->find($material_id);
        if (!$material) {
            return redirect()->back()->with('error', 'Material not found.');
        }

        $fullPath = WRITEPATH . $material['file_path'];
        if (is_file($fullPath)) {
            @unlink($fullPath);
        }

        $materialModel->delete($material_id);
        return redirect()->back()->with('success', 'Material deleted.');
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
        $isEnrolled = $enrollmentModel->isAlreadyEnrolled($user_id, $course_id);

        if (!($isAdminOrTeacher || $isEnrolled)) {
            return redirect()->back()->with('error', 'Access restricted to enrolled students.');
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
        // Get enrolled courses
        $enrolled = $db->table('enrollments e')
            ->select('c.id, c.title')
            ->join('courses c', 'c.id = e.course_id')
            ->where('e.user_id', $userId)
            ->orderBy('c.title', 'ASC')
            ->get()->getResultArray();

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
        $enrolled = $db->table('enrollments e')
            ->select('c.id, c.title')
            ->join('courses c', 'c.id = e.course_id')
            ->where('e.user_id', $userId)
            ->orderBy('c.title', 'ASC')
            ->get()->getResultArray();

        return view('student/courses', [ 'courses' => $enrolled ]);
    }
}
