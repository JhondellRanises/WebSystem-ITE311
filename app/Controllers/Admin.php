<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Admin extends Controller
{
    public function dashboard()
    {
        if (!session()->get('logged_in') || session()->get('user_role') !== 'admin') {
            return redirect()->to('/login')->with('error', 'Access denied. Admin only.');
        }

        // Load the Admin Dashboard view
        return view('admin/dashboard');
    }
}
