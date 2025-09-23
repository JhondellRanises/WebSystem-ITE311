<?php

namespace App\Controllers;

class Student extends BaseController
{
    public function dashboard()
    {
        if (!session()->get('logged_in') || strtolower(session()->get('user_role')) !== 'student') {
            return redirect()->to('/login')->with('error', 'Unauthorized access.');
        }

        $data = [
            'title'     => 'Student Dashboard',
            'user_name' => session()->get('user_name'),
            'user_role' => session()->get('user_role'),
        ];

        return view('dashboard/student', $data);
    }
}
