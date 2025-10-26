<table cellpadding="0" cellspacing="0" class="mainTable">
    <tr>
        <?php $headerCount = 8; ?>
        <td colspan="<?php echo $headerCount; ?>">
            <?php include_partial('printReport/bqReportHeader', array('reportTitle' => $reportTitle,'topLeftRow1' => $topLeftRow1, 'topLeftRow2' => $topLeftRow2)); ?>
        </td>
    </tr>
    <tr>
        <td class="bqHeadCell" rowspan="2" style="min-width:60px;width:60px;">
            Item
        </td>
        <td class="bqHeadCell" rowspan="2" style="min-width:280px;width:280px;">
            <?php echo $descHeader; ?>
        </td>
        <td class="bqHeadCell" rowspan="2" style="min-width:60px;width:60px;">
            <?php echo "Unit"; ?>
        </td>
        <td class="bqHeadCell" rowspan="2" style="min-width:100px;width:100px;">
            <?php echo "Rate"; ?>
        </td>
        <td class="bqHeadCell" colspan="2">
            <?php echo "Omission"; ?>
        </td>
        <td class="bqHeadCell" colspan="2">
            <?php echo "Addition"; ?>
        </td>
    </tr>
    <tr>
        <td class="bqHeadCell" style="min-width:70px;width:70px;">
            <?php echo "Qty" ?>
        </td>
        <td class="bqHeadCell" style="min-width:100px;width:100px;">
            <?php echo "Amount"; ?>
        </td>
        <td class="bqHeadCell" style="min-width:70px;width:70px;">
            <?php echo "Qty" ?>
        </td>
        <td class="bqHeadCell" style="min-width:100px;width:100px;">
            <?php echo "Amount"; ?>
        </td>
    </tr>
    <?php

    $rowCount = 0;
    $totalAmount = 0;

    for($x=0; $x <= $maxRows; $x++):

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

        $rowCount++;

        $itemId = $itemRow ? $itemRow[0] : null;
        $rate   = $itemRow ? $itemRow[6] : null;
        $unit   = $itemRow ? $itemRow[sfBuildspaceVOItemsReportGenerator::ROW_BILL_ITEM_UNIT] : null;

        $qtyOmission = $itemRow && is_array($itemRow[sfBuildspaceVOItemsReportGenerator::ROW_OMISSION]) && array_key_exists('qty', $itemRow[sfBuildspaceVOItemsReportGenerator::ROW_OMISSION]) ? $itemRow[sfBuildspaceVOItemsReportGenerator::ROW_OMISSION]['qty'] : null;
        $amountOmission   = $itemRow && is_array($itemRow[sfBuildspaceVOItemsReportGenerator::ROW_OMISSION]) && array_key_exists('amount', $itemRow[sfBuildspaceVOItemsReportGenerator::ROW_OMISSION]) ? $itemRow[sfBuildspaceVOItemsReportGenerator::ROW_OMISSION]['amount'] : null;

        $qtyAddition = $itemRow && is_array($itemRow[sfBuildspaceVOItemsReportGenerator::ROW_ADDITION]) && array_key_exists('qty', $itemRow[sfBuildspaceVOItemsReportGenerator::ROW_ADDITION]) ? $itemRow[sfBuildspaceVOItemsReportGenerator::ROW_ADDITION]['qty'] : null;
        $amountAddition   = $itemRow && is_array($itemRow[sfBuildspaceVOItemsReportGenerator::ROW_ADDITION]) && array_key_exists('amount', $itemRow[sfBuildspaceVOItemsReportGenerator::ROW_ADDITION]) ? $itemRow[sfBuildspaceVOItemsReportGenerator::ROW_ADDITION]['amount'] : null;

        if ($itemRow and ($itemRow[4] == BillItem::TYPE_HEADER OR $itemRow[4] == BillItem::TYPE_HEADER_N))
        {
            $headerClass = 'bqHead'.$itemRow[3];
            $headerStyle = null;
        }
        elseif($itemRow and $itemRow[4] == sfBuildspaceBQPageGenerator::ROW_TYPE_ELEMENT)
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

        if ( $indentItem AND $itemRow AND ($itemRow[4] != sfBuildspaceBQPageGenerator::ROW_TYPE_ELEMENT AND $itemRow[4] != BillItem::TYPE_HEADER) )
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
                <?php $preClass = $headerClass ? $headerClass : 'description'?>
                <?php echo $itemRow ? '<pre class="'.$preClass.'" style="'.$headerStyle.'">'.trim($itemRow[2]).'</pre>' : null?>
            </td>
            <td class="bqRateCell" style="text-align: center;">
                <?php echo $unit ? $unit : null?>
            </td>
            <td class="bqRateCell">
                <?php echo ! $printNoPrice && $rate && $rate != 0 ? number_format($rate, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
            </td>
            <td class="bqQtyCell">
                <?php echo $qtyOmission ? ($qtyOmission == 0) ? '-' : Utilities::number_clean(number_format($qtyOmission, 2, '.', ''), array('decimal_points' => $priceFormatting[0], 'thousand_separator' => $priceFormatting[1])) : null?>
            </td>
            <td class="bqRateCell">
                <?php echo ! $printNoPrice && $amountOmission ? ($amountOmission == 0) ? '-' : sprintf('(%s)', number_format($amountOmission, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1])) : null ?>
            </td>
            <td class="bqQtyCell">
                <?php echo $qtyAddition ? ($qtyAddition == 0) ? '-' : Utilities::number_clean(number_format($qtyAddition, 2, '.', ''), array('decimal_points' => $priceFormatting[0], 'thousand_separator' => $priceFormatting[1])) : null?>
            </td>
            <td class="bqRateCell">
                <?php echo (! $printNoPrice && $amountAddition) ?  ($amountAddition == 0) ? '-' : number_format($amountAddition, $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null ?>
            </td>
        </tr>
            <?php unset($itemPage[$x], $amount);?>
        <?php endfor; ?>

        <?php if($printGrandTotal) : ?>
            <tr>
                <td class="footer" style="padding-right:5px;" colspan="4">
                    Total (<?php echo $currency->currency_code; ?>) :
                </td>
                <td class="footerSumAmount" colspan="2">
                    <?php echo ($variationTotal && array_key_exists('omission_amount', $variationTotal)) ? sprintf('(%s)', number_format($variationTotal['omission_amount'], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1])) : null; ?>
                </td>
                <td class="footerSumAmount" colspan="2">
                    <?php echo ($variationTotal && array_key_exists('addition_amount', $variationTotal)) ? number_format($variationTotal['addition_amount'], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null; ?>
                </td>
            </tr>
            <tr>
                <td style="padding-right:5px;" colspan="4">
                    Nett Omission/Addition (<?php echo $currency->currency_code; ?>) :
                </td>
                <td class="footerSumAmount" colspan="4">
                    <?php echo ($variationTotal && array_key_exists('addition_amount', $variationTotal) && array_key_exists('omission_amount', $variationTotal)) ? number_format($variationTotal['addition_amount'] - $variationTotal['omission_amount'], $priceFormatting[2], $priceFormatting[0], ($rateCommaRemove) ? '' : $priceFormatting[1]) : null; ?>
                </td>
            </tr>
        <?php else: ?>
            <tr>
                <td class="footer" style="padding-right:5px;padding-top:10px;text-align:center;" colspan="<?php echo $headerCount; ?>">
                    Page <?php echo $pageCount; ?> of <?php echo $totalPage; ?>
                </td>
            </tr>
        <?php endif;?>

        <?php if($isLastPage): ?>
            <tr>
                <td colspan="<?php echo $headerCount; ?>">
                <?php
                    include_partial('printReport/footerLayout', array(
                        'leftText' => $left_text,
                        'rightText' => $right_text
                    ));
                ?>
                </td>
            </tr>
        <?php endif; ?>
</table>
</body>
</html>