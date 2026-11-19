<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name' => 'أ.د / هارون العفيف',
                'email' => 'admin@ibbuniv.edu.ye',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'د / مدير الشؤون الأكاديمية',
                'email' => 'academic@ibbuniv.edu.ye',
                'password' => Hash::make('password'),
            ],
            [
                'name' => 'أ / مختص الأرشفة والتوثيق',
                'email' => 'archivist@ibbuniv.edu.ye',
                'password' => Hash::make('password'),
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }
    }
}
