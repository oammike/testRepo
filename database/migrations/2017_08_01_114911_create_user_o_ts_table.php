<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserOTsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_ot', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('biometrics_id')->unsigned();
            $table->foreign('biometrics_id')->references('id')->on('biometrics')->onUpdate('cascade')->onDelete('cascade');

            $table->decimal('billable_hours',5,2);
            $table->decimal('filed_hours',5,2);
            $table->time('timeStart');
            $table->time('timeEnd');
            $table->string('reason');

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
        Schema::drop('user_ot');
    }
}
