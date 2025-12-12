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
     * Check if schedule conflicts with any existing schedules
     * Prevents students from having overlapping schedules in different courses
     * Checks: day and time overlap (students cannot attend two classes at same time)
     */
    public function hasStudentConflict($courseId, $dayOfWeek, $startTime, $endTime, $roomNumber, $building, $excludeScheduleId = null)
    {
        $db = \Config\Database::connect();

        // Parse the new schedule's day range
        $newDays = $this->expandDayRange($dayOfWeek);

        // Convert times to comparable format (HH:MM)
        $newStartTime = substr($startTime, 0, 5);
        $newEndTime = substr($endTime, 0, 5);

        // Get all active schedules for ALL OTHER courses
        $builder = $db->table('schedules s')
            ->select('s.*')
            ->where('s.is_active', true)
            ->where('s.course_id !=', $courseId);

        if ($excludeScheduleId) {
            $builder->where('s.id !=', $excludeScheduleId);
        }

        $otherSchedules = $builder->get()->getResultArray();

        // Check each other schedule for time conflicts
        foreach ($otherSchedules as $other) {
            $otherDays = $this->expandDayRange($other['day_of_week']);

            // Check if there's any day overlap
            $dayOverlap = array_intersect($newDays, $otherDays);

            if (!empty($dayOverlap)) {
                // Convert other times to comparable format
                $otherStartTime = substr($other['start_time'], 0, 5);
                $otherEndTime = substr($other['end_time'], 0, 5);

                // Check if times overlap on any common day
                // Times overlap if: other_start < new_end AND other_end > new_start
                // A student cannot attend two classes at the same time, regardless of location
                if ($otherStartTime < $newEndTime && $otherEndTime > $newStartTime) {
                    return true; // Conflict found - overlapping day and time
                }

                // Also check for exact same time match
                if ($otherStartTime === $newStartTime && $otherEndTime === $newEndTime) {
                    return true; // Conflict found - exact same time
                }
            }
        }

        return false; // No conflicts
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
