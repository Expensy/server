<?php

use App\Models\Entry;
use App\Models\User;
use Faker\Factory;
use Illuminate\Database\Seeder;

class EntriesTableSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run() {
    $faker = Factory::create();

    $user = User::where('email', '=', 'dev@expensy.com')->first();
    $project = $user->projects->first();
    $categoryIds = $project->categories->pluck('id')->all();

    foreach (range(1, 100) as $index) {
      $date = $faker->dateTimeBetween('-3months');
      factory(Entry::class, 1)->create([
        'project_id' => $project->id,
        'category_id' => $faker->randomElement($categoryIds),
        'date' => $date,
        'created_at' => $date,
        'updated_at' => $date
      ]);
    }
  }
}
