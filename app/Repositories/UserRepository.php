<?php
namespace App\Repositories;

use Underscore\Parse;
use Underscore\Types\Arrays;
use App\Models\User;

class UserRepository extends BaseRepository
{
  public function __construct(User $model)
  {
    parent::__construct($model);
  }

  public function filter(Array $filters)
  {
    $limit = $this->getLimit($filters);

    return $this->model->with([])->paginate($limit);
  }

  public function findByEmail($email)
  {
    return $this->model->where('email', $email);
  }
}