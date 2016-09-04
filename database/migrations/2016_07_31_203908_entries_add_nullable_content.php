<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class EntriesAddNullableContent extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up() {
    Schema::table('entries', function (Blueprint $table) {
      $table->string('content')->nullable()->change();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down() {
    Schema::table('entries', function (Blueprint $table) {
      $table->string('content')->nullable(false)->change();
    });
  }
}
