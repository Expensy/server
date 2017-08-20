<?php

use App\Models\Category;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class CategoriesTableSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run() {
    $user = User::where('email', '=', 'dev@expensy.com')->first();
    $project = $user->projects->first();

    factory(Category::class, 5)->create([
      'project_id' => $project->id,
      'created_at' => Carbon::now(),
      'updated_at' => Carbon::now()
    ]);
  }
}
