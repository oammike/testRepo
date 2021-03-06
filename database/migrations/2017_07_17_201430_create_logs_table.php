<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('logs')) return;
        Schema::create('logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('biometrics_id')->unsigned();
            $table->foreign('biometrics_id')->references('id')->on('biometrics')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->time('logTime');
            $table->integer('logType_id')->unsigned();
            $table->foreign('logType_id')->references('id')->on('logType')->onUpdate('cascade')->onDelete('cascade');

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
        Schema::drop('logs');
    }
}
