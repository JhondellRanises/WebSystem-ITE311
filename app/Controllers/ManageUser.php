<?php

namespace App\Controllers;

use App\Models\UserModel;

class ManageUser extends BaseController
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

    public function index()
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $model = new UserModel();
        $q = trim((string)$this->request->getGet('q'));
        if ($q !== '') {
            $model = $model->groupStart()->like('name', $q)->orLike('email', $q)->groupEnd();
        }
        $users = $model->orderBy('created_at', 'DESC')->paginate(10);

        return view('admin/ManageUser', [
            'users' => $users,
            'pager' => $model->pager,
            'q' => $q,
        ]);
    }

    public function create()
    {
        if ($resp = $this->requireAdmin()) return $resp;

        if ($this->request->getMethod() === 'GET') {
            return redirect()->to('/admin/manage-users');
        }

        $rules = [
            'name' => 'required|min_length[3]|max_length[255]',
            'email' => 'required|valid_email|is_unique[users.email]',
            'password' => 'required|min_length[6]',
            'confirm_password' => 'required|matches[password]',
            'role' => 'required|in_list[admin,teacher,student]',
        ];
        if (!$this->validate($rules)) {
            return redirect()->to('/admin/manage-users')->withInput()->with('errors', $this->validator->getErrors());
        }

        $model = new UserModel();
        $model->createAccount([
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
            'role' => strtolower($this->request->getPost('role')),
        ]);

        return redirect()->to('/admin/manage-users')->with('success', 'User created successfully.');
    }

    public function edit($id)
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $model = new UserModel();
        $user = $model->find($id);
        if (!$user) {
            return redirect()->to('/admin/manage-users')->with('error', 'User not found.');
        }

        if ($this->request->getMethod() === 'GET') {
            return redirect()->to('/admin/manage-users?edit=' . (int)$id);
        }

        $rules = [
            'name' => 'required|min_length[3]|max_length[255]',
            'email' => 'required|valid_email',
            'role' => 'required|in_list[admin,teacher,student]',
        ];
        $passwordInput = (string) $this->request->getPost('password');
        if (strlen(trim($passwordInput)) > 0) {
            $rules['password'] = 'required|min_length[6]';
            $rules['confirm_password'] = 'required|matches[password]';
        }
        if (!$this->validate($rules)) {
            return redirect()->to('/admin/manage-users?edit=' . (int)$id)
                             ->withInput()
                             ->with('errors', $this->validator->getErrors());
        }

        $email = $this->request->getPost('email');
        $existing = $model->where('email', $email)->where('id !=', $id)->first();
        if ($existing) {
            return redirect()->to('/admin/manage-users?edit=' . (int)$id)
                             ->withInput()
                             ->with('errors', ['email' => 'Email is already taken by another user.']);
        }

        $updateData = [
            'name' => $this->request->getPost('name'),
            'email' => $email,
            'role' => strtolower($this->request->getPost('role')),
        ];
        if (strlen(trim($passwordInput)) > 0) {
            $updateData['password'] = password_hash($passwordInput, PASSWORD_DEFAULT);
        }

        $model->update($id, $updateData);

        return redirect()->to('/admin/manage-users')->with('success', 'User updated successfully.');
    }

    public function delete($id)
    {
        if ($resp = $this->requireAdmin()) return $resp;

        if ((int)$id === (int)session()->get('user_id')) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        $model = new UserModel();
        $user = $model->find($id);
        if (!$user) {
            return redirect()->to('/admin/manage-users')->with('error', 'User not found.');
        }
        $model->delete($id);
        return redirect()->to('/admin/manage-users')->with('success', 'User deleted successfully.');
    }

    // Removed reset() as password changes are handled via Edit modal

    public function show($id)
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $model = new UserModel();
        $user = $model->select('id,name,email,role')->find($id);
        if (!$user) {
            return $this->response->setStatusCode(404)->setJSON(['error' => 'Not found']);
        }

        return $this->response->setJSON([
            'user' => $user,
            'csrf' => csrf_hash(),
        ]);
    }
}
