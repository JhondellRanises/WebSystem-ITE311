<?php

namespace App\Models;

use CodeIgniter\Model;

class CourseModel extends Model
{
    protected $table = 'courses';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'title',
        'description',
        'instructor_id',
        'course_code',
        'units',
        'term',
        'semester',
        'academic_year',
        'department',
        'program',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Return a single course with instructor info.
     */
    public function findWithInstructor(int $id)
    {
        return $this->select('courses.*, users.name as instructor_name, users.role as instructor_role')
            ->join('users', 'users.id = courses.instructor_id', 'left')
            ->where('courses.id', $id)
            ->first();
    }
}

