<?php

namespace App\Transformers;

use Underscore\Types\Arrays;

class EntryTransformer extends Transformer
{
    public $notificationTransformer;
    public $userTransformer;
    public $categoryTransformer;

    function __construct(NotificationTransformer $notificationTransformer, UserTransformer $userTransformer, categoryTransformer $categoryTransformer)
    {
        $this->notificationTransformer = $notificationTransformer;
        $this->userTransformer = $userTransformer;
        $this->categoryTransformer = $categoryTransformer;
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
                'price'      => $item['price'],
                'date'       => $item['date']->toIso8601String(),
                'content'    => $item['content'],
                'category'   => $item->categoryTransformer->basicTransform($item->category),
                'user'       => $this->userTransformer->basicTransform($item->user),
                'created_at' => $item['created_at']->toIso8601String(),
                'updated_at' => $item['updated_at']->toIso8601String()
            ]);
    }

    public function fullTransform($item)
    {
        return Arrays::merge(
            $this->extendedTransform($item),
            [
                'notifications' => $this->notificationTransformer->transformCollection($item->notifications->all())
            ]);
    }
}
