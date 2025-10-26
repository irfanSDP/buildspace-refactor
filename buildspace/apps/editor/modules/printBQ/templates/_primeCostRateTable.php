<table cellpadding="0" cellspacing="0" class="mainTable" style="min-height:0!important;max-height:0;">
    <tr>
        <td style="width:175px;padding-right:4px;text-align:left;" class="<?php echo ($itemRow and $itemRow[3] == -2) ? 'pcRateTotal':null?>">
            <?php
                if($itemRow and !is_null($itemRow[6]) and $itemRow[3] != -1)
                {
                    echo $itemRow[2]." (". (! $printNoPrice ? number_format($itemRow[6], $priceFormatting[2], $priceFormatting[0], $priceFormatting[1]) : "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;")."%)";
                }
                elseif($itemRow and $itemRow[3] != -1)
                {
                    echo $itemRow[2];
                }
                else
                {
                    echo '&nbsp;';
                }
            ?>
        </td>
        <td class="<?php echo ($itemRow and $itemRow[3] == -2) ? 'pcRateTotal':null?>" style="text-align:right;width:<?php echo ($printAmountOnly) ? 60 : 20; ?>px;">
            <?php echo ($itemRow and $itemRow[3] != -1) ? $currency->currency_code : '&nbsp;'?>
        </td>
        <td class="<?php echo ($itemRow and $itemRow[3] == -2) ? 'pcRateTotal':null?>" style="text-align:right;width:<?php echo ($printAmountOnly) ? 200 : 100; ?>px;">
            <?php echo (($itemRow[2] == BillItem::ITEM_TYPE_PC_SUPPLIER_RATE_TEXT OR ! $printNoPrice) and $itemRow and $itemRow[3] != -1) ? Utilities::displayScientific($itemRow[7], 11, array( 
                            'decimal_places' => $priceFormatting[2],
                            'decimal_points' => $priceFormatting[0],
                            'thousand_separator' => $rateCommaRemove ? '' : $priceFormatting[1]
                        ), false) : null; ?>
        </td>
    </tr>
</table>

