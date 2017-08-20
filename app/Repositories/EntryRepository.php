<?php

namespace App\Repositories;

use App\Models\Entry;
use Carbon\Carbon;

class EntryRepository extends BaseRepository
{
  /**
   * @var ProjectRepository
   */
  private $projectRepository;

  public function __construct(Entry $model, ProjectRepository $projectRepository) {
    parent::__construct($model);
    $this->projectRepository = $projectRepository;
  }

  public function filter(Array $filters) {
    $limit = $this->getLimit($filters);

    $project = $this->getProject($filters['project_id']);

    $startDate = new Carbon($filters['start_date']);
    $endDate = new Carbon($filters['end_date']);
    $endDate->addDays(1);

    return $project->entries()
      ->where('created_at', '>=', $startDate)
      ->where('created_at', '<', $endDate)
      ->paginate($limit);
  }

  public function stats(Array $filters) {
    $project = $this->getProject($filters['project_id']);

    $entries = $this->filter($filters);

    $categories = $project->categories
      ->map(function ($item) use ($entries) {
        return [
          'id' => $item->id,
          'title' => $item->title,
          'total' => $entries->where('category_id', $item->id)->sum('price')
        ];
      });

    return [
      'total' => $entries->sum('price'),
      'categories' => $categories->all()
    ];
  }


  private function getProject($projectId) {
    return $this->projectRepository->find($projectId);
  }
}
