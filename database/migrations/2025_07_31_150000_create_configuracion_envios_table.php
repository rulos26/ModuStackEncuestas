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
        Schema::create('configuracion_envios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('encuesta_id');
            $table->string('nombre_remitente');
            $table->string('correo_remitente');
            $table->string('asunto');
            $table->text('cuerpo_mensaje');
            $table->enum('tipo_envio', ['automatico', 'manual', 'programado']);
            $table->string('plantilla')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete('cascade');
            $table->foreign('encuesta_id')->references('id')->on('encuestas')->onDelete('cascade');

            // Índice único para evitar duplicados
            $table->unique(['empresa_id', 'encuesta_id'], 'unique_empresa_encuesta');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracion_envios');
    }
};
