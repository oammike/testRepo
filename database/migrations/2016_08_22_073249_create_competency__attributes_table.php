<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompetencyAttributesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         if(Schema::hasTable('competency__Attribute')) return;
        Schema::create('competency__Attribute', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('competency_id')->unsigned();
            $table->foreign('competency_id')->references('id')->on('competency')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('attribute_id')->unsigned();
            $table->foreign('attribute_id')->references('id')->on('attribute')->onUpdate('cascade')->onDelete('cascade');

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
        Schema::drop('competency__Attribute');
    }
}
