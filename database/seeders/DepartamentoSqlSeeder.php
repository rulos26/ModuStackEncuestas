<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ExecuteSqlSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Ruta al archivo SQL
        $sqlFilePath = database_path('sql/sql/departamentos.sql');

        // Verificar si el archivo existe
        if (File::exists($sqlFilePath)) {
            // Leer el contenido del archivo
            $sqlContent = File::get($sqlFilePath);

            // Ejecutar el contenido SQL
            DB::unprepared($sqlContent);

            $this->command->info('Archivo SQL ejecutado con éxito.');
        } else {
            $this->command->error('El archivo SQL no existe.');
        }
    }
}
