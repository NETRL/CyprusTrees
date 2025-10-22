<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::create([
            'first_name'              => 'Admin',
            'last_name'              => 'Admin',
            'email'             => 'admin@admin.com',
            'email_verified_at' => now(),
            'password'          => password_hash('password', PASSWORD_BCRYPT),
            'remember_token'    => Str::random(10),
            'phone'    => fake()->phoneNumber(),
        ]);

        $user = User::create([
            'first_name'              => 'Test',
            'last_name'              => 'User',
            'email'             => 'test@example.com',
            'email_verified_at' => now(),
            'password'          => password_hash('password', PASSWORD_BCRYPT),
            'remember_token'    => Str::random(10),
        ]);

        $admin->assignRole('admin');
        UserAddress::factory()->for($admin)->create();
    }
}
