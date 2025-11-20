<?php

namespace Database\Seeders;

use App\Models\Users;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Hapus data existing jika ada
        Users::truncate();

        $users = [
            [
                'name' => 'Super Admin',
                'email' => 'superadmin@tursinakebab.com',
                'password' => Hash::make('superadmin123'),
                'role' => 'superadmin',
                'status' => 'active'
            ],
            [
                'name' => 'Admin Tursina',
                'email' => 'admin@tursinakebab.com',
                'password' => Hash::make('admin123'),
                'role' => 'admin',
                'status' => 'active'
            ]
        ];

        foreach ($users as $user) {
            Users::create($user);
        }

        $this->command->info('Users berhasil ditambahkan!');
        $this->command->info('Email: admin@tursinakebab.com');
        $this->command->info('Password: admin123');
    }
}
