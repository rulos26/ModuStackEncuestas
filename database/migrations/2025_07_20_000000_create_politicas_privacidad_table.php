<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('politicas_privacidad', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->longText('contenido');
            $table->boolean('estado')->default(false);
            $table->string('version')->nullable();
            $table->date('fecha_publicacion')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('politicas_privacidad');
    }
};
