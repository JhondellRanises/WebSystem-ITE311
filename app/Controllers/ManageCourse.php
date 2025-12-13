<?php

namespace App\Controllers;

use App\Models\CourseModel;
use App\Models\UserModel;
use CodeIgniter\Exceptions\PageNotFoundException;

class ManageCourse extends BaseController
{
    protected $helpers = ['form', 'url'];

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
     * List courses with search and counts.
     */
    public function index()
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $q = trim((string)$this->request->getGet('q'));

        $courseModel = new CourseModel();
        $courseModel->select('courses.id, courses.title, courses.description, courses.created_at, courses.course_code, courses.term, courses.semester, courses.units, courses.academic_year, courses.department, courses.program, u.name as instructor_name, u.role as instructor_role')
            ->select('(SELECT COUNT(*) FROM enrollments e WHERE e.course_id = courses.id) as enroll_count', false)
            ->select('(SELECT COUNT(*) FROM materials m WHERE m.course_id = courses.id) as material_count', false)
            ->join('users u', 'u.id = courses.instructor_id', 'left')
            ->orderBy('courses.created_at', 'DESC');

        if ($q !== '') {
            $courseModel->groupStart()
                ->like('courses.title', $q)
                ->orLike('courses.description', $q)
                ->orLike('u.name', $q)
                ->groupEnd();
        }

        $courses = $courseModel->paginate(10, 'courses');
        $pager = $courseModel->pager;

        // Instructor options (admin or teacher only - exclude students)
        $userModel = new UserModel();
        $instructors = $userModel->whereIn('role', ['admin', 'teacher'])
            ->orderBy('name', 'ASC')
            ->findAll();

        // Dropdown options
        $academicYears = [];
        $currentYear = (int)date('Y');
        for ($i = $currentYear - 2; $i <= $currentYear + 2; $i++) {
            $academicYears[] = ($i - 1) . '-' . $i;
        }

        $semesters = [
            '1st Semester',
            '2nd Semester',
            'Summer',
        ];

        $terms = ['1', '2', '3'];

        $departments = [
            'Department of Teachers Education',
            'Department of Engineering and Technology',
            'Department of Arts and Sciences',
            'Department of Criminal and Justice',
            'Department of Business Education',
            'Department of Allied Health and Sciences',
        ];

        // Map departments to their programs
        $departmentPrograms = [
            'Department of Teachers Education' => [
                'Bachelor of Elementary Education',
                'Bachelor of Secondary Education',
            ],
            'Department of Engineering and Technology' => [
                'Bachelor of Science in Civil Engineering',
                'Bachelor of Science in Information Technology',
                'Bachelor of Science in Computer Science',
                'Bachelor of Science in Computer Engineering',
            ],
            'Department of Arts and Sciences' => [
                'Bachelor of Arts in English',
                'Bachelor of Arts in Mathematics',
                'Bachelor of Science in Psychology',
                'Bachelor of Science in Biology',
                'Bachelor of Science in Chemistry',
            ],
            'Department of Criminal and Justice' => [
                'Bachelor of Science in Criminology',
            ],
            'Department of Business Education' => [
                'Bachelor of Science in Business Administration',
                'Bachelor of Science in Accountancy',
                'Bachelor of Science in Entrepreneurship',
                'Bachelor of Science in Customs Administration'
            ],
            'Department of Allied Health and Sciences' => [
                'Bachelor of Science in Nursing',
                'Bachelor of Science in Medical Technology',
                'Bachelor of Science in Pharmacy',
                'Bachelor of Science in Physical Therapy',
            ],
        ];

        // Flatten all programs for initial dropdown (will be filtered by JS)
        $programs = [];
        foreach ($departmentPrograms as $deptPrograms) {
            $programs = array_merge($programs, $deptPrograms);
        }

