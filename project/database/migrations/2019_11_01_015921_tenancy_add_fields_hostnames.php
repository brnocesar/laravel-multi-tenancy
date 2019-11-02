<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class TenancyAddFieldsHostnames extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('hostnames', function (Blueprint $table) {
            $table->string('responsavel')->nullable();
            $table->string('fantasia')->nullable();
            $table->string('cidade')->nullable();
            $table->string('razao_social')->nullable();
            $table->string('cnpj')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('hostnames', function (Blueprint $table) {
            //
        });
    }
}
