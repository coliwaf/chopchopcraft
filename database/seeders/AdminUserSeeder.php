<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Only seed if no admin exists yet
        if (User::where('email', 'admin@chopchopcraft.test')->exists()) return;

        User::create([
            'name'     => 'Admin',
            'email'    => 'admin@chopchopcraft.test',
            'password' => Hash::make('password'),
        ]);
    }
}
