<table cellpadding="0" cellspacing="0" class="mainTable">
    <tr>
        <td colspan="<?php echo ($printDollarAndCentColumn) ? 7 : 6; ?>">
            <?php include_partial('singleTypeHeader', array('topLeftRow1' => $topLeftRow1, 'topRightRow1' => $topRightRow1, 'topLeftRow2' => $topLeftRow2, 'elementHeaderDescription' => $elementHeaderDescription, 'printElementTitle' => $printElementTitle, 'printDollarAndCentColumn' => $printDollarAndCentColumn, 'printAmountOnly' => false)); ?>
        </td>
    </tr>
    <tr>
        <td class="bqHeadCell" style="min-width:35px;width:35px;"></td>
        <td class="bqHeadCell" style="border-right:none!important;min-width:395px;width:395px;"><?php echo $descHeader; ?></td>
        <td class="bqHeadCell" style="border-left:none!important;border-right:none!important;min-width:10px;width:10px;"></td>
        <td class="bqHeadCell" style="border-left:none!important;border-right:1px solid #000;min-width:10px;width:10px;"></td>
        <td class="bqHeadCell" style="border-left:none!important;border-right:none!important;min-width:15px;width:15px;"></td>

        <?php if ( $printDollarAndCentColumn ): ?>
        <td class="bqHeadCell" style="border-left:none!important;min-width:120px;width:120px;"><?php echo $currencyFormat[0]; ?></td>
        <td class="bqHeadCell" style="min-width:85px;width:85px;<?php echo ! $closeGrid ? 'border-right: none;' : null; ?>"><?php echo $currencyFormat[1]; ?></td>
        <?php else: ?>
        <td class="bqHeadCell" style="border-left:none!important;min-width:205px;width:205px;<?php echo ! $closeGrid ? 'border-right: none;' : null; ?>"><?php echo $amtHeader; ?></td>
        <?php endif; ?>
    </tr>

    <?php
    $rowCount = 0;
    $billColumnSettingId = $billColumnSettings[0]['id'];//single type will always return 1 bill column setting

    for($x=0; $x < $maxRows; $x++):
        $itemRow = array_key_exists($x, $collectionPage) ? $collectionPage[$x] : false;
        $rowCount++;

        if ( ! $printElementInGrid AND $itemRow AND $itemRow[1] == sfBuildspaceBQPageGenerator::ROW_TYPE_ELEMENT )
        {
            $x++;
            continue;
        }

        if($itemRow and $itemRow[1] == sfBuildspaceBQPageGenerator::ROW_TYPE_ELEMENT)
        {
            $descriptionClass = 'elementHeader';

            if ( $alignElementTitleToTheLeft )
            {
                $descriptionClass .= ' alignLeft';
            }
            else
            {
                $descriptionClass .= ' alignCenter';
            }
        }
        elseif($itemRow and $itemRow[1] == sfBuildspaceBQPageGenerator::ROW_TYPE_LAST_COLLECTION)
        {
            $descriptionClass = 'bqDescriptionCell collectionLastDescription';
        }
        elseif($itemRow and $itemRow[1] == sfBuildspaceBQPageGenerator::ROW_TYPE_COLLECTION_TITLE)
        {
            $descriptionClass = 'bqDescriptionCell collectionTitle';
        }
        else
        {
            $descriptionClass = 'bqDescriptionCell';
        }
        ?>
        <tr>
            <td class="bqCounterCell"></td>
            <td class="<?php echo $descriptionClass?>" style="padding-left: 6px; border:none!important;">
                <pre><?php echo $itemRow ? trim($itemRow[0]) : '&nbsp;'?></pre>
            </td>
            <td colspan="2"></td>

            <?php

            if ( $itemRow[1] == sfBuildspaceBQPageGenerator::ROW_TYPE_COLLECTION_TITLE )
            {
                if($printGrandTotalQty)
                {
                    $rowAmount = $itemRow[2][0];
                }
                else
                {
                    $rowAmount = (isset ($itemRow[2][$billColumnSettingId])) ? $itemRow[2][$billColumnSettingId] : 0;
                }

                $rowAmount = ($rowAmount != 0) ? number_format($rowAmount, $priceFormatting[2], $priceFormatting[0], ($amtCommaRemove) ? '' : $priceFormatting[1]) : null;
            }
            else
            {
                if($itemRow[2] instanceof SplFixedArray)
                {
                    $rowAmount = $itemRow[2][0][1];
                    $rowAmount = number_format($rowAmount, $priceFormatting[2], $priceFormatting[0], ($amtCommaRemove) ? '' : $priceFormatting[1]);
                }
                else
                {
                    $rowAmount = ($printGrandTotalQty && !is_null($itemRow[2])) ? number_format($itemRow[2], $priceFormatting[2], $priceFormatting[0], ($amtCommaRemove) ? '' : $priceFormatting[1]) : null;
                }
            }
            ?>

            <?php if ( $printDollarAndCentColumn ): $rowAmount = ($rowAmount) ? explode($priceFormatting[0], $rowAmount) : null; ?>
            <td class="bqAmountCell" style="border-left:1px solid #000;" colspan="2">
                <?php echo ! $printNoPrice && isset($rowAmount[0]) ? $rowAmount[0] : null; ?>
            </td>
            <td class="bqAmountCell" style="border-left:1px solid #000;<?php echo ! $closeGrid ? 'border-right: none;' : null; ?>">
                <?php echo ! $printNoPrice && isset($rowAmount[1]) ? $rowAmount[1] : null; ?>
            </td>
            <?php else: ?>
            <td class="bqAmountCell" style="border-left:1px solid #000;<?php echo ! $closeGrid ? 'border-right: none;' : null; ?>" colspan="2">
                <?php echo ! $printNoPrice ? $rowAmount : null; ?>
            </td>
            <?php endif; ?>
        </tr>
        <?php unset($rowAmount); endfor; ?>
    <tr>
        <td class="bqCounterCell"></td>
        <td class="bqDescriptionCell" style="border:none!important;"></td>
        <td colspan="2"></td>

        <?php if ( $printDollarAndCentColumn ): ?>
        <td class="bqAmountCell" style="border-left:1px solid #000;" colspan="2"></td>
        <td class="bqAmountCell" style="border-left:1px solid #000;<?php echo ! $closeGrid ? 'border-right: none;' : null; ?>"></td>
        <?php else: ?>
        <td class="bqAmountCell" style="border-left:1px solid #000;<?php echo ! $closeGrid ? 'border-right: none;' : null; ?>" colspan="2"></td>
        <?php endif; ?>
    </tr>

    <?php if ( $isLastPage ): ?>
    <tr>
        <?php
        if($printGrandTotalQty)
        {
            $totalAmount = $collectionPage['total_amount'];
        }
        else
        {
            $totalAmount = array_key_exists($billColumnSettingId, $collectionPage['total_amount']) ? $collectionPage['total_amount'][$billColumnSettingId] : 0;
        }

        $totalAmount = number_format($totalAmount, $priceFormatting[2], $priceFormatting[0], ($amtCommaRemove) ? '' : $priceFormatting[1]);
        ?>

        <td class="footer" style="padding-right:5px;" colspan="4">Carried To Summary (<?php echo $currency->currency_code; ?>)</td>

        <?php if ( $printDollarAndCentColumn ): $totalAmount = explode($priceFormatting[0], $totalAmount); ?>
        <td class="footerSumAmount" colspan="2">
            <?php echo ! $printNoPrice ? $totalAmount[0] : null; ?>
        </td>
        <td class="footerSumAmount" style="<?php echo ! $closeGrid ? 'border-right: none;' : null; ?>">
            <?php echo ! $printNoPrice && isset($totalAmount[1]) ? $totalAmount[1] : null; ?>
        </td>
        <?php else: ?>
        <td class="footerSumAmount" style="<?php echo ! $closeGrid ? 'border-right: none;' : null; ?>" colspan="2">
            <?php echo ! $printNoPrice ? $totalAmount : null; ?>
        </td>
        <?php endif; ?>
    </tr>
    <?php else: ?>
    <tr>
        <?php
        if($printGrandTotalQty){
            $totalAmount = $collectionPage['total_amount'];
        }else{
            $totalAmount = array_key_exists($billColumnSettingId, $collectionPage['total_amount']) ? $collectionPage['total_amount'][$billColumnSettingId] : 0;
        }
        $totalAmount = number_format($totalAmount, $priceFormatting[2], $priceFormatting[0], ($amtCommaRemove) ? '' : $priceFormatting[1]);
        ?>

        <td class="footer" style="padding-right:5px;" colspan="4">Carried forward to next Collection page</td>

        <?php if ( $printDollarAndCentColumn ): $totalAmount = explode($priceFormatting[0], $totalAmount); ?>
        <td class="footerSumAmount" colspan="2">
            <?php echo ! $printNoPrice ? $totalAmount[0] : null; ?>
        </td>
        <td class="footerSumAmount">
            <?php echo ! $printNoPrice && isset($totalAmount[1]) ? $totalAmount[1] : null; ?>
        </td>
        <?php else: ?>
        <td class="footerSumAmount" colspan="2">
            <?php echo ! $printNoPrice ? $totalAmount : null; ?>
        </td>
        <?php endif; ?>
    </tr>
    <?php endif; ?>

    <tr>
        <td colspan="<?php echo ($printDollarAndCentColumn) ? 7 : 6; ?>">
            <table cellpadding="0" cellspacing="0" class="footer-table" style="width: 100%;">
                <tr>
                    <td style="width: 40%;" class="leftFooter"><?php echo (strlen($botLeftRow1) > 32) ? substr($botLeftRow1,0,32).'...' : $botLeftRow1; ?></td>
                    <td style="width: 20%; text-align: center;" class="pageFooter">&nbsp;</td>
                    <td style="width: 40%;">&nbsp;</td>
                </tr>
                <tr>
                    <td style="width: 40%;" class="leftFooter"><?php echo (strlen($botLeftRow2) > 32) ? substr($botLeftRow2,0,32).'...' : $botLeftRow2; ?></td>
                    <td style="width: 20%; text-align: center;" class="pageFooter">
                        <?php echo trim("{$pageNoPrefix}{$elementCount}/{$pageCount}"); ?>
                    </td>
                    <td style="width: 40%; text-align: right;"><?php echo $printDateOfPrinting ? '<span style="font-weight:bold;">Date of Printing: </span><span style="font-style: italic;">'.date('d/M/Y').'</span>' : '&nbsp;'; ?></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>