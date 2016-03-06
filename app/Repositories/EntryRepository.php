<?php
namespace Arato\Repositories;

use App\Models\Entry;
use Underscore\Parse;
use Underscore\Types\Arrays;

class EntryRepository extends Repository
{
    public function __construct(Entry $model)
    {
        parent::__construct($model);
    }

    public function filter(Array $filters)
    {
        $query = $this->model;

        $limit = Maybe(Arrays::get($filters, 'limit'))
            ->map(function ($maybe) {
                $limit = Parse::toInteger($maybe->val($this->defaultLimit));

                return $limit <= 50 && $limit > 0 ? $limit : $this->defaultLimit;
            })
            ->val($this->defaultLimit);

        $userId = Maybe(Arrays::get($filters, 'userId'))
            ->val();

        if ($userId) {
            $query = $query->where('user_id', '=', $userId);
        }

        $priceMin = Maybe(Arrays::get($filters, 'priceMin'))
            ->val();

        if ($priceMin) {
            $query = $query->where('price', '>=', $priceMin);
        }

        $priceMax = Maybe(Arrays::get($filters, 'priceMax'))
            ->val();

        if ($priceMax) {
            $query = $query->where('price', '<=', $priceMax);
        }

        $availableSorts = ['id', 'created_at', 'price'];

        $sortBy = Maybe(Arrays::get($filters, 'sort'))
            ->map(function ($maybe) use ($availableSorts) {
                $sort = $maybe->val();

                return Arrays::contains($availableSorts, $maybe->val())
                    ? $sort
                    : $this->defaultSort;
            })
            ->val($this->defaultSort);

        $availableOrders = ['asc', 'desc'];
        $order = Maybe(Arrays::get($filters, 'order'))
            ->map(function ($maybe) use ($availableOrders) {
                $order = $maybe->val();

                return Arrays::contains($availableOrders, $maybe->val())
                    ? $order
                    : $this->defaultOrder;
            })
            ->val($this->defaultOrder);

        return $query->with('user')->orderBy($sortBy, $order)->paginate($limit);
    }
}
