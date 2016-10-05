<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateEntriesTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up() {
    Schema::create('entries', function (Blueprint $table) {
      $table->increments('id');
      $table->string('title');
      $table->integer('price', false, true);
      $table->timestamp('date');
      $table->text('content')->nullable();
      $table->integer('project_id')->unsigned();
      $table->foreign('project_id')->references('id')->on('projects');
      $table->integer('category_id')->unsigned();
      $table->foreign('category_id')->references('id')->on('categories');

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
    Schema::drop('entries');
  }
}
