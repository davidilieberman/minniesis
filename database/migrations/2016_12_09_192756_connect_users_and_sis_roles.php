<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ConnectUsersAndSisRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function(Blueprint $table) {
          $table->integer('sis_role_id')->unsigned();
          $table->foreign('sis_role_id')->references('id')->on('sis_roles');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function(Blueprint $table) {
          $table->dropForeign('users_sis_role_id_foreign');
          $table->dropColumn('sis_role_id');
        });
    }
}
