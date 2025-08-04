<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class VerificarTablaEmpresas extends Command
{
    protected $signature = 'verificar:tabla-empresas';
    protected $description = 'Verificar específicamente la tabla empresas_clientes';

    public function handle()
    {
        $this->info('🔍 VERIFICANDO TABLA EMPRESAS_CLIENTES');
        $this->info('=====================================');

        try {
            // Verificar si la tabla existe
            if (Schema::hasTable('empresas_clientes')) {
                $this->info('✅ Tabla empresas_clientes existe');

                // Mostrar todas las columnas
                $columnas = Schema::getColumnListing('empresas_clientes');
                $this->info('📋 Columnas de la tabla:');
                $this->table(['Columna'], array_map(function($col) { return [$col]; }, $columnas));

                // Verificar columna nombre específicamente
                if (in_array('nombre', $columnas)) {
                    $this->info('✅ Columna nombre existe');

                    // Verificar tipo de datos
                    $tipoNombre = DB::select("SHOW COLUMNS FROM empresas_clientes WHERE Field = 'nombre'")[0];
                    $this->info("📊 Tipo de datos de nombre: {$tipoNombre->Type}");

                    // Contar registros
                    $registros = DB::table('empresas_clientes')->count();
                    $this->info("📊 Total de registros: {$registros}");

                    // Mostrar algunos registros de ejemplo
                    if ($registros > 0) {
                        $this->info('📋 Primeros 5 registros:');
                        $ejemplos = DB::table('empresas_clientes')->select('id', 'nombre', 'nit')->limit(5)->get();
                        $datos = [];
                        foreach ($ejemplos as $empresa) {
                            $datos[] = [$empresa->id, $empresa->nombre, $empresa->nit];
                        }
                        $this->table(['ID', 'Nombre', 'NIT'], $datos);
                    }

                } else {
                    $this->error('❌ Columna nombre NO existe');
                    $this->info('💡 Columnas disponibles: ' . implode(', ', $columnas));
                }

            } else {
                $this->error('❌ Tabla empresas_clientes NO existe');

                // Mostrar todas las tablas disponibles
                $this->info('📋 Tablas disponibles en la base de datos:');
                $tablas = DB::select('SHOW TABLES');
                $nombresTablas = [];
                foreach ($tablas as $tabla) {
                    $nombresTablas[] = [array_values((array)$tabla)[0]];
                }
                $this->table(['Tabla'], $nombresTablas);
            }

        } catch (\Exception $e) {
            $this->error('❌ Error durante la verificación: ' . $e->getMessage());
            $this->error('📋 Stack trace: ' . $e->getTraceAsString());
            return 1;
        }

        $this->info('🎉 Verificación completada');
        return 0;
    }
}
