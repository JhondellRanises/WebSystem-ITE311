<?php namespace App\Models;

use CodeIgniter\Model;

class AnnouncementModel extends Model
{
    protected $table      = 'announcements';
    protected $primaryKey = 'id';

    protected $useTimestamps = true;        // useful for created_at if named correctly
    protected $createdField  = 'created_at';
    protected $updatedField  = '';          // no updated_at used
    protected $deletedField  = '';          // no soft deletes

    protected $allowedFields = ['title', 'content', 'created_at'];
}
