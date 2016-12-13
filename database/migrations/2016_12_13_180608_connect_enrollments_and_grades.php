<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ConnectEnrollmentsAndGrades extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::table('enrollments', function(Blueprint $table) {
        $table->integer('grade_id')->unsigned()->nullable();
        $table->foreign('grade_id')->references('id')->on('grades');
      });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
      Schema::table('enrollments', function(Blueprint $table) {
        $table->dropForeign('enrollments_grade_id_foreign');
        $table->dropColumn('grade_id');
      });
    }
}
