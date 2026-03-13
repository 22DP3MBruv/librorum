<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the admin user.
     */
    public function run(): void
    {
        User::create([
            'username' => 'admin',
            'email' => 'admin@librorum.icu',
            'password_hash' => Hash::make('admin'),
            'password' => Hash::make('admin2026'), // For compatibility with the User model
            'role' => 'admin',
            'name' => 'admin',
            'join_date' => now(),
            'profile_visibility' => true,
            'reading_progress_visibility' => true,
            'activity_visibility' => true,
            'allow_follows' => true,
            'require_follow_approval' => false,
            'is_flagged' => false,
        ]);
    }
}
