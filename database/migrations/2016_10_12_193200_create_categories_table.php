<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCategoriesTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up() {
    Schema::create('categories', function (Blueprint $table) {
      $table->increments('id');
      $table->string('title');
      $table->string('color', 7);
      $table->boolean('by_default')->default(0);
      $table->integer('project_id')->unsigned();
      $table->foreign('project_id')->references('id')->on('projects');
      $table->timestamps();
      $table->softDeletes();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {
    Schema::drop('categories');
  }
}
