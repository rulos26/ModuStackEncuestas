<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departamentos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->unsignedBigInteger('pais_id');
            $table->timestamps();

            $table->foreign('pais_id')->references('id')->on('paises')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departamentos');
    }
};
