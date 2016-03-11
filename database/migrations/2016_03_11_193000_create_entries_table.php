<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEntriesTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('entries', function (Blueprint $table) {
      $table->increments('id');
      $table->string('title');
      $table->integer('price', false, true);
      $table->timestamp('date');
      $table->text('content');
      $table->integer('project_id')->unsigned();
      $table->integer('category_id')->unsigned();

      $table->timestamps();
      $table->softDeletes();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::drop('entries');
  }
}
