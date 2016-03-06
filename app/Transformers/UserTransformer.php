<?php


namespace App\Transformers;


class UserTransformer extends Transformer
{
    public function basicTransform($item)
    {
        return [
            'id'    => $item['id'],
            'name' => $item['name'],
            'email' => $item['email']
        ];
    }

    public function extendedTransform($item)
    {
        return $this->basicTransform($item);
    }

    public function fullTransform($item)
    {
        return $this->extendedTransform($item);
    }
}