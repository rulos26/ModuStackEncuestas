<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCamposAdicionalesPreguntas extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('preguntas', function (Blueprint $table) {
            // Campos para validación y configuración
            $table->text('descripcion')->nullable()->after('texto'); // Descripción adicional de la pregunta
            $table->string('placeholder')->nullable()->after('descripcion'); // Texto de placeholder
            $table->integer('min_caracteres')->nullable()->after('placeholder'); // Mínimo de caracteres
            $table->integer('max_caracteres')->nullable()->after('min_caracteres'); // Máximo de caracteres

            // Campos para escalas
            $table->integer('escala_min')->nullable()->after('max_caracteres'); // Valor mínimo de escala
            $table->integer('escala_max')->nullable()->after('escala_min'); // Valor máximo de escala
            $table->string('escala_etiqueta_min')->nullable()->after('escala_max'); // Etiqueta para valor mínimo
            $table->string('escala_etiqueta_max')->nullable()->after('escala_etiqueta_min'); // Etiqueta para valor máximo

            // Campos para archivos
            $table->string('tipos_archivo_permitidos')->nullable()->after('escala_etiqueta_max'); // Tipos de archivo permitidos
            $table->integer('tamano_max_archivo')->nullable()->after('tipos_archivo_permitidos'); // Tamaño máximo en MB

            // Campos para lógica condicional
            $table->json('condiciones_mostrar')->nullable()->after('tamano_max_archivo'); // Condiciones para mostrar la pregunta
            $table->json('logica_salto')->nullable()->after('condiciones_mostrar'); // Lógica de salto

            // Campos para cuadrículas
            $table->json('opciones_filas')->nullable()->after('logica_salto'); // Opciones para las filas de la cuadrícula
            $table->json('opciones_columnas')->nullable()->after('opciones_filas'); // Opciones para las columnas de la cuadrícula

            // Campos para ubicación
            $table->decimal('latitud_default', 10, 8)->nullable()->after('opciones_columnas'); // Latitud por defecto
            $table->decimal('longitud_default', 11, 8)->nullable()->after('latitud_default'); // Longitud por defecto
            $table->integer('zoom_default')->nullable()->after('longitud_default'); // Zoom por defecto del mapa
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('preguntas', function (Blueprint $table) {
            $table->dropColumn([
                'descripcion',
                'placeholder',
                'min_caracteres',
                'max_caracteres',
                'escala_min',
                'escala_max',
                'escala_etiqueta_min',
                'escala_etiqueta_max',
                'tipos_archivo_permitidos',
                'tamano_max_archivo',
                'condiciones_mostrar',
                'logica_salto',
                'opciones_filas',
                'opciones_columnas',
                'latitud_default',
                'longitud_default',
                'zoom_default'
            ]);
        });
    }
}
