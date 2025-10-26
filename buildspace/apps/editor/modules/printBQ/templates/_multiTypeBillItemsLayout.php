<?php $totalAmount = array()?>
<table cellpadding="0" cellspacing="0" class="mainTable">

    <?php include_partial('multiTypeHeader', array('printAmountOnly' => $printAmountOnly, 'topLeftRow1' => $topLeftRow1, 'topRightRow1' => $topRightRow1, 'topLeftRow2' => $topLeftRow2, 'elementHeaderDescription' => $elementHeaderDescription, 'billColumnSettings' => $billColumnSettings, 'printElementTitle' => $printElementTitle)); ?>

    <tr>
        <td class="bqHeadCell" style="min-width:40px;width:40px;" rowspan="2">Item</td>

        <?php if ( ! $printAmountOnly ): ?>
            <td class="bqHeadCell" style="min-width:320px;width:320px;" rowspan="2">
                <?php echo $descHeader; ?>
            </td>
            <td class="bqHeadCell" style="min-width:50px;width:50px;" rowspan="2"><?php echo $unitHeader; ?></td>
            <?php $rateWidth = (count($billColumnSettings) <= 2) ? 120 : 80;  ?>
            <td class="bqHeadCell" style="min-width:<?php echo $rateWidth; ?>px;width:<?php echo $rateWidth; ?>px;" rowspan="2"><?php echo $rateHeader; ?></td>
            <?php foreach($billColumnSettings as $billColumnSetting):?>
            <?php
                $totalAmount[$billColumnSetting['id']] = 0;
            ?>
            <td class="bqHeadCell" colspan="2"><?php echo $billColumnSetting['name']?></td>
            <?php endforeach?>
        <?php else: ?>
            <td class="bqHeadCell" style="min-width:455px;width:455px;" rowspan="2"><?php echo $descHeader; ?></td>
            <td class="bqHeadCell" colspan="<?php echo 2 * count($billColumnSettings); ?>"><?php echo $amtHeader; ?></td>
        <?php endif; ?>
    </tr>
    <tr>
        <?php if ( ! $printAmountOnly ): ?>
            <?php foreach($billColumnSettings as $billColumnSetting):?>
            <?php $qtyWidth = (count($billColumnSettings) <= 2) ? 100 : 70;  ?>
            <td class="bqHeadCell" style="min-width:<?php echo $qtyWidth; ?>px;width:<?php echo $qtyWidth; ?>px;"><?php echo $qtyHeader; ?></td>
            <?php $amtWidth = (count($billColumnSettings) <= 2) ? 130 : 100;  ?>
            <td class="bqHeadCell" style="min-width:<?php echo $amtWidth; ?>px;width:<?php echo $amtWidth; ?>px;"><?php echo $amtHeader; ?></td>
            <?php endforeach; ?>
        <?php else: ?>
            <?php foreach($billColumnSettings as $billColumnSetting): $totalAmount[$billColumnSetting['id']] = 0; ?>
                <?php $amtWidth = (count($billColumnSettings) <= 2) ? 235 : 160;  ?>
                <td class="bqHeadCell"  style="min-width:<?php echo $amtWidth; ?>px;width:<?php echo $amtWidth; ?>px;" colspan="2"><?php echo $billColumnSetting['name']?></td>
            <?php endforeach; ?>
        <?php endif; ?>
    </tr>
    <?php
    $rowCount = 0;
    for($x=0; $x < $maxRows; $x++):
        $itemPadding = 0;

        $itemRow = array_key_exists($x, $itemPage) ? $itemPage[$x] : false;

        if ( $itemRow[4] == sfBuildspaceBQPageGenerator::ROW_TYPE_ELEMENT AND ! $printElementInGrid )
        {
            $x++;
            continue;
        }

        if ( $printElementInGridOnce AND $itemRow[4] == sfBuildspaceBQPageGenerator::ROW_TYPE_ELEMENT AND $pageCount > 1 )
        {
            $x++;
            continue;
        }

        $rate = $itemRow ? $itemRow[6] : null;

        $rowCount++;

        if ($itemRow and ($itemRow[4] == BillItem::TYPE_HEADER OR $itemRow[4] == BillItem::TYPE_HEADER_N))
        {
            $headerClass = 'bqHead'.$itemRow[3];
        }
        elseif($itemRow and $itemRow[4] == sfBuildspaceBQPageGenerator::ROW_TYPE_ELEMENT)
        {
            $headerClass = 'elementHeader';

            if ( $alignElementTitleToTheLeft )
            {
                $headerClass .= ' alignLeft';
            }
            else
            {
                $headerClass .= ' alignCenter';
            }
        }
        else
        {
            $headerClass = null;
        }

        if ( $indentItem AND $itemRow AND ($itemRow[4] != sfBuildspaceBQPageGenerator::ROW_TYPE_ELEMENT AND $itemRow[4] != BillItem::TYPE_HEADER AND $itemRow[4] != BillItem::TYPE_HEADER_N) )
        {
            $itemPadding = 15;
        }
        else
        {
            $itemPadding = 6;
        }
        ?>
        <tr>
            <td class="bqCounterCell"><?php echo $itemRow ? $itemRow[1] : '&nbsp;'?></td>
            <td class="bqDescriptionCell" style="padding-left: <?php echo $itemPadding; ?>px;">
                <?php if($itemRow[4] == sfBuildspaceBQPageGenerator::ROW_TYPE_PC_RATE):?>
                <?php include_partial('printBQ/primeCostRateTable', array('printAmountOnly' => $printAmountOnly, 'currency'=>$currency,'itemRow'=>$itemRow,'priceFormatting'=>$priceFormatting, 'printNoPrice' => $printNoPrice, 'printFullDecimal' => $printFullDecimal, 'rateCommaRemove' => $rateCommaRemove)) ?>
                <?php else:?>
                <?php $preClass = $headerClass ? $headerClass : 'description'?>
                <?php echo $itemRow ? '<pre class="'.$preClass.'">'.trim($itemRow[2]).'</pre>' : null?>
                <?php endif?>
            </td>

            <?php if ( ! $printAmountOnly ): ?>
                <?php if ( $itemRow[4] == BillItem::TYPE_ITEM_LUMP_SUM_PERCENT AND ! empty($itemRow[0]) ): ?>
                <td class="bqUnitCell">%</td>
                <?php else: ?>
                <td class="bqUnitCell"><?php echo $itemRow ? $itemRow[5] : '&nbsp;'?></td>
                <?php endif; ?>
            <?php endif; ?>

            <?php if ( $itemRow[4] == BillItem::TYPE_ITEM_RATE_ONLY ): ?>
                <?php include_partial('printBQ/multiTypeItemTypes/multiTypeItemRateOnly', array(
                    'billColumnSettings' => $billColumnSettings,
                    'itemRow'            => $itemRow,
                    'rate'               => $rate,
                    'priceFormatting'    => $priceFormatting,
                    'printNoPrice'       => $printNoPrice,
                    'printFullDecimal'   => $printFullDecimal,
                    'rateCommaRemove'    => $rateCommaRemove,
                    'amtCommaRemove'     => $amtCommaRemove,
                    'printAmountOnly'    => $printAmountOnly,
                ));
            ?>

            <?php elseif ( $itemRow[4] == BillItem::TYPE_ITEM_LUMP_SUM ): ?>
                <?php
                foreach($billColumnSettings as $billColumnSetting) {
                    $billColumnSettingId = $billColumnSetting['id'];

                    $includeStatus = $itemRow && is_array($itemRow[8]) && array_key_exists($billColumnSettingId, $itemRow[8]) ? $itemRow[8][$billColumnSettingId] : 0;

                    $amount = 0;

                    if ($rate && $rate != 0)
                    {
                        $amount = sfBuildspaceBQMasterFunction::gridCurrencyRoundingFormat($rate * 1);

                        if ( $includeStatus )
                        {
                            $totalAmount[$billColumnSettingId] += $amount;
                        }
                    }
                }

                include_partial('printBQ/multiTypeItemTypes/multiTypeItemLSAmt', array(
                    'billColumnSettings' => $billColumnSettings,
                    'itemRow'            => $itemRow,
                    'rate'               => $rate,
                    'priceFormatting'    => $priceFormatting,
                    'printNoPrice'       => $printNoPrice,
                    'printFullDecimal'   => $printFullDecimal,
                    'rateCommaRemove'    => $rateCommaRemove,
                    'amtCommaRemove'     => $amtCommaRemove,
                    'printAmountOnly'    => $printAmountOnly
                ));
            ?>

            <?php elseif ( $itemRow[4] == BillItem::TYPE_ITEM_LUMP_SUM_EXCLUDE ): ?>
                <?php
                foreach($billColumnSettings as $billColumnSetting) {
                    $billColumnSettingId = $billColumnSetting['id'];

                    $includeStatus = $itemRow && is_array($itemRow[8]) && array_key_exists($billColumnSettingId, $itemRow[8]) ? $itemRow[8][$billColumnSettingId] : 0;

                    $amount = 0;

                    if ($rate && $rate != 0)
                    {
                        $amount = sfBuildspaceBQMasterFunction::gridCurrencyRoundingFormat($rate * 1);

                        if ( $includeStatus )
                        {
                            $totalAmount[$billColumnSettingId] += $amount;
                        }
                    }
                }

                include_partial('printBQ/multiTypeItemTypes/multiTypeItemLSExclude', array(
                    'billColumnSettings' => $billColumnSettings,
                    'itemRow'            => $itemRow,
                    'quantity'           => $quantity,
                    'rate'               => $rate,
                    'priceFormatting'    => $priceFormatting,
                    'printFullDecimal'   => $printFullDecimal,
                    'printNoPrice'       => $printNoPrice,
                    'rateCommaRemove'    => $rateCommaRemove,
                    'amtCommaRemove'     => $amtCommaRemove,
                    'qtyCommaRemove'     => $qtyCommaRemove,
                    'printAmountOnly'    => $printAmountOnly
                ));
            ?>

            <?php elseif ( $itemRow[4] == BillItem::TYPE_ITEM_LUMP_SUM_PERCENT ): ?>
                <?php

                foreach($billColumnSettings as $billColumnSetting) {
                    $billColumnSettingId = $billColumnSetting['id'];

                    $includeStatus = $itemRow && is_array($itemRow[8]) && array_key_exists($billColumnSettingId, $itemRow[8]) ? $itemRow[8][$billColumnSettingId] : 0;

                    $amount = 0;

                    if ($rate && $rate != 0)
                    {
                        $amount = sfBuildspaceBQMasterFunction::gridCurrencyRoundingFormat($rate);

                        if ( $includeStatus )
                        {
                            $totalAmount[$billColumnSettingId] += $amount;
                        }
                    }

                    $quantity = $itemRow && is_array($itemRow[7]) && array_key_exists($billColumnSettingId, $itemRow[7]) && $itemRow[7][$billColumnSettingId] != 0 ? $itemRow[7][$billColumnSettingId] : 0;
                }

                include_partial('printBQ/multiTypeItemTypes/multiTypeItemLSPercent', array(
                    'billColumnSettings' => $billColumnSettings,
                    'itemRow'            => $itemRow,
                    'quantity'           => $quantity,
                    'rate'               => $rate,
                    'amount'             => $amount,
                    'priceFormatting'    => $priceFormatting,
                    'printNoPrice'       => $printNoPrice,
                    'printFullDecimal'   => $printFullDecimal,
                    'rateCommaRemove'    => $rateCommaRemove,
                    'amtCommaRemove'     => $amtCommaRemove,
                    'printAmountOnly'    => $printAmountOnly,
                ));
            ?>

            <?php else: ?>

                <?php if ( ! $printAmountOnly ): ?>

                    <?php if ( $itemRow[4] == sfBuildspaceBQPageGenerator::ROW_TYPE_PC_RATE ): ?>
                    <td class="bqRateCell">&nbsp;</td>
                    <?php else: ?>
                    <td class="bqRateCell"><?php echo ! $printNoPrice && $rate && $rate != 0 ? Utilities::displayScientific($rate, 9, array( 
                            'decimal_places' => $priceFormatting[2],
                            'decimal_points' => $priceFormatting[0],
                            'thousand_separator' => $rateCommaRemove ? '' : $priceFormatting[1]
                        ), $printFullDecimal) : null ?></td>
                    <?php endif; ?>

                    <?php
                    foreach($billColumnSettings as $billColumnSetting):
                        $billColumnSettingId = $billColumnSetting['id'];
                        $quantity = $itemRow && is_array($itemRow[7]) && array_key_exists($billColumnSettingId, $itemRow[7]) ? $itemRow[7][$billColumnSettingId] : 0;

                        $includeStatus = $itemRow && is_array($itemRow[8]) && array_key_exists($billColumnSettingId, $itemRow[8]) ? $itemRow[8][$billColumnSettingId] : 0;
                    ?>

                    <?php if ( is_null($includeStatus) OR ( ! is_bool ($includeStatus) AND empty($includeStatus)) ): ?>
                    <td class="bqQtyCell" style="text-align: center;">&nbsp;</td>
                    <td class="bqAmountCell" style="text-align: center;">&nbsp;</td>
                    <?php elseif ( $includeStatus == true ): ?>
                    <td class="bqQtyCell"><?php echo $quantity && $quantity != 0 ? Utilities::number_clean($quantity, array(
                            'decimal_points'     => $priceFormatting[0],
                            'thousand_separator' => $qtyCommaRemove ? '' : $priceFormatting[1],
                            'display_scientific' => array(
                                'displayFull' => $printFullDecimal,
                                'charLength' => 6
                            )
                        )) : null?></td>
                    <td class="bqAmountCell">
                        <?php
                        $amount = 0;

                        if ($rate && $rate != 0 && $quantity && $quantity != 0)
                        {
                            $amount                            = sfBuildspaceBQMasterFunction::gridCurrencyRoundingFormat($rate * $quantity);
                            $totalAmount[$billColumnSettingId] += $amount;

                            echo ! $printNoPrice ? Utilities::displayScientific($amount, ($printAmountOnly) ? 18 : 11, array( 
                                'decimal_places' => $priceFormatting[2],
                                'decimal_points' => $priceFormatting[0],
                                'thousand_separator' => $amtCommaRemove ? '' : $priceFormatting[1]
                            ), $printFullDecimal) : null;
                        }
                        ?>
                    </td>
                    <?php else: ?>
                    <td class="bqQtyCell" style="text-align: center;">-</td>
                    <td class="bqAmountCell" style="text-align: center;">-</td>
                    <?php endif; ?>

                    <?php endforeach?>
                <?php else: ?>
                    <?php
                    foreach($billColumnSettings as $billColumnSetting):
                        $billColumnSettingId = $billColumnSetting['id'];
                        $quantity = $itemRow && is_array($itemRow[7]) && array_key_exists($billColumnSettingId, $itemRow[7]) ? $itemRow[7][$billColumnSettingId] : 0;
                        $includeStatus = $itemRow && is_array($itemRow[8]) && array_key_exists($billColumnSettingId, $itemRow[8]) ? $itemRow[8][$billColumnSettingId] : 0;
                    ?>

                    <?php if ( $includeStatus ): ?>
                    <td class="bqAmountCell" colspan="2">
                        <?php
                        $amount = 0;

                        if ($rate && $rate != 0 && $quantity && $quantity != 0)
                        {
                            $amount                            = sfBuildspaceBQMasterFunction::gridCurrencyRoundingFormat($rate * $quantity);
                            $totalAmount[$billColumnSettingId] += $amount;

                            echo ! $printNoPrice ? number_format($amount, $priceFormatting[2], $priceFormatting[0], ($amtCommaRemove) ? '' : $priceFormatting[1]) : null;
                        }
                        ?>
                    </td>
                    <?php elseif ( is_null($includeStatus) OR ( ! is_bool ($includeStatus) AND empty($includeStatus)) ): ?>
                    <td class="bqAmountCell" colspan="2" style="text-align: center;">&nbsp;</td>
                    <?php else: ?>
                    <td class="bqAmountCell" colspan="2" style="text-align: center;">-</td>
                    <?php endif; ?>

                    <?php endforeach?>
                <?php endif; ?>

            <?php endif; ?>
        </tr>
        <?php unset($itemPage[$x]);?>
        <?php endfor; ?>
    <tr>
        <td class="bqCounterCell">&nbsp;</td>
        <td class="bqDescriptionCell"></td>

        <?php if ( ! $printAmountOnly ): ?>
            <td class="bqUnitCell"></td>
            <td class="bqQtyCell"></td>
            <?php foreach($billColumnSettings as $billColumnSetting):?>
            <td class="bqRateCell"></td>
            <td class="bqAmountCell"></td>
            <?php endforeach?>
        <?php else: ?>
            <?php foreach($billColumnSettings as $billColumnSetting):?>
            <td class="bqAmountCell" colspan="2"></td>
            <?php endforeach?>
        <?php endif; ?>
    </td>
    </tr>
    <tr>
        <?php if ( ! $printAmountOnly ): ?>
            <td class="footer" style="padding-right:5px;" colspan="4"><?php echo $toCollection; ?> (<?php echo $currency->currency_code; ?>)</td>
            <?php foreach($billColumnSettings as $billColumnSetting):?>
            <td class="footerSumAmount" colspan="2"><?php echo ! $printNoPrice ? Utilities::displayScientific($totalAmount[$billColumnSetting['id']], 18, array( 
                'decimal_places' => $priceFormatting[2],
                'decimal_points' => $priceFormatting[0],
                'thousand_separator' => $amtCommaRemove ? '' : $priceFormatting[1]
            ), $printFullDecimal) : null; ?></td>
            <?php endforeach?>
        <?php else: ?>
            <td class="footer" colspan="2" style="padding-right:5px;"><?php echo $toCollection; ?> (<?php echo $currency->currency_code; ?>)</td>
            <?php foreach($billColumnSettings as $billColumnSetting):?>
            <td class="footerSumAmount" colspan="2"><?php echo ! $printNoPrice ? Utilities::displayScientific($totalAmount[$billColumnSetting['id']], 18, array( 
                'decimal_places' => $priceFormatting[2],
                'decimal_points' => $priceFormatting[0],
                'thousand_separator' => $amtCommaRemove ? '' : $priceFormatting[1]
            ), $printFullDecimal) : null; ?>
            </td>
            <?php endforeach?>
        <?php endif; ?>
    </tr>
    <tr>
        <td class="pageFooter" colspan="<?php echo 4 + count($billColumnSettings) * 2?>">
            <table cellpadding="0" cellspacing="0" class="footer-table" style="width: 100%;">
                <tr>
                    <td style="width: 40%;" class="leftFooter"><?php echo $botLeftRow1; ?></td>
                    <td style="width: 20%; text-align: center;" class="pageFooter">&nbsp;</td>
                    <td style="width: 40%;">&nbsp;</td>
                </tr>
                <tr>
                    <td style="width: 40%;" class="leftFooter"><?php echo $botLeftRow2; ?></td>
                    <td style="width: 20%; text-align: center;" class="pageFooter">
                        <?php echo trim("{$pageNoPrefix}{$elementCount}/{$pageCount}"); ?>
                    </td>
                    <td style="width: 40%; text-align: right;"><?php echo $printDateOfPrinting ? '<span style="font-weight:bold;">Date of Printing: </span><span style="font-style: italic;">'.date('d/M/Y').'</span>' : '&nbsp;'; ?></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</div>
</body></html>