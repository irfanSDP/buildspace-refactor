<?php if ( ! $printAmountOnly ): ?>
    <?php if (! empty($itemRow[0])): ?>

        <td class="bqQtyCell"><?php echo ! $printNoPrice ? "{$quantity}" : null; ?></td>

        <td class="bqRateCell"><?php echo ($rate != 0) ? $rate : null; ?></td>
        <td class="bqRateCell"><?php echo ($estimatedTotal != 0) ? $estimatedTotal : null; ?></td>

        <?php foreach($tenderers as $k => $tenderer) : ?>
            <!-- set rate and total columns -->
            <?php
            $rate = ( isset( $contractorRates[ $tenderer['id'] ][ $elementId ][ $itemId ] ) ) ? $contractorRates[ $tenderer['id'] ][ $elementId ][ $itemId ] : 0;
            $total = $rate * $quantity;
            $rateCellValue = ( ! $printNoPrice && $itemId > 0 && ( $rate != 0 ) ) ? number_format($rate, $priceFormatting[2], $priceFormatting[0], ( $rateCommaRemove ) ? '' : $priceFormatting[1]) : null;
            $totalCellValue = ( ! $printNoPrice && $itemId > 0 && ( $total != 0 ) ) ? number_format($total, $priceFormatting[2], $priceFormatting[0], ( $rateCommaRemove ) ? '' : $priceFormatting[1]) : null;
            ?>

            <!-- rate and total columns -->
            <td class="bqRateCell" style="<?php
            if($tenderer['id'] == $lowestTendererId)
            {
                echo $lowestStyle;
            }
            else if($tenderer['id'] == $highestTendererId)
            {
                echo $highestStyle;
            }
            ?>"><?php echo $rateCellValue ?></td>
            <td class="bqRateCell" style="<?php
            if($tenderer['id'] == $lowestTendererId)
            {
                echo $lowestStyle;
            }
            else if($tenderer['id'] == $highestTendererId)
            {
                echo $highestStyle;
            }
            ?>"><?php echo $totalCellValue ?></td>
        <?php endforeach; ?>
    <?php else: ?>

        <td class="bqQtyCell">&nbsp;</td>

        <td class="bqRateCell" style="text-align: center;">&nbsp;</td>
        <td class="bqRateCell" style="text-align: center;">&nbsp;</td>

        <?php foreach($tenderers as $k => $tenderer) : ?>
            <td class="bqRateCell">&nbsp;</td>
            <td class="bqRateCell">&nbsp;</td>
        <?php endforeach; ?>

    <?php endif; ?>
<?php endif; ?>