<?php if ( ! $printAmountOnly ): ?>
    <td class="bqQtyCell"><?php echo $quantity != 0 ? $quantity : null; ?></td>
    <td class="bqAmountCell"><?php echo $amount != 0 ? $amount : null; ?></td>
    <td class="bqAmountCell"><?php echo $estimatedTotal != 0 ? $estimatedTotal : null; ?></td>
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
<?php endif; ?>