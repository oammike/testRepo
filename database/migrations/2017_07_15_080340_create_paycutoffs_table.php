<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaycutoffsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if(Schema::hasTable('paycutoffs')) return;
        Schema::create('paycutoffs', function (Blueprint $table) {
            $table->increments('id');
            $table->date('fromDate');
            $table->date('toDate');
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
        Schema::drop('paycutoffs');
    }
}
