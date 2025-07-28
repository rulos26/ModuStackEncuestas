<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

    public function down(): void
    {
        Schema::dropIfExists('tokens_encuesta');
    }
};
