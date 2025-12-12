<?php

namespace App\Models;

use CodeIgniter\Model;

class MaterialModel extends Model
{
    protected $table = 'materials';
    protected $primaryKey = 'id';
    protected $allowedFields = ['course_id', 'file_name', 'file_path', 'exam_type', 'created_at', 'deleted_at'];
    public $useTimestamps = false;

    public function insertMaterial($data)
    {
        return $this->insert($data);
    }

    public function getMaterialsByCourse($course_id)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('materials')
                      ->where('course_id', $course_id)
                      ->orderBy('created_at', 'DESC');
        
        // Only filter by deleted_at if the column exists
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
                $builder->where('deleted_at', null);
            }
        } catch (\Exception $e) {
            // If we can't check columns, just skip the filter
        }
        
        return $builder->get()->getResultArray();
    }

    public function getDeletedMaterials($course_id = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('materials m')
                      ->select('m.id, m.file_name, m.created_at, m.course_id, m.deleted_at, c.title as course_title')
                      ->join('courses c', 'c.id = m.course_id', 'left');
        
        // Only filter by deleted_at if the column exists
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
                $builder->where('m.deleted_at IS NOT NULL');
            } else {
                return []; // No deleted_at column, return empty
            }
        } catch (\Exception $e) {
            return []; // If we can't check columns, return empty
        }
        
        if ($course_id) {
            $builder->where('m.course_id', $course_id);
        }
        return $builder->orderBy('m.deleted_at', 'DESC')
                       ->get()
                       ->getResultArray();
    }

    public function getAllMaterials($course_id = null)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('materials');
        
        // Only filter by deleted_at if the column exists
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
                $builder->where('deleted_at', null);
            }
        } catch (\Exception $e) {
            // If we can't check columns, just skip the filter
        }
        
        if ($course_id) {
            $builder->where('course_id', $course_id);
        }
        return $builder->orderBy('created_at', 'DESC')
                       ->get()
                       ->getResultArray();
    }

    public function getMaterialsByExamType($course_id, $exam_type)
    {
        $db = \Config\Database::connect();
        $builder = $db->table('materials')
                      ->where('course_id', $course_id)
                      ->where('exam_type', $exam_type)
                      ->orderBy('created_at', 'DESC');
        
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
                $builder->where('deleted_at', null);
            }
        } catch (\Exception $e) {
            // If we can't check columns, just skip the filter
        }
        
        return $builder->get()->getResultArray();
    }

    public function getMaterialsGroupedByExamType($course_id)
    {
        $examTypes = ['Prelim', 'Midterm', 'Final'];
        $grouped = [];
        
        foreach ($examTypes as $type) {
            $grouped[$type] = $this->getMaterialsByExamType($course_id, $type);
        }
        
        return $grouped;
    }
}
