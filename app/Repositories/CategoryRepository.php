<?php
namespace App\Repositories;

use App\Models\Category;

class CategoryRepository extends BaseRepository {
  /**
   * @var ProjectRepository
   */
  private $projectRepository;

  public function __construct(Category $model, ProjectRepository $projectRepository) {
    parent::__construct($model);
    $this->projectRepository = $projectRepository;
  }

  public function filter(Array $filters) {
    $limit = $this->getLimit($filters);

    $project = $this->getProject($filters['project_id']);

    return $project->categories()->with([])->paginate($limit);
  }


  private function getProject($projectId) {
    return $this->projectRepository->find($projectId);
  }
}
