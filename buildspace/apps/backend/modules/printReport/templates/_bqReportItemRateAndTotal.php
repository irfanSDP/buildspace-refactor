<?php use sfBuildspaceBQPageGenerator as PageGenerator; ?>
<table cellpadding="0" cellspacing="0" class="mainTable">
    <tr>
        <?php
            /*
             * Columns
             * 1. Bill Ref
             * 2. Description
             * 3. Unit
             * 4. Quantity
             * 5. Estimate (Rate)
             * 6. Estimate (Total)
             * */
            // 2 rows per tenderer ( rate and total )
            $headerCount = ($printQty) ? 7+(count($tenderers)*2) : 6+(count($tenderers)*2);
        ?>

        <td colspan="<?php echo $headerCount; ?>">
            <?php include_partial('bqReportHeader', array('reportTitle' => $reportTitle, 'topLeftRow1' => $topLeftRow1, 'topLeftRow2' => $topLeftRow2)); ?>
        </td>
    </tr>
    <tr>
        <?php
            switch(count($tenderers))
            {
                case 0 :
                    $descriptionWidth = 420;
                    break;
                case 1 :
                    $descriptionWidth = 325;
                    break;
                default :
                    $descriptionWidth = 340;
            }

            if(count($tenderers) > 3)
            {
                $descriptionWidth = 360;
            }

            if(!$printQty)
            {
                $descriptionWidth+=80;
            }

        ?>

        <td class="bqHeadCell" style="min-width:80px;width:80px;" rowspan="2">Bill Ref</td>
        <td class="bqHeadCell" style="min-width:80px;width:<?php echo $descriptionWidth; ?>px" rowspan="2"><?php echo $descHeader; ?></td>
        <td class="bqHeadCell" style="min-width:70px;width:70px;" rowspan="2">Unit</td>

        <?php if($printQty) : ?>
            <td class="bqHeadCell" style="min-width:80px;width:80px;" rowspan="2"><?php echo $qtyHeader; ?></td>
        <?php endif ?>

        <td class="bqHeadCell" style="min-width:100px;width:100px;" colspan="2"><?php echo "Estimate"; ?></td>

        <?php if ( count($tenderers) ): ?>
            <?php foreach($tenderers as $k => $tenderer) : ?>
                <td class="bqHeadCell" style="min-width:100px;width:100px;" colspan="2">
                    <?php if ( isset($tenderer['selected']) AND $tenderer['selected'] ) echo '<span style="color:red;">* </span>'; ?>

                    <?php $tendererName = (strlen($tenderer['shortname'])) ? $tenderer['shortname'] : ((strlen($tenderer['name']) > 15) ? substr($tenderer['name'],0,10).'...' : $tenderer['name']); ?>

                    <?php if ( isset($tenderer['selected']) AND $tenderer['selected'] ): ?>
                        <span style="color:blue;"><?php echo $tendererName; ?></span>
                    <?php else: ?>
                        <?php echo $tendererName; ?>
                    <?php endif; ?>
                </td>
            <?php endforeach; ?>
        <?php endif; ?>

    </tr>

    <tr>
        <td class="bqHeadCell" style="min-width:100px;width:100px;">
            Rate
        </td>
        <td class="bqHeadCell" style="min-width:100px;width:100px;">
            Total
        </td>
        <?php if ( count($tenderers) ): ?>
            <?php foreach($tenderers as $k => $tenderer) : ?>
                <td class="bqHeadCell" style="min-width:100px;width:100px;">
                    <?php if ( isset($tenderer['selected']) AND $tenderer['selected'] ): ?>
                        <span style="color:blue;">Rate</span>
                    <?php else: ?>
                        Rate
                    <?php endif; ?>
                </td>
                <td class="bqHeadCell" style="min-width:100px;width:100px;">
                    <?php if ( isset($tenderer['selected']) AND $tenderer['selected'] ): ?>
                        <span style="color:blue;">Total</span>
                    <?php else: ?>
                        Total
                    <?php endif; ?>
                </td>
            <?php endforeach; ?>
        <?php endif; ?>
    </tr>

    <?php
    $rowCount = 0;
    $totalAmount = 0;
    $billColumnSettingId = $billColumnSettings[0]['id'];//single type will always return 1 bill column setting


    for($x=0; $x <= $maxRows; $x++):

        $itemPadding = 0;

        $itemRow = array_key_exists($x, $itemPage) ? $itemPage[$x] : false;

        if ( $itemRow[PageGenerator::ROW_BILL_ITEM_TYPE] == PageGenerator::ROW_TYPE_ELEMENT AND ! $printElementInGrid )
        {
            $x++;
            continue;
        }

        if ( $printElementInGridOnce AND $itemRow[PageGenerator::ROW_BILL_ITEM_TYPE] == PageGenerator::ROW_TYPE_ELEMENT AND $pageCount > 1 )
        {
            $x++;
            continue;
        }

        $rowCount++;

        if($itemRow[PageGenerator::ROW_BILL_ITEM_TYPE] == BillItem::TYPE_ITEM_NOT_LISTED)
        {
            $estimatedRate = $itemRow ? $itemRow[PageGenerator::ROW_BILL_ITEM_RATE][0] : null;
            $estimatedTotal = $itemRow ? $itemRow[PageGenerator::ROW_BILL_ITEM_TOTAL][0] : null;
        }
        else
        {
            $estimatedRate = $itemRow ? $itemRow[PageGenerator::ROW_BILL_ITEM_RATE] : null;
            $estimatedTotal = $itemRow ? $itemRow[PageGenerator::ROW_BILL_ITEM_TOTAL] : null;
        }

        $itemId = $itemRow ? $itemRow[PageGenerator::ROW_BILL_ITEM_ID] : null;

        $counter = 1;

        if($itemId > 0 && count($tenderers))
        {
            $lowestTendererId = null;
            $highestTendererId = null;

            $listOfRates = array();

            foreach($tenderers as $k => $tenderer)
            {
                if(array_key_exists($tenderer['id'], $tendererRates) && array_key_exists($itemId, $tendererRates[$tenderer['id']]))
                {
                    $listOfRates[$tenderer['id']] = $tendererRates[$tenderer['id']][$itemId];
                }

            }

            $lowestRate = count($listOfRates) ? min($listOfRates) : 0;
            $highestRate = count($listOfRates) ? max($listOfRates) : 0;

            $lowestTendererId  = array_search($lowestRate, $listOfRates);
            $highestTendererId = array_search($highestRate, $listOfRates);

            if($lowestTendererId == $highestTendererId)
            {
                $lowestStyle = '';
                $highestStyle = '';
            }
            else
            {
                $highestStyle = "font-weight:bold;color:#ee4559;font-style:italic;";
                $lowestStyle = "font-weight:bold;font-style:italic;color:#adf393;text-decoration:underline;";
            }

            $counter++;
        }
        else
        {
            $lowestStyle = '';
            $highestStyle = '';
        }

        if($itemRow && is_array($itemRow[PageGenerator::ROW_BILL_ITEM_QTY_PER_UNIT]))
        {
            $quantity = 0;

            foreach($billColumnSettings as $column)
            {
                $qtyField = array_key_exists($column['id'], $itemRow[PageGenerator::ROW_BILL_ITEM_QTY_PER_UNIT]) ? $itemRow[PageGenerator::ROW_BILL_ITEM_QTY_PER_UNIT][$column['id']] : 0;
                $quantity+= ($qtyField * $column['quantity']);
            }
        }
        else
        {
            $quantity = 0;
        }

        if ($itemRow and ($itemRow[PageGenerator::ROW_BILL_ITEM_TYPE] == BillItem::TYPE_HEADER OR $itemRow[PageGenerator::ROW_BILL_ITEM_TYPE] == BillItem::TYPE_HEADER_N))
        {
            $headerClass = 'bqHead'.$itemRow[PageGenerator::ROW_BILL_ITEM_LEVEL];
            $headerStyle = null;
        }
        elseif($itemRow and $itemRow[PageGenerator::ROW_BILL_ITEM_TYPE] == PageGenerator::ROW_TYPE_ELEMENT)
        {
            $headerClass = 'elementHeader';
            $headerStyle = 'font-style: italic;';

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
            $headerStyle = null;
        }

        if ( $indentItem AND $itemRow AND ($itemRow[PageGenerator::ROW_BILL_ITEM_TYPE] != PageGenerator::ROW_TYPE_ELEMENT AND $itemRow[PageGenerator::ROW_BILL_ITEM_TYPE] != BillItem::TYPE_HEADER AND $itemRow[PageGenerator::ROW_BILL_ITEM_TYPE] != BillItem::TYPE_HEADER_N) )
        {
            $itemPadding = 15;
        }
        else
        {
            $itemPadding = 6;
        }
        ?>
        <tr>
            <td class="bqCounterCell"><?php echo $itemRow ? $itemRow[PageGenerator::ROW_BILL_ITEM_ROW_IDX] : '&nbsp;'?></td>
            <td class="bqDescriptionCell <?php echo $headerClass?>" style="padding-left: <?php echo $itemPadding; ?>px;">
                <?php if($itemRow[PageGenerator::ROW_BILL_ITEM_TYPE] == PageGenerator::ROW_TYPE_PC_RATE):?>
                <?php include_partial('printBQ/bqReportItem/primeCostRateTable', array('currency'=>$currency, 'itemRow'=>$itemRow, 'priceFormatting'=> $priceFormatting, 'printNoPrice' => $printNoPrice)) ?>
                <?php else:?>
                <?php $preClass = $headerClass ? $headerClass : 'description'?>
                <?php echo $itemRow ? '<pre class="'.$preClass.'" style="'.$headerStyle.'">'.trim($itemRow[PageGenerator::ROW_BILL_ITEM_DESCRIPTION]).'</pre>' : null?>
                <?php endif?>
            </td>
            <td class="bqUnitCell">
                <?php echo $itemRow ? $itemRow[ PageGenerator::ROW_BILL_ITEM_UNIT ] : '&nbsp;' ?>
            </td>

            <?php if ( $itemRow[PageGenerator::ROW_BILL_ITEM_TYPE] == BillItem::TYPE_ITEM_RATE_ONLY ): ?>
                <?php include_partial('printReport/bqReportItem/itemRateOnlyRateAndTotal', array(
                    'itemRow'                  => $itemRow,
                    'rate'                     => $estimatedRate,
                    'estimatedTotal'           => $estimatedTotal,
                    'tendererRates'            => $tendererRates,
                    'tendererTotals'           => $tendererTotals,
                    'tenderers'                => $tenderers,
                    'itemId'                   => $itemId,
                    'rateCommaRemove'          => $rateCommaRemove,
                    'priceFormatting'          => $priceFormatting,
                    'printNoPrice'             => $printNoPrice,
                    'toggleColumnArrangement'  => $toggleColumnArrangement,
                    'printDollarAndCentColumn' => $printDollarAndCentColumn,
                    'printAmountOnly'          => $printAmountOnly,
                    'printQty'                 => $printQty,
                    'lowestTendererId'         => isset( $lowestTendererId ) ? $lowestTendererId : null,
                    'highestTendererId'        => isset( $highestTendererId ) ? $highestTendererId : null,
                    'lowestStyle'              => $lowestStyle,
                    'highestStyle'             => $highestStyle,
                ));
                ?>

            <?php elseif ( $itemRow[PageGenerator::ROW_BILL_ITEM_TYPE] == BillItem::TYPE_ITEM_LUMP_SUM ): ?>
                <?php
                $amount = 0;

                if ($estimatedRate && $estimatedRate != 0)
                {
                    $amount      = $estimatedRate * 1;
                    $totalAmount += $amount;
                }

                include_partial('printReport/bqReportItem/itemLSAmtRateAndTotal', array(
                    'itemRow'                  => $itemRow,
                    'rate'                     => $estimatedRate,
                    'estimatedTotal'           => $estimatedTotal,
                    'amount'                   => $amount,
                    'quantity'                 => Utilities::number_clean(number_format($quantity, 2, '.', ''), array('decimal_points' => $priceFormatting[0], 'thousand_separator' => $priceFormatting[1])),
                    'tendererRates'            => $tendererRates,
                    'tendererTotals'           => $tendererTotals,
                    'tenderers'                => $tenderers,
                    'itemId'                   => $itemId,
                    'rateCommaRemove'          => $rateCommaRemove,
                    'priceFormatting'          => $priceFormatting,
                    'printNoPrice'             => $printNoPrice,
                    'toggleColumnArrangement'  => $toggleColumnArrangement,
                    'printDollarAndCentColumn' => $printDollarAndCentColumn,
                    'printAmountOnly'          => $printAmountOnly,
                    'amtCommaRemove'           => $amtCommaRemove,
                    'printQty'                 => $printQty,
                    'lowestTendererId'         => isset( $lowestTendererId ) ? $lowestTendererId : null,
                    'highestTendererId'        => isset( $highestTendererId ) ? $highestTendererId : null,
                    'lowestStyle'              => $lowestStyle,
                    'highestStyle'             => $highestStyle,
                ));
                ?>

            <?php elseif ( $itemRow[PageGenerator::ROW_BILL_ITEM_TYPE] == BillItem::TYPE_ITEM_LUMP_SUM_EXCLUDE ): ?>
                <?php
                $amount = PageGenerator::gridCurrencyRoundingFormat($estimatedRate * 1);
                $totalAmount += $amount;

                include_partial('printReport/bqReportItem/itemLSExcludeRateAndTotal', array(
                    'itemRow'                  => $itemRow,
                    'quantity'                 => Utilities::number_clean(number_format($quantity, 2, '.', ''), array('decimal_points' => $priceFormatting[0], 'thousand_separator' => $priceFormatting[1])),
                    'rate'                     => $estimatedRate,
                    'estimatedTotal'           => $estimatedTotal,
                    'amount'                   => number_format($amount, $priceFormatting[2], $priceFormatting[0], ($amtCommaRemove) ? '' : $priceFormatting[1]),
                    'tendererRates'            => $tendererRates,
                    'tendererTotals'           => $tendererTotals,
                    'tenderers'                => $tenderers,
                    'itemId'                   => $itemId,
                    'rateCommaRemove'          => $rateCommaRemove,
                    'priceFormatting'          => $priceFormatting,
                    'printNoPrice'             => $printNoPrice,
                    'toggleColumnArrangement'  => $toggleColumnArrangement,
                    'printDollarAndCentColumn' => $printDollarAndCentColumn,
                    'printAmountOnly'          => $printAmountOnly,
                    'printQty'                 => $printQty,
                    'lowestTendererId'         => isset( $lowestTendererId ) ? $lowestTendererId : null,
                    'highestTendererId'        => isset( $highestTendererId ) ? $highestTendererId : null,
                    'lowestStyle'              => $lowestStyle,
                    'highestStyle'             => $highestStyle,
                ));
                ?>

            <?php elseif ( $itemRow[PageGenerator::ROW_BILL_ITEM_TYPE] == BillItem::TYPE_ITEM_LUMP_SUM_PERCENT ): ?>
                <?php
                $amount = 0;

                if($estimatedRate && $estimatedRate != 0)
                {
                    $amount      = PageGenerator::gridCurrencyRoundingFormat($estimatedRate);
                    $totalAmount += $amount;
                }

                include_partial('printReport/bqReportItem/itemLSPercentRateAndTotal', array(
                    'itemRow'                  => $itemRow,
                    'quantity'                 => Utilities::number_clean(number_format($quantity, 2, '.', ''), array('decimal_points' => $priceFormatting[0], 'thousand_separator' => $priceFormatting[1])),
                    'rate'                     => $estimatedRate,
                    'estimatedTotal'           => $estimatedTotal,
                    'amount'                   => number_format($amount, $priceFormatting[2], $priceFormatting[0], ($amtCommaRemove) ? '' : $priceFormatting[1]),
                    'tendererRates'            => $tendererRates,
                    'tendererTotals'           => $tendererTotals,
                    'tenderers'                => $tenderers,
                    'itemId'                   => $itemId,
                    'rateCommaRemove'          => $rateCommaRemove,
                    'priceFormatting'          => $priceFormatting,
                    'printNoPrice'             => $printNoPrice,
                    'toggleColumnArrangement'  => $toggleColumnArrangement,
                    'printDollarAndCentColumn' => $printDollarAndCentColumn,
                    'printAmountOnly'          => $printAmountOnly,
                    'printQty'                 => $printQty,
                    'lowestTendererId'         => isset( $lowestTendererId ) ? $lowestTendererId : null,
                    'highestTendererId'        => isset( $highestTendererId ) ? $highestTendererId : null,
                    'lowestStyle'              => $lowestStyle,
                    'highestStyle'             => $highestStyle,
                ));
                ?>

            <?php else: ?>
                <?php if($printQty) : ?>
                    <td class="bqQtyCell"><?php echo $quantity && $quantity != 0 ? Utilities::number_clean(number_format($quantity, 2, '.', ''), array('decimal_points' => $priceFormatting[0], 'thousand_separator' => $priceFormatting[1])) : null?></td>
                <?php endif ?>

                <?php if ( $itemRow[PageGenerator::ROW_BILL_ITEM_TYPE] == PageGenerator::ROW_TYPE_PC_RATE ): ?>
                    <td class="bqRateCell">&nbsp;</td>
                    <td class="bqRateCell">&nbsp;</td>
                <?php else: ?>
                    <td class="bqRateCell"><?php echo ! $printNoPrice && $estimatedRate && $estimatedRate != 0 ? number_format($estimatedRate, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?></td>
                    <td class="bqRateCell"><?php echo ! $printNoPrice && $estimatedTotal && $estimatedTotal != 0 ? number_format($estimatedTotal, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?></td>
                    <?php if(count($tenderers)) : $counter = 1; ?>
                        <?php foreach($tenderers as $k => $tenderer) : ?>

                            <!-- set rate and total columns -->
                            <?php
                                if ($itemRow[PageGenerator::ROW_BILL_ITEM_TYPE] == BillItem::TYPE_ITEM_NOT_LISTED )
                                {
                                    $contractorRate = $itemRow ? $itemRow[PageGenerator::ROW_BILL_ITEM_RATE][$counter] : null;
                                    $contractorTotal = $itemRow ? $itemRow[PageGenerator::ROW_BILL_ITEM_TOTAL][$counter] : null;
                                    $counter++;
                                    $rateCellValue = ! $printNoPrice  && $itemId > 0 && $contractorRate != 0 ? number_format($contractorRate, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null;
                                    $totalCellValue = ! $printNoPrice  && $itemId > 0 && $contractorTotal != 0 ? number_format($contractorTotal, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null;
                                }
                                else
                                {
                                    $rateCellValue = ! $printNoPrice  && $itemId > 0 && $tendererRates && array_key_exists($itemId, $tendererRates[$tenderer['id']]) && $tendererRates[$tenderer['id']][$itemId] != 0 ? number_format($tendererRates[$tenderer['id']][$itemId], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null;
                                    $totalCellValue = ! $printNoPrice  && $itemId > 0 && $tendererTotals && array_key_exists($itemId, $tendererTotals[$tenderer['id']]) && $tendererTotals[$tenderer['id']][$itemId] != 0 ? number_format($tendererTotals[$tenderer['id']][$itemId], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null;
                                }
                            ?>

                            <!-- rate and total columns -->
                            <td class="bqRateCell" style="<?php
                                if($tenderer['id'] == $lowestTendererId)
                                {
                                    echo $lowestStyle;
                                }
                                else if($tenderer['id'] == $highestTendererId)
                                {
                                    echo $highestStyle;
                                }
                            ?>"><?php echo $rateCellValue ?></td>
                            <td class="bqRateCell" style="<?php
                                if($tenderer['id'] == $lowestTendererId)
                                {
                                    echo $lowestStyle;
                                }
                                else if($tenderer['id'] == $highestTendererId)
                                {
                                    echo $highestStyle;
                                }
                            ?>"><?php echo $totalCellValue ?></td>

                        <?php endforeach;?>
                    <?php endif;?>

                <?php endif; ?>

            <?php endif; ?>
        </tr>
        <?php unset($itemPage[$x], $amount);?>
        <?php endfor; ?>
        <?php if($printGrandTotal) : ?>
            <tr>
                <td class="footer" style="padding-right:5px;" colspan="<?php echo ($printQty) ? 4 : 3 ;?>">
                    <?php echo $elementHeaderDescription; ?> (<?php echo $currency->currency_code; ?>) :
                </td>
                <td class="footerSumAmount" colspan="2">
                    <?php echo ($estimateElementTotal) ? number_format($estimateElementTotal, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null; ?>
                </td>
                <?php if ( count($tenderers) ): ?>
                    <?php foreach($tenderers as $k => $tenderer) : ?>
                        <td class="footerSumAmount" colspan="2">
                            <?php echo ($contractorElementTotals && array_key_exists($tenderer['id'], $contractorElementTotals)) ? number_format($contractorElementTotals[$tenderer['id']], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null; ?>
                        </td>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tr>
            <tr>
                <td style="padding-right:5px;padding-top:10px;text-align:center;" colspan="<?php echo $headerCount; ?>">
                    Page <?php echo $pageCount; ?> of <?php echo $totalPage; ?>
                </td>
            </tr>
        <?php else: ?>
            <tr>
                <td class="footer" style="padding-right:5px;padding-top:10px;text-align:center;" colspan="<?php echo $headerCount; ?>">
                    Page <?php echo $pageCount; ?> of <?php echo $totalPage; ?>
                </td>
            </tr>
        <?php endif;?>
</table>
</body>
</html>

