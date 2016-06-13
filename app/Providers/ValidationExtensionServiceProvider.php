<?php

namespace App\Providers;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Log;
use Validator;
use Underscore\Types\Arrays;

class ValidationExtensionServiceProvider extends ServiceProvider
{

  public function register()
  {
    // TODO: Implement register() method.
  }

  public function boot()
  {
    Validator::extend('unique_project_name', function ($attribute, $value, $parameters) {
      $connectedUser = Auth::user();
      $projects = $connectedUser->projects->all();

      if (count($projects) == 0) {
        return true;
      }

      $isProjectWithName = Arrays::matchesAny($projects, function ($project) use ($value) {
        return $project->title == $value;
      });

      return $isProjectWithName == false;
    });

    Validator::extend('one_default_category', function ($attribute, $value, $parameters) {
      if ($value) {
        return true;
      }

      $categories = Project::find($parameters[0])->categories->all();

      return Arrays::matchesAny($categories, function ($category) use ($parameters) {
        Log::info('' . isset($parameters[1]) == true);
        if (isset($parameters[1])) {
          return $category->by_default == true && $category->id != $parameters[1];
        }
        return $category->by_default == false;
      });
    });

    Validator::extend('hex_color', function ($attribute, $value, $parameters) {
      return preg_match("/#[A-Fa-f0-9]{6}/", $value);
    });
  }
}
