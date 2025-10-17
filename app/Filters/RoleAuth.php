<?php namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class RoleAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // 🔒 If not logged in, redirect to login
        if (! $session->get('isLoggedIn')) {
            return redirect()->to(site_url('login'))->with('error', 'Please log in first.');
        }

        $role = $session->get('role') ?? '';
        $path = ltrim($request->getUri()->getPath(), '/'); // e.g. "admin/dashboard"

        // ✅ 1. Allow everyone to access announcements
        if ($path === 'announcements' || $path === 'announcements/index') {
            return null; // allow access
        }

        // ✅ 2. Admin: only allowed to access /admin/*
        if (strpos($path, 'admin') === 0) {
            if ($role !== 'admin') {
                return redirect()->to(site_url('announcements'))
                                 ->with('error', 'Access Denied: Insufficient Permissions');
            }
            return null;
        }

        // ✅ 3. Teacher: only allowed to access /teacher/*
        if (strpos($path, 'teacher') === 0) {
            if ($role !== 'teacher') {
                return redirect()->to(site_url('announcements'))
                                 ->with('error', 'Access Denied: Insufficient Permissions');
            }
            return null;
        }

        // ✅ 4. Student: only allowed to access /student/*
        if (strpos($path, 'student') === 0) {
            if ($role !== 'student') {
                return redirect()->to(site_url('announcements'))
                                 ->with('error', 'Access Denied: Insufficient Permissions');
            }
            return null;
        }

        // ✅ 5. If path is something else (like public page), allow access
        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // No action needed after
    }
}
