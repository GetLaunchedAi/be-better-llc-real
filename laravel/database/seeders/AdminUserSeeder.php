<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the initial admin user.
     *
     * Override defaults via environment variables:
     *   ADMIN_NAME, ADMIN_EMAIL, ADMIN_PASSWORD
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL', 'admin@bebetterbsbl.com');

        $user = User::updateOrCreate(
            ['email' => $email],
            [
                'name' => env('ADMIN_NAME', 'Admin'),
                'password' => Hash::make(env('ADMIN_PASSWORD', 'BeBetter2025!')),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        $this->command->info("Admin user ready: {$user->email} (role: {$user->role})");
    }
}

