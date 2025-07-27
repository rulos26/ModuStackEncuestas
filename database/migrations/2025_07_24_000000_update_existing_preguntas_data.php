<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateExistingPreguntasData extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // Cambiar temporalmente a string para permitir nuevos valores
        Schema::table('preguntas', function (Blueprint $table) {
            $table->string('tipo')->change();
        });

        // Actualizar datos existentes
        DB::table('preguntas')->where('tipo', 'texto')->update(['tipo' => 'respuesta_corta']);
        DB::table('preguntas')->where('tipo', 'seleccion_multiple')->update(['tipo' => 'casillas_verificacion']);
        DB::table('preguntas')->where('tipo', 'numero')->update(['tipo' => 'escala_lineal']);
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Revertir los cambios
        DB::table('preguntas')->where('tipo', 'respuesta_corta')->update(['tipo' => 'texto']);
        DB::table('preguntas')->where('tipo', 'casillas_verificacion')->update(['tipo' => 'seleccion_multiple']);
        DB::table('preguntas')->where('tipo', 'escala_lineal')->update(['tipo' => 'numero']);

        // Volver a enum original
        Schema::table('preguntas', function (Blueprint $table) {
            $table->enum('tipo', ['texto', 'seleccion_unica', 'seleccion_multiple', 'numero', 'fecha'])->change();
        });
    }
}
