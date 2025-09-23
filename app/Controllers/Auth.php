<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    protected $helpers = ['form', 'url'];

    // 🔹 Handle Login
    public function login()
    {
        // If already logged in, redirect based on role
        if (session()->get('logged_in')) {
            return $this->redirectByRole(session()->get('user_role'));
        }

        // GET request → show login form
        if ($this->request->getMethod() === 'GET') {
            return view('auth/login');
        }

        // POST request → process login
        if ($this->request->getMethod() === 'POST') {
            if (!$this->validate([
                'email'    => 'required|valid_email',
                'password' => 'required|min_length[6]'
            ])) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $userModel = new UserModel();
            $user = $userModel->findUserByEmail($this->request->getPost('email'));

            // Check user existence and password
            if (!$user || !password_verify($this->request->getPost('password'), $user['password'])) {
                return redirect()->back()->withInput()->with('error', 'Invalid email or password.');
            }

            // Normalize role (always lowercase)
            $role = strtolower($user['role']);

            // Set session
            session()->set([
                'user_id'   => $user['id'],
                'user_name' => $user['name'],
                'user_role' => $role,
                'logged_in' => true,
            ]);

            // ✅ Redirect based on role
            return $this->redirectByRole($role);
        }
    }

    // 🔹 Handle Register
    public function register()
    {
        if ($this->request->getMethod() === 'GET') {
            return view('auth/register');
        }

        if ($this->request->getMethod() === 'POST') {
            if (!$this->validate([
                'name'              => 'required|min_length[3]|max_length[255]',
                'email'             => 'required|valid_email|is_unique[users.email]',
                'password'          => 'required|min_length[6]',
                'confirm_password'  => 'required|matches[password]',
                // ✅ Validation updated: accepts lowercase roles
                'role'              => 'required|in_list[admin,teacher,student]',
            ])) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $userModel = new UserModel();
            $userModel->createAccount([
                'name'     => $this->request->getPost('name'),
                'email'    => $this->request->getPost('email'),
                'password' => $this->request->getPost('password'),
                'role'     => strtolower($this->request->getPost('role')), // ✅ always lowercase
            ]);

            return redirect()->to('/login')->with('success', 'Account created successfully. You can now login.');
        }
    }

    // 🔹 Logout
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login')->with('success', 'You have been logged out.');
    }

    // 🔹 Utility: Role-based redirection
    private function redirectByRole($role)
    {
        switch (strtolower($role)) {
            case 'admin':
                return redirect()->to('/admin/dashboard');
            case 'teacher':
                return redirect()->to('/teacher/dashboard');
            case 'student':
                return redirect()->to('/student/dashboard');
            default:
                return redirect()->to('/login')->with('error', 'Invalid role.');
        }
    }
}
