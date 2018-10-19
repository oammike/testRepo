<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserCWSTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_cws', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('biometrics_id')->unsigned();
            $table->foreign('biometrics_id')->references('id')->on('biometrics')->onUpdate('cascade')->onDelete('cascade');

            $table->time('timeStart');
            $table->time('timeEnd');
            $table->time('timeStart_old');
            $table->time('timeEnd_old');

            $table->boolean('isRD');
            $table->boolean('isApproved')->nullable();

            $table->integer('approver')->unsigned();
            $table->foreign('approver')->references('id')->on('immediateHead_Campaigns')->onUpdate('cascade')->onDelete('cascade');

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
        Schema::drop('user_cws');
    }
}
