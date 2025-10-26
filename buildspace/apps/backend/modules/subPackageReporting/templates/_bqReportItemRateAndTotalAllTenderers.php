<table cellpadding="0" cellspacing="0" class="mainTable">
    <tr>
        <!--
            1. Bill ref
            2. Description
            3. Unit
            4. Quantity
            5. Estimate Rate
            6. Estimate Total

            7. SubContractor Rate
            .  SubContractor Total
            .
        -->
        <?php
            $headerCount = 6 + ( count($subCons) * 2 );
        ?>

        <td colspan="<?php echo $headerCount; ?>">
            <?php include_partial('printReport/bqReportHeader', array('reportTitle' => $reportTitle, 'topLeftRow1' => $topLeftRow1, 'topLeftRow2' => $topLeftRow2)); ?>
        </td>
    </tr>

    <!-- Header -->
    <tr>
        <?php
            switch(count($subCons))
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

            if(count($subCons) > 3)
            {
                $descriptionWidth = 360;
            }
        ?>
        <td class="bqHeadCell" rowspan="2" style="min-width:80px;width:80px;">Bill Ref</td>
        <td class="bqHeadCell" rowspan="2" style="min-width:80px;width:<?php echo $descriptionWidth; ?>px"><?php echo $descHeader; ?></td>
        <td class="bqHeadCell" rowspan="2" style="min-width:80px;width:80px;">Unit</td>
        <td class="bqHeadCell" style="min-width:80px;width:80px;" rowspan="2"><?php echo $qtyHeader; ?></td>
        <td class="bqHeadCell" colspan="2" style="min-width:200px;width:200px;">Estimate</td>

        <?php foreach($subCons as $k => $subCon) : ?>
            <?php
                $subConName = (strlen($subCon['shortname'])) ? $subCon['shortname'] : ((strlen($subCon['name']) > 15) ? substr($subCon['name'],0,10).'...' : $subCon['name']);
                $isSelected = ( isset($subCon['selected']) AND $subCon['selected'] );
            ?>
            <td class="bqHeadCell" colspan="2" style="min-width:200px;width:200px;">
                <?php if($isSelected): ?>
                    <span style="color:red;">* </span>
                    <span style="color: blue;"><?php echo $subConName; ?></span>
                <?php else:?>
                    <?php echo $subConName; ?>
                <?php endif ?>
            </td>
        <?php endforeach; ?>
    </tr>
    <tr>
        <td class="bqHeadCell" style="min-width:100px;width:100px;">Rate</td>
        <td class="bqHeadCell" style="min-width:100px;width:100px;">Total</td>

        <?php foreach($subCons as $k => $subCon) : ?>
            <?php
                $style_color = '';
                $isSelected = ( isset($subCon['selected']) AND $subCon['selected'] );
                if($isSelected)
                {
                    $style_color = "color: blue";
                }
            ?>
            <td class="bqHeadCell" style="min-width:100px;width:100px;<?php echo $style_color?>">Rate</td>
            <td class="bqHeadCell" style="min-width:100px;width:100px;<?php echo $style_color?>">Total</td>
        <?php endforeach; ?>
    </tr>
    <!-- Header (End) -->

    <!-- Contents -->
    <?php
    $rowCount    = 0;
    $totalAmount = 0;

    // Variables to make the code (especially for comparisons) shorter in length (and hence, more readable).
    $itemRowIndex_id = sfSubPackageItemRateAllTendererPageGenerator::ROW_BILL_ITEM_ID;
    $itemRowIndex_rowIdx = sfSubPackageItemRateAllTendererPageGenerator::ROW_BILL_ITEM_ROW_IDX;
    $itemRowIndex_description = sfSubPackageItemRateAllTendererPageGenerator::ROW_BILL_ITEM_DESCRIPTION;
    $itemRowIndex_level = sfSubPackageItemRateAllTendererPageGenerator::ROW_BILL_ITEM_LEVEL;
    $itemRowIndex_type = sfSubPackageItemRateAllTendererPageGenerator::ROW_BILL_ITEM_TYPE;
    $itemRowIndex_unit = sfSubPackageItemRateAllTendererPageGenerator::ROW_BILL_ITEM_UNIT;
    $itemRowIndex_rate = sfSubPackageItemRateAllTendererPageGenerator::ROW_BILL_ITEM_RATE;
    $itemRowIndex_quantity = sfSubPackageItemRateAllTendererPageGenerator::ROW_BILL_ITEM_QTY_PER_UNIT;
    $itemRowIndex_total = sfSubPackageItemRateAllTendererPageGenerator::ROW_BILL_ITEM_TOTAL;

    for($x=0; $x <= $maxRows; $x++):

        $itemPadding  = 0;

        $itemRow      = array_key_exists($x, $itemPage) ? $itemPage[$x] : false;
        $itemPadding  = 6;
        $lowestStyle  = '';
        $highestStyle = '';
        $headerClass  = null;
        $headerStyle  = null;

        if ( $itemRow[$itemRowIndex_type] == sfBuildspaceBQPageGenerator::ROW_TYPE_ELEMENT AND ! $printElementInGrid )
        {
            $x++;
            continue;
        }

        if ( $printElementInGridOnce AND $itemRow[$itemRowIndex_type] == sfBuildspaceBQPageGenerator::ROW_TYPE_ELEMENT AND $pageCount > 1 )
        {
            $x++;
            continue;
        }

        $rowCount++;

        $description = '';
        if($itemRow[$itemRowIndex_type] == sfBuildspaceBQPageGenerator::ROW_TYPE_PC_RATE)
        {
            $isPCRate = true;

            // (printSubPackage/primeCostRateTable) partial uses currency->currency_code
            $currencyObject = new stdClass();
            $currencyObject->currency_code = $currency;
        }
        else
        {
            $preClass = $headerClass ? $headerClass : 'description';
            $description = $itemRow ? '<pre class="'.$preClass.'" style="'.$headerStyle.'">'.trim($itemRow[$itemRowIndex_description]).'</pre>' : null;
        }

        $itemId = $itemRow ? $itemRow[$itemRowIndex_id] : null;
        $itemQuantity   = $itemRow ? $itemRow[$itemRowIndex_quantity] : null;
        $rate   = $itemRow ? $itemRow[$itemRowIndex_rate] : null;
        $estimateRate = $rate && $rate != 0 ? number_format($rate, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null;

        $estimateTotal = null;

        if(!is_null($estimateRate))
        {
            $estimateTotal = $estimateRate * $itemQuantity;
            $estimateTotal = $estimateTotal && $estimateTotal != 0 ? number_format($estimateTotal, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null;
        }

        $counter = 1;

        if($itemId > 0 && count($subCons))
        {
            $lowestStyle       = '';
            $highestStyle      = '';
            $lowestTendererId  = null;
            $highestTendererId = null;

            $listOfRates = array();

            foreach($subCons as $k => $subCon)
            {
                if ( isset($subConRates[$subCon['id']][$itemId]) )
                {
                    $listOfRates[$subCon['id']] = $subConRates[$subCon['id']][$itemId];
                }
            }

            $lowestRate        = count($listOfRates) ? min($listOfRates) : 0;
            $highestRate       = count($listOfRates) ? max($listOfRates) : 0;
            $lowestTendererId  = array_search($lowestRate, $listOfRates);
            $highestTendererId = array_search($highestRate, $listOfRates);

            if ($lowestTendererId != $highestTendererId)
            {
                $highestStyle = "font-weight:bold;color:#ee4559;font-style:italic;";
                $lowestStyle  = "font-weight:bold;font-style:italic;color:#adf393;text-decoration:underline;";
            }

            $counter++;
        }

        if ($itemRow and ($itemRow[$itemRowIndex_type] == BillItem::TYPE_HEADER OR $itemRow[$itemRowIndex_type] == BillItem::TYPE_HEADER_N))
        {
            $headerClass = 'bqHead'.$itemRow[$itemRowIndex_level];
            $headerStyle = null;
        }
        elseif($itemRow and $itemRow[$itemRowIndex_type] == sfBuildspaceBQPageGenerator::ROW_TYPE_ELEMENT)
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

        if ( $indentItem AND $itemRow AND ($itemRow[$itemRowIndex_type] != sfBuildspaceBQPageGenerator::ROW_TYPE_ELEMENT AND $itemRow[$itemRowIndex_type] != BillItem::TYPE_HEADER AND $itemRow[$itemRowIndex_type] != BillItem::TYPE_HEADER_N) )
        {
            $itemPadding = 15;
        }
        ?>
        <tr>
            <td class="bqCounterCell"><?php echo $itemRow ? $itemRow[$itemRowIndex_rowIdx] : '&nbsp;'?></td>
            <td class="bqDescriptionCell <?php echo $headerClass?>" style="padding-left: <?php echo $itemPadding; ?>px;">
                <?php
                    if(isset($isPCRate) && $isPCRate)
                    {
                        include_partial('printSubPackage/primeCostRateTable', array(
                            'currency'=>$currencyObject,
                            'itemRow'=>$itemRow,
                            'priceFormatting'=> $priceFormatting,
                            'rateCommaRemove'=> $rateCommaRemove,
                            'printNoPrice' => $printNoPrice));
                    }
                    else
                    {
                        echo $description;
                    }
                ?>
            </td>

            <td class="bqUnitCell"><?php echo $itemRow ? $itemRow[$itemRowIndex_unit] : '&nbsp;'; ?></td>
            <td class="bqQtyCell"><?php echo $itemQuantity && $itemQuantity != 0 ? Utilities::number_clean(number_format($itemQuantity, 2, '.', ''), array('decimal_points' => $priceFormatting[0], 'thousand_separator' => $priceFormatting[1])) : null?></td>

            <?php if ( $itemRow[$itemRowIndex_type] == sfBuildspaceBQPageGenerator::ROW_TYPE_PC_RATE ): ?>
                <td class="bqRateCell">&nbsp;</td>
            <?php else: ?>

                <td class="bqRateCell"><?php echo $estimateRate ?></td>
                <td class="bqRateCell"><?php echo $estimateTotal ?></td>

                <?php if(count($subCons)) : $counter = 1; ?>
                    <?php foreach($subCons as $subCon) : ?>
                        <?php
                            $contractorRate = ( isset($subConRates[$subCon['id']][$itemId]) ) ? $subConRates[$subCon['id']][$itemId] : 0;
                            $contractorRate = ! $printNoPrice  && $itemId > 0 && $contractorRate != 0 ? number_format($contractorRate, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null;

                            $contractorTotal = null;

                            if(!is_null($contractorRate))
                            {
                                $contractorTotal = $contractorRate * $itemQuantity;
                                $contractorTotal = ! $printNoPrice  && $itemId > 0 && $contractorTotal != 0 ? number_format($contractorTotal, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null;
                            }

                            $style = '';
                            if(isset($lowestTendererId) && ($subCon['id'] == $lowestTendererId))
                            {
                                $style = $lowestStyle;
                            }
                            else if(isset($highestTendererId) && ($subCon['id'] == $highestTendererId))
                            {
                                $style = $highestStyle;
                            }
                        ?>
                        <td class="bqRateCell" style="<?php echo $style; ?>">
                            <?php echo $contractorRate; ?>
                        </td>
                        <td class="bqRateCell" style="<?php echo $style; ?>">
                            <?php echo $contractorTotal; ?>
                        </td>
                    <?php endforeach;?>
                <?php endif;?>
            <?php endif; ?>

        </tr>
        <?php unset($itemPage[$x], $amount);?>
    <?php endfor; ?>

    <!-- Contents (End) -->

    <!-- Footer -->
    <?php if($printGrandTotal) : ?>
        <tr>
            <td class="footer" style="padding-right:5px;" colspan="4">
                <?php echo $elementHeaderDescription; ?> (<?php echo $currency; ?>) :
            </td>
            <td class="footerSumAmount" colspan="2">
                <?php echo isset($estimateElementTotal[$elementId]) ? number_format($estimateElementTotal[$elementId], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null; ?>
            </td>
            <?php if ( count($subCons) ): ?>
                <?php foreach($subCons as $k => $subCon) : ?>
                    <td class="footerSumAmount" colspan="2">
                        <?php echo isset($contractorElementTotals[$subCon['id']][$elementId]) ? number_format($contractorElementTotals[$subCon['id']][$elementId], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null; ?>
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

    <!-- Footer (End) -->
</table>
</body>
</html>