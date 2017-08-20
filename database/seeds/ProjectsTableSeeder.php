<?php

use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class ProjectsTableSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run() {
    $user = User::where('email', '=', 'dev@expensy.com')->first();

    $project = factory(Project::class)->create([
      'title' => 'Project 1',
      'currency' => 'GBP',
      'created_at' => Carbon::now(),
      'updated_at' => Carbon::now()
    ]);
    $project->users()->attach($user->id);
  }
}
