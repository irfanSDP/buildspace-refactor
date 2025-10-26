    <?php if ( ! $printAmountOnly ): ?>
        <td class="bqRateCell"><?php echo ! empty($itemRow[0]) && ! $printNoPrice && $rate && $rate != 0 ? Utilities::displayScientific($rate, 9, array( 
                'decimal_places' => $priceFormatting[2],
                'decimal_points' => $priceFormatting[0],
                'thousand_separator' => $rateCommaRemove ? '' : $priceFormatting[1]
            ), $printFullDecimal) : null ?>
        </td>
        <?php foreach($billColumnSettings as $billColumnSetting): ?>
        <td class="bqQtyCell"><?php echo ! empty($itemRow[0]) ? 'RATE ONLY' : null; ?></td>
        <td class="bqAmountCell" style="text-align: center;"><?php echo ! empty($itemRow[0]) ? '-' : null; ?></td>
        <?php endforeach?>
    <?php else: ?>
        <?php foreach($billColumnSettings as $billColumnSetting): ?>
        <td class="bqAmountCell" colspan="2" style="text-align: center;"><?php echo ! empty($itemRow[0]) ? '-' : null; ?></td>
        <?php endforeach?>
    <?php endif; ?>