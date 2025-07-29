<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('preguntas', function (Blueprint $table) {
            // Agregar columnas faltantes
            $table->text('descripcion')->nullable()->after('texto');
            $table->string('placeholder')->nullable()->after('descripcion');

            // Cambiar el enum de tipo para incluir todos los tipos necesarios
            $table->enum('tipo', [
                'respuesta_corta',
                'parrafo',
                'seleccion_unica',
                'casillas_verificacion',
                'lista_desplegable',
                'escala_lineal',
                'cuadricula_opcion_multiple',
                'cuadricula_casillas',
                'fecha',
                'hora',
                'carga_archivos',
                'ubicacion_mapa',
                'logica_condicional'
            ])->change();

            // Agregar campos para validación de texto
            $table->integer('min_caracteres')->nullable()->after('obligatoria');
            $table->integer('max_caracteres')->nullable()->after('min_caracteres');

            // Agregar campos para escalas
            $table->integer('escala_min')->nullable()->after('max_caracteres');
            $table->integer('escala_max')->nullable()->after('escala_min');
            $table->string('escala_etiqueta_min', 100)->nullable()->after('escala_max');
            $table->string('escala_etiqueta_max', 100)->nullable()->after('escala_etiqueta_min');

            // Agregar campos para archivos
            $table->string('tipos_archivo_permitidos', 255)->nullable()->after('escala_etiqueta_max');
            $table->integer('tamano_max_archivo')->nullable()->after('tipos_archivo_permitidos');

            // Agregar campos para ubicación
            $table->decimal('latitud_default', 10, 8)->nullable()->after('tamano_max_archivo');
            $table->decimal('longitud_default', 11, 8)->nullable()->after('latitud_default');
            $table->integer('zoom_default')->nullable()->after('longitud_default');

            // Agregar campos para lógica condicional
            $table->json('condiciones_mostrar')->nullable()->after('zoom_default');
            $table->json('logica_salto')->nullable()->after('condiciones_mostrar');

            // Agregar campos para cuadrículas
            $table->json('opciones_filas')->nullable()->after('logica_salto');
            $table->json('opciones_columnas')->nullable()->after('opciones_filas');
        });
    }

    public function down(): void
    {
        Schema::table('preguntas', function (Blueprint $table) {
            // Revertir cambios
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
                'latitud_default',
                'longitud_default',
                'zoom_default',
                'condiciones_mostrar',
                'logica_salto',
                'opciones_filas',
                'opciones_columnas'
            ]);

            // Revertir enum
            $table->enum('tipo', ['texto', 'seleccion_unica', 'seleccion_multiple', 'numero', 'fecha'])->change();
        });
    }
};
