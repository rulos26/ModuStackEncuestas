<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('encuestas', function (Blueprint $table) {
            // Eliminar el campo legacy tiempo_disponible
            $table->dropColumn('tiempo_disponible');
        });
    }

    public function down(): void
    {
        Schema::table('encuestas', function (Blueprint $table) {
            // Restaurar el campo en caso de rollback
            $table->dateTime('tiempo_disponible')->nullable()->after('numero_encuestas');
        });
    }
};
