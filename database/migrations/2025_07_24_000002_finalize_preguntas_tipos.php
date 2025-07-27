<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class FinalizePreguntasTipos extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('preguntas', function (Blueprint $table) {
            // Cambiar de string a enum con todos los nuevos tipos
            $table->enum('tipo', [
                'respuesta_corta',           // Texto breve (nombre, correo, cargo, etc.)
                'parrafo',                   // Respuesta de texto largo o desarrollo
                'seleccion_unica',           // Escoger solo una opción entre varias (radio)
                'casillas_verificacion',     // Elegir una o varias opciones (checkbox)
                'lista_desplegable',         // Seleccionar una opción desde un menú desplegable
                'escala_lineal',             // Calificación en rango numérico
                'cuadricula_opcion_multiple', // Tabla con varias filas y columnas de opciones
                'cuadricula_casillas',       // Tabla donde puedes marcar múltiples opciones en cada fila
                'fecha',                     // Selector de fecha
                'hora',                      // Selector de hora
                'carga_archivos',            // Permitir al usuario subir un archivo
                'ubicacion_mapa',            // Selección geográfica
                'logica_condicional'         // Mostrar preguntas según respuestas anteriores
            ])->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('preguntas', function (Blueprint $table) {
            // Revertir a string
            $table->string('tipo')->change();
        });
    }
}
