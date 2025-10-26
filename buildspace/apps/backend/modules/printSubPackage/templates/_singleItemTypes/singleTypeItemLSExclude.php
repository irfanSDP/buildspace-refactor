    <?php if ( ! $printAmountOnly ): ?>
        <?php if ( $toggleColumnArrangement ): ?>
        <td class="bqQtyCell" style="text-align: center;"><?php echo (! empty($itemRow[0])) ? '-' : null; ?></td>
        <td class="bqUnitCell"><?php echo $itemRow ? $itemRow[5] : '&nbsp;'?></td>
        <?php else: ?>
        <td class="bqUnitCell"><?php echo $itemRow ? $itemRow[5] : '&nbsp;'?></td>
        <td class="bqQtyCell" style="text-align: center;"><?php echo (! empty($itemRow[0])) ? '-' : null; ?></td>
        <?php endif; ?>
        <td class="bqRateCell" style="text-align: center;"><?php echo (! empty($itemRow[0])) ? '-' : null ?></td>
    <?php endif; ?>

    <?php if ( $printDollarAndCentColumn ): $amount = explode($priceFormatting[0], $amount); ?>
    <td class="bqAmountCell">
        <?php echo $amount[0] != 0 ? Utilities::displayScientific($amount[0], 20, array( 
                'decimal_places' => 0,
                'decimal_points' => $priceFormatting[0],
                'thousand_separator' => $amtCommaRemove ? '' : $priceFormatting[1]
            ), $printFullDecimal) : null; 
        ?>
    </td>
    <td class="bqAmountCell" style="<?php echo ! $closeGrid ? 'border-right: none;' : null; ?>"><?php echo ! empty($itemRow[0]) && ! $printNoPrice && isset($amount[1]) ? $amount[1] : null; ?></td>
    <?php else: ?>
    <td class="bqAmountCell" style="<?php echo ! $closeGrid ? 'border-right: none;' : null; ?>"><?php echo $amount != 0 ? Utilities::displayScientific($amount, ($printAmountOnly) ? 20 : 11, array(
            'decimal_places' => $priceFormatting[2],
            'decimal_points' => $priceFormatting[0],
            'thousand_separator' => $amtCommaRemove ? '' : $priceFormatting[1]
        ), $printFullDecimal) : null; ?></td>
    <?php endif; ?>