<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $email = 'admin@noamanahmed.com';
        
        // Generate a secure random password
        $password = Str::random(32);
        
        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'password' => bcrypt($password),
                'is_verified' => 1,
                'status' => \App\Enums\UserStatusEnum::ACTIVE,
                'type' => \App\Enums\UserTypeEnum::ADMIN,
            ]
        );

        $this->command->info("Admin user created/updated:");
        $this->command->info("  Email: {$email}");
        $this->command->info("  Password: {$password}");
        $this->command->warn("Save this password! It will not be shown again.");
    }
}
