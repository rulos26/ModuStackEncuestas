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
        Schema::create('analisis_encuesta', function (Blueprint $table) {
            $table->id();
            $table->foreignId('encuesta_id')->constrained('encuestas')->onDelete('cascade');
            $table->foreignId('pregunta_id')->constrained('preguntas')->onDelete('cascade');
            $table->string('tipo_grafico'); // barras, pastel, lineas, dispersion, etc.
            $table->text('analisis_ia'); // Análisis textual de la IA
            $table->json('configuracion_grafico')->nullable(); // Configuración específica del gráfico
            $table->json('datos_procesados')->nullable(); // Datos procesados para el gráfico
            $table->string('estado')->default('pendiente'); // pendiente, procesando, completado, error
            $table->text('error_mensaje')->nullable(); // Mensaje de error si falla
            $table->timestamp('fecha_analisis')->nullable();
            $table->timestamps();

            // Índices para optimizar consultas
            $table->index(['encuesta_id', 'pregunta_id']);
            $table->index('estado');
            $table->index('fecha_analisis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('analisis_encuesta');
    }
};
