<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTempUploadsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('temp_uploads', function (Blueprint $table) {
            
            if(Schema::hasTable('temp_uploads')) return;
            $table->increments('id');
            $table->string('employeeNumber');
            $table->date('productionDate');
            $table->time('logTime');
            $table->string('logType');
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
        Schema::drop('temp_uploads');
    }
}
