<?php

namespace App\Controllers;

use CodeIgniter\Controller;

class Dashboard extends Controller
{
    public function index()
    {
        $session = session();

        if (! $session->get('isLoggedIn')) {
            return redirect()->to('/login');
        }

        return view('dashboard');
    }
}
