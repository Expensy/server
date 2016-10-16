<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Project;
use Illuminate\Support\ServiceProvider;

class ExpensyEventServiceProvider extends ServiceProvider
{
  public function boot() {
    Project::created(function ($project) {
      Category::create([
        'title' => env('DEFAULT_CATEGORY_TITLE', 'Category 1'),
        'color' => env('DEFAULT_CATEGORY_COLOR', '#419fdb'),
        'by_default' => true,
        'project_id' => $project->id
      ]);
    });

    Category::saved(function ($category) {
      if ($category->by_default) {

        Category::where('project_id', $category->project_id)
          ->where('id', '!=', $category->id)
          ->where('by_default', true)
          ->update(['by_default' => false]);
      }
    });
  }

  /**
   * Register the service provider.
   *
   * @return void
   */
  public function register() {
    // TODO: Implement register() method.
  }
}
