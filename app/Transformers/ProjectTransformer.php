<?php

namespace App\Transformers;

use Underscore\Types\Arrays;

class ProjectTransformer extends Transformer {
  public $userTransformer;
  private $categoryTransformer;

  function __construct(UserTransformer $userTransformer, CategoryTransformer $categoryTransformer) {
    $this->userTransformer = $userTransformer;
    $this->categoryTransformer = $categoryTransformer;
  }

  public function fullTransform($item) {
    return Arrays::merge(
      $this->extendedTransform($item),
      []);
  }

  public function extendedTransform($item) {
    return Arrays::merge(
      $this->basicTransform($item),
      [
        'members' => $this->_getUsers($item),
        'categories' => $this->_getCategories($item),
        'created_at' => $item['created_at']->toIso8601String(),
        'updated_at' => $item['updated_at']->toIso8601String()
      ]);
  }

  public function basicTransform($item) {
    return [
      'id' => $item['id'],
      'title' => $item['title']
    ];
  }

  private function _getUsers($item) {
    return Arrays::each($item->users->all(), function ($user) {
      return $this->userTransformer->basicTransform($user);
    });
  }

  private function _getCategories($item) {
    return Arrays::each($item->categories->all(), function ($category) {
      return $this->categoryTransformer->extendedTransform($category);
    });
  }
}
