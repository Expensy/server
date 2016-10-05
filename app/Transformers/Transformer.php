<?php

namespace App\Transformers;

abstract class Transformer
{
  public function transformCollection(array $items) {
    return collect($items)->map([$this, 'extendedTransform'])->all();
  }

  public abstract function basicTransform($item);

  public abstract function extendedTransform($item);

  public abstract function fullTransform($item);
}
