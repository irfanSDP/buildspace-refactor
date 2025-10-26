<?php namespace PCK\Helpers;

use Illuminate\Database\Eloquent\Collection;

class Sorter {

    public static function sortCollectionWithDefinedOrder(Collection $items, array $order, $attribute)
    {
        $items->sort(function($itemA, $itemB) use ($order, $attribute)
        {
            $pos_a = array_search($itemA->{$attribute}, $order);
            $pos_b = array_search($itemB->{$attribute}, $order);

            return $pos_a > $pos_b;
        });

        return $items;
    }

    public static function multiSort(Collection &$items, $conditions)
    {
        $items->sort(function($a, $b) use ($conditions)
        {
            foreach($conditions as $condition)
            {
                $firstValue  = $a[ $condition['attribute'] ];
                $secondValue = $b[ $condition['attribute'] ];

                if( isset( $condition['sort'] ) && strtolower($condition['sort']) == 'desc' )
                {
                    $firstValue  = $b[ $condition['attribute'] ];
                    $secondValue = $a[ $condition['attribute'] ];
                }

                if( isset( $condition['order'] ) )
                {
                    $pos_a  = array_search($firstValue, $condition['order']);
                    $pos_b  = array_search($secondValue, $condition['order']);
                    $result = $pos_a <=> $pos_b;
                }
                else
                {
                    $result = ( $firstValue <=> $secondValue );
                }

                if( $result != 0 ) break;
            }

            return $result ?? 0;
        });
    }

}