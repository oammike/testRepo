  <?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEvalFormsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         if(Schema::hasTable('evalForm')) return;
         Schema::create('evalForm', function (Blueprint $table) {
            $table->increments('id');
            $table->boolean('coachingDone');
            $table->dateTime('coachingTimestamp')->nullable();

            $table->decimal('overAllScore',4,2);
            $table->integer('salaryIncrease');
            $table->dateTime('startPeriod');
            $table->dateTime('endPeriod');

            $table->integer('evalSetting_id')->unsigned();
            $table->foreign('evalSetting_id')->references('id')->on('evalSetting')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');

            $table->integer('evaluatedBy')->unsigned();
            $table->foreign('evaluatedBy')->references('id')->on('immediateHead_Campaigns')->onUpdate('cascade');

            $table->boolean('isDraft');

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
        Schema::drop('evalForm');
    }
}
