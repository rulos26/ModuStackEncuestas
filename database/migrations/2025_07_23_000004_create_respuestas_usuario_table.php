<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRespuestasUsuarioTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('respuestas_usuario', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('encuesta_id');
            $table->unsignedBigInteger('pregunta_id');
            $table->unsignedBigInteger('respuesta_id')->nullable();
            $table->text('respuesta_texto')->nullable(); // Para respuestas de texto libre
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->foreign('encuesta_id')->references('id')->on('encuestas')->onDelete('cascade');
            $table->foreign('pregunta_id')->references('id')->on('preguntas')->onDelete('cascade');
            $table->foreign('respuesta_id')->references('id')->on('respuestas')->onDelete('cascade');

            // Índices para mejorar el rendimiento
            $table->index(['encuesta_id', 'pregunta_id']);
            $table->index('ip_address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('respuestas_usuario');
    }
}
