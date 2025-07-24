<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSlugHabilitadaToEncuestasTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('encuestas', function (Blueprint $table) {
            $table->string('slug')->unique()->nullable()->after('id'); // link único
            $table->boolean('habilitada')->default(true)->after('slug'); // estado público
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('encuestas', function (Blueprint $table) {
            $table->dropColumn(['slug', 'habilitada']);
        });
    }
}