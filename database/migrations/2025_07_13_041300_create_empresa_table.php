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
        Schema::create('empresa', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_legal');
            $table->string('nit')->unique();
            $table->string('representante_legal');
            $table->string('telefono');
            $table->string('email')->unique();
            $table->string('direccion');
            $table->unsignedBigInteger('pais_id');
            $table->unsignedBigInteger('departamento_id');
            $table->unsignedBigInteger('municipio_id');
            $table->text('mision')->nullable();
            $table->text('vision')->nullable();
            $table->text('descripcion')->nullable();
            $table->date('fecha_creacion')->nullable();
            $table->timestamps();

            $table->foreign('pais_id')->references('id')->on('paises')->onDelete('restrict');
            $table->foreign('departamento_id')->references('id')->on('departamentos')->onDelete('restrict');
            $table->foreign('municipio_id')->references('id')->on('municipios')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('empresa');
    }
};
