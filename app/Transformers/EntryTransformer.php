<?php

namespace App\Transformers;

use Underscore\Types\Arrays;

class EntryTransformer extends Transformer {
  private $categoryTransformer;

  function __construct(CategoryTransformer $categoryTransformer) {
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
        'price' => (int) $item['price'],
        'date' => $item['date']->toIso8601String(),
        'content' => $item['content'],
        'category' => $this->categoryTransformer->basicTransform($item->category->toArray()),
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
}
