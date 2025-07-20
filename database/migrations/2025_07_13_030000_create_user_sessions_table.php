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
        Schema::create('user_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('session_id')->unique(); // ID único de la sesión
            $table->string('ip_address', 45); // Soporte para IPv6
            $table->text('user_agent'); // Navegador y sistema operativo
            $table->string('current_route')->nullable(); // Ruta actual del usuario
            $table->string('current_page')->nullable(); // Nombre de la página actual
            $table->timestamp('last_activity'); // Última actividad
            $table->boolean('is_active')->default(true); // Estado activo/inactivo
            $table->timestamp('login_time'); // Hora de inicio de sesión
            $table->timestamp('logout_time')->nullable(); // Hora de cierre de sesión
            $table->json('additional_data')->nullable(); // Datos adicionales (ubicación, etc.)
            $table->timestamps();

            // Índices para optimizar consultas
            $table->index(['user_id', 'is_active']);
            $table->index('last_activity');
            $table->index('session_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_sessions');
    }
};
