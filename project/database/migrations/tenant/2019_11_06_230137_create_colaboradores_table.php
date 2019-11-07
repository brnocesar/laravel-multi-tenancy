<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateColaboradoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('colaboradores', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('matricula')->unique();
            $table->string('nome');
            $table->date('admissao')->default( date('Y-m-d H:i:s') );
            $table->string('cracha')->nullable();
            $table->string('cpf')->nullable();
            $table->date('nascimento')->nullable();
            $table->string('centro_custo')->nullable();
            $table->boolean('status')->default(true);
            $table->boolean('requerente')->default(false);
            $table->unsignedBigInteger('cargo_id');
            $table->foreign('cargo_id')->references('id')->on('cargos');

            $table->softDeletes();
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
        Schema::dropIfExists('colaboradores');
    }
}
