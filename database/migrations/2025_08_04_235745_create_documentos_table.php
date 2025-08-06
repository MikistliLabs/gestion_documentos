<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documentos', function (Blueprint $table) {
            $table->id('id_documento');
            $table->foreignId('id_carpeta')->constrained('carpetas', 'id_carpeta');
            $table->foreignId('id_tipo_documento')->constrained('tipos_archivos', 'id_tipo');
            // $table->foreignId('id_carpeta')->constrained('carpetas', 'id_carpeta');
            // $table->foreignId('id_tipo_documento')->constrained('tipos_archivos', 'id_tipo');
            $table->string('nombre');
            $table->string('archivo');
            $table->timestamp('fecha_creacion')->useCurrent();
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
        Schema::dropIfExists('documentos');
    }
}
