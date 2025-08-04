<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class DiagnosticarTablasMigracion extends Command
{
    protected $signature = 'diagnosticar:tablas-migracion';
    protected $description = 'Diagnosticar las tablas necesarias para la migración de configuración de envíos';

    public function handle()
    {
        $this->info('🔍 DIAGNÓSTICO DE TABLAS PARA MIGRACIÓN');
        $this->info('========================================');

        // Verificar tabla empresas
        $this->info('📋 Verificando tabla empresas...');
        if (Schema::hasTable('empresas')) {
            $this->info('✅ Tabla empresas existe');

            // Verificar columnas de empresas
            $columnasEmpresas = Schema::getColumnListing('empresas');
            $this->table(['Columnas de empresas'], array_map(function($col) { return [$col]; }, $columnasEmpresas));

            // Verificar si tiene columna id
            if (in_array('id', $columnasEmpresas)) {
                $this->info('✅ Columna id existe en empresas');

                // Verificar tipo de datos de id
                $tipoId = DB::select("SHOW COLUMNS FROM empresas WHERE Field = 'id'")[0];
                $this->info("📊 Tipo de datos de id: {$tipoId->Type}");

                // Verificar si es primary key
                if ($tipoId->Key === 'PRI') {
                    $this->info('✅ Columna id es Primary Key');
                } else {
                    $this->warn('⚠️  Columna id NO es Primary Key');
                }
            } else {
                $this->error('❌ Columna id NO existe en empresas');
            }

            // Contar registros
            $registrosEmpresas = DB::table('empresas')->count();
            $this->info("📊 Registros en empresas: {$registrosEmpresas}");

        } else {
            $this->error('❌ Tabla empresas NO existe');
        }

        $this->info('');

        // Verificar tabla encuestas
        $this->info('📋 Verificando tabla encuestas...');
        if (Schema::hasTable('encuestas')) {
            $this->info('✅ Tabla encuestas existe');

            // Verificar columnas de encuestas
            $columnasEncuestas = Schema::getColumnListing('encuestas');
            $this->table(['Columnas de encuestas'], array_map(function($col) { return [$col]; }, $columnasEncuestas));

            // Verificar si tiene columna id
            if (in_array('id', $columnasEncuestas)) {
                $this->info('✅ Columna id existe en encuestas');

                // Verificar tipo de datos de id
                $tipoId = DB::select("SHOW COLUMNS FROM encuestas WHERE Field = 'id'")[0];
                $this->info("📊 Tipo de datos de id: {$tipoId->Type}");

                // Verificar si es primary key
                if ($tipoId->Key === 'PRI') {
                    $this->info('✅ Columna id es Primary Key');
                } else {
                    $this->warn('⚠️  Columna id NO es Primary Key');
                }
            } else {
                $this->error('❌ Columna id NO existe en encuestas');
            }

            // Verificar si tiene columna empresa_id
            if (in_array('empresa_id', $columnasEncuestas)) {
                $this->info('✅ Columna empresa_id existe en encuestas');

                // Verificar tipo de datos de empresa_id
                $tipoEmpresaId = DB::select("SHOW COLUMNS FROM encuestas WHERE Field = 'empresa_id'")[0];
                $this->info("📊 Tipo de datos de empresa_id: {$tipoEmpresaId->Type}");
            } else {
                $this->error('❌ Columna empresa_id NO existe en encuestas');
            }

            // Contar registros
            $registrosEncuestas = DB::table('encuestas')->count();
            $this->info("📊 Registros en encuestas: {$registrosEncuestas}");

        } else {
            $this->error('❌ Tabla encuestas NO existe');
        }

        $this->info('');

        // Verificar tabla configuracion_envios
        $this->info('📋 Verificando tabla configuracion_envios...');
        if (Schema::hasTable('configuracion_envios')) {
            $this->info('✅ Tabla configuracion_envios ya existe');

            // Verificar columnas
            $columnasConfiguracion = Schema::getColumnListing('configuracion_envios');
            $this->table(['Columnas de configuracion_envios'], array_map(function($col) { return [$col]; }, $columnasConfiguracion));

            // Contar registros
            $registrosConfiguracion = DB::table('configuracion_envios')->count();
            $this->info("📊 Registros en configuracion_envios: {$registrosConfiguracion}");

        } else {
            $this->info('ℹ️  Tabla configuracion_envios NO existe (esto es normal)');
        }

        $this->info('');

        // Verificar foreign keys existentes
        $this->info('🔗 Verificando foreign keys existentes...');
        $foreignKeys = DB::select("
            SELECT
                TABLE_NAME,
                COLUMN_NAME,
                CONSTRAINT_NAME,
                REFERENCED_TABLE_NAME,
                REFERENCED_COLUMN_NAME
            FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
            AND REFERENCED_TABLE_NAME IS NOT NULL
            ORDER BY TABLE_NAME, COLUMN_NAME
        ");

        if (!empty($foreignKeys)) {
            $fkInfo = [];
            foreach ($foreignKeys as $fk) {
                $fkInfo[] = [
                    $fk->TABLE_NAME,
                    $fk->COLUMN_NAME,
                    $fk->REFERENCED_TABLE_NAME,
                    $fk->REFERENCED_COLUMN_NAME
                ];
            }
            $this->table(['Tabla', 'Columna', 'Tabla Referenciada', 'Columna Referenciada'], $fkInfo);
        } else {
            $this->info('ℹ️  No se encontraron foreign keys en la base de datos');
        }

        $this->info('');

        // Recomendaciones
        $this->info('💡 RECOMENDACIONES:');

        if (!Schema::hasTable('empresas')) {
            $this->error('❌ Crear tabla empresas primero');
        }

        if (!Schema::hasTable('encuestas')) {
            $this->error('❌ Crear tabla encuestas primero');
        }

        if (Schema::hasTable('empresas') && Schema::hasTable('encuestas')) {
            $this->info('✅ Las tablas necesarias existen');
            $this->info('✅ Puedes ejecutar la migración de configuracion_envios');
        }

        return 0;
    }
}
