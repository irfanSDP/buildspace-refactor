    <?php if ( ! $printAmountOnly ): ?>

        <?php if (! empty($itemRow[0])): ?>
            <?php if ( $toggleColumnArrangement ): ?>
            <td class="bqQtyCell"><?php echo ! $printNoPrice ? "{$quantity}" : null; ?></td>
            <td class="bqUnitCell">%</td>
            <?php else: ?>
            <td class="bqUnitCell">%</td>
            <td class="bqQtyCell"><?php echo ! $printNoPrice ? "{$quantity}" : null; ?></td>
            <?php endif; ?>

            <td class="bqRateCell" style="text-align: center;">-</td>
        <?php else: ?>
            <td class="bqUnitCell">&nbsp;</td>
            <td class="bqQtyCell">&nbsp;</td>
            <td class="bqRateCell" style="text-align: center;">&nbsp;</td>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ( $printDollarAndCentColumn ): $amount = explode($priceFormatting[0], $amount); ?>
    <td class="bqAmountCell"><?php echo ! $printNoPrice && $amount[0] != 0 ? Utilities::displayScientific($amount[0], 20, array( 
            'decimal_places' => 0,
            'decimal_points' => $priceFormatting[0],
            'thousand_separator' => $amtCommaRemove ? '' : $priceFormatting[1]
        ), $printFullDecimal) : null; ?>
    </td>
    <td class="bqAmountCell" style="<?php echo ! $closeGrid ? 'border-right: none;' : null; ?>"><?php echo ! $printNoPrice && $amount[0] != 0 && isset($amount[1]) ? $amount[1] : null; ?></td>
    <?php else: ?>
    <td class="bqAmountCell" style="<?php echo ! $closeGrid ? 'border-right: none;' : null; ?>"><?php echo ! $printNoPrice && $amount != 0 ? Utilities::displayScientific($amount, ($printAmountOnly) ? 20 : 11, array(
            'decimal_places' => $priceFormatting[2],
            'decimal_points' => $priceFormatting[0],
            'thousand_separator' => $amtCommaRemove ? '' : $priceFormatting[1]
        ), $printFullDecimal) : null; ?></td>
    <?php endif; ?>