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
        // ============================================================================
        // TABLA ENCUESTAS - ESTRUCTURA COMPLETA Y OPTIMIZADA
        // ============================================================================
        Schema::create('encuestas', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique()->nullable(); // Link único para acceso público
            $table->boolean('habilitada')->default(true); // Estado público de la encuesta
            $table->string('titulo');
            $table->unsignedBigInteger('empresa_id');
            $table->integer('numero_encuestas')->default(0);

            // Campos de seguimiento
            $table->integer('encuestas_enviadas')->default(0);
            $table->integer('encuestas_respondidas')->default(0);
            $table->integer('encuestas_pendientes')->default(0);

            // Campos de fecha (optimizados como DATE)
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();

            // Campos de envío por correo
            $table->boolean('enviar_por_correo')->default(false);
            $table->text('plantilla_correo')->nullable();
            $table->string('asunto_correo')->nullable();
            $table->boolean('envio_masivo_activado')->default(false);

            // Estado y validación
            $table->enum('estado', ['borrador', 'en_progreso', 'enviada', 'pausada', 'completada', 'publicada'])->default('borrador');
            $table->boolean('validacion_completada')->default(false);
            $table->text('errores_validacion')->nullable();

            // Campos para enlaces dinámicos
            $table->string('token_acceso')->nullable();
            $table->datetime('token_expiracion')->nullable();

            // Relaciones
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            // Índices para optimización
            $table->foreign('empresa_id')->references('id')->on('empresa')->onDelete('cascade');
            $table->index(['estado', 'habilitada']);
            $table->index(['fecha_inicio', 'fecha_fin']);
            $table->index('slug');
        });

        // ============================================================================
        // TABLA PREGUNTAS - ESTRUCTURA COMPLETA CON TODOS LOS TIPOS
        // ============================================================================
        Schema::create('preguntas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('encuesta_id');
            $table->string('texto');
            $table->text('descripcion')->nullable(); // Descripción adicional de la pregunta
            $table->string('placeholder')->nullable(); // Texto de placeholder

            // Tipo de pregunta con todos los tipos disponibles
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
            ]);

            $table->integer('orden')->default(1);
            $table->boolean('obligatoria')->default(true);

            // Campos para validación de texto
            $table->integer('min_caracteres')->nullable();
            $table->integer('max_caracteres')->nullable();

            // Campos para escalas
            $table->integer('escala_min')->nullable();
            $table->integer('escala_max')->nullable();
            $table->string('escala_etiqueta_min', 100)->nullable();
            $table->string('escala_etiqueta_max', 100)->nullable();

            // Campos para archivos
            $table->string('tipos_archivo_permitidos', 255)->nullable();
            $table->integer('tamano_max_archivo')->nullable();

            // Campos para ubicación
            $table->decimal('latitud_default', 10, 8)->nullable();
            $table->decimal('longitud_default', 11, 8)->nullable();
            $table->integer('zoom_default')->nullable();

            // Campos para lógica condicional
            $table->json('condiciones_mostrar')->nullable();
            $table->json('logica_salto')->nullable();

            // Campos para cuadrículas
            $table->json('opciones_filas')->nullable();
            $table->json('opciones_columnas')->nullable();

            $table->timestamps();

            // Relaciones e índices
            $table->foreign('encuesta_id')->references('id')->on('encuestas')->onDelete('cascade');
            $table->index(['encuesta_id', 'orden']);
            $table->index('tipo');
        });

        // ============================================================================
        // TABLA RESPUESTAS - OPCIONES PARA PREGUNTAS DE SELECCIÓN
        // ============================================================================
        Schema::create('respuestas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pregunta_id');
            $table->string('texto');
            $table->integer('orden')->default(1);
            $table->timestamps();

            $table->foreign('pregunta_id')->references('id')->on('preguntas')->onDelete('cascade');
            $table->index(['pregunta_id', 'orden']);
        });

        // ============================================================================
        // TABLA LÓGICAS - CONFIGURACIÓN DE SALTOS CONDICIONALES
        // ============================================================================
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

            $table->index(['pregunta_id', 'respuesta_id']);
        });

        // ============================================================================
        // TABLA RESPUESTAS_USUARIO - RESPUESTAS DE LOS USUARIOS FINALES
        // ============================================================================
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

        // ============================================================================
        // TABLA BLOQUES_ENVIO - CONTROL DE ENVÍO MASIVO DE CORREOS
        // ============================================================================
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

        // ============================================================================
        // TABLA TOKENS_ENCUESTA - ENLACES DINÁMICOS PARA ACCESO
        // ============================================================================
        Schema::create('tokens_encuesta', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('encuesta_id');
            $table->string('email_destinatario');
            $table->string('token_acceso', 64)->unique();
            $table->datetime('fecha_expiracion');
            $table->boolean('usado')->default(false);
            $table->datetime('fecha_uso')->nullable();
            $table->string('ip_acceso')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();

            $table->foreign('encuesta_id')->references('id')->on('encuestas')->onDelete('cascade');
            $table->index(['encuesta_id', 'email_destinatario']);
            $table->index(['token_acceso']);
            $table->index(['fecha_expiracion']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Eliminar en orden inverso para respetar las foreign keys
        Schema::dropIfExists('tokens_encuesta');
        Schema::dropIfExists('bloques_envio');
        Schema::dropIfExists('respuestas_usuario');
        Schema::dropIfExists('logicas');
        Schema::dropIfExists('respuestas');
        Schema::dropIfExists('preguntas');
        Schema::dropIfExists('encuestas');
    }
};
