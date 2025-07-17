<?php

namespace Database\Seeders;

use App\Models\AdminDashboard\Token;
use Illuminate\Database\Seeder;

class TokenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        /* User::Create([
            'name' => 'Juan Carlos Diaz Lara',
            'email' => 'rulos26@gmail.com',
            'password'=>bcrypt('12345678'),
        ]); */

        Token::Create([
            'usuario' => '1',
            'nombre_del_usuario' => 'juan carlos diaz lara',
            'rol' => '1',
            'nombre_del_rol' => 'SuperAdministrador',
            'password' => '',
            'vencimiento' => '2023-12-20',
            'dias' => '0',
        ]);

        Token::Create([
            'usuario' => '2',
            'nombre_del_usuario' => 'juan manuel diaz lara',
            'rol' => '2',
            'nombre_del_rol' => 'Administrador',
            'password' => '',
            'vencimiento' => '2023-12-20',
            'dias' => '0',
        ]);
    }
}
