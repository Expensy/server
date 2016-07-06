<?php

namespace App\Repositories;

use App\Models\Enum\Action;
use App\Utils\PostValidator;
use Illuminate\Database\Eloquent\Model;
use Underscore\Parse;
use Underscore\Types\Arrays;

abstract class BaseRepository {
  protected $defaultLimit = 20;
  protected $defaultSort = 'id';
  protected $defaultOrder = 'desc';

  protected $model;

  public function __construct(Model $model) {
    $this->model = $model;
  }

  public abstract function filter(array $filters);

  public function isValidForCreation($type, array $data) {
    $instance = new $type();
    $validated = $instance->validate($data, Action::CREATION);

    $errors = [];

    if (!$validated) {
      $validationErrors = $instance->errors();

      if (count($validationErrors)) {
        $errors = Arrays::invoke($validationErrors, function ($error) {
          return array_keys($error);
        });
      }
    }

    $object = new PostValidator($validated, $errors);

    return $object;
  }

  public function isValidForUpdate($type, array $data) {
    $instance = new $type();
    $validated = $instance->validate($data, Action::UPDATE);

    $object = new PostValidator($validated, $instance->errors());

    return $object;
  }

  public function all() {
    return $this->model->all()->toArray();
  }

  public function allWith(array $with) {
    return $this->model->with($with)->get()->toArray();
  }

  public function create($inputs) {
    return $this->model->create($inputs);
  }

  public function update($id, $inputs) {
    $updated = $this->model->find($id)->update($inputs);

    if ($updated) {
      $resource = $this->model->find($id);
      $resource->touch();

      return $resource;
    }

    return null;
  }

  public function find($id, $searchInDeleted = false) {
    $model = $this->model;

    if ($searchInDeleted) {
      $model = $model->withTrashed();
    }

    $model = $model->find($id);

    if ($model) {
      return $model;
    }

    return null;
  }

  public function delete($id) {
    return $this->model->find($id)->delete();
  }

  protected function getLimit($filters) {
    return Maybe(Arrays::get($filters, 'limit'))
      ->map(function ($maybe) {
        $limit = Parse::toInteger($maybe->val($this->defaultLimit));

        return $limit <= 50 ? $limit : $this->defaultLimit;
      })
      ->val($this->defaultLimit);
  }
}
