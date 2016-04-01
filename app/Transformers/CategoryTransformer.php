<?php

namespace App\Transformers;

use Underscore\Types\Arrays;

class CategoryTransformer extends Transformer
{
  function __construct() { }

  public function basicTransform($item)
  {
    return [
        'id'    => $item['id'],
        'title' => $item['title']
    ];
  }

  public function extendedTransform($item)
  {
    return Arrays::merge(
        $this->basicTransform($item),
        [
            'color'      => $item['color'],
            'by_default' => (bool)$item['by_default'],
            'created_at' => $item['created_at']->toIso8601String(),
            'updated_at' => $item['updated_at']->toIso8601String()
        ]);
  }

  public function fullTransform($item)
  {
    return Arrays::merge(
        $this->extendedTransform($item),
        []);
  }
}
