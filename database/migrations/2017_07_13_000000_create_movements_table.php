<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMovementsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         if(Schema::hasTable('movement')) return;
         Schema::create('movement', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade');

            // $table->integer('oldHead_id')->unsigned();
            // $table->foreign('oldHead_id')->references('id')->on('immediateHead')->onUpdate('cascade');

            // $table->integer('newHead_id')->unsigned();
            // $table->foreign('newHead_id')->references('id')->on('immediateHead')->onUpdate('cascade');

            $table->boolean('withinProgram');

            $table->dateTime('fromPeriod'); //if first instance of movement, get date of regularization
            $table->dateTime('effectivity');

            $table->boolean('isApproved');
            $table->boolean('isNotedFrom');
            $table->boolean('isNotedTo');
            $table->boolean('isDone');

            $table->dateTime('dateRequested');
            $table->integer('requestedBy')->unsigned();
            $table->foreign('requestedBy')->references('id')->on('immediateHead')->onUpdate('cascade');

            $table->integer('notedBy')->unsigned();
            $table->foreign('notedBy')->references('id')->on('users')->onUpdate('cascade');

            $table->integer('personnelChange_id')->unsigned();
            $table->foreign('personnelChange_id')->references('id')->on('personnelChange')->onUpdate('cascade')->onDelete('cascade');


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
        Schema::drop('movement');
    }
}
