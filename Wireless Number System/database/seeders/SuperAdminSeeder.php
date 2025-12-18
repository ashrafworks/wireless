<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@wireless.com',
            'password' => Hash::make('superadmin@wireless'),
            'role' => User::SUPER_ADMINISTRATOR_ROLE,
            'is_active' => true,
            'is_admin' => true,
        ]);
    }
}
