<?php

namespace App\Http\Middleware;

use App\Models\Category;
use App\Models\Entry;
use App\Models\Project;
use Closure;

class ProjectMiddleware
{
  /**
   * Handle an incoming request.
   *
   * @param  \Illuminate\Http\Request $request
   * @param  \Closure                 $next
   *
   * @return mixed
   */
  public function handle($request, Closure $next) {
    $project = null;

    if (!is_null($request->projectId) || !is_null($request->id)) {
      // for creation/update of nested resource
      if (!is_null($request->projectId)) {
        $project = Project::find($request->projectId);
      }

      // for index/show of nested resources
      if (!is_null($request->id)) {
        $projectId = null;
        $category = Category::find($request->id);
        $entry = Entry::find($request->id);

        if (!is_null($category)) {
          $projectId = $category->project_id;
        } else if (!is_null($entry)) {
          $projectId = $entry->project_id;
        }
        if (!is_null($projectId)) {
          $project = Project::find($projectId);
        }
      }

      if (!$project) {
        return response()->json('Project does not exist', 404);
      } else if (!$project->isAccessibleByConnectedUser()) {
        return response()->json('Forbidden', 403);
      }
    }

    return $next($request);
  }
}
