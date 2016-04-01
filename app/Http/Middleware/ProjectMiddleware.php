<?php

namespace App\Http\Middleware;

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
  public function handle($request, Closure $next)
  {
    if (!is_null($request->id) || !is_null($request->projects)) {
      $projectId = $request->projects ?: $request->id;
      $project = Project::find($projectId);

      if (!$project) {
        return response()->json('Project does not exist', 404);
      } else if (!$project->isAccessibleByConnectedUser()) {
        return response()->json('Forbidden', 403);
      }
    }

    return $next($request);
  }
}
