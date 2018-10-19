<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePerformanceSummariesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         if(Schema::hasTable('performanceSummary')) return;
         Schema::create('performanceSummary', function (Blueprint $table) {
            $table->increments('id');
            $table->text('value')->nullable();

            $table->integer('summary_id')->unsigned();
            $table->foreign('summary_id')->references('id')->on('summary')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('evalForm_id')->unsigned();
            $table->foreign('evalForm_id')->references('id')->on('evalForm')->onUpdate('cascade')->onDelete('cascade');
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
        Schema::drop('performanceSummary');
    }
}
