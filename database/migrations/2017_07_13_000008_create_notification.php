<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNotification extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         if(Schema::hasTable('notification')) return;
         Schema::create('notification', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('relatedModelID')->nullable();

            $table->integer('type')->unsigned();
            $table->foreign('type')->references('id')->on('notifType')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('from')->unsigned()->nullable();
            
            
            
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
        Schema::drop('notification');
    }
}
