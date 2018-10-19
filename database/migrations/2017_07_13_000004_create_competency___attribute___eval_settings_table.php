<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCompetencyAttributeEvalSettingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         if(Schema::hasTable('competency__Att__evalSetting')) return;
         Schema::create('competency__Att__evalSetting', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('competency__Attribute_id')->unsigned();
            $table->foreign('competency__Attribute_id')->references('id')->on('competency__Attribute')->onUpdate('cascade')->onDelete('cascade');
            $table->integer('evalSetting_id')->unsigned();
            $table->foreign('evalSetting_id')->references('id')->on('evalSetting')->onUpdate('cascade')->onDelete('cascade');
            
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
        Schema::drop('competency__Att__evalSetting');
    }
}
