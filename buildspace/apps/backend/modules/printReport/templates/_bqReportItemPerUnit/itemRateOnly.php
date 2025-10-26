<td class="bqQtyCell"><?php echo (! empty($itemRow[0])) ? 'RATE ONLY' : null; ?></td>
<?php
$printRateRow = ( ! empty( $itemRow[0] ) );
?>
<td class="bqRateCell"><?php echo ! $printNoPrice && $rate && $rate != 0 ? $rate : ($printRateRow ? '-' : '') ?></td>
<td class="bqRateCell"><?php echo $printRateRow ? '-' : '' ?></td>

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