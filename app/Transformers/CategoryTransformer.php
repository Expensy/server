<?php

namespace App\Transformers;

class CategoryTransformer extends Transformer
{
  function __construct() { }

  public function fullTransform($item) {
    return array_merge($this->extendedTransform($item), []);
  }

  public function extendedTransform($item) {
    return array_merge($this->basicTransform($item), [
      'color' => $item['color'],
      'by_default' => (bool) $item['by_default'],
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
