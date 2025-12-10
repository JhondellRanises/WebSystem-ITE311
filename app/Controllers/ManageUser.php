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

    /**
     * Check if the given user ID is the currently logged-in user
     */
    private function isCurrentUser($userId): bool
    {
        return (int)$userId === (int)session()->get('user_id');
    }

    /**
     * Check if the given user ID belongs to an admin account
     */
    private function isAdminUser($userId): bool
    {
        $model = new UserModel();
        return $model->isAdmin($userId);
    }

    /**
     * Display a listing of all users (including deleted)
     * Deleted users will show a Restore button instead of Delete
     */
    public function index()
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $model = new UserModel();
        $q = trim((string)$this->request->getGet('q'));
        
        // Get all users including soft deleted
        $query = $model->withDeleted();
        
        // Build search query
        if ($q !== '') {
            $query = $query->groupStart()->like('name', $q)->orLike('email', $q)->groupEnd();
        }
        
        // Get all users (active and deleted) ordered by created date
        $users = $query->orderBy('created_at', 'DESC')->paginate(10);

        return view('admin/ManageUser', [
            'users' => $users,
            'pager' => $model->pager,
            'q' => $q,
        ]);
    }

    /**
     * Show the form for creating a new user (handled by modal in view)
     */
    public function create()
    {
        if ($resp = $this->requireAdmin()) return $resp;

        if ($this->request->getMethod() === 'GET') {
            return redirect()->to('/admin/manage-users');
        }

        // Validation rules
        $rules = [
            'name' => 'required|min_length[3]|max_length[255]|regex_match[/^[a-zA-Z0-9\s\-\'\.]+$/]',
            'email' => 'required|valid_email|regex_match[/^[a-zA-Z0-9]+(?:\.?[a-zA-Z0-9]+)*@gmail\.com$/]|is_unique[users.email]',
            'password' => 'required|min_length[6]',
            'confirm_password' => 'required|matches[password]',
            'role' => 'required|in_list[admin,teacher,student]',
        ];
        
        // Custom error messages
        $errors = [
            'name' => [
                'regex_match' => 'The name field cannot contain special characters. Only letters, numbers, spaces, hyphens, apostrophes, and periods are allowed.'
            ],
            'email' => [
                'regex_match' => 'Please use a valid Gmail address without special characters (letters, numbers, and periods only).'
            ]
        ];
        
        if (!$this->validate($rules, $errors)) {
            return redirect()->to('/admin/manage-users')
                ->withInput()
                ->with('errors', $this->validator->getErrors());
        }

        $model = new UserModel();
        
        // Check if email exists (including soft deleted)
        $existingUser = $model->withDeleted()->where('email', $this->request->getPost('email'))->first();
        if ($existingUser) {
            return redirect()->to('/admin/manage-users')
                ->withInput()
                ->with('errors', ['email' => 'Email is already taken.']);
        }

        $model->createAccount([
            'name' => $this->request->getPost('name'),
            'email' => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
            'role' => strtolower($this->request->getPost('role')),
        ]);

        return redirect()->to('/admin/manage-users')->with('success', 'User created successfully.');
    }

    /**
     * Store a newly created user (alias for create)
     */
    public function store()
    {
        return $this->create();
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit($id)
    {
        if ($resp = $this->requireAdmin()) return $resp;

        // RULE 2: Prevent logged-in admin from editing their own account
        if ($this->isCurrentUser($id)) {
            return redirect()->to('/admin/manage-users')->with('error', 'You cannot edit your own account.');
        }

        $model = new UserModel();
        // Find user including soft deleted
        $user = $model->withDeleted()->find($id);
        
        if (!$user) {
            return redirect()->to('/admin/manage-users')->with('error', 'User not found.');
        }

        // Check if user is deleted (handle both array and object)
        $isDeleted = false;
        if (is_array($user)) {
            $isDeleted = !empty($user['deleted_at']);
        } else {
            $isDeleted = !empty($user->deleted_at);
        }

        // If user is soft deleted, cannot edit
        if ($isDeleted) {
            return redirect()->to('/admin/manage-users')->with('error', 'Cannot edit deleted user. Please restore it first.');
        }

        if ($this->request->getMethod() === 'GET') {
            return redirect()->to('/admin/manage-users?edit=' . (int)$id);
        }

        // Validation rules
        $rules = [
            'name' => 'required|min_length[3]|max_length[255]|regex_match[/^[a-zA-Z0-9\s\-\'\.]+$/]',
            'email' => 'required|valid_email|regex_match[/^[a-zA-Z0-9]+(?:\.?[a-zA-Z0-9]+)*@gmail\.com$/]',
            'role' => 'required|in_list[admin,teacher,student]',
        ];
        
        $passwordInput = (string) $this->request->getPost('password');
        if (strlen(trim($passwordInput)) > 0) {
            $rules['password'] = 'required|min_length[6]';
            $rules['confirm_password'] = 'required|matches[password]';
        }
        
        // Custom error messages
        $errors = [
            'name' => [
                'regex_match' => 'The name field cannot contain special characters. Only letters, numbers, spaces, hyphens, apostrophes, and periods are allowed.'
            ],
            'email' => [
                'regex_match' => 'Please use a valid Gmail address without special characters (letters, numbers, and periods only).'
            ]
        ];
        
        if (!$this->validate($rules, $errors)) {
            return redirect()->to('/admin/manage-users?edit=' . (int)$id)
                             ->withInput()
                             ->with('errors', $this->validator->getErrors());
        }

        $email = $this->request->getPost('email');
        
        // Check if email exists (excluding current user and soft deleted)
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

    /**
     * Update the specified user (alias for edit)
     */
    public function update($id)
    {
        return $this->edit($id);
    }

    /**
     * Soft delete the specified user
     */
    public function delete($id)
    {
        if ($resp = $this->requireAdmin()) return $resp;

        // Prevent self-deletion
        if ($this->isCurrentUser($id)) {
            return redirect()->back()->with('error', 'You cannot delete your own account.');
        }

        // RULE 1: Prevent deleting admin accounts
        if ($this->isAdminUser($id)) {
            return redirect()->back()->with('error', 'Admin accounts cannot be deleted.');
        }

        $model = new UserModel();
        $user = $model->find($id);
        
        if (!$user) {
            return redirect()->to('/admin/manage-users')->with('error', 'User not found.');
        }

        // Perform soft delete (sets deleted_at timestamp)
        $model->delete($id);
        
        return redirect()->to('/admin/manage-users')->with('success', 'User deleted successfully. Click Restore to reactivate.');
    }


    /**
     * Restore a soft deleted user
     * Updates the database to set deleted_at to NULL, moving the user back to active list
     */
    public function restore($id)
    {
        if ($resp = $this->requireAdmin()) return $resp;

        $id = (int) $id;
        
        if ($id <= 0) {
            return redirect()->to('/admin/manage-users')->with('error', 'Invalid user ID.');
        }

        $model = new UserModel();
        
        // Find user including soft deleted
        $user = $model->withDeleted()->find($id);
        
        if (!$user) {
            return redirect()->to('/admin/manage-users')->with('error', 'User not found.');
        }

        // Get user name for success message (handle both array and object)
        $userName = '';
        if (is_array($user)) {
            $userName = $user['name'] ?? 'User';
            $isDeleted = !empty($user['deleted_at']);
            $userRole = $user['role'] ?? '';
        } else {
            $userName = $user->name ?? 'User';
            $isDeleted = !empty($user->deleted_at);
            $userRole = $user->role ?? '';
        }

        // Check if user is actually deleted
        if (!$isDeleted) {
            return redirect()->to('/admin/manage-users')->with('error', 'User is not deleted.');
        }

        // RULE 1: Prevent restoring admin accounts (extra safeguard)
        if ($userRole === 'admin') {
            return redirect()->to('/admin/manage-users')->with('error', 'Admin accounts cannot be restored.');
        }

        // Restore using model's restore method
        // This updates deleted_at to NULL, which moves the user back to active list
        $restored = $model->restoreUser($id);
        
        if (!$restored) {
            return redirect()->to('/admin/manage-users')->with('error', 'Failed to restore user. Please try again.');
        }
        
        // After successful restore, redirect back to users list
        return redirect()->to('/admin/manage-users')->with('success', 'User "' . $userName . '" has been restored successfully.');
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
