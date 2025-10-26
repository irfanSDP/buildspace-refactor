<table cellpadding="0" cellspacing="0" class="mainTable">
    <tr>
        <td colspan="<?php echo ($printDollarAndCentColumn) ? 5 : 4; ?>">
            <table cellpadding="0" cellspacing="0" class="headerTable" style="width: 100%;">
                <tr>
                    <td class="leftHeader" style="width: 60%;"><?php echo (strlen($topLeftRow1) > 45) ? substr($topLeftRow1,0,45).'...' : $topLeftRow1; ?></td>
                    <td class="rightHeader" style="width: 40%;"><?php echo (strlen($topRightRow1) > 32) ? substr($topRightRow1,0,32).'...' : $topRightRow1; ?></td>
                </tr>
                <tr>
                    <td class="leftHeader" style="width: 60%;"><?php echo (strlen($topLeftRow2) > 45) ? substr($topLeftRow2,0,45).'...' : $topLeftRow2; ?></td>
                    <td class="rightPageElement" style="width: 40%;"><?php echo (strlen($summaryHeaderDescription) > 32) ? substr($summaryHeaderDescription,0,32).'...' : $summaryHeaderDescription; ?></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td class="bqHeadCell" style="min-width:35px;width:35px;"></td>
        <td class="bqHeadCell" style="min-width:365px;width:365px;"><?php echo $descHeader; ?></td>
        <td class="bqHeadCell" style="min-width:115px;width:115px;"><?php echo $summaryPageNoPrefix; ?></td>

        <?php if ( $printDollarAndCentColumn ): ?>
        <td class="bqHeadCell" style="min-width:115px;width:115px;"><?php echo $currencyFormat[0]; ?></td>
        <td class="bqHeadCell" style="min-width:40px;width:40px;<?php echo ! $closeGrid ? 'border-right: none;' : null; ?>"><?php echo $currencyFormat[1]; ?></td>
        <?php else: ?>
        <td class="bqHeadCell" style="min-width:155px;width:155px;<?php echo ! $closeGrid ? 'border-right: none;' : null; ?>"><?php echo $amtHeader; ?></td>
        <?php endif; ?>
    </tr>
    <?php
    $rowCount = 0;
    $billColumnSettingId = $billColumnSettings[0]['id'];//single type will always return 1 bill column setting
    $billColumnSettingQty = $billColumnSettings[0]['quantity'];//single type will always return 1 bill column setting

    for($x=0; $x < $maxRows; $x++):
        $itemRow = array_key_exists($x, $summaryPage) ? $summaryPage[$x] : false;
        $rowCount++;
    ?>
        <tr>
            <td class="bqCounterCell">&nbsp;</td>
            <td class="<?php echo ($itemRow[1] == sfBuildspaceBQPageGenerator::ROW_TYPE_SUMMARY_PAGE_TITLE) ? 'summaryPageInGridHeader' : 'bqDescriptionCell'; ?>" style="border:none!important;">
                <pre><?php echo trim($itemRow[0]); ?></pre>
            </td>
            <td class="bqUnitCell" style="border-left:1px solid #000;"><?php echo $itemRow[3]?></td>

            <?php
            if($printGrandTotalQty)
            {
                $elementAmount = $itemRow[2][0];
            }
            else
            {
                $elementAmount = is_array($itemRow[2]) && array_key_exists($billColumnSettingId, $itemRow[2]) ? $itemRow[2][$billColumnSettingId] : 0;
            }

            $elementAmount = number_format($elementAmount, $priceFormatting[2], $priceFormatting[0], ($amtCommaRemove) ? '' : $priceFormatting[1]);
            ?>

            <?php if ( $printDollarAndCentColumn ): $elementAmount = explode($priceFormatting[0], $elementAmount); ?>
            <td class="bqAmountCell" style="border-left:1px solid #000;">
                <?php echo ! $printNoPrice && isset($elementAmount[0]) && $elementAmount[0] != 0 ? $elementAmount[0] : null; ?>
            </td>
            <td class="bqAmountCell" style="border-left:1px solid #000;<?php echo ! $closeGrid ? 'border-right: none;' : null; ?>">
                <?php echo ! $printNoPrice && isset($elementAmount[1]) && $elementAmount[0] != 0 ? $elementAmount[1] : null; ?>
            </td>
            <?php else: ?>
            <td class="bqAmountCell" style="border-left:1px solid #000;<?php echo ! $closeGrid ? 'border-right: none;' : null; ?>">
                <?php echo ! $printNoPrice && $elementAmount != 0 ? $elementAmount : null; ?>
            </td>
            <?php endif; ?>
        </tr>
        <?php unset($elementAmount); endfor; ?>
    <tr style="border-bottom: 1px solid black;">
        <td class="bqCounterCell">&nbsp;</td>
        <td class="bqDescriptionCell" style="border:none!important;"></td>
        <td class="bqUnitCell" style="border-left:1px solid #000;"></td>

        <?php if ( $printDollarAndCentColumn ): ?>
        <td class="bqAmountCell" style="border-left:1px solid #000;"></td>
        <td class="bqAmountCell" style="border-left:1px solid #000;<?php echo ! $closeGrid ? 'border-right: none;' : null; ?>"></td>
        <?php else: ?>
        <td class="bqAmountCell" style="border-left:1px solid #000;<?php echo ! $closeGrid ? 'border-right: none;' : null; ?>"></td>
        <?php endif; ?>
    </tr>
    <?php if($isLastPage):?>
        <?php if(!$printGrandTotalQty):?>
    <tr>
        <td style="border-left:1px solid #000;border-right:1px solid #000;text-align:right;padding: 5px 5px 5px 0;" colspan="3"><?php echo $totalPerUnitPrefix; ?> (<?php echo $currency->currency_code?>)</td>

        <?php $totalPerUnit = number_format(isset($summaryPage['total_per_unit'][$billColumnSettingId]) ? $summaryPage['total_per_unit'][$billColumnSettingId] : 0, $priceFormatting[2], $priceFormatting[0], $priceFormatting[1]);?>

        <?php if ( $printDollarAndCentColumn ): $totalPerUnit = explode($priceFormatting[0], $totalPerUnit); ?>
        <td style="border-bottom:1px solid #000; border-right:1px solid #000;text-align:right;padding: 5px 5px 5px 0;">
            <?php echo ! $printNoPrice ? $totalPerUnit[0] : null;?>
        </td>
        <td style="border-bottom:1px solid #000; border-right:1px solid #000;text-align:right;padding: 5px 5px 5px 0;<?php echo ! $closeGrid ? 'border-right: none;' : null; ?>">
            <?php echo ! $printNoPrice && isset($totalPerUnit[1]) ? $totalPerUnit[1] : null;?>
        </td>
        <?php else: ?>
        <td style="border-bottom:1px solid #000; border-right:1px solid #000;text-align:right;padding: 5px 5px 5px 0;<?php echo ! $closeGrid ? 'border-right: none;' : null; ?>">
            <?php echo ! $printNoPrice ? $totalPerUnit : null;?>
        </td>
        <?php endif; ?>
    </tr>
    <tr>
        <td style="border-left:1px solid #000;border-right:1px solid #000;text-align:right;padding: 1px 5px 1px 0; border-bottom: none !important;" colspan="3"><?php echo $totalUnitPrefix; ?> X</td>

        <?php if ( $printDollarAndCentColumn ): ?>
        <td colspan="2" style="border-bottom:1px solid #000;border-right:1px solid #000;text-align:right;padding: 2px 5px 2px 0;<?php echo ! $closeGrid ? 'border-right: none;' : null; ?>"><?php echo $billColumnSettingQty?></td>
        <?php else: ?>
        <td style="border-bottom:1px solid #000;border-right:1px solid #000;text-align:right;padding: 1px 5px 1px 0;<?php echo ! $closeGrid ? 'border-right: none;' : null; ?>"><?php echo $billColumnSettingQty?></td>
        <?php endif; ?>
    </tr>
        <?php endif;?>
    <tr style="border-left:1px solid #000;border-bottom: 1px solid #000;">
        <td style="border-right:1px solid #000;text-align:right;padding: 5px 5px 5px 0;font-weight:bold;" colspan="3"><?php echo $tenderPrefix; ?> (<?php echo $currency->currency_code?>)</td>

        <?php
        if($printGrandTotalQty)
        {
            $totalPerType = $summaryPage['total_per_unit'][0];
        }
        else
        {
            $totalPerType = isset($summaryPage['total_per_unit'][$billColumnSettingId]) ? $summaryPage['total_per_unit'][$billColumnSettingId] * $billColumnSettingQty : 0;
        }
            $totalPerType = number_format($totalPerType, $priceFormatting[2], $priceFormatting[0], ($amtCommaRemove) ? '' : $priceFormatting[1]);
        ?>

        <?php if ( $printDollarAndCentColumn ): $totalPerType = explode($priceFormatting[0], $totalPerType); ?>
        <td style="border-right:1px solid #000;text-align:right;padding: 5px 5px 5px 0;font-weight:bold;">
            <?php echo ! $printNoPrice ? $totalPerType[0] : null; ?>
        </td>
        <td style="border-right:1px solid #000;text-align:right;padding: 5px 5px 5px 0;font-weight:bold;<?php echo ! $closeGrid ? 'border-right: none;' : null; ?>">
            <?php echo ! $printNoPrice && isset($totalPerType[1]) ? $totalPerType[1] : null; ?>
        </td>
        <?php else: ?>
        <td style="border-right:1px solid #000;text-align:right;padding: 5px 5px 5px 0;font-weight:bold;<?php echo ! $closeGrid ? 'border-right: none;' : null; ?>">
            <?php echo ! $printNoPrice ? $totalPerType : null; ?>
        </td>
        <?php endif; ?>
    </tr>
    <?php else: ?>
    <tr style="border-bottom: 1px solid black;">
        <td style="border-left:1px solid #000;border-right:1px solid #000;text-align:right;padding: 5px 5px 5px 0;" colspan="3">
            Carried forward to the next Summary Page
        </td>

        <?php
            $totalPerUnit = $printGrandTotalQty ? number_format($summaryPage['total_per_unit'][0], $priceFormatting[2], $priceFormatting[0], $priceFormatting[1]) : number_format($summaryPage['total_per_unit'][$billColumnSettingId], $priceFormatting[2], $priceFormatting[0], $priceFormatting[1]);
        ?>

        <?php if ( $printDollarAndCentColumn ): $totalPerUnit = explode($priceFormatting[0], $totalPerUnit); ?>
        <td style="border-bottom:1px solid #000; border-right:1px solid #000;text-align:right;padding: 5px 5px 5px 0;">
            <?php echo ! $printNoPrice ? $totalPerUnit[0] : null;?>
        </td>
        <td style="border-bottom:1px solid #000; border-right:1px solid #000;text-align:right;padding: 5px 5px 5px 0;<?php echo ! $closeGrid ? 'border-right: none;' : null; ?>">
            <?php echo ! $printNoPrice && isset($totalPerUnit[1]) ? $totalPerUnit[1] : null;?>
        </td>
        <?php else: ?>
        <td style="border-bottom:1px solid #000; border-right:1px solid #000;text-align:right;padding: 5px 5px 5px 0;<?php echo ! $closeGrid ? 'border-right: none;' : null; ?>">
            <?php echo ! $printNoPrice ? $totalPerUnit : null;?>
        </td>
        <?php endif; ?>
    </tr>
    <?php endif?>

    <tr>
        <td colspan="<?php echo ($printDollarAndCentColumn) ? 5 : 4; ?>">
            <table cellpadding="0" cellspacing="0" class="footer-table" style="width: 100%;">
                <tr>
                    <td style="width: 40%;" class="leftFooter"><?php echo (strlen($botLeftRow1) > 32) ? substr($botLeftRow1,0,32).'...' : $botLeftRow1; ?></td>
                    <td style="width: 20%; text-align: center;" class="pageFooter">&nbsp;</td>
                    <td style="width: 40%;">&nbsp;</td>
                </tr>
                <tr>
                    <td style="width: 40%;" class="leftFooter"><?php echo (strlen($botLeftRow2) > 32) ? substr($botLeftRow2,0,32).'...' : $botLeftRow2; ?></td>
                    <td style="width: 20%; text-align: center;" class="pageFooter"><?php echo $pageNo; ?></td>
                    <td style="width: 40%; text-align: right;"><?php echo $printDateOfPrinting ? '<span style="font-weight:bold;">Date of Printing: </span><span style="font-style: italic;">'.date('d/M/Y').'</span>' : '&nbsp;'; ?></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
