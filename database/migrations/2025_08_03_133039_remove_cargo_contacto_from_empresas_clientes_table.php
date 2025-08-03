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
        Schema::table('empresas_clientes', function (Blueprint $table) {
            $table->dropColumn('cargo_contacto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empresas_clientes', function (Blueprint $table) {
            $table->string('cargo_contacto')->after('contacto');
        });
    }
};
