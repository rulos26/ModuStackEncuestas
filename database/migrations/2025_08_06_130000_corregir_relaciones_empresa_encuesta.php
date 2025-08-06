<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Eliminar las foreign keys incorrectas si existen
        try {
            Schema::table('encuestas', function (Blueprint $table) {
                $table->dropForeign(['empresa_id']);
            });
        } catch (Exception $e) {
            // La foreign key no existe, continuar
        }

        try {
            Schema::table('configuracion_envios', function (Blueprint $table) {
                $table->dropForeign(['empresa_id']);
            });
        } catch (Exception $e) {
            // La foreign key no existe, continuar
        }

        // 2. Verificar que la tabla empresas_clientes existe
        if (!Schema::hasTable('empresas_clientes')) {
            throw new Exception('La tabla empresas_clientes no existe. Ejecuta primero las migraciones de empresas_clientes.');
        }

        // 3. Crear las foreign keys correctas
        Schema::table('encuestas', function (Blueprint $table) {
            $table->foreign('empresa_id')->references('id')->on('empresas_clientes')->onDelete('cascade');
        });

        Schema::table('configuracion_envios', function (Blueprint $table) {
            $table->foreign('empresa_id')->references('id')->on('empresas_clientes')->onDelete('cascade');
        });

        // 4. Verificar y corregir datos inconsistentes
        $this->corregirDatosInconsistentes();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar las foreign keys correctas
        Schema::table('encuestas', function (Blueprint $table) {
            $table->dropForeign(['empresa_id']);
        });

        Schema::table('configuracion_envios', function (Blueprint $table) {
            $table->dropForeign(['empresa_id']);
        });

        // Restaurar las foreign keys incorrectas (solo para rollback)
        Schema::table('encuestas', function (Blueprint $table) {
            $table->foreign('empresa_id')->references('id')->on('empresa')->onDelete('cascade');
        });

        Schema::table('configuracion_envios', function (Blueprint $table) {
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
        });
    }

    /**
     * Corregir datos inconsistentes
     */
    private function corregirDatosInconsistentes(): void
    {
        // Verificar si hay encuestas con empresa_id que no existe en empresas_clientes
        $encuestasInconsistentes = DB::table('encuestas')
            ->leftJoin('empresas_clientes', 'encuestas.empresa_id', '=', 'empresas_clientes.id')
            ->whereNull('empresas_clientes.id')
            ->whereNotNull('encuestas.empresa_id')
            ->get();

        if ($encuestasInconsistentes->count() > 0) {
            echo "⚠️ Encontradas {$encuestasInconsistentes->count()} encuestas con empresa_id inconsistente\n";

            // Obtener el primer empresa_id válido
            $primerEmpresa = DB::table('empresas_clientes')->first();

            if ($primerEmpresa) {
                // Actualizar encuestas inconsistentes
                DB::table('encuestas')
                    ->leftJoin('empresas_clientes', 'encuestas.empresa_id', '=', 'empresas_clientes.id')
                    ->whereNull('empresas_clientes.id')
                    ->whereNotNull('encuestas.empresa_id')
                    ->update(['empresa_id' => $primerEmpresa->id]);

                echo "✅ Encuestas actualizadas con empresa_id válido\n";
            } else {
                echo "❌ No hay empresas_clientes disponibles para corregir las encuestas\n";
            }
        }

        // Verificar configuraciones de envío inconsistentes
        $configuracionesInconsistentes = DB::table('configuracion_envios')
            ->leftJoin('empresas_clientes', 'configuracion_envios.empresa_id', '=', 'empresas_clientes.id')
            ->whereNull('empresas_clientes.id')
            ->whereNotNull('configuracion_envios.empresa_id')
            ->get();

        if ($configuracionesInconsistentes->count() > 0) {
            echo "⚠️ Encontradas {$configuracionesInconsistentes->count()} configuraciones con empresa_id inconsistente\n";

            $primerEmpresa = DB::table('empresas_clientes')->first();

            if ($primerEmpresa) {
                // Actualizar configuraciones inconsistentes
                DB::table('configuracion_envios')
                    ->leftJoin('empresas_clientes', 'configuracion_envios.empresa_id', '=', 'empresas_clientes.id')
                    ->whereNull('empresas_clientes.id')
                    ->whereNotNull('configuracion_envios.empresa_id')
                    ->update(['empresa_id' => $primerEmpresa->id]);

                echo "✅ Configuraciones actualizadas con empresa_id válido\n";
            } else {
                echo "❌ No hay empresas_clientes disponibles para corregir las configuraciones\n";
            }
        }
    }
};
