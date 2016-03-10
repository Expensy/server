<?php

namespace App\Transformers;

use Underscore\Types\Arrays;

class ProjectTransformer extends Transformer
{
  public $userTransformer;

  function __construct(UserTransformer $userTransformer)
  {
    $this->userTransformer = $userTransformer;
  }

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
            'members'    => $this->_getUsers($item),
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

  private function _getUsers($item)
  {
    return Arrays::each($item->users->all(), function ($user) {
      return $this->userTransformer->basicTransform($user);
    });
  }
}
