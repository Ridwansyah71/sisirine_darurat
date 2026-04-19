<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Admin
        User::create([
            'name'      => 'Admin Sisirine',
            'email'     => 'admin@sisirine.id',
            'password'  => Hash::make('admin123'),
            'role'      => 'admin',
            'is_active' => true,
        ]);

        // User biasa
        User::create([
            'name'      => 'Ridwan',
            'email'     => 'ridwan@sisirine.id',
            'password'  => Hash::make('ridwan123'),
            'role'      => 'user',
            'is_active' => true,
        ]);
    }
}