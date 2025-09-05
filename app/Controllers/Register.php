<?php

namespace App\Controllers;

use App\Models\UserModel;

class Register extends BaseController
{
    public function index()
    {
        return view('register'); // yung register form view mo
    }

    public function store()
    {
        $userModel = new UserModel();

        $data = [
            'name'     => $this->request->getPost('name'),
            'email'    => $this->request->getPost('email'),
            'password' => password_hash($this->request->getPost('password'), PASSWORD_DEFAULT),
            'role'     => $this->request->getPost('role'), // student/instructor/admin
        ];

        $userModel->save($data);

        return redirect()->to('/login')->with('success', 'Registration successful! Please login.');
    }
}
