<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'name'              => 'Admin',
            'email'             => 'admin@admin.com',
            'email_verified_at' => now(),
            'password'          => password_hash('password', PASSWORD_BCRYPT),
            'remember_token'    => Str::random(10),
        ]);

        // $citizen = User::create([
        //     'name'              => 'citizen',
        //     'email'             => 'citizen@admin.com',
        //     'email_verified_at' => now(),
        //     'password'          => password_hash('password', PASSWORD_BCRYPT),
        //     'remember_token'    => Str::random(10),
        // ]);

        // $staff = User::create([
        //     'name'              => 'staff',
        //     'email'             => 'staff@admin.com',
        //     'email_verified_at' => now(),
        //     'password'          => password_hash('password', PASSWORD_BCRYPT),
        //     'remember_token'    => Str::random(10),
        // ]);

        $admin->assignRole('admin');
        // $citizen->assignRole('web');
        // $staff->assignRole('web');
    }
}
