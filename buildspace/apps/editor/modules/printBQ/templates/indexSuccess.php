<style type="text/css"><?php echo $layoutStyling?></style>
<?php
foreach($pages as $page)
{
    for($i=1;$i<=$page['item_pages']->count(); $i++)
    {
        if($page['item_pages'] instanceof SplFixedArray and $page['item_pages']->offsetExists($i))
        {
            $billItemsLayoutParams = array(
                'itemPages' => $page['item_pages']->offsetGet($i),
                'rates' => $rates,
                'billColumnSettings' => $billColumnSettings,
                'maxRows' => $maxRows,
                'elementHeaderDescription' => $page['description'],
                'elementCount' => $page['element_count'],
                'pageCount' => $i
            );
            include_partial('printBQ/'.$billItemsLayout, $billItemsLayoutParams);

            $page['item_pages']->offsetUnset($i);
        }
    }

    foreach($page['collection_pages'] as $pageNo => $collectionPage)
    {
        $collectionPageParams = array(
            'collectionPage' => $collectionPage,
            'billColumnSettings' => $billColumnSettings,
            'maxRows' => count($billColumnSettings) > 1 ? $maxRows-4 : $maxRows,//less 4 rows for collection page
            'elementHeaderDescription' => $page['description'],
            'elementCount' => $page['element_count'],
            'pageCount' => $pageNo
        );

        include_partial('printBQ/'.$collectionPageLayout, $collectionPageParams);

        unset($page['collection_pages'][$i]);
    }

}
?>
</div>