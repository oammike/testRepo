<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEvalDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         if(Schema::hasTable('evalDetail')) return;
         Schema::create('evalDetail', function (Blueprint $table) {
            $table->increments('id');
            
            $table->integer('evalForm_id')->unsigned();
            $table->foreign('evalForm_id')->references('id')->on('evalForm')->onUpdate('cascade')->onDelete('cascade');
            
            $table->integer('competency__Attribute_id')->unsigned();
            $table->foreign('competency__Attribute_id')->references('id')->on('competency__Attribute')->onUpdate('cascade')->onDelete('cascade');
            
            $table->integer('ratingScale_id')->unsigned();
            $table->foreign('ratingScale_id')->references('id')->on('ratingScale')->onUpdate('cascade')->onDelete('cascade');
            
            $table->text('attributeValue');
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
        Schema::drop('evalDetail');
    }
}
