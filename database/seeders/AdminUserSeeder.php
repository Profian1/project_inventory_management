<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Seed the default administrator account.
     *
     * Login: admin@balsheet.test / password
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@balsheet.test'],
            [
                'name' => 'Balsheet Admin',
                'password' => Hash::make('password'),
                'role' => User::ROLE_ADMIN,
                'email_verified_at' => now(),
            ]
        );
    }
}
