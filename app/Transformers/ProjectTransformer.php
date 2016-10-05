<?php

namespace App\Transformers;

class ProjectTransformer extends Transformer
{
  public $userTransformer;
  private $categoryTransformer;

  function __construct(UserTransformer $userTransformer, CategoryTransformer $categoryTransformer) {
    $this->userTransformer = $userTransformer;
    $this->categoryTransformer = $categoryTransformer;
  }

  public function fullTransform($item) {
    return array_merge($this->extendedTransform($item), []);
  }

  public function extendedTransform($item) {
    return array_merge($this->basicTransform($item), [
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
    return collect($item->users->all())->map(function ($user) {
      return $this->userTransformer->basicTransform($user);
    })->all();
  }

  private function _getCategories($item) {
    return collect($item->categories->all())->map(function ($category) {
      return $this->categoryTransformer->extendedTransform($category);
    })->all();
  }
}
