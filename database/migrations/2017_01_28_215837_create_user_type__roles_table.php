<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUserTypeRolesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         if(Schema::hasTable('userType_roles')) return;
         Schema::create('userType_roles', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('userType_id')->unsigned();
            $table->foreign('userType_id')->references('id')->on('userType')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('role_id')->unsigned();
            $table->foreign('role_id')->references('id')->on('roles')->onUpdate('cascade')->onDelete('cascade');


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
        Schema::drop('userType_roles');
    }
}
