    <?php if ( ! $printAmountOnly ): ?>

        <?php if ( ! empty($itemRow[0]) ): ?>
        <td class="bqRateCell" style="text-align: center;">-</td>
        <?php else: ?>
        <td class="bqRateCell" style="text-align: center;">&nbsp;</td>
        <?php endif; ?>

        <?php
        foreach($billColumnSettings as $billColumnSetting):
            $billColumnSettingId = $billColumnSetting['id'];
        ?>

        <?php if ( ! empty($itemRow[0]) ): ?>
        <td class="bqQtyCell"><?php echo ! $printNoPrice ? Utilities::number_clean($quantity) : null; ?></td>
        <?php else: ?>
        <td class="bqQtyCell" style="text-align: center;">&nbsp;</td>
        <?php endif; ?>

        <td class="bqAmountCell">
            <?php
            $amount = 0;

            if ($rate && $rate != 0)
            {
                $amount = Utilities::displayScientific($rate, 10, array( 
                    'decimal_places' => $priceFormatting[2],
                    'decimal_points' => $priceFormatting[0],
                    'thousand_separator' => $amtCommaRemove ? '' : $priceFormatting[1]
                ), $printFullDecimal);
            }
            else
            {
                $amount = null;
            }

            echo ! $printNoPrice ? $amount : null;
            ?>
        </td>
        <?php endforeach?>
    <?php else: ?>
        <?php foreach($billColumnSettings as $billColumnSetting): ?>
        <td class="bqAmountCell" colspan="2">
            <?php
            $amount = 0;

            if ($rate && $rate != 0)
            {
                $amount = Utilities::displayScientific($rate, 10, array( 
                    'decimal_places' => $priceFormatting[2],
                    'decimal_points' => $priceFormatting[0],
                    'thousand_separator' => $amtCommaRemove ? '' : $priceFormatting[1]
                ), $printFullDecimal);
            }
            else
            {
                $amount = null;
            }

            echo ! $printNoPrice ? $amount : null;
            ?>
        </td>
        <?php endforeach?>
    <?php endif; ?>