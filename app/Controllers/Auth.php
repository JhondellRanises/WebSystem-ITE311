<?php

namespace App\Controllers;

use App\Models\UserModel;

class Auth extends BaseController
{
    public function register()
    {
        if ($this->request->getMethod() === 'post') {
            $rules = [
                'name'     => 'required|min_length[3]',
                'email'    => 'required|valid_email|is_unique[users.email]',
                'password' => 'required|min_length[6]',
            ];

            if (!$this->validate($rules)) {
                return view('auth/register', [
                    'validation' => $this->validator
                ]);
            }

            $userModel = new UserModel();
            $userModel->save([
                'name'     => $this->request->getPost('name'),
                'email'    => $this->request->getPost('email'),
                'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
                'role'     => 'user',
            ]);

            return redirect()->to('/login')->with('success', 'Registration successful! Please login.');
        }

        return view('auth/register');
    }

    public function login()
    {
        if ($this->request->getMethod() === 'post') {
            $userModel = new UserModel();
            $user = $userModel->where('email', $this->request->getPost('email'))->first();

            if ($user && password_verify($this->request->getPost('password'), $user['password'])) {
                session()->set([
                    'user_id' => $user['id'],
                    'name'    => $user['name'],
                    'email'   => $user['email'],
                    'role'    => $user['role'],
                    'isLoggedIn' => true,
                ]);
                return redirect()->to('/dashboard');
            } else {
                return redirect()->back()->with('error', 'Invalid email or password');
            }
        }

        return view('auth/login');
    }

    public function dashboard()
    {
        if (!session()->get('isLoggedIn')) {
            return redirect()->to('/login')->with('error', 'Please login first.');
        }

        return view('auth/dashboard', [
            'name' => session()->get('name'),
        ]);
    }

     public function logout()
    {
        // Clear all session data
        session()->remove(['user_id', 'name', 'email', 'role', 'isLoggedIn']);
        
        // Destroy the entire session
        session()->destroy();
        
        // Clear any flash messages
        session()->setFlashdata('success', 'You have been successfully logged out.');
        
        // Redirect to home page
        return redirect()->to('/')->with('success', 'You have been successfully logged out.');
    }
}