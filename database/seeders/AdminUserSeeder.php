<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Creates (or updates) the admin account used to sign in at /admin/login.
     *
     * Credentials come from ADMIN_EMAIL / ADMIN_PASSWORD in .env so production
     * never has to ship the development default below.
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@voidlayer.in');
        $password = env('ADMIN_PASSWORD', 'password');

        User::updateOrCreate(
            ['email' => $email],
            [
                'name' => env('ADMIN_NAME', 'Void Layer Admin'),
                'password' => Hash::make($password),
                'email_verified_at' => now(),
                'role' => User::ROLE_ADMIN,
            ]
        );

        $this->command?->info("Admin ready: {$email}");
    }
}
