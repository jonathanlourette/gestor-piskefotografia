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
        $password = env('ADMIN_INITIAL_PASSWORD');

        if ($password === null || $password === 'password') {
            $this->command->error(
                'ADMIN_INITIAL_PASSWORD não definida ou ainda com valor padrão inseguro. '
                .'Defina uma senha forte no .env como ADMIN_INITIAL_PASSWORD antes de rodar o seeder.'
            );

            return;
        }

        User::firstOrCreate(
            ['email' => 'piskefotografia@gmail.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make($password),
                'is_admin' => true,
            ]
        );
    }
}
