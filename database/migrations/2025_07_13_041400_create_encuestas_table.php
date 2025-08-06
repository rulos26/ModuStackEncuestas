<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('encuestas', function (Blueprint $table) {
            $table->id();
            $table->string('titulo');
            $table->unsignedBigInteger('empresa_id');
            $table->integer('numero_encuestas')->default(0);
            $table->dateTime('tiempo_disponible')->nullable();
            $table->boolean('enviar_por_correo')->default(false);
            $table->enum('estado', ['borrador', 'enviada', 'publicada'])->default('borrador');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();

            $table->foreign('empresa_id')->references('id')->on('empresas_clientes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('encuestas');
    }
};