        return view('admin/ManageCourse', [
            'courses' => $courses,
            'pager' => $pager,
            'q' => $q,
            'instructors' => $instructors,
            'academicYears' => $academicYears,
            'semesters' => $semesters,
            'terms' => $terms,
            'departments' => $departments,
            'programs' => $programs,
            'departmentPrograms' => $departmentPrograms,
            'errors' => session()->getFlashdata('errors') ?? [],
        ]);
    }

    /**
     * Create new course.
     */
    public function create()
    {
        if ($resp = $this->requireAdmin()) return $resp;
        if ($this->request->getMethod() === 'GET') {
            return redirect()->to('/admin/manage-courses');
        }

        $rules = [
            'title' => 'required|min_length[3]|max_length[150]|regex_match[/^[a-zA-Z0-9ñÑ\s\-()&.,\']+$/]',
            'description' => 'permit_empty|max_length[1000]',
            'instructor_id' => 'required|is_not_unique[users.id]',
            'course_code' => 'required|max_length[50]|regex_match[/^[a-zA-Z0-9ñÑ\s\-()&.,\']*$/]',
            'term' => 'required|in_list[1,2,3]',
            'semester' => 'required|max_length[50]',
            'units' => 'required|decimal',
            'academic_year' => 'required|max_length[20]',
            'department' => 'required|max_length[100]',
            'program' => 'required|max_length[100]',
        ];
        
        // Custom error messages
        $errors = [
            'title' => [
                'regex_match' => 'The course title can only contain letters, numbers, and ñ. Special characters are not allowed.'
            ],
            'course_code' => [
                'regex_match' => 'The course code can only contain letters, numbers, and ñ. Special characters are not allowed.'
            ]
        ];

        if (!$this->validate($rules, $errors)) {
            return redirect()->to('/admin/manage-courses')
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $instructorId = $this->request->getPost('instructor_id');
        if (!empty($instructorId)) {
            $instructorId = (int)$instructorId;
            $userModel = new UserModel();
            $instructor = $userModel->find($instructorId);
            if (!$instructor || !in_array($instructor['role'], ['admin', 'teacher'])) {
                return redirect()->to('/admin/manage-courses')
                    ->withInput()
                    ->with('errors', ['instructor_id' => 'Instructor must be an Admin or Teacher account.']);
            }
        } else {
            $instructorId = null;
        }

        $courseModel = new CourseModel();
        $courseModel->insert([
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'instructor_id' => $instructorId,
            'course_code' => $this->request->getPost('course_code'),
            'units' => $this->request->getPost('units'),
            'term' => $this->request->getPost('term'),
            'semester' => $this->request->getPost('semester'),
            'academic_year' => $this->request->getPost('academic_year'),
            'department' => $this->request->getPost('department'),
            'program' => $this->request->getPost('program'),
        ]);

        return redirect()->to('/admin/manage-courses')->with('success', 'Course created successfully.');
    }

    /**
     * Show course for AJAX edit.
     */
    public function show($id)
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $courseModel = new CourseModel();
        $course = $courseModel->findWithInstructor((int)$id);
        if (!$course) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->response->setJSON([
            'course' => $course,
            'csrf' => csrf_hash(),
        ]);
    }

    /**
     * Update course.
     */
    public function edit($id)
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $id = (int)$id;
        if ($this->request->getMethod() === 'GET') {
            return redirect()->to('/admin/manage-courses?edit=' . $id);
        }

        $courseModel = new CourseModel();
        $course = $courseModel->find($id);
        if (!$course) {
            return redirect()->to('/admin/manage-courses')->with('error', 'Course not found.');
        }

        $rules = [
            'title' => 'required|min_length[3]|max_length[150]|regex_match[/^[a-zA-Z0-9ñÑ\s\-()&.,\']+$/]',
            'description' => 'permit_empty|max_length[1000]',
            'instructor_id' => 'permit_empty|is_not_unique[users.id]',
            'course_code' => 'permit_empty|max_length[50]|regex_match[/^[a-zA-Z0-9ñÑ\s\-()&.,\']*$/]',
            'units' => 'permit_empty|decimal|greater_than_equal_to[0]|less_than_equal_to[10]',
            'term' => 'permit_empty|in_list[1,2,3]',
            'semester' => 'permit_empty|max_length[50]',
            'academic_year' => 'permit_empty|max_length[20]',
            'department' => 'permit_empty|max_length[100]',
            'program' => 'permit_empty|max_length[100]',
        ];

        // Custom error messages for edit
        $errors = [
            'title' => [
                'regex_match' => 'The course title can only contain letters, numbers, and ñ. Special characters are not allowed.'
            ],
            'course_code' => [
                'regex_match' => 'The course code can only contain letters, numbers, and ñ. Special characters are not allowed.'
            ]
        ];

        if (!$this->validate($rules, $errors)) {
            return redirect()->to('/admin/manage-courses?edit=' . $id)
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $instructorId = $this->request->getPost('instructor_id');
        if (!empty($instructorId)) {
            $instructorId = (int)$instructorId;
            $userModel = new UserModel();
            $instructor = $userModel->find($instructorId);
            if (!$instructor || !in_array($instructor['role'], ['admin', 'teacher'])) {
                return redirect()->to('/admin/manage-courses?edit=' . $id)
                    ->withInput()
                    ->with('errors', ['instructor_id' => 'Instructor must be an Admin or Teacher account.']);
            }
        } else {
            $instructorId = null;
        }

        $courseModel = new CourseModel();
        $courseModel->update($id, [
            'title' => $this->request->getPost('title'),
            'description' => $this->request->getPost('description'),
            'instructor_id' => $instructorId,
            'course_code' => $this->request->getPost('course_code'),
            'units' => $this->request->getPost('units'),
            'term' => $this->request->getPost('term'),
            'semester' => $this->request->getPost('semester'),
            'academic_year' => $this->request->getPost('academic_year'),
            'department' => $this->request->getPost('department'),
            'program' => $this->request->getPost('program'),
        ]);

        return redirect()->to('/admin/manage-courses')->with('success', 'Course updated successfully.');
    }

    /**
     * Delete course.
     */
    public function delete($id)
    {
        if ($resp = $this->requireAdmin()) return $resp;
        $id = (int)$id;
        $courseModel = new CourseModel();
        $course = $courseModel->find($id);
        if (!$course) {
            return redirect()->to('/admin/manage-courses')->with('error', 'Course not found.');
        }

        try {
            $courseModel->delete($id);
            return redirect()->to('/admin/manage-courses')->with('success', 'Course deleted successfully.');
        } catch (\Throwable $e) {
            return redirect()->to('/admin/manage-courses')->with('error', 'Failed to delete course: ' . $e->getMessage());
        }
    }
}

