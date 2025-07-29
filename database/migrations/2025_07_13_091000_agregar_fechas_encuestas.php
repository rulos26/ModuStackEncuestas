<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('encuestas', function (Blueprint $table) {
            // Verificar si las columnas existen antes de agregarlas
            if (!Schema::hasColumn('encuestas', 'fecha_inicio')) {
                $table->date('fecha_inicio')->nullable()->after('numero_encuestas');
            }

            if (!Schema::hasColumn('encuestas', 'fecha_fin')) {
                $table->date('fecha_fin')->nullable()->after('fecha_inicio');
            }
        });

        // Si las columnas ya existen pero son datetime, cambiarlas a date
        if (Schema::hasColumn('encuestas', 'fecha_inicio')) {
            Schema::table('encuestas', function (Blueprint $table) {
                $table->date('fecha_inicio')->nullable()->change();
            });
        }

        if (Schema::hasColumn('encuestas', 'fecha_fin')) {
            Schema::table('encuestas', function (Blueprint $table) {
                $table->date('fecha_fin')->nullable()->change();
            });
        }
    }

    public function down(): void
    {
        Schema::table('encuestas', function (Blueprint $table) {
            if (Schema::hasColumn('encuestas', 'fecha_inicio')) {
                $table->dropColumn('fecha_inicio');
            }
            if (Schema::hasColumn('encuestas', 'fecha_fin')) {
                $table->dropColumn('fecha_fin');
            }
        });
    }
};
