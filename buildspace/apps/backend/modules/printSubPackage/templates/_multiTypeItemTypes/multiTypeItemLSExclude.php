    <?php if ( ! $printAmountOnly ): ?>
        <td class="bqRateCell" style="text-align: center;"><?php echo (! empty($itemRow[0])) ? '-' : null; ?></td>
        <?php
        foreach($billColumnSettings as $billColumnSetting):
            $billColumnSettingId = $billColumnSetting['id'];
            $quantity = 1;

            $amount = 0;

            if ($rate && $rate != 0 && $quantity && $quantity != 0)
            {
                $amount = Utilities::displayScientific($rate * $quantity, 10, array( 
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

        <td class="bqQtyCell" style="text-align: center;"><?php echo (! empty($itemRow[0])) ? '-' : null; ?></td>
        <td class="bqAmountCell"><?php echo ($amount) ? $amount : null; ?></td>
        <?php endforeach?>
    <?php else: ?>
        <?php
        foreach($billColumnSettings as $billColumnSetting):
            $billColumnSettingId = $billColumnSetting['id'];
            $quantity = 1;
            $amount = 0;

            if ($rate && $rate != 0 && $quantity && $quantity != 0)
            {
                $amount = Utilities::displayScientific($rate * $quantity, 10, array( 
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

        <td class="bqAmountCell" colspan="2"><?php echo ($amount) ? $amount : null; ?></td>
        <?php endforeach?>
    <?php endif; ?>