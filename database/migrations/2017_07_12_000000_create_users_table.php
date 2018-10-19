<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         if(Schema::hasTable('users')) return;
         Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('firstname');
            $table->string('middlename')->nullable();
            $table->string('lastname');
            $table->string('employeeNumber')->unique();
            $table->string('email')->unique();
            $table->string('password');
            $table->boolean('updatedPass');
            
           
            $table->dateTime('dateHired');
            $table->dateTime('dateRegularized')->nullable();

            $table->string('currentAddress1')->nullable();
            $table->string('currentAddress2')->nullable();
            $table->string('currentAddress3')->nullable();

            $table->string('permanentAddress1')->nullable();
            $table->string('permanentAddress2')->nullable();
            $table->string('permanentAddress3')->nullable();

            $table->string('mobileNumber')->nullable();
            $table->string('phoneNumber')->nullable();

            $table->integer('userType_id')->unsigned();
            $table->foreign('userType_id')->references('id')->on('userType')->onDelete('cascade')->onUpdate('cascade');

            $table->integer('status_id')->unsigned();
            $table->foreign('status_id')->references('id')->on('statuses')->onUpdate('cascade');

            $table->integer('position_id')->unsigned();
            $table->foreign('position_id')->references('id')->on('positions')->onDelete('cascade')->onUpdate('cascade');

            $table->string('hascoverphoto',5)->nullable();

            /* $table->integer('immediateHead_Campaigns_id')->unsigned();
            $table->foreign('immediateHead_Campaigns_id')->references('id')->on('immediateHead_Campaigns')->onUpdate('cascade');

            $table->integer('campaign_id')->unsigned();
            $table->foreign('campaign_id')->references('id')->on('campaign')->onUpdate('cascade'); */

            // $table->integer('immediateHead_id')->unsigned();
            // $table->foreign('immediateHead_id')->references('id')->on('immediateHead')->onUpdate('cascade');

            

            $table->rememberToken();
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
        Schema::drop('users');
    }
}
