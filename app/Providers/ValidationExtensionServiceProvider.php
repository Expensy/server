<?php

namespace App\Providers;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Validator;

class ValidationExtensionServiceProvider extends ServiceProvider
{

  public function register() {
    // TODO: Implement register() method.
  }

  public function boot() {
    Validator::extend('unique_project_name', function ($attribute, $value, $parameters) {
      $connectedUser = Auth::user();
      $projects = $connectedUser->projects->all();

      if (count($projects) == 0) {
        return true;
      }

      return !collect($projects)->contains(function ($project) use ($value) {
        return $project->title == $value;
      });
    });

    Validator::extend('one_default_category', function ($attribute, $value, $parameters) {
      if ($value) {
        return true;
      }

      $project = Project::find($parameters[0]);
      $category = Project::find($parameters[0]);

      if (is_null($project) && is_null($category)) {
        return false;
      }

      $categories = !is_null($project) ?
        $project->categories->all() :
        $category->project->categories->all();

      return collect($categories)->contains(function ($category) use ($parameters) {
        if (isset($parameters[1])) {
          return $category->by_default == true && $category->id != $parameters[1];
        }

        return $category->by_default == true;
      });
    });

    Validator::extend('hex_color', function ($attribute, $value, $parameters) {
      return preg_match("/#[A-Fa-f0-9]{6}/", $value);
    });
  }
}
