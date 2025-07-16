<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // Usuario administrador solicitado por el usuario
        User::create([
            'name' => 'juan carlos diaz lara',
            'email' => 'rulos26@gmail.com',
            'password' => bcrypt('0382646740Ju*'),
            'role' => 'admin',
        ]);
    }
}
