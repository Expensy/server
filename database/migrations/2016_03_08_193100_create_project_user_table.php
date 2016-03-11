<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectUserTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('project_user', function (Blueprint $table) {
      $table->increments('id');

      $table->integer('project_id', false, true)->index();
      $table->foreign('project_id')->references('id')->on('projects');

      $table->integer('user_id', false, true)->index();
      $table->foreign('user_id')->references('id')->on('users');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::drop('project_user');
  }
}
