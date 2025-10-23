<?php

namespace App\Controllers;

use App\Models\MaterialModel;
use CodeIgniter\Controller;

class Admin extends BaseController
{
    public function dashboard()
    {
        // ✅ Check if logged in
        if (!session()->get('logged_in')) {
            return redirect()->to('/login')->with('error', 'Please log in first.');
        }

        // ✅ Only admins can access
        if (session()->get('user_role') !== 'admin') {
            return redirect()->to('/announcements')
                             ->with('error', 'Access Denied: Insufficient Permissions.');
        }

        // ✅ Load uploaded materials from DB (optional table `materials`)
        $materialModel = new MaterialModel();

        $materials = $materialModel->orderBy('created_at', 'DESC')->findAll();

        // Dummy course ID (you can replace this with real logic later)
        $data = [
            'user_name' => session()->get('user_name'),
            'materials' => $materials,
            'course_id' => 1, // ✅ default value to prevent undefined variable error
        ];

        return view('admin/dashboard', $data);
    }
}
