<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MejorarCamposEncuesta extends Migration
{
    public function up()
    {
        Schema::table('encuestas', function (Blueprint $table) {
            // Agregar campos de fecha de inicio y fin
            $table->datetime('fecha_inicio')->nullable()->after('tiempo_disponible');
            $table->datetime('fecha_fin')->nullable()->after('fecha_inicio');

            // Agregar campos para seguimiento
            $table->integer('encuestas_enviadas')->default(0)->after('numero_encuestas');
            $table->integer('encuestas_respondidas')->default(0)->after('encuestas_enviadas');
            $table->integer('encuestas_pendientes')->default(0)->after('encuestas_respondidas');

            // Agregar campos para envío masivo
            $table->text('plantilla_correo')->nullable()->after('enviar_por_correo');
            $table->string('asunto_correo')->nullable()->after('plantilla_correo');
            $table->boolean('envio_masivo_activado')->default(false)->after('asunto_correo');

            // Agregar campos para enlaces dinámicos
            $table->string('token_acceso')->nullable()->after('slug');
            $table->datetime('token_expiracion')->nullable()->after('token_acceso');

            // Agregar campos para validación de integridad
            $table->boolean('validacion_completada')->default(false)->after('habilitada');
            $table->text('errores_validacion')->nullable()->after('validacion_completada');
        });
    }

    public function down()
    {
        Schema::table('encuestas', function (Blueprint $table) {
            $table->dropColumn([
                'fecha_inicio',
                'fecha_fin',
                'encuestas_enviadas',
                'encuestas_respondidas',
                'encuestas_pendientes',
                'plantilla_correo',
                'asunto_correo',
                'envio_masivo_activado',
                'token_acceso',
                'token_expiracion',
                'validacion_completada',
                'errores_validacion'
            ]);
        });
    }
}
