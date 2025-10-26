<table cellpadding="0" cellspacing="0" class="mainTable">
    <tr>
        <?php 
            $headerCount = 7;
        ?>

        <td colspan="<?php echo $headerCount; ?>">
            <?php include_partial('tradeReportHeader', 
                array(
                    'reportTitle' => $reportTitle, 
                    'headerDescription' => $headerDescription, 
                    'topLeftRow1' => $topLeftRow1, 
                    'topLeftRow2' => $topLeftRow2)
                ); 
            ?>
        </td>
    </tr>
    <tr>
        <td class="bqHeadCell" style="min-width:70px;width:70px;">
            No.
        </td>
        <td class="bqHeadCell" style="min-width:280px;width:280px;">
            <?php echo $descHeader; ?>
        </td>
        <td class="bqHeadCell" style="min-width:80px;width:80px;">
            <?php echo $unitHeader; ?>
        </td>
        <td class="bqHeadCell" style="min-width:100px;width:100px;">
            <?php echo $rateHeader; ?>
        </td>
        <td class="bqHeadCell" style="min-width:80px;width:80px;">
            <?php echo "Wastage (%)"; ?>
        </td>
        <td class="bqHeadCell" style="min-width:100px;width:100px;">
            <?php echo "Total Qty"; ?>
        </td>
        <td class="bqHeadCell" style="min-width:100px;width:100px;">
            <?php echo "Total Cost"; ?>
        </td>
    </tr>
    <?php

        $rowCount = 0;

        $totalAmount = 0;

        for($x=0; $x <= $maxRows; $x++):
            
            $itemPadding = 0;

            $itemRow = array_key_exists($x, $itemPage) ? $itemPage[$x] : false;

            $rowCount++;

            $rate = $itemRow ? $itemRow[sfBuildspaceResourceItemGenerator::ROW_BILL_ITEM_RATE] : null;
            
            $unit = $itemRow ? $itemRow[sfBuildspaceResourceItemGenerator::ROW_BILL_ITEM_UNIT] : null;

            $wastage = $itemRow ? $itemRow[sfBuildspaceResourceItemGenerator::ROW_BILL_ITEM_WASTAGE] : null;

            $totalQty = $itemRow ? $itemRow[sfBuildspaceResourceItemGenerator::ROW_BILL_ITEM_TOTAL_QTY] : null;

            $totalCost = $itemRow ? $itemRow[sfBuildspaceResourceItemGenerator::ROW_BILL_ITEM_TOTAL_COST] : null;

            $itemId = $itemRow ? $itemRow[0] : null;

            if ($itemRow and ($itemRow[4] == ResourceItem::TYPE_HEADER))
            {
                $headerClass = 'bqHead'.$itemRow[3];
                $headerStyle = null;
            }
            elseif($itemRow and $itemRow[4] == sfBuildspaceResourceItemGenerator::ROW_TYPE_ELEMENT)
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

            if ( $indentItem AND $itemRow AND ($itemRow[4] != sfBuildspaceResourceItemGenerator::ROW_TYPE_ELEMENT AND $itemRow[4] != ResourceItem::TYPE_HEADER) )
            {
                $itemPadding = 15;
            }
            else
            {
                $itemPadding = 6;
            }

        ?>
        <tr>
            <td class="bqCounterCell">
                <?php echo $itemRow ? $itemRow[1] : '&nbsp;'?>
            </td>
            <td class="bqDescriptionCell <?php echo $headerClass?>" style="padding-left: <?php echo $itemPadding; ?>px;">
                <?php $preClass = $headerClass ? $headerClass : 'description'?>
                <?php echo $itemRow ? '<pre class="'.$preClass.'" style="'.$headerStyle.'">'.trim($itemRow[2]).'</pre>' : null?>
            </td>
            <td class="bqUnitCell">
                <?php echo $unit; ?>
            </td>
            <td class="bqRateCell">
                <?php echo ! $printNoPrice && $rate && $rate != 0 ? number_format($rate, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
            </td>
            <td class="bqRateCell">
                <?php echo ! $printNoPrice && $wastage && $wastage != 0 ? number_format($wastage, 2).'%' : null ?>
            </td>
            <td class="bqQtyCell">
                <?php echo $totalQty && $totalQty != 0 ? Utilities::number_clean(number_format($totalQty, 2, '.', ''), array('decimal_points' => $priceFormatting[0], 'thousand_separator' => $priceFormatting[1])) : null?>
            </td>
            <td class="bqRateCell">
                <?php echo ! $printNoPrice && $totalCost && $totalCost != 0 ? number_format($totalCost, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
            </td>
        </tr>
            <?php unset($itemPage[$x], $amount);?>
        <?php endfor; ?>

        <?php if($printGrandTotal) : ?>
            <tr>
                <td class="footer" style="padding-right:5px;" colspan="<?php echo 3; ?>">
                    <?php echo "Total "; ?> (<?php echo $currency->currency_code; ?>) :
                </td>
                <td class="footerSumAmount">
                    &nbsp;
                </td>
                <td class="footerSumAmount">
                    &nbsp;
                </td>
                <td class="footerSumAmount">
                    &nbsp;
                </td>
                <td class="footerSumAmount">
                    &nbsp;
                </td>
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

