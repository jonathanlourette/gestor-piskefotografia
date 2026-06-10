<?php

declare(strict_types=1);

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

        User::firstOrCreate(
            ['email' => 'piskefotografia@gmail.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make("123456789@a"),
                'is_admin' => true,
            ]
        );
    }
}
