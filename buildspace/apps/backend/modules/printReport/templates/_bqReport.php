<?php use sfBuildspaceBQPageGenerator as PageGenerator; ?>
<table cellpadding="0" cellspacing="0" class="mainTable">
    <tr>
        <?php
            $headerCount = ($printQty) ? 6+(count($tenderers)) : 5+(count($tenderers));
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
        <td class="bqHeadCell" style="min-width:80px;width:80px;">Bill Ref</td>
        <td class="bqHeadCell" style="min-width:80px;width:<?php echo $descriptionWidth; ?>px"><?php echo $descHeader; ?></td>

        <td class="bqHeadCell" style="min-width:70px;width:70px;">Unit</td>

        <?php if($printQty) : ?>
            <td class="bqHeadCell" style="min-width:80px;width:80px;"><?php echo $qtyHeader; ?></td>
        <?php endif ?>

        <td class="bqHeadCell" style="min-width:100px;width:100px;"><?php echo "Estimate"; ?></td>

        <?php if ( count($tenderers) ): ?>
            <?php foreach($tenderers as $k => $tenderer) : ?>
                <td class="bqHeadCell" style="min-width:100px;width:100px;">
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
    <?php
    $rowCount = 0;
    $totalAmount = 0;
    $billColumnSettingId = $billColumnSettings[0]['id'];//single type will always return 1 bill column setting


    for($x=0; $x <= $maxRows; $x++):

        $itemPadding = 0;

        $itemRow = array_key_exists($x, $itemPage) ? $itemPage[$x] : false;

        if ( $itemRow[4] == PageGenerator::ROW_TYPE_ELEMENT AND ! $printElementInGrid )
        {
            $x++;
            continue;
        }

        if ( $printElementInGridOnce AND $itemRow[4] == PageGenerator::ROW_TYPE_ELEMENT AND $pageCount > 1 )
        {
            $x++;
            continue;
        }

        $rowCount++;

        if($itemRow[4] == BillItem::TYPE_ITEM_NOT_LISTED)
        {
            $rate = $itemRow ? $itemRow[6][0] : null;
        }
        else
        {
            $rate = $itemRow ? $itemRow[6] : null;
        }

        $itemId = $itemRow ? $itemRow[0] : null;

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

        if($itemRow && is_array($itemRow[7]))
        {
            $quantity = 0;

            foreach($billColumnSettings as $column)
            {
                $qtyField = array_key_exists($column['id'], $itemRow[7]) ? $itemRow[7][$column['id']] : 0;
                $quantity+= ($qtyField * $column['quantity']);
            }
        }
        else
        {
            $quantity = 0;
        }

        if ($itemRow and ($itemRow[4] == BillItem::TYPE_HEADER OR $itemRow[4] == BillItem::TYPE_HEADER_N))
        {
            $headerClass = 'bqHead'.$itemRow[3];
            $headerStyle = null;
        }
        elseif($itemRow and $itemRow[4] == PageGenerator::ROW_TYPE_ELEMENT)
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

        if ( $indentItem AND $itemRow AND ($itemRow[4] != PageGenerator::ROW_TYPE_ELEMENT AND $itemRow[4] != BillItem::TYPE_HEADER AND $itemRow[4] != BillItem::TYPE_HEADER_N) )
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
            <td class="bqDescriptionCell <?php echo $headerClass?>" style="padding-left: <?php echo $itemPadding; ?>px;">
                <?php if($itemRow[4] == PageGenerator::ROW_TYPE_PC_RATE):?>
                <?php include_partial('printBQ/bqReportItem/primeCostRateTable', array('currency'=>$currency, 'itemRow'=>$itemRow, 'priceFormatting'=> $priceFormatting, 'printNoPrice' => $printNoPrice)) ?>
                <?php else:?>
                <?php $preClass = $headerClass ? $headerClass : 'description'?>
                <?php echo $itemRow ? '<pre class="'.$preClass.'" style="'.$headerStyle.'">'.trim($itemRow[2]).'</pre>' : null?>
                <?php endif?>
            </td>

            <td class="bqUnitCell">
                <?php echo $itemRow ? $itemRow[ PageGenerator::ROW_BILL_ITEM_UNIT ] : '&nbsp;' ?>
            </td>

            <?php if ( $itemRow[4] == BillItem::TYPE_ITEM_RATE_ONLY ): ?>
                <?php include_partial('printReport/bqReportItem/itemRateOnly', array(
                    'itemRow'                  => $itemRow,
                    'rate'                     => $rate,
                    'tendererRates'            => $tendererRates,
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

            <?php elseif ( $itemRow[4] == BillItem::TYPE_ITEM_LUMP_SUM ): ?>
                <?php
                $amount = 0;

                if ($rate && $rate != 0)
                {
                    $amount      = $rate * 1;
                    $totalAmount += $amount;
                }

                include_partial('printReport/bqReportItem/itemLSAmt', array(
                    'itemRow'                  => $itemRow,
                    'rate'                     => $rate,
                    'amount'                   => $amount,
                    'quantity'                 => Utilities::number_clean(number_format($quantity, 2, '.', ''), array('decimal_points' => $priceFormatting[0], 'thousand_separator' => $priceFormatting[1])),
                    'tendererRates'            => $tendererRates,
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

            <?php elseif ( $itemRow[4] == BillItem::TYPE_ITEM_LUMP_SUM_EXCLUDE ): ?>
                <?php
                $amount = PageGenerator::gridCurrencyRoundingFormat($rate * 1);
                $totalAmount += $amount;

                include_partial('printReport/bqReportItem/itemLSExclude', array(
                    'itemRow'                  => $itemRow,
                    'quantity'                 => Utilities::number_clean(number_format($quantity, 2, '.', ''), array('decimal_points' => $priceFormatting[0], 'thousand_separator' => $priceFormatting[1])),
                    'rate'                     => $rate,
                    'amount'                   => number_format($amount, $priceFormatting[2], $priceFormatting[0], ($amtCommaRemove) ? '' : $priceFormatting[1]),
                    'tendererRates'            => $tendererRates,
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

            <?php elseif ( $itemRow[4] == BillItem::TYPE_ITEM_LUMP_SUM_PERCENT ): ?>
                <?php
                $amount = 0;

                if($rate && $rate != 0)
                {
                    $amount      = PageGenerator::gridCurrencyRoundingFormat($rate);
                    $totalAmount += $amount;
                }

                include_partial('printReport/bqReportItem/itemLSPercent', array(
                    'itemRow'                  => $itemRow,
                    'quantity'                 => Utilities::number_clean(number_format($quantity, 2, '.', ''), array('decimal_points' => $priceFormatting[0], 'thousand_separator' => $priceFormatting[1])),
                    'rate'                     => $rate,
                    'amount'                   => number_format($amount, $priceFormatting[2], $priceFormatting[0], ($amtCommaRemove) ? '' : $priceFormatting[1]),
                    'tendererRates'            => $tendererRates,
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

                <?php if ( $itemRow[4] == PageGenerator::ROW_TYPE_PC_RATE ): ?>
                    <td class="bqRateCell">&nbsp;</td>
                <?php else: ?>
                    <td class="bqRateCell"><?php echo ! $printNoPrice && $rate && $rate != 0 ? number_format($rate, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?></td>
                    <?php if(count($tenderers)) : $counter = 1; ?>
                        <?php foreach($tenderers as $k => $tenderer) : ?>
                            <td class="bqRateCell" style="<?php
                                if($tenderer['id'] == $lowestTendererId)
                                {
                                    echo $lowestStyle;
                                }
                                else if($tenderer['id'] == $highestTendererId)
                                {
                                    echo $highestStyle;
                                }
                            ?>">
                                <?php if ( $itemRow[4] == BillItem::TYPE_ITEM_NOT_LISTED ): ?>

                                    <?php
                                        $contractorRate = $itemRow ? $itemRow[6][$counter] : null;
                                        $counter++;
                                    ?>

                                    <?php echo ! $printNoPrice  && $itemId > 0 && $contractorRate != 0 ? number_format($contractorRate, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
                                <?php else: ?>
                                    <?php echo ! $printNoPrice  && $itemId > 0 && $tendererRates && array_key_exists($itemId, $tendererRates[$tenderer['id']]) && $tendererRates[$tenderer['id']][$itemId] != 0 ? number_format($tendererRates[$tenderer['id']][$itemId], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
                                <?php endif;?>
                            </td>
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
                <td class="footerSumAmount">
                    <?php echo ($estimateElementTotal) ? number_format($estimateElementTotal, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null; ?>
                </td>
                <?php if ( count($tenderers) ): ?>
                    <?php foreach($tenderers as $k => $tenderer) : ?>
                        <td class="footerSumAmount">
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

