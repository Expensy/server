<?php

namespace App\Transformers;

class UserTransformer extends Transformer
{
  public function __construct() {
  }

  public function fullTransform($item) {
    return $this->extendedTransform($item);
  }

  public function extendedTransform($item) {
    return array_merge($this->basicTransform($item), [
      'projects' => $this->_getProjects($item)
    ]);
  }

  public function basicTransform($item) {
    return [
      'id' => $item['id'],
      'first_name' => $item['first_name'],
      'last_name' => $item['last_name'],
      'email' => $item['email']
    ];
  }

  private function _getProjects($item) {
    return collect($item->projects->all())->map(function ($project) {
      return [
        'id' => $project['id'],
        'title' => $project['title']
      ];
    })->all();
  }
}
