<?php

use Carbon\Carbon;
use Illuminate\Database\Seeder;

class UsersTableSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run() {
    DB::table('users')->insert([
      'first_name' => 'Dev',
      'last_name' => 'Dev',
      'email' => 'dev@expensy.com',
      'password' => bcrypt('password'),
      'created_at' => Carbon::now(),
      'updated_at' => Carbon::now()
    ]);
  }
}
