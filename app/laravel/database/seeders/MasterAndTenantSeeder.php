<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MasterAndTenantSeeder extends Seeder
{
    public function run(): void
    {
        // Master user
        $master = User::firstOrCreate(
            ['email' => 'master@example.com'],
            [
                'name' => 'Master User',
                'password' => Hash::make('password'),
                'global_role' => 'MASTER',
            ]
        );

        // Default tenant
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'demo'],
            [
                'name' => 'Demo Tenant',
                'domain' => null,
                'status' => 'active',
            ]
        );

        // Admin user for the tenant
        $admin = User::firstOrCreate(
            ['email' => 'admin@demo.test'],
            [
                'name' => 'Admin Demo',
                'password' => Hash::make('password'),
                'global_role' => 'USER',
            ]
        );

        // Attach roles
        $tenant->users()->syncWithoutDetaching([$admin->id => ['role' => 'ADMIN']]);
    }
}

