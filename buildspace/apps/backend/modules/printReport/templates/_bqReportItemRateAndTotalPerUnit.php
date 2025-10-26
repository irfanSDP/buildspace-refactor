<?php use sfBuildspaceReportItemRateAndTotalPerUnitPageGenerator as PageGenerator; ?>
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
        $headerCount = 7+(count($tenderers)*2);
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

        ?>

        <td class="bqHeadCell" style="min-width:80px;width:80px;" rowspan="2">Bill Ref</td>
        <td class="bqHeadCell" style="min-width:80px;width:<?php echo $descriptionWidth; ?>px" rowspan="2">Description</td>
        <td class="bqHeadCell" style="min-width:70px;width:70px;" rowspan="2">Unit</td>

        <td class="bqHeadCell" style="min-width:80px;width:80px;" rowspan="2">Single Unit Quantity</td>

        <td class="bqHeadCell" style="min-width:100px;width:100px;" colspan="2">Estimate</td>

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
            Single Unit Total
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
    for($x=0; $x <= $maxRows; $x++):

        $itemPadding = 0;

        $itemRow = array_key_exists($x, $itemPage) ? $itemPage[$x] : false;

        $itemId = $itemRow ? $itemRow[PageGenerator::ROW_BILL_ITEM_ID] : null;

        $counter = 1;

        // Estimate Quantity.
        $quantity = 0;
        if($itemRow && is_array($itemRow[PageGenerator::ROW_BILL_ITEM_QTY_PER_UNIT]))
        {
            $quantity = array_key_exists($billColumnSetting->id, $itemRow[PageGenerator::ROW_BILL_ITEM_QTY_PER_UNIT]) ? $itemRow[PageGenerator::ROW_BILL_ITEM_QTY_PER_UNIT][$billColumnSetting->id] : null;
        }

        // Estimate Rate.
        $estimatedRate = $itemRow ? $itemRow[PageGenerator::ROW_BILL_ITEM_RATE] : null;

        // Estimate Total.
        $estimatedTotal = 0;
        if($itemRow && is_array($itemRow[PageGenerator::ROW_BILL_ITEM_TOTAL]))
        {
            $estimatedTotal = array_key_exists($billColumnSetting->id, $itemRow[PageGenerator::ROW_BILL_ITEM_TOTAL]) ? $itemRow[PageGenerator::ROW_BILL_ITEM_TOTAL][$billColumnSetting->id] : null;
        }

        // Tenderer Style.
        if($itemId > 0 && count($tenderers))
        {
            $lowestTendererId = null;
            $highestTendererId = null;

            $listOfRates = array();

            foreach($tenderers as $k => $tenderer)
            {
                if( isset( $contractorRates[ $tenderer['id'] ][ $elementId ][ $itemId ] ) )
                {
                    $listOfRates[$tenderer['id']] = $contractorRates[ $tenderer['id'] ][ $elementId ][ $itemId ];
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

        // Header Style.
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
                <?php include_partial('printReport/bqReportItemPerUnit/itemRateOnly', array(
                    'itemRow'           => $itemRow,
                    'rate'              => $estimatedRate,
                    'quantity'          => Utilities::number_clean(number_format($quantity, 2, '.', ''), array( 'decimal_points' => $priceFormatting[0], 'thousand_separator' => $priceFormatting[1] )),
                    'tenderers'         => $tenderers,
                    'itemId'            => $itemId,
                    'elementId'         => $elementId,
                    'rateCommaRemove'   => $rateCommaRemove,
                    'priceFormatting'   => $priceFormatting,
                    'printNoPrice'      => $printNoPrice,
                    'contractorRates'   => $contractorRates,
                    'lowestTendererId'  => isset( $lowestTendererId ) ? $lowestTendererId : null,
                    'highestTendererId' => isset( $highestTendererId ) ? $highestTendererId : null,
                    'lowestStyle'       => $lowestStyle,
                    'highestStyle'      => $highestStyle,
                ));
                ?>

            <?php elseif ( $itemRow[PageGenerator::ROW_BILL_ITEM_TYPE] == BillItem::TYPE_ITEM_LUMP_SUM ): ?>
                <?php
                include_partial('printReport/bqReportItemPerUnit/itemLSAmt', array(
                    'itemRow'           => $itemRow,
                    'rate'              => $estimatedRate,
                    'estimatedTotal'    => $estimatedTotal,
                    'quantity'          => Utilities::number_clean(number_format($quantity, 2, '.', ''), array( 'decimal_points' => $priceFormatting[0], 'thousand_separator' => $priceFormatting[1] )),
                    'tenderers'         => $tenderers,
                    'itemId'            => $itemId,
                    'elementId'         => $elementId,
                    'rateCommaRemove'   => $rateCommaRemove,
                    'priceFormatting'   => $priceFormatting,
                    'printNoPrice'      => $printNoPrice,
                    'contractorRates'   => $contractorRates,
                    'lowestTendererId'  => isset( $lowestTendererId ) ? $lowestTendererId : null,
                    'highestTendererId' => isset( $highestTendererId ) ? $highestTendererId : null,
                    'lowestStyle'       => $lowestStyle,
                    'highestStyle'      => $highestStyle,
                ));
                ?>

            <?php elseif ( $itemRow[PageGenerator::ROW_BILL_ITEM_TYPE] == BillItem::TYPE_ITEM_LUMP_SUM_EXCLUDE ): ?>
                <?php
                $amount = PageGenerator::gridCurrencyRoundingFormat($estimatedRate);

                include_partial('printReport/bqReportItemPerUnit/itemLSExclude', array(
                    'itemRow'           => $itemRow,
                    'quantity'          => Utilities::number_clean(number_format($quantity, 2, '.', ''), array( 'decimal_points' => $priceFormatting[0], 'thousand_separator' => $priceFormatting[1] )),
                    'estimatedTotal'    => $estimatedTotal,
                    'amount'            => number_format($amount, $priceFormatting[2], $priceFormatting[0], ( $amtCommaRemove ) ? '' : $priceFormatting[1]),
                    'tenderers'         => $tenderers,
                    'itemId'            => $itemId,
                    'elementId'         => $elementId,
                    'rateCommaRemove'   => $rateCommaRemove,
                    'priceFormatting'   => $priceFormatting,
                    'printNoPrice'      => $printNoPrice,
                    'printAmountOnly'   => $printAmountOnly,
                    'contractorRates'   => $contractorRates,
                    'lowestTendererId'  => isset( $lowestTendererId ) ? $lowestTendererId : null,
                    'highestTendererId' => isset( $highestTendererId ) ? $highestTendererId : null,
                    'lowestStyle'       => $lowestStyle,
                    'highestStyle'      => $highestStyle,
                ));
                ?>

            <?php elseif ( $itemRow[PageGenerator::ROW_BILL_ITEM_TYPE] == BillItem::TYPE_ITEM_LUMP_SUM_PERCENT ): ?>
                <?php
                include_partial('printReport/bqReportItemPerUnit/itemLSPercent', array(
                    'itemRow'                 => $itemRow,
                    'quantity'                => Utilities::number_clean(number_format($quantity, 2, '.', ''), array( 'decimal_points' => $priceFormatting[0], 'thousand_separator' => $priceFormatting[1] )),
                    'rate'                    => $estimatedRate,
                    'estimatedTotal'          => $estimatedTotal,
                    'tenderers'               => $tenderers,
                    'itemId'                  => $itemId,
                    'elementId'               => $elementId,
                    'rateCommaRemove'         => $rateCommaRemove,
                    'priceFormatting'         => $priceFormatting,
                    'printNoPrice'            => $printNoPrice,
                    'toggleColumnArrangement' => $toggleColumnArrangement,
                    'printAmountOnly'         => $printAmountOnly,
                    'contractorRates'         => $contractorRates,
                    'lowestTendererId'        => isset( $lowestTendererId ) ? $lowestTendererId : null,
                    'highestTendererId'       => isset( $highestTendererId ) ? $highestTendererId : null,
                    'lowestStyle'             => $lowestStyle,
                    'highestStyle'            => $highestStyle,
                ));
                ?>

            <?php else: ?>
                <td class="bqQtyCell"><?php echo $quantity && $quantity != 0 ? Utilities::number_clean(number_format($quantity, 2, '.', ''), array('decimal_points' => $priceFormatting[0], 'thousand_separator' => $priceFormatting[1])) : null?></td>

                <?php if ( $itemRow[PageGenerator::ROW_BILL_ITEM_TYPE] == PageGenerator::ROW_TYPE_PC_RATE ): ?>
                    <td class="bqRateCell">&nbsp;</td>
                    <td class="bqRateCell">&nbsp;</td>
                <?php else: ?>
                    <td class="bqRateCell"><?php echo ! $printNoPrice && $estimatedRate && $estimatedRate != 0 ? number_format($estimatedRate, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?></td>
                    <td class="bqRateCell"><?php echo ! $printNoPrice && $estimatedTotal && $estimatedTotal != 0 ? number_format($estimatedTotal, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?></td>

                    <?php foreach($tenderers as $k => $tenderer) : ?>

                        <!-- set rate and total columns -->
                        <?php
                        $rate = ( isset( $contractorRates[ $tenderer['id'] ][ $elementId ][ $itemId ] ) ) ? $contractorRates[ $tenderer['id'] ][ $elementId ][ $itemId ] : 0;
                        $total = $rate * $quantity;
                        $rateCellValue = ( ! $printNoPrice && $itemId > 0 && ( $rate != 0 ) ) ? number_format($rate, $priceFormatting[2], $priceFormatting[0], ( $rateCommaRemove ) ? '' : $priceFormatting[1]) : null;
                        $totalCellValue = ( ! $printNoPrice && $itemId > 0 && ( $total != 0 ) ) ? number_format($total, $priceFormatting[2], $priceFormatting[0], ( $rateCommaRemove ) ? '' : $priceFormatting[1]) : null;
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

                <?php endif; ?>

            <?php endif; ?>
        </tr>
        <?php unset($itemPage[$x], $amount);?>
    <?php endfor; ?>

    <!--=======
    Footer
    ========-->
    <?php if ( $printGrandTotal ) : ?>
        <!-- Unit Total -->
        <tr>
            <td class="footer" style="padding-right:5px;" colspan="4">
                Total Per Unit (<?php echo $currency->currency_code; ?>) :
            </td>
            <td class="footerSumAmount" colspan="2">
                <?php echo ( isset( $estimateOverAllTotal[ $elementId ][ $billColumnSetting->id ] ) && $estimateOverAllTotal[ $elementId ][ $billColumnSetting->id ] != 0 ) ? number_format($estimateOverAllTotal[ $elementId ][ $billColumnSetting->id ], $priceFormatting[2], $priceFormatting[0], ( $rateCommaRemove ) ? '' : $priceFormatting[1]) : null; ?>
            </td>
            <?php foreach ( $tenderers as $k => $tenderer ) : ?>
                <td class="footerSumAmount" colspan="2">
                    <?php
                        $overallTotal = isset( $contractorOverAllTotal[ $tenderer['id'] ][ $elementId ][ $billColumnSetting->id ] ) ? $contractorOverAllTotal[ $tenderer['id'] ] [ $elementId ][ $billColumnSetting->id ] : 0;
                        echo ( $overallTotal != 0 ) ? number_format($overallTotal, $priceFormatting[2], $priceFormatting[0], ( $rateCommaRemove ) ? '' : $priceFormatting[1]) : null;
                    ?>
                </td>
            <?php endforeach; ?>
        </tr>

        <!-- Units -->
        <tr>
            <td style="padding-right: 5px;" colspan="4">Units :</td>
                 <td class="footerSumAmount" colspan="<?php echo ((1 + count($tenderers)) * 2); ?>"><?php echo $billColumnSetting->quantity; ?></td>
        </tr>

        <!-- Grand Total -->
        <tr>
            <td style="padding-right: 5px;" colspan="4">Final Total (<?php echo $currency->currency_code; ?>) :</td>
            <td class="footerSumAmount" colspan="2">
                <?php echo ( isset( $estimateOverAllTotal[$elementId][$billColumnSetting->id] ) && $estimateOverAllTotal[$elementId][$billColumnSetting->id] != 0 ) ? number_format($estimateOverAllTotal[$elementId][$billColumnSetting->id] * $billColumnSetting->quantity, $priceFormatting[2], $priceFormatting[0], ( $rateCommaRemove ) ? '' : $priceFormatting[1]) : null; ?>
            </td>
            <?php foreach ( $tenderers as $k => $tenderer ) : ?>
                <td class="footerSumAmount" colspan="2">
                    <?php echo ( isset( $contractorOverAllTotal[ $tenderer['id'] ][ $itemId ][ $billColumnSetting->id ] ) && $contractorOverAllTotal[ $tenderer['id'] ] [ $itemId ][ $billColumnSetting->id ] != 0 ) ? number_format($contractorOverAllTotal[ $tenderer['id'] ] [ $itemId ][ $billColumnSetting->id ] * $billColumnSetting->quantity, $priceFormatting[2], $priceFormatting[0], ( $rateCommaRemove ) ? '' : $priceFormatting[1]) : null; ?>
                    <?php
                        $overallTotal = isset( $contractorOverAllTotal[ $tenderer['id'] ][ $elementId ][ $billColumnSetting->id ] ) ? $contractorOverAllTotal[ $tenderer['id'] ] [ $elementId ][ $billColumnSetting->id ] : 0;
                        $finalTotal = $overallTotal * $billColumnSetting->quantity;
                        echo ( $finalTotal != 0 ) ? number_format($finalTotal, $priceFormatting[2], $priceFormatting[0], ( $rateCommaRemove ) ? '' : $priceFormatting[1]) : null;
                    ?>
                </td>
            <?php endforeach; ?>
        </tr>
        <tr>
            <td style="padding-right:5px;padding-top:10px;text-align:center;" colspan="<?php echo $headerCount; ?>">
                Page <?php echo $pageCount; ?>
            </td>
        </tr>
    <?php else: ?>
        <tr>
            <td class="footer" style="padding-right:5px;padding-top:10px;text-align:center;"
                colspan="<?php echo $headerCount; ?>">
                Page <?php echo $pageCount; ?>
            </td>
        </tr>
    <?php endif; ?>
</table>
</body>
</html>

