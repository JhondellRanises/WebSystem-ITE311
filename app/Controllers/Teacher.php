<?php

namespace App\Controllers;

class Teacher extends BaseController
{
    public function dashboard()
    {
        // ✅ Ensure only logged in teachers can access
        if (!session()->get('logged_in') || session()->get('user_role') !== 'teacher') {
            return redirect()->to('/login')->with('error', 'Access denied. Please log in as a teacher.');
        }

        return view('teacher/dashboard');
    }
}
