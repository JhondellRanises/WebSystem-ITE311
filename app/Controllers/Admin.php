<?php

namespace App\Controllers;

class Admin extends BaseController
{
    public function dashboard()
    {
        // Authorization check
        if (!session()->get('logged_in') || strtolower(session()->get('user_role')) !== 'admin') {
            return redirect()->to('/login')->with('error', 'Unauthorized access.');
        }

        $data = [
            'title'     => 'Admin Dashboard',
            'user_name' => session()->get('user_name'),
            'user_role' => session()->get('user_role'),
        ];

        return view('dashboard/admin', $data);
    }
}
