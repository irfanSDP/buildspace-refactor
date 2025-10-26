<?php namespace PCK\Helpers;

use Illuminate\Database\Eloquent\Collection;

class Paginator {

    public static function paginate(Collection $items, $itemsPerPage, $currentPage)
    {
        $currentPageItems = $items->slice(( $currentPage - 1 ) * $itemsPerPage, $itemsPerPage);

        return \Paginator::make(Arrays::collectionToArray($currentPageItems), $items->count(), $itemsPerPage);
    }
}