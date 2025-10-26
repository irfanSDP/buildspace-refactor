<?php if($printQty) : ?>
    <?php if ( $toggleColumnArrangement ): ?>
    <td class="bqQtyCell"><?php echo (! empty($itemRow[0])) ? 'RATE ONLY' : null; ?></td>
    <?php else: ?>
    <td class="bqQtyCell"><?php echo (! empty($itemRow[0])) ? 'RATE ONLY' : null; ?></td>
    <?php endif; ?>
<?php endif ?>

<?php
$printRateRow = ( ! empty( $itemRow[0] ) );
?>
<td class="bqRateCell"><?php echo ! $printNoPrice && $rate && $rate != 0 ? $rate : ($printRateRow ? '-' : '') ?></td>
<td class="bqRateCell"><?php echo $printRateRow ? '-' : '' ?></td>

<?php if(count($tenderers)) : ?>
    <?php foreach($tenderers as $k => $tenderer) : ?>
        <td class="bqRateCell" style="<?php
            if($tenderer['id'] == $lowestTendererId)
            {
                echo $lowestStyle;
            }
            else if($tenderer['id'] == $highestTendererId)
            {
                echo $highestStyle;
            }
            ?>"><?php echo ! $printNoPrice  && $itemId > 0 && $tendererRates && array_key_exists($itemId, $tendererRates[$tenderer['id']]) && $tendererRates[$tenderer['id']][$itemId] != 0 ? number_format($tendererRates[$tenderer['id']][$itemId], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?></td>
        <td class="bqRateCell" style="<?php
            if($tenderer['id'] == $lowestTendererId)
            {
                echo $lowestStyle;
            }
            else if($tenderer['id'] == $highestTendererId)
            {
                echo $highestStyle;
            }
            ?>"><?php echo ! $printNoPrice  && $itemId > 0 && $tendererTotals && array_key_exists($itemId, $tendererTotals[$tenderer['id']]) && $tendererTotals[$tenderer['id']][$itemId] != 0 ? number_format($tendererTotals[$tenderer['id']][$itemId], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?></td>
    <?php endforeach; ?>
<?php endif;?>