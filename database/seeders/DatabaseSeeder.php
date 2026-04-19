<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@sisirine.id'],
            [
                'name'      => 'Admin Sisirine',
                'password'  => Hash::make('admin123'),
                'role'      => 'admin',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'ridwan@sisirine.id'],
            [
                'name'      => 'Ridwan',
                'password'  => Hash::make('ridwan123'),
                'role'      => 'user',
                'is_active' => true,
            ]
        );
    }
}