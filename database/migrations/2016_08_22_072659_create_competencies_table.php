<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompetenciesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         if(Schema::hasTable('competency')) return;
        Schema::create('competency', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('percentage');
            $table->decimal('agentPercentage',5,2)->nullable();
            $table->text('definitions');
            $table->boolean('acrossTheBoard');
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
        Schema::drop('competency');
    }
}
