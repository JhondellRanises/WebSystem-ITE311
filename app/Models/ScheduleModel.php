<?php

namespace App\Models;

use CodeIgniter\Model;

class ScheduleModel extends Model
{
    protected $table = 'schedules';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'course_id',
        'instructor_id',
        'day_of_week',
        'start_time',
        'end_time',
        'room_number',
        'building',
        'duration_minutes',
        'capacity',
        'notes',
        'is_active',
        'created_at',
        'updated_at'
    ];
    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    /**
     * Get all schedules with course and instructor details
     */
    public function getAllSchedules()
    {
        return $this->select('schedules.*, courses.title as course_title, courses.course_code, users.name as instructor_name')
            ->join('courses', 'courses.id = schedules.course_id', 'left')
            ->join('users', 'users.id = schedules.instructor_id', 'left')
            ->orderBy('schedules.day_of_week', 'ASC')
            ->orderBy('schedules.start_time', 'ASC')
            ->findAll();
    }

    /**
     * Get schedules for a specific course
     */
    public function getCourseSchedules($courseId)
    {
        return $this->select('schedules.*, courses.title as course_title, users.name as instructor_name')
            ->join('courses', 'courses.id = schedules.course_id', 'left')
            ->join('users', 'users.id = schedules.instructor_id', 'left')
            ->where('schedules.course_id', $courseId)
            ->where('schedules.is_active', true)
            ->orderBy('schedules.day_of_week', 'ASC')
            ->orderBy('schedules.start_time', 'ASC')
            ->findAll();
    }

    /**
     * Get schedules for a specific instructor
     */
    public function getInstructorSchedules($instructorId)
    {
        return $this->select('schedules.*, courses.title as course_title, courses.course_code')
            ->join('courses', 'courses.id = schedules.course_id', 'left')
            ->where('schedules.instructor_id', $instructorId)
            ->where('schedules.is_active', true)
            ->orderBy('schedules.day_of_week', 'ASC')
            ->orderBy('schedules.start_time', 'ASC')
            ->findAll();
    }

    /**
     * Get schedules for a student (through enrollments)
     */
    public function getStudentSchedules($studentId)
    {
        return $this->select('schedules.*, courses.title as course_title, courses.course_code, users.name as instructor_name')
            ->join('courses', 'courses.id = schedules.course_id', 'left')
            ->join('users', 'users.id = schedules.instructor_id', 'left')
            ->join('enrollments', 'enrollments.course_id = schedules.course_id', 'left')
            ->where('enrollments.user_id', $studentId)
            ->where('enrollments.status', 'approved')
            ->where('schedules.is_active', true)
            ->groupBy('schedules.id')
            ->orderBy('schedules.day_of_week', 'ASC')
            ->orderBy('schedules.start_time', 'ASC')
            ->findAll();
    }

    /**
     * Get schedule with full details
     */
    public function getScheduleWithDetails($scheduleId)
    {
        return $this->select('schedules.*, courses.title as course_title, courses.course_code, users.name as instructor_name, users.email as instructor_email')
            ->join('courses', 'courses.id = schedules.course_id', 'left')
            ->join('users', 'users.id = schedules.instructor_id', 'left')
            ->where('schedules.id', $scheduleId)
            ->first();
    }

    /**
     * Check if time slot is available for instructor
     * Handles both single days and day ranges (e.g., "Monday-Friday")
     */
    public function isTimeSlotAvailable($instructorId, $dayOfWeek, $startTime, $endTime, $excludeScheduleId = null)
    {
        // Get all active schedules for this instructor
        $builder = $this->where('instructor_id', $instructorId)
            ->where('is_active', true);

        if ($excludeScheduleId) {
            $builder->where('id !=', $excludeScheduleId);
        }

        $existingSchedules = $builder->findAll();

        // Parse the new schedule's day range
        $newDays = $this->expandDayRange($dayOfWeek);
        
        // Check each existing schedule for conflicts
        foreach ($existingSchedules as $existing) {
            $existingDays = $this->expandDayRange($existing['day_of_week']);
            
            // Check if there's any day overlap
            $dayOverlap = array_intersect($newDays, $existingDays);
            
            if (!empty($dayOverlap)) {
                // Check if times overlap on any common day
                // Overlap occurs when: start_time < new_end_time AND end_time > new_start_time
                if ($existing['start_time'] < $endTime && $existing['end_time'] > $startTime) {
                    return false; // Conflict found
                }
            }
        }

        return true; // No conflicts
    }

    /**
     * Expand day range (e.g., "Monday-Friday") into array of individual days
     */
    private function expandDayRange($dayRange)
    {
        $dayOrder = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
        $days = [];
        
        if (strpos($dayRange, '-') !== false) {
            // Handle day range (e.g., "Monday-Friday")
            list($startDay, $endDay) = explode('-', $dayRange);
            $startDay = trim($startDay);
            $endDay = trim($endDay);
            
            $inRange = false;
            foreach ($dayOrder as $day) {
                if ($day === $startDay) {
                    $inRange = true;
                }
                if ($inRange) {
                    $days[] = $day;
                }
                if ($day === $endDay) {
                    $inRange = false;
                }
            }
        } else {
            // Single day
            $days[] = $dayRange;
        }
        
        return $days;
    }
}
