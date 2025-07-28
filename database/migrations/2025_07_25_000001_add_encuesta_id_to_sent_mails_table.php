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
        Schema::table('sent_mails', function (Blueprint $table) {
            $table->unsignedBigInteger('encuesta_id')->nullable()->after('sent_by');
            $table->foreign('encuesta_id')->references('id')->on('encuestas')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sent_mails', function (Blueprint $table) {
            $table->dropForeign(['encuesta_id']);
            $table->dropColumn('encuesta_id');
        });
    }
};
