<?php

namespace App\Controllers;
use App\Models\AnnouncementModel;

class Announcement extends BaseController
{
    public function index()
    {
        $model = new AnnouncementModel();
        $data['announcements'] = $model->orderBy('created_at', 'DESC')->findAll();

        return view('announcements', $data);
    }
}
