    <?php
    $amount = 0;

    if ($rate && $rate != 0)
    {
        $amount = Utilities::displayScientific($rate * 1, ($printAmountOnly) ? 14 : 10, array( 
            'decimal_places' => $priceFormatting[2],
            'decimal_points' => $priceFormatting[0],
            'thousand_separator' => $amtCommaRemove ? '' : $priceFormatting[1]
        ), $printFullDecimal);
    }
    else
    {
        $amount = null;
    }
    
    ?>

    <?php if ( ! $printAmountOnly ): ?>
        <td class="bqRateCell" style="text-align: center;"><?php echo (! empty($itemRow[0])) ? '-' : null; ?></td>
        <?php foreach($billColumnSettings as $billColumnSetting): ?>
        <td class="bqQtyCell" style="text-align: center;"><?php echo (! empty($itemRow[0])) ? '-' : null; ?></td>
        <td class="bqAmountCell">
            <?php echo ! $printNoPrice ? $amount : null; ?>
        </td>
        <?php endforeach?>
    <?php else: ?>
        <?php foreach($billColumnSettings as $billColumnSetting): ?>
        <td class="bqAmountCell" colspan="2">
            <?php echo ! $printNoPrice ? $amount : null; ?>
        </td>
        <?php endforeach; ?>
    <?php endif; ?>