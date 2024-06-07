<?php

namespace App\Helpers;

use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PaginationHelper
{
    /**
     * Paginate a standard Laravel Collection.
     *
     * @param Collection $items
     * @param int $perPage
     * @param int|null $page
     * @param array $options
     * @return LengthAwarePaginator
     */
    public static function paginate(Collection $items, $perPage = 15, $page = null, $options = [])
    {
        $page = $page ?: (LengthAwarePaginator::resolveCurrentPage() ?: 1);
        $items = $items->forPage($page, $perPage);
        return new LengthAwarePaginator($items, $items->count(), $perPage, $page, $options);
    }
}
