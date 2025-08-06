<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCarpetasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('carpetas', function (Blueprint $table) {
            $table->id('id_carpeta');
            $table->foreignId('id_padre')->nullable()->references('id_carpeta')->on('carpetas');
            $table->foreignId('id_area')->references('id_area')->on('areas');
            // $table->foreignId('id_padre')->nullable()->constrained('carpetas', 'id_carpeta');;
            // $table->foreignId('id_area')->constrained('areas', 'id_area');
            $table->string('nombre');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('carpetas');
    }
}
