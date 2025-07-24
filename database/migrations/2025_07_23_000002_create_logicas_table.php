<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLogicasTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('logicas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pregunta_id');     // Pregunta origen
            $table->unsignedBigInteger('respuesta_id');    // Respuesta que activa la lógica
            $table->unsignedBigInteger('siguiente_pregunta_id')->nullable(); // Destino
            $table->boolean('finalizar')->default(false);  // Si se termina la encuesta
            $table->timestamps();

            $table->foreign('pregunta_id')->references('id')->on('preguntas')->onDelete('cascade');
            $table->foreign('respuesta_id')->references('id')->on('respuestas')->onDelete('cascade');
            $table->foreign('siguiente_pregunta_id')->references('id')->on('preguntas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('logicas');
    }
}
