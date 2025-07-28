<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('encuestas', function (Blueprint $table) {
            // Cambiar fecha_inicio de datetime a date
            $table->date('fecha_inicio')->nullable()->change();

            // Cambiar fecha_fin de datetime a date
            $table->date('fecha_fin')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('encuestas', function (Blueprint $table) {
            // Revertir fecha_inicio de date a datetime
            $table->datetime('fecha_inicio')->nullable()->change();

            // Revertir fecha_fin de date a datetime
            $table->datetime('fecha_fin')->nullable()->change();
        });
    }
};
