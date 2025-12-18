<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class NtsAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'Nawab Tech Solutions Admin',
            'email' => 'ntsadmin@wireless.com',
            'password' => Hash::make('ntsadmin@wireless'),
            'role' => User::NTS_ADMINISTRATOR_ROLE,
            'is_active' => true,
            'is_admin' => true,
        ]);
    }
}
