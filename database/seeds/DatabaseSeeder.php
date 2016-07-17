<?php

use App\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder {
  private $tables = [
    'categories',
    'entries',
    'password_resets',
    'project_user',
    'projects',
    'users'
  ];

  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run() {
    $this->cleanDatabase();
    Eloquent::unguard();
  }

  private function cleanDatabase() {
    DB::statement('SET FOREIGN_KEY_CHECKS=0');
    foreach ($this->tables as $tableName) {
      DB::table($tableName)->truncate();
    }
    DB::statement('SET FOREIGN_KEY_CHECKS=1');
  }
}
