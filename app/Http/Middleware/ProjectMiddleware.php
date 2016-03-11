<?php

namespace App\Http\Middleware;

use App\Models\Project;
use Closure;

class ProjectMiddleware extends BaseM
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
    dd($request->id);
    if($request->id || $request->projects) {
      $projectId = $request->id ?: $request->projects;
      $project = Project::find($projectId);

      dd($project->isAccessibleByConnectedUser());
      if (!$project) {
        return $this->respond('project.not_found', 'Project does not exist', 404);
      } else if (!$project->isAccessibleByConnectedUser()) {
        return $this->respond('project.forbidden', 'Forbidden', 403);
      }
    }

    return $next($request);
  }
}
