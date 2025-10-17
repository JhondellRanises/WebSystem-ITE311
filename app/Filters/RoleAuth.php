<?php namespace App\Filters;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class RoleAuth implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();

        // if not logged in, redirect to login
        if (! $session->get('isLoggedIn')) {
            return redirect()->to(site_url('login'))->with('error', 'Please log in first.');
        }

        $role = $session->get('role') ?? '';

        // get requested path (no baseURL)
        $path = $request->getUri()->getPath(); // e.g., "admin/dashboard" or "teacher/dashboard"

        // normalize (remove leading slash)
        $path = ltrim($path, '/');

        // Admin: allowed for routes starting with admin
        if (strpos($path, 'admin') === 0) {
            if ($role !== 'admin') {
                return redirect()->to(site_url('announcements'))->with('error', 'Access Denied: Insufficient Permissions');
            }
            // allowed
            return null;
        }

        // Teacher: allowed for routes starting with teacher
        if (strpos($path, 'teacher') === 0) {
            if ($role !== 'teacher') {
                return redirect()->to(site_url('announcements'))->with('error', 'Access Denied: Insufficient Permissions');
            }
            return null;
        }

        // Student: allowed only /student/* and /announcements
        if (strpos($path, 'student') === 0) {
            if ($role !== 'student') {
                return redirect()->to(site_url('announcements'))->with('error', 'Access Denied: Insufficient Permissions');
            }
            return null;
        }

        // Announcements are allowed for all logged-in roles (explicit allow)
        if ($path === 'announcements' || $path === 'announcements/index') {
            return null;
        }

        // For any other protected prefix you may add rules here.
        // If not explicitly allowed, allow (or deny) depending on your policy:
        return null; // allow access to public pages
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // no-op
    }
}
