<?php if ( ! $printAmountOnly ): ?>
    <?php if (! empty($itemRow[0])): ?>
        <?php if($printQty) : ?>
            <?php if ( $toggleColumnArrangement ): ?>
            <td class="bqQtyCell"><?php echo ! $printNoPrice ? "{$quantity}" : null; ?></td>
            <?php else: ?>
            <td class="bqQtyCell"><?php echo ! $printNoPrice ? "{$quantity}" : null; ?></td>
            <?php endif; ?>
        <?php endif; ?>

        <td class="bqRateCell"><?php echo ($rate != 0) ? $rate : null; ?></td>
        <td class="bqRateCell"><?php echo ($estimatedTotal != 0) ? $estimatedTotal : null; ?></td>

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
    <?php else: ?>

        <?php if($printQty) : ?>
            <td class="bqQtyCell">&nbsp;</td>
        <?php endif; ?>

        <td class="bqRateCell" style="text-align: center;">&nbsp;</td>
        <td class="bqRateCell" style="text-align: center;">&nbsp;</td>

        <?php if(count($tenderers)) : ?>
            <?php foreach($tenderers as $k => $tenderer) : ?>
                <td class="bqRateCell">&nbsp;</td>
                <td class="bqRateCell">&nbsp;</td>
            <?php endforeach; ?>
        <?php endif;?>

    <?php endif; ?>
<?php endif; ?>