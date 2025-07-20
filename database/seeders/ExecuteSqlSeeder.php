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
        $this->executeSqlFile(database_path('sql/data_departamentos.sql'));
        $this->executeSqlFile(database_path('sql/data_municipios.sql'));
        $this->executeSqlFile(database_path('sql/data_paises.sql'));
        //$this->executeSqlFile(database_path('sql/data_profesion.sql'));
        //$this->executeSqlFile(database_path('sql/data_mensaje_bienvenida.sql'));
        //$this->executeSqlFile(database_path('sql/data_tipo_documentos.sql'));
        //$this->executeSqlFile(database_path('sql/data_barrios.sql'));
        //$this->executeSqlFile(database_path('sql/data_razas.sql'));
        //$this->executeSqlFile(database_path('sql/data_departamentos.sql'));
        //$this->executeSqlFile(database_path('sql/data_ciudades.sql'));
        //$this->executeSqlFile(database_path('sql/data_sectores.sql'));
        //$this->executeSqlFile(database_path('sql/data_tipos_empresas.sql'));
        //$this->executeSqlFile(database_path('sql/data_empresa.sql'));
        /*  $this->executeSqlFile(database_path('sql/municipios.sql'));
         $this->executeSqlFile(database_path('sql/paises.sql'));
         $this->executeSqlFile(database_path('sql/departamentos.sql'));
         $this->executeSqlFile(database_path('sql/festivos.sql'));
         $this->executeSqlFile(database_path('sql/tipo_entidad.sql'));
         $this->executeSqlFile(database_path('sql/tipo_bancos.sql'));
         $this->executeSqlFile(database_path('sql/data_term.sql'));
         $this->executeSqlFile(database_path('sql/data-habeas.sql'));
         $this->executeSqlFile(database_path('sql/token.sql'));
         $this->executeSqlFile(database_path('sql/data_tipo_contrato.sql'));

         $this->executeSqlFile(database_path('sql/data_tipo_cuenta.sql'));
         $this->executeSqlFile(database_path('sql/data_tipo_jornada.sql'));
         $this->executeSqlFile(database_path('sql/patch_anexo.sql'));
         $this->executeSqlFile(database_path('sql/patch_contrato.sql'));
         $this->executeSqlFile(database_path('sql/patch_firma.sql'));
         $this->executeSqlFile(database_path('sql/messages.sql'));
         $this->executeSqlFile(database_path('sql/modulos.sql'));
         $this->executeSqlFile(database_path('sql/tipos_messages.sql'));
         $this->executeSqlFile(database_path('sql/patch_perfil.sql'));
         $this->executeSqlFile(database_path('sql/p_q_r_d_s.sql'));
         $this->executeSqlFile(database_path('sql/genero.sql')); */
    }

    /**
     * Execute an SQL file.
     *
     * @param  string  $filePath
     * @return void
     */
    private function executeSqlFile($filePath)
    {
        if (File::exists($filePath)) {
            $sql = File::get($filePath);
            DB::unprepared($sql);
            $this->command->info("SQL file {$filePath} executed successfully.");
        } else {
            $this->command->error("SQL file {$filePath} not found.");
        }
    }
}
