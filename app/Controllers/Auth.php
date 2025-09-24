<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    protected $helpers = ['form', 'url'];

    // 🔹 Login
    public function login()
    {
        if (session()->get('logged_in')) {
            return redirect()->to('/dashboard');
        }

        if ($this->request->getMethod() === 'GET') {
            return view('auth/login');
        }

        if ($this->request->getMethod() === 'POST') {
            if (!$this->validate([
                'email'    => 'required|valid_email',
                'password' => 'required|min_length[6]'
            ])) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $userModel = new UserModel();
            $user = $userModel->findUserByEmail($this->request->getPost('email'));

            if (!$user || !password_verify($this->request->getPost('password'), $user['password'])) {
                return redirect()->back()->withInput()->with('error', 'Invalid email or password.');
            }

            $role = strtolower($user['role']);

            session()->set([
                'user_id'   => $user['id'],
                'user_name' => $user['name'],
                'user_role' => $role,
                'logged_in' => true,
            ]);

            return redirect()->to('/dashboard');
        }
    }

    // 🔹 Register
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
                'role'              => 'required|in_list[admin,teacher,student]',
            ])) {
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            $userModel = new UserModel();
            $userModel->createAccount([
                'name'     => $this->request->getPost('name'),
                'email'    => $this->request->getPost('email'),
                'password' => $this->request->getPost('password'),
                'role'     => strtolower($this->request->getPost('role')),
            ]);

            return redirect()->to('/login')->with('success', 'Account created successfully. You can now login.');
        }
    }

    // 🔹 Unified Dashboard
    public function dashboard()
    {
        $session = session();

        if (!$session->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Please log in first.');
        }

        $data = [
            'user_name' => $session->get('user_name'),
            'user_role' => $session->get('user_role'),
        ];

        return view('auth/dashboard', $data);
    }

    // 🔹 Logout
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/login')->with('success', 'You have been logged out.');
    }
}
