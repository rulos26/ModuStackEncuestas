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
        Schema::table('encuestas', function (Blueprint $table) {
            // Actualizar el enum de estado para incluir los nuevos estados
            $table->enum('estado', [
                'borrador',      // Estado inicial de la encuesta
                'en_progreso',   // Encuesta en proceso de configuración
                'enviada',       // Encuesta enviada a destinatarios
                'pausada',       // Encuesta pausada temporalmente
                'completada',    // Encuesta completada por todos los destinatarios
                'publicada'      // Encuesta publicada públicamente
            ])->default('borrador')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('encuestas', function (Blueprint $table) {
            // Revertir a los estados originales
            $table->enum('estado', [
                'borrador',
                'enviada',
                'publicada'
            ])->default('borrador')->change();
        });
    }
};
