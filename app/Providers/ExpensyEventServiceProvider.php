<?php

namespace App\Providers;

use App\Models\Category;
use Illuminate\Support\ServiceProvider;

class ExpensyEventServiceProvider extends ServiceProvider
{
  public function boot() {
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
