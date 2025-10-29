<?php
namespace App\Models;

use CodeIgniter\Model;

class NotificationModel extends Model
{
    protected $table = 'notifications';
    protected $primaryKey = 'id';
    protected $allowedFields = ['user_id', 'message', 'is_read', 'created_at'];
    public $useTimestamps = false;

    public function getUnreadCount(int $userId): int
    {
        return (int)$this->where('user_id', $userId)->where('is_read', 0)->countAllResults();
    }

    public function getNotificationsForUser(int $userId, int $limit = 5, bool $onlyUnread = true): array
    {
        $builder = $this->where('user_id', $userId);
        if ($onlyUnread) {
            $builder = $builder->where('is_read', 0);
        }
        return $builder->orderBy('created_at', 'DESC')->findAll($limit);
    }

    public function markAsRead(int $notificationId): bool
    {
        return (bool)$this->update($notificationId, ['is_read' => 1]);
    }

    public function createNotification(int $userId, string $message): bool
    {
        return (bool)$this->insert([
            'user_id' => $userId,
            'message' => $message,
            'is_read' => 0,
            'created_at' => date('Y-m-d H:i:s')
        ]);
    }
}
