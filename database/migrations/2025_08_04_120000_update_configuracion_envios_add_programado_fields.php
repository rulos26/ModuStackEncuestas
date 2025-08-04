<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('configuracion_envios', function (Blueprint $table) {
            // Eliminar la opción 'automatico' del enum tipo_envio
            $table->enum('tipo_envio', ['manual', 'programado'])->change();

            // Agregar nuevos campos para envío programado
            $table->date('fecha_envio')->nullable()->after('plantilla');
            $table->time('hora_envio')->nullable()->after('fecha_envio');
            $table->enum('tipo_destinatario', ['empleados', 'clientes', 'proveedores', 'personalizado'])->nullable()->after('hora_envio');
            $table->integer('numero_bloques')->default(1)->after('tipo_destinatario');
            $table->string('correo_prueba')->nullable()->after('numero_bloques');
            $table->boolean('modo_prueba')->default(false)->after('correo_prueba');
            $table->enum('estado_programacion', ['pendiente', 'en_proceso', 'completado', 'cancelado'])->default('pendiente')->after('modo_prueba');

            // Índices para mejorar rendimiento
            $table->index(['tipo_envio', 'estado_programacion']);
            $table->index('fecha_envio');
            $table->index('hora_envio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('configuracion_envios', function (Blueprint $table) {
            // Revertir enum a incluir 'automatico'
            $table->enum('tipo_envio', ['automatico', 'manual', 'programado'])->change();

            // Eliminar campos agregados
            $table->dropColumn([
                'fecha_envio',
                'hora_envio',
                'tipo_destinatario',
                'numero_bloques',
                'correo_prueba',
                'modo_prueba',
                'estado_programacion'
            ]);

            // Eliminar índices
            $table->dropIndex(['tipo_envio', 'estado_programacion']);
            $table->dropIndex(['fecha_envio']);
            $table->dropIndex(['hora_envio']);
        });
    }
};
