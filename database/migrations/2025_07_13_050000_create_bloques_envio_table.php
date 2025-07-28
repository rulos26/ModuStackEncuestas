<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bloques_envio', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('encuesta_id');
            $table->integer('numero_bloque');
            $table->integer('cantidad_correos');
            $table->enum('estado', ['pendiente', 'en_proceso', 'enviado', 'error'])->default('pendiente');
            $table->datetime('fecha_programada');
            $table->datetime('fecha_envio')->nullable();
            $table->integer('correos_enviados')->default(0);
            $table->integer('correos_fallidos')->default(0);
            $table->text('errores')->nullable();
            $table->timestamps();

            $table->foreign('encuesta_id')->references('id')->on('encuestas')->onDelete('cascade');
            $table->unique(['encuesta_id', 'numero_bloque']);
            $table->index(['encuesta_id', 'estado']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bloques_envio');
    }
};
