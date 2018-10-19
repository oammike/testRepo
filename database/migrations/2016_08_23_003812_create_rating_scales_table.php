<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRatingScalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         if(Schema::hasTable('ratingScale')) return;
         Schema::create('ratingScale', function (Blueprint $table) {
            $table->increments('id');
            $table->string('label');
            $table->string('status');
            $table->text('description');
            $table->integer('percentage');
            $table->integer('increase')->unsigned();
            $table->string('icon');
            $table->decimal('maxRange',5,2);
            $table->string('letterGrade')->nullable();
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
        Schema::drop('ratingScale');
    }
}
