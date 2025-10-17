<?php namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class AnnouncementsSeeder extends Seeder
{
    public function run()
    {
        $data = [
            [
                'title'      => 'Welcome to the Student Portal',
                'content'    => 'Welcome students! The portal is now live. Check announcements regularly for updates.',
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
            ],
            [
                'title'      => 'Library Schedule Update',
                'content'    => 'The library will be open from 8AM to 8PM starting next week.',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
            ],
        ];

        $this->db->table('announcements')->insertBatch($data);
    }
}
