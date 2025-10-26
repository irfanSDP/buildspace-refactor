<table cellpadding="0" cellspacing="0" class="mainTable" style="min-height:0!important;max-height:0;">
    <tr>
        <td style="width:175px;padding-right:4px;text-align:left;" class="<?php echo ($itemRow and $itemRow[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_LEVEL] == -2) ? 'pcRateTotal':null?>">
        <?php
            if($itemRow and !is_null($itemRow[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_RATE]) and $itemRow[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_LEVEL] != -1)
            {
                echo trim(preg_replace('/\s+/', ' ', $itemRow[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_DESCRIPTION]))." (". (! $printNoPrice ? number_format($itemRow[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_RATE], $priceFormatting[2], $priceFormatting[0], $priceFormatting[1]) : "")." %)";
            }
            elseif($itemRow and $itemRow[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_LEVEL] != -1)
            {
                echo trim(preg_replace('/\s+/', ' ', $itemRow[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_DESCRIPTION]));
            }
            ?>
        </td>
        <td class="<?php echo ($itemRow and $itemRow[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_LEVEL] == -2) ? 'pcRateTotal':null?>"  style="text-align:right;width:<?php echo ($printAmountOnly) ? 60 : 20; ?>px;">
        <?php echo ($itemRow and $itemRow[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_LEVEL] != -1) ? $currency->currency_code : '&nbsp;'?>
        </td>
        <td class="<?php echo ($itemRow and $itemRow[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_LEVEL] == -2) ? 'pcRateTotal':null?>" style="text-align:right;width:<?php echo ($printAmountOnly) ? 200 : 100; ?>px;">
        <?php echo (($itemRow[2] == BillItem::ITEM_TYPE_PC_SUPPLIER_RATE_TEXT OR ! $printNoPrice) and $itemRow and $itemRow[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_LEVEL] != -1) ? Utilities::displayScientific($itemRow[sfBuildspaceBQPageGenerator::ROW_BILL_ITEM_QTY_PER_UNIT], 11, array( 
                'decimal_places' => $priceFormatting[2],
                'decimal_points' => $priceFormatting[0],
                'thousand_separator' => $rateCommaRemove ? '' : $priceFormatting[1]
            ), false) : null; ?>
        </td>
    </tr>
</table>