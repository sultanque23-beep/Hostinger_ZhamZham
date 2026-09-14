<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class RoleAndUserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Role
        $roleOwner  = Role::create(['name' => 'owner']);
        $roleKasir  = Role::create(['name' => 'kasir']);
        $roleGudang = Role::create(['name' => 'gudang']);

        // 2. Buat Akun Owner Utama (Otomatis Disetujui)
        $owner = User::create([
            'name'        => 'Owner ZhamZham',
            'email'       => 'owner@zhamzham.com',
            'password'    => Hash::make('password123'),
            'is_approved' => true,
        ]);

        $owner->assignRole($roleOwner);
    }
}