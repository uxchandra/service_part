<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            [
                'name' => 'superadmin',
                'description' => 'Administrator dengan akses penuh'
            ],
            [
                'name' => 'admin scanner',
                'description' => 'User yang melakukan proses posting dan pulling barang'
            ],
            [
                'name' => 'admin isp',
                'description' => 'User yang melakukan proses packing barang'
            ],
        ];

        foreach ($roles as $role) {
            Role::create($role);
        }
    }
}
