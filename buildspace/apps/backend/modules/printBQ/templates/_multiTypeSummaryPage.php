<table cellpadding="0" cellspacing="0" class="mainTable">
    <tr>
        <td class="leftHeader" colspan="3"><?php echo $topLeftRow1; ?></td>
        <td class="rightHeader" colspan="<?php echo count($billColumnSettings)?>"><?php echo $topRightRow1; ?></td>
    </tr>
    <tr>
        <td class="leftHeader" colspan="3"><?php echo $topLeftRow2; ?></td>
        <td class="rightPageElement" colspan="<?php echo count($billColumnSettings)?>"><?php echo $summaryHeaderDescription; ?></td>
    </tr>
    <tr>
        <td class="bqHeadCell" style="min-width:35px;width:35px;" rowspan="2"></td>
        <td class="bqHeadCell" style="min-width:375px;width:375px;border-right:none;" rowspan="2"><?php echo $descHeader; ?></td>
        <td class="bqHeadCell" style="min-width:120px;width:120px;" rowspan="2"><?php echo $summaryPageNoPrefix; ?></td>
        <td class="bqHeadCell" colspan="<?php echo count($billColumnSettings)?>"><?php echo $amtHeader; ?></td>
    </tr>
    <tr>
        <?php foreach($billColumnSettings as $billColumnSetting):?>
            <?php $amountWidth = (count($billColumnSettings) <= 2) ? 220 : 150;  ?>
            <td class="bqHeadCell" style="min-width:<?php echo $amountWidth; ?>;width:<?php echo $amountWidth; ?>;"><?php echo $billColumnSetting['name']?></td>
        <?php endforeach?>
    </tr>
    <?php
    $rowCount = 0;
    $totalAmount = 0;
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
            <?php foreach($billColumnSettings as $idx => $billColumnSetting):?>
            <td class="bqAmountCell">
                <?php
                $elementAmount = is_array($itemRow[2]) && array_key_exists($billColumnSetting['id'], $itemRow[2]) ? $itemRow[2][$billColumnSetting['id']] : 0;

                echo ! $printNoPrice && $elementAmount != 0 ? number_format($elementAmount, $priceFormatting[2], $priceFormatting[0], ($amtCommaRemove) ? '' : $priceFormatting[1]) : null;
                ?>
            </td>
            <?php endforeach?>
        </tr>
        <?php endfor; ?>
    <tr style="border-bottom: 1px solid black;">
        <td class="bqCounterCell">&nbsp;</td>
        <td class="bqDescriptionCell"></td>
        <td class="bqUnitCell" style="border-left:1px solid #000;"></td>
        <?php foreach($billColumnSettings as $billColumnSetting):?>
        <td class="bqAmountCell"></td>
        <?php endforeach?>
    </tr>
    <?php if($isLastPage):?>
    <tr>
        <td style="border-left:1px solid #000;border-right:1px solid #000;text-align:right;padding: 5px 5px 5px 0;" colspan="3"><?php echo $totalPerUnitPrefix; ?> (<?php echo $currency->currency_code?>)</td>
        <?php foreach($billColumnSettings as $billColumnSetting):?>
        <td style="border-bottom:1px solid #000;border-right:1px solid #000;text-align:right;padding-right:5px;">
            <?php echo ! $printNoPrice ? number_format(isset($summaryPage['total_per_unit'][$billColumnSetting['id']]) ? $summaryPage['total_per_unit'][$billColumnSetting['id']] : 0, $priceFormatting[2], $priceFormatting[0], ($amtCommaRemove) ? '' : $priceFormatting[1]) : null;?>
        </td>
        <?php endforeach?>
    </tr>
    <tr>
        <td style="border-left:1px solid #000;border-right:1px solid #000;text-align:right;padding: 1px 5px 1px 0;" colspan="3"><?php echo $totalUnitPrefix; ?> X</td>
        <?php foreach($billColumnSettings as $billColumnSetting):?>
        <td style="border-bottom:1px solid #000;border-right:1px solid #000;text-align:right;padding: 1px 5px 1px 0;"><?php echo $billColumnSetting['quantity']?></td>
        <?php endforeach?>
    </tr>
    <tr>
        <td style="border-left:1px solid #000;border-right:1px solid #000;text-align:right;padding: 5px 5px 5px 0;" colspan="3"><?php echo $totalPerTypePrefix; ?> (<?php echo $currency->currency_code?>)</td>
        <?php foreach($billColumnSettings as $billColumnSetting):?>
        <td style="border-bottom:1px solid #000;border-right:1px solid #000;text-align:right;padding-right:5px;">
            <?php
            $totalPerType = isset($summaryPage['total_per_unit'][$billColumnSetting['id']]) ? $summaryPage['total_per_unit'][$billColumnSetting['id']] * $billColumnSetting['quantity'] : 0;
            echo ! $printNoPrice ? number_format($totalPerType, $priceFormatting[2], $priceFormatting[0], ($amtCommaRemove) ? '' : $priceFormatting[1]) : null;
            $totalAmount += $totalPerType;
            ?>
        </td>
        <?php endforeach?>
    </tr>
    <tr style="border-bottom: 1px solid black;">
        <td style="border-left:1px solid #000;border-right:1px solid #000;text-align:right;padding-right:5px;font-weight:bold;" colspan="3"><?php echo $tenderPrefix; ?> (<?php echo $currency->currency_code?>)</td>
        <td style="border-right:1px solid #000;text-align:right;padding: 5px 5px 5px 0;font-weight:bold;" colspan="<?php echo count($billColumnSettings)?>">
            <?php
                echo ! $printNoPrice ? number_format($totalAmount, $priceFormatting[2], $priceFormatting[0], ($amtCommaRemove) ? '' : $priceFormatting[1]) : '&nbsp;';
            ?>
        </td>
    </tr>
    <?php else: ?>
    <tr>
        <td style="border-bottom: 1px solid black;border-left:1px solid #000;border-right:1px solid #000;text-align:right;padding: 5px 5px 5px 0;" colspan="3">
            Carried forward to the next Summary Page
        </td>
        <?php foreach($billColumnSettings as $billColumnSetting):?>
        <td style="border-bottom:1px solid #000;border-right:1px solid #000;text-align:right;padding: 5px 5px 5px 0;">
            <?php echo ! $printNoPrice ? number_format($summaryPage['total_per_unit'][$billColumnSetting['id']], $priceFormatting[2], $priceFormatting[0], ($amtCommaRemove) ? '' : $priceFormatting[1]) : null;?>
        </td>
        <?php endforeach?>
    </tr>
    <?php endif?>
    <tr>
        <td class="pageFooter" colspan="<?php echo count($billColumnSettings) + 4; ?>">
            <table cellpadding="0" cellspacing="0" class="footer-table" style="width: 100%;">
                <tr>
                    <td style="width: 40%;" class="leftFooter"><?php echo $botLeftRow1; ?></td>
                    <td style="width: 20%; text-align: center;" class="pageFooter">&nbsp;</td>
                    <td style="width: 40%;">&nbsp;</td>
                </tr>
                <tr>
                    <td style="width: 40%;" class="leftFooter"><?php echo $botLeftRow2; ?></td>
                    <td style="width: 20%; text-align: center;" class="pageFooter"><?php echo $pageNo; ?></td>
                    <td style="width: 40%; text-align: right;"><?php echo $printDateOfPrinting ? '<span style="font-weight:bold;">Date of Printing: </span><span style="font-style: italic;">'.date('d/M/Y').'</span>' : '&nbsp;'; ?></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>