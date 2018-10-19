<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEvalSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         if(Schema::hasTable('evalSetting')) return;
         Schema::create('evalSetting', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('description');
            $table->boolean('enableSelfEval');
            $table->boolean('enableCoaching');
            $table->integer('startMonth')->unsigned()->nullable();
            $table->integer('startDate')->unsigned()->nullable();
            $table->integer('endMonth')->unsigned()->nullable();
            $table->integer('endDate')->unsigned()->nullable();
            

            $table->integer('evalType_id')->unsigned();
            $table->foreign('evalType_id')->references('id')->on('evalType')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::drop('evalSetting');
    }
}
