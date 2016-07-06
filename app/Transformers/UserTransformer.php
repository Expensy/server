<?php

namespace App\Transformers;

use Underscore\Types\Arrays;

class UserTransformer extends Transformer {
  public function __construct() {
  }

  public function basicTransform($item) {
    return [
      'id' => $item['id'],
      'first_name' => $item['first_name'],
      'last_name' => $item['last_name'],
      'email' => $item['email']
    ];
  }

  public function extendedTransform($item) {
    return Arrays::merge(
      $this->basicTransform($item),
      [
        'projects' => $this->_getProjects($item)
      ]);
  }

  public function fullTransform($item) {
    return $this->extendedTransform($item);
  }

  private function _getProjects($item) {
    return Arrays::each($item->projects->all(), function ($project) {
      return [
        'id' => $project['id'],
        'title' => $project['title']
      ];
    });
  }
}
