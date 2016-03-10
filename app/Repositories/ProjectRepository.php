<?php
namespace App\Repositories;

use App\Models\Project;
use Illuminate\Support\Facades\Auth;

class ProjectRepository extends BaseRepository
{
  public function __construct(Project $model)
  {
    parent::__construct($model);
  }

  public function filter(array $filters)
  {
    $limit = $this->getLimit($filters);

    return Auth::user()->projects()->with([])->paginate($limit);
  }

  public function create($inputs)
  {
    $project = parent::create($inputs);
    $project->users()->attach(Auth::user()->id);

    return $project;
  }

  public function addUser($id, $userId)
  {
    $item = $this->model->find($id);
    $item->users()->attach($userId);

    return $item;
  }

  public function removeUser($id, $userId)
  {
    $item = $this->model->find($id);
    $item->users()->detach($userId);

    return $item;
  }
}