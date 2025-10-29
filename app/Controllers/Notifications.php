<?php
namespace App\Controllers;

use App\Models\NotificationModel;
use CodeIgniter\HTTP\ResponseInterface;

class Notifications extends BaseController
{
    public function get()
    {
        if (!session()->get('logged_in')) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
                ->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }
        $userId = (int) session()->get('user_id');
        $model = new NotificationModel();
        $count = $model->getUnreadCount($userId);
        $limit = (int) ($this->request->getGet('limit') ?? 50);
        if ($limit < 1) { $limit = 1; }
        if ($limit > 100) { $limit = 100; }
        $list = $model->getNotificationsForUser($userId, $limit);
        return $this->response->setJSON([
            'status' => 'success',
            'count' => (int)$count,
            'notifications' => $list,
            'csrf' => csrf_hash(),
        ]);
    }

    public function mark_as_read($id = null)
    {
        if (!session()->get('logged_in')) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
                ->setJSON(['status' => 'error', 'message' => 'Unauthorized']);
        }
        $id = $id !== null ? (int)$id : (int)($this->request->getPost('id') ?? $this->request->getGet('id') ?? 0);
        // Accept POST (preferred) and GET (fallback) for labs
        if (!in_array(strtolower($this->request->getMethod()), ['post','get'])) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_METHOD_NOT_ALLOWED)
                ->setJSON(['status' => 'error', 'message' => 'Invalid method']);
        }
        $userId = (int) session()->get('user_id');
        $model = new NotificationModel();
        $notif = $model->find($id);
        if (!$notif || (int)$notif['user_id'] !== $userId) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_FORBIDDEN)
                ->setJSON(['status' => 'error', 'message' => 'Forbidden']);
        }
        $ok = $model->markAsRead($id);
        return $this->response->setJSON([
            'status' => $ok ? 'success' : 'error',
            'csrf' => csrf_hash(),
        ]);
    }
}
