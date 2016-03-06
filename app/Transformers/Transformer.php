<?php

namespace App\Transformers;

use Underscore\Types\Arrays;


abstract class Transformer
{
    public function transformCollection(array $items)
    {
        return Arrays::invoke($items, [$this, 'extendedTransform']);
    }

    public abstract function basicTransform($item);

    public abstract function extendedTransform($item);

    public abstract function fullTransform($item);
}