<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddedByDefaultCategoriesTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::table('categories', function (Blueprint $table) {
      $table->boolean('by_default')->after('color');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::table('categories', function (Blueprint $table) {
      $table->dropColumn('by_default');
    });
  }
}
