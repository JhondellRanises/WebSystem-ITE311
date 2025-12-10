<?php

namespace App\Models;

use CodeIgniter\Model;

class EnrollmentModel extends Model
{
    protected $table = 'enrollments';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id', 
        'course_id', 
        'enrollment_date',
        'status',
        'approved_at',
        'rejected_at',
        'rejection_reason'
    ];
    public $useTimestamps = false;

    public function enrollUser($data)
    {
        return $this->insert($data);
    }

    public function isAlreadyEnrolled($user_id, $course_id)
    {
        return $this->where('user_id', $user_id)
                    ->where('course_id', $course_id)
                    ->countAllResults() > 0;
    }

    public function isApproved($user_id, $course_id)
    {
        return $this->where('user_id', $user_id)
                    ->where('course_id', $course_id)
                    ->where('status', 'approved')
                    ->countAllResults() > 0;
    }

    public function getPendingEnrollments($course_id)
    {
        return $this->select('enrollments.*, users.name as student_name, users.email as student_email')
            ->join('users', 'users.id = enrollments.user_id', 'left')
            ->where('enrollments.course_id', $course_id)
            ->where('enrollments.status', 'pending')
            ->orderBy('enrollments.enrollment_date', 'DESC')
            ->findAll();
    }
}
